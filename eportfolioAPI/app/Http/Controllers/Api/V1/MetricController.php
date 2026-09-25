<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MetricResource;
use App\Models\Metric;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    use FallbackData;

    public function index(Request $request): AnonymousResourceCollection
    {
        // Bug #13: bound per_page.
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $metrics = $this->withFallback(
            fn () => Metric::paginate($validated['per_page'] ?? 15),
            'metrics'
        );

        return MetricResource::collection($metrics);
    }
}
