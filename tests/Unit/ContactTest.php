<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせが特定のカテゴリに属していること(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertTrue($contact->category->is($category));
    }

    /** @test */
    public function お問い合わせに複数のタグを紐付けられる(): void
    {
        Category::factory()->create();

        $contact = Contact::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $contact->tags()->sync($tags->pluck('id'));

        $this->assertCount(3, $contact->tags);
        $this->assertTrue($contact->tags->contains(
            fn ($tag) => $tag->is($tags->first())
        ));
    }
}
