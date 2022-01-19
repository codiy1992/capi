<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        \Illuminate\Translation\Translator::macro('transCode', function (int $code, array $msg_args = []) {
            $msg = trans('code.' . $code);
            if (!empty($msg_args)) {
                $msg = vsprintf($msg, $msg_args);
            }
            return $msg;
        });

        \Laravel\Sanctum\Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
