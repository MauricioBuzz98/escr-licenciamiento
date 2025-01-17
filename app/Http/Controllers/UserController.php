<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->input('role') !== 'todos') {
            $query->where('role', $request->input('role'));
        }

        return $query->paginate($request->input('per_page', 10));
    }

    public function indexCompany(Request $request)
    {
        $query = User::query()
            ->where('company_id', auth()->user()->company_id);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->input('role') !== 'todos') {
            $query->where('role', $request->input('role'));
        }

        return $query->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombreCompleto' => 'required|string',
                'correo' => 'required|email|unique:users,email',
                'puesto' => 'required|string',
                'telefono' => 'required|string',
            ]);

            // Separar nombre y apellido
            $nombreCompleto = explode(' ', $request->nombreCompleto);
            $name = $nombreCompleto[0];
            $lastname = count($nombreCompleto) > 1 ? implode(' ', array_slice($nombreCompleto, 1)) : '';

            // Generar contraseña aleatoria
            $password = Str::random(10);

            $user = User::create([
                'name' => $name,
                'lastname' => $lastname,
                'email' => $request->correo,
                'phone' => $request->telefono,
                'position' => $request->puesto,
                'password' => Hash::make($password),
                'role' => 'user',
                'status' => 'approved',
                'company_id' => auth()->user()->company_id,
                'form_sended' => false,
            ]);

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'user' => $user
            ], 201); // Agregamos código de estado 201 para Created

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error de validación
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log del error para debugging
            Log::error('Error creating user: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al crear usuario',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nombreCompleto' => 'required|string',
            'correo' => 'required|email|unique:users,email,' . $user->id,
            'puesto' => 'required|string',
            'telefono' => 'required|string',
        ]);

        $nombreCompleto = explode(' ', $request->nombreCompleto);
        $name = $nombreCompleto[0];
        $lastname = count($nombreCompleto) > 1 ? implode(' ', array_slice($nombreCompleto, 1)) : '';

        try {
            $user->update([
                'name' => $name,
                'lastname' => $lastname,
                'email' => $request->correo,
                'phone' => $request->telefono,
                'position' => $request->puesto,
            ]);

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar usuario'], 500);
        }
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    public function getPendingUsers()
    {
        try {
            $pendingUsers = User::where('company_id', auth()->user()->company_id)
                            ->where('status', 'pending')
                            ->get(['id', 'name', 'lastname', 'email', 'created_at']);

            return response()->json($pendingUsers);
        } catch (\Exception $e) {
            Log::error('Error getting pending users: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener usuarios pendientes'], 500);
        }
    }
} 