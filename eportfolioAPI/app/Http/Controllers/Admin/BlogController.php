<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\MockDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use HandlesMongoFallback;

    public function index(Request $request)
    {
        if (! $this->isMongoAvailable()) {
            $items = MockDataService::get('blogs');
            if ($request->filled('search')) {
                $search = $request->input('search');
                // Search title + excerpt, matching the live-DB query below
                // (audit r2 F3, 2026-09-28: degraded mode must agree with the
                // healthy path).
                // Audit post-mongo Bug 6: the package's `like` operator is
                // case-insensitive on Mongo — the degraded filter must be too.
                $needle = mb_strtolower($search);
                $items = array_values(array_filter($items, fn ($b) => str_contains(mb_strtolower($b['title'] ?? ''), $needle) || str_contains(mb_strtolower($b['excerpt'] ?? ''), $needle)));
            }
            $blogs = $this->mockPaginate($items);

            return view('admin.blogs.index', ['blogs' => $blogs]);
        }

        $query = Blog::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'published') {
                $query->whereNotNull('date');
            } elseif ($request->input('status') === 'draft') {
                $query->whereNull('date');
            }
        }

        // Bug #5: whitelist sort/direction to prevent crafted query strings
        // from throwing unhandled exceptions.
        $validated = $request->validate([
            'sort' => ['sometimes', 'in:title,created_at,date'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ]);
        $sort = $validated['sort'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'desc';
        $query->orderBy($sort, $direction);

        $blogs = $query->paginate(15)->withQueryString();

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request)
    {
        // Bug #5 (2026-09-25): store() lacked the Mongo-down guard.
        if ($redirect = $this->denyWhenMongoDown('admin.blogs.index')) {
            return $redirect;
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string'],
            'date' => ['nullable', 'date'],
            'readTime' => ['required', 'string', 'max:50'],
            // Bug #3: use the model class so the presence verifier resolves
            // the model's mongodb connection.
            'slug' => ['required', 'string', 'max:255', 'unique:'.Blog::class.',slug'],
            'content' => ['required', 'string'],
        ]);

        Blog::create($validated);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        // Audit F3 (2026-09-28): update() lacked the Mongo-down guard that
        // store/destroy/toggle/bulk already carry (bug #5 series).
        if ($redirect = $this->denyWhenMongoDown('admin.blogs.index')) {
            return $redirect;
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string'],
            'date' => ['nullable', 'date'],
            'readTime' => ['required', 'string', 'max:50'],
            // Bug #3: model-class rule + explicit _id key column so the
            // exclusion targets the Mongo document being edited.
            'slug' => ['required', 'string', 'max:255', 'unique:'.Blog::class.',slug,'.$blog->id.',_id'],
            'content' => ['required', 'string'],
        ]);

        $blog->update($validated);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.blogs.index')) {
            return $redirect;
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    public function togglePublish(Blog $blog): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.blogs.index')) {
            return $redirect;
        }

        $blog->update([
            'date' => $blog->date ? null : now()->toDateTimeString(),
        ]);

        $status = $blog->date ? 'published' : 'unpublished';

        return back()->with('success', "Blog post {$status}.");
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.blogs.index')) {
            return $redirect;
        }

        // Audit post-mongo Bug 7: ids must be well-formed ObjectIds, not
        // arbitrary strings reaching the driver.
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string', 'regex:/^[a-f0-9]{24}$/i'],
        ]);

        // Audit post-mongo Bug 4: delete models one by one (like the project
        // controller) so ActivityObserver::deleted() fires for each. The
        // previous query-builder mass delete never instantiated models and
        // left bulk blog deletions with ZERO audit trail.
        $blogs = Blog::whereIn('_id', $request->input('ids'))->get();
        $count = 0;
        foreach ($blogs as $blog) {
            $count += $blog->delete() ? 1 : 0;
        }

        return redirect()->route('admin.blogs.index')
            ->with('success', "{$count} blog post(s) deleted.");
    }
}
