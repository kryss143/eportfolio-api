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

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string'],
            'date' => ['nullable', 'date'],
            'readTime' => ['required', 'string', 'max:50'],
            'slug' => ['required', 'string', 'max:255', 'unique:blogs,slug'],
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
            'slug' => ['required', 'string', 'max:255', 'unique:blogs,slug,'.$blog->id.',_id'],
            'content' => ['required', 'string'],
        ]);

        $blog->update($validated);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    public function togglePublish(Blog $blog): RedirectResponse
    {
        $blog->update([
            'date' => $blog->date ? null : now()->toDateTimeString(),
        ]);

        $status = $blog->date ? 'published' : 'unpublished';

        return back()->with('success', "Blog post {$status}.");
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate(['ids' => ['required', 'array']]);

        $count = Blog::whereIn('_id', $request->input('ids'))->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', "{$count} blog post(s) deleted.");
    }
}
