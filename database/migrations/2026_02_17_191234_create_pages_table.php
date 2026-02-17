<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('url_id')->constrained()->onDelete('cascade');
            $table->string('title', 500)->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('content');
            $table->timestamps();
            
            $table->fullText(['title', 'content', 'meta_description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};