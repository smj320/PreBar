<?php

/**
 * Local configuration.
 *
 * Copy this file to `local.php` and change its settings as required.
 * `local.php` is ignored by git and safe to use for local and sensitive data like usernames and passwords.
 */

declare(strict_types=1);

return [
    'db' => [
        'driver' => 'Pdo_Sqlite',
        'database' => __DIR__ . '/../../data/prebar.sqlite',
    ],
    'books' => [
        '010' => ['id' => '010', 'key' => '日本国憲法', 'abbr' => '憲法'],
        '020' => ['id' => '020', 'key' => '刑法', 'abbr' => '刑法'],
        '030' => ['id' => '030', 'key' => '民法', 'abbr' => '民法'],
        '040' => ['id' => '040', 'key' => '民事訴訟法', 'abbr' => '民訴'],
        '050' => ['id' => '050', 'key' => '刑事訴訟法', 'abbr' => '刑訴'],
        '060' => ['id' => '060', 'key' => '商法', 'abbr' => '商法'],
        '070' => ['id' => '070', 'key' => '会社法', 'abbr' => '会社'],
        '080' => ['id' => '080', 'key' => '地方自治法', 'abbr' => '地自'],
        '090' => ['id' => '090', 'key' => '行政手続法', 'abbr' => '行手'],
        '100' => ['id' => '100', 'key' => '行政不服審査法', 'abbr' => '行審'],
        '110' => ['id' => '110', 'key' => '行政事件訴訟法', 'abbr' => '行訴'],
        '120' => ['id' => '120', 'key' => '国家賠償法', 'abbr' => '国賠'],
        '130' => ['id' => '130', 'key' => '借地借家法', 'abbr' => '借借'],
        '140' => ['id' => '140', 'key' => '労働基準法', 'abbr' => '労基'],
        '150' => ['id' => '150', 'key' => '労働契約法', 'abbr' => '労契']
    ]
];
