<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Member;
use App\Models\MemberTier;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Author;
use App\Models\Category;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        /* ── Tiers ── */
        $regular = MemberTier::create([
            'name'            => 'Regular',
            'max_books'       => 3,
            'loan_period_days'=> 14,
            'fine_rate'       => 50,
        ]);

        $premium = MemberTier::create([
            'name'            => 'Premium',
            'max_books'       => 6,
            'loan_period_days'=> 21,
            'fine_rate'       => 50,
        ]);

        /* ── Staff ── */
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@library.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Librarian User',
            'email'    => 'librarian@library.com',
            'password' => bcrypt('password'),
            'role'     => 'librarian',
        ]);

        /* ── Members ── */
        Member::create([
            'first_name'     => 'Ade',
            'last_name'      => 'Regular',
            'email'          => 'ade@member.com',
            'password'       => bcrypt('password'),
            'phone_number'   => '08012345678',
            'member_tier_id' => $regular->id,
            'status'         => 'active',
            'joined_at'      => now(),
        ]);

        Member::create([
            'first_name'     => 'Bola',
            'last_name'      => 'Premium',
            'email'          => 'bola@member.com',
            'password'       => bcrypt('password'),
            'phone_number'   => '08087654321',
            'member_tier_id' => $premium->id,
            'status'         => 'active',
            'joined_at'      => now(),
        ]);

        /* ── Authors ── */
        $author1 = Author::create([
            'name' => 'Chinua Achebe',
            'bio'  => 'Nigerian novelist and poet.',
        ]);

        $author2 = Author::create([
            'name' => 'Wole Soyinka',
            'bio'  => 'Nigerian playwright and poet.',
        ]);

        $author3 = Author::create([
            'name' => 'Chimamanda Ngozi Adichie',
            'bio'  => 'Nigerian author of novels and short stories.',
        ]);

        /* ── Categories ── */
        $fiction    = Category::create(['name' => 'Fiction',    'description' => 'Fictional literature']);
        $nonfiction = Category::create(['name' => 'Non-Fiction','description' => 'Non-fictional works']);

        /* ── Books with copies ── */
        $book1 = Book::create([
            'title'          => 'Things Fall Apart',
            'isbn'           => '978-0-385-47454-2',
            'description'    => 'A classic Nigerian novel.',
            'published_year' => 1958,
            'is_retired'     => false,
        ]);
        $book1->authors()->attach($author1->id);
        $book1->categories()->attach($fiction->id);
        BookCopy::create(['book_id' => $book1->id, 'copy_number' => 1, 'condition' => 'good',      'status' => 'available']);
        BookCopy::create(['book_id' => $book1->id, 'copy_number' => 2, 'condition' => 'good',      'status' => 'available']);

        $book2 = Book::create([
            'title'          => 'Purple Hibiscus',
            'isbn'           => '978-1-61620-302-5',
            'description'    => 'A story of family and faith.',
            'published_year' => 2003,
            'is_retired'     => false,
        ]);
        $book2->authors()->attach($author3->id);
        $book2->categories()->attach($fiction->id);
        BookCopy::create(['book_id' => $book2->id, 'copy_number' => 1, 'condition' => 'good',      'status' => 'available']);
        BookCopy::create(['book_id' => $book2->id, 'copy_number' => 2, 'condition' => 'good',      'status' => 'available']);

        $book3 = Book::create([
            'title'          => 'You Must Set Forth at Dawn',
            'isbn'           => '978-0-375-50739-6',
            'description'    => 'Memoirs of Wole Soyinka.',
            'published_year' => 2006,
            'is_retired'     => false,
        ]);
        $book3->authors()->attach($author2->id);
        $book3->categories()->attach($nonfiction->id);
        BookCopy::create(['book_id' => $book3->id, 'copy_number' => 1, 'condition' => 'good',      'status' => 'available']);
    }
}