<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Experience;
use App\Models\Metric;
use App\Models\Project;
use App\Models\Skill;
use App\Models\TechSkill;
use App\Services\MockDataService;
use App\Support\MongoProbe;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\RuntimeException as MongoRuntimeException;

class LandingController extends Controller
{
    public function __invoke()
    {
        // Bug #14: query the real collections and fall back to mock data when
        // Mongo is unavailable or everything is empty.
        //
        // Bug #1 (2026-09-25): the healthy branch ran bare — with Mongo
        // unreachable (and/or a probe false-positive), the queries threw an
        // unhandled ConnectionTimeoutException and the homepage 500'd. The
        // queries are now wrapped so any connection failure degrades to mock
        // data exactly like the public API does.
        $hasContent = false;

        if (MongoProbe::available()) {
            try {
                // Bug #3 (2026-09-25): drafts (date IS NULL) must never be
                // published on the public site — the API's "published" filter
                // uses the same whereNotNull('date') convention.
                $projects = Project::orderByDesc('created_at')->get()->toArray();
                $blogs = Blog::whereNotNull('date')->orderByDesc('date')->get()->toArray();
                $metrics = Metric::all()->toArray();
                $techSkills = TechSkill::orderBy('label')->get()->toArray();
                $experience = Experience::first()?->toArray();
                $skills = Skill::first()?->toArray();

                $hasContent = count($projects) > 0 || count($blogs) > 0 || count($techSkills) > 0;
            } catch (ConnectionException|AuthenticationException|MongoRuntimeException $e) {
                MongoProbe::flush();
                $hasContent = false;
            }
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
}
