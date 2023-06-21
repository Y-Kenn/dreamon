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
        Schema::create('follow_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_base_id');
            $table->string('target_twitter_id');
            $table->timestamp('thrown_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('target_base_id')->references('id')->on('target_base_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::table('follow_targets', function (Blueprint $table){
            $table->dropForeign(['target_base_id']);
        });
        Schema::dropIfExists('follow_targets');
    }
};
