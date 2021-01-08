<?php

namespace Aienming\HolidayManage\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model {

    use SoftDeletes;

    protected $table = 'holiday';

    protected $guarded = [];

    // 处理时间
    public function getCreatedAtAttribute($val)
    {
        return Carbon::create($val)->toDateTimeString();
    }
    public function getUpdatedAtAttribute($val)
    {
        return Carbon::create($val)->toDateTimeString();
    }
}