<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TechSkillResource;
use App\Models\TechSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class TechSkillController extends Controller
{
    use FallbackData;

    public function index(Request $request): AnonymousResourceCollection
    {
        $techSkills = $this->withFallback(function () use ($request) {
            $query = TechSkill::query();

            if ($request->filled('category')) {
                $query->where('category', $request->input('category'));
            }

            $query->orderBy('label');

            return $query->paginate($request->input('per_page', 50));
        }, 'tech_skills');

        return TechSkillResource::collection($techSkills);
    }

    public function byCategory(string $category): JsonResponse
    {
        $skills = $this->withFallback(
            fn () => TechSkill::where('category', $category)->orderBy('label')->get(),
            'tech_skills'
        );

        // If fallback returned paginated, filter manually
        if ($skills instanceof LengthAwarePaginator) {
            $filtered = collect($skills->items())->filter(
                fn ($s) => ($s->category?->value ?? $s->category ?? null) === $category
            );
        } else {
            $filtered = collect($skills)->filter(
                fn ($s) => ($s->category?->value ?? $s->category ?? null) === $category
            );
        }

        return response()->json([
            'category' => $category,
            'skills' => TechSkillResource::collection($filtered),
        ]);
    }
}
