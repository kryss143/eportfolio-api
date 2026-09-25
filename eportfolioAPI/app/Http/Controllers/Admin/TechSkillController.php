<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TechCategory;
use App\Http\Controllers\Controller;
use App\Models\TechSkill;
use App\Services\MockDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TechSkillController extends Controller
{
    use HandlesMongoFallback;

    public function index(Request $request)
    {
        if (! $this->isMongoAvailable()) {
            $items = MockDataService::get('tech_skills');
            if ($request->filled('category')) {
                $items = array_values(array_filter($items, fn ($s) => ($s['category'] ?? '') === $request->input('category')));
            }
            $techSkills = $this->mockPaginate($items);
            $categories = TechCategory::cases();

            return view('admin.tech-skills.index', compact('techSkills', 'categories'));
        }

        $query = TechSkill::query();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('label', 'like', "%{$search}%");
        }

        $query->orderBy('category')->orderBy('label');

        $techSkills = $query->paginate(15)->withQueryString();
        $categories = TechCategory::cases();

        return view('admin.tech-skills.index', compact('techSkills', 'categories'));
    }

    public function create()
    {
        $categories = TechCategory::cases();

        return view('admin.tech-skills.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Bug #5 (2026-09-25): store() lacked the Mongo-down guard.
        if ($redirect = $this->denyWhenMongoDown('admin.tech-skills.index')) {
            return $redirect;
        }

        $validated = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_column(TechCategory::cases(), 'value'))],
            'logo' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
        ]);

        TechSkill::create($validated);

        return redirect()->route('admin.tech-skills.index')
            ->with('success', 'Tech skill created successfully.');
    }

    public function edit(TechSkill $techSkill)
    {
        $categories = TechCategory::cases();

        return view('admin.tech-skills.edit', compact('techSkill', 'categories'));
    }

    public function update(Request $request, TechSkill $techSkill)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_column(TechCategory::cases(), 'value'))],
            'logo' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $techSkill->update($validated);

        return redirect()->route('admin.tech-skills.index')
            ->with('success', 'Tech skill updated successfully.');
    }

    public function destroy(TechSkill $techSkill): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.tech-skills.index')) {
            return $redirect;
        }

        $techSkill->delete();

        return redirect()->route('admin.tech-skills.index')
            ->with('success', 'Tech skill deleted successfully.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        if ($redirect = $this->denyWhenMongoDown('admin.tech-skills.index')) {
            return $redirect;
        }

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        $count = TechSkill::whereIn('_id', $request->input('ids'))->delete();

        return redirect()->route('admin.tech-skills.index')
            ->with('success', "{$count} tech skill(s) deleted.");
    }
}
