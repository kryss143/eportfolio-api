<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExperienceController extends Controller
{
    use FallbackData;

    public function index(): AnonymousResourceCollection
    {
        $experiences = $this->withFallback(
            fn () => Experience::all(),
            'experiences'
        );

        return ExperienceResource::collection($experiences);
    }
}
