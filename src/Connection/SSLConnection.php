<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Swoft\Amqp\Connection;

use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class SSLConnection
 *
 * @since 2.0
 *
 * @Bean(scope=Bean::PROTOTYPE)
 */
class SSLConnection extends Connection
{
    public function create(): void
    {
    }
}
