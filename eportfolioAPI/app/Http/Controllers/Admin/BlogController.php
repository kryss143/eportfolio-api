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
                $items = array_values(array_filter($items, fn ($b) => str_contains($b['title'] ?? '', $search)));
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
            // Bug #3: use the model class so the presence verifier resolves the
            // model's mongodb connection instead of the default (sqlite) one.
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

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        $count = Blog::whereIn('_id', $request->input('ids'))->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', "{$count} blog post(s) deleted.");
    }
}
