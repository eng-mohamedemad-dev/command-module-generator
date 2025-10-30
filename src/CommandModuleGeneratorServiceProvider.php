<?php

namespace CommandModuleGenerator;

use Illuminate\Support\ServiceProvider;

class CommandModuleGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // نشر ملف الكونفج
        $this->publishes([
            __DIR__.'/../config/module-generator.php' => config_path('module-generator.php'),
        ], 'command-module-generator-config');
        // نشر stubs
        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/command-module-generator/')
        ], 'command-module-generator-stubs');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \CommandModuleGenerator\Commands\MakeModuleCommand::class,
                \CommandModuleGenerator\Commands\DeleteModuleCommand::class,
            ]);
        }
        // نشر stubs لاحقاً
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/module-generator.php', 'module-generator'
        );
    }
}
