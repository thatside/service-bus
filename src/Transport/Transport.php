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

namespace Desperado\ServiceBus\Transport;

use Amp\Promise;

/**
 *
 */
interface Transport
{
    /**
     * Create topic
     *
     * @param Topic $topic
     *
     * @return void

     * @throws \Desperado\ServiceBus\Transport\Exceptions\CreateTopicFailed
     */
    public function createTopic(Topic $topic): void;

    /**
     * Bind topic to topic with specified key
     *
     * @param TopicBind $to
     *
     * @return void
     *
     * @throws \Desperado\ServiceBus\Transport\Exceptions\BindFailed
     */
    public function bindTopic(TopicBind $to): void;

    /**
     * Create queue
     *
     * @param Queue $queue
     *
     * @return void
     *
     * @throws \Desperado\ServiceBus\Transport\Exceptions\CreateQueueFailed
     */
    public function createQueue(Queue $queue): void;

    /**
     * Bind queue to topic with specified key
     *
     * @param QueueBind $to
     *
     * @return void
     *
     * @throws \Desperado\ServiceBus\Transport\Exceptions\BindFailed
     */
    public function bindQueue(QueueBind $to): void;

    /**
     * Create publisher
     *
     * @return Publisher
     */
    public function createPublisher(): Publisher;

    /**
     * Create consumer
     *
     * @param Queue $listenQueue
     *
     * @return Consumer
     */
    public function createConsumer(Queue $listenQueue): Consumer;

    /**
     * Close context
     *
     * @return Promise<null>
     */
    public function close(): Promise;
}
