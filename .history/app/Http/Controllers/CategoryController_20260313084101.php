<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $videos = $category->videos()
            ->with(['group'])
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(20);

        return view('category-show', compact('category', 'videos'));
    }
}
