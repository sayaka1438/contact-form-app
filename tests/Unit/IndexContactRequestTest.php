<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
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
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function genderが不正な値だとバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $data = [
            'keyword' => 'テスト',
            'gender' => 999,
            'category_id' => $category->id,
            'date' => '2026-06-25',
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('gender'));
    }
}
