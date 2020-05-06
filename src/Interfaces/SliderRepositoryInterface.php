<?php

namespace Scaupize1123\JustOfficalSlider\Interfaces;

interface SliderRepositoryInterface
{
    public function getListPage($filter);

    public function delete($uuid, $lang = null);

    public function create($create);

    public function update($update);

    public function getByUUID($uuid, $lang = null);

    //check lang slider exist
    public function checkOneLangSlider($uuid, $lang);

    public function checkSlider($uuid);
}
