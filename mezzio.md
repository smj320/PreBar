# mezzo　の使い方

## モジュールの登録と使い方

### 概要
mezzo用のモジュールはConfigProvider.phpという登録用の共通規格のファイルを持っている。
これをconfig/config.phpに登録する。

ちなみにsrc/App/自体もモジュールなので、スケルトンで作ったAppはConfigProvider.phpが
あり config/config.phpに登録されている。

### コンテナの作り方・使い方
index.phpにコンテナの呼出方が書かれている。$containerはrequire onceで、
config/container.php の中でnewで生成されたServiceManagerインスタンス
が返ってくる。
ServiceMangerにはFactoryを登録した配列をわたす。単独で使う場合は

```php
use Laminas\ServiceManager\ServiceManager;
// 設定配列を定義
$config = [
    'factories' => [
        MyService::class => MyServiceFactory::class,
    ],
];
// コンストラクタに渡す
$container = new ServiceManager($config);
// 使う
$service = $container->get(MyService::class);
```

となる。

### Hydratorを使ったフォーム

```php

```

