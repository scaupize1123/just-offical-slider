<?php

namespace Scaupize1123\JustOfficalSlider\Tests\Restful;

use Scaupize1123\JustOfficalSlider\Tests\TestCase;
use Scaupize1123\JustOfficalSlider\Slider;
use Scaupize1123\JustOfficalSlider\SliderTranslation;
use Illuminate\Support\Str;
use JWTAuth;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class SliderControllerTest extends TestCase
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
        factory(Slider::class)->create()->each(function($u) {
            $model = factory(SliderTranslation::class)->make();
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

    public function test_slider_list_api() {
        $token = $this->getToken();
        Slider::insert([
            'uuid' => Str::uuid(), 
            'status' => 1,
        ]);
        $response = $this->get('http://127.0.0.1:8000/api/slider', []);
        $response->assertStatus(401);
        $response = $this->get('http://127.0.0.1:8000/api/slider', ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $this->assertEquals(count($content['data']) === 0, false);
        $this->assertEquals($content['meta']['total'], 1);
    }

    public function test_fail_create_slider_api() {
        $token = $this->getToken();
        $data = [
            //'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            //'lang' => 1,
        ];
        //failed request
        $response = $this->post('http://127.0.0.1:8000/api/slider', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(400);
        $this->assertEquals($content['ReturnCode'], '2');
        $this->assertEquals(array_key_exists('name', $content['errorDetail']), true);
        $this->assertEquals(array_key_exists('lang', $content['errorDetail']), true);
    }

    public function test_success_create_slider_api() {
        $token = $this->getToken();
        $data = [
            'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'lang' => 1,
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/slider', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $thisSlider = Slider::with('translation')->where('id', $content['slider']['id'])->first();
        $this->assertEquals($content['ReturnCode'], '0');
        $this->assertTrue(!empty($thisSlider->translation[0]->image));
        $this->assertTrue(!empty($thisSlider->translation[0]->name));
    }

    public function test_update_slider_api() {
        $token = $this->getToken();
        $data = [
            'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'lang' => 1,
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/slider', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $update = [
            'name' => 'test1111',
            'brief' => 'test2',
            'lang' => 1,
        ];
        $response = $this->json('put', 'http://127.0.0.1:8000/api/slider/'.$content['slider']['uuid'], $update, ['Authorization' => 'Bearer '.$token]);
        $response->assertStatus(200);
        $thisSlider = Slider::with('translation')->where('id', $content['slider']['id'])->first();
        $this->assertEquals($thisSlider->translation[0]->name, 'test1111');
        $this->assertEquals($content['ReturnCode'], '0');
    }

    public function test_delete_slider_api() {
        $token = $this->getToken();
        $data = [
            'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'lang' => 1,
        ];
        $response = $this->json('post', 'http://127.0.0.1:8000/api/slider', $data, ['Authorization' => 'Bearer '.$token]);
        $content = $response->decodeResponseJson();
        $response->assertStatus(200);
        $response = $this->json('delete', 'http://127.0.0.1:8000/api/slider/'.$content['slider']['uuid'], [], ['Authorization' => 'Bearer '.$token]);
        $response->assertStatus(200);
        $thisSlider = Slider::with('translation')->where('id', $content['slider']['id'])->where('status', '1')->first();
        $this->assertEquals($content['ReturnCode'], '0');
        $this->assertTrue(empty($thisSlider));
    }
}