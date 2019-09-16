<?php declare(strict_types=1);


namespace Jcsp\Amqp\Connection;

use Closure;
use Jcsp\Amqp\Client;
use Jcsp\Amqp\Contract\ConnectionInterface;
use Jcsp\Amqp\Pool;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Swoft\Connection\Pool\AbstractConnection;
use Throwable;
use Exception;

/**
 * Class Connection
 *
 * @since 2.0
 */
abstract class Connection extends AbstractConnection implements ConnectionInterface
{

    /**
     * @var AMQPStreamConnection|AMQPSocketConnection|AMQPSSLConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var string
     */
    protected $exchange;

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @param Pool   $pool
     * @param Client $client
     */
    public function initialize(Pool $pool, Client $client): void
    {
        $this->pool   = $pool;
        $this->client = $client;
    }

    /**
     * close
     *
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * reconnect
     *
     * @return bool
     */
    public function reconnect(): bool
    {
        try {
            $this->create();
        } catch (Throwable $e) {
            Log::error('RabbitMQ reconnect error(%s)', $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * channel
     *
     * @param null $channelId
     *
     * @return AMQPChannel
     */
    public function channel($channelId = null): AMQPChannel
    {
        $this->channel = $this->connection->channel($channelId);

        return $this->channel;
    }

    /**
     * exchange
     *
     * @param array $exchangeConf
     *
     * @return string
     * @throws \Jcsp\Amqp\Exception\AMQPException
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function exchange(array $exchangeConf = []): string
    {
        $config = empty($exchangeConf) ? $this->client->getExchange() : $exchangeConf;
        $this->channel->exchange_declare(
            $config['name'],
            $config['type'],
            $config['passive'] ?? false,
            $config['durable'] ?? true,
            $config['auto_delete'] ?? false,
            $config['internal'] ?? false,
            $config['nowait'] ?? false,
            $config['arguments'] ?? [],
            $config['ticket'] ?? null
        );

        $this->exchange = $config['name'];

        return $this->exchange;
    }

    /**
     * queue
     *
     * @param array $queueConf
     *
     * @return string
     * @throws \Jcsp\Amqp\Exception\AMQPException
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function queue(array $queueConf = []): string
    {
        $config = empty($queueConf) ? $this->client->getQueue() : $queueConf;
        $this->channel->queue_declare(
            $config['name'],
            $config['passive'] ?? false,
            $config['durable'] ?? true,
            $config['exclusive'] ?? false,
            $config['auto_delete'] ?? false,
            $config['nowait'] ?? false,
            $config['arguments'] ?? [],
            $config['ticket'] ?? null
        );

        $this->queue = $config['name'];

        return $this->queue;
    }

    /**
     * 构建连接
     * establish
     *
     * @param null  $channelId
     * @param array $routeConf
     * @param array $queueConf
     * @param array $exchangeConf
     *
     * @return AMQPChannel
     * @throws \Jcsp\Amqp\Exception\AMQPException
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function establish($channelId = null, array $routeConf = [], array $queueConf = [], array $exchangeConf = []): AMQPChannel
    {
        $config   = $routeConf ?? $this->client->getRoute();
        $channel  = $this->channel($channelId);
        $queue    = $this->queue($queueConf);
        $exchange = $this->exchange($exchangeConf);

        $channel->queue_bind(
            $queue,
            $exchange,
            $config['key'] ?? '',
            $config['nowait'] ?? false,
            $config['arguments'] ?? [],
            $config['ticket'] ?? null
        );

        return $channel;
    }

    /**
     * 推送消息
     * push
     *
     * @param string $body
     * @param array  $properties
     * @param string $routeKey
     *
     * @throws \PhpAmqpLib\Exception\AMQPConnectionClosedException
     */
    public function push(string $body, array $properties = [], string $routeKey = ''): void
    {
        $message = new AMQPMessage($body, $properties);
        $this->channel->basic_publish($message, $this->exchange, $routeKey);
    }

    /**
     * 获取第一条消息
     * pop
     *
     * @return string|null
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function pop(): ?string
    {
        /* @var AMQPMessage $message */
        $message = $this->channel->basic_get($this->queue, true);

        return $message ? $message->body : null;
    }

    /**
     * 持续订阅消息
     * consume
     *
     * @param Closure|null $callback
     * @param array        $consume
     *
     * @return string
     * @throws \ErrorException
     * @throws \PhpAmqpLib\Exception\AMQPOutOfBoundsException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function consume(Closure $callback = null, array $consume = []): void
    {
        //消费消息
        $config = empty($consume) ? $this->client->getConsume() : $consume;
        $this->channel->basic_consume(
            $this->queue,
            $config['consumer_tag'] ?? '',
            $config['no_local'] ?? false,
            $config['no_ack'] ?? false,
            $config['exclusive'] ?? false,
            $config['nowait'] ?? false,
            function (AMQPMessage $message) use ($callback, $config) {
                $cancel = is_array($config['cancel_tag']) ? in_array($message->body, $config['cancel_tag']) : $message->body == $config['cancel_tag'];
                $this->channel->basic_ack($message->delivery_info['delivery_tag']);
                if ($cancel) {
                    $this->channel->basic_cancel($message->delivery_info['consumer_tag']);
                }
                !empty($callback) && $callback($message);
            }
        );
        //等待获取队列
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    /**
     * getClient
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
