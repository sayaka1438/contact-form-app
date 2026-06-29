<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分自身と同じnameは更新できる(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'テスト',
        ]);

        $data = [
            'name' => 'テスト',
        ];

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private $tag) {}

                public function parameter($key)
                {
                    return $key === 'tag' ? $this->tag : null;
                }
            };
        });

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 他ですでに使用されているnameだとバリデーションエラーになる(): void
    {
        $tag = Tag::factory()->create([
            'name' => '質問',
        ]);

        Tag::factory()->create([
            'name' => '要望',
        ]);

        $data = [
            'name' => '要望',
        ];

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private $tag) {}

                public function parameter($key)
                {
                    return $key === 'tag' ? $this->tag : null;
                }
            };
        });

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }
}
