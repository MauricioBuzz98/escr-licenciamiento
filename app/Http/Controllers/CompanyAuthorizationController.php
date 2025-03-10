<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\AutoEvaluationResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
class CompanyAuthorizationController extends Controller
{
    public function authorizeCompany(Company $company)
    {
        // Verificar si el formulario fue enviado
        $autoEvaluationResult = AutoEvaluationResult::where('company_id', $company->id)
            ->first();

        if (!$autoEvaluationResult || !$autoEvaluationResult->form_sended) {
            return response()->json([
                'message' => 'La empresa debe completar y enviar el formulario antes de ser autorizada'
            ], 422);
        }

        $company->authorized = true;

        // Actualizar la columna estado_eval en la tabla companies
        $company->update(['estado_eval' => 'evaluacion']);

        $company->save();

        $adminUser = User::where('company_id', $company->id)->where('role', 'admin')->first();

        try {
            Mail::to($adminUser->email)->send(new \App\Mail\AuthorizeToEvalaution($company));
        } catch (\Exception $e) {
            // Log the error or handle it as needed, but continue execution
            Log::error('Error al enviar el correo de autorización: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Empresa autorizada exitosamente']);
    }
}
