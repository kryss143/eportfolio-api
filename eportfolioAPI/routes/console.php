<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh GitHub-sourced metrics (Total Projects, Total Commits) into MongoDB
// on a 3–6 hour randomized cadence. The GitHub metrics:update command
// (app/Console/Commands/UpdateMetricsCommand.php) fetches live GitHub stats
// via GitHubMetricsService and writes them into the MongoDB `metrics`
// collection, so the frontend metrics section always reflects fresh data
// without each client hitting the rate-limited API directly.
//
// How the randomized cadence works:
//   - 'everyThreeHours(1)' emits the cron '1 */3 * * *'. This anchors the
//     schedule to the 3-hour boundary (hours 1, 4, 7, …, 22).
//   - The 'when' callback randomly skips every 3rd-hour slot, so consecutive
//     executions land 3 or 6 hours apart. This spreads the GitHub Search API
//     quota across the fleet and avoids a thundering-herd of requests at the
//     same wall-clock moment, while keeping the effective interval between 3
//     and 6 hours (average 4.5 hours).
//   - 'app()->environment(\'local\')' forces the command to run on every slot
//     in local/dev so the metrics stay in sync during development.
Schedule::command('metrics:update')
    ->everyThreeHours(1)
    ->onFailure(fn () => logger('metrics:update command failed'))
    ->when(fn (): bool => app()->environment('local') || rand(0, 2) !== 0);
