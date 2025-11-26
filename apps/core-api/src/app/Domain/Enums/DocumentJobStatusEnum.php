<?php

namespace App\Domain\Enums;

enum DocumentJobStatusEnum: string
{
    case Pending  = 'pending';   // задача создана, но воркер её ещё не взял
    case Running  = 'running';   // воркер выполняет генерацию
    case Done     = 'done';      // статус успешного завершения
    case Failed   = 'failed';    // генерация упала
}
