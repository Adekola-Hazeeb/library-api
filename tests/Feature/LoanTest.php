<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MemberTier;
use App\Models\User;

/* Staff auth helper */
function loanStaffAuth(): array
{
    $user  = User::factory()->create(['role' => 'librarian']);
    $token = $user->createToken('staff-token')->plainTextToken;
    return ['user' => $user, 'headers' => ['Authorization' => "Bearer {$token}"]];
}

/* Member auth helper */
function loanMemberAuth(?MemberTier $tier = null): array
{
    $tier   = $tier ?? MemberTier::factory()->create([
        'max_books'        => 3,
        'loan_period_days' => 14,
        'fine_rate'        => 50,
    ]);
    $member = Member::factory()->create([
        'member_tier_id' => $tier->id,
        'status'         => 'active',
    ]);
    $token  = $member->createToken('member-token')->plainTextToken;
    return ['member' => $member, 'headers' => ['Authorization' => "Bearer {$token}"]];
}

/* Book with available copy helper */
function loanBook(): array
{
    $book = Book::factory()->create(['is_retired' => false]);
    $copy = BookCopy::factory()->create([
        'book_id'   => $book->id,
        'status'    => 'available',
        'condition' => 'good',
    ]);
    return ['book' => $book, 'copy' => $copy];
}

/* ==================== LOAN CREATION TESTS ==================== */

it('member can borrow an available book', function () {
    $auth = loanMemberAuth();
    $data = loanBook();

    $response = $this->postJson('/api/loans', [
        'book_id' => $data['book']->id,
    ], $auth['headers']);

    $response->assertStatus(201)
             ->assertJsonPath('data.status', 'active')
             ->assertJsonStructure([
                 'data' => [
                     'id', 'status', 'borrowed_at', 'due_date',
                     'renewals_count', 'fines_accrued', 'is_overdue',
                 ],
             ]);

    /* Copy must now be marked as borrowed */
    $this->assertDatabaseHas('book_copies', [
        'id'     => $data['copy']->id,
        'status' => 'borrowed',
    ]);
});

it('guest cannot borrow a book', function () {
    $data = loanBook();

    $response = $this->postJson('/api/loans', [
        'book_id' => $data['book']->id,
    ]);

    $response->assertStatus(401);
});

it('member cannot borrow a retired book', function () {
    $auth = loanMemberAuth();
    $book = Book::factory()->create(['is_retired' => true]);

    $response = $this->postJson('/api/loans', [
        'book_id' => $book->id,
    ], $auth['headers']);

    $response->assertStatus(422);
});

it('member cannot borrow when suspended', function () {
    $tier   = MemberTier::factory()->create(['max_books' => 3, 'loan_period_days' => 14, 'fine_rate' => 50]);
    $member = Member::factory()->create(['member_tier_id' => $tier->id, 'status' => 'suspended']);
    $token  = $member->createToken('token')->plainTextToken;
    $data   = loanBook();

    $response = $this->postJson('/api/loans', [
        'book_id' => $data['book']->id,
    ], ['Authorization' => "Bearer {$token}"]);

    $response->assertStatus(403);
});

it('member cannot borrow when they have unpaid fines', function () {
    $auth = loanMemberAuth();
    $data = loanBook();

    /* Create an unpaid fine for this member */
    Fine::factory()->create([
        'member_id' => $auth['member']->id,
        'amount'    => 500,
        'is_paid'   => false,
    ]);

    $response = $this->postJson('/api/loans', [
        'book_id' => $data['book']->id,
    ], $auth['headers']);

    $response->assertStatus(403);
});

it('member cannot exceed tier max books', function () {
    $tier = MemberTier::factory()->create([
        'max_books'        => 1,
        'loan_period_days' => 14,
        'fine_rate'        => 50,
    ]);
    $auth = loanMemberAuth($tier);
    $data = loanBook();

    /* First borrow succeeds */
    $this->postJson('/api/loans', ['book_id' => $data['book']->id], $auth['headers'])
         ->assertStatus(201);

    /* Second borrow fails — limit is 1 */
    $data2 = loanBook();
    $this->postJson('/api/loans', ['book_id' => $data2['book']->id], $auth['headers'])
         ->assertStatus(422);
});

it('member cannot borrow same book twice', function () {
    $auth = loanMemberAuth();
    $data = loanBook();

    /* First borrow */
    $this->postJson('/api/loans', ['book_id' => $data['book']->id], $auth['headers'])
         ->assertStatus(201);

    /* Add another available copy of same book */
    BookCopy::factory()->create([
        'book_id'   => $data['book']->id,
        'status'    => 'available',
        'condition' => 'good',
    ]);

    /* Second borrow of same book fails */
    $this->postJson('/api/loans', ['book_id' => $data['book']->id], $auth['headers'])
         ->assertStatus(422);
});

/* ==================== RETURN TESTS ==================== */

it('staff can return a loan', function () {
    $staff  = loanStaffAuth();
    $auth   = loanMemberAuth();
    $data   = loanBook();

    $loan = Loan::factory()->create([
        'member_id'    => $auth['member']->id,
        'book_copy_id' => $data['copy']->id,
        'borrowed_at'  => now()->subDays(5),
        'due_date'     => now()->addDays(9),
        'status'       => 'active',
    ]);

    $response = $this->patchJson("/api/loans/{$loan->id}/return", [], $staff['headers']);

    $response->assertStatus(200)
             ->assertJsonPath('data.status', 'returned');

    $this->assertDatabaseHas('book_copies', [
        'id'     => $data['copy']->id,
        'status' => 'available',
    ]);
});

it('returning overdue loan creates a fine', function () {
    $staff = loanStaffAuth();
    $auth  = loanMemberAuth();
    $data  = loanBook();

    /* Loan that is 3 days overdue */
    $loan = Loan::factory()->create([
        'member_id'    => $auth['member']->id,
        'book_copy_id' => $data['copy']->id,
        'borrowed_at'  => now()->subDays(17),
        'due_date'     => now()->subDays(3),
        'status'       => 'active',
        'returned_at'  => null,
    ]);

    $this->patchJson("/api/loans/{$loan->id}/return", [], $staff['headers'])
         ->assertStatus(200);

    /* Fine record must exist */
    $this->assertDatabaseHas('fines', [
        'member_id' => $auth['member']->id,
        'loan_id'   => $loan->id,
        'is_paid'   => false,
    ]);
});

/* ==================== RENEWAL TESTS ==================== */

it('member can renew an active loan', function () {
    $auth = loanMemberAuth();
    $data = loanBook();

    $loan = Loan::factory()->create([
        'member_id'      => $auth['member']->id,
        'book_copy_id'   => $data['copy']->id,
        'borrowed_at'    => now()->subDays(5),
        'due_date'       => now()->addDays(9),
        'status'         => 'active',
        'renewals_count' => 0,
    ]);

    $response = $this->patchJson("/api/loans/{$loan->id}/renew", [], $auth['headers']);

    $response->assertStatus(200)
             ->assertJsonPath('data.renewals_count', 1);
});

it('member cannot renew beyond max renewals', function () {
    $auth = loanMemberAuth();
    $data = loanBook();

    $loan = Loan::factory()->create([
        'member_id'      => $auth['member']->id,
        'book_copy_id'   => $data['copy']->id,
        'borrowed_at'    => now()->subDays(5),
        'due_date'       => now()->addDays(9),
        'status'         => 'active',
        'renewals_count' => 2, /* Already at max */
    ]);

    $response = $this->patchJson("/api/loans/{$loan->id}/renew", [], $auth['headers']);

    $response->assertStatus(422);
});