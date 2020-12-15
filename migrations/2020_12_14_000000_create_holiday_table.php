<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHolidayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('holiday')) {
            Schema::create('holiday', function(Blueprint $table) {
                $table->id();
                $table->string('holiday', 100)->comment('节假日名称');
                $table->string('remark', 255)->comment('备注');
                $table->date('start')->comment('开始时间');
                $table->date('end')->comment('结束时间');
                $table->string('lieuDay', 100)->comment('调休日');
                $table->timestamps(0);
                $table->softDeletes('deleted_at', 0);
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dorp('holiday');
    }
}
