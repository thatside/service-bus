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

namespace Desperado\ServiceBus\Storage\SQL\AmpPostgreSQL;

use function Amp\call;
use Amp\Postgres\ConnectionConfig;
use function Amp\Postgres\pool;
use Amp\Postgres\Pool;
use Amp\Promise;
use Amp\Sql\Transaction;
use Amp\Success;
use Desperado\ServiceBus\Storage\StorageAdapter;
use Desperado\ServiceBus\Storage\StorageConfiguration;

/**
 *
 */
final class AmpPostgreSQLAdapter implements StorageAdapter
{
    /**
     * Connections pool
     *
     * @var Pool
     */
    private $pool;

    /**
     * DSN example:
     *
     * user=root password=qwerty host=localhost port=5342 dbname=test options='--client_encoding=UTF8'
     *
     * @param StorageConfiguration $configuration
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function __construct(StorageConfiguration $configuration)
    {
        $queryData  = $configuration->queryParameters();
        $this->pool = pool(
            new ConnectionConfig(
                $configuration->host(),
                $configuration->port() ?? ConnectionConfig::DEFAULT_PORT,
                $configuration->username(),
                $configuration->password(),
                $configuration->databaseName()
            ),
            $queryData['max_connections'] ?? Pool::DEFAULT_MAX_CONNECTIONS,
            $queryData['idle_timeout'] ?? Pool::DEFAULT_IDLE_TIMEOUT
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(string $queryString, array $parameters = []): Promise
    {
        $connectionsPool = $this->pool;

        /** @psalm-suppress InvalidArgument */
        return call(
            static function(string $queryString, array $parameters = []) use ($connectionsPool): \Generator
            {
                try
                {
                    /** @var \Amp\Sql\Link $connection */
                    $connection = yield $connectionsPool->extractConnection();

                    /** @var \Amp\Sql\Statement $statement */
                    $statement = yield $connection->prepare($queryString);

                    /** @psalm-suppress UndefinedClass Class or interface Amp\Postgres\TupleResult does not exist */
                    $result = new AmpPostgreSQLResultSet(
                        yield $statement->execute($parameters)
                    );

                    $connection->close();

                    return $result;
                }
                catch(\Throwable $throwable)
                {
                    throw AmpExceptionConvert::do($throwable);
                }
            },
            $queryString,
            $parameters
        );
    }

    /**
     * @inheritdoc
     */
    public function supportsTransaction(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function transaction(): Promise
    {
        $connectionsPool = $this->pool;

        /** @psalm-suppress InvalidArgument */
        return call(
            static function() use ($connectionsPool): \Generator
            {
                try
                {
                    return yield new Success(
                        new AmpPostgreSQLTransactionAdapter(
                            yield $connectionsPool->beginTransaction(Transaction::ISOLATION_COMMITTED)
                        )
                    );
                }
                    // @codeCoverageIgnoreStart
                catch(\Throwable $throwable)
                {
                    throw AmpExceptionConvert::do($throwable);
                }
                // @codeCoverageIgnoreEnd
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function unescapeBinary($payload): string
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        return \pg_unescape_bytea($payload);
    }
}