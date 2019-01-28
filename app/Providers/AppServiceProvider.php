<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    protected $providers = [
        \Barryvdh\Cors\ServiceProvider::class,
        \Tymon\JWTAuth\Providers\LumenServiceProvider::class,/*jwt认证*/
        \GrahamCampbell\Throttle\ThrottleServiceProvider::class,/*访问频率限制*/
    ];

    /*开发环境的服务提供者*/
    protected $testProviders = [
        \Clockwork\Support\Lumen\ClockworkServiceProvider::class,/*api调试*/
        \Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $local = getenv('APP_ENV', 'local') == 'local';

        $providers = $local ? array_merge($this->providers, $this->testProviders) : $this->providers;
        foreach ($providers as $provider) {
            app()->register($provider);
        }
    }

    public function boot()
    {
        Schema::defaultStringLength(191);/*mysql版本低于5.7需设置*/
    }
}
