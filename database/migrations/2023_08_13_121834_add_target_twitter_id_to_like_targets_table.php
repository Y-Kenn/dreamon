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
        Schema::table('like_targets', function (Blueprint $table) {
            $table->string('target_twitter_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('like_targets', function (Blueprint $table) {
            $table->dropColumn('target_twitter_id');
        });
    }
};
