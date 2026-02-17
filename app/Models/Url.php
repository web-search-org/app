<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Url extends Model
{
    protected $fillable = [
        'url',
        'domain',
        'status',
        'last_crawled_at',
        'crawl_count',
    ];

    protected $casts = [
        'last_crawled_at' => 'datetime',
    ];

    public function page(): HasOne
    {
        return $this->hasOne(Page::class);
    }
}