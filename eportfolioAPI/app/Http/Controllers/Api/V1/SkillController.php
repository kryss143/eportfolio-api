<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkillController extends Controller
{
    use FallbackData;

    public function index(Request $request): AnonymousResourceCollection
    {
        // Bug #13: bound per_page.
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $skills = $this->withFallback(
            fn () => Skill::paginate($validated['per_page'] ?? 15),
            'skills'
        );

        return SkillResource::collection($skills);
    }
}
