<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Laminas\Db\TableGateway\TableGateway;

const URL_TOC = 'https://laws.e-gov.go.jp/api/1/lawlists/1?LawType=1';
const URL_BOOK = 'https://laws.e-gov.go.jp/api/1/lawdata/';
const PATH_TOC_XML = __DIR__ . "/../data/book_id.xml";
const PATH_BOOK_BODY_XML = __DIR__ . "/../data/xml/%s_%s.xml";
const PATH_DB = __DIR__ . "/../data/prebar.sqlite";

$pdo = Null;

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

/*
 * 再帰処理
*/

$id_counter = 0;
const ARROWED_TAGS = ["MainProvision", "Part", "Chapter", "Section", "Article", "Paragraph"];

function toc_recursive($book, $node, $depth, $id_parent, $pdo): void
{
    global $id_counter;
    global $tableGateway;

    $c_article = new \App\Model\Article();

    //MainProvision直下でないものはスキップ。必要な子要素は親から覗く
    $name = $node->nodeName;
    if (!in_array($name, ARROWED_TAGS)) {
        return;
    }
    $id_counter += 1;
    $id_me = $id_counter;
    printf("me %04d, parent %04d %s\n", $id_me, $id_parent, $name);

    //Articleのすぐ下にある条文の付属譲歩を取得
    $num_raw = $node->getAttribute("Num");
    $num = renum($num_raw);
    $caption = $node->getElementsByTagName($name . 'Caption')->item(0)->nodeValue ?? '';
    $title = $node->getElementsByTagName($name . 'Title')->item(0)->nodeValue ?? '';
    //
    $c_article->id_book = $book["id"];
    $c_article->id_me = $id_me;
    $c_article->id_parent = $id_parent;
    $c_article->id_depth = $depth;
    $c_article->num = $num;
    $c_article->title = $title;
    $c_article->name = $name;
    $c_article->caption = $caption;
    if ($name == "Article") {
        // Articleノードはその中身（項・号など）をすべて含んだXMLとして保存
        $xml_fragment = $node->ownerDocument->saveXML($node);
        $c_article->article_xml = '<?xml version="1.0" encoding="UTF-8"?>' . $xml_fragment;
        $tableGateway->insert($c_article->getArrayCopy()); // TableGatewayを使用
        return; // Article以下のノードは個別にDB登録しないので、ここで再帰を終了（方針通り）
    }
    $c_article->article_xml = "txt";
    $tableGateway->insert($c_article->getArrayCopy());

    // Article以下は上に戻っているので、それ以外で子要素がある場合再帰処理
    foreach ($node->childNodes as $child) {
        if ($child->nodeType === XML_ELEMENT_NODE) {
            toc_recursive($book, $child, $depth + 1, $id_me, $pdo);
        }
    }
}

function make_toc($book, $xml): void
{
    global $id_counter;
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $targetNode = $dom->getElementsByTagName('MainProvision')->item(0);
    $id_counter = 0;
    $pdo = new PDO("sqlite:" . PATH_DB);
    toc_recursive($book, $targetNode, 1, 1, $pdo);
}

function main(): void
{
    global $id_me, $tableGateway;
    # configの読み込み
    $config = require(__DIR__ . "/../config/config.php");
    /*
     * /config/autoload/dependencies.global.phpでAdapterのFactoryを登録(laminas-db付属)。
    *  /config/autoload/local.phpに 'db' を登録
    *  こうしておくと$containerから$adapterを引っ張ってこれる。
    */
    $container = require __DIR__ . '/../config/container.php';
    $adapter = $container->get(\Laminas\Db\Adapter\AdapterInterface::class);
    $tableGateway = new TableGateway('article', $adapter);

    # 法典IDの入ったXMLを取り込む
    if (!file_exists(PATH_TOC_XML)) {
        # ファイルがなければリモートからとって保存
        $toc_xml = file_get_contents(URL_TOC);
        file_put_contents(PATH_TOC_XML, $toc_xml);
    } else {
        # あればローカルから
        $toc_xml = file_get_contents(PATH_TOC_XML);
    }

# configに書いている法令名からIDと本体をとってくる
    foreach ($config['books'] as $book) {
        $book_xml = getBookXml($toc_xml, $book);
        $id_me = 0;
        make_toc($book, $book_xml);
    };
}

//-------
main();