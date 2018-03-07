<?php

/**
 * PHP Service Bus (CQS implementation)
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Annotations\Services;

use Desperado\ServiceBus\Annotations\AbstractAnnotation;

/**
 * Annotation pointing to a service
 *
 * @Annotation
 * @Target("CLASS")
 */
final class Service extends AbstractAnnotation
{
    private const DEFAULT_LOGGER_CHANNEL = 'default';

    /**
     * Logger channel
     *
     * The context of the message must implement the interface "ContextLoggerInterface"
     *
     * @var string|null
     */
    private $loggerChannel;

    /**
     * Get service logger channel
     *
     * @return string
     */
    public function getLoggerChannel(): string
    {
        return $this->loggerChannel ?? self::DEFAULT_LOGGER_CHANNEL;
    }
}
