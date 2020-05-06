<?php

namespace Scaupize1123\JustOfficalSlider\Controllers\Api;

use Storage;
use Validator;
use App\Helpers;
use App\Traits\ImageTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Scaupize1123\JustOfficalSlider\Slider;
use App\Exceptions\Api\BadRequestException;
use Scaupize1123\JustOfficalSlider\Resources\Slider as SliderResources;
use Scaupize1123\JustOfficalSlider\Interfaces\SliderRepositoryInterface;
use Scaupize1123\JustOfficalSlider\Resources\SignleSlider as SingleSliderResources;

class SliderController extends \App\Http\Controllers\Controller
{
    use ImageTrait;

    private $sliderRepo = null;

    public function __construct(SliderRepositoryInterface $sliderRepo) 
    {
        $this->sliderRepo = $sliderRepo;
    }

    public function showPage()
    {
        $filter = [];
        $filter['size'] = Input::get('size') ?? 10;
        $filter['text'] = Input::get('text') ?? '';
        $filter['lang'] = Input::get('lang') ?? '';
        $filter['page'] = Input::get('page') ?? 1;
        $filter['langSort'] = Input::get('langSort') ?? '1';

        if (Input::get('sort') === 'sort_date_desc') {
            $sort_name = 'created_at';
            $sort_type = 'desc';
        } else if (Input::get('sort') === 'sort_date_asc') {
            $sort_name = 'created_at';
            $sort_type = 'asc';
        } else if (Input::get('sort') === 'sort_title_desc') {
            $sort_name = 'slider_translation.name';
            $sort_type = 'desc';
        } else if (Input::get('sort') === 'sort_title_asc') {
            $sort_name = 'slider_translation.name';
            $sort_type = 'asc';
        } 

        $filter['sort_name'] = $sort_name ?? 'created_at';
        $filter['sort_type'] = $sort_type ?? 'desc';
        $result = $this->sliderRepo->getListPage($filter);

        return SliderResources::collection($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  request()
     * @param  \App\slider  $slider
     * @return \Illuminate\Http\Response
     */
    public function update($uuid, Request $request)
    {
        $message = [
            'name.required' => '名稱為必填',
            'brief.required' => '簡述為必填',
            'lang.required' => '語言為必填',
        ];

        $validator = Validator::make(request()->all(), [
            'lang' => 'required',
            'name' => 'required',
            'brief' => 'required',
        ],$message);

        if (!$validator->fails()) {

            $thisSlider = $this->sliderRepo->getByUUID($uuid, request()->input('lang'));
            if (empty($thisSlider)) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("slider not found");
            }
            $update = [];

            if (request()->hasFile('image')) {
                if(!empty($thisSlider->translation[0]->image)) {
                    $fileSplit = explode('/', $thisSlider->translation[0]->image);
                    $filename = $fileSplit[count($fileSplit)-1];
                    ImageTrait::deleteFile('/slider/'.$uuid.'/'.$filename);
                }
                $image = request()->file('image');
                $mimeType = $image->getMimeType();
                $filename = ImageTrait::saveFile($image, storage_path('app/public').'/slider/'.$uuid, ImageTrait::transMimeType($mimeType));
                $update['image'] = Storage::url('slider/'.$uuid.'/'.$filename);
                $update['image_name'] = $image->getClientOriginalName();
            } else {
                $update['image'] = $thisSlider->translation[0]->image ?? null;
                $update['image_name'] = $thisSlider->translation[0]->image_name ?? null;
            }

            $update['uuid'] = $uuid;
            $update['lang'] = request()->input('lang');
            $update['name'] = request()->input('name');
            $update['brief'] = request()->input('brief');
            $slider = $this->sliderRepo->update($update);
            return response()->json(['ReturnCode' => 0, 'slider' => $slider]);
            
        } else {
            throw new BadRequestException($validator->errors());
        }
    }

    public function create()
    {
        $message = [
            'name.required' => '名稱為必填',
            'brief.required' => '簡述為必填',
            'lang' => '語言為必填',
        ];
        /* Optional
            image: file
            desc: long text
            tags: text, comma separated
        */
        $validator = Validator::make(request()->all(), [
            'lang' => 'required',
            'name' => 'required',
            'brief' => 'required',
        ],$message);

        if (!$validator->fails()) {
            $create = [];
            $uuid = Str::uuid();

            if (request()->hasFile('image')) {
                $image = request()->file('image');
                $mimeType = $image->getMimeType();
                $filename = ImageTrait::saveFile($image, storage_path('app/public').'/slider/'.$uuid,
                                                 ImageTrait::transMimeType($mimeType));
                $create['image'] = Storage::url('slider/'.$uuid.'/'.$filename);
                $create['image_name'] = $image->getClientOriginalName();
            }

            $create['uuid'] = $uuid;
            $create['lang'] = request()->input('lang');
            $create['name'] = request()->input('name');
            $create['brief'] = request()->input('brief');
            $slider = $this->sliderRepo->create($create);

            return response()->json(['ReturnCode' => 0, 'slider' => $slider]);
        } else {
            throw new BadRequestException($validator->errors());
        }
    }


    public function showSingle($uuid)
    {
        $filter = [];
        $filter['lang'] = Input::get('lang') ?? null;

        $data_array = array(
            "uuid" => $uuid,
        );

        $validator = Validator::make($data_array, [
            'uuid' => 'required',
        ]);

        if (!$validator->fails()) {
            $slider = $this->sliderRepo->getByUUID($uuid, $filter['lang']);
            if(empty($slider)) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("slider not found");
            }
            return new SingleSliderResources($slider);
        } else {
            throw new BadRequestException($validator->errors());
        }
    }

    public function delete($uuid)
    {
        $data = [];
        $data['lang'] = request()->input("lang") ?? null;
        $data['uuid'] = $uuid;

        $validator = Validator::make($data, [
            'uuid' => 'required',
        ]);

        if (!$validator->fails()) {
            if ($this->sliderRepo->checkOneLangSlider($data['uuid'], $data['lang']) == 0) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("slider not found");
            }
            $this->sliderRepo->delete($data['uuid'], $data['lang']);
            
            return response()->json(['ReturnCode' => 0]);
        } else {
            throw new BadRequestException($validator->errors());
        }
    }
}