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
        Schema::create('reserved_tweets', function (Blueprint $table) {
            $table->id();
            $table->string('twitter_id');
            $table->text('text');
            $table->datetime('reserved_date');
            $table->timestamp('thrown_at')->nullable();
            $table->timestamp('tweeted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('twitter_id')->references('twitter_id')->on('twitter_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reserved_tweets', function (Blueprint $table){
            $table->dropForeign(['twitter_id']);
        });
        Schema::dropIfExists('reserved_tweets');
    }
};
