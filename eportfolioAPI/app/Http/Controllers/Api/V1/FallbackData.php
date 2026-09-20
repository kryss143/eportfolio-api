<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\MockDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

trait FallbackData
{
    /**
     * Try a MongoDB query; on connection failure, fall back to mock data.
     */
    protected function withFallback(callable $dbCallback, string $mockCollection)
    {
        try {
            DB::connection('mongodb')->getMongoDB();
            $result = $dbCallback();

            if ($result instanceof LengthAwarePaginator && $result->total() === 0) {
                $mockItems = MockDataService::get($mockCollection);
                if (! empty($mockItems)) {
                    return $this->mockPaginate($mockItems, $mockCollection);
                }
            }

            return $result;
        } catch (\Throwable $e) {
            $items = MockDataService::get($mockCollection);

            return $this->mockPaginate($items, $mockCollection);
        }
    }

    /**
     * Try a single-record DB query; fall back to mock data.
     */
    protected function withFallbackSingle(callable $dbCallback, string $mockCollection, string $key, string $value)
    {
        try {
            DB::connection('mongodb')->getMongoDB();
            $result = $dbCallback();

            if ($result) {
                return $result;
            }

            return $this->findMockItem($mockCollection, $key, $value);
        } catch (\Throwable $e) {
            return $this->findMockItem($mockCollection, $key, $value);
        }
    }

    /**
     * Find a single mock item by id or slug.
     */
    protected function findMockItem(string $mockCollection, string $key, string $value): ?Model
    {
        if ($key === 'slug') {
            $item = MockDataService::findBySlug($mockCollection, $value);
        } else {
            $item = MockDataService::findById($mockCollection, $value);
        }

        return $item ? $this->toModel($item) : null;
    }

    /**
     * Paginate mock items wrapped as model instances.
     */
    protected function mockPaginate(array $items, string $collection = '', int $perPage = 15): LengthAwarePaginator
    {
        $page = (int) request()->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($items, $offset, $perPage);
        $models = array_map(fn ($item) => $this->toModel($item, $collection), $sliced);

        return new LengthAwarePaginator(
            collect($models),
            count($items),
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }

    /**
     * Convert a mock data array into an anonymous Eloquent model
     * so API Resources can access fields via $this->property.
     */
    protected function toModel(array $data, string $collection = ''): Model
    {
        $model = new class extends Model
        {
            protected $guarded = [];

            protected $keyType = 'string';

            public $incrementing = false;
        };

        if ($collection) {
            $model->setTable($collection);
        }

        // Force fill all attributes including id
        $model->forceFill($data);
        $model->exists = true;

        return $model;
    }
}
