<?php

namespace Database\Factories;

use App\Enums\TechCategory;
use App\Models\TechSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TechSkill>
 */
class TechSkillFactory extends Factory
{
    protected $model = TechSkill::class;

    protected static array $labels = [];

    protected static int $labelIndex = 0;

    public function definition(): array
    {
        $skills = [
            TechCategory::Frontend => [
                ['logo' => './devicons/react-original.svg', 'label' => 'React'],
                ['logo' => './devicons/angular-original.svg', 'label' => 'Angular'],
                ['logo' => './devicons/vuejs-original.svg', 'label' => 'Vue'],
                ['logo' => './devicons/svelte-original.svg', 'label' => 'Svelte'],
                ['logo' => './devicons/tailwindcss-original.svg', 'label' => 'TailwindCSS'],
            ],
            TechCategory::Backend => [
                ['logo' => './devicons/express-original.svg', 'label' => 'Express.js'],
                ['logo' => './devicons/php-original.svg', 'label' => 'PHP'],
                ['logo' => './devicons/nodejs-original.svg', 'label' => 'Node.js'],
                ['logo' => './devicons/python-original.svg', 'label' => 'Python'],
                ['logo' => './devicons/csharp-original.svg', 'label' => 'C#'],
                ['logo' => './devicons/dot-net-original.svg', 'label' => '.NET'],
            ],
            TechCategory::Fullstack => [
                ['logo' => './devicons/nextjs-original.svg', 'label' => 'Next.js'],
                ['logo' => './devicons/nuxtjs-original.svg', 'label' => 'Nuxt.js'],
                ['logo' => './devicons/laravel-original.svg', 'label' => 'Laravel'],
            ],
            TechCategory::CICD => [
                ['logo' => './devicons/azuredevops-original.svg', 'label' => 'Microsoft Azure DevOps'],
                ['logo' => './devicons/githubactions-original.svg', 'label' => 'GitHub Actions'],
                ['logo' => './devicons/docker-original.svg', 'label' => 'Docker'],
            ],
            TechCategory::AI => [
                ['logo' => './devicons/copilot-original.svg', 'label' => 'GitHub Copilot'],
                ['logo' => './devicons/claude-original.svg', 'label' => 'Claude'],
            ],
            TechCategory::Database => [
                ['logo' => './devicons/sqldeveloper-original.svg', 'label' => 'SQL'],
                ['logo' => './devicons/mysql-original.svg', 'label' => 'MySQL'],
                ['logo' => './devicons/postgresql-original.svg', 'label' => 'PostgreSQL'],
                ['logo' => './devicons/mongodb-original.svg', 'label' => 'MongoDB'],
                ['logo' => './devicons/firebase-original.svg', 'label' => 'Firebase'],
            ],
            TechCategory::Deploy => [
                ['logo' => './devicons/vercel-original.svg', 'label' => 'Vercel'],
                ['logo' => './devicons/netlify-original.svg', 'label' => 'Netlify'],
                ['logo' => './devicons/github-original.svg', 'label' => 'GitHub'],
                ['logo' => './devicons/git-original.svg', 'label' => 'Git'],
            ],
            TechCategory::Fundamental => [
                ['logo' => './devicons/html5-original.svg', 'label' => 'HTML5'],
                ['logo' => './devicons/css3-original.svg', 'label' => 'CSS3'],
                ['logo' => './devicons/javascript-original.svg', 'label' => 'JavaScript'],
                ['logo' => './devicons/typescript-original.svg', 'label' => 'TypeScript'],
            ],
        ];

        // Pick a random category and skill
        $category = fake()->randomElement(TechCategory::cases());
        $skill = fake()->randomElement($skills[$category]);

        return [
            'category' => $category,
            'logo' => $skill['logo'],
            'label' => $skill['label'],
        ];
    }
}
