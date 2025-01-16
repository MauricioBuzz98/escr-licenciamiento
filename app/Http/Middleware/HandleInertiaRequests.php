<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        if ($user) {
            $autoEvaluationResult = \App\Models\AutoEvaluationResult::where('company_id', $user->company_id)->first();
            $user->form_sended = $autoEvaluationResult ? $autoEvaluationResult->form_sended : false;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'dashboard_route' => $this->getDashboardRoute($user),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }

    private function getDashboardRoute($user)
    {
        if (!$user) return null;

        return match ($user->role) {
            'super_admin' => 'super.dashboard',
            'admin' => 'admin.dashboard',
            'evaluador' => 'evaluador.dashboard',
            default => 'dashboard',
        };
    }
}
