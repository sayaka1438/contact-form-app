<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせフォーム画面を表示できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->get(route('contacts.index'));

        $response->assertOk();
        $response->assertViewIs('contact.index');

        $response->assertViewHas('categories');
        $response->assertViewHas('tags');

        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    /** @test */
    public function 確認画面を表示できる(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'tag_ids' => $tags->pluck('id')->toArray(),
            'detail' => 'テストお問い合わせ内容',
        ];

        $response = $this->post(route('contacts.confirm'), $data);

        $response->assertOk();
        $response->assertViewIs('contact.confirm');

        $response->assertSee('太郎');
        $response->assertSee('山田');
        $response->assertSee('男性');
        $response->assertSee($category->content);
        $response->assertSee('test@example.com');
        $response->assertSee('テストお問い合わせ内容');

        foreach ($tags as $tag) {
            $response->assertSee($tag->name);
        }
    }

    /** @test */
    public function お問い合わせを保存できる(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'tag_ids' => $tags->pluck('id')->toArray(),
            'detail' => 'テストお問い合わせ内容',
        ];

        $response = $this->post(route('contacts.store'), $data);

        $response->assertRedirect(route('contacts.thanks'));

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ内容',
        ]);

        $contact = Contact::where('email', 'test@example.com')->firstOrFail();

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    /** @test */
    public function サンクス画面を表示できる(): void
    {
        $response = $this->get(route('contacts.thanks'));

        $response->assertOk();
        $response->assertViewIs('contact.thanks');
    }

    /** @test */
    public function ログイン済み管理者はフィルタ条件付きで_cs_vをダウンロードできる(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        $male = Contact::factory()->create([
            'gender' => 1,
        ]);

        $female = Contact::factory()->create([
            'gender' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('contacts.export', [
                'gender' => 1,
            ]));

        $response->assertOk();
        $response->assertHeader(
            'content-disposition',
            'attachment; filename=contacts.csv'
        );

        $content = $response->streamedContent();

        $this->assertStringContainsString($male->email, $content);
        $this->assertStringNotContainsString($female->email, $content);
    }

    /** @test */
    public function ログイン済み管理者はフィルタ未指定で新着順の_cs_vをダウンロードできる(): void
    {
        $admin = User::factory()->create();
        Category::factory()->create();

        $old = Contact::factory()->create([
            'email' => 'old@example.com',
            'created_at' => '2025-06-30 10:00:00',
        ]);

        $new = Contact::factory()->create([
            'email' => 'new@example.com',
            'created_at' => '2026-06-30 10:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('contacts.export'));

        $response->assertOk();
        $response->assertHeader(
            'content-disposition',
            'attachment; filename=contacts.csv'
        );

        $content = $response->streamedContent();

        $this->assertStringContainsString($old->email, $content);
        $this->assertStringContainsString($new->email, $content);

        $this->assertLessThan(
            strpos($content, $old->email),
            strpos($content, $new->email)
        );
    }
}
