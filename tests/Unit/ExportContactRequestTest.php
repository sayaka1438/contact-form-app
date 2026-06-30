<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 正しい検索条件を受け付ける(): void
    {
        $category = Category::factory()->create();

        $data = [
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-30',
        ];

        $validator = Validator::make(
            $data,
            (new ExportContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function genderが不正な値だとバリデーションエラーになる(): void
    {
        $data = [
            'gender' => 999,
        ];

        $validator = Validator::make(
            $data,
            (new ExportContactRequest)->rules()
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
            (new ExportContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('category_id'));
    }
}
