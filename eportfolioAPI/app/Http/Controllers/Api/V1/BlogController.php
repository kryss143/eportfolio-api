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
        // Bug #13: bound per_page; Bug #15: validate sort/direction values.
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'in:published,draft,unpublished'],
            'search' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', 'in:date,created_at,title'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ]);

        $blogs = $this->withFallback(function () use ($request, $validated) {
            $query = Blog::query();

            if ($request->filled('search')) {
                $search = $validated['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            }

            if ($validated['status'] ?? null) {
                // 'published' = has date, 'draft'/'unpublished' = no date
                if ($validated['status'] === 'published') {
                    $query->whereNotNull('date');
                } else {
                    $query->whereNull('date');
                }
            } else {
                // Drafts (date IS NULL) are never public by default — same
                // invariant the landing page and dashboard use (audit F2,
                // 2026-09-28). Explicit ?status=draft remains available.
                $query->whereNotNull('date');
            }

            $sort = $validated['sort'] ?? 'date';
            $direction = $validated['direction'] ?? 'desc';
            $query->orderBy($sort, $direction);

            return $query->paginate($validated['per_page'] ?? 15);
        }, 'blogs');

        return BlogResource::collection($blogs);
    }

    public function show(string $slug): JsonResponse
    {
        // Drafts (date IS NULL) are never publicly readable — matches the
        // index() default and the landing page invariant (audit F2).
        $blog = $this->withFallbackSingle(
            fn () => Blog::where('slug', $slug)->whereNotNull('date')->first(),
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
