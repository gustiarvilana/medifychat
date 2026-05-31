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
            $table->string('admin_wa_number', 100)->nullable()->after('rs_name');
            $table->boolean('quota_exhausted')->default(false)->after('medify_api_password');
            $table->boolean('quota_notified')->default(false)->after('quota_exhausted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->dropColumn(['admin_wa_number', 'quota_exhausted', 'quota_notified']);
        });
    }
};
