<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Human-friendly reason for the latest failed attempt (null when OK).
            $table->text('last_error')->nullable()->after('platforms');
            $table->timestamp('last_attempt_at')->nullable()->after('last_error');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['last_error', 'last_attempt_at']);
        });
    }
};
