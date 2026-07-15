<?php

namespace App\Model;

use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ArticleTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dbAdapter = $container->get(AdapterInterface::class);

        // Articleオブジェクトをプロトタイプとして設定
        $hydrator = new ReflectionHydrator();
        $bookPrototype = new Article();
        $resultSetPrototype = new HydratingResultSet($hydrator, $bookPrototype);

        // テーブル名は 'article'
        $tableGateway = new TableGateway('article', $dbAdapter, null, $resultSetPrototype);

        return new ArticleTable($tableGateway, $hydrator);
    }
}
