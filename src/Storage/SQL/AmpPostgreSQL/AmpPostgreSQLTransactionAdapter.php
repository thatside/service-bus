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

use Amp\Postgres\Transaction as AmpTransaction;
use function Amp\call;
use Amp\Promise;
use Amp\Success;
use Desperado\ServiceBus\Storage\TransactionAdapter;

/**
 *  Async PostgreSQL transaction adapter
 */
final class AmpPostgreSQLTransactionAdapter implements TransactionAdapter
{
    /**
     * Original transaction object
     *
     * @var AmpTransaction
     */
    private $transaction;

    /**
     * @param AmpTransaction $transaction
     */
    public function __construct(AmpTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function __destruct()
    {
        $this->transaction->close();
    }

    /**
     * @inheritdoc
     */
    public function execute(string $queryString, array $parameters = []): Promise
    {
        $transaction = $this->transaction;

        /** @psalm-suppress InvalidArgument */
        return call(
            static function(string $queryString, array $parameters = []) use ($transaction): \Generator
            {
                try
                {
                    /** @var \Amp\Sql\Statement $statement */
                    $statement = yield $transaction->prepare($queryString);

                    /** @var \Amp\Postgres\PooledResultSet $result */
                    $result = yield $statement->execute($parameters);

                    unset($statement);

                    return yield new Success(
                        new AmpPostgreSQLResultSet($result)
                    );
                }
                    // @codeCoverageIgnoreStart
                catch(\Throwable $throwable)
                {
                    throw AmpExceptionConvert::do($throwable);
                }
                // @codeCoverageIgnoreEnd
            },
            $queryString,
            $parameters
        );
    }

    /**
     * @inheritdoc
     */
    public function commit(): Promise
    {
        $transaction = $this->transaction;

        /** @psalm-suppress InvalidArgument */
        return call(
            static function() use ($transaction): \Generator
            {
                try
                {
                    yield $transaction->commit();

                    $transaction->close();
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
    public function rollback(): Promise
    {
        $transaction = $this->transaction;

        /** @psalm-suppress InvalidArgument */
        return call(
            static function() use ($transaction): \Generator
            {
                try
                {
                    return yield $transaction->rollback();
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
}
