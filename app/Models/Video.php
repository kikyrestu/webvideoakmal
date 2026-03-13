<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Video extends Model
{
    use Searchable;

    protected $fillable = [
        'title', 'slug', 'description', 'thumbnail_path',
        'group_id', 'category_id', 'video_type', 'video_path',
        'embed_url', 'is_live', 'views_count', 'duration',
        'status', 'published_at',
    ];

    protected $casts = [
        'is_live'      => 'boolean',
        'published_at' => 'datetime',
        'views_count'  => 'integer',
        'duration'     => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'video_tags');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(VideoLike::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(VideoRating::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // ─── Scout Search Indexing ───────────────────────────────────────

    public function toSearchableArray(): array
    {
        $this->loadMissing(['category', 'group', 'tags']);

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'category'    => $this->category?->name,
            'group'       => $this->group?->name,
            'tags'        => $this->tags->pluck('name')->toArray(),
            'status'      => $this->status,
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /**
     * Detect embed type: 'youtube' | 'vimeo' | 'raw'
     */
    public function getEmbedType(): string
    {
        if ($this->video_type !== 'embed') {
            return 'upload';
        }

        $url = strtolower($this->embed_url ?? '');

        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        }

        if (str_contains($url, 'vimeo.com')) {
            return 'vimeo';
        }

        return 'raw';
    }

    /**
     * Generate embed iframe code for the embed modal
     */
    public function getEmbedCode(): string
    {
        $url = route('videos.show', $this->slug);
        return '<iframe src="' . $url . '" width="560" height="315" frameborder="0" allowfullscreen></iframe>';
    }

    /**
     * Get average rating
     */
    public function getAverageRating(): float
    {
        return round($this->ratings()->avg('score') ?? 0, 1);
    }

    /**
     * Format duration from seconds to H:i:s or i:s
     */
    public function getFormattedDuration(): ?string
    {
        if (!$this->duration) return null;

        $h = intdiv($this->duration, 3600);
        $m = intdiv($this->duration % 3600, 60);
        $s = $this->duration % 60;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }
}
