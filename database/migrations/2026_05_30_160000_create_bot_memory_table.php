<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_memory', function (Blueprint $table) {
            $table->id();
            $table->string('sender', 20);
            $table->string('key_name', 50);
            $table->text('value')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['sender', 'key_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_memory');
    }
};
