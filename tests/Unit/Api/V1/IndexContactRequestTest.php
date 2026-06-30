<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 検索条件を受け付ける(): void
    {
        $category = Category::factory()->create();

        $data = [
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-25',
            'per_page' => 20,
            'page' => 1,
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function keywordが256文字以上だとバリデーションエラーになる(): void
    {
        $data = [
            'keyword' => str_repeat('あ', 256),
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('keyword'));
    }

    /** @test */
    public function genderが不正な値だとバリデーションエラーになる(): void
    {
        $data = [
            'gender' => 999,
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('gender'));
    }

    /** @test */
    public function 存在しないcategory_idだとバリデーションエラーになる(): void
    {
        $data = [
            'category_id' => 999,
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('category_id'));
    }

    /** @test */
    public function per_pageが0だとバリデーションエラーになる(): void
    {
        $data = [
            'per_page' => 0,
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('per_page'));
    }

    /** @test */
    public function per_pageが101以上だとバリデーションエラーになる(): void
    {
        $data = [
            'per_page' => 101,
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('per_page'));
    }

    /** @test */
    public function pageが0だとバリデーションエラーになる(): void
    {
        $data = [
            'page' => 0,
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('page'));
    }
}
