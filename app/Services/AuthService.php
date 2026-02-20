<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected $repository;

    public function __construct(AuthRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(array $data)
    {
        $user = $this->repository->createUser($data);
        return [
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ];
    }

    public function login(string $email, string $password)
    {
        $user = $this->repository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return [
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ];
    }
}