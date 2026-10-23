<?php

namespace Jlrodriguezdev\BotProtection;

use Illuminate\Support\ServiceProvider;

class BotProtectionServiceProvider extends ServiceProvider
{
    /**
     * Register package services and configurations.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/bot-protection.php',
            'bot-protection'
        );
    }

    /**
     * Bootstrap package resources.
     */
    public function boot()
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/bot-protection.php' => config_path('bot-protection.php'),
            ], 'bot-protection-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/bot-protection'),
            ], 'bot-protection-views');
        }

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bot-protection');
    }
}
