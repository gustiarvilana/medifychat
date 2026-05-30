<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->string('medify_api_url', 255)->nullable()->after('gemini_api_key');
            $table->string('medify_api_email', 255)->nullable()->after('medify_api_url');
            $table->string('medify_api_password', 255)->nullable()->after('medify_api_email');
        });
    }

    public function down(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->dropColumn(['medify_api_url', 'medify_api_email', 'medify_api_password']);
        });
    }
};
