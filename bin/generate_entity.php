<?php

declare(strict_types=1);

if ($argc < 2) {
    echo "Usage: php bin/generate_entity.php <sql_file_path>\n";
    exit(1);
}

$sqlFile = $argv[1];
if (!file_exists($sqlFile)) {
    echo "File not found: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);

// CREATE TABLE 文の抽出 (大まかなパース)
if (!preg_match('/CREATE TABLE\s+(\w+)\s*\((.*)\)/is', $sql, $matches)) {
    echo "Could not find CREATE TABLE statement.\n";
    exit(1);
}

$tableName = $matches[1];
$columnDefs = explode(',', $matches[2]);

$entityName = ucfirst($tableName) . 'Entity';
$properties = [];

foreach ($columnDefs as $def) {
    $def = trim($def);
    if (empty($def) || preg_match('/^(PRIMARY KEY|CONSTRAINT|UNIQUE|INDEX)/i', $def)) {
        continue;
    }

    $parts = preg_split('/\s+/', $def);
    $name = trim($parts[0], '`"');
    $type = strtoupper($parts[1] ?? 'TEXT');

    $phpType = 'string';
    $cast = '';

    if (strpos($type, 'INT') !== false) {
        $phpType = 'int';
        $cast = '(int) ';
    } elseif (strpos($type, 'BOOL') !== false) {
        $phpType = 'bool';
        $cast = '(bool) ';
    } elseif (strpos($type, 'FLOAT') !== false || strpos($type, 'DOUBLE') !== false || strpos($type, 'DECIMAL') !== false) {
        $phpType = 'float';
        $cast = '(float) ';
    }

    $properties[] = [
        'name' => $name,
        'phpType' => $phpType,
        'cast' => $cast
    ];
}

// コード生成
$code = "<?php\n\n";
$code .= "declare(strict_types=1);\n\n";
$code .= "namespace App\Model;\n\n";
$code .= "/**\n";
$code .= " * {$tableName} テーブルに対応するエンティティクラス\n";
$code .= " */\n";
$code .= "class {$entityName}\n";
$code .= "{\n";

foreach ($properties as $prop) {
    $code .= "    public ?{$prop['phpType']} \${$prop['name']} = null;\n";
}

$code .= "\n    public function exchangeArray(array \$data): void\n";
$code .= "    {\n";
foreach ($properties as $prop) {
    if ($prop['cast']) {
        $code .= "        \$this->{$prop['name']} = isset(\$data['{$prop['name']}']) ? {$prop['cast']}\$data['{$prop['name']}'] : \$this->{$prop['name']};\n";
    } else {
        $code .= "        \$this->{$prop['name']} = \$data['{$prop['name']}'] ?? \$this->{$prop['name']};\n";
    }
}
$code .= "    }\n";

$code .= "\n    public function getArrayCopy(): array\n";
$code .= "    {\n";
$code .= "        return [\n";
foreach ($properties as $prop) {
    $code .= "            '{$prop['name']}' => \$this->{$prop['name']},\n";
}
$code .= "        ];\n";
$code .= "    }\n";
$code .= "}\n";

echo $code;

