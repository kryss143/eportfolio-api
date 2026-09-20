<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkillController extends Controller
{
    use FallbackData;

    public function index(): AnonymousResourceCollection
    {
        $skills = $this->withFallback(
            fn () => Skill::all(),
            'skills'
        );

        return SkillResource::collection($skills);
    }
}
