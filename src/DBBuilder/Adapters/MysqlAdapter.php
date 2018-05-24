<?php
/**
 * Created by PhpStorm.
 * User: phith0n
 * Date: 18/5/23
 * Time: 下午6:19
 */

namespace CTFDBBuilder\Adapters;


class MysqlAdapter extends BaseAdapter
{
    protected $sanitizer = '`';

    protected $quote = "'";

    protected function connect(array $config)
    {
        $connectionString = "mysql:dbname={$config['database']}";

        if (isset($config['host'])) {
            $connectionString .= ";host={$config['host']}";
        }

        if (isset($config['port'])) {
            $connectionString .= ";port={$config['port']}";
        }

        if (isset($config['unix_socket'])) {
            $connectionString .= ";unix_socket={$config['unix_socket']}";
        }

        $this->pdo = new \PDO($connectionString, $config['username'], $config['password'], $config['options']);

        if (isset($config['charset'])) {
            $this->pdo->prepare("SET NAMES '{$config['charset']}'")->execute();
        }
    }
}