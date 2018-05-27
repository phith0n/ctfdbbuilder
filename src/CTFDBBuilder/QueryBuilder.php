<?php
/**
 * Created by PhpStorm.
 * User: phith0n
 * Date: 18/5/23
 * Time: 下午8:23
 */

namespace CTFDBBuilder;



class QueryBuilder
{
    /**
     * @var Connection
     */
    protected $connect;

    /**
     * @var \CTFDBBuilder\Adapters\BaseAdapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $statements = [];

    function __construct(Connection $connect)
    {
        $this->connect = $connect;
        $this->adapter = $this->connect->getAdapter();
    }

    protected function addStatement($key, $value, $overwrite = false)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        if ($overwrite || !array_key_exists($key, $this->statements)) {
            $this->statements[$key] = $value;
        } else {
            $this->statements[$key] = array_merge($this->statements[$key], $value);
        }
    }

    private function _default_state_callback($state)
    {
        return " $state[0] ";
    }

    protected function getStatement($key, Callable $callback = null)
    {
        if (!$callback) {
            $callback = [$this, '_default_state_callback'];
        }

        if (array_key_exists($key, $this->statements) && $this->statements[$key]) {
            return call_user_func($callback, $this->statements[$key]);
        } else {
            return '';
        }
    }

    protected function prepareString($value)
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_string($value)) {
            return $this->adapter->escape($value);
        } else {
            return strval($value);
        }
    }

    protected function escapeField($field)
    {
        if (preg_match('#\s|`|"|\'|\*|\(|\)#', $field)) {
            return $field;
        } else {
            return $this->adapter->escapeField($field);
        }
    }

    public function where($key, $op = null, $value = null)
    {
        $this->addStatement('where', $this->Q($key, $op, $value));
        return $this;
    }

    public function select($fields)
    {
        $this->addStatement('select', $this->escapeField($fields), true);
        return $this;
    }

    public function from($table)
    {
        return $this->table($table);
    }

    public function table($table)
    {
        $this->addStatement('table', $this->escapeField($table), true);
        return $this;
    }

    public function join($sql)
    {
        $this->addStatement('join', $this->escapeField($sql), true);
        return $this;
    }

    public function groupBy($field)
    {
        $this->addStatement('groupBy', $this->escapeField($field));
        return $this;
    }

    public function having($sql)
    {
        $this->addStatement('having', $this->escapeField($sql), true);
        return $this;
    }

    public function limit($number)
    {
        $this->addStatement('limit', $number, true);
        return $this;
    }

    public function offset($number)
    {
        $this->addStatement('offset', $number, true);
        return $this;
    }

    public function orderBy($field, $direction = null)
    {
        if (func_num_args() == 1) {
            $this->addStatement('orderBy', $this->escapeField($field));
        } else {
            $this->addStatement('orderBy', "{$this->escapeField($field)} {$direction}");
        }
        return $this;
    }

    public function set($key, $value)
    {
        if (is_array($key)) {
            foreach ($key as $_k => $_v) {
                $this->addStatement('set', [$this->escapeField($_k), $this->prepareString($_v)]);
            }
        } else {
            $this->addStatement('set', [$this->escapeField($key), $this->prepareString($value)]);
        }
        return $this;
    }

    public function query($sql)
    {
        return $this->execute($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function execute($sql)
    {
        return $this->adapter->query($sql);
    }

    public function first()
    {
        $records = $this->limit(1)->get();
        return empty($records) ? null : $records[0];
    }

    public function get()
    {
        if (empty($this->statements['select'])) {
            $this->addStatement('select', '*', true);
        }

        $sql = "SELECT {$this->getStatement('select')} FROM {$this->getStatement('table')} {$this->getStatement('join')} ";

        $sql .= $this->getStatement('where', function ($state) {
            return ' WHERE ' . implode(" AND ", $state) . ' ';
        });

        $sql .= $this->getStatement('groupBy', function($state) {
            return ' GROUP BY ' . implode(', ', $state) . ' ';
        });
        $sql .= $this->getStatement('having');
        $sql .= $this->getStatement('orderBy', function ($state) {
            return ' ORDER BY ' . implode(", ", $state) . ' ';
        });

        $sql .= $this->getStatement('limit', function($state) {
            return " LIMIT {$state[0]} ";
        });
        $sql .= $this->getStatement('offset', function($state) {
            return " OFFSET {$state[0]} ";
        });

        return $this->query($sql);
    }

    public function update()
    {
        $sql = $this->getStatement('table', function ($state) {
            return "UPDATE {$state[0]} SET ";
        });
        $sql .= $this->getStatement('set', function ($state) {
            $set = [];
            foreach ($state as $line) {
                $set[] = "{$line[0]} = {$line[1]}";
            }

            return ' ' . implode(', ', $set) . ' ';
        });
        $sql .= $this->getStatement('where', function ($state) {
            return ' WHERE ' . implode(" AND ", $state) . ' ';
        });
        $sql .= $this->getStatement('limit', function($state) {
            return " LIMIT {$state[0]} ";
        });

        return $this->execute($sql);
    }

    public function insert()
    {
        $sql = $this->getStatement('table', function ($state) {
            return "INSERT INTO {$state[0]} ";
        });
        $sql .= $this->getStatement('set', function ($state) {
            $keys = [];
            $values = [];
            foreach ($state as $line) {
                $keys[] = $line[0];
                $values[] = $line[1];
            }

            return ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ') ';
        });

        return $this->execute($sql);
    }

    public function delete()
    {
        $sql = $this->getStatement('table', function ($state) {
            return "DELETE FROM {$state[0]} ";
        });
        $sql .= $this->getStatement('where', function ($state) {
            return ' WHERE ' . implode(" AND ", $state) . ' ';
        });
        return $this->execute($sql);
    }

    public function Q($field, $op = null, $value = null)
    {
        $argc = func_num_args();
        if($argc == 2) {
            return "{$this->escapeField($field)} = {$this->adapter->escape($op)}";
        } elseif($argc == 3) {
            return "{$this->escapeField($field)} {$op} {$this->adapter->escape($value)}";
        } else {
            return $field;
        }
    }
}