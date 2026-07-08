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

final class HomePageHandler implements RequestHandlerInterface
{
    public function __construct(
        public readonly TableGateway               $tableGateway,
        public readonly string                     $containerName,
        public readonly RouterInterface            $router,
        public readonly ?TemplateRendererInterface $template = null
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $config = require __DIR__ . '/../../../config/config.php';

        $id = $request->getAttribute('id_book') ?? '010';
        $id_book = $config["books"][$id]["id"];
        $key = $config["books"][$id]["key"];

        $articles = $this->tableGateway->select(['id_book' => $id_book]);

        $line = "";
        foreach ($articles as $article) {
            if ($article->name == "Article") {
                $article = sprintf("<a  class='btn btn-outline-primary btn-sm article_link' href='/article/%s/%s/01'>第%s条</a>%s",
                    $article->id_book, $article->num,
                    $article->num, $article->caption);
                $line .= "<p>" . $article . "</p>\n";
            } else {
                $depth = (int)$article->id_depth + 1;
                $line .= sprintf("<h%d>%s %s</h%d\n>",
                    $depth, $article->title, $article->caption, $depth);
            }

        }
        $args = ["pageTitle" => $key, "body" => $line];
        return new HtmlResponse($this->template->render('app::home-page', $args));
    }
}
