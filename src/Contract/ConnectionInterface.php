<?php declare(strict_types=1);

namespace Jcsp\Amqp\Contract;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class ConnectionInterface
 *
 * @since 2.0
 */
interface ConnectionInterface
{

    public function create(): void;

}