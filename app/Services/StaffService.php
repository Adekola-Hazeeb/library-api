<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class StaffService
{
    public function getAllStaff(array $filters): LengthAwarePaginator
    {
        return User::query()
            ->when(isset($filters['role']), fn($q) => $q->where('role', $filters['role']))
            ->when(isset($filters['name']), fn($q) => $q->where('name', 'like', '%' . $filters['name'] . '%'))
            ->paginate(15);
    }
    public function createStaff(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);
    }
    public function updateStaff(User $user, array $data): User
    {
        $user->update(array_filter([
            'name'  => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'role'  => $data['role'] ?? null,
        ], fn($v) => !is_null($v)));

        return $user->fresh();
    }
    public function deleteStaff(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }
}