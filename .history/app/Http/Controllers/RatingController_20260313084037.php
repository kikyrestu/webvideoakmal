<?php

namespace App\Http\Controllers;

use App\Models\VideoRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        $request->validate(['score' => 'required|integer|min:1|max:5']);

        $ipHash = hash('sha256', $request->ip());

        VideoRating::updateOrCreate(
            ['video_id' => $id, 'ip_address' => $ipHash],
            ['score' => $request->score]
        );

        $average = round(VideoRating::where('video_id', $id)->avg('score'), 1);
        $total   = VideoRating::where('video_id', $id)->count();

        return response()->json([
            'average'    => $average,
            'total'      => $total,
            'user_score' => $request->score,
        ]);
    }
}
