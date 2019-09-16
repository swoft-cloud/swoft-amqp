<?php declare(strict_types=1);

namespace Jcsp\Amqp\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class StreamConnection
 *
 * @package Jcsp\Amqp\Connection
 * @since   2.0
 *
 * @Bean(scope=Bean::PROTOTYPE)
 */
class StreamConnection extends Connection
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

        $this->connection = AMQPStreamConnection::create_connection($auths, $options);
    }

}