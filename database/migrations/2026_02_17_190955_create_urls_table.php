<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048)->unique();
            $table->string('domain');
            $table->enum('status', ['pending', 'crawled', 'failed'])->default('pending');
            $table->timestamp('last_crawled_at')->nullable();
            $table->integer('crawl_count')->default(0);
            $table->timestamps();
            
            $table->index('status');
            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};