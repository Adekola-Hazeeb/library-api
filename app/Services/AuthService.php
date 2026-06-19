<?php

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
      public function __construct()
    {
        // 
    }
    public function staffLogin(
        string $email,
        string $password
    ): array {
        $user = User::where('email', $email)->first();

        if (
            !$user ||
            !Hash::check($password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('staff-token')
            ->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function memberLogin(
        string $email,
        string $password
    ): array {
        $member = Member::where('email', $email)->first();

        if (
            !$member ||
            !Hash::check($password, $member->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $member->createToken('member-token')
            ->plainTextToken;

        return [
            'member' => $member,
            'token' => $token,
        ];
    }
  
}
