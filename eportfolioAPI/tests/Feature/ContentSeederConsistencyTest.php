<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Blog;
use App\Models\Experience;
use App\Models\Metric;
use App\Models\Project;
use App\Models\Skill;
use App\Models\TechSkill;
use App\Services\MockDataService;
use Tests\Concerns\CleansMongoCollections;
use Tests\TestCase;

/**
 * Locks ContentSeeder to database/mock-data.json — the single source of
 * truth ported from the portfolio's src/contents/*. These tests fail if the
 * seeder and the fallback file drift apart again (the old inline seeder
 * arrays had abridged blog bodies, an outdated GrabCat blurb, and a
 * nonexistent copilot devicon path, so the healthy and degraded API modes
 * served different content).
 *
 * Mock rows carry synthetic ids/timestamps that are intentionally not seeded
 * (Mongo documents get real ObjectIds); every assertion below therefore
 * compares the FIELDS the API Resources expose, in the file's row order.
 */
class ContentSeederConsistencyTest extends TestCase
{
    use CleansMongoCollections;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanMongoCollections();
        $this->artisan('migrate', ['--force' => true]);
        $this->artisan('db:seed', ['--class' => 'ContentSeeder', '--force' => true]);
    }

    public function test_seeds_experience_from_mock_data(): void
    {
        $mock = MockDataService::findById('experiences', 'primary');

        $this->assertNotNull($mock, 'mock-data.json must contain the "primary" experience row');

        $experience = Experience::query()->first();

        $this->assertNotNull($experience);
        $this->assertSame($mock['position'], $experience->position);
        $this->assertSame((int) $mock['yearsOfExperience'], (int) $experience->yearsOfExperience);
        $this->assertSame((int) $mock['soloProjects'], (int) $experience->soloProjects);
        $this->assertSame((int) $mock['collabProjects'], (int) $experience->collabProjects);
    }

    public function test_seeds_metrics_from_mock_data(): void
    {
        $mockRows = MockDataService::get('metrics');

        $this->assertNotEmpty($mockRows);

        $metrics = Metric::query()->orderBy('id')->get();

        $this->assertCount(count($mockRows), $metrics);

        foreach ($metrics as $index => $metric) {
            $mock = $mockRows[$index];

            $this->assertSame($mock['label'], $metric->label);
            $this->assertSame((int) $mock['value'], (int) $metric->value);
            $this->assertSame($mock['suffix'] ?? null, $metric->suffix);
            $this->assertSame($mock['metricDescription'], $metric->metricDescription);
        }
    }

    public function test_seeds_projects_from_mock_data(): void
    {
        $mockRows = MockDataService::get('projects');

        $this->assertNotEmpty($mockRows);

        $projects = Project::query()->orderBy('id')->get();

        $this->assertCount(count($mockRows), $projects);

        foreach ($projects as $index => $project) {
            $mock = $mockRows[$index];

            $this->assertSame($mock['title'], $project->title);
            $this->assertSame($mock['description'], $project->description);
            $this->assertSame($mock['technologies'], $project->technologies);
            $this->assertSame(
                ProjectStatus::from($mock['status']),
                $project->status,
                "project \"{$mock['title']}\" must carry a status the ProjectStatus enum knows"
            );
            $this->assertSame($mock['githubLink'], $project->githubLink);
            $this->assertSame($mock['demoLink'] ?? null, $project->demoLink);
            $this->assertSame($mock['image'], $project->image);
            $this->assertSame($mock['outcome'], $project->outcome);
            $this->assertSame($mock['metrics'], $project->metrics);
            $this->assertSame((bool) ($mock['featured'] ?? false), $project->featured);
        }
    }

    public function test_seeds_blogs_with_full_portfolio_content(): void
    {
        $mockRows = MockDataService::get('blogs');

        $this->assertNotEmpty($mockRows);

        $blogs = Blog::query()->orderBy('id')->get();

        $this->assertCount(count($mockRows), $blogs);

        foreach ($blogs as $index => $blog) {
            $mock = $mockRows[$index];

            $this->assertSame($mock['title'], $blog->title);
            $this->assertSame($mock['excerpt'], $blog->excerpt);
            $this->assertSame($mock['slug'], $blog->slug);
            $this->assertSame($mock['readTime'], $blog->readTime);
            // Full-fidelity body: this is the assertion the old inline seeder
            // would have failed — it shipped abridged content paragraphs.
            $this->assertSame($mock['content'], $blog->content);
            $this->assertSame(
                $mock['date'],
                $blog->date->format('Y-m-d'),
                "blog \"{$mock['slug']}\" date must match mock-data.json"
            );
        }
    }

    public function test_seeds_skills_singleton_from_mock_data(): void
    {
        $mockRows = MockDataService::get('skills');

        $this->assertCount(1, $mockRows, 'mock-data.json must contain exactly one skills row (the singleton)');

        $groups = ['proficient', 'familiar', 'authentication', 'architecture', 'toolsPlatforms', 'practices', 'ai'];

        // Exactly one document, keyed for the admin controller (audit Bug 5).
        $this->assertSame(1, Skill::query()->count());

        $skill = Skill::query()->where('key', Skill::SINGLETON_KEY)->first();

        $this->assertNotNull($skill, 'the skills singleton must carry key='.Skill::SINGLETON_KEY);

        foreach ($groups as $group) {
            $this->assertSame($mockRows[0][$group], $skill->{$group}, "skills.{$group} must match mock-data.json");
        }
    }

    public function test_seeds_tech_skills_from_mock_data(): void
    {
        $mockRows = MockDataService::get('tech_skills');

        $this->assertNotEmpty($mockRows);

        $skills = TechSkill::query()->orderBy('id')->get();

        $this->assertCount(count($mockRows), $skills);

        foreach ($skills as $index => $techSkill) {
            $mock = $mockRows[$index];

            $this->assertSame($mock['category'], $techSkill->category->value);
            $this->assertSame($mock['logo'], $techSkill->logo, "tech skill \"{$mock['label']}\" logo path");
            $this->assertSame($mock['label'], $techSkill->label);
        }
    }

    public function test_skills_ai_labels_match_tech_skill_labels(): void
    {
        // The portfolio's TechStack matches skill labels against tech-skill
        // labels via a normalized map; if the two files disagree, chips lose
        // their logos silently. Lock the known-overlapping groups here.
        $skill = Skill::query()->first();
        $techLabels = TechSkill::query()->get()->map(fn ($t) => $t->label)->all();

        $this->assertContains('Github Copilot', $techLabels, 'Copilot appears in both skills.ai and the AI tech-skill category');
        $this->assertContains('Claude', $techLabels);

        foreach (['Cursor', 'Bolt'] as $label) {
            $this->assertContains($label, $skill->ai);
            $this->assertContains($label, $techLabels);
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        // Re-run must not duplicate anything (truncate-and-recreate semantics).
        $before = [
            'projects' => Project::query()->count(),
            'blogs' => Blog::query()->count(),
            'metrics' => Metric::query()->count(),
            'tech_skills' => TechSkill::query()->count(),
            'experiences' => Experience::query()->count(),
            'skills' => Skill::query()->count(),
        ];

        $this->artisan('db:seed', ['--class' => 'ContentSeeder', '--force' => true]);

        $this->assertSame($before['projects'], Project::query()->count());
        $this->assertSame($before['blogs'], Blog::query()->count());
        $this->assertSame($before['metrics'], Metric::query()->count());
        $this->assertSame($before['tech_skills'], TechSkill::query()->count());
        $this->assertSame($before['experiences'], Experience::query()->count());
        $this->assertSame($before['skills'], Skill::query()->count());
    }
}
