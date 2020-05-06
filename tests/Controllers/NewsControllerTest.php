<?php

namespace Scaupize1123\JustOfficalNews\Tests\Controllers;

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
    protected $newsRepo;

    public function setUp()
    {
        // 一定要先呼叫，建立 Laravel Service Container 以便測試
        parent::setUp();
        $this->newsRepo = $this->app->make('Scaupize1123\JustOfficalNews\Interfaces\NewsRepositoryInterface');
        // 每次都要初始化資料庫
        Session::start();
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    public function test_get_news_page() {
        $filter = [];
        $filter['news_category_id'] = null;
        $filter['size'] = 10;
        $filter['text'] = '';
        $filter['lang'] = '';
        $filter['page'] = 1;
        $filter['langSort'] = '1';
        $filter['sort_name'] = 'created_at';
        $filter['sort_type'] = 'desc';
        $list = $this->newsRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
        factory(News::class, 15)->create()->each(function($u) {
            $model = factory(NewsTranslation::class)->make([
                'news_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $filter['lang'] = 2;
        $list = $this->newsRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
    }

    public function test_delete_news() {
        $filter = [];
        $filter['news_category_id'] = null;
        $filter['size'] = 10;
        $filter['text'] = '';
        $filter['lang'] = '';
        $filter['page'] = 1;
        $filter['langSort'] = '1';
        $filter['sort_name'] = 'created_at';
        $filter['sort_type'] = 'desc';

        $uuid = '';
        factory(News::class)->create()->each(function($u) use (&$uuid) {
            $uuid = $u->uuid;
            $model = factory(NewsTranslation::class)->make([
                'news_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $news = $this->newsRepo->getByUUID($uuid);
        $this->assertTrue($news->count() == 1);
        $news = $this->newsRepo->delete($uuid);
        $list = $this->newsRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
    }

    public function test_update_news() {
        $uuid = '';
        factory(News::class)->create()->each(function($u) use (&$uuid) {
            $uuid = $u->uuid;
            $model = factory(NewsTranslation::class)->make([
                'news_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $news = $this->newsRepo->getByUUID($uuid);
        $this->assertTrue($news->count() == 1);
        $news = $this->newsRepo->update([
            'uuid' => $uuid,
            'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            'lang' => 1,
            'category' => 1
        ]);
        $news = $this->newsRepo->getByUUID($uuid);
        $this->assertTrue($news->translation[0]->name == 'test');
    }

    public function test_create_news() {
        $news = $this->newsRepo->create([
            'uuid' => Str::uuid(),
            'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            'lang' => 1,
            'category' => 1
        ]);
        $this->assertTrue($news->count() == 1);
        $news = $this->newsRepo->getByUUID($news['uuid']);
        $this->assertTrue($news->translation[0]->name == 'test');
    }

    public function test_check_news() {
        $news = $this->newsRepo->create([
            'uuid' => Str::uuid(),
            'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            'lang' => 1,
            'category' => 1
        ]);
        $this->assertTrue($news->count() == 1);
        $this->assertTrue($this->newsRepo->checkNews($news['uuid']));
    }

    public function test_check_one_lang_news() {
        $news = $this->newsRepo->create([
            'uuid' => Str::uuid(),
            'name' => 'test',
            'brief' => 'test2',
            'desc' => 'test3',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            'start_date' => '2020-10-10',
            'end_date' => '2022-10-10',
            'lang' => 1,
            'category' => 1
        ]);
        $this->assertTrue($news->count() == 1);
        $this->assertTrue($this->newsRepo->checkOneLangNews($news['uuid'], 1));
    }


}