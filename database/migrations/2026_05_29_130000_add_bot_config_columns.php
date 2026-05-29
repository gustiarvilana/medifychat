<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->integer('port')->nullable()->after('last_activity');
            $table->text('qr_code')->nullable()->after('port');
            $table->integer('pid')->nullable()->after('qr_code');
        });
    }

    public function down(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->dropColumn(['port', 'qr_code', 'pid']);
        });
    }
};
