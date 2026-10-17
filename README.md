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
pnpm install                 # install JS deps + link workspace
pnpm dev                     # dashboard :3000 + storefront :3001 (turbo)

# API (separate)
cd apps/api && php artisan serve   # :8000
```

## Build / lint / test
```bash
pnpm build    # turbo: all JS apps
pnpm lint
pnpm test
```

## Status
Scaffold (#0.1) done. Next: #0.2 Laravel Sanctum+Postgres+Horizon, #0.3 farm-scoped schema.
See ISSUES.md for backlog.
