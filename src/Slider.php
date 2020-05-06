<?php

namespace Scaupize1123\JustOfficalSlider;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'sliders';

    protected $fillable = ['uuid', 'status'];

    public function translation() {
        return $this->hasMany('Scaupize1123\JustOfficalSlider\SliderTranslation', 'slider_id', 'id');
    }
}
