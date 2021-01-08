<?php

namespace Aienming\HolidayManage;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Aienming\HolidayManage\Models\Holiday;

/**
 * 节假日管理服务
 *
 * Class DateService
 * @package App\Services
 */
class HolidayManage
{
    protected $repository;

    /**
     * 判断是否为工作日
     *
     * @param $date
     * @return bool
     * @author xuxiaoming
     * @datetime 2020/12/9 15:47
     */
    public static function isWorkDay($date)
    {
        try {
            if (is_string($date)) {
                // 转化为carbon
                $date = Carbon::create($date);
                // 检查是否和设置的节假日冲突
                $self = new self();
                $holidays = $self->holidaysInyear([$date->year]);

                $isWorkDay = $self->compareWithHoliday($date, $holidays);

                return $isWorkDay;
            }

        } catch (\Exception $e) {
            return $e;
        }

    }

    /**
     * 获取一段时间内的工作日天数
     *
     * @param $start
     * @param $end
     * @return int
     * @author xuxiaoming
     * @datetime 2020/12/10 10:17
     */
    public static function numberOfWorkDay($start, $end)
    {
        try {
            $start = is_string($start) ? Carbon::create($start) : $start;
            $end = is_string($end) ? Carbon::create($end) : $end;

            $self = new self();
            $holidays = $self->holidaysInTimeBucket($start, $end);

            // 生成CarbonPeriod实例，相当于laravel的集合，以下方为例集合中将包含十个元素
            // $a = Carbon::parse('2021-01-1')->daysUntil('2021-1-10');

            // 此方法间隔一天但不包含最后一天，比如：1-1 -> 1-10，不会循环到1-10这一天
            return $start->diffFiltered(CarbonInterval::dayz(), function ($date) use ($holidays, $self) {

                $isWorkDay = $self->compareWithHoliday($date, $holidays);

                return $isWorkDay;
            }, $end->addDay(1));

        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * 根据指定日期的carbon与数据库取出的节假日信息比较确定是否为工作日
     *
     * @param Carbon $date
     * @param $holidays
     * @return bool
     * @author xuxiaoming
     * @datetime 2020/12/10 10:17
     */
    protected function compareWithHoliday(Carbon $date, $holidays)
    {
        $isWorkDay = $date->isWeekday();
        foreach ($holidays as $val) {
            $start = Carbon::create($val->start);
            $end = Carbon::create($val->end);
            // 是否为休息日
            $isHoliday = $date->between($start, $end);
            if ($isHoliday) {
                $isWorkDay = false;
                break;
            }
            // 是否为调休日
            if ($val->lieuDay) {
                $isLieuDay = in_array($date->toDateString(), (new self())->convertLieuDayToArray($val->lieuDay));
                if ($isLieuDay) {
                    $isWorkDay = true;
                    break;
                }
            }
        }
//        dump($date->toDateString());
//        dump($isWorkDay);
        return $isWorkDay;
    }

    /**
     * 根据年份数组取出已设置的节假日
     *
     * @param array $years
     * @return mixed
     * @author xuxiaoming
     * @datetime 2020/12/9 15:48
     */
    protected function holidaysInYear($years=[])
    {
        $years = empty($years) ? [Date('Y')] : $years;

        $holidays = collect([]);
        foreach ($years as $year) {
            $itemHoliday = Holiday::whereRaw('YEAR(start) = ' . $year)->get();

            $holidays = $holidays->merge($itemHoliday);
        }

        return $holidays;
    }

    /**
     * 取出指定时间段的节假日信息
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     * @author xuxiaoming
     * @datetime 2020/12/9 17:33
     */
    protected function holidaysInTimeBucket(Carbon $start, Carbon $end)
    {
        return Holiday::whereDate('start', '<=', $end)
            ->whereDate('start', '>=', $start)
            ->get();
    }

    /**
     * 转换调休日为数组格式
     *
     * @param String $lieuDay
     * @return array
     * @author xuxiaoming
     * @datetime 2020/12/9 15:48
     */
    protected function convertLieuDayToArray(String $lieuDay)
    {
        return explode('，', $lieuDay);
    }

    /**
     * 添加或更新一个节假日
     *
     * @param String $start
     * @param String $end
     * @param array $params
     * @param int $id
     * @return mixed
     * @author xuxiaoming
     * @datetime 2020/12/15 11:22
     */
    public function createOrUpdate(String $start, String $end, Array $params, int $id=null)
    {

        $start = Carbon::create($start);
        $end = Carbon::create($end);
        // 根据参数确定是否有重复节假日的时间存在，如果存在给与提醒并不允许插入新的数据
        $isExit = Holiday::where(function ($query) use ($start) {
            return $query->whereDate('start', '<=', $start->toDateString())
                ->whereDate('end', '>=', $start->toDateString());
        })->orWhere(function ($query) use ($end) {
            return $query->whereDate('start', '<=', $end->toDateString())
                ->whereDate('end', '>=', $end->toDateString());
        })->get();

        if($isExit->isNotEmpty() && !$id) {
            return ['result'=>false,'error' => '该节假日和已有的节假日冲突，不允许添加！'];
        }

        // 如果新增的假日与已有的调休日冲突了，也不予以插入
        $years = $start->year .','. $end->year;

        $lieuDays = Holiday::whereRaw('YEAR(start) in (' . $years .')')
            ->orWhereRaw('YEAR(end) in (' . $years .')')
            ->pluck('lieuDay');

        foreach ($lieuDays as $lieus) {
            if($lieus) {
                $lieuArr = explode('，', $lieus);
                foreach ($lieuArr as $lieu) {
                    $isBetween = Carbon::create($lieu)->between($start, $end);
                    if($isBetween && !$id) {
                        return ['result'=>false,'error' => '该节假日和调休日冲突，不允许添加！'];
                    }
                }
            }
        }

        try {
            $holiday = Holiday::updateOrCreate(
                [
                    'id' => $id
                ],
                [
                    'holiday' => $params['holiday'],
                    'remark' => isset($params['remark']) ? $params['remark'] : '',
                    'start' => $start,
                    'end'   => $end,
                    'lieuDay' => $params['lieuDay']
                ]
            );

            return ['result'=>true,'data' => $holiday];
        } catch (\Exception $e) {
            return ['result'=>false,'error' => $e->getMessage()];
        }

    }

    /**
     * 删除一个节假日
     *
     * @param Request $request
     * @return mixed
     * @author xuxiaoming
     * @datetime 2020/12/10 11:22
     */
    public function del($id)
    {
        try {
            Holiday::destroy($id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取节假日列表
     *
     * @param bool $keyWord
     * @param bool $year
     * @param bool $pageP
     * @return mixed
     * @author xuxiaoming
     * @datetime 2021/1/8 10:22
     */
    public static function getHolidayList($keyWord=false, $year=false, $pageP=false)
    {
        $res = ['result' => true];
        try {
            $takePage = $pageP && isset($pageP['page']) && isset($pageP['per_page']) ? true : false;

            $build = Holiday::when($keyWord, function($query) use($keyWord) {
                return $query->where('holiday', 'like', '%' . $keyWord . '%');
            })->when($year, function($query) use($year) {
                return $query->where(function($q) use($year) {
                    return $q->whereYear('start', $year);
                })->orWhere(function($q) use($year) {
                    return $q->whereYear('end', $year);
                });
            });

            $list['total'] = $build->count();
            $list['data'] = $build->when($takePage, function($query) use($pageP) {
                return $query->offset(($pageP['page'] -1) * $pageP['per_page'])
                    ->limit($pageP['page']);
            })
                ->get()
                ->toArray();

            $res['data'] = $list;

        } catch (\Exception $e) {
            $res['result'] = false;
            $res['error'] = $e->getMessage();
        }
        return $res;
    }
}