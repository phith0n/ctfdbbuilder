<?php
/**
 * Created by PhpStorm.
 * User: phith0n
 * Date: 18/5/23
 * Time: 下午8:51
 */

namespace DBBuilder\Adapters;


class PgsqlAdapter extends BaseAdapter
{
    protected $sanitizer = '"';

    protected $quote = "'";

    protected function connect(array $config)
    {
        $connectionString = "pgsql:host={$config['host']};dbname={$config['database']}";

        if (isset($config['port'])) {
            $connectionString .= ";port={$config['port']}";
        }

        $this->pdo = new \PDO($connectionString, $config['username'], $config['password'], $config['options']);

        if (isset($config['charset'])) {
            $this->pdo->prepare("SET NAMES '{$config['charset']}'")->execute();
        }

        if (isset($config['schema'])) {
            $this->pdo->prepare("SET search_path TO '{$config['schema']}'")->execute();
        }
    }
}
