# qsl-curd

## 1、引入composer

```she

composer require gmxiaoli/qsl-curd:^1.0 --dev

```


## 2、使用命令创建模块

* 生成模块

  ```shell
  php artisan gmxiaoli:g
  ```


* 生成模块 带有参数 --force

  默认文件存在，会提示是否被覆盖，添加上`--force` 参数后将自动覆盖，请谨慎选择。

  

* 生成指定的文件，使用 `--only` 指定。`--only` 允许的类型是：`controller` 、`logic` 、`service` 、`model`  其他格式不被允许

  ```shell
  php artisan gmxiaoli:g --only=service --module=Store
  ```

  如果文件存在，会提示是否覆盖文件。

  

  