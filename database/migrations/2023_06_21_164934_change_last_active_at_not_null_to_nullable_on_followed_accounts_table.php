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
        Schema::table('followed_accounts', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('followed_accounts', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable(false)->change();
        });
    }
};
