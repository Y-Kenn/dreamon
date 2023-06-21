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
        Schema::create('like_targets', function (Blueprint $table) {
            $table->id();
            $table->string('user_twitter_id');
            $table->string('target_tweet_id');
            $table->timestamp('thrown_at')->nullable();
            $table->timestamp('liked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_twitter_id')->references('twitter_id')->on('twitter_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('like_targets', function (Blueprint $table){
            $table->dropForeign(['user_twitter_id']);
        });
        Schema::dropIfExists('like_targets');
    }
};
