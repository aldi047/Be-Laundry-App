<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users.
     */
    public function readAll(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->userRepository->aksiReadAll($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Get user profile.
     */
    public function profile(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->userRepository->aksiProfile($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $params = $request->only(['username', 'email']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->userRepository->aksiUpdateProfile($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $params = $request->only(['current_password', 'new_password', 'new_password_confirmation']);
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->userRepository->aksiChangePassword($params);
        return response()->json($response, $response['code']);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $params = [];
        $params = array_merge($params, $this->getDefaultParameter($request));
        $response = $this->userRepository->aksiLogout($params);
        return response()->json($response, $response['code']);
    }
}