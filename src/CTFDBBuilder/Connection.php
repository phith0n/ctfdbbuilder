<?php
/**
 * Created by PhpStorm.
 * User: phith0n
 * Date: 18/5/23
 * Time: 下午6:17
 */

namespace CTFDBBuilder;


use Pimple\Container;

class Connection
{
    /**
     * @var Container
     */
    protected $container;

    protected $adapter;

    protected static $storedConnection;

    /**
     * Connection constructor.
     * @param $adapter
     * @param array $adapterConfig
     * @param Container|null $container
     */
    function __construct($adapter, array $adapterConfig = [], Container $container = null)
    {
        $this->container = $container ? $container : new Container();

        $this->connect($adapter, $adapterConfig);
    }

    protected function connect($adapter, $config)
    {
        $this->container['adapter'] = function (Container $c) use ($adapter, $config) {
            $adapter = '\\CTFDBBuilder\\Adapters\\' . ucfirst(strtolower($adapter)) . 'Adapter';
            return new $adapter($config);
        };

        $this->container['builder'] = $this->container->factory(function(Container $c) {
            return new QueryBuilder($this);
        });
    }

    public function getAdapter()
    {
        return $this->container['adapter'];
    }

    public function getBuilder()
    {
        return $this->container['builder'];
    }

    static public function newBuilder(...$args)
    {
        if (!self::$storedConnection) {
            self::$storedConnection = new self(...$args);
        }

        return self::$storedConnection->getBuilder();
    }
}
