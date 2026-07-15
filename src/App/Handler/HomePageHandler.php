<?php

declare(strict_types=1);

namespace App\Handler;

use App\Model\ArticleTable;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

final class HomePageHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ?TemplateRendererInterface $template = null,
        private readonly ArticleTable               $articleTable
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $config = require __DIR__ . '/../../../config/config.php';

        $id = $request->getAttribute('id_book') ?? '010';
        $id_book = $config["books"][$id]["id"];
        $key = $config["books"][$id]["key"];

        $articles = $this->articleTable->fetchAll();

        $line = "";
        foreach ($articles as $article) {
            if ($article->name == "Article") {
                $num_disp = ltrim($article->num, '0');
                $article_link = sprintf("<a  class='btn btn-outline-primary btn-sm article_link' href='/article/%s/%s/01'>第%s条</a>%s",
                    $article->id_book, $article->num,
                    $num_disp, $article->caption);
                $line .= "<p>" . $article_link . "</p>\n";
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
