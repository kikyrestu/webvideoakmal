<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Tag;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter');

        $groupsQuery = Group::with(['videos' => function ($q) use ($filter) {
            $q->where('status', 'published')
              ->with(['group', 'category'])
              ->latest('published_at')
              ->take(8);

            if ($filter === 'info') {
                $q->whereHas('category', fn($c) => $c->where('slug', 'peristiwa')->orWhere('slug', 'event'));
            } elseif ($filter === 'umum') {
                $q->whereHas('category', fn($c) => $c->where('slug', 'umum'));
            }
        }])
        ->orderBy('sort_order')
        ->get()
        ->filter(fn($g) => $g->videos->count() > 0);

        $tags = cache()->remember('tags_all', 3600, fn() =>
            Tag::withCount('videos')->orderByDesc('videos_count')->take(20)->get()
        );

        return view('home', compact('groupsQuery', 'tags', 'filter'));
    }
}
