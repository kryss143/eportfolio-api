<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Services\MockDataService;
use App\Support\MongoProbe;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    use HandlesMongoFallback;

    public function index()
    {
        if (! $this->isMongoAvailable()) {
            $mock = MockDataService::get('skills')[0] ?? [];
            $skills = $mock === []
                ? null
                : $this->toModel($mock);

            return view('admin.skills.index', compact('skills'));
        }

        $skills = Skill::first();

        return view('admin.skills.index', compact('skills'));
    }

    public function edit()
    {
        if (! $this->isMongoAvailable()) {
            $mock = MockDataService::get('skills')[0] ?? [];
            $skills = $mock === []
                ? null
                : $this->toModel($mock);

            return view('admin.skills.edit', compact('skills'));
        }

        $skills = Skill::firstOrCreate([], [
            'proficient' => [],
            'familiar' => [],
            'authentication' => [],
            'architecture' => [],
            'toolsPlatforms' => [],
            'practices' => [],
            'ai' => [],
        ]);

        return view('admin.skills.edit', compact('skills'));
    }

    public function update(Request $request)
    {
        // Write-path guard: strict primary check (a reachable secondary does
        // not make writes possible).
        if (! MongoProbe::writeAvailable()) {
            return redirect()->route('admin.skills.edit')
                ->with('error', 'MongoDB writes are unavailable right now (primary unreachable). Please try again shortly.');
        }

        $validated = $request->validate([
            'proficient' => ['required', 'array'],
            'proficient.*' => ['string'],
            'familiar' => ['required', 'array'],
            'familiar.*' => ['string'],
            'authentication' => ['required', 'array'],
            'authentication.*' => ['string'],
            'architecture' => ['required', 'array'],
            'architecture.*' => ['string'],
            'toolsPlatforms' => ['required', 'array'],
            'toolsPlatforms.*' => ['string'],
            'practices' => ['required', 'array'],
            'practices.*' => ['string'],
            'ai' => ['required', 'array'],
            'ai.*' => ['string'],
        ]);

        $skills = Skill::firstOrCreate([]);
        $skills->update($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skills updated successfully.');
    }
}
