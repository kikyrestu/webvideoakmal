<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q', '');

        $videos = collect();
        if (strlen($q) >= 2) {
            $videos = Video::search($q)
                ->query(fn($q) => $q->with(['group', 'category'])->where('status', 'published'))
                ->paginate(20);
        }

        return view('search', compact('videos', 'q'));
    }
}
