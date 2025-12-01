<?php

namespace App\Http\Controllers;

use App\Repositories\AuthRepository;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $params = [
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'role_id' => $request->input('role_id', 2),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
        ];
        $params = array_merge($params, $this->getDefaultParameter($request));

        $result = (new AuthRepository)->aksiRegisterUser($params);
        return response()->json($result, $result['code']);
    }

    public function login(Request $request)
    {
        $params = [
            'username' => trim($request->input('username')),
            'password' => $request->input('password')
        ];
        $params = array_merge($params, $this->getDefaultParameter($request));

        $result = (new AuthRepository)->aksiLoginUser($params);
        return response()->json($result, $result['code']);
    }

    public function loginUsingEmailPassword(Request $request)
    {
        $params = [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ];
        $params = array_merge($params, $this->getDefaultParameter($request));

        $result = (new AuthRepository)->aksiLoginUserUsingEmailPassword($params);
        return response()->json($result, $result['code']);
    }

    public function refresh(Request $request)
    {
        $params = $this->getDefaultParameter($request);
        $result = (new AuthRepository)->aksiRefreshToken($params);
        return response()->json($result, $result['code']);
    }
}
