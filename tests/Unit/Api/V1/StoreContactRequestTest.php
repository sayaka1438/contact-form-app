<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 全ての必須項目とタグ入力を受け付ける(): void
    {
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ詳細',
            'tag_ids' => [
                $tag1->id,
                $tag2->id,
            ],
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
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
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ詳細',
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('first_name'));
    }

    /** @test */
    public function genderが不正な値だとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 999,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ詳細',
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('gender'));
    }

    /** @test */
    public function 存在しないcategory_idだとバリデーションエラーになる(): void
    {
        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => 999,
            'detail' => 'お問い合わせ詳細',
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('category_id'));
    }

    /** @test */
    public function 存在しないtag_idsだとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ詳細',
            'tag_ids' => [999],
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tag_ids.0'));
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
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 121),
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('detail'));
    }
}
