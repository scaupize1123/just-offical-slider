<?php

namespace Scaupize1123\JustOfficalSlider\Repositories;

use Illuminate\Support\Str;
use Scaupize1123\JustOfficalSlider\Slider;
use Scaupize1123\JustOfficalSlider\Helpers;
use Scaupize1123\JustOfficalSlider\SliderCategory;
use Scaupize1123\JustOfficalSlider\SliderTranslation;
use Scaupize1123\JustOfficalSlider\Interfaces\SliderRepositoryInterface;

class SliderRepository implements SliderRepositoryInterface
{
    public function translationExists($filter) {
        return function($q) use ($filter) {
            if(!empty($filter['lang'])){
                $q->where('language_id', $filter['lang']);
            }
            if(!empty($filter['text'])) {
                $q->where(function($query) use ($filter) {
                   $query->where('name', 'like', '%'.$filter['text'].'%');
               });
           }
           $q->where('status', 1);
           
        };
    }

    public function getTranslation($filter) {
        return function($q) use ($filter) {
            if(!empty($filter['lang'])){
                $q->where('language_id', $filter['lang']);
            }
            if(!empty($filter['text'])) {
                $q->where(function($query) use ($filter) {
                   $query->where('name', 'like', '%'.$filter['text'].'%');
               });
           }
           $q->where('status', 1);
        };
    }

    public function getListPage($filter) {
        // $query_builder = Slider::join('slider_translation','slider.id','=','slider_translation.slider_id')
        //     ->where('slider.status', 1)
        //     ->where('slider_translation.language_id', $filter['langSort'])
        //     ->orderBy($filter['sort_name'], $filter['sort_type'])
        //     ->selectRaw('DISTINCT slider.id, slider.created_at, slider.updated_at, uuid')
        //     ->WhereHas('translation', $this->translationExists($filter))
        //     ->with(['translation' => $this->getTranslation($filter),
        //         'category.translation' => function($q) use ($filter) {
        //         if(!empty($filter['lang'])){
        //             $q->where('language_id', $filter['lang']);
        //         }
        //     }, 'translation.language', 'category.translation.language']);

        $query_builder = Slider::where('status', 1)
            ->whereHas('translation', $this->translationExists($filter))
            ->with(['translation' => $this->getTranslation($filter),
                    'translation.language']);

        $sliderList = $query_builder->paginate($filter['size']);
        return $sliderList;
    }

    public function getByUUID($uuid, $lang = null) {
        $filter = ['lang' => $lang];
        $queryBuilder = Slider::where('status', 1)
            ->whereHas('translation', $this->translationExists($filter))
            ->where('uuid', $uuid)
            ->with(['translation' => function($q) use ($lang) {
                if(!empty($lang)){
                    $q->where('language_id', $lang);
                }
                $q->where('status', 1);
            },  'translation.language']);
        return $queryBuilder->first();
    }

    public function delete($uuid, $lang = null) {
        if(empty($lang)) {
            $slider = Slider::where('uuid', $uuid)->first();
            $slider->translation()->where('slider_id', $slider->id)->update(['status' => 0]);
            Slider::where('uuid', $uuid)
                ->update(['status' => 0]);
        } else {
            $slider = Slider::where('uuid', $uuid)->first();
            $slider->translation()
                ->where('language_id', $lang)
                ->update(['status' => 0]);
        }
    }

    public function checkOneLangSlider($uuid, $lang) {
        $data = Slider::where('uuid', $uuid)
            ->whereHas('translation', function($q) use ($lang) {
                if (!empty($lang)) {
                    $q->where('language_id', $lang);
                }
            })->get();

        if($data->isEmpty()) {
            return false;
        }
        return true;
    }

    public function checkSlider($uuid) {
        $isExisted =  Slider::where('uuid', $uuid)
            ->get()->count();

        if(empty($isExisted)) {
            return false;
        }
        return true;
    }

    public function update($update) {
        $slider = Slider::where('uuid', $update['uuid'])->first();

        $slider->translation()
            ->updateOrCreate([
                'slider_id' => $slider->id,
                'language_id' => $update['lang']
            ],[
                'name' => $update['name'],
                'brief' => $update['brief'],
                'image_name' => $update['image_name'] ?? null,
                'image' => $update['image'] ?? null,
                'status' => 1
            ]);
        return $slider;
    }

    public function create($create) {
        $uuid =  $create['uuid'];
        $slider = Slider::create([
            'uuid' => $uuid,
            'status' => 1,
        ]);

        $slider->translation()->create([
            'name' => $create['name'],
            'brief' => $create['brief'],
            'language_id' => $create['lang'],
            'image_name' => $create['image_name'] ?? null,
            'image' => $create['image'] ?? null,
            'status' => 1,
            'slider_id' => $slider->id
        ]);

        return $slider;
    }
}
