<?php

namespace App\Services;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;

class AuthService
{
    public function register(RegisterRequest $request): array
    {
        $user = User::create([
            'name'     => $request->string('name')->toString(),
            'email'    => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user'  => new UserResource($user),
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request): array
    {
        $credentials = $request->only('email', 'password');

        if (! $token = $this->guard()->attempt($credentials)) {
            throw new AuthenticationException('Email atau password salah');
        }

        return [
            'token' => $token,
            'user'  => new UserResource($this->guard()->user()),
        ];
    }

    public function me(): UserResource
    {
        return new UserResource($this->guard()->user());
    }

    public function logout(): void
    {
        $this->guard()->logout();
    }

    public function refresh(): string
    {
        return $this->guard()->refresh();
    }

    private function guard(): JWTGuard
    {
        /** @var JWTGuard */
        return auth('api');
    }
}
