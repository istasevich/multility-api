<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'api_keys';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'key',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ApiKey $model): void {
            if (empty($model->key)) {
                $model->key = hash('sha256', Str::uuid()->toString() . Str::random(40));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMaskedKeyAttribute(): string
    {
        return substr($this->key, 0, 6) . str_repeat('*', 10);
    }
}
