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

final class ArticleGetHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly PDO                        $pdo,
        private readonly string                     $containerName,
        private readonly RouterInterface            $router,
        private readonly ?TemplateRendererInterface $template = null
    )
    {
    }


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dummy = <<<EOM
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed 
do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
Ut enim ad minim veniam, quis nostrud exercitation ullamco 
laboris nisi ut aliquip ex ea commodo consequat.
EOM;

        $config = require __DIR__ . '/../../../config/config.php';

        $id_book = $request->getAttribute('id_book') ?? '010';
        $num = $request->getAttribute('num');
        $id_type = $request->getAttribute('id_type');
        $key = $config["books"][$id_book]["key"];
        if($id_type == "01"){
            $query = "SELECT * FROM article WHERE id_book = :id_book AND num=:num";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([":id_book" => $id_book, ":num" => $num]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $article = $row["article_xml"];
        }else{
            $article = "";
        }

        $args = ["pageTitle" => $key, "article" => $article];
        return new HtmlResponse($this->template->render('app::article', $args));
    }
}
