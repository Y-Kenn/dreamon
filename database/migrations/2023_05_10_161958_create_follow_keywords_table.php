<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('follow_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('twitter_id');
            $table->string('keywords');
            $table->boolean('not_flag');
            $table->timestamps();

            $table->foreign('twitter_id')->references('twitter_id')->on('twitter_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::table('follow_keywords', function (Blueprint $table){
            $table->dropForeign(['twitter_id']);
        });
        Schema::dropIfExists('follow_keywords');
    }
};
