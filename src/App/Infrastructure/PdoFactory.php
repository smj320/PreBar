<?php

namespace App\Infrastructure;

use PDO;
use Psr\Container\ContainerInterface;
class PdoFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $dsn = $container->get('config')['db']['dsn'];
        $pdo = new PDO($dsn);

        // エラーが発生した際に例外を投げる設定（必須級）
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // フェッチモードをデフォルトで連想配列にする設定（お好みで）
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}