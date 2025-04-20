<?php

namespace App\Providers;

use App\Repositories\ImportProcessErrorRepository\ImportProcessErrorRepositoryEloquent;
use App\Repositories\ImportProcessErrorRepository\ImportProcessErrorRepositoryInterface;
use App\Repositories\ImportProcessRepository\ImportProcessRepositoryEloquent;
use App\Repositories\ImportProcessRepository\ImportProcessRepositoryInterface;
use App\Repositories\JobsRepository\JobRepositoryInterface;
use App\Repositories\JobsRepository\JobRepositoryQueryBuilder;
use App\Repositories\UserRepository\UserRepositoryEloquent;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepositoryEloquent::class
        );

        $this->app->bind(
            ImportProcessRepositoryInterface::class,
            ImportProcessRepositoryEloquent::class
        );

        $this->app->bind(
            ImportProcessErrorRepositoryInterface::class,
            ImportProcessErrorRepositoryEloquent::class
        );

        $this->app->bind(
            JobRepositoryInterface::class,
            JobRepositoryQueryBuilder::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
