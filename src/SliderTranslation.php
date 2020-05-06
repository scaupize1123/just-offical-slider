<?php

namespace Scaupize1123\JustOfficalSlider;

use Illuminate\Database\Eloquent\Model;

class SliderTranslation extends Model
{
    protected $fillable = [ 'name',
                            'brief',
                            'language_id',
                            'status',
                            'image_name',
                            'image',
                            'slider_id'];

    protected $table = 'slider_translation';

    public function language() {
        return $this->hasOne('App\Language', 'id', 'language_id');
    }
}
