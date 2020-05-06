<?php

namespace Scaupize1123\JustOfficalNews\Tests\Restful;

use Scaupize1123\JustOfficalNews\Tests\TestCase;
use Scaupize1123\JustOfficalNews\News;
use Scaupize1123\JustOfficalNews\NewsTranslation;
use App\Tag;
use Illuminate\Support\Str;
use JWTAuth;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class NewsControllerTest extends TestCase
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
        factory(News::class)->create()->each(function($u) {
            $model = factory(NewsTranslation::class)->make();
            $u->translation()->save($model);
        });
        if (Auth::attempt(['name' => 'admin', 'password' => '1111'])) {
            $user = Auth::user();
        }
        return JWTAuth::fromUser($user);
    }

    public function test_login() {
        $response = $this->post('http://127.0.0.1:8000/api/login', ['name' => 'admin', 'password' => '1111']);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $this->assertEquals($content['ReturnCode'], 0);
    }

    public function test_news_list_api() {
        $token = $this->getToken();
        News::insert([
            'uuid' => Str::uuid(), 
            'status' => 1,
            'news_category_id' => 1,
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10'
        ]);
        $response = $this->get('http://127.0.0.1:8000/api/news', []);
        $response->assertStatus(401);
        $response = $this->get('http://127.0.0.1:8000/api/news', ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $this->assertEquals(count($content['data']) === 0, false);
        $this->assertEquals($content['meta']['total'], 1);
    }

    public function test_fail_create_news_api() {
        $token = $this->getToken();
        $data = [
            //'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            //'lang' => 1,
            'category' => 1
        ];
        //failed request
        $response = $this->post('http://127.0.0.1:8000/api/news', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(400);
        $this->assertEquals($content['ReturnCode'], '2');
        $this->assertEquals(array_key_exists('name', $content['errorDetail']), true);
        $this->assertEquals(array_key_exists('lang', $content['errorDetail']), true);
    }

    public function test_success_create_news_api() {
        $token = $this->getToken();
        $data = [
            'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            'lang' => 1,
            'category' => 1,
            'tags' => 'a,b'
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/news', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $thisNews = News::with('translation')->where('id', $content['news']['id'])->first();
        $this->assertEquals($content['ReturnCode'], '0');
        $this->assertTrue(!empty($thisNews->translation[0]->image));
        $this->assertTrue(!empty($thisNews->translation[0]->name));
        $this->assertEquals(Tag::count(), 2);
    }

    public function test_update_news_api() {
        $token = $this->getToken();
        $data = [
            'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            'lang' => 1,
            'category' => 1,
            'tags' => 'a,b'
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/news', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $update = [
            'name' => 'test1111',
            'brief' => 'test2',
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-10',
            'lang' => 1,
            'category' => 1,
            'tags' => 'c,b,d',
        ];
        $response = $this->json('put', 'http://127.0.0.1:8000/api/news/'.$content['news']['uuid'], $update, ['Authorization' => 'Bearer '.$token]);
        $response->assertStatus(200);
        $thisNews = News::with('translation')->where('id', $content['news']['id'])->first();
        $this->assertEquals($thisNews->translation[0]->name, 'test1111');
        $this->assertEquals($content['ReturnCode'], '0');
        $this->assertEquals(Tag::count(), 3);
    }

    public function test_delete_news_api() {
        $token = $this->getToken();
        $data = [
            'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            'lang' => 1,
            'category' => 1,
            'tags' => 'a,b'
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/news', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $this->assertEquals(Tag::count(), 2);
        $response = $this->json('delete', 'http://127.0.0.1:8000/api/news/'.$content['news']['uuid'], [], ['Authorization' => 'Bearer '.$token]);
        $response->assertStatus(200);
        $thisNews = News::with('translation')->where('id', $content['news']['id'])->where('status', '1')->first();
        $this->assertEquals($content['ReturnCode'], '0');
        $this->assertTrue(empty($thisNews));
        $this->assertEquals(Tag::count(), 0);
    }
}