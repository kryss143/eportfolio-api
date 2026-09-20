<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Services\MockDataService;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    use HandlesMongoFallback;

    public function index()
    {
        if (! $this->isMongoAvailable()) {
            $experiences = collect(
                array_map(fn (array $item) => $this->toModel($item), MockDataService::get('experiences'))
            );

            return view('admin.experiences.index', compact('experiences'));
        }

        $experiences = Experience::all();

        return view('admin.experiences.index', compact('experiences'));
    }

    public function create()
    {
        return view('admin.experiences.create');
    }

    public function store(Request $request)
    {
        if (! $this->isMongoAvailable()) {
            return redirect()->route('admin.experiences.index')
                ->with('error', 'MongoDB is not available. Mock data cannot be modified — install ext-mongodb and connect to create entries.');
        }

        $validated = $request->validate([
            'position' => ['required', 'string', 'max:255'],
            'yearsOfExperience' => ['required', 'integer', 'min:0'],
            'soloProjects' => ['required', 'integer', 'min:0'],
            'collabProjects' => ['required', 'integer', 'min:0'],
        ]);

        Experience::create($validated);

        return redirect()->route('admin.experiences.index')
            ->with('success', 'Experience created successfully.');
    }

    public function edit(Experience $experience)
    {
        return view('admin.experiences.edit', compact('experience'));
    }

    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'position' => ['required', 'string', 'max:255'],
            'yearsOfExperience' => ['required', 'integer', 'min:0'],
            'soloProjects' => ['required', 'integer', 'min:0'],
            'collabProjects' => ['required', 'integer', 'min:0'],
        ]);

        $experience->update($validated);

        return redirect()->route('admin.experiences.index')
            ->with('success', 'Experience updated successfully.');
    }
}
