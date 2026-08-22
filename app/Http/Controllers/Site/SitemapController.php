<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\PlantStatus;
use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Http\Request;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\HttpFoundation\Response;

final class SitemapController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $sitemap = Sitemap::create();

        $staticPages = [
            ['name' => 'site.home', 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY, 'priority' => 1.0],
            ['name' => 'site.developers', 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY, 'priority' => 0.8],
            ['name' => 'site.contribute', 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY, 'priority' => 0.6],
            ['name' => 'site.about', 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY, 'priority' => 0.5],
            ['name' => 'site.impressum', 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY, 'priority' => 0.2],
            ['name' => 'site.datenschutz', 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY, 'priority' => 0.2],
        ];

        foreach ($staticPages as $page) {
            $sitemap->add(
                Url::create(route($page['name']))
                    ->setChangeFrequency($page['changefreq'])
                    ->setPriority($page['priority']),
            );
        }

        Plant::query()
            ->where('status', PlantStatus::Approved)
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('slug')
            ->lazy()
            ->each(function (Plant $plant) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('site.plants.show', $plant->slug))
                        ->setLastModificationDate($plant->updated_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7),
                );
            });

        return $sitemap->toResponse($request);
    }
}
