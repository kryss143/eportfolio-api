<?php

namespace Database\Factories;

use App\Models\Metric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Metric>
 */
class MetricFactory extends Factory
{
    protected $model = Metric::class;

    protected static array $ids = ['releases', 'checks', 'performance', 'commits'];

    protected static int $idIndex = 0;

    public function definition(): array
    {
        $id = self::$ids[self::$idIndex % count(self::$ids)];
        self::$idIndex++;

        $data = match ($id) {
            'releases' => [
                'label' => 'Total Projects',
                'value' => fake()->numberBetween(3, 12),
                'suffix' => null,
                'metricDescription' => 'Product-style builds spanning frontend, backend, database, and deployment workflows.',
            ],
            'checks' => [
                'label' => 'Ongoing',
                'value' => fake()->numberBetween(0, 3),
                'suffix' => null,
                'metricDescription' => 'Active project work that keeps the portfolio iterative and current.',
            ],
            'performance' => [
                'label' => 'Live Demo/s Available',
                'value' => fake()->numberBetween(2, 8),
                'suffix' => null,
                'metricDescription' => 'Public demos recruiters can review without navigating repositories.',
            ],
            'commits' => [
                'label' => 'Total Commits',
                'value' => fake()->numberBetween(100, 500),
                'suffix' => '+',
                'metricDescription' => 'Visible delivery cadence across shipped and in-progress work.',
            ],
        };

        return array_merge(['id' => $id], $data);
    }
}
