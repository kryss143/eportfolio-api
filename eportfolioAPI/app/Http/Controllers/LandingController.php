<?php

namespace App\Http\Controllers;

use App\Services\MockDataService;

class LandingController extends Controller
{
    public function __invoke()
    {
        $projects = MockDataService::get('projects');
        $blogs = MockDataService::get('blogs');
        $metrics = MockDataService::get('metrics');
        $techSkills = MockDataService::get('tech_skills');
        $experience = MockDataService::get('experiences')[0] ?? null;
        $skills = MockDataService::get('skills')[0] ?? null;

        // Group tech skills by category
        $groupedSkills = collect($techSkills)->groupBy('category');

        return view('landing', compact('projects', 'blogs', 'metrics', 'techSkills', 'experience', 'skills', 'groupedSkills'));
    }
}
