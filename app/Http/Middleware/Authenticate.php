<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;//$request->expectsJson() ? null : route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 401)
        );
    }
}
