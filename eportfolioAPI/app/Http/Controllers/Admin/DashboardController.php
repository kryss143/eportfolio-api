<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\Project;
use App\Models\TechSkill;
use App\Services\MockDataService;

class DashboardController extends Controller
{
    use HandlesMongoFallback;

    public function index()
    {
        $mongoAvailable = $this->isMongoAvailable();

        if ($mongoAvailable) {
            $stats = [
                'projects' => Project::count(),
                'blogs' => Blog::count(),
                'tech_skills' => TechSkill::count(),
            ];

            $projectsByStatus = [
                'built' => Project::where('status', 'built')->count(),
                'in-progress' => Project::where('status', 'in-progress')->count(),
            ];

            // Bug #2: the MongoDB query builder cannot compile selectRaw()
            // expressions inside a grouped aggregation, so count in PHP
            // (the collection is small) instead of groupBy + pluck.
            $skillsByCategory = TechSkill::all()
                ->groupBy(fn (TechSkill $skill) => $skill->category?->value ?? 'other')
                ->map->count()
                ->toArray();

            $recentBlogs = Blog::orderByDesc('created_at')->limit(5)->get();
            $recentActivity = ActivityLog::with('user')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        } else {
            // Use mock data
            $mockProjects = MockDataService::get('projects');
            $mockBlogs = MockDataService::get('blogs');
            $mockTechSkills = MockDataService::get('tech_skills');            $stats = [
                'projects' => count($mockProjects),
                'blogs' => count($mockBlogs),
                'tech_skills' => count($mockTechSkills),
            ];

            $projectsByStatus = [
                'built' => count(array_filter($mockProjects, fn ($p) => $p['status'] === 'built')),
                'in-progress' => count(array_filter($mockProjects, fn ($p) => $p['status'] === 'in-progress')),
            ];

            $skillsByCategory = [];
            foreach ($mockTechSkills as $ts) {
                $cat = $ts['category'] ?? 'other';
                $skillsByCategory[$cat] = ($skillsByCategory[$cat] ?? 0) + 1;
            }

            $recentBlogs = collect($mockBlogs)->take(5)->map(fn ($b) => $this->toModel($b));
            $recentActivity = collect();
        }

        $needsAttention = [];

        $draftBlogs = $mongoAvailable
            ? Blog::whereNull('date')->count()
            : count(array_filter(MockDataService::get('blogs'), fn ($b) => empty($b['date'])));

        if ($draftBlogs > 0) {
            $needsAttention[] = [
                'message' => "{$draftBlogs} draft blog post(s) unpublished",
                'link' => route('admin.blogs.index'),
                'type' => 'warning',
            ];
        }

        $noGithub = $mongoAvailable
            ? Project::whereNull('githubLink')->count()
            : count(array_filter(MockDataService::get('projects'), fn ($p) => empty($p['githubLink'])));

        if ($noGithub > 0) {
            $needsAttention[] = [
                'message' => "{$noGithub} project(s) missing GitHub link",
                'link' => route('admin.projects.index'),
                'type' => 'warning',
            ];
        }

        if ($projectsByStatus['in-progress'] > 0) {
            $needsAttention[] = [
                'message' => "{$projectsByStatus['in-progress']} project(s) in progress",
                'link' => route('admin.projects.index'),
                'type' => 'info',
            ];
        }

        return view('admin.dashboard', compact(
            'stats',
            'projectsByStatus',
            'skillsByCategory',
            'recentBlogs',
            'recentActivity',
            'needsAttention'
        ));
    }
}
