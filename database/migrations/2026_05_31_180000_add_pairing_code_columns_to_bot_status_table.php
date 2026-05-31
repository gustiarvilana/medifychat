<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->string('login_method', 20)->default('qr')->after('pid');
            $table->string('pairing_phone', 20)->nullable()->after('login_method');
            $table->string('pairing_code', 10)->nullable()->after('pairing_phone');
        });
    }

    public function down(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->dropColumn(['login_method', 'pairing_phone', 'pairing_code']);
        });
    }
};
