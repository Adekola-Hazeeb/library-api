<?php

use App\Models\User;
use App\Models\Author;
use App\Models\Category;
use App\Models\Book;
use App\Models\MemberTier;
use App\Models\Member;

/* Helper — creates a staff user and returns headers with Bearer token */
function staffAuth(): array
{
    $user  = User::factory()->create(['role' => 'librarian']);
    $token = $user->createToken('staff-token')->plainTextToken;

    return [
        'user'    => $user,
        'headers' => ['Authorization' => "Bearer {$token}"],
    ];
}

/* Helper — creates a member and returns headers with Bearer token */
function memberAuth(): array
{
    $tier   = MemberTier::factory()->create();
    $member = Member::factory()->create(['member_tier_id' => $tier->id]);
    $token  = $member->createToken('member-token')->plainTextToken;

    return [
        'member'  => $member,
        'headers' => ['Authorization' => "Bearer {$token}"],
    ];
}

/* Helper — creates a book with author, category and copies */
function createBook(int $copies = 2): array
{
    $author   = Author::factory()->create();
    $category = Category::factory()->create();
    $book     = Book::factory()->create();

    $book->authors()->attach($author->id);
    $book->categories()->attach($category->id);

    for ($i = 1; $i <= $copies; $i++) {
        $book->copies()->create([
            'copy_number' => $i,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
    }

    return [
        'book'     => $book,
        'author'   => $author,
        'category' => $category,
    ];
}

/* ==================== CATEGORY TESTS ==================== */

it('anyone can list categories', function () {
    Category::factory()->count(3)->create();

    $response = $this->getJson('/api/categories');

    $response->assertStatus(200)
             ->assertJsonCount(3, 'data')
             ->assertJsonStructure([
                 'data' => [['id', 'name', 'description', 'created_at']],
                 'meta' => ['current_page', 'last_page', 'per_page', 'total'],
             ]);
});

it('staff can create a category', function () {
    $auth = staffAuth();

    $response = $this->postJson('/api/categories', [
        'name'        => 'Science Fiction',
        'description' => 'Futuristic fiction',
    ], $auth['headers']);

    $response->assertStatus(201)
             ->assertJsonPath('data.name', 'Science Fiction');

    $this->assertDatabaseHas('categories', ['name' => 'Science Fiction']);
});

it('guests cannot create a category', function () {
    $response = $this->postJson('/api/categories', [
        'name' => 'Science Fiction',
    ]);

    $response->assertStatus(401);
});

it('members cannot create a category', function () {
    $auth = memberAuth();

    $response = $this->postJson('/api/categories', [
        'name' => 'Science Fiction',
    ], $auth['headers']);

    /* Member passes auth:sanctum (Sanctum accepts any HasApiTokens model)
       but fails role:librarian,admin middleware — correctly returns 403 */
    $response->assertStatus(403);
});
it('cannot create duplicate category name', function () {
    $auth = staffAuth();
    Category::factory()->create(['name' => 'Fiction']);

    $response = $this->postJson('/api/categories', [
        'name' => 'Fiction',
    ], $auth['headers']);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
});

/* ==================== AUTHOR TESTS ==================== */

it('anyone can list authors', function () {
    Author::factory()->count(3)->create();

    $response = $this->getJson('/api/authors');

    $response->assertStatus(200)
             ->assertJsonCount(3, 'data');
});

it('staff can create an author', function () {
    $auth = staffAuth();

    $response = $this->postJson('/api/authors', [
        'name' => 'Wole Soyinka',
        'bio'  => 'Nigerian playwright and poet',
    ], $auth['headers']);

    $response->assertStatus(201)
             ->assertJsonPath('data.name', 'Wole Soyinka');

    $this->assertDatabaseHas('authors', ['name' => 'Wole Soyinka']);
});

it('staff can update an author', function () {
    $auth   = staffAuth();
    $author = Author::factory()->create();

    $response = $this->patchJson("/api/authors/{$author->id}", [
        'bio' => 'Updated biography.',
    ], $auth['headers']);

    $response->assertStatus(200)
             ->assertJsonPath('data.bio', 'Updated biography.');
});

it('guests cannot create an author', function () {
    $response = $this->postJson('/api/authors', [
        'name' => 'Wole Soyinka',
    ]);

    $response->assertStatus(401);
});

/* ==================== BOOK TESTS ==================== */

it('anyone can list books', function () {
    createBook();
    createBook();

    $response = $this->getJson('/api/books');

    $response->assertStatus(200)
             ->assertJsonCount(2, 'data')
             ->assertJsonStructure([
                 'data' => [[
                     'id', 'title', 'isbn', 'is_retired',
                     'authors', 'categories',
                     'copies_count', 'available_copies_count',
                 ]],
             ]);
});

it('retired books are hidden from public list', function () {
    createBook();
    $retired = Book::factory()->create(['is_retired' => true]);

    $response = $this->getJson('/api/books');

    $response->assertStatus(200)
             ->assertJsonCount(1, 'data');
});

it('staff can see retired books', function () {
    $auth = staffAuth();
    createBook();
    Book::factory()->create(['is_retired' => true]);

    $response = $this->getJson('/api/books', $auth['headers']);

    $response->assertStatus(200)
             ->assertJsonCount(2, 'data');
});

it('anyone can view a single book', function () {
    $data = createBook();

    $response = $this->getJson("/api/books/{$data['book']->id}");

    $response->assertStatus(200)
             ->assertJsonPath('data.id', $data['book']->id)
             ->assertJsonStructure([
                 'data' => [
                     'id', 'title', 'authors', 'categories',
                     'copies_count', 'available_copies_count',
                 ],
             ]);
});

it('returns 404 for non-existent book', function () {
    $response = $this->getJson('/api/books/999');

    $response->assertStatus(404);
});

it('staff can create a book with authors categories and copies', function () {
    $auth     = staffAuth();
    $author   = Author::factory()->create();
    $category = Category::factory()->create();

    $response = $this->postJson('/api/books', [
        'title'          => 'Things Fall Apart',
        'isbn'           => '978-0-385-47454-2',
        'description'    => 'A pre-colonial story',
        'published_year' => 1958,
        'author_ids'     => [$author->id],
        'category_ids'   => [$category->id],
        'copies'         => 3,
    ], $auth['headers']);

    $response->assertStatus(201)
             ->assertJsonPath('data.title', 'Things Fall Apart')
             ->assertJsonPath('data.copies_count', 3)
             ->assertJsonPath('data.available_copies_count', 3);

    $this->assertDatabaseHas('books', ['title' => 'Things Fall Apart']);
    $this->assertDatabaseCount('book_copies', 3);
});

it('cannot create book with non-existent author', function () {
    $auth     = staffAuth();
    $category = Category::factory()->create();

    $response = $this->postJson('/api/books', [
        'title'          => 'Test Book',
        'isbn'           => '123-456',
        'published_year' => 2020,
        'author_ids'     => [999],
        'category_ids'   => [$category->id],
    ], $auth['headers']);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['author_ids.0']);
});

it('staff can update a book', function () {
    $auth = staffAuth();
    $data = createBook();

    $response = $this->patchJson("/api/books/{$data['book']->id}", [
        'description' => 'Updated description.',
    ], $auth['headers']);

    $response->assertStatus(200)
             ->assertJsonPath('data.description', 'Updated description.');
});

it('staff can retire a book with no active loans', function () {
    $auth = staffAuth();
    $data = createBook();

    $response = $this->deleteJson("/api/books/{$data['book']->id}", [], $auth['headers']);

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Book retired successfully.');

    $this->assertDatabaseHas('books', [
        'id'         => $data['book']->id,
        'is_retired' => true,
    ]);
});

it('cannot retire a book with active loans', function () {
    $auth = staffAuth();
    $data = createBook();
    $tier = MemberTier::factory()->create();
    $member = Member::factory()->create(['member_tier_id' => $tier->id]);

    /* Create an active loan against copy 1 */
    $data['book']->copies->first()->loans()->create([
        'member_id'   => $member->id,
        'borrowed_at' => now(),
        'due_date'    => now()->addDays(14),
        'status'      => 'active',
    ]);

    $response = $this->deleteJson("/api/books/{$data['book']->id}", [], $auth['headers']);

    $response->assertStatus(409);

    /* Book must still be active in database */
    $this->assertDatabaseHas('books', [
        'id'         => $data['book']->id,
        'is_retired' => false,
    ]);
});

it('guests cannot create a book', function () {
    $response = $this->postJson('/api/books', [
        'title' => 'Test',
    ]);

    $response->assertStatus(401);
});

it('can filter books by title', function () {
    $auth = staffAuth();
    createBook();
    $data = createBook();
    $data['book']->update(['title' => 'Unique Title XYZ']);

    $response = $this->getJson('/api/books?title=Unique');

    $response->assertStatus(200)
             ->assertJsonCount(1, 'data');
});