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

namespace Desperado\ServiceBus\Tests\Services\Annotations;

use Desperado\ServiceBus\Services\Annotations\CommandHandler;
use PHPUnit\Framework\TestCase;

/**
 *
 */
final class CommandHandlerTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function withWrongProperties(): void
    {
        new CommandHandler(['qwerty' => 'root']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function withoutAnyFields(): void
    {
        $annotation = new CommandHandler([]);

        static::assertFalse($annotation->validationEnabled());
        static::assertEmpty($annotation->validationGroups());
    }

    /**
     * @test
     *
     * @return void
     */
    public function withValidation(): void
    {
        $annotation = new CommandHandler([
            'validate' => true,
            'groups'   => [
                'qwerty',
                'root'
            ]
        ]);

        static::assertTrue($annotation->validationEnabled());
        static::assertEquals(['qwerty', 'root'], $annotation->validationGroups());
    }
}
