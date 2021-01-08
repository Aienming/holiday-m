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

    **添加和编辑节假日：**
    
    参数：
    
    |参数 | 必须 | 类型 | 说明 |
    |:--- | :--- | :--- | :--- |
    |start | 是 | string | 开始日，格式：Y-m-d |
    |end | 是 | string | 结束日，格式：Y-m-d |
    |params['holiday'] | 否 | 节假日名称，不传显示默认字符 | 
    |params['lieuDay'] | 否 | 调休日，多个调休日期需以中文逗号('，')分割 | 
    |params['remark'] | 否 | 备注说明 |
    |id | 否 | int | 节假日id，携带时为更新操作 |
    
    ```$php
    use HolidayM;
    
    HolidayM::createOrUpdate($start, $end, $params[, $id]);     // 返回array
    // 返回格式：
    //          [
    //              'result' => false,                                  // 操作是否成功
    //              'error' => '该节假日和调休日冲突，不允许添加！',    // 错误提示
    //              'data'  => obj                                       // 成功时返回的模型
    //          ]
    
    ```
    - 获取已设置的节假日
    
    参数：
        
        |参数 | 必须 | 类型 | 说明 |
        |:--- | :--- | :--- | :--- |
        |keyWord | 否 | string | 节假日名字搜索 |
        |year | 否 | string | 年份条件 |
        |pageP['page'] | 否 | 分页之第几页 | 
        |pageP['per_page'] | 否 | 分页之每页数量 |
        
        ```$php
        use HolidayM;
        
        HolidayM::getHolidayList($keyWord, $year, $pageP);     // 返回array
        // 返回格式：
        //          [
        //              'total' => 0,                                  // 查询到的数量
        //              'data' => [],                           // 已设置的节假日数据
        //          ]
        
        ```
    - 删除已设置的节假日
    ```$php
    use HolidayM;
    
    HolidayM::del($id);     // 返回bool
    ```

