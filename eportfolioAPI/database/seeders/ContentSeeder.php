<?php

namespace Database\Seeders;

use App\Enums\TechCategory;
use App\Models\Blog;
use App\Models\Experience;
use App\Models\Metric;
use App\Models\Project;
use App\Models\Skill;
use App\Models\TechSkill;
use App\Services\MockDataService;
use App\Support\MongoProbe;
use Illuminate\Database\Seeder;

/**
 * Seeds MongoDB with the portfolio's real content.
 *
 * Single source of truth: database/mock-data.json — a faithful port of
 * eportfolio/portfolio/src/contents/* (experience, metrics, projects, blogs,
 * skills, tech-skill chips). The old inline PHP arrays drifted from the
 * portfolio copy (abridged blog bodies, an outdated project blurb, a
 * nonexistent copilot devicon path), so the DB, the degraded-mode fallback
 * (FallbackData → MockDataService) and the portfolio no longer agreed.
 * Seeding from the same file the fallback serves makes drift structurally
 * impossible: edit the portfolio contents first, mirror it here once, and
 * both the healthy and degraded API paths serve identical content.
 *
 * The mock rows' "id" / "created_at" / "updated_at" keys are fallback-mode
 * bookkeeping (stable ids for the detail endpoints, deterministic timestamps)
 * and are intentionally NOT seeded: MongoDB documents get real ObjectId keys,
 * and the API Resources expose no timestamps.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // Check THIS connection explicitly: a generic Throwable catch alone
        // would hide a misconfigured app (e.g. an unreachable MongoDB URI
        // silently seeding nothing while the app serves mock data).
        if (! MongoProbe::available()) {
            $this->command?->warn('ContentSeeder skipped: MongoDB not available. API will use mock-data.json fallback.');

            return;
        }

        $this->seedExperience();
        $this->seedMetrics();
        $this->seedProjects();
        $this->seedBlogs();
        $this->seedSkills();
        $this->seedTechSkills();
    }

    protected function seedExperience(): void
    {
        Experience::truncate();

        $row = MockDataService::findById('experiences', 'primary');

        if ($row === null) {
            $this->command?->error('ContentSeeder: mock-data.json is missing the "primary" experience row.');

            return;
        }

        Experience::create([
            'position' => $row['position'],
            'yearsOfExperience' => (int) $row['yearsOfExperience'],
            'soloProjects' => (int) $row['soloProjects'],
            'collabProjects' => (int) $row['collabProjects'],
        ]);
    }

    protected function seedMetrics(): void
    {
        Metric::truncate();

        foreach (MockDataService::get('metrics') as $row) {
            Metric::create([
                'label' => $row['label'],
                'value' => (int) $row['value'],
                'suffix' => $row['suffix'] ?? null,
                'metricDescription' => $row['metricDescription'],
            ]);
        }
    }

    protected function seedProjects(): void
    {
        Project::truncate();

        foreach (MockDataService::get('projects') as $row) {
            Project::create([
                'title' => $row['title'],
                'description' => $row['description'],
                'technologies' => $row['technologies'],
                // Validated against the ProjectStatus enum when the model
                // casts it — a typo in mock-data.json must fail loudly here,
                // not surface as a broken status badge in production.
                'status' => $row['status'],
                'githubLink' => $row['githubLink'],
                'demoLink' => $row['demoLink'] ?? null,
                'image' => $row['image'],
                'outcome' => $row['outcome'],
                'metrics' => $row['metrics'],
                'featured' => (bool) ($row['featured'] ?? false),
            ]);
        }
    }

    protected function seedBlogs(): void
    {
        Blog::truncate();

        foreach (MockDataService::get('blogs') as $row) {
            Blog::create([
                'title' => $row['title'],
                'excerpt' => $row['excerpt'],
                'date' => $row['date'],
                'readTime' => $row['readTime'],
                'slug' => $row['slug'],
                'content' => $row['content'],
            ]);
        }
    }

    protected function seedSkills(): void
    {
        Skill::truncate();

        $rows = MockDataService::get('skills');

        if (count($rows) !== 1) {
            $this->command?->error('ContentSeeder: mock-data.json must contain exactly one skills row (the singleton).');

            return;
        }

        $row = $rows[0];

        // Audit post-mongo Bug 5: seed the singleton via its fixed `key` so
        // the document is the exact one the admin controller targets. The
        // `key` is attached here rather than stored in mock-data.json, which
        // the degraded mode serves verbatim and clients never need.
        Skill::create(array_merge(['key' => Skill::SINGLETON_KEY], [
            'proficient' => $row['proficient'],
            'familiar' => $row['familiar'],
            'authentication' => $row['authentication'],
            'architecture' => $row['architecture'],
            'toolsPlatforms' => $row['toolsPlatforms'],
            'practices' => $row['practices'],
            'ai' => $row['ai'],
        ]));
    }

    protected function seedTechSkills(): void
    {
        TechSkill::truncate();

        foreach (MockDataService::get('tech_skills') as $row) {
            TechSkill::create([
                // FromString keeps the file's plain category strings honest —
                // an unknown category throws instead of silently writing a
                // value the TechSkillController's category filter can't match.
                'category' => TechCategory::from($row['category']),
                'logo' => $row['logo'],
                'label' => $row['label'],
            ]);
        }
    }
}
