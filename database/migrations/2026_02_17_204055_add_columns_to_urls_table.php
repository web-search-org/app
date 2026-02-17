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
        Schema::table('urls', function (Blueprint $table) {
            $table->string('canonical_url', 2048)->nullable();
            $table->integer('http_status')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->integer('content_hash', 64)->nullable(); // SHA-256
            $table->string('content_type', 100)->nullable();
            $table->string('language', 10)->nullable();
            $table->integer('outbound_links_count')->default(0);
            $table->integer('page_size_bytes')->nullable();
            $table->unsignedTinyInteger('priority')->default(5); // 1-10
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('next_crawl_at')->nullable();
            $table->boolean('is_sitemap')->default(false);
            $table->boolean('robots_allowed')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urls', function (Blueprint $table) {
            //
        });
    }
};
