<?php

namespace App\Repositories;

use App\Helpers\MessageHelper;
use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository
{
    public function aksiRegisterUser($params)
    {
        try {
            $validator = Validator::make($params, [
                'username' => 'required|min:4|unique:users',
                'email'     => 'nullable|email|unique:users',
                'role_id'   => 'required',
                'password'  => 'required',
                'password_confirmation' => 'required|same:password',
            ], [
                'username.required' => 'Username harus diisi',
                'email.email'       => 'Pastikan format email benar',
                'email.unique'      => 'Email sudah dipakai',
                'role_id.required'  => 'Role pengguna harus diisi',
                'password.required' => 'Password harus diisi',
                'password_confirmation.required' => 'Konfirmasi password harus diisi',
                'password_confirmation.same' => 'Password dan konfirmasi password tidak cocok',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $user = User::create([
                'username' => $params['username'],
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
                'role_id' => $params['role_id']
            ]);

            return ResponseHelper::successResponse(MessageHelper::successCreated('user'), $user);
        } catch (\Exception $exception) {
            Log::error('AuthRepository@aksiRegisterUser: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiLoginUser($params)
    {
        try {
            $validator = Validator::make($params, [
                'username' => 'required',
                'password' => 'required',
            ], [
                'username.required' => 'Username harus diisi',
                'password.required' => 'Password harus diisi'
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $credentials = [
                'username' => $params['username'],
                'password' => $params['password']
            ];

            if (!$token = auth()->attempt($credentials)) {
                return ResponseHelper::errorResponse(401, 'Username atau password salah');
            }

            $userData = auth()->user();
            $responseData = [
                'user' => $userData,
                'token' => $token
            ];

            return ResponseHelper::successResponse(MessageHelper::successLogin($userData->role->name), $responseData);
        } catch (\Exception $exception) {
            dd($exception);
            Log::error('AuthRepository@aksiLoginUser: ' . $exception->getMessage() . ' | ' . $exception->getFile() . ' | ' . $exception->getLine());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiLoginUserUsingEmailPassword($params)
    {
        try {
            $validator = Validator::make($params, [
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'Email harus diisi',
                'email.email' => 'Pastikan format email benar',
                'password.required' => 'Password harus diisi'
            ]);
            
            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $credentials = [
                'email' => $params['email'],
                'password' => $params['password']
            ];

            if (!$token = auth()->attempt($credentials)) {
                return ResponseHelper::errorResponse('Email atau password salah', 401);
            }

            $userData = auth()->user();
            $responseData = [
                'user' => $userData,
                'token' => $token
            ];

            return ResponseHelper::successResponse(MessageHelper::successLogin($userData->role->name), $responseData);
        } catch (\Exception $exception) {
            Log::error('AuthRepository@aksiLoginUserUsingEmailPassword: ' . $exception->getMessage() . ' | ' . $exception->getFile() . ' | ' . $exception->getLine());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiRefreshToken($params)
    {
        try {
            $refreshToken = auth()->refresh();
            $data = [
                'token' => $refreshToken
            ];
            return ResponseHelper::successResponse(MessageHelper::successFound('refresh token'), $data);
        } catch (\Exception $exception) {
            Log::error('AuthRepository@aksiRefreshToken: ' . $exception->getMessage() . ' | ' . $exception->getFile() . ' | ' . $exception->getLine());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

}
