<?php
/**
 * Created by PhpStorm.
 * User: phith0n
 * Date: 18/5/23
 * Time: 下午6:17
 */

namespace CTFDBBuilder\Adapters;


abstract class BaseAdapter
{
    /**
     * @var string
     */
    protected $sanitizer;

    /**
     * @var string
     */
    protected $quote;

    /**
     * @var \PDO
     */
    protected $pdo;

    function __construct(array $config)
    {
        $this->connect($config);

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * connect to database
     *
     * @param array $config
     * @return null
     */
    abstract protected function connect(array $config);

    /**
     * execute a sql query string
     *
     * @param $sql
     * @return \PDOStatement
     */
    public function query($sql)
    {
        return $this->pdo->query($sql);
    }

    public function escape($string)
    {
        return $this->quote . $string . $this->quote;
    }

    public function escapeField($field)
    {
        return $this->sanitizer . $field . $this->sanitizer;
    }
}