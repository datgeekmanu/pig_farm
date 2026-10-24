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
#0.1 scaffold + #0.2 (Sanctum, Postgres, Redis, Horizon, Scramble, docker-compose) + #0.3 (farm-scoped schema) done.
Next: #0.4 Next.js dashboard base (auth guard, i18n, shadcn).
See ISSUES.md for backlog.

### Auth (Sanctum SPA — cookie/session, not tokens)
Frontend must send credentials and bootstrap CSRF:
1. `GET /sanctum/csrf-cookie` (sets `XSRF-TOKEN`) — `withCredentials: true` / `fetch(..., {credentials:'include'})`.
2. `POST /api/login` `{email,password}` with `X-XSRF-TOKEN` header → session cookie.
3. Authenticated calls (`GET /api/me`, `/api/farms`, …) ride the session cookie.
4. `POST /api/logout` ends the session.

Requests count as "stateful" (session applied) when their `Origin`/`Referer` is in `SANCTUM_STATEFUL_DOMAINS`. Allowed frontend origins: `FRONTEND_URLS`. CSRF enforced (missing token → 419).

### Multi-tenancy (farm scoping)
`account → farm (1..n)`, `user ↔ farm` pivot (`role`). Tenant-owned models use the
`BelongsToFarm` trait: a **fail-closed** `FarmScope` filters every query by the active
farm and throws if none is set. `CurrentFarm` (request-scoped singleton) holds the
active farm; `ResolveFarm` middleware (alias `farm`) sets it from `X-Farm-Id` after auth.
Escape hatch for global queries: `Model::withoutGlobalScope(FarmScope::class)`.
Seeded dev login: `owner@example.com` / `password`.
