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
    if (strpos($type, 'INT') !== false) {
        $phpType = 'int';
    } elseif (strpos($type, 'BOOL') !== false) {
        $phpType = 'bool';
    } elseif (strpos($type, 'FLOAT') !== false || strpos($type, 'DOUBLE') !== false || strpos($type, 'DECIMAL') !== false) {
        $phpType = 'float';
    }

    $properties[] = [
        'name' => $name,
        'phpType' => $phpType,
    ];
}

// コード生成: exchangeArray/getArrayCopy を削除
$code = "<?php\n\n";
$code .= "declare(strict_types=1);\n\n";
$code .= "namespace App\Model;\n\n";
$code .= "class {$entityName}\n";
$code .= "{\n";

foreach ($properties as $prop) {
    // POPO なのでパブリックプロパティにするだけでOK
    $code .= "    public ?{$prop['phpType']} \${$prop['name']} = null;\n";
}

$code .= "}\n";

echo $code;