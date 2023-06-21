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
        Schema::create('twitter_accounts', function (Blueprint $table) {
            $table->string('twitter_id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->boolean('active_flag')->default(true);
            $table->string('access_token');
            $table->string('refresh_token');
            $table->timestamp('token_generated_time');
            $table->boolean('following_flag')->default(false);
            $table->boolean('unfollowing_flag')->default(false);;
            $table->boolean('liking_flag')->default(false);;
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::table('twitter_accounts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('twitter_accounts');
    }
};
