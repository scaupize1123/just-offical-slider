<?php

namespace Scaupize1123\JustOfficalSlider\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SignleSlider extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $tags = [];
        $this->tags->each(function($value) use (&$tags) {
            $tags[] = $value->name;
        });

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'translation' => $this->translation,
        ];
 
    }
}
