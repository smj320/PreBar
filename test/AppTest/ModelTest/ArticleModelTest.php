<?php

namespace AppTest\ModelTest;

require_once __DIR__ . '/../../../vendor/autoload.php';

use PDO;
use PDOException;
use App\Model\AritcleClass;


$config = require __DIR__ . '/../../../bin/config.php';
$pdo = new PDO( $config['dsn']);

$article = new AritcleClass();
$article->save($pdo);
