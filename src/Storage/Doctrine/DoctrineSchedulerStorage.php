<?php

/**
 * PHP Service Bus (CQS implementation)
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\Doctrine;

use Desperado\ServiceBus\Scheduler\Storage\SchedulerStorageInterface;
use Doctrine\DBAL\Connection;

/**
 *
 */
final class DoctrineSchedulerStorage implements SchedulerStorageInterface
{
    /**
     * Doctrine2 connection
     *
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function load(string $id): ?string
    {
        try
        {
            $result = $this->connection
                ->createQueryBuilder()
                ->select('*')
                ->from(SchemaBuilder::TABLE_NAME_SCHEDULER)
                ->where('id = ?')
                ->setParameters([$id])
                ->execute()
                ->fetch();

            return true === \is_array($result) && true === isset($result['data'])
                ? \hex2bin(
                    true === \is_resource($result['data'])
                        ? \stream_get_contents($result['data'], -1, 0)
                        : $result['data']
                )
                : null;
        }
        catch(\Throwable $throwable)
        {
            throw DoctrineExceptionConverter::convert($throwable);
        }
    }

    /**
     * @inheritdoc
     */
    public function add(string $id, string $registryPayload): void
    {
        try
        {
            $this->connection
                ->createQueryBuilder()
                ->insert(SchemaBuilder::TABLE_NAME_SCHEDULER)
                ->values([
                    'id'   => '?',
                    'data' => '?'
                ])
                ->setParameters([$id, \bin2hex($registryPayload)])
                ->execute();
        }
        catch(\Throwable $throwable)
        {
            throw DoctrineExceptionConverter::convert($throwable);
        }
    }

    /**
     * @inheritdoc
     */
    public function update(string $id, string $registryPayload): void
    {
        try
        {
            $this->connection
                ->createQueryBuilder()
                ->update(SchemaBuilder::TABLE_NAME_SCHEDULER)
                ->set('data', '?')
                ->where('id = ?')
                ->setParameters([\bin2hex($registryPayload), $id])
                ->execute();
        }
        catch(\Throwable $throwable)
        {
            throw DoctrineExceptionConverter::convert($throwable);
        }
    }
}
