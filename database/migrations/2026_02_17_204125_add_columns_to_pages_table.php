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
        Schema::table('pages', function (Blueprint $table) {
            $table->string('h1', 500)->nullable();
            $table->text('h2s')->nullable(); // JSON array
            $table->text('open_graph')->nullable(); // JSON
            $table->text('structured_data')->nullable(); // JSON-LD
            $table->integer('word_count')->default(0);
            $table->float('quality_score')->nullable(); // 0-1
            $table->string('canonical', 2048)->nullable();
            $table->text('keywords')->nullable(); // Extracted keywords
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            //
        });
    }
};
