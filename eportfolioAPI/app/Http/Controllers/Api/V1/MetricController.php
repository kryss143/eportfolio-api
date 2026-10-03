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

        // Bug #13: the metrics collection can legitimately be empty (no
        // metrics seeded yet). withFallback() only falls back on a connection
        // error, so an empty *result* would otherwise come back as an empty
        // paginator envelope. Mirror the list endpoints: when the collection
        // has no documents at all, serve the mock metrics instead of an
        // empty healthy-mode response.
        if ($metrics instanceof \Illuminate\Pagination\LengthAwarePaginator && $metrics->total() === 0 && ! $this->collectionHasDocuments('metrics')) {
            $mockMetrics = $this->applyMockFilters('metrics');

            if (! empty($mockMetrics)) {
                $metrics = $this->mockPaginate($mockMetrics, 'metrics');
            }
        }

        return MetricResource::collection($metrics);
    }
}
