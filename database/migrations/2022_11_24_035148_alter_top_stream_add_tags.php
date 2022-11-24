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
        Schema::table('top_streams', function (Blueprint $table) {
            $table->string('stream_id')->after('id')->index()->nullable();
            $table->text('tags')->after('viewers')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('top_streams', function (Blueprint $table) {
            $table->dropColumn('tags');
            $table->dropColumn('stream_id');
        });
    }
};
