<?php

namespace Aienming\HolidayManage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model {

    use SoftDeletes;

    protected $table = 'holiday';

    protected $guarded = [];
}