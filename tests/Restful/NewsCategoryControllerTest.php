<?php

namespace Scaupize1123\JustOfficalNews\Tests\Restful;

use Scaupize1123\JustOfficalNews\Tests\TestCase;
use Scaupize1123\JustOfficalNews\NewsCategory;
use Scaupize1123\JustOfficalNews\NewsCategoryTranslation;
use App\Tag;
use Illuminate\Support\Str;
use JWTAuth;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class NewsCategoryControllerTest extends TestCase
{

    public function setUp()
    {
        // 一定要先呼叫，建立 Laravel Service Container 以便測試
        parent::setUp();

        // 每次都要初始化資料庫
        Session::start();
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    public function getToken() {
        if (Auth::attempt(['name' => 'admin', 'password' => '1111'])) {
            $user = Auth::user();
        }
        return JWTAuth::fromUser($user);
    }

    public function test_news_category_list_api() {
        $this->assertTrue(true);
        $token = $this->getToken();
        factory(NewsCategory::class, 15)->create()->each(function($u) {
            $model = factory(NewsCategoryTranslation::class)->make([
                'news_category_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $response = $this->get('http://127.0.0.1:8000/api/news-categories', []);
        $response->assertStatus(401);
        $response = $this->get('http://127.0.0.1:8000/api/news-categories', ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $this->assertEquals(count($content['data']) === 0, false);
        $this->assertEquals(count($content['data']), 15);

        $response = $this->get('http://127.0.0.1:8000/api/news?lang=2', ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $this->assertEquals(count($content['data']), 0);
    }

    public function test_fail_create_news_category_api() {
        $token = $this->getToken();
        $data = [
            //'name' => 'test',
        ];
        //failed request
        $response = $this->post('http://127.0.0.1:8000/api/news-categories', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(400);
        $this->assertEquals($content['ReturnCode'], '2');
        $this->assertEquals(array_key_exists('category', $content['errorDetail']), true);
    }

    public function test_success_create_news_category_api() {
        $token = $this->getToken();
        $data = [
            'category' => [
                [
                    'name' => 'test',
                    'lang' => 1
                ]
            ],
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/news-categories', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $this->assertEquals($content['ReturnCode'], '0');
    }

    public function test_update_news_category_api() {
        $token = $this->getToken();
        $data = [
            'category' => [
                [
                    'name' => 'test',
                    'lang' => 1
                ]
            ],
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/news-categories', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $update = [
            'category' => [
                [
                    'name' => 'test1',
                    'lang' => 1
                ]
            ],
        ];
        $response = $this->json('put', 'http://127.0.0.1:8000/api/news-categories/'.$content['data'][0]['id'], $update, ['Authorization' => 'Bearer '.$token]);
        $response->assertStatus(200);
    }

    public function test_delete_news_category_api() {
        $token = $this->getToken();
        $data = [
            'category' => [
                [
                    'name' => 'test',
                    'lang' => 1
                ]
            ],
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/news-categories', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $response = $this->json('delete', 'http://127.0.0.1:8000/api/news-categories/'.$content['data'][0]['id'], [], ['Authorization' => 'Bearer '.$token]);
        $response->assertStatus(200);
    }
}