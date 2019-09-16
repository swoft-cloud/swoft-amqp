<?php declare(strict_types=1);

namespace Jcsp\Amqp;

/**
 * Class AmqpDb
 *
 * @package Jcsp\Amqp
 *
 * @method void push(string $body, array $properties = [], string $routeKey = '')
 * @method string pop()
 * @method void consume(Closure $callback = null, array $config = [])
 * @method Client client()
 */
class AmqpDb
{

    /**
     * @var string
     */
    private static $pool;

    /**
     * pool
     *
     * @param string $pool
     *
     * @return AmqpDb
     */
    public static function pool(string $pool = 'amqp.pool'): AmqpDb
    {
        self::$pool = $pool;

        return new AmqpDb();
    }

    /**
     * initial
     */
    public static function initial(): void
    {
        self::$pool = 'amqp.pool';
    }

    /**
     * getPool
     *
     * @return string
     */
    public static function getPool()
    {
        if (!self::$pool) {
            self::initial();
        }

        return self::$pool;
    }

    /**
     * __call
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $result = Amqp::{$name}(...$arguments);
        AmqpDb::initial();

        return $result;
    }

}