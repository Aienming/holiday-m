<?php

namespace Aienming\HolidayManage;

use Illuminate\Support\ServiceProvider;

class HolidayServiceProvider extends ServiceProvider {

    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton('holidayM', function() {
            return new HolidayManage;
        });
    }
}