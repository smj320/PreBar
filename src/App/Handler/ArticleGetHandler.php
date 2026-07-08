<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Db\TableGateway\TableGateway;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use PDO;

final class ArticleGetHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TableGateway               $tableGateway,
        private readonly string                     $containerName,
        private readonly RouterInterface            $router,
        private readonly ?TemplateRendererInterface $template = null
    )
    {
    }


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $config = require __DIR__ . '/../../../config/config.php';

        $id_book = $request->getAttribute('id_book') ?? '010';
        $num = $request->getAttribute('num');
        $id_type = $request->getAttribute('id_type');
        $key = $config["books"][$id_book]["key"];
        if ($id_type == "01") {
            $row = $this->tableGateway->select(['id_book' => $id_book, 'num' => $num])->current();
            $article = $row->article_xml;
        } else {
            $article = "";
        }

        $args = ["pageTitle" => $key, "id_book" => $id_book, "article" => $article];
        return new HtmlResponse($this->template->render('app::article', $args));
    }
}
