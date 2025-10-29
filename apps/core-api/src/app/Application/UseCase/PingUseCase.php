<?php

namespace App\Application\UseCase;

final class PingUseCase
{
    public function handle(): array
    {
        return [
            'status' => 'ok',
            'app' => config('app.name'),
            'time' => now()->toISOString(),
        ];
    }
}
