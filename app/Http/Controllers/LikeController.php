<?php

namespace App\Http\Controllers;

use App\Models\VideoLike;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    public function toggle(int $id): JsonResponse
    {
        $ipHash = hash('sha256', request()->ip());

        $existing = VideoLike::where('video_id', $id)
            ->where('ip_address', $ipHash)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            VideoLike::create([
                'video_id'   => $id,
                'ip_address' => $ipHash,
            ]);
            $liked = true;
        }

        $count = VideoLike::where('video_id', $id)->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }
}
