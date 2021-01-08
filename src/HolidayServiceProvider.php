<?php

namespace Aienming\HolidayManage;

use Illuminate\Support\ServiceProvider;

class HolidayServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations', 'holidayM');
    }

    public function register()
    {
        // 绑定实例到容器中
        $this->app->singleton('holidayM', function() {
            return new HolidayManage;
        });
    }
}