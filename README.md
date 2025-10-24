# 🌌 Multility API  
**One API. Infinite Utilities.**

Unified API-hub for modern developers.  
Generate PDFs, extract text, relay webhooks, convert currencies — all from one endpoint.

---

## 🚀 MVP Overview

**Multility API** combines multiple developer utilities into a single modular API platform.

### 🔹 Core MVP Modules
| Module | Endpoint | Description |
|---------|-----------|-------------|
| 🧾 **PDF & Screenshot Suite** | `/api/pdf/html`, `/api/pdf/url` | Convert HTML/URL → PDF or image |
| 🧠 **AI Summary + OCR Combo** | `/api/ocr/upload` | Extract text from image/PDF and summarize |
| 🛰️ **Webhook Relay + Visual Logs** | `/api/webhook/{token}` | Receive, inspect, and replay webhooks |
| 💱 **Crypto & Rates Mini-API** | `/api/crypto/convert` | Real-time fiat ↔ crypto conversion |

> Phase 2 will add: `HTML2Text / Metadata Extractor`

---

## ⚙️ Tech Stack

| Layer | Technology | Purpose |
|-------|-------------|----------|
| **Core API** | Laravel 11 (PHP 8.3) | Main API gateway, routing, auth |
| **Async Workers** | Python (FastAPI + Celery) | Heavy tasks: PDF/OCR processing |
| **Frontend / Landing** | Next.js + Tailwind CSS | Public docs, API showcase |
| **Admin Panel** | Filament (Laravel) | Manage API keys, logs, usage |
| **Database** | PostgreSQL + Redis | Persistent storage + queue/cache |
| **Storage** | S3 / Cloudflare R2 | Store PDFs, screenshots, logs |
| **CI/CD** | GitHub Actions + Docker Compose | Continuous integration & deploy |

---

## 🧱 Project Structure

---
multility-api/
├── apps/
│ ├── core-api/ → Laravel Core (API Gateway)
│ ├── worker/ → Python / FastAPI Worker
│ └── landing/ → Next.js Frontend (Landing & Docs)
│
├── shared/
│ ├── docker/ → Compose configs
│ ├── env/ → Environment templates
│ └── scripts/ → Deploy & build utilities
│
└── docker-compose.yml
---

## 🔐 Authentication

All requests require an **API Key**.

Authorization: Bearer <YOUR_API_KEY>


### Free Tier (MVP)
- 100 requests / day per key  
- No billing required  
- Redis-based rate limiter  
- Usage logs available in admin dashboard

---

## 🧰 Installation

### 1️⃣ Clone the repo
```bash
git clone https://github.com/your-org/multility-api.git
cd multility-api
2️⃣ Copy environment file
cp .env.example .env
3️⃣ Launch Docker environment
docker-compose up -d --build
4️⃣ Run migrations & seeders
docker exec -it multility-core-api php artisan migrate --seed
5️⃣ Access services
Service	URL
API	http://localhost:PORT
Landing	http://localhost:3000
Admin (Filament)	http://localhost:PORT/admin

🧩 Example Requests
🧾 PDF from HTML
curl -X POST https://api.multility.dev/api/pdf/html \
  -H "Authorization: Bearer YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{"html":"<h1>Hello Multility</h1>"}'
💱 Convert USD → BTC
curl "https://api.multility.dev/api/crypto/convert?from=USD&to=BTC" \
  -H "Authorization: Bearer YOUR_KEY"
🧠 Architecture Overview
arduino
Client → Laravel Core API → Redis Queue → Python Worker → S3/DB → Result
Flow:

Request accepted and validated via API Key

Light tasks handled instantly (Crypto, Webhooks)

Heavy tasks sent to worker queue (PDF/OCR)

Worker processes and stores results

Client polls /result/{job_id} for output

🌐 Deployment
MVP Setup
Single VPS running Docker Compose

Containers:
php-fpm, nginx, redis, postgres, worker, nextjs

Storage via AWS S3 or Cloudflare R2

CI/CD with GitHub Actions auto-deploy

Future Scaling
Split workers (Python/Go) to microservices

Move to Kubernetes or AWS ECS

Add metered billing (Stripe)

💰 Pricing & Access Model
Plan	Requests / Day	Cost	Notes
Free	100	$0	MVP tier
Starter	10 000	$9 / mo	Metered billing (Stage 2)
Pro	100 000	$29 / mo	SLA support
Enterprise	Custom	—	Dedicated infra

(MVP uses Free Tier only — Stripe billing to be added later.)

🧭 Roadmap
Stage	Focus	Outcome
1	Core setup + 4 modules	MVP live
2	Billing + usage tracking	Paid tiers
3	New utilities + scaling	Full API hub

🤝 Contributing
Pull requests are welcome.
Please follow PSR-12 coding style and provide tests where applicable.

🧾 License
MIT © 2025 Multility API

Multility API — Modular. Fast. Developer-First.

One API. Infinite Utilities.
