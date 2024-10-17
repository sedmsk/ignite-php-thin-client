<?php

declare(strict_types=1);

namespace Apache\Ignite\Transaction;

enum TransactionStateEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
    case CLOSED = 2;
}
