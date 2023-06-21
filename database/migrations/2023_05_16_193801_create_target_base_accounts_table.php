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
        Schema::create('target_base_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('user_twitter_id');
            $table->string('base_twitter_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
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
        Schema::table('target_base_accounts', function (Blueprint $table){
            $table->dropForeign(['user_twitter_id']);
        });
        Schema::dropIfExists('target_base_accounts');
    }
};
