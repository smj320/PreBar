<?php

declare(strict_types=1);

namespace App\Handler;

use App\Model\Entity\Article;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;

final class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $router = $container->get(RouterInterface::class);
        assert($router instanceof RouterInterface);

        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;
        assert($template instanceof TemplateRendererInterface || null === $template);

        try {
            $adapter = $container->get(AdapterInterface::class);
        } catch (\Exception $e) {
            die($e->getMessage());
        } catch (NotFoundExceptionInterface $e) {
            die($e->getMessage());
        } catch (ContainerExceptionInterface $e) {
            die($e->getMessage());
        }
        $articlePrototype = new Article();
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype($articlePrototype);
        $tableGateway = new TableGateway('article', $adapter, null, $resultSetPrototype);

        return new HomePageHandler($tableGateway, $container::class, $router, $template);
    }
}
