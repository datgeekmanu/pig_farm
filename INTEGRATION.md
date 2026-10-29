# Frontend ↔ API Integration

How `apps/dashboard` and `apps/storefront` (farmer field PWA) wire to the Laravel API
(`apps/api`). Every mocked `queryFn` / `TODO(#epic)` in the frontend now has a live route.

## Going live
1. `NEXT_PUBLIC_API_URL` → the API origin **with `/api`** (e.g. `http://localhost:8000/api`).
   The client derives the Sanctum origin by stripping `/api`.
2. Set `NEXT_PUBLIC_API_MOCKS=false`.
3. Replace each mock `queryFn` with the endpoint from the table below.

## Auth (Sanctum SPA — cookies, no tokens)
Already implemented in both `lib/api-client.ts` — no change needed:
1. `GET {origin}/sanctum/csrf-cookie` once (axios does it automatically on first write).
2. `POST /api/login` `{email,password}` → session cookie.
3. Every request sends `withCredentials` + `X-XSRF-TOKEN` + `X-Farm-Id` (active farm).
4. `POST /api/logout`.
- Errors: `{ message, errors?: { field: [msg] } }`. 419 → refresh CSRF + retry (client already does).
- Money is **minor units** (XOF = whole francs, EUR = cents).

## Endpoint map

| Frontend need (TODO) | Method + path | Returns / notes |
|---|---|---|
| auth me | `GET /api/user` (alias of `/me`) | user + `farms[]` + `active_farm_id` |
| login / logout | `POST /api/login`, `POST /api/logout` | session cookie |
| password reset | `POST /api/forgot-password`, `/reset-password` | |
| google | `POST /api/auth/google` `{access_token}` | existing users only |
| farms (switcher) | `GET /api/farms` | user's farms + role |
| farm summary (home) | `GET /api/farm/summary` | `{herd:{total,weeklyChange,adgGramsPerDay,mortalityPct,feedConversion}, user:{firstName,initials,unreadNotifications}}` |
| lots list / detail | `GET /api/lots`, `GET /api/lots/{id}` | lot + computed metrics (below) |
| lot events timeline | `GET /api/lots/{id}/events` | `[{icon,text,date}]` newest-first |
| record lot weigh-in | `POST /api/lots/{id}/weighings` | body: `weights[]` **or** `count`+`total_kg` **or** `avg_weight_kg`; `weighed_on?` |
| lot weigh-in history | `GET /api/lots/{id}/weighings` | |
| feed intake (FCR) | `POST /api/lots/{id}/feed` | `{date, quantity_kg, stock_item_id?, cost?}`; decrements stock if linked |
| create lot | `POST /api/lots` (alias of `/batches`) | `{nom, date_mise_en_lot, effectif_initial, batiment_loge?, poids_moyen_initial_kg?}` |
| sows list / detail | `GET /api/sows`, `GET /api/sows/{id}` | projected status `gestante\|vide\|allaitante` + serviceDate/boar/parity/cycle |
| service (saillie) | `POST /api/breedings` | `{sow_id, bred_on, boar_id?}` → `expected_farrow_on` auto (+114d) |
| ultrasound | `PATCH /api/breedings/{id}` | `{ultrasound_on, ultrasound_result: gestante\|vide}` |
| farrowing (mise bas) | `POST /api/farrowings` | `{breeding_id?, sow_id?, farrowed_on, born_alive, born_dead?}` |
| weaning (sevrage) | `POST /api/weanings` | `{farrowing_id, weaned_on, weaned_count}` |
| GTTT | `GET /api/reproduction/gtt` | herd repro KPIs |
| stock list | `GET /api/stock-items` | `?type=alimentation\|veterinaire&search=&low=1` |
| stock movement | `POST /api/stock-items/{id}/movements` | `{change, reason?}` (±) |
| treatments | `GET/POST /api/treatments` | `{scope: lot\|individual\|prophylaxie, batch_id\|pig_id, date, produit, ...}` |
| upcoming treatments | `GET /api/treatments/upcoming` | `[{inDays,product,target,type,dose,caregiver,urgency,tag}]` |
| mortality | `POST /api/mortalities` | `{batch_id\|pig_id, died_on, cause?, count?}` |
| feed formulation (manual) | `POST /api/formulas` | lines sum to 100% → nutrition + cost/kg |
| feed least-cost (LP) | `POST /api/formulas/optimize` | `{phase, ingredient_ids?}` → mix; 422 if infeasible |
| ingredients | `GET /api/ingredients` | nutrient profiles + price/kg |
| finances overview | `GET /api/finances/overview?months=n` | `chiffre_affaires`, `depenses_totales`, `benefice_brut`, `par_mois[]`, `par_categorie[]`, `seuil_rentabilite`, `sujets_vendus` |
| sales | `GET/POST /api/sales` | items + total; links sold pigs/lots |
| expenses | `GET/POST /api/expenses` | `{date, montant, categorie, ...}` |
| salaries | `GET/POST /api/salaries` | `{nom_ouvrier, montant, date_paiement, mois_concerne}` |
| notifications | `GET /api/notifications` | `?unread=1`; `+unread_count`; `PATCH /{id}/read`, `POST /read-all` |
| today-tasks done | `GET/POST /api/tasks/done` | persist "À faire aujourd'hui" completion (`{key}`) |
| listings (sell) | `GET/POST/PATCH/DELETE /api/listings` | lot-linked; `available` = lot current head, `averageKg` from weigh-ins |
| offline sync | `POST /api/sync` | `{weights:[{client_uuid,pig_id,...}], mortalities:[...]}` idempotent |
| settings / team | `GET/PUT /api/farm`, `/api/farm/members` | owner-only writes |
| PDF receipts/reports | `GET /api/{sales/{id}/receipt, batches/{id}/summary, formulas/{id}/sheet, finances/report, reproduction/gtt-report}` | `application/pdf` |

## Computed lot metrics (on every `Lot`)
`headcount` (initial − deaths − sales), `avg_weight_kg`, `uniformity_cv`,
`adg_g_per_day` (GMQ), `feed_conversion` (IC, from feed intakes), `phase`
(Post-sevrage/Démarrage/Croissance/Finition by weight), `age_days`, `pen`.

## Frontend-side adjustments (the "meet in the middle" half)
- **Finance field names**: map `ca→revenue`, `depenses→expenses`, `mois→month`; compute
  expense **share %** from the amounts in `par_categorie`.
- **Sow status**: dashboard uses `gestating/empty/lactating` — map from API French
  `gestante/vide/allaitante` (storefront already French).
- **Role**: API returns `owner | worker` (internal `staff` is normalised to `worker`).

## Parked
Buyer-facing marketplace API (`buyers`, `orders`, `payments`, `/api/shop/*` buyer routes)
is built but **dormant** — the frontend has no buyer app, only seller **listings**
(above). Re-activate when a buyer app is built.

## Not yet wired (v2 / needs real data)
- Nutrient profiles, IFIP growth reference, phase requirements, comfort norms are
  **approximate placeholders** — replace with real values before production decisions.
- Payment gateway (mobile-money/card) is a stub (reference + status only).
- Reproduction French field labels are provisional (built from the standard cycle).
