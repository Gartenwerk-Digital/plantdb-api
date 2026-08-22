<?php

declare(strict_types=1);

use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\SitePageController;
use App\Http\Controllers\Site\SitePlantController;
use Illuminate\Support\Facades\Route;

Route::redirect('/docs', '/docs/api');

Route::get('/sitemap.xml', SitemapController::class)->name('site.sitemap');

Route::get('/', [SitePageController::class, 'home'])->name('site.home');
Route::get('/developers', [SitePageController::class, 'developers'])->name('site.developers');
Route::get('/contribute', [SitePageController::class, 'contribute'])->name('site.contribute');
Route::get('/about', [SitePageController::class, 'about'])->name('site.about');
Route::get('/impressum', [SitePageController::class, 'impressum'])->name('site.impressum');
Route::get('/datenschutz', [SitePageController::class, 'datenschutz'])->name('site.datenschutz');
Route::get('/plants/{slug}', [SitePlantController::class, 'show'])->name('site.plants.show');
