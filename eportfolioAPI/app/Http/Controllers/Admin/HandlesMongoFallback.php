<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

        try {
            DB::connection('mongodb')->getDatabase();

            return $this->mongoAvailable = true;
        } catch (\Throwable) {
            return $this->mongoAvailable = false;
        }
    }

    /**
     * Short-circuit row actions when MongoDB is unavailable: the mock data
     * source is read-only, so persisting anything is impossible. Bug #4.
     */
    protected function denyWhenMongoDown(string $route = 'admin.dashboard'): ?\Illuminate\Http\RedirectResponse
    {
        if (! $this->isMongoAvailable()) {
            return redirect()->route($route)
                ->with('error', 'MongoDB is not available. Mock data is read-only, so this action cannot be performed.');
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
