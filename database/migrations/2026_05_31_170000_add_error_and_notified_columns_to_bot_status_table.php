<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->boolean('quota_notified')->default(false)->after('quota_exhausted');
            $table->text('last_error')->nullable()->after('quota_notified');
            $table->timestamp('last_error_at')->nullable()->after('last_error');
            $table->boolean('last_error_notified')->default(false)->after('last_error_at');
        });
    }

    public function down(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->dropColumn(['quota_notified', 'last_error', 'last_error_at', 'last_error_notified']);
        });
    }
};
