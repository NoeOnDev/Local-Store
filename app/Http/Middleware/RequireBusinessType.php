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

        if (!$user->has_custom_fields) {
            $businessTypes = app()->make('App\Http\Controllers\AuthController')->getBusinessTypes()->original;

            return response()->json([
                'message' => 'Debes configurar campos personalizados para tu negocio',
                'error_type' => 'custom_fields_required',
                'business_types' => $businessTypes,
                'options' => [
                    'customize_template' => 'Personalizar template existente',
                    'create_custom' => 'Crear desde cero'
                ]
            ], 403);
        }

        return $next($request);
    }
}
