<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TechCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\TechSkillResource;
use App\Models\TechSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class TechSkillController extends Controller
{
    use FallbackData;

    public function index(Request $request): AnonymousResourceCollection
    {
        // Bug #13: bound per_page; Bug #15: validate category values.
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'category' => ['sometimes', 'string', Rule::in(array_column(TechCategory::cases(), 'value'))],
        ]);

        $techSkills = $this->withFallback(function () use ($request, $validated) {
            $query = TechSkill::query();

            if ($request->filled('category')) {
                $query->where('category', $validated['category']);
            }

            $query->orderBy('label');

            return $query->paginate($validated['per_page'] ?? 50);
        }, 'tech_skills');

        return TechSkillResource::collection($techSkills);
    }

    public function byCategory(string $category): JsonResponse
    {
        // Bug #15: unknown categories are a client error, not "no skills".
        $validated = validator(
            ['category' => $category],
            ['category' => ['required', 'string', Rule::in(array_column(TechCategory::cases(), 'value'))]]
        )->validate();

        // The category arrives as a route parameter, but the mock fallback
        // filters read request input — merge it so the fallback is filtered.
        request()->merge(['category' => $validated['category']]);

        $skills = $this->withFallback(
            fn () => TechSkill::where('category', $validated['category'])->orderBy('label')->get(),
            'tech_skills'
        );

        // Filter real DB results defensively (enum vs raw values).
        $filtered = collect($skills)
            ->filter(fn ($s) => ($s->category?->value ?? $s->category ?? null) === $validated['category'])
            ->values();

        return response()->json([
            'category' => $validated['category'],
            'count' => $filtered->count(),
            'skills' => TechSkillResource::collection($filtered),
        ]);
    }
}
