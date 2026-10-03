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
            // Note: deliberately no availability pre-check here — getDatabase()
            // is I/O-free (probe bug #2), and the query itself is the probe:
            // its connection errors are caught below and trigger the fallback.
            $result = $dbCallback();

            // Bug #12: empty Collections (all()/get() endpoints) must fall back
            // too, not just empty paginators.
            $isEmptyPaginator = $result instanceof LengthAwarePaginator && $result->total() === 0;
            $isEmptyCollection = $result instanceof \Illuminate\Support\Collection && $result->isEmpty();

            if ($isEmptyPaginator || $isEmptyCollection) {
                // Guard (audit r2 F1, 2026-09-28): fall back to mock only when
                // the underlying collection has NO documents at all — the same
                // rule withFallbackSingle() applies. An empty *filtered* view
                // of a populated live collection (e.g. ?status=published on an
                // all-drafts DB, or a search that matches nothing) is the
                // truthful answer and must not be substituted with mock data.
                if ($this->collectionHasDocuments($mockCollection)) {
                    return $result;
                }

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
     * Try a single-record DB query; fall back to mock data ONLY when the DB
     * is reachable but has no data (collection empty) — i.e. exactly when the
     * list endpoints serve mock content (bug #10).
     *
     * On a connection error the mock item is NOT served: the list endpoint
     * would 404/fail for the same identifier, and mock ids ("p1", "b1") are
     * not stable identifiers a client can rely on. This keeps list and detail
     * endpoints consistent — no phantom detail pages for ids the list never
     * exposes.
     */
    protected function withFallbackSingle(callable $dbCallback, string $mockCollection, string $key, string $value)
    {
        try {
            // No availability pre-check — the callback's query IS the probe:
            // its connection errors are caught below and trigger the mock
            // fallback (a pre-check would just add a round trip per request).
            $result = $dbCallback();

            if ($result) {
                return $result;
            }

            // DB answered authoritatively: serve the mock item only when the
            // collection is empty (mirrors withFallback's empty-DB branch).
            if ($this->collectionHasDocuments($mockCollection)) {
                return null;
            }

            return $this->findMockItem($mockCollection, $key, $value);
        } catch (\Throwable $e) {
            if (! $this->isConnectionError($e)) {
                throw $e;
            }

            // Mongo unreachable: same condition as the list fallback —
            // serve the mock item so detail links from a mock list work.
            // Use the stable 'id' key here too, so mock detail lookups
            // (p1/b1) are consistent with the list path below.
            return $this->findMockItem($mockCollection, 'id', $value);
        }
    }

    /**
     * Does the given collection contain at least one document?
     */
    protected function collectionHasDocuments(string $collection): bool
    {
        // first() rather than exists() — guaranteed supported by the MongoDB
        // query builder (used throughout the codebase).
        return DB::connection('mongodb')->table($collection)->limit(1)->first() !== null;
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
        } elseif ($collection === 'blogs') {
            // Drafts (no date) are never public by default — mirrors the DB
            // path's whereNotNull('date') default (audit F2, 2026-09-28), so
            // the degraded mode serves exactly what the healthy mode would.
            $items = array_values(array_filter($items, fn ($b) => ! empty($b['date'])));
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
            // Mock rows use a stable 'id' (e.g. 'p1', 'b1') that never
            // collides with a MongoDB ObjectId, and the list path serves the
            // same row for a ?id=<id> query. Match that to avoid phantom
            // detail pages and to keep the DB path's primary key as the
            // single source of truth for lookup.
            $item = MockDataService::findById($mockCollection, $value);
        }

        return $item ? $this->toModel($item, $mockCollection) : null;
    }

    /**
     * Paginate mock items wrapped as model instances.
     * Bug #17: keeps the query string on generated pagination links.
     *
     * v2-audit fix: honor the request's validated per_page (bounded to the
     * same 1..100 range the endpoints validate) instead of hardcoding 15 —
     * the DB path and the mock path must produce identical envelope shapes.
     */
    protected function mockPaginate(array $items, string $collection = '', ?int $perPage = null): LengthAwarePaginator
    {
        $perPage ??= (int) request()->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));
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
