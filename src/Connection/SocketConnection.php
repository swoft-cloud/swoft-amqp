<?php declare(strict_types=1);

namespace Jcsp\Amqp\Connection;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class SocketConnection
 *
 * @package Jcsp\Amqp\Connection
 * @since   2.0
 *
 * @Bean(scope=Bean::PROTOTYPE)
 */
class SocketConnection extends Connection
{

    /**
     * create
     *
     * @throws \Exception
     */
    public function create(): void
    {
        $auths   = $this->client->getAuths();
        $options = $this->client->getOptions();

        $this->connection = AMQPSocketConnection::create_connection($auths, $options);
    }

}