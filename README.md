## Laravel 5.6 从已经存在的数据库表格中批量创建模型

## 注册

在文件 /app/Console/Kernel.php 中注册命令
```php
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Wythe\LaravelCommand\MakeModels::class  //这一行代码注册命令，注意第一个\要加
    ];
```
## 选项说明 artisan help make:models
     --table[=TABLE]            指定要创建的表格 不建议填，填了报错，此处字符串转换数组尚未修复，懒得修复
     
     --path[=PATH]              模型创建的目录，默认目录为 app/Models
     
     --database[=DATABASE]      要创建的数据库，不选，使用默认数据库
     
     --created_at[=CREATED_AT]  Set create time field [default: "created_at"]  指定创建时间字段
     
     --updated_at[=UPDATED_AT]  Set update time field [default: "updated_at"]  指定更新时间字段
     
      -ut, --timestamps         是否使用自动更新时间戳，不填x此项，默认不使用
