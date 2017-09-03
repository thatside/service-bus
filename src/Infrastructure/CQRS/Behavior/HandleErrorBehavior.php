<?php

/**
 * Command Query Responsibility Segregation, Event Sourcing implementation
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @url     https://github.com/mmasiukevich
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\Framework\Infrastructure\CQRS\Behavior;

use Desperado\Framework\Domain\Behavior\BehaviorInterface;
use Desperado\Framework\Domain\Pipeline\PipelineInterface;
use Desperado\Framework\Domain\Task\TaskInterface;
use Desperado\Framework\Infrastructure\CQRS\Task\ErrorHandlerWrappedTask;

/**
 * Run error handler for failed command (if specified)
 */
class HandleErrorBehavior implements BehaviorInterface
{
    /**
     * Error handlers
     *
     * @var array
     */
    private $handlers = [];

    /**
     * Append error handlers
     *
     * @param array $errorHandlers
     *
     * @return void
     */
    public function appendHandlers(array $errorHandlers): void
    {
        $this->handlers = \array_merge($this->handlers, $errorHandlers);
    }

    /**
     * @inheritdoc
     */
    public function apply(PipelineInterface $pipeline, TaskInterface $task): TaskInterface
    {
        return new ErrorHandlerWrappedTask($task, $this->handlers);
    }
}