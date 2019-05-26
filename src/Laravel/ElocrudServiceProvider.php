<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 11:23 AM
 */

namespace Angujo\Elocrud\Laravel;


use Angujo\Elocrud\Laravel\Factory as Elocrud;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ElocrudServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/elocrud.php' => config_path('elocrud.php'),
            ], 'elocrud-config');
            $this->commands([ConsoleCommands::class]);
        }
    }

    public function register()
    {
        $this->app->singleton(Elocrud::class, function (Application $app) {
            return new Elocrud($app->make('db'), $app->make('config')->get('elocrud'));
        });
    }

    public function provides()
    {
        return [Elocrud::class];
    }
}