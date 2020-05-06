<?php

namespace Scaupize1123\JustOfficalNews\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsLang extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //\Log::info($this->collection);
        return [
            'name' => $this->name,
            'brief' => $this->brief,
            'language' => $this->language,
        ];
 
    }
}
