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

namespace Desperado\ServiceBus\Scheduler\Store;

use Amp\Promise;
use Desperado\ServiceBus\Scheduler\Data\ScheduledOperation;
use Desperado\ServiceBus\Scheduler\ScheduledOperationId;

/**
 *
 */
interface SchedulerStore
{
    /**
     * Extract operation (load and delete)
     *
     * @param ScheduledOperationId $id
     * @param callable             $postExtract function(ScheduledOperation $operation) {}
     *
     * @return Promise<null>
     *
     * @throws \Desperado\ServiceBus\Scheduler\Exceptions\ScheduledOperationNotFound
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\OperationFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function extract(ScheduledOperationId $id, callable $postExtract): Promise;

    /**
     * Remove operation
     *
     * @param ScheduledOperationId $id
     * @param callable             $postRemove function(?NextScheduledOperation){}
     *
     * @return Promise<bool>
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\OperationFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function remove(ScheduledOperationId $id, callable $postRemove): Promise;

    /**
     * Save new operation
     *
     * @param ScheduledOperation $operation
     * @param callable           $postAdd function(ScheduledOperation $operation, ?NextScheduledOperation) {}
     *
     * @return Promise<null>
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\UniqueConstraintViolationCheckFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\OperationFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function add(ScheduledOperation $operation, callable $postAdd): Promise;
}
