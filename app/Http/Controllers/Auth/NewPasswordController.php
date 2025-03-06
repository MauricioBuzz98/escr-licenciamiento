<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        $token = $request->route('token');
        $email = $request->email;
        
        // Verificar si el token existe y no ha expirado
        $tokenRecord = DB::table(config('auth.passwords.users.table'))
            ->where('email', $email)
            ->first();
            
        $tokenExpired = false;
        
        // Si no se encuentra el token o ha expirado
        if (!$tokenRecord || (now()->subMinutes(config('auth.passwords.users.expire'))->isAfter($tokenRecord->created_at))) {
            $tokenExpired = true;
        }
        
        return Inertia::render('Auth/ResetPassword', [
            'email' => $email,
            'token' => $token,
            'tokenExpired' => $tokenExpired,
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        // Verificar si el token existe y no ha expirado
        $tokenRecord = DB::table(config('auth.passwords.users.table'))
            ->where('email', $request->email)
            ->first();
            
        // Si no se encuentra el token o ha expirado
        if (!$tokenRecord || (now()->subMinutes(config('auth.passwords.users.expire'))->isAfter($tokenRecord->created_at))) {
            throw ValidationException::withMessages([
                'email' => ['El enlace para restablecer la contraseña ha expirado o no es válido.'],
            ]);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
