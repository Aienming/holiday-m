# holiday-m
一个关于节假日管理的东东

### 安装
```$shell
$ composer require Aienming/Holiday-m

# 发布服务
$ php artisan vendor:publish --provider="Aienming/HolidayManage/HolidayServiceProvider.php"

# 迁移数据表
$ php artisan migrate
```


### 相关操作
- 判断某一日期是否为工作日
```$php
use HolidayM

HolidayM::isWorkDay('2020-12-15');   // 返回bool值，true表示是工作日
```

- 判断一段日期中工作日的天数
```$php
use HolidayM

HolidayM::numberOfWorkDay('2020-12-15', '2020-12-31');  // 返回int。注：计算包括开始日期和结束日期
```

- 增删改节假日
```$php
use HolidayM
// $start和$end对应的是数据表中start和end字段，是节假日的开始和结束日期
// $params为数据表中的其他字段，其中lieuDay使用的是中文逗号“，”来分隔不同日期
// $id为可选参数，id存在时表示为更新操作
// 返回格式：
//          [
//              'result' => false,                                  // 操作是否成功
//              'error' => '该节假日和调休日冲突，不允许添加！',    // 错误提示
//              'data'  => obj                                       // 成功时返回的模型
//          ]
HolidayM::createOrUpdate($start, $end, $params[, $id]);     // 返回array

HolidayM::del($id);     // 返回bool
```

