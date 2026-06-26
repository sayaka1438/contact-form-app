<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;

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
    public function first_nameが空だとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ内容',
        ];

        $response = $this->post(route('contacts.store'), $data);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function emailが空だとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => '',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ内容',
        ];

        $response = $this->post(route('contacts.store'), $data);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function detailが空だとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => '',
        ];

        $response = $this->post(route('contacts.store'), $data);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function 存在しないcategory_idだとバリデーションエラーになる(): void
    {
        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => 999,
            'detail' => 'テストお問い合わせ内容',
        ];

        $response = $this->post(route('contacts.store'), $data);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function detailは120文字まで入力できる(): void
    {
        $category = Category::factory()->create();

        $detail = str_repeat('あ', 120);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => $detail,
        ];

        $response = $this->post(route('contacts.store'), $data);

        $response->assertRedirect(route('contacts.thanks'));

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
            'detail' => $detail,
        ]);
    }

    /** @test */
    public function detailが121文字以上だとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 121),
        ];

        $response = $this->post(route('contacts.store'), $data);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHasErrors('detail');
    }
}
