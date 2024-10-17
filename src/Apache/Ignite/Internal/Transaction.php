<?php

declare(strict_types=1);

namespace Apache\Ignite\Internal;

use Apache\Ignite\Exception\ClientException;
use Apache\Ignite\Internal\Binary\BinaryCommunicator;
use Apache\Ignite\Internal\Binary\ClientOperation;
use Apache\Ignite\Internal\Binary\MessageBuffer;
use Apache\Ignite\Internal\Utils\ArgumentChecker;
use Apache\Ignite\Transaction\TransactionConcurrencyModeEnum;
use Apache\Ignite\Transaction\TransactionInterface;
use Apache\Ignite\Transaction\TransactionIsolationLevelEnum;
use Apache\Ignite\Transaction\TransactionStateEnum;
use Apache\Ignite\Type\ObjectType;
use DateTime;
use DateTimeInterface;

class Transaction implements TransactionInterface
{
    private ?\DateTimeInterface $startTime = null;
    private TransactionIsolationLevelEnum $isolationLevel;
    private TransactionConcurrencyModeEnum $concurrencyMode;
    private TransactionStateEnum $state;
    private int $timeout;
    private BinaryCommunicator $communicator;
    private ?int $id;
    private ?string $label;

    /**
     * @throws ClientException
     */
    public function __construct(BinaryCommunicator             $communicator,
                                TransactionConcurrencyModeEnum $concurrencyMode = TransactionConcurrencyModeEnum::PESSIMISTIC,
                                TransactionIsolationLevelEnum  $isolationLevel = TransactionIsolationLevelEnum::REPEATABLE_READ,
                                int                            $timeout = 0, ?string $label = null)
    {
        if ($timeout < 0) {
            ArgumentChecker::illegalArgument("Timeout value should be a positive integer, $timeout passed instead");
        }

        $this->id = null;
        $this->communicator = $communicator;
        $this->isolationLevel = $isolationLevel;
        $this->concurrencyMode = $concurrencyMode;
        $this->state = TransactionStateEnum::INACTIVE;
        $this->timeout = $timeout;
        $this->label = $label;
    }

    public function __destruct()
    {
        if ($this->id !== null && $this->state !== TransactionStateEnum::CLOSED) {
            $this->state = TransactionStateEnum::CLOSED;
            $this->end(false);
        }
    }

    /**
     * @inheritDoc
     */
    public function getStartTime(): ?DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * @inheritDoc
     */
    public function getIsolationLevel(): int
    {
        return $this->isolationLevel->value;
    }

    /**
     * @inheritDoc
     */
    public function getConcurrencyMode(): int
    {
        return $this->concurrencyMode->value;
    }

    /**
     * @inheritDoc
     */
    public function getTransactionState(): int
    {
        return $this->state->value;
    }

    /**
     * @inheritDoc
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        if ($this->id !== null && $this->state !== TransactionStateEnum::CLOSED) {
            $this->state = TransactionStateEnum::CLOSED;
            $this->end(true);
        }
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
        if ($this->id !== null && $this->state !== TransactionStateEnum::CLOSED) {
            $this->state = TransactionStateEnum::CLOSED;
            $this->end(false);
        }
    }

    public function getTransactionId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    /** Starts the transaction. If the transaction is already started, does nothing
     * @return int|null
     */
    public function start(): ?int
    {
        if ($this->state === TransactionStateEnum::INACTIVE) {
            $this->communicator->send(ClientOperation::TX_START,
                function (MessageBuffer $payload) {
                    $payload->writeByte($this->concurrencyMode->value);
                    $payload->writeByte($this->isolationLevel->value);
                    $payload->writeLong($this->timeout);
                    $payload->writeTypedString($this->label);
                }, function (MessageBuffer $payload) use (&$value) {
                    $value = $this->communicator->readTypedObject($payload, ObjectType::INTEGER);
                });
            $this->startTime = new DateTime();
            $this->id = $value;
            return $value;
        }
        return null;
    }

    private function end(bool $isCommited): void
    {
        $this->communicator->send(ClientOperation::TX_END,
            function (MessageBuffer $payload) use ($isCommited) {
                $payload->writeInteger($this->id);
                $payload->writeBoolean($isCommited);
            });
    }
}