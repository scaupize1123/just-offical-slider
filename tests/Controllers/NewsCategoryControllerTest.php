<?php

namespace Scaupize1123\JustOfficalNews\Tests\Controllers;

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
    protected $newsCategoryRepo;

    public function setUp()
    {
        // 一定要先呼叫，建立 Laravel Service Container 以便測試
        parent::setUp();
        $this->newsCategoryRepo = $this->app->make('Scaupize1123\JustOfficalNews\Interfaces\NewsCategoryRepositoryInterface');
        // 每次都要初始化資料庫
        Session::start();
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    public function test_get_news_category_page() {
        $filter = [];
        $filter['news_category_id'] = null;
        $filter['size'] = 10;
        $filter['text'] = '';
        $filter['lang'] = '';
        $filter['page'] = 1;
        $filter['langSort'] = '1';
        $filter['sort_name'] = 'created_at';
        $filter['sort_type'] = 'desc';
        $list = $this->newsCategoryRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
        factory(NewsCategory::class, 15)->create()->each(function($u) {
            $model = factory(NewsCategoryTranslation::class)->make([
                'news_category_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $list = $this->newsCategoryRepo->getListPage($filter);
        $this->assertTrue($list->count() == 10);
        $list = $this->newsCategoryRepo->getListPage($filter);
        $filter['lang'] = 2;
        $list = $this->newsCategoryRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
    }

    public function test_delete_news_category() {
        $filter = [];
        $filter['news_category_id'] = null;
        $filter['size'] = 10;
        $filter['text'] = '';
        $filter['lang'] = '';
        $filter['page'] = 1;
        $filter['langSort'] = '1';
        $filter['sort_name'] = 'created_at';
        $filter['sort_type'] = 'desc';

        $id = '';
        factory(NewsCategory::class)->create()->each(function($u) use (&$id) {
            $id = $u->id;
            $model = factory(NewsCategoryTranslation::class)->make([
                'news_category_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $news = $this->newsCategoryRepo->get(['lang' =>1, 'id' => $id]);
        $this->assertTrue($news->count() == 1);
        $news = $this->newsCategoryRepo->delete($id);
        $list = $this->newsCategoryRepo->getListPage($filter);
        $this->assertTrue($list->isEmpty());
    }

    public function test_update_news_category() {
        $id = '';
        factory(NewsCategory::class)->create()->each(function($u) use (&$id) {
            $id = $u->id;
            $model = factory(NewsCategoryTranslation::class)->make([
                'news_category_id' => $u->id
            ]);
            $u->translation()->save($model);
        });
        $data = [
            [
                'name' => 'test',
                'lang' => 1
            ]
        ];
        $this->newsCategoryRepo->update($data, $id);
        $newsCate = $this->newsCategoryRepo->get(['lang' =>1, 'id' =>$id]);
        $this->assertTrue($newsCate[0]->translation[0]->name == 'test');
    }

    public function test_create_news_category() {
        $data = [
            [
                'name' => 'test',
                'lang' => 1
            ]
        ];
        $newsCate = $this->newsCategoryRepo->create($data);
        $newsCate = $this->newsCategoryRepo->get(['lang' =>1, 'id' => $newsCate[0]->id]);
        $this->assertTrue($newsCate[0]->translation[0]->name == 'test');
    }
}