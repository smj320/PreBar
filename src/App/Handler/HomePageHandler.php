<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use PDO;

final class HomePageHandler implements RequestHandlerInterface
{
    public function __construct(
        public readonly PDO                         $pdo,
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
        $book_id = $config["books"][$id]["id"];
        $key = $config["books"][$id]["key"];

        $stmt = $this->pdo->prepare("SELECT * FROM article WHERE id_book = :book_id");
        $stmt->execute([":book_id" => $book_id]);
        $articles = $stmt->fetchAll();;
        $line = "";
        foreach ($articles as $article) {
            if ($article["name"] == "Article") {
                $hanrei = sprintf("<a class='text-decoration-none' href='/article/%s/%s/02'>[判例] </a>",
                    $article["id_book"], $article['num']);
                $memo = sprintf("<a  class='text-decoration-none' href='/article/%s/%s/03'>[注] </a>",
                    $article["id_book"], $article['num']);
                $article = sprintf("<a  class='text-decoration-none' href='/article/%s/%s/01'>第%s条</a>%s",
                    $article["id_book"], $article['num'],
                    $article['num'], $article['caption']);
                $line .= "<p>" . $hanrei . $memo . $article . "</p>\n";
            } else {
                $depth = (int)$article["id_depth"] + 1;
                $line .= sprintf("<h%d>%s %s</h%d\n>",
                    $depth, $article['title'], $article['caption'], $depth);
            }

        }
        $args = ["pageTitle" => $key, "body" => $line];
        return new HtmlResponse($this->template->render('app::home-page', $args));
    }
}
