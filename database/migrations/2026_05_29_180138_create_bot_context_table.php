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
        Schema::create('bot_context', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('type', 20);
            $table->string('file_path', 500)->nullable();
            $table->integer('file_size')->nullable()->unsigned();
            $table->longText('content')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('tags', 255)->nullable();
            $table->string('status', 20)->default('pending');
            $table->tinyInteger('progress')->default(0)->unsigned();
            $table->text('error_message')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_context');
    }
};
