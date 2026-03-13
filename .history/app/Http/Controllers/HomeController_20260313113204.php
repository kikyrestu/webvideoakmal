<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter');
        $groupSlug = $request->get('g');

        // ── Video per Group ──────────────────────────────────
        $groupsQuery = Group::with(['videos' => function ($q) use ($filter, $groupSlug) {
            $q->where('status', 'published')
              ->with(['group', 'category'])
              ->latest('published_at')
              ->take(8);

            if ($filter === 'info') {
                $q->whereHas('category', fn($c) => $c->whereIn('slug', ['peristiwa', 'event']));
            } elseif ($filter === 'umum') {
                $q->whereHas('category', fn($c) => $c->where('slug', 'umum'));
            }
        }])
        ->when($groupSlug, fn($q) => $q->where('slug', $groupSlug))
        ->orderBy('sort_order')
        ->get()
        ->filter(fn($g) => $g->videos->count() > 0);

        // ── Video tanpa Group (section Lainnya) ───────────────
        $ungroupedVideos = Video::where('status', 'published')
            ->whereNull('group_id')
            ->with(['category'])
            ->latest('published_at')
            ->take(8)
            ->get();

        $tags = cache()->remember('tags_all', 3600, fn() =>
            Tag::withCount('videos')->orderByDesc('videos_count')->take(20)->get()
        );

        return view('home', compact('groupsQuery', 'ungroupedVideos', 'tags', 'filter'));
    }
}
