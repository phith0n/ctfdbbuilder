<?php
/**
 * Created by PhpStorm.
 * User: phith0n
 * Date: 18/5/23
 * Time: 下午8:53
 */

namespace CTFDBBuilder\Adapters;


class SqliteAdapter extends BaseAdapter
{
    protected $sanitizer = '"';

    protected $quote = "'";

    protected function connect(array $config)
    {
        $connectionString = 'sqlite:' . $config['database'];
        $this->pdo = new \PDO($connectionString, null, null, $config['options']);
    }
}
