<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    use FallbackData;

    public function index(Request $request): AnonymousResourceCollection
    {
        // Bug #13: bound per_page; Bug #15: validate status values instead of
        // silently matching nothing.
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'in:built,in-progress'],
            'featured' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string', 'max:255'],
            'technology' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', 'in:title,created_at,status'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ]);

        $projects = $this->withFallback(function () use ($request, $validated) {
            $query = Project::query();

            if ($request->filled('status')) {
                $query->where('status', $validated['status']);
            }

            if ($request->filled('featured')) {
                $query->where('featured', $request->boolean('featured'));
            }

            if ($request->filled('search')) {
                $search = $validated['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('technology')) {
                $query->where('technologies', 'like', "%{$validated['technology']}%");
            }

            $sort = $validated['sort'] ?? 'created_at';
            $direction = $validated['direction'] ?? 'desc';
            $query->orderBy($sort, $direction);

            return $query->paginate($validated['per_page'] ?? 15);
        }, 'projects');

        return ProjectResource::collection($projects);
    }

    public function show(string $id): JsonResponse
    {
        $project = $this->withFallbackSingle(
            fn () => Project::find($id),
            'projects',
            'id',
            $id
        );

        if (! $project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        return response()->json(['data' => new ProjectResource($project)]);
    }
}
