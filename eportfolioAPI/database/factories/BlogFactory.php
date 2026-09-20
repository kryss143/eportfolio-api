<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'excerpt' => fake()->paragraph(1),
            'date' => fake()->dateTimeBetween('-6 months', 'now'),
            'readTime' => fake()->numberBetween(3, 12).' min read',
            'slug' => str($title)->slug()->toString(),
            'content' => '<p>'.fake()->paragraph(4, true).'</p><p>'.fake()->paragraph(4, true).'</p>',
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'date' => null,
        ]);
    }
}
