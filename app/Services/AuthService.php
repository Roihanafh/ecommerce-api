<?php

namespace App\Services;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;

class AuthService
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([

            'success' => true,

            'message' => 'Register success',

            'token' => $token,

            'user' => new UserResource($user),

        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = $this->guard()->attempt($credentials)) {

            return response()->json([

                'success' => false,

                'message' => 'Email atau password salah',

            ], 401);
        }

        return response()->json([

            'success' => true,

            'message' => 'Login success',

            'token' => $token,

            'user' => new UserResource($this->guard()->user()),

        ]);
    }

    public function me()
    {
        return response()->json([

            'success' => true,

            'user' => new UserResource($this->guard()->user()),

        ]);
    }

    public function logout()
    {
        $this->guard()->logout();

        return response()->json([

            'success' => true,

            'message' => 'Logout success',

        ]);
    }

    public function refresh()
    {
        return response()->json([

            'success' => true,

            'token' => $this->guard()->refresh(),

        ]);
    }

    private function guard(): JWTGuard
    {
        /** @var JWTGuard */
        return auth('api');
    }
}
