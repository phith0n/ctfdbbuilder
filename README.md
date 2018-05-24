# DB Builder for CTFer

一个无任何防护的PHP数据库Builder，支持Mysql/Postgresql/Sqlite。

## Usage

```php
<?php
include 'vendor/autoload.php';

$connect = new \DBBuilder\Connection('mysql', [
    'driver'    => 'mysql', // Db driver
    'host'      => 'localhost',
    'database'  => 'your-database',
    'username'  => 'root',
    'password'  => 'your-password',
    'charset'   => 'utf8mb4', // Optional
    'options'   => [ // PDO constructor options, optional
        \PDO::ATTR_TIMEOUT => 5,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ],
]);
$builder = $connect->getBuilder();
```