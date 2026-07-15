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

final class ArticleGetHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ArticleTable               $articleTable,
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
            try {
                // ここでは select ではなく getArticle を使うか、直接 TableGateway にアクセスする場合は TableTable を介すべき
                // 既存コードに合わせて fetchAll からフィルタするか、TableTable にメソッドを追加するのが望ましいが
                // ここでは実装の修正に留める
                $row = $this->articleTable->getArticle($id_book); // 仮定：num 等での検索が必要なら Model 側を拡張すべき
                $article = $row->article_xml;
            } catch (\Exception $e) {
                $article = "Not Found";
            }
        } else {
            $article = "";
        }

        $args = ["pageTitle" => $key, "id_book" => $id_book, "article" => $article];
        return new HtmlResponse($this->template->render('app::article', $args));
    }
}
