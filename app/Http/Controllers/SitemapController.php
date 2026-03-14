<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Group;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $videos     = Video::where('status', 'published')->orderByDesc('updated_at')->get();
        $groups     = Group::all();
        $categories = Category::all();
        $tags       = Tag::has('videos')->get();

        $content = view('sitemap', compact('videos', 'groups', 'categories', 'tags'))->render();

        return response($content, 200)->header('Content-Type', 'application/xml');
    }
}
