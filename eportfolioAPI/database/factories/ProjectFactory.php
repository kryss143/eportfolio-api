<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);
        $status = fake()->randomElement(['built', 'in-progress']);

        $techStacks = [
            ['React', 'Tailwind CSS', 'Node.js', 'Express.js', 'PostgreSQL'],
            ['Vue', 'DaisyUI', 'Firebase', 'Firestore'],
            ['Next.js', 'Supabase', 'TypeScript'],
            ['Angular', 'DaisyUI', '.NET', 'MongoDB', 'Prisma'],
            ['React', 'TailwindCSS', 'Express.js', 'Firebase', 'Firestore'],
        ];

        $technologies = fake()->randomElement($techStacks);

        return [
            'title' => $title,
            'description' => fake()->paragraph(2),
            'technologies' => $technologies,
            'status' => $status,
            'githubLink' => fake()->optional(0.8)->url(),
            'demoLink' => $status === 'built' ? fake()->optional(0.7)->url() : null,
            'image' => '/projects/'.str($title)->slug().'.webp',
            'outcome' => fake()->paragraph(1),
            'metrics' => [
                fake()->sentence(3),
                fake()->sentence(3),
                fake()->sentence(3),
            ],
            'featured' => fake()->boolean(30),
        ];
    }

    public function built(): static
    {
        return $this->state(fn () => ['status' => 'built']);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => 'in-progress']);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
