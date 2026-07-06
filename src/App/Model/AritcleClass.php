<?php

namespace App\Model;

use PDO;
use PDOException;

class AritcleClass
{
    public $id_book = "01";
    public $id_me = 0;
    public $id_parent = 0;
    public $id_depth = 0;
    public $num = "01";
    public $name = "name";
    public $title = "title";
    public $caption = "caption";
    public $article_xml = "xml";

    // 自分自身のプロパティからSQL用の情報を返すメソッド
    public function getSqlData(): array
    {
        $data = get_object_vars($this);
        $keys = array_keys($data);

        return [
            'columns' => implode(', ', $keys),
            'placeholders' => ':' . implode(', :', $keys),
            'values' => $data // executeにそのまま渡せる形式
        ];
    }

    function save($pdo)
    {
        $sqlData = $this->getSqlData();
        $sql = $sql = sprintf(
            "INSERT INTO article (%s) VALUES (%s)",
            $sqlData['columns'],
            $sqlData['placeholders']
        );
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($sqlData['values']);
        } catch (PDOException $e) {
            echo "エラーが発生しました: " . $e->getMessage() . "\n";
        }
    }
}