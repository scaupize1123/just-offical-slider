<?php

namespace Scaupize1123\JustOfficalSlider\Tests\Controllers;

use Scaupize1123\JustOfficalSlider\Tests\TestCase;
use Scaupize1123\JustOfficalSlider\Slider;
use Scaupize1123\JustOfficalSlider\SliderTranslation;
use App\Tag;
use Illuminate\Support\Str;
use JWTAuth;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class SliderControllerTest extends TestCase
{
    protected $sliderRepo;

    public function setUp()
    {
        // 一定要先呼叫，建立 Laravel Service Container 以便測試
        parent::setUp();
        $this->sliderRepo = $this->app->make('Scaupize1123\JustOfficalSlider\Interfaces\SliderRepositoryInterface');
        // 每次都要初始化資料庫
        Session::start();
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    public function test_get_slider_page() {
        $filter = [];
        $filter['size'] = 10;
        $filter['text'] = '';
        $filter['lang'] = '';
        $filter['page'] = 1;
        $filter['sort_name'] = 'created_at';
        $filter['sort_type'] = 'desc';
        $list = $this->sliderRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
        factory(Slider::class, 15)->create()->each(function($u) {
            $model = factory(SliderTranslation::class)->make([
                'slider_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $filter['lang'] = 2;
        $list = $this->sliderRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
    }

    public function test_delete_slider() {
        $filter = [];
        $filter['slider_category_id'] = null;
        $filter['size'] = 10;
        $filter['text'] = '';
        $filter['lang'] = '';
        $filter['page'] = 1;
        $filter['sort_name'] = 'created_at';
        $filter['sort_type'] = 'desc';

        $uuid = '';
        factory(Slider::class)->create()->each(function($u) use (&$uuid) {
            $uuid = $u->uuid;
            $model = factory(SliderTranslation::class)->make([
                'slider_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $slider = $this->sliderRepo->getByUUID($uuid);
        $this->assertTrue($slider->count() == 1);
        $slider = $this->sliderRepo->delete($uuid);
        $list = $this->sliderRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
    }

    public function test_update_slider() {
        $uuid = '';
        factory(Slider::class)->create()->each(function($u) use (&$uuid) {
            $uuid = $u->uuid;
            $model = factory(SliderTranslation::class)->make([
                'slider_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $slider = $this->sliderRepo->getByUUID($uuid);
        $this->assertTrue($slider->count() == 1);
        $slider = $this->sliderRepo->update([
            'uuid' => $uuid,
            'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'lang' => 1,
        ]);
        $slider = $this->sliderRepo->getByUUID($uuid);
        $this->assertTrue($slider->translation[0]->name == 'test');
    }

    public function test_create_slider() {
        $slider = $this->sliderRepo->create([
            'uuid' => Str::uuid(),
            'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'lang' => 1,
        ]);
        $this->assertTrue($slider->count() == 1);
        $slider = $this->sliderRepo->getByUUID($slider['uuid']);
        $this->assertTrue($slider->translation[0]->name == 'test');
    }

    public function test_check_slider() {
        $slider = $this->sliderRepo->create([
            'uuid' => Str::uuid(),
            'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ]);
        $this->assertTrue($slider->count() == 1);
        $this->assertTrue($this->sliderRepo->checkSlider($slider['uuid']));
    }

    public function test_check_one_lang_slider() {
        $slider = $this->sliderRepo->create([
            'uuid' => Str::uuid(),
            'name' => 'test',
            'brief' => 'test2',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ]);
        $this->assertTrue($slider->count() == 1);
        $this->assertTrue($this->sliderRepo->checkOneLangSlider($slider['uuid'], 1));
    }


}