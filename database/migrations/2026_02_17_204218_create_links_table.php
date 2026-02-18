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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_url_id')->constrained('urls')->onDelete('cascade');
            $table->foreignId('target_url_id')->nullable()->constrained('urls')->onDelete('set null');
            $table->string('anchor_text', 500)->nullable();
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
            
            $table->index(['source_url_id', 'target_url_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
