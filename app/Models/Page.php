<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'url_id',
        'title',
        'meta_description',
        'content',
    ];

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }
}