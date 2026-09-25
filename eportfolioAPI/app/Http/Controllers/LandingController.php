<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Experience;
use App\Models\Metric;
use App\Models\Project;
use App\Models\Skill;
use App\Models\TechSkill;
use App\Services\MockDataService;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\RuntimeException as MongoRuntimeException;

class LandingController extends Controller
{
    public function __invoke()
    {
        // Bug #14: the landing page previously rendered mock data only, so
        // admin-managed content never appeared on the public site. Query the
        // real collections and fall back to mock data on connection errors
        // or when everything is empty.
        if ($this->mongoAvailable()) {
            $projects = Project::orderByDesc('created_at')->get()->toArray();
            $blogs = Blog::orderByDesc('date')->get()->toArray();
            $metrics = Metric::all()->toArray();
            $techSkills = TechSkill::orderBy('label')->get()->toArray();
            $experience = Experience::first()?->toArray();
            $skills = Skill::first()?->toArray();

            $hasContent = count($projects) > 0 || count($blogs) > 0 || count($techSkills) > 0;
        } else {
            $hasContent = false;
        }

        if (! $hasContent) {
            $projects = MockDataService::get('projects');
            $blogs = MockDataService::get('blogs');
            $metrics = MockDataService::get('metrics');
            $techSkills = MockDataService::get('tech_skills');
            $experience = MockDataService::get('experiences')[0] ?? null;
            $skills = MockDataService::get('skills')[0] ?? null;
        }

        // Group tech skills by category
        $groupedSkills = collect($techSkills)->groupBy('category');

        return view('landing', compact('projects', 'blogs', 'metrics', 'techSkills', 'experience', 'skills', 'groupedSkills'));
    }

    protected function mongoAvailable(): bool
    {
        try {
            DB::connection('mongodb')->getDatabase();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
