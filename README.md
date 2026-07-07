# PreBar

PreBar: A tool for studying Yobishiken (Japanese Preliminary Bar Exam)

## 準備
* config.phpに取得したい法文を記述する
* xml_fetch.phpを動かして、法文をとってくる。取得先は下記URL
  * https://laws.e-gov.go.jp/api/1/lawlists/1

## 法典の階層構造

```xml
  <xs:element name="MainProvision">
  <xs:complexType>
    <xs:choice>
      <xs:element maxOccurs="unbounded" ref="Part"/>
      <xs:element maxOccurs="unbounded" ref="Chapter"/>
      <xs:element maxOccurs="unbounded" ref="Section"/>
      <xs:element maxOccurs="unbounded" ref="Article"/>
      <xs:element maxOccurs="unbounded" ref="Paragraph"/>
    </xs:choice>
    <xs:attribute name="Extract" type="xs:boolean"/>
  </xs:complexType>
</xs:element>
```

## 具体例

```xml
          <MainProvision>
            <Part Num="1">
              <PartTitle>第一編　総則</PartTitle>
              <Chapter Num="1">
                <ChapterTitle>第一章　通則</ChapterTitle>
                <Article Num="1">
                  <ArticleCaption>（基本原則）</ArticleCaption>
                  <ArticleTitle>第一条</ArticleTitle>
                  <Paragraph Num="1">
                    <ParagraphNum/>
                    <ParagraphSentence>
                      <Sentence Num="1" WritingMode="vertical">私権は、公共の福祉に適合しなければならない。</Sentence>
                    </ParagraphSentence>
                  </Paragraph>
                  <Paragraph Num="2">
                    <ParagraphNum>２</ParagraphNum>
                    <ParagraphSentence>
                      <Sentence Num="1" WritingMode="vertical">権利の行使及び義務の履行は、信義に従い誠実に行わなければならない。</Sentence>
                    </ParagraphSentence>
                  </Paragraph>
                  <Paragraph Num="3">
                    <ParagraphNum>３</ParagraphNum>
                    <ParagraphSentence>
                      <Sentence Num="1" WritingMode="vertical">権利の濫用は、これを許さない。</Sentence>
                    </ParagraphSentence>
                  </Paragraph>
                </Article>
                  ...
              </Chapter>
            </Part>
          </MainProvision>
```

再帰処理に<MainProvision>を渡して、for で contentsを取得すると <Part>の羅列が出てくる。
Partのタイトルはその下に配置されている。
Partを処理する場合は、その下のPartTitleやPartCaptionを拾う。
Part直下にArticleがある場合もあるが、<Chapter>の羅列ががある場合もある。
<Article>の下には<ArticleTitle>第何条の型でかならずあるが、
<ArticleCaption>は憲法や刑訴のようにない場合がある。


## Factoryの登録のやり方

共通でnewするようなクラスは、ファクトリークラスを作って共通で使う作り方を書いておく。
アプリケーションで共通のものはApp/Infrastructure/PdoFactor.phpとかにおく。

コンフィグに関してはconfig/autoload/local.phpに
```php
//SQLiteの場合
return [
    'db' => [
        'dsn' => 'sqlite:' . __DIR__ . '/../../data/prebar.sqlite',
    ],
];
//MySQLの場合
return [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=test_db;charset=utf8mb4',
        'user' => 'db_user',
        'pass' => 'db_pass',
    ],
];
```

```php
namespace App\Infrastructure;

use PDO;
use Psr\Container\ContainerInterface;

class PdoFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['db'];
        
        $pdo = new PDO($config['dsn']);
        
        // エラーが発生した際に例外を投げる設定（必須級）
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // フェッチモードをデフォルトで連想配列にする設定（お好みで）
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    }
}
```
このクラスを作るレシピは App/ConfigProvider.phpに登録 に登録

```php
'dependencies' => [
    'factories' => [
        PDO::class => App\Infrastructure\PdoFactory::class,
    ],
],
```

ハンドラー等App/ConfigProvider.phpに登録。
