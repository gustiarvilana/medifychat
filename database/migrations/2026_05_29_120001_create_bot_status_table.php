<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_status', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_running')->default(false);
            $table->boolean('is_logged_in')->default(false);
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });

        DB::table('bot_status')->insert([
            'id' => 1,
            'is_running' => false,
            'is_logged_in' => false,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_status');
    }
};
