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
        Schema::create('unfollow_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('followed_accounts_id');
            $table->string('user_twitter_id');
            $table->string('target_twitter_id');
            $table->timestamp('thrown_at')->nullable();
            $table->timestamp('unfollowed_at')->nullable();
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
        Schema::table('unfollow_targets', function (Blueprint $table){
            $table->dropForeign(['user_twitter_id']);
        });
        Schema::dropIfExists('unfollow_targets');
    }
};
