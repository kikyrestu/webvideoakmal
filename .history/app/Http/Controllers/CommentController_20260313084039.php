<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, int $id)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50',
            'content'  => 'required|string|min:3|max:1000',
        ]);

        Comment::create([
            'video_id'     => $id,
            'username'     => strip_tags($validated['username']),
            'content'      => strip_tags($validated['content']),
            'is_from_admin'=> false,
            'status'       => 'pending',
        ]);

        return response()->json(['message' => 'Komentar menunggu moderasi.']);
    }
}
