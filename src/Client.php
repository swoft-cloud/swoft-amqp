<?php declare(strict_types=1);

namespace Jcsp\Amqp;

use Jcsp\Amqp\Connection\Connection;
use Jcsp\Amqp\Connection\SocketConnection;
use Jcsp\Amqp\Connection\SSLConnection;
use Jcsp\Amqp\Connection\StreamConnection;
use Jcsp\Amqp\Exception\AMQPException;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use Swoft\Bean\BeanFactory;

/**
 * Class Client
 *
 * @package Jcsp\Amqp
 * @since   2.0
 */
class Client
{

    /**
     * StreamConnection
     */
    const STREAM = 'StreamConnection';

    /**
     * SocketConnection
     */
    const SOCKET = 'SocketConnection';

    /**
     * SSLConnection
     */
    const SSL = 'SSLConnection';

    /**
     * @var string
     */
    private $driver = self::STREAM;

    /**
     * must contain keys (host, port, user, password, vhost)
     *
     * @var array
     */
    private $auths = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $exchange = [
        'name'        => 'exchange',
        'type'        => AMQPExchangeType::DIRECT,
        'passive'     => false,
        'durable'     => true,
        'auto_delete' => false,
        'internal'    => false,
        'nowait'      => false,
        'arguments'   => [],
        'ticket'      => null,
    ];

    /**
     * @var array
     */
    private $queue = [
        'name'        => 'queue',
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => false,
        'nowait'      => false,
        'arguments'   => [],
        'ticket'      => null,
    ];

    /**
     * @var array
     */
    private $route = [
        'key'       => '',
        'nowait'    => false,
        'arguments' => [],
        'ticket'    => null,
    ];

    /**
     * @var array
     */
    private $consume = [
        'cancel_tag'   => ['exit', 'quit'],
        'consumer_tag' => 'consumer',
        'no_local'     => false,
        'no_ack'       => false,
        'exclusive'    => false,
        'nowait'       => false,
    ];

    public function createConnection(Pool $pool): Connection
    {
        switch ($this->driver) {
            default:
            case self::STREAM:
                $connection = bean(StreamConnection::class);
                break;
            case self::SOCKET:
                $connection = bean(SocketConnection::class);
                break;
            case self::SSL:
                $connection = bean(SSLConnection::class);
                break;
        }
        $connection->initialize($pool, $this);
        $connection->create();

        return $connection;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * getAuths
     *
     * @return array
     */
    public function getAuths(): array
    {
        return $this->auths;
    }

    /**
     * getOptions
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * getExchange
     *
     * @return array
     * @throws AMQPException
     */
    public function getExchange(): array
    {
        if (!isset($this->exchange['name']) || !isset($this->exchange['type'])) {
            throw new AMQPException(sprintf('AMQP(dirver=%s) exchange error, must contain name and type', $this->driver));
        }

        return $this->exchange;
    }

    /**
     * getQueue
     *
     * @return array
     * @throws AMQPException
     */
    public function getQueue(): array
    {
        if (!isset($this->queue['name'])) {
            throw new AMQPException(sprintf('AMQP(dirver=%s) queue error, must contain name', $this->driver));
        }

        return $this->queue;
    }

    /**
     * getRoute
     *
     * @return array
     */
    public function getRoute(): array
    {
        return $this->route;
    }

    /**
     * getConsume
     *
     * @return array
     */
    public function getConsume()
    {
        return $this->consume;
    }

}