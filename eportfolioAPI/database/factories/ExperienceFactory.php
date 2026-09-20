<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    public function definition(): array
    {
        return [
            'id' => 'primary',
            'position' => fake()->randomElement([
                'Full-Stack Developer',
                'Frontend Developer',
                'Backend Developer',
                'Software Engineer',
            ]),
            'yearsOfExperience' => fake()->numberBetween(1, 8),
            'soloProjects' => fake()->numberBetween(1, 10),
            'collabProjects' => fake()->numberBetween(1, 10),
        ];
    }
}
