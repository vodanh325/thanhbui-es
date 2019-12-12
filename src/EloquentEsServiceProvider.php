<?php namespace EloquentEs;

use EloquentEs\Client\Factory;
use EloquentEs\Client\Manage;
use Illuminate\Support\ServiceProvider;

/**
 * Class EloquentEsServiceProvider
 * @package EloquentEs
 */
class EloquentEsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config.php' => base_path('config/elastic.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ees.elastic', function($app) {
            $factory = new Factory();
            return new Manage($app, $factory);
        });
    }
}
