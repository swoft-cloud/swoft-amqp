<?php declare(strict_types=1);

namespace Jcsp\Amqp\Connection;

use PhpAmqpLib\Connection\AMQPSSLConnection;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class SSLConnection
 *
 * @package Jcsp\Amqp\Connection
 * @since   2.0
 *
 * @Bean(scope=Bean::PROTOTYPE)
 */
class SSLConnection extends Connection
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

        $this->connection = AMQPSSLConnection::create_connection($auths, ['ssl_options' => $options]);
    }

}