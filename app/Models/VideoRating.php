<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRating extends Model
{
    protected $fillable = ['video_id', 'ip_address', 'score'];

    protected $casts = [
        'score' => 'integer',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
