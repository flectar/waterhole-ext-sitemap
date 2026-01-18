<?php

namespace Flectar\Sitemap;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Flectar\Sitemap\Http\Controllers\SitemapController;

class SitemapServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(
            __DIR__ . "/../resources/views",
            "waterhole-sitemap",
        );

        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::prefix("sitemap")
            ->middleware("web")
            ->group(function () {
                Route::get("/", [SitemapController::class, "index"]);
                Route::get("/posts", [SitemapController::class, "posts"]);
                Route::get("/channels", [SitemapController::class, "channels"]);
                Route::get("/pages", [SitemapController::class, "pages"]);
                Route::get("/users", [SitemapController::class, "users"]);
            });
    }
}
