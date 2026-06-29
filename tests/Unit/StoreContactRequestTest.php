<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
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
    public function 不正な電話番号形式だとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ詳細',
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tel'));
    }
}
