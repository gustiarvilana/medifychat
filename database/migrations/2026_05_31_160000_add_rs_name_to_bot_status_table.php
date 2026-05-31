<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->string('rs_name', 255)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('bot_status', function (Blueprint $table) {
            $table->dropColumn('rs_name');
        });
    }
};
