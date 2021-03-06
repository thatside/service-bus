<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation)
 * Supports Saga pattern and Event Sourcing
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Scheduler\Messages\Event;

use Desperado\ServiceBus\Common\Contract\Messages\Event;
use Desperado\ServiceBus\Scheduler\Data\NextScheduledOperation;
use Desperado\ServiceBus\Scheduler\ScheduledOperationId;

/**
 * Scheduler operation emitted
 *
 * @see EmitSchedulerOperation
 */
final class SchedulerOperationEmitted implements Event
{
    /**
     * Scheduled operation identifier
     *
     * @var ScheduledOperationId
     */
    private $id;

    /**
     * Next operation data
     *
     * @var NextScheduledOperation|null
     */
    private $nextOperation;

    /**
     * @param ScheduledOperationId        $id
     * @param NextScheduledOperation|null $nextScheduledOperation
     *
     * @return self
     */
    public static function create(ScheduledOperationId $id, ?NextScheduledOperation $nextScheduledOperation = null): self
    {
        $self = new self();

        $self->id            = $id;
        $self->nextOperation = $nextScheduledOperation;

        return $self;
    }

    /**
     * Receive scheduled operation identifier
     *
     * @return ScheduledOperationId
     */
    public function id(): ScheduledOperationId
    {
        return $this->id;
    }

    /**
     * Receive next operation data
     *
     * @return NextScheduledOperation|null
     */
    public function nextOperation(): ?NextScheduledOperation
    {
        return $this->nextOperation;
    }
}