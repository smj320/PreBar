<?php
require_once __DIR__ . '/../vendor/autoload.php';

const URL_TOC = 'https://laws.e-gov.go.jp/api/1/lawlists/1?LawType=1';
const URL_BOOK = 'https://laws.e-gov.go.jp/api/1/lawdata/';
const PATH_TOC_XML = __DIR__ . "/../data/book_id.xml";
const PATH_BOOK_BODY_XML = __DIR__ . "/../data/xml/%s_%s.xml";
const PATH_BOOK_TOC = __DIR__ . "/../data/cache/%s_%s_toc.balade.html";
const PATH_DB = __DIR__ . "/../data/prebar.sqlite";

const TOC_OFFSET = 3;
const TOC_FMT = [
    "<h2>%s</h2>\n",
    "<h3>%s</h3>\n",
    "<h4>%s</h4>\n",
    "<h5>%s</h5>\n",
    "<h6>%s</h6>\n"
];
const FMT_ARTICLE = "<p class='article ' id='%s'>"
    . "<a class='hanrei link-underline-opacity-0' href='/index.php?key=%s&legal_kb=1' >第%s条</a>"
    . "<a class='hanrei link-underline-opacity-0' href='/index.php?key=%s&legal_kb=2' > [判例] </a>"
    . "<a class='hanrei link-underline-opacity-0' href='/index.php?key=%s&legal_kb=3' > [メモ] </a>"
    . "%s"
    . "</p>\n";
# $content = sprintf(FMT_ARTICLE, $key, $key, $num_raw, $key, $key, $captions);

$pdo = Null;
function save_db(
    $pdo,
    $book_id, $id_node, $id_parent, $type, $num, $num_parent, $depth, $title, $content
)
{
    try {
        // 2. INSERT文の準備（? はプレースホルダ）
        $sql = "INSERT INTO article "
            . "(book_id, id_me, id_parent, type, num, num_parent, depth, title, content)"
            . " VALUES (:book_id, :id_me, :id_parent, :type, :num, :num_parent, :depth, :title , :content)";
        $stmt = $pdo->prepare($sql);

        // 3. 値をバインドして実行
        /*
        $params = [
            ':book_id' => $book_id,
            ':id_me' => (int)$id_node,
            ':id_parent' => (int)$id_parent,
            ':type' => (int)$type,
            ':num' => $num,
            ':num_parent' => $num_parent,
            ':depth' => (int)$depth,
            ':title' => $title,
            ':content' => $content,
        ];
        */
        $params = [
            ':book_id' => $book_id,
            ':id_me' => (int)$id_node,
            ':id_parent' => (int)$id_parent,
            ':type' => (int)$type,
            ':num' => $num,
            ':num_parent' => $num_parent,
            ':depth' => (int)$depth,
            ':title' => $title,
            ':content' => $content,
        ];
        $rows = $stmt->execute($params);

        echo "書き込みが完了しました！ {$rows}件\n";

    } catch (PDOException $e) {
        echo "エラーが発生しました: " . $e->getMessage() . "\n";
    }
}

function getBookXml($xml, $book): ?string
{
    $filename = sprintf(PATH_BOOK_BODY_XML, $book["id"], $book["abbr"]);
    #　ファイルがなければIDを探してリモートからとってくる
    if (!file_exists($filename)) {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        // 日本国憲法というLawTitleを持つLawNameListInfoの中のLawIdを指定
        $query = sprintf("//LawNameListInfo[LawName='%s']/LawId", $book["key"]);
        $node = $xpath->query($query)->item(0);
        $book_id = $node->nodeValue;
        $book_xml = file_get_contents(URL_BOOK . $book_id);
        # ファイルに記録
        file_put_contents($filename, $book_xml);
    } else {
        # あればローカルから
        $book_xml = file_get_contents($filename);
    }

    return $book_xml;
}

function renum($num)
{
    //分解
    $parts = explode('_', $num);
    // 2. 各要素をループして変換する
    $arr = array();
    for ($i = 0; $i < count($parts); $i++) {
        $fmt = $i == 0 ? "%04d" : "%02d";
        $arr[] = sprintf($fmt, (int)$parts[$i]);
    }

    // 4. アンダースコアで結合して元に戻す
    return implode('_', $arr);
}

$i_node = 0;
function toc_recursive($book, $node, $depth, $fp): void
{
    global $i_node;

    // Articleの場合、リンクを表示
    if (str_contains($node->nodeName, "Article")) {
        $i_node++;
        //Articleのすぐ下にある条文の付属譲歩を取得
        $num_raw = $node->getAttribute("Num");
        $num = renum($num_raw);
        $key = $book["id"] . "_" . $num;
        $captions = $node->getElementsByTagName('ArticleCaption')->item(0)->nodeValue ?? '';
        $content = sprintf(FMT_ARTICLE, $key, $key, $num_raw, $key, $key, $captions);
        fwrite($fp, $content);
        save_db();
        return;
    }

    // Articleより前の目次
    if (str_contains($node->nodeName, "Title")) {
        $i_node++;
        $content = sprintf(TOC_FMT[$depth - TOC_OFFSET], $node->textContent);
        fwrite($fp, $content);
        printf("%05d, %05d %s %s\n", $i_node, $depth, $node->nodeName, $node->textContent);
        save_db();
    }

    // Article以下は上に戻っているので、それ以外で子要素がある場合再帰処理
    foreach ($node->childNodes as $child) {
        if ($child->nodeType === XML_ELEMENT_NODE) {
            toc_recursive($book, $child, $depth + 1, $fp);
        }
    }
}

function make_toc($book, $xml, $fp)
{
    global $i_node;
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $targetNode = $dom->getElementsByTagName('MainProvision')->item(0);
    $i_node = 0;
    toc_recursive($book, $targetNode, 1, $fp);
}

$header = <<<EOT
<!doctype html>
<html lang='ja'>
<head>
<meta charset='utf8'>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
        body {
            font-family: 'Meiryo', sans-serif;
        }
        a:active,
        a:hover,
        a:visited,
        a {
            color: blue;
            text-decoration: none;
        }
</style>
</head>
<body>
<div class='container'>
<h1>テスト</h1>
EOT;

$footer = <<<EOT
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-jdSIJTK9l6XwXj3RixpVDXtMcA2bFd9O81RlLAwhpr2oXRqvQP88rr16IeFXTgFE" crossorigin="anonymous"></script>
</body>
</html>
EOT;


function main(): void
{
    global $header, $footer, $pdo;
    # configの読み込み
    $config = require("config.php");

    # 法典IDの入ったXMLを取り込む
    if (!file_exists(PATH_TOC_XML)) {
        # ファイルがなければリモートからとって保存
        $toc_xml = file_get_contents(URL_TOC);
        file_put_contents(PATH_TOC_XML, $toc_xml);
    } else {
        # あればローカルから
        $toc_xml = file_get_contents(PATH_TOC_XML);
    }

    // 1. データベースへの接続 (ファイルがない場合は自動作成されます)
    if (!file_exists(PATH_DB)) die("データベースファイルが見つかりません。");
    $pdo = new PDO("sqlite:" . PATH_DB);

    // エラーモードを例外に設定（デバッグしやすくなります）
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


# configに書いている法令名からIDと本体をとってくる
    foreach ($config['books'] as $book) {
        $book_xml = getBookXml($toc_xml, $book);
        $filename = sprintf(PATH_BOOK_TOC, $book["id"], $book["abbr"]);
        $fp = fopen($filename, "w");
        fwrite($fp, $header);
        make_toc($book, $book_xml, $fp);
        fwrite($fp, $footer);
        fclose($fp);
    };
}

//-------
main();