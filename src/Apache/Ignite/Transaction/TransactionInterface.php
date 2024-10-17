<?php

declare(strict_types=1);

namespace Apache\Ignite\Transaction;

interface TransactionInterface
{
    /** Transaction ID
     * @return int|null
     */
    public function getTransactionId(): ?int;

    /** Start time of this transaction.
     * @return \DateTimeInterface|null
     */
    public function getStartTime(): ?\DateTimeInterface;

    /** Transaction isolation level.
     * @return int
     * @see TransactionIsolationLevelEnum
     */
    public function getIsolationLevel(): int;

    /** Transaction concurrency mode.
     * @return int
     * @see TransactionConcurrencyModeEnum
     */
    public function getConcurrencyMode(): int;

    /** Current transaction state.
     * @see TransactionStateEnum
     * @return int
     */
    public function getTransactionState(): int;

    /** Timeout for this transaction. If transaction times
     *  out prior to its completion, an exception will be thrown.
     *  0 for infinite timeout
     * @return int
     */
    public function getTimeout(): int;

    /** Label of current transaction.
     * @return string|null
     */
    public function getLabel(): ?string;

    /** Commits this transaction.
     * @return void
     */
    public function commit(): void;

    /** Rolls back this transaction.
     * @return void
     */
    public function rollback(): void;
}