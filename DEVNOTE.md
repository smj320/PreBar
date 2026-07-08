# 開発環境・設定備忘録

本プロジェクトの開発において設定した内容や、トラブルシューティングの記録です。

## 1. PHP / Xdebug 設定 (`php.ini`)

Xdebug 3 の設定。`/opt/homebrew/etc/php/8.3/php.ini` などに適用。

```ini
[xdebug]
zend_extension="/opt/homebrew/opt/xdebug@8.5/xdebug.so"
xdebug.mode = debug,develop
xdebug.client_host = 127.0.0.1
xdebug.client_port = 9003

; デバッグを開始するスイッチをトリガー制にする（Composer等で止まらないようにする）
xdebug.start_with_request = trigger

; IDEが例外を捕まえる前にPHPが終了するのを防ぐ設定
xdebug.show_error_trace = 0
xdebug.show_exception_trace = 0
xdebug.default_enable = 0

; 診断用ログ
xdebug.log = /tmp/xdebug.log
xdebug.log_level = 7
```

- **トリガーの出し方**:
    - ブラウザ: URL末尾に `?XDEBUG_SESSION_START=1` をつけるか拡張機能を使用。
    - CLI: `XDEBUG_SESSION=1 php script.php`

## 2. PhpStorm デバッグ設定

### 通信設定
- **Settings > Languages & Frameworks > PHP > Debug**
    - `Ignore external connections through unregistered server configurations` -> **OFF** にする。
    - これが ON だと、新しいパスの接続があった際にダイアログが出ず、無視される。

### 例外ブレークポイント (Exception Breakpoints)
- **場所**: `Run > View Breakpoints...` (Shift + Cmd + 8)
- **設定**: `PHP Exception Breakpoints` に以下を追加。
    - `\Throwable`
    - `\Exception`
    - `\Error`
    - `\TypeError`
- **注意点**:
    - **クラス名の末尾に半角スペースが入らないように注意すること。**（マッチングに失敗するため）
    - 候補に出なくても手動入力でOK。

## 3. Mezzio エラーハンドリングの調整

デバッグ中に Whoops 画面が表示されて IDE に戻らない場合は、`config/pipeline.php` の以下の行を一時的にコメントアウトする。

```php
// $app->pipe(ErrorHandler::class);
```

これにより、例外がフレームワークにキャッチされず Xdebug まで到達し、IDE で停止できるようになる。

## 4. Bootstrap 5 Tips

### ボタン・クラスの注意
- **全角スペース厳禁**: `class="btn btn-sm　w-10"` のように全角スペースが入ると、後ろのクラスが認識されず、ボタンが巨大化（標準サイズ）する。

### テキストエリア
- **初期値の空白**: `<textarea> ... </textarea>` のようにタグの間で改行やインデントをすると、その空白がすべて入力値として扱われてしまう。開始タグと終了タグは1行で書く。
- **幅の指定**: `class="form-control"` を付与することで、親要素の幅100%になる。

### ボタンの幅とリンク
- **リンクボタン**: `<a href="..." class="btn btn-primary">` を推奨。
- **固定幅**: 幅を揃えたい場合は `style="width: 90px;"` などの指定が有効。
