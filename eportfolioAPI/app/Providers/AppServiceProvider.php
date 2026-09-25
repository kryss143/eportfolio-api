<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\Experience;
use App\Models\Metric;
use App\Models\Project;
use App\Models\TechSkill;
use App\Observers\ActivityObserver;
use App\Support\MongoProbe;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Computed lazily, only when an admin view renders, and memoized per
        // request so controllers, index-view action guards and the banners
        // share one availability probe. Bug #4: index views use these to hide
        // row actions that would fail — mock rows when Mongo is down entirely
        // (mongoAvailable), real rows during a primary partition
        // (mongoWritable, strict-primary probe).
        View::composer('admin.*', function ($view) {
            $view->with('mongoAvailable', $this->mongoAvailable());
            $view->with('mongoWritable', MongoProbe::writeAvailable());
        });

        Blog::observe(ActivityObserver::class);
        Experience::observe(ActivityObserver::class);
        Metric::observe(ActivityObserver::class);
        Project::observe(ActivityObserver::class);
        TechSkill::observe(ActivityObserver::class);
    }

    /**
     * Is MongoDB reachable? Skipped entirely when the extension is missing;
     * result memoized for the rest of the request (per-process static).
     *
     * Bug #2 (2026-09-25): getMongoDB() was both deprecated and, like
     * getDatabase(), I/O-free. MongoProbe::available() pings the server.
     */
    private function mongoAvailable(): bool
    {
        return MongoProbe::available();
    }
}
