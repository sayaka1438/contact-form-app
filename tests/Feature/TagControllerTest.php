<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーはタグ編集画面を表示できない(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('admin.tags.edit', $tag));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /** @test */
    public function 管理者はタグを作成できる(): void
    {
        $admin = User::factory()->create();
        $data = [
            'name' => 'テスト',
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.tags.store'), $data);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseHas('tags', [
            'name' => $data['name'],
        ]);
    }

    /** @test */
    public function 管理者はタグ編集画面を表示できる(): void
    {
        $admin = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.tags.edit', $tag));

        $response->assertOk();
        $response->assertViewIs('admin.tags.edit');

        $response->assertViewHas('tag', $tag);
    }

    /** @test */
    public function 管理者はタグを更新できる(): void
    {
        $admin = User::factory()->create();
        $tag = Tag::factory()->create([
            'name' => '元のタグ名',
        ]);

        $data = [
            'name' => '更新後のタグ名',
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.tags.update', $tag), $data);

        $response->assertRedirect(route('admin.contacts.index'));

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => $data['name'],
        ]);
    }

    /** @test */
    public function 管理者はタグを削除できる(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        $tag = Tag::factory()->create();

        $contact = Contact::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->actingAs($admin)
            ->delete(route('admin.tags.destroy', $tag));

        $response->assertRedirect(route('admin.contacts.index'));

        $this->assertDatabaseMissing('contact_tag', [
            'tag_id' => $tag->id,
            'contact_id' => $contact->id,
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }
}
