<?php

namespace App\Http\Controllers\Admin;

use App\Support\MongoProbe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

trait HandlesMongoFallback
{
    /**
     * Memoized per-instance MongoDB availability probe.
     */
    protected ?bool $mongoAvailable = null;

    protected function isMongoAvailable(): bool
    {
        if ($this->mongoAvailable !== null) {
            return $this->mongoAvailable;
        }

        // Bug #2 (2026-09-25): getDatabase() performs no server I/O, so the
        // probe reported Mongo as available even while unreachable. MongoProbe
        // issues a real ping() instead.
        //
        // Primary-partition lesson (2026-09-25 connectivity audit): Eloquent
        // queries — READS included — run with the default `primary` read
        // preference, so a reachable secondary does NOT make admin list
        // pages work (observed: read probe true, /admin/projects 500).
        // Admin branching therefore keys on PRIMARY reachability.
        return $this->mongoAvailable = MongoProbe::writeAvailable();
    }

    /**
     * Short-circuit row actions when MongoDB is unavailable: the mock data
     * source is read-only, so persisting anything is impossible. Bug #4.
     *
     * Uses the WRITE-availability probe (strict primary): a reachable
     * secondary can serve reads for the index pages, but without the primary
     * every mutation would 500 (observed during the 2026-09-25 primary
     * partition — pings OK, writes failing).
     */
    protected function denyWhenMongoDown(string $route = 'admin.dashboard'): ?\Illuminate\Http\RedirectResponse
    {
        if (! MongoProbe::writeAvailable()) {
            return redirect()->route($route)
                ->with('error', 'MongoDB writes are unavailable right now (primary unreachable). Data shown may be read-only; please try again shortly.');
        }

        return null;
    }

    /**
     * Paginate mock items (when MongoDB is unavailable) so the view can use
     * ->links(), ->hasPages() and object property access like real models.
     */
    protected function mockPaginate(array $items, int $perPage = 15): LengthAwarePaginator
    {
        $page = max(1, (int) request()->input('page', 1));
        $models = array_map(
            fn (array $item) => $this->toModel($item),
            array_slice($items, ($page - 1) * $perPage, $perPage),
        );

        return (new LengthAwarePaginator(
            collect($models),
            count($items),
            $perPage,
            $page,
            ['path' => request()->url()],
        ))->withQueryString();
    }

    /**
     * Convert a mock data array into an anonymous Eloquent model
     * so views can access fields via $item->property.
     */
    protected function toModel(array $data): Model
    {
        $model = new class extends Model
        {
            protected $guarded = [];

            protected $keyType = 'string';

            public $incrementing = false;
        };

        $model->forceFill($data);
        $model->exists = true;

        return $model;
    }
}
