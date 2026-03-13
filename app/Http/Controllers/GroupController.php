<?php

namespace App\Http\Controllers;

use App\Models\Group;

class GroupController extends Controller
{
    public function show(string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();

        $videos = $group->videos()
            ->with(['category'])
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(20);

        return view('group-show', compact('group', 'videos'));
    }
}
