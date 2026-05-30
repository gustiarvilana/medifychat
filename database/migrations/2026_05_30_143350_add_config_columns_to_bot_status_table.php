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
        Schema::table('bot_status', function (Blueprint $table) {
            $table->string('admin_wa_number', 100)->nullable()->after('pid');
            $table->string('gemini_api_key', 255)->nullable()->after('admin_wa_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->dropColumn(['admin_wa_number', 'gemini_api_key']);
        });
    }
};
