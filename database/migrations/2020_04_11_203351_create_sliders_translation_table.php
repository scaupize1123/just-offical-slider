<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlidersTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sliders_translation', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('brief');
            $table->integer('language_id');
            $table->text('image')->nullable();
            $table->text('image_name')->nullable();
            $table->integer('slider_id');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sliders_translation');
    }
}
