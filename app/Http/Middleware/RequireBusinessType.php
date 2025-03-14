<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireBusinessType
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->business_type_id === null && !$user->has_custom_fields) {
            $businessTypes = app()->make('App\Http\Controllers\AuthController')->getBusinessTypes()->original;

            return response()->json([
                'message' => 'Debes seleccionar un tipo de negocio o crear campos personalizados',
                'error_type' => 'business_type_required',
                'business_types' => $businessTypes,
                'options' => [
                    'select_template' => 'Usar template predefinido',
                    'customize_template' => 'Personalizar template existente',
                    'create_custom' => 'Crear desde cero'
                ]
            ], 403);
        }

        return $next($request);
    }
}
