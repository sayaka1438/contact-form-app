<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせ一覧を取得できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $contacts->each(fn ($contact) => $contact->tags()->attach($tag->id));

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category' => [
                        'id',
                        'content',
                    ],
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
                    'tags' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /** @test */
    public function 検索条件を指定してお問い合わせ一覧を取得できる(): void
    {
        Category::factory()->create();

        $male = Contact::factory()->create([
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'gender' => 2,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'email' => $male->email,
        ]);
    }

    /** @test */
    public function お問い合わせ一覧はページネーションできる(): void
    {
        Category::factory()->create();
        Contact::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/contacts?per_page=10');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');

        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', 25);
    }

    /** @test */
    public function 不正な検索条件だと422エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?per_page=101');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('per_page');
    }

    /** @test */
    public function お問い合わせ詳細を取得できる(): void
    {
        Category::factory()->create();

        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'email' => $contact->email,
        ]);
        $response->assertJsonPath('data.id', $contact->id);
    }

    /** @test */
    public function 存在しないお問い合わせだと404エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts/999');

        $response->assertNotFound();
    }

    /** @test */
    public function お問い合わせを作成できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ詳細',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);

        $contact = Contact::where('email', 'test@example.com')->first();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    /** @test */
    public function お問い合わせ作成時にバリデーションエラーだと422が返る(): void
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    /** @test */
    public function お問い合わせを更新できる(): void
    {
        Category::factory()->create();

        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $contact->category_id,
            'detail' => '更新後の内容',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertOk();
        $response->assertJsonFragment([
            'detail' => '更新後の内容',
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'detail' => '更新後の内容',
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    /** @test */
    public function 存在しないお問い合わせは更新できない(): void
    {
        $response = $this->putJson('/api/v1/contacts/999', []);

        $response->assertNotFound();
    }

    /** @test */
    public function お問い合わせ更新時にバリデーションエラーだと422が返る(): void
    {
        Category::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    /** @test */
    public function お問い合わせを削除できる(): void
    {
        Category::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    /** @test */
    public function 存在しないお問い合わせは削除できない(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/999');

        $response->assertNotFound();
    }
}
