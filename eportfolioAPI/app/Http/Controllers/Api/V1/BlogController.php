<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogController extends Controller
{
    use FallbackData;

    public function index(Request $request): AnonymousResourceCollection
    {
        $blogs = $this->withFallback(function () use ($request) {
            $query = Blog::query();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            }

            $sort = $request->input('sort', 'date');
            $direction = $request->input('direction', 'desc');
            $query->orderBy($sort, $direction);

            return $query->paginate($request->input('per_page', 15));
        }, 'blogs');

        return BlogResource::collection($blogs);
    }

    public function show(string $slug): JsonResponse
    {
        $blog = $this->withFallbackSingle(
            fn () => Blog::where('slug', $slug)->first(),
            'blogs',
            'slug',
            $slug
        );

        if (! $blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        return response()->json(['data' => new BlogResource($blog)]);
    }
}
