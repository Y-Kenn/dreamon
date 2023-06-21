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
        Schema::create('followed_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('user_twitter_id');
            $table->string('target_twitter_id');
            $table->timestamp('followed_at');
            $table->timestamp('last_active_at');
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
        Schema::table('followed_accounts', function (Blueprint $table){
            $table->dropForeign(['user_twitter_id']);
        });
        Schema::dropIfExists('followed_accounts');
    }
};
