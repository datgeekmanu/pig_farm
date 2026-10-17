# Pig Farm

Pig-farm management app (AFAP Porc clone) + client storefront. Monorepo.

## Layout

```
apps/
  api/         Laravel 13 API (Sanctum auth, Postgres, queues) — #0.2+
  dashboard/   Next.js 16 farmer dashboard (:3000)
  storefront/  Next.js 16 buyer PWA (:3001)
packages/
  types/       @pig-farm/types — shared API contracts (OpenAPI-generated in #0.5)
recon/         AFAP app recon: screenshots + form field dumps (reference only)
ISSUES.md      Full issue backlog (12 epics)
```

## Prereqs
node ≥20, pnpm 11, php 8.5, composer 2.8.

## Dev
```bash
docker compose up -d         # postgres :5432, redis :6380

pnpm install                 # install JS deps + link workspace
pnpm dev                     # dashboard :3000 + storefront :3001 (turbo)

# API (separate)
cd apps/api
cp .env.example .env && php artisan key:generate   # first time
php artisan migrate
php artisan serve            # :8000  (docs at /docs/api — Scramble OpenAPI)
php artisan horizon         # queue worker (separate terminal)
```

Services: Postgres + Redis via `docker-compose.yml` (redis on host **6380** to avoid
a collision with another local redis). API runs on host against the exposed ports.

## Build / lint / test
```bash
pnpm build    # turbo: all JS apps
pnpm lint
pnpm test
```

## Status
#0.1 scaffold + #0.2 (Sanctum, Postgres, Redis, Horizon, Scramble OpenAPI, docker-compose) done.
Next: #0.3 farm-scoped schema (`account → farm`, every row `farm_id`).
See ISSUES.md for backlog.
