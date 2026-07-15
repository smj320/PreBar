<?php

declare(strict_types=1);

namespace App\Model;

/*
 * article テーブルに対応するエンティティクラス
 */
class Article
{
    public ?string $id_book = null;
    public ?int $id_me = null;
    public ?int $id_parent = null;
    public ?int $id_depth = null;
    public ?string $num = null;
    public ?string $name = null;
    public ?string $title = null;
    public ?string $caption = null;
    public ?string $article_xml = null;
}

