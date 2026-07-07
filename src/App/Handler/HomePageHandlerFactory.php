<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PDO;

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
        //$config = require __DIR__ . '/../../../config/config.php';
        $dsn = "sqlite:/Users/kikuchi/Projects/PhpstormProjects/PreBar/data/prebar.sqlite";
        $pdo = new PDO($dsn);
        return new HomePageHandler($pdo, $container::class, $router, $template);
    }
}
