<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Validates the Authorization Bearer token using JWTAuth,
     * attaches `user_id` and `TOKEN` to the request, or returns 401.
     */
    public function handle($request, Closure $next)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return ResponseHelper::errorResponse(401, 'Token tidak ditemukan');
            }

            // Authenticate user from token
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return ResponseHelper::errorResponse(401, 'Token tidak valid');
            }

            // Attach helpful params for downstream usage
            $request->merge([
                'user_id' => $user->id,
                'TOKEN' => $token,
            ]);

            return $next($request);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            $response = ResponseHelper::errorResponse(401, 'Token kedaluwarsa');
            return response()->json($response, 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            $response = ResponseHelper::errorResponse(401, 'Token tidak valid');
            return response()->json($response, 401);
        } catch (\Exception $e) {
            Log::error('JwtMiddleware@handle: ' . $e->getMessage());
            $response = ResponseHelper::errorResponse(401, 'Autentikasi gagal');
            return response()->json($response, 401);
        }
    }
}