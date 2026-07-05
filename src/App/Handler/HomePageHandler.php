<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

final class HomePageHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly string $containerName,
        private readonly RouterInterface $router,
        private readonly ?TemplateRendererInterface $template = null
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $config = require __DIR__.'/../../../bin/config.php';
        $params = $request->getQueryParams();
        $book_id = $params['book_id'] ?? '01';
        $key = $config["books"][$book_id]["key"];

        $args = ["pageTitle"=>$key];
        return new HtmlResponse($this->template->render('app::home-page', $args));
    }
}
