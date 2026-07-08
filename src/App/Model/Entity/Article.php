<?php

declare(strict_types=1);

namespace App\Model\Entity;

use ArrayObject;
use ReturnTypeWillChange;

/**
 * article テーブルに対応するエンティティクラス
 */
class Article extends ArrayObject
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

    #[ReturnTypeWillChange]
    public function exchangeArray($array): void
    {
        $this->id_book = $array['id_book'] ?? $this->id_book;
        $this->id_me = isset($array['id_me']) ? (int) $array['id_me'] : $this->id_me;
        $this->id_parent = isset($array['id_parent']) ? (int) $array['id_parent'] : $this->id_parent;
        $this->id_depth = isset($array['id_depth']) ? (int) $array['id_depth'] : $this->id_depth;
        $this->num = $array['num'] ?? $this->num;
        $this->name = $array['name'] ?? $this->name;
        $this->title = $array['title'] ?? $this->title;
        $this->caption = $array['caption'] ?? $this->caption;
        $this->article_xml = $array['article_xml'] ?? $this->article_xml;

        // 親クラス（ArrayObject）の内部データも更新しておくのが安全
        parent::exchangeArray($array);
    }

    public function getArrayCopy(): array
    {
        return [
            'id_book' => $this->id_book,
            'id_me' => $this->id_me,
            'id_parent' => $this->id_parent,
            'id_depth' => $this->id_depth,
            'num' => $this->num,
            'name' => $this->name,
            'title' => $this->title,
            'caption' => $this->caption,
            'article_xml' => $this->article_xml,
        ];
    }
}
