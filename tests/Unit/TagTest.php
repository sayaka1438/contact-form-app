<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグに紐づく複数のお問い合わせが取得できる(): void
    {
        Category::factory()->create();

        $tag = Tag::factory()->create();
        $contacts = Contact::factory()->count(5)->create();

        $tag->contacts()->sync($contacts->pluck('id'));

        $this->assertCount(5, $tag->contacts);
        $this->assertTrue($tag->contacts->contains(
            fn ($contact) => $contact->is($contacts->first())
        ));
    }
}
