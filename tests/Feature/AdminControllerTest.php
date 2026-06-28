<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未ログインユーザーは管理画面にアクセスできない(): void
    {
        $response = $this->get(route('admin.contacts.index'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /** @test */
    public function 管理者は管理画面でお問い合わせ一覧を表示できる(): void
    {
        $admin = User::factory()->create();

        $category = Category::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag = Tag::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));

        $response->assertOk();
        $response->assertViewIs('admin.index');

        $response->assertViewHas('contacts');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');

        $response->assertSee($contact->email);
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    /** @test */
    public function 一覧画面はページネーションされている(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        Contact::factory()->count(10)->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.index'));

        $response->assertOk();

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 7
                && $contacts->total() === 10;
        });
    }

    /** @test */
    public function 管理者はキーワード検索ができる(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        $hitContact = Contact::factory()->create([
            'email' => 'hit@example.com',
        ]);

        $otherContact = Contact::factory()->create([
            'email' => 'other@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.index', ['keyword' => 'hit']));

        $response->assertOk();

        $response->assertSee($hitContact->email);
        $response->assertDontSee($otherContact->email);
    }

    /** @test */
    public function 管理者は性別で検索できる(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        $hitContact = Contact::factory()->create([
            'gender' => 1,
        ]);

        $otherContact = Contact::factory()->create([
            'gender' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.index', ['gender' => 1]));

        $response->assertOk();

        $response->assertSee($hitContact->email);
        $response->assertDontSee($otherContact->email);
    }

    /** @test */
    public function 管理者はカテゴリーで検索できる(): void
    {
        $admin = User::factory()->create();

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $hitContact = Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        $otherContact = Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.index', ['category_id' => $category1->id]));

        $response->assertOk();

        $response->assertSee($hitContact->email);
        $response->assertDontSee($otherContact->email);
    }

    /** @test */
    public function 管理者は日付で検索できる(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        $hitContact = Contact::factory()->create([
            'created_at' => '2026-06-25 10:00:00',
        ]);

        $otherContact = Contact::factory()->create([
            'created_at' => '2026-06-26 10:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.index', ['date' => '2026-06-25']));

        $response->assertOk();

        $response->assertSee($hitContact->email);
        $response->assertDontSee($otherContact->email);
    }

    /** @test */
    public function 管理者はお問い合わせ詳細を表示できる(): void
    {
        $admin = User::factory()->create();

        $category = Category::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag = Tag::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.show', $contact));

        $response->assertOk();
        $response->assertViewIs('admin.show');

        $response->assertViewHas('contact', $contact);

        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    /** @test */
    public function 管理者はお問い合わせを削除できる(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        $contact = Contact::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.contacts.destroy', $contact));

        $response->assertRedirect(route('admin.contacts.index'));

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
