<?php

namespace Aienming\HolidayManage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * 提供facade类
 *
 * Class HolidayManage
 * @package Aienming\HolidayManage\Facades
 */
class HolidayManage extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'holidayM';
    }
}