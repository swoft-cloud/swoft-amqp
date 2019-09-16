<?php declare(strict_types=1);

namespace Jcsp\Amqp;

use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Swoft\Connection\Pool\AbstractPool;

class Pool extends AbstractPool
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {
        return $this->client->createConnection($this);
    }

}