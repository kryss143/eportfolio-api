<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\Experience;
use App\Models\Metric;
use App\Models\Project;
use App\Models\TechSkill;
use App\Observers\ActivityObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private ?bool $mongoAvailable = null;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Computed lazily, only when an admin view renders, and memoized per
        // request so controllers, index-view action guards and the banner
        // share one availability probe. Bug #4: index views use this to hide
        // row actions that would 404/500 against read-only mock rows.
        View::composer('admin.*', function ($view) {
            $view->with('mongoAvailable', $this->mongoAvailable());
        });

        Blog::observe(ActivityObserver::class);
        Experience::observe(ActivityObserver::class);
        Metric::observe(ActivityObserver::class);
        Project::observe(ActivityObserver::class);
        TechSkill::observe(ActivityObserver::class);
    }

    /**
     * Is MongoDB reachable? Skipped entirely when the extension is missing;
     * result memoized for the rest of the request.
     */
    private function mongoAvailable(): bool
    {
        if ($this->mongoAvailable !== null) {
            return $this->mongoAvailable;
        }

        if (! extension_loaded('mongodb')) {
            return $this->mongoAvailable = false;
        }

        try {
            DB::connection('mongodb')->getMongoDB();

            return $this->mongoAvailable = true;
        } catch (\Throwable) {
            return $this->mongoAvailable = false;
        }
    }
}
