<?php

declare(strict_types=1);

namespace Apache\Ignite\Transaction;

enum TransactionIsolationLevelEnum: int
{
    case READ_COMMITTED = 0;
    case REPEATABLE_READ = 1;
    case SERIALIZABLE = 2;
}
