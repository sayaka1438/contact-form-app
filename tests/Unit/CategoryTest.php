<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function カテゴリから紐づく複数のお問い合わせを取得できること(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->count(5)->create([
            'category_id' => $category->id,
        ]);

        $contacts = $category->contacts;

        $this->assertCount(5, $contacts);
        $this->assertTrue($contacts->every(
            fn ($contact) => $contact->category_id === $category->id
        ));
    }
}
