<?php

namespace FarLab\Drivers\Line\Providers;

use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Studio\Providers\StudioServiceProvider;
use FarLab\Drivers\Line\LineAudioDriver;
use FarLab\Drivers\Line\LineDriver;
use FarLab\Drivers\Line\LineEventDriver;
use FarLab\Drivers\Line\LineFileDriver;
use FarLab\Drivers\Line\LineImageDriver;
use FarLab\Drivers\Line\LineLocationDriver;
use FarLab\Drivers\Line\LineVideoDriver;
use Illuminate\Support\ServiceProvider;

class LineServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->isRunningInBotManStudio()) {
            $this->loadDrivers();

            $this->publishes([
                __DIR__ . '/../../stubs/line.php' => config_path('botman/line.php'),
            ]);

            $this->mergeConfigFrom(__DIR__ . '/../../stubs/line.php', 'botman.line');
        }
    }

    /**
     * @return bool
     */
    protected function isRunningInBotManStudio()
    {
        return class_exists(StudioServiceProvider::class);
    }

    /**
     * Load BotMan drivers.
     */
    protected function loadDrivers()
    {
        DriverManager::loadDriver(LineDriver::class);
        DriverManager::loadDriver(LineAudioDriver::class);
        DriverManager::loadDriver(LineEventDriver::class);
        DriverManager::loadDriver(LineFileDriver::class);
        DriverManager::loadDriver(LineImageDriver::class);
        DriverManager::loadDriver(LineLocationDriver::class);
        DriverManager::loadDriver(LineVideoDriver::class);
    }
}