<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoLike;
use App\Models\VideoRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VideoController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $video = Video::with(['group', 'category', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // ── View counter (1x per session per video) ──────────
        $sessionKey = 'viewed_video_' . $video->id;
        if (!session()->has($sessionKey)) {
            session()->put($sessionKey, true);
            Video::where('id', $video->id)->increment('views_count');
            $video->views_count++;
        }

        // ── Related videos ────────────────────────────────────
        $related = Cache::remember('related_videos_' . $video->id, 900, function () use ($video) {
            return Video::with(['group', 'category'])
                ->where('status', 'published')
                ->where('id', '!=', $video->id)
                ->where(function ($q) use ($video) {
                    if ($video->group_id) $q->orWhere('group_id', $video->group_id);
                    if ($video->category_id) $q->orWhere('category_id', $video->category_id);
                })
                ->latest()
                ->take(10)
                ->get();
        });

        // ── Comments ──────────────────────────────────────────
        $comments = $video->comments()
            ->where('status', 'approved')
            ->orderBy('created_at')
            ->get();

        // ── Like & Rating state from IP ───────────────────────
        $ipHash = hash('sha256', $request->ip());
        $userHasLiked  = VideoLike::where('video_id', $video->id)->where('ip_address', $ipHash)->exists();
        $userRating    = VideoRating::where('video_id', $video->id)->where('ip_address', $ipHash)->value('score');
        $likesCount    = $video->likes()->count();
        $averageRating = $video->getAverageRating();
        $ratingsCount  = $video->ratings()->count();

        return view('video-show', compact(
            'video', 'related', 'comments',
            'userHasLiked', 'userRating', 'likesCount',
            'averageRating', 'ratingsCount'
        ));
    }
}
