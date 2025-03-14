<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCustomFields
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user->has_custom_fields) {
            return response()->json([
                'message' => 'No puedes modificar campos en un template predefinido. Debes usar el modo "customize" al seleccionar el tipo de negocio.',
                'error_type' => 'template_locked'
            ], 403);
        }

        return $next($request);
    }
}
