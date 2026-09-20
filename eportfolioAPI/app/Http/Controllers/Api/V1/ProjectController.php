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
        $projects = $this->withFallback(function () use ($request) {
            $query = Project::query();

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('featured')) {
                $query->where('featured', $request->boolean('featured'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('technology')) {
                $query->where('technologies', 'like', "%{$request->input('technology')}%");
            }

            $sort = $request->input('sort', 'created_at');
            $direction = $request->input('direction', 'desc');
            $query->orderBy($sort, $direction);

            return $query->paginate($request->input('per_page', 15));
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
