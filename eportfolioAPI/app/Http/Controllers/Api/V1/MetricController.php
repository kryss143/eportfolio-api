<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MetricResource;
use App\Models\Metric;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MetricController extends Controller
{
    use FallbackData;

    public function index(): AnonymousResourceCollection
    {
        $metrics = $this->withFallback(
            fn () => Metric::all(),
            'metrics'
        );

        return MetricResource::collection($metrics);
    }
}
