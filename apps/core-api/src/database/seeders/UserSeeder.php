<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🧑 Создаём тестового пользователя
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@multility.local'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        $apiKey = ApiKey::query()->create([
            'user_id'     => $user->id,
            'name'        => 'Default Key',
            'key'         => hash('sha256', Str::uuid()->toString() . Str::random(40)),
            'usage_count' => 0,
        ]);

        $this->command?->info("✅ Demo user: {$user->email}");
        $this->command?->info("🔑 API Key: {$apiKey->key}");
    }
}
