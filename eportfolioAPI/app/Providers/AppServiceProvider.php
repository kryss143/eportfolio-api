<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\Experience;
use App\Models\Metric;
use App\Models\Project;
use App\Models\TechSkill;
use App\Observers\ActivityObserver;
use App\Support\MongoProbe;
use Illuminate\Support\Facades\URL;
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
        // Bug (2026-10-07): when a request arrives as /index.php/* (e.g. nginx
        // try_files fallback), Laravel's URI root keeps an "index.php" segment
        // and generated links become /index.php/admin/login — which our nginx
        // then mishandled, landing on the home page. Canonicalize to the bare
        // request root so every url()/route() link is index.php-free.
        if (str_contains(request()->getRequestUri(), '/index.php')) {
            URL::forceRootUrl(rtrim(str_replace('/index.php', '', request()->getSchemeAndHttpHost() . request()->getBasePath()), '/'));
        }

        // Computed lazily, only when an admin view renders, and memoized per
        // request so controllers, index-view action guards and the banners
        // share one availability probe. Bug #4: index views use these to hide
        // row actions that would fail — mock rows when Mongo is down entirely
        // (mongoAvailable), real rows during a primary partition
        // (mongoWritable, strict-primary probe).
        //
        // Audit r3 F2 (2026-09-28): scoped to the views that actually consume
        // the variables — the shared layout (banner) and the index views (row
        // action gating). The previous 'admin.*' wildcard fired the
        // write-probe on the public login page and every create/edit form,
        // adding up to a full probe cycle (~2.5s when Mongo is down) to
        // unauthenticated traffic for data those views never read.
        View::composer(['admin.layouts.app', 'admin.*.index'], function ($view) {
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
