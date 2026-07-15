<?php

namespace App\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Hydrator\HydratorInterface;
use RuntimeException;

class ArticleTable
{
    private TableGatewayInterface $tableGateway;
    private HydratorInterface $hydrator;

    public function __construct(TableGatewayInterface $tableGateway, HydratorInterface $hydrator)
    {
        $this->tableGateway = $tableGateway;
        $this->hydrator = $hydrator;
    }

    public function fetchAll(): ResultSetInterface
    {
        return $this->tableGateway->select();
    }

    public function getArticle(string $id_book)
    {
        $rowset = $this->tableGateway->select(['id_book' => $id_book]);
        $row = $rowset->current();
        if (!$row) {
            throw new RuntimeException("Could not find row with identifier $id_book");
        }
        return $row;
    }

    /**
     * IDがあれば更新、無ければ作成
     */
    public function update(Article $article): void
    {
        $this->saveArticle($article);
    }

    public function saveArticle(Article $article): void
    {
        $data = $this->hydrator->extract($article);
        $id_book = $article->id_book;

        try {
            $this->getArticle($id_book);
        } catch (RuntimeException $e) {
            $this->tableGateway->insert($data);
            return;
        }

        $this->tableGateway->update($data, ['id_book' => $id_book]);
    }

    public function deleteArticle(string $id_book): void
    {
        $this->tableGateway->delete(['id_book' => $id_book]);
    }
}
