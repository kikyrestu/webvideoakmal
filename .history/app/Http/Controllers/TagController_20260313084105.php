<?php

namespace App\Http\Controllers;

use App\Models\Tag;

class TagController extends Controller
{
    public function show(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $videos = $tag->videos()
            ->with(['group', 'category'])
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(20);

        return view('tag-show', compact('tag', 'videos'));
    }
}
