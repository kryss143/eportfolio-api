<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        return [
            'proficient' => [
                'JavaScript', 'TypeScript', 'React', 'Vue', 'Node.js',
                'Express.js', 'HTML5', 'CSS3', 'TailwindCSS', 'PostgreSQL',
                'SQL', 'REST API Design', 'Git', 'GitHub',
            ],
            'familiar' => [
                'Next.js', 'Angular', 'PHP', 'Laravel', 'Redux',
                'MongoDB', 'Supabase', 'MySQL', 'Python', 'C#', '.NET',
            ],
            'authentication' => [
                'Firebase Authentication', 'JWT', 'Session Management',
                'Role-Based Access Control (RBAC)', 'CORS',
            ],
            'architecture' => [
                'Lazy Loading', 'Code Splitting', 'Async/Await',
                'State Management', 'MVC Pattern', 'Clean Architecture',
            ],
            'toolsPlatforms' => [
                'GitHub Actions', 'Vercel', 'Firebase Hosting', 'Render',
                'Postman', 'Netlify',
            ],
            'practices' => [
                'Agile/Scrum', 'CI/CD', 'Mobile-First Responsive Design',
                'Version Control', 'Code Review',
            ],
            'ai' => ['GitHub Copilot', 'Claude', 'Cursor', 'Bolt'],
        ];
    }
}
