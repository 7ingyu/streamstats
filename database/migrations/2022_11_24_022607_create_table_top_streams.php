<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_streams', function (Blueprint $table) {
            $table->id();
            $table->string('channel_name')->nullable();
            $table->string('stream_title')->nullable();
            $table->string('game_name')->nullable();
            $table->integer('viewers')->default(0);
            $table->dateTime('start_time')->nullable();
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
        Schema::dropIfExists('table_top_streams');
    }
};
