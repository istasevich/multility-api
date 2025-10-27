# Multility Core API (Laravel)

One API. Infinite Utilities. This is the backend core for Multility — a unified utilities platform. The Core API serves HTTP endpoints, orchestrates business use‑cases, persists data, and offloads heavy work to Workers (FastAPI) via HTTP/Redis.

---

## Stack

* **Laravel** (PHP 8.3, FPM)
* **PostgreSQL**
* **Redis** (cache, queues)
* **Nginx** reverse proxy (Next.js landing → `/`, Laravel → `/api`, FastAPI worker → `/worker`)
* **Docker Compose** for local dev

---

## Quick Start

```bash
# Inside container
cd /var/www/html/src
cp .env.example .env
php artisan key:generate
php artisan migrate        # when DB is configured
php artisan serve          # optional, for local debugging
```

Health checks:

* **Core API**: `GET /api/ping` (simple) and `GET /api/health` (DB/Redis)
* **Worker**: `GET /worker/ping`

---

## Architecture: “Lean Clean” Laravel

We follow a **minimal Clean Architecture**—just enough structure to scale, without silver‑bullets or over‑abstraction. Keep it pragmatic:

```
app/
├─ Application/               # Use-cases, DTOs, Query objects
│  ├─ UseCase/
│  └─ DTO/
├─ Domain/                    # Entities, Value Objects, domain Contracts
│  ├─ Entity/
│  ├─ ValueObject/
│  └─ Contract/               # Repository interfaces, domain services
├─ Infrastructure/            # Adapters to outside world (DB, HTTP, Redis, 3rd parties)
│  ├─ Persistence/
│  │  └─ Eloquent/
│  ├─ Http/
│  └─ Cache/
├─ Http/                      # Presentation (Controllers, Requests, Resources)
│  ├─ Controllers/
│  ├─ Requests/
│  └─ Resources/
├─ Providers/
└─ Support/                   # Helpers, Traits, Enums, Exceptions
```

**Guidelines**

* Stick to **Laravel conventions** (routing, controllers, requests) for fast delivery.
* Encapsulate business logic in **Application\UseCase**.
* Keep **Domain** pure (no Laravel facades here). Contracts live in `Domain\Contract`.
* **Infrastructure** implements those contracts (Eloquent repositories, HTTP clients, etc.).
* Bind contracts → implementations in a **Service Provider**.
* Prefer `protected` over `private`. Empty constructors: `// Nothing`.
* DTOs should be dumb, immutable when possible (`readonly` where appropriate).

---

## Request Flow

1. **HTTP Controller** (validation via Form Requests)
2. **UseCase** executes business logic (orchestrates repositories/services)
3. **Domain Contracts** used to access infrastructure
4. **Infrastructure** (Eloquent/HTTP/Cache) fulfills the contracts
5. **Resource** transforms output to API response

---

## Example: Health Check as Use Case

**routes/api.php**

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/ping', fn () => response()->json(['status' => 'ok', 'app' => 'Multility Core API']));
Route::get('/health', HealthController::class);
```

**app/Http/Controllers/HealthController.php**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = ['db' => false, 'redis' => false];

        try { DB::select('select 1'); $checks['db'] = true; } catch (\Throwable) {}
        try { Redis::connection()->ping(); $checks['redis'] = true; } catch (\Throwable) {}

        return response()->json([
            'status' => ($checks['db'] && $checks['redis']) ? 'ok' : 'degraded',
            'checks' => $checks,
        ]);
    }
}
```

> Keep controllers thin; prefer `__invoke()` for single‑action endpoints.

---

## Example: Minimal Feature Slice

Suppose we need **Rates** (fiat↔crypto conversions).

**Domain Contract**

```php
<?php
namespace App\Domain\Contract;

interface RateProvider
{
    public function convert(string $from, string $to, float $amount): float;
}
```

**Infrastructure Implementation (HTTP)**

```php
<?php
namespace App\Infrastructure\Http;

use App\Domain\Contract\RateProvider;
use Illuminate\Support\Facades\Http;

final class HttpRateProvider implements RateProvider
{
    public function __construct(
    ) {
        // Nothing
    }

    public function convert(string $from, string $to, float $amount): float
    {
        $res = Http::get(env('RATES_API').'/convert', compact('from','to','amount'));
        $data = $res->json();
        return (float)($data['result'] ?? 0.0);
    }
}
```

**Application Use Case**

```php
<?php
namespace App\Application\UseCase\Rates;

use App\Domain\Contract\RateProvider;

final class ConvertAmount
{
    public function __construct(
        protected RateProvider $provider,
    ) {
        // Nothing
    }

    public function handle(string $from, string $to, float $amount): float
    {
        return $this->provider->convert($from, $to, $amount);
    }
}
```

**Controller**

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Application\UseCase\Rates\ConvertAmount;
use App\Http\Requests\ConvertRequest;

final class ConvertController extends Controller
{
    public function __invoke(ConvertRequest $request, ConvertAmount $uc): JsonResponse
    {
        $v = $request->validated();
        $sum = $uc->handle($v['from'], $v['to'], (float)$v['amount']);
        return response()->json(['result' => $sum]);
    }
}
```

**Form Request**

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ConvertRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['required','string','size:3'],
            'to' => ['required','string','size:3'],
            'amount' => ['required','numeric','min:0'],
        ];
    }
}
```

**Binding in Service Provider**

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Contract\RateProvider;
use App\Infrastructure\Http\HttpRateProvider;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RateProvider::class, HttpRateProvider::class);
    }

    public function boot(): void
    {
        // Nothing
    }
}
```

Register the provider in `config/app.php` → `providers`.

---

## Error Handling & Responses

* Throw domain exceptions from **UseCases**; map them to HTTP in a custom exception handler or with middleware.
* Use **HTTP Resources** for stable response shapes.
* Validation via **Form Requests** (keeps controllers clean).

---

## Conventions

* **SOLID**: composition over inheritance; prefer final classes for use‑cases/controllers.
* **Visibility**: `protected` fields/methods preferred. Empty constructors include `// Nothing`.
* **Naming**: `UseCase::handle()` or `execute()`; controllers `__invoke()`.
* **Commits**: Conventional Commits (`feat:`, `fix:`, `chore:`, etc.).
* **Static analysis**: phpstan level high (tune gradually).
* **Tests**: PHPUnit 11; feature tests for endpoints, unit tests for use‑cases and domain.

---

## Testing

```bash
php artisan test              # run all
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

Sample Feature Test (Health):

```php
<?php
it('returns ok for health when deps are up', function () {
    $resp = $this->getJson('/api/health');
    $resp->assertOk();
    $resp->assertJsonStructure(['status','checks' => ['db','redis']]);
});
```

---

## Environment

Key variables (see `.env`):

```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=multility
DB_USERNAME=multility
DB_PASSWORD=secret

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

---

## Queues & Jobs

* Use `QUEUE_CONNECTION=redis`
* Create jobs in `app/Application/Job` if they are part of use‑cases; infrastructure jobs in `app/Infrastructure/Queue`.
* Run worker: `php artisan queue:work --tries=1` (tune for env)

---

## Coding Style

* PSR‑12, strict types where possible.
* Avoid facades in **Domain**; allowed in **Infrastructure**/**Http**/**Support**.
* Request validation in `Http/Requests`, response shaping in `Http/Resources`.

---

## Roadmap (MVP → v0.1)

* [x] Health checks (`/api/ping`, `/api/health`)
* [ ] Base modules: Rates, OCR+Summary proxy, PDF/Screenshot proxy
* [ ] Webhook Relay endpoints + visual logs (persist to DB)
* [ ] Auth (API keys) middleware + rate limiting per key
* [ ] Observability: request/response logs, error tracking hook

---

## Troubleshooting

* **Redis degraded**: ensure `phpredis` extension is enabled and `REDIS_HOST=redis`, `REDIS_PORT=6379` inside Docker network.
* **Nginx 404 on /api**: check that `/api` location rewrites to `/api/index.php` and `SCRIPT_FILENAME` points to `/var/www/html/src/public/index.php` (or use `$document_root`).
* **Permissions**: `storage/` and `bootstrap/cache` must be writable by PHP-FPM user.

---

## License

Proprietary © Multility. All rights reserved.
