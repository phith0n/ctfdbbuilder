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

### Select (SQL injection)


```php
<?php
$article = $builder->table('articles')->where('id', '=', $_GET['id'])->first();
```

```php
<?php
$article = $builder->table('users')->where('age', '>', $_GET['age'])->first();
```

```php
<?php
$article = $builder->table('users')->select('COUNT() AS `cnt`');
```

```php
<?php
$article = $builder->table('users')->where('username', $_POST['username'])->where('password', md5($_POST['password']))->first();
```