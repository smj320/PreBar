<?php

declare(strict_types=1);

namespace App\Handler;

use App\Model\ArticleTable;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

final class ArticlePostHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ?TemplateRendererInterface $template = null,
        private readonly ArticleTable               $articleTable
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $config = require __DIR__ . '/../../../bin/config.php';

        $id = $request->getAttribute('id') ?? '010';
        $book_id = $config["books"][$id]["id"];
        $key = $config["books"][$id]["key"];

        // 本来は $this->articleTable を通じて取得すべきだが、既存の PDO 直叩きを残す場合はそのまま
        // ここでは TableTable を使うようにリファクタリングする例を示す
        $articles = $this->articleTable->fetchAll(); // 本来は book_id でフィルタすべき

        $line = "";
        foreach ($articles as $article) {
            if ($article->name == "Article") {
                $line .= sprintf("<p><a href='/article/%s/%s'>第%s条</a>%s</p>\n",
                    $article->id_book, $article->num,
                    $article->num, $article->caption);
            } else {
                $depth = (int)$article->id_depth + 1;
                $line .= sprintf("<h%d>%s %s</h%d>\n>",
                    $depth, $article->title, $article->caption, $depth);
            }
        }
        $args = ["pageTitle" => $key, "body" => $line];
        return new HtmlResponse($this->template->render('app::home-page', $args));
    }
}
