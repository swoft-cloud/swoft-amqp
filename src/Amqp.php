<?php declare(strict_types=1);

namespace Jcsp\Amqp;

use Closure;
use Exception;
use Jcsp\Amqp\Connection\Connection;
use Jcsp\Amqp\Exception\AMQPException;
use Swoft\Bean\BeanFactory;

/**
 * Class Amqp
 *
 * @package Jcsp\Amqp
 * @since   2.0
 *
 * @method static AmqpDb pool(string $pool = 'amqp.pool')
 */
class Amqp
{

    /**
     * 连接服务器
     * connection
     *
     * @return Connection|null
     * @throws AMQPException
     */
    protected static function connection(): ?Connection
    {
        try {
            /* @var Pool $amqpPool */
            $amqpPool   = BeanFactory::getBean(AmqpDb::getPool());
            $connection = $amqpPool->getConnection();
            $amqpPool->release($connection);

            return $connection;
        } catch (Exception $exception) {
            throw new AMQPException('AMQP connection error: '.$exception->getMessage());
        }
    }

    /**
     * 推送消息
     * push
     *
     * @param string $body
     * @param array  $properties
     * @param string $routeKey
     *
     * @throws AMQPException
     */
    public static function push(string $body, array $properties = [], string $routeKey = ''): void
    {
        try {
            /* @var Connection */
            $connection = self::connection();
            $connection->establish();
            $connection->push($body, $properties, $routeKey);
            $connection->close();
        } catch (Exception $exception) {
            throw new AMQPException('AMQP push error: '.$exception->getMessage());
        }
    }

    /**
     * 获取第一条消息
     * pop
     *
     * @return string|null
     * @throws AMQPException
     */
    public static function pop(): ?string
    {
        try {
            /* @var Connection */
            $connection = self::connection();
            $connection->establish();
            $body = $connection->pop();
            $connection->close();

            return $body;
        } catch (Exception $exception) {
            throw new AMQPException('AMQP pop error: '.$exception->getMessage());
        }
    }

    /**
     * 持续监听消息
     * consume
     *
     * @param Closure|null $callback
     * @param array        $config
     *
     * @throws AMQPException
     */
    public static function consume(Closure $callback = null, array $config = []): void
    {
        try {
            /* @var Connection $connection */
            $connection = self::connection();
            $connection->establish();
            $connection->consume($callback, $config);
            $connection->close();
        } catch (Exception $exception) {
            throw new AMQPException('AMQP consume error: '.$exception->getMessage());
        }
    }

    /**
     * client
     *
     * @return Client
     * @throws AMQPException
     */
    public static function client(): Client
    {
        try {
            $connection = self::connection();

            return $connection->getClient();
        } catch (Exception $exception) {
            throw new AMQPException('AMQP client error: '.$exception->getMessage());
        }
    }


    /**
     * __call
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return AmqpDb::{$name}(...$arguments);
    }

}
