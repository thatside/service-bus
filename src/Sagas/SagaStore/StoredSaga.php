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

namespace Desperado\ServiceBus\Sagas\SagaStore;

use Desperado\ServiceBus\Sagas\SagaId;
use Desperado\ServiceBus\Sagas\SagaStatus;
use Desperado\ServiceBus\Sagas\SagaStore\Exceptions\RestoreSagaFailed;

/**
 * The saved saga representation
 */
final class StoredSaga
{
    /**
     * Saga identifier
     *
     * @var string
     */
    private $id;

    /**
     * Saga identifier class
     *
     * @var string
     */
    private $idClass;

    /**
     * Saga class
     *
     * @var string
     */
    private $sagaClass;

    /**
     * Serialized representation of the saga
     *
     * @var string
     */
    private $payload;

    /**
     * Current status of the saga
     *
     * @var string
     */
    private $status;

    /**
     * ate of creation of the saga
     *
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * Closing date of the saga
     *
     * @var \DateTimeImmutable|null
     */
    private $closedAt;

    /**
     * @param SagaId                  $id
     * @param SagaStatus              $status
     * @param string                  $payload
     * @param \DateTimeImmutable      $createdAt
     * @param \DateTimeImmutable|null $closedAt
     *
     * @return self
     */
    public static function create(
        SagaId $id,
        SagaStatus $status,
        string $payload,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $closedAt
    ): self
    {
        $self = new self();

        $self->id        = (string) $id;
        $self->idClass   = \get_class($id);
        $self->sagaClass = $id->sagaClass();
        $self->status    = (string) $status;
        $self->payload   = $payload;
        $self->createdAt = $createdAt;
        $self->closedAt  = $closedAt;

        return $self;
    }

    /**
     * @param string      $id
     * @param string      $idClass
     * @param string      $sagaClass
     * @param string      $payload
     * @param string      $status
     * @param string      $createdAt
     * @param null|string $closedAt
     *
     * @return self
     *
     * @throws \Desperado\ServiceBus\Sagas\SagaStore\Exceptions\RestoreSagaFailed
     */
    public static function restore(
        string $id,
        string $idClass,
        string $sagaClass,
        string $payload,
        string $status,
        string $createdAt,
        ?string $closedAt
    ): self
    {
        try
        {
            return self::create(
                self::identifierInstantiator($idClass, $id, $sagaClass),
                SagaStatus::create($status),
                $payload,
                new \DateTimeImmutable($createdAt),
                null !== $closedAt ? new \DateTimeImmutable($closedAt) : null
            );
        }
        catch(\Throwable $throwable)
        {
            throw new RestoreSagaFailed($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @param array $rowData
     *
     * @return self
     *
     * @throws @throws \Desperado\ServiceBus\Sagas\SagaStore\Exceptions\RestoreSagaFailed
     */
    public static function fromRow(array $rowData): self
    {
        return self::restore(
            $rowData['id'],
            $rowData['identifier_class'],
            $rowData['saga_class'],
            $rowData['payload'],
            $rowData['state_id'],
            $rowData['created_at'],
            '' !== (string) $rowData['closed_at']
                ? $rowData['closed_at']
                : null
        );
    }

    /**
     * Receive saga id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Receive saga id class
     *
     * @return string
     */
    public function idClass(): string
    {
        return $this->idClass;
    }

    /**
     * Receive saga class
     *
     * @return string
     */
    public function sagaClass(): string
    {
        return $this->sagaClass;
    }

    /**
     * Receive serialized saga payload
     *
     * @return string
     */
    public function payload(): string
    {
        return $this->payload;
    }

    /**
     * Receive current status
     *
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * Receive created_at date
     *
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Receive closed_at date
     *
     * @return \DateTimeImmutable|null
     */
    public function closedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    /**
     * Receive created_at as string
     *
     * @param string $format
     *
     * @return string
     */
    public function formatCreatedAt(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->createdAt->format($format);
    }

    /**
     * Receive closed_at as string (if defined)
     *
     * @param string $format
     *
     * @return string|null
     */
    public function formatClosedAt(string $format = 'Y-m-d H:i:s'): ?string
    {
        if(null !== $this->closedAt)
        {
            return $this->closedAt->format($format);
        }

        return null;
    }


    /**
     * Create identifier instance
     *
     * @template        SagaId
     * @template-typeof SagaId $idClass
     *
     * @param string $idClass
     * @param string $idValue
     * @param string $sagaClass
     *
     * @return SagaId
     */
    private static function identifierInstantiator(string $idClass, string $idValue, string $sagaClass): SagaId
    {
        return new $idClass($idValue, $sagaClass);
    }

    /**
     * Close constructor
     */
    private function __construct()
    {

    }
}
