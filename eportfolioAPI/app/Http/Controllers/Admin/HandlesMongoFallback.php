<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

trait HandlesMongoFallback
{
    protected function isMongoAvailable(): bool
    {
        try {
            DB::connection('mongodb')->getMongoDB();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function mockOrRedirect(string $collection, string $route)
    {
        if (! $this->isMongoAvailable()) {
            return redirect()->route($route)
                ->with('error', 'MongoDB is not available. Showing mock data only. Install ext-mongodb to enable full admin features.');
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
