<?php

namespace Gmlo\DataTable;

use Gmlo\DataTable\Services\DataTable;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        require __DIR__ . '/functions.php';
        $this->app->singleton('dataTable', function(){
            return new DataTable;
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../resources/js' => resource_path('js/vendor/datatable')
        ], 'vue-components');
    }
}