<?php

use Faker\Generator as Faker;

$factory->define(Scaupize1123\JustOfficalSlider\SliderTranslation::class, function (Faker $faker) {
    return [
       'name' => $faker->text($maxNbChars = 200),
       'brief' => $faker->text($maxNbChars = 200),
       'language_id' => 1,
       'slider_id' => 1,
       'status' => 1,
    ];
});
