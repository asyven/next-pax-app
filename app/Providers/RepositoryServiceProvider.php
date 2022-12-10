<?php

namespace App\Providers;

use App\Entities\Availability;
use App\Entities\Price;
use App\Repositories\AvailabilityRepository;
use App\Repositories\PriceRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    private const ENTITY_REPOSITORY_MAP = [
        Price::class => PriceRepository::class,
        Availability::class => AvailabilityRepository::class,
    ];

    public function register(): void
    {
        foreach (self::ENTITY_REPOSITORY_MAP as $class => $repository) {
            $this->app->bind(
                $repository,
                function (Application $app) use ($repository, $class) {
                    return new $repository(
                        $app['em'],
                        $app['em']->getClassMetaData($class)
                    );
                }
            );

            $this->app->bind(
                $repository . 'Interface',
                function (Application $app) use ($repository, $class) {
                    return new $repository(
                        $app['em'],
                        $app['em']->getClassMetaData($class)
                    );
                }
            );
        }
    }
}
