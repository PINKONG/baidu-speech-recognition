<?php

namespace Pinkong\BaiduSpeechRecognition\Providers;

use Illuminate\Support\ServiceProvider;
use Pinkong\BaiduSpeechRecognition\Services\BaiduSpeechService;

class BaiduSpeechServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/baiduspeech.php' => config_path('baiduspeech.php')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('BaiduSpeechHelper', function () {
            return new BaiduSpeechService();
        });
    }

}
