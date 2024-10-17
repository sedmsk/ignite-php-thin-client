<?php

declare(strict_types=1);

namespace Apache\Ignite\Transaction;

enum TransactionConcurrencyModeEnum: int
{
    case OPTIMISTIC = 0;
    case PESSIMISTIC = 1;
}
