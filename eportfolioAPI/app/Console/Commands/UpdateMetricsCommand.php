<?php

namespace App\Console\Commands;

use App\Models\Metric;
use App\Services\GitHubMetricsService;
use App\Support\MongoProbe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command: php artisan metrics:update
 *
 * Fetches live GitHub stats (own-repo count, total commits) and writes them
 * into the MongoDB `metrics` collection. The MetricController serves these
 * values through GET /api/v1/metrics, so the frontend always reflects fresh
 * GitHub data without each client hitting the rate-limited API directly.
 *
 * Scheduled to run every 3 hours, randomized by up to 3 hours (effective
 * interval 3–6 hours) so that the 30 req/min search quota is shared across
 * the fleet and never hammered at the same wall-clock moment.
 */
class UpdateMetricsCommand extends Command
{
    protected $signature = 'metrics:update {--dry-run : Show what would change without writing to MongoDB}';

    protected $description = 'Fetch live GitHub metrics and update MongoDB';

    /** @var array<string,int> Map of metric label => value to update. */
    protected array $labelUpdates = [
        'Total Projects' => 0, // ownRepoCount — set at runtime
        'Total Commits' => 0,  // total commits — set at runtime
    ];

    public function handle(GitHubMetricsService $service): int
    {
        if (! $service->isConfigured()) {
            $this->warn('GitHub credentials are not configured (GITHUB_USERNAME missing). Nothing to do.');

            return self::SUCCESS;
        }

        if (! MongoProbe::writeAvailable()) {
            Log::warning('metrics:update skipped — MongoDB primary is not writable.');

            $this->error('MongoDB is not writable — skipping metric update.');

            return self::FAILURE;
        }

        $metrics = $service->fetchMetrics();

        $this->labelUpdates['Total Projects'] = $metrics['ownRepoCount'];
        $this->labelUpdates['Total Commits'] = $metrics['total'];

        $updated = $this->updateMetrics();

        $this->info("Updated {$updated} metric(s) from GitHub:");
        foreach ($this->labelUpdates as $label => $value) {
            $suffix = $label === 'Total Commits' ? '+' : '';
            $this->line("  {$label}: {$value}{$suffix}");
        }

        return self::SUCCESS;
    }

    /**
     * Write each label/value pair into the MongoDB `metrics` collection.
     *
     * @return int Number of documents actually updated.
     */
    protected function updateMetrics(): int
    {
        if ($this->option('dry-run')) {
            $this->info('[dry-run] No writes performed.');

            return 0;
        }

        $updated = 0;

        foreach ($this->labelUpdates as $label => $value) {
            $metric = Metric::where('label', $label)->first();

            if ($metric === null) {
                $this->warn("Metric with label '{$label}' not found in MongoDB — creating it.");

                $metric = Metric::create([
                    'label' => $label,
                    'value' => 0,
                    'suffix' => $label === 'Total Commits' ? '+' : null,
                    'metricDescription' => '',
                ]);
            }

            $metric->value = $value;
            $metric->save();
            $updated++;
        }

        return $updated;
    }
}
