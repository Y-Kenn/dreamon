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
        Schema::create('protected_followed_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('user_twitter_id');
            $table->string('protected_twitter_id');
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
        Schema::table('protected_followed_accounts', function (Blueprint $table){
            $table->dropForeign(['user_twitter_id']);
        });
        Schema::dropIfExists('protected_followed_accounts');
    }
};
