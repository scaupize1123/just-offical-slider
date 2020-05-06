<?php

use Faker\Generator as Faker;

$factory->define(Scaupize1123\JustOfficalSlider\Slider::class, function (Faker $faker) {
    return [
       'uuid' => $faker->uuid(),
       'status' => 1,
    ];
});
