<?php

use App\Models\Member;
use App\Models\MemberTier;
use App\Models\User;

/* Staff Authentication Tests */

it('staff can login with valid credentials', function () {
    User::factory()->create([
        'email'    => 'librarian@library.com',
        'password' => 'password123',
        'role'     => 'librarian',
    ]);

    $response = $this->postJson('/api/staff/login', [
        'email'    => 'librarian@library.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'token',
                 'data' => ['id', 'name', 'email', 'role'],
             ]);
});

it('staff cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/staff/login', [
        'email'    => 'wrong@library.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
});

it('staff can access their profile', function () {
    $user  = User::factory()->create(['role' => 'librarian']);
    $token = $user->createToken('staff-token')->plainTextToken;

    $response = $this->getJson('/api/staff/me', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('data.email', $user->email);
});

it('staff can logout', function () {
    $user  = User::factory()->create(['role' => 'librarian']);
    $token = $user->createToken('staff-token')->plainTextToken;

    $response = $this->postJson('/api/staff/logout', [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(200);
});

/* Member Authentication Tests */

it('member can login with valid credentials', function () {
    $tier = MemberTier::factory()->create([
        'name'             => 'Regular',
        'max_books'        => 3,
        'loan_period_days' => 14,
        'fine_rate'        => 50.00,
    ]);

    Member::factory()->create([
        'email'          => 'john@example.com',
        'password'       => 'password123',
        'member_tier_id' => $tier->id,
    ]);

    $response = $this->postJson('/api/member/login', [
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'token',
                 'data' => ['id', 'first_name', 'last_name', 'email'],
             ]);
});

it('member cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/member/login', [
        'email'    => 'wrong@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
});

it('member can access their profile', function () {
    $tier   = MemberTier::factory()->create();
    $member = Member::factory()->create([
        'member_tier_id' => $tier->id,
    ]);
    $token = $member->createToken('member-token')->plainTextToken;

    $response = $this->getJson('/api/member/me', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('data.email', $member->email);
});

it('member can logout', function () {
    $tier   = MemberTier::factory()->create();
    $member = Member::factory()->create([
        'member_tier_id' => $tier->id,
    ]);
    $token = $member->createToken('member-token')->plainTextToken;

    $response = $this->postJson('/api/member/logout', [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(200);
});

it('returns 401 when accessing protected route without token', function () {
    $response = $this->getJson('/api/staff/me');

    $response->assertStatus(401);
});