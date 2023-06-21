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
        Schema::create('twitter_account_data', function (Blueprint $table) {
            $table->id();
            $table->string('twitter_id');
            $table->unsignedInteger('following');
            $table->unsignedInteger('followers');
            $table->timestamps();

            $table->foreign('twitter_id')->references('twitter_id')->on('twitter_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('twitter_account_data', function (Blueprint $table){
            $table->dropForeign(['twitter_id']);
        });
        Schema::dropIfExists('twitter_account_data');
    }
};
