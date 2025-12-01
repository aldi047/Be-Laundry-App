<?php

namespace App\Repositories;

use App\Helpers\MessageHelper;
use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserRepository
{
    public function aksiReadAll($params)
    {
        try {
            $users = User::all();

            return ResponseHelper::successResponse('Data user berhasil diambil', $users);
        } catch (\Exception $exception) {
            Log::error('UserRepository@aksiReadAll: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiProfile($params)
    {
        try {
            $user = auth()->user();

            return ResponseHelper::successResponse('Data profile berhasil diambil', $user);
        } catch (\Exception $exception) {
            Log::error('UserRepository@aksiProfile: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiUpdateProfile($params)
    {
        try {
            $user = User::find($params['user_id']);

            if (!$user) {
                return ResponseHelper::errorResponse(404, 'User tidak ditemukan');
            }

            $validator = Validator::make($params, [
                'username' => 'sometimes|min:4|unique:users,username,' . $user->id,
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
            ], [
                'username.min' => 'Username minimal 4 karakter',
                'username.unique' => 'Username sudah digunakan',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $updateData = [];
            if (isset($params['username'])) {
                $updateData['username'] = $params['username'];
            }
            if (isset($params['email'])) {
                $updateData['email'] = $params['email'];
            }

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            return ResponseHelper::successResponse('Profile berhasil diupdate', $user);
        } catch (\Exception $exception) {
            Log::error('UserRepository@aksiUpdateProfile: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiChangePassword($params)
    {
        try {
            $validator = Validator::make($params, [
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ], [
                'current_password.required' => 'Password saat ini harus diisi',
                'new_password.required' => 'Password baru harus diisi',
                'new_password.min' => 'Password baru minimal 8 karakter',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first() ?? 'Terjadi kesalahan validasi!';
                return ResponseHelper::errorResponse(422, $error);
            }

            $user = User::find($params['user_id']);

            if (!$user) {
                return ResponseHelper::errorResponse(404, 'User tidak ditemukan');
            }

            if (!Hash::check($params['current_password'], $user->password)) {
                return ResponseHelper::errorResponse(400, 'Password saat ini salah');
            }

            $user->update([
                'password' => Hash::make($params['new_password']),
            ]);

            return ResponseHelper::successResponse('Password berhasil diubah', []);
        } catch (\Exception $exception) {
            Log::error('UserRepository@aksiChangePassword: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public function aksiLogout($params)
    {
        try {
            $token = $params['TOKEN'] ?? null;

            if ($token) {
                JWTAuth::setToken($token)->invalidate();
            }

            return ResponseHelper::successResponse('Berhasil logout', []);
        } catch (\Exception $exception) {
            Log::error('UserRepository@aksiLogout: ' . $exception->getMessage());
            return ResponseHelper::serverErrorResponse($exception);
        }
    }
}