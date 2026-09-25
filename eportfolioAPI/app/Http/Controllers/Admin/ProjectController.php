<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\MockDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    use HandlesMongoFallback;

    public function index(Request $request)
    {
        if (! $this->isMongoAvailable()) {
            $items = MockDataService::get('projects');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $items = array_values(array_filter($items, fn ($p) => str_contains($p['title'] ?? '', $search) || str_contains($p['description'] ?? '', $search)));
            }
            if ($request->filled('status')) {
                $items = array_values(array_filter($items, fn ($p) => ($p['status'] ?? '') === $request->input('status')));
            }
            $projects = $this->mockPaginate($items);

            return view('admin.projects.index', ['projects' => $projects]);
        }

        $query = Project::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Bug #5: whitelist sort/direction to prevent crafted query strings
        // from throwing unhandled exceptions.
        $validated = $request->validate([
            'sort' => ['sometimes', 'in:title,created_at,status'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ]);
        $sort = $validated['sort'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'desc';
        $query->orderBy($sort, $direction);

        $projects = $query->paginate(15)->withQueryString();

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        // Bug #5 (2026-09-25): store() lacked the guard — with Mongo down the
        // create() threw an unhandled ConnectionTimeoutException (500) after
        // the admin filled in the whole form.
        if ($redirect = $this->denyWhenMongoDown('admin.projects.index')) {
            return $redirect;
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'technologies' => ['required', 'array'],
            'technologies.*' => ['string'],
            'status' => ['required', 'in:built,in-progress'],
            'githubLink' => ['nullable', 'url'],
            'demoLink' => ['nullable', 'url'],
            'image' => ['nullable', 'image', 'max:2048'],
            'outcome' => ['required', 'string'],
            'metrics' => ['required', 'array'],
            'metrics.*' => ['string'],
            'featured' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('projects', 'public');
        }

        $validated['featured'] = $request->boolean('featured');

        Project::create($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'technologies' => ['required', 'array'],
            'technologies.*' => ['string'],
            'status' => ['required', 'in:built,in-progress'],
            'githubLink' => ['nullable', 'url'],
            'demoLink' => ['nullable', 'url'],
            'image' => ['nullable', 'image', 'max:2048'],
            'outcome' => ['required', 'string'],
            'metrics' => ['required', 'array'],
            'metrics.*' => ['string'],
            'featured' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($project->image && Storage::disk('public')->exists($project->image)) {
                Storage::disk('public')->delete($project->image);
            }
            $validated['image'] = $request->file('image')->store('projects', 'public');
        }

        $validated['featured'] = $request->boolean('featured');

        $project->update($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.projects.index')) {
            return $redirect;
        }

        if ($project->image && Storage::disk('public')->exists($project->image)) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function toggleFeatured(Project $project): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.projects.index')) {
            return $redirect;
        }

        $project->update(['featured' => ! $project->featured]);

        return back()->with('success', 'Project featured status toggled.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.projects.index')) {
            return $redirect;
        }

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        $projects = Project::whereIn('_id', $request->input('ids'))->get();

        // Bug #8 (2026-09-25): count the models BEFORE mutating them so the
        // flash message reports the attempted (not post-mutation) size.
        $attempted = count($projects);
        $deleted = 0;

        foreach ($projects as $project) {
            if ($project->image && Storage::disk('public')->exists($project->image)) {
                Storage::disk('public')->delete($project->image);
            }
            $deleted += $project->delete() ? 1 : 0;
        }

        return redirect()->route('admin.projects.index')
            ->with('success', "{$deleted}/{$attempted} project(s) deleted.");
    }
}
