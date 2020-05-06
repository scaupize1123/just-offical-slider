<?php 

Route::group(['prefix' => 'api','middleware' => ['jwt.auth']], function () {
 
    Route::get('/slider', 'Scaupize1123\JustOfficalSlider\Controllers\Api\SliderController@showPage')->name('slider.showPage');
    Route::get('/slider/{uuid}', 'Scaupize1123\JustOfficalSlider\Controllers\Api\SliderController@showSingle')->name('slider.showSingle');
    Route::delete('/slider/{uuid}', 'Scaupize1123\JustOfficalSlider\Controllers\Api\SliderController@delete')->name('slider.delete');
    Route::post('/slider', 'Scaupize1123\JustOfficalSlider\Controllers\Api\SliderController@create')->name('slider.create');
    Route::put('/slider/{uuid}', 'Scaupize1123\JustOfficalSlider\Controllers\Api\SliderController@update')->name('slider.update');      
});