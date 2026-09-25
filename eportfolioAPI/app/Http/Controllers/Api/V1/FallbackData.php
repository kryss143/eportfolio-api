<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\MockDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\RuntimeException as MongoRuntimeException;

trait FallbackData
{
    /**
     * Exceptions that mean "MongoDB is unreachable" and justify falling back
     * to mock data. Everything else is a real bug and must propagate.
     * Bug #10: the previous catch-all (\Throwable) swallowed developer errors
     * and served mock data with HTTP 200.
     */
    protected function isConnectionError(\Throwable $e): bool
    {
        return $e instanceof ConnectionException
            || $e instanceof AuthenticationException
            || $e instanceof MongoRuntimeException
            || str_contains($e->getMessage(), 'MongoDB connection configuration requires');
    }

    /**
     * Try a MongoDB query; on connection failure only, fall back to mock data.
     */
    protected function withFallback(callable $dbCallback, string $mockCollection)
    {
        try {
            DB::connection('mongodb')->getDatabase();
            $result = $dbCallback();

            // Bug #12: empty Collections (all()/get() endpoints) must fall back
            // too, not just empty paginators.
            $isEmptyPaginator = $result instanceof LengthAwarePaginator && $result->total() === 0;
            $isEmptyCollection = $result instanceof \Illuminate\Support\Collection && $result->isEmpty();

            if ($isEmptyPaginator || $isEmptyCollection) {
                $mockItems = $this->applyMockFilters($mockCollection);

                if (! empty($mockItems)) {
                    return $result instanceof LengthAwarePaginator
                        ? $this->mockPaginate($mockItems, $mockCollection)
                        : collect(array_map(
                            fn (array $item) => $this->toModel($item, $mockCollection),
                            $mockItems,
                        ));
                }
            }

            return $result;
        } catch (\Throwable $e) {
            if (! $this->isConnectionError($e)) {
                throw $e;
            }

            $items = $this->applyMockFilters($mockCollection);

            return $this->mockPaginate($items, $mockCollection);
        }
    }

    /**
     * Try a single-record DB query; fall back to mock data on connection errors.
     */
    protected function withFallbackSingle(callable $dbCallback, string $mockCollection, string $key, string $value)
    {
        try {
            DB::connection('mongodb')->getDatabase();
            $result = $dbCallback();

            if ($result) {
                return $result;
            }

            return $this->findMockItem($mockCollection, $key, $value);
        } catch (\Throwable $e) {
            if (! $this->isConnectionError($e)) {
                throw $e;
            }

            return $this->findMockItem($mockCollection, $key, $value);
        }
    }

    /**
     * Apply the current request's filters to the mock data set.
     * Bug #11: filters were silently dropped on the fallback path.
     * Supported generic filters: search (title/excerpt/description/label),
     * status (projects + blogs), featured (projects), category (tech skills),
     * technology (projects).
     */
    protected function applyMockFilters(string $collection): array
    {
        $items = MockDataService::get($collection);
        $request = request();

        if ($request->filled('search')) {
            $search = mb_strtolower($request->input('search'));
            $items = array_values(array_filter($items, function ($item) use ($search) {
                foreach (['title', 'excerpt', 'description', 'label'] as $field) {
                    if (isset($item[$field]) && str_contains(mb_strtolower($item[$field]), $search)) {
                        return true;
                    }
                }

                return false;
            }));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($collection === 'blogs') {
                // 'published' = has date, 'draft'/'unpublished' = no date
                $wantPublished = $status === 'published';
                $items = array_values(array_filter($items, fn ($b) => $wantPublished xor empty($b['date'])));
            } else {
                $items = array_values(array_filter($items, fn ($p) => ($p['status'] ?? '') === $status));
            }
        }

        if ($request->filled('featured')) {
            $wantFeatured = filter_var($request->input('featured'), FILTER_VALIDATE_BOOL);
            $items = array_values(array_filter($items, fn ($p) => (bool) ($p['featured'] ?? false) === $wantFeatured));
        }

        if ($request->filled('category')) {
            $category = $request->input('category');
            $items = array_values(array_filter($items, fn ($s) => ($s['category'] ?? '') === $category));
        }

        if ($request->filled('technology')) {
            $technology = $request->input('technology');
            $items = array_values(array_filter($items, fn ($p) => in_array($technology, $p['technologies'] ?? [], true)));
        }

        return $items;
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

        return $item ? $this->toModel($item, $mockCollection) : null;
    }

    /**
     * Paginate mock items wrapped as model instances.
     * Bug #17: keeps the query string on generated pagination links.
     */
    protected function mockPaginate(array $items, string $collection = '', int $perPage = 15): LengthAwarePaginator
    {
        $page = max(1, (int) request()->input('page', 1));
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($items, $offset, $perPage);
        $models = array_map(fn ($item) => $this->toModel($item, $collection), $sliced);

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

        // Normalize common date fields so resource output matches the DB path
        // (Bug #16): mock JSON stores plain strings like '2024-03-15'.
        foreach (['date', 'created_at', 'updated_at'] as $dateField) {
            if (isset($data[$dateField]) && is_string($data[$dateField])) {
                try {
                    $data[$dateField] = \Illuminate\Support\Carbon::parse($data[$dateField]);
                } catch (\Throwable) {
                    // leave as-is if unparseable
                }
            }
        }

        $model->forceFill($data);
        $model->exists = true;

        return $model;
    }
}
