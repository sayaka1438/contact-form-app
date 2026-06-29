<?php

namespace Tests\Unit;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\StoreTagRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nameが空だとバリデーションエラーになる(): void
    {
        $data = [
            'name' => '',
        ];

        $validator = Validator::make(
            $data,
            (new StoreTagRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function nameが51文字以上だとバリデーションエラーになる(): void
    {
        $data = [
            'name' => str_repeat('あ', 51),
        ];

        $validator = Validator::make(
            $data,
            (new StoreTagRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function 存在するnameだとバリデーションエラーになる(): void
    {
        Tag::factory()->create([
            'name' => 'テスト',
        ]);

        $data = [
            'name' => 'テスト',
        ];

        $validator = Validator::make(
            $data,
            (new StoreTagRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }
}
