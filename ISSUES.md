# Pig Farm — API Backlog (Laravel)

Clone of AFAP Porc (afaporc.com) + client storefront. **This repo's scope = backend API only.**
Frontend (`apps/dashboard`, `apps/storefront`) is built by others and consumes this API.
Tracer-bullet vertical slices — each issue shippable + testable. `[est]`: S<1d, M 1-3d, L 3-5d, XL >5d.

Every endpoint: farm-scoped (`BelongsToFarm` + `farm` middleware), FormRequest validation,
API Resource response, Scramble-documented, feature-tested. Field specs (from live AFAP app,
`recon/forms/`) define request payloads. `*` = required.

Scope decisions:
- Single-customer, **multi-farm**. Tenant-ready schema (every row `farm_id`); no public signup/billing/multi-tenant in v1 → v2.
- Formulation v1 = Manuel + **LP (Économique)** + Analyse. v2 = Professionnel (INRA/NRC) + Victor AI.
- Storefront = new scope, not in AFAP. API only here (PWA shell lives in frontend repo).
- Money stored minor-unit integers; currency per-farm (XOF/EUR). PDF generated API-side.

---

## EPIC 0 — Foundation  ✅ done
- **#0.1** Monorepo scaffold ✅
- **#0.2** Laravel API: Sanctum, Postgres, Redis, Horizon, scheduler, Scramble/OpenAPI ✅
- **#0.3** `account → farm`, `user ↔ farm` pivot, fail-closed `FarmScope` ✅
- **#0.8** CI (lint/test) + staging deploy (API). `[M]` — _todo_

## EPIC 1 — Auth & farm context
- **#1.1** Auth endpoints: `POST /login`, `POST /logout`, forgot/reset password, email verify (Sanctum tokens). `[M]`
- **#1.2** Google OAuth token exchange (Socialite) — optional. `[S]`
- **#1.3** Roles/policies: owner, staff (access levels); Gate/Policy base + `farm` middleware on protected group. `[M]`
- **#1.4** Farm context: `GET /farms` (user's farms), `X-Farm-Id` resolution (ResolveFarm) + `GET /me` returns active farm. `[S]`
- **#1.5** Account: `PUT /account/password`, `POST /account/reset-farm` (wipe farm data), `DELETE /account` (typed confirm). `[M]`
- *(v2: public signup, 14-day trial, subscription/payment, multi-tenant onboarding)*

## EPIC 2 — Mes Porcs (herd)
Models: `batch`(lot), `pig`, `weight_record`, `mortality_record`, `building`.
- **#2.1** Lots CRUD `/batches`. `[L]`
  - Payload: nom*, batiment_loge, date_mise_en_lot*, effectif_initial*, date_naissance?, poids_moyen_initial_kg?, prix_achat_unitaire?, notes?, peres[], meres[].
- **#2.2** Pigs CRUD `/pigs`. `[L]`
  - Payload: pere?, mere?, identifiant*, nom?, sexe(enum), date_naissance?, date_arrivee?, date_inconnue(bool), poids_arrivee_kg*, prix_achat?, categorie(enum: verrat/truie/engraissement…), valeur_reforme?, provenance?, notes?.
- **#2.3** Weigh-ins `/pigs/{}/weights` + `/batches/{}/weights`; compute **poids moyen + homogénéité (CV%)**. `[M]`
- **#2.4** **IFIP growth curve** endpoint: ref curve (sevrage/démarrage/croissance/finition by week) + actual weigh-in series. `[M]`
- **#2.5** Mortalité `/mortalities`: cause, date; rate per lot + totals; date-range filter. `[M]`
- **#2.6** Densité & Homogénéité: buildings + occupancy/comfort calc endpoint. `[M]`

## EPIC 3 — Reproduction
Models: `sow`, `boar`, `breeding`(saillie), `ultrasound`, `farrowing`(mise bas), `litter`, `piglet`, `weaning`, `band`.
⚠ Most sub-form payloads NOT captured (need a sow in live app). Capture before building each.
- **#3.1** Breeders registry `/breeders`: verrats/truies, carrière + généalogie. `[L]`
- **#3.2** Sow individual + collectif (bandes). `[M]`
- **#3.3** **Gestation** `/breedings`: saillie → **114-day calc (configurable)** → écho → mise bas. `[L]`
- **#3.4** Maternité: portées, allaitement, sevrage; born alive/stillborn, survival. `[L]`
- **#3.5** Truies Vides: à saillir + retours en chaleur. `[M]`
- **#3.6** GTTT KPIs: farrowing rate, piglets/sow/yr, pre-wean mortality. `[L]`
- **#3.7** Planification bandes: scheduling + production targets. `[M]`

## EPIC 4 — Formulation
Models: `ingredient`, `nutrient`, `ingredient_nutrient`, `formula`, `formula_line`, `phase_requirement`, `calc_history`.
- **#4.1** Ingredient library `/ingredients` + nutrient profiles + price/kg; seed defaults, editable. `[L]`
- **#4.2** `phase_requirement` + seed (phase nutrient min/max). `[M]`
- **#4.3** **Manuel** `POST /formulas`: name, phase, lines(ingredient+%), compute nutrition + cost/kg, validate sum=100. `[L]`
- **#4.4** **Économique / LP** `POST /formulas/optimize`: poids total, phase, ingredient pool → least-cost solve. `[XL]`
  - Lib: Python/PuLP microservice (recommended) or glpk.js. **Infeasible → 422 clear message, no crash.**
  - Self-check: known ingredients → expected least-cost output (assert).
- **#4.5** Analyse `POST /formulas/analyze`: lines + phase → deficiency score vs requirements. `[M]`
- **#4.6** Formulas CRUD (save/version/duplicate) + calc history. `[M]`
- *(v2: Professionnel INRA/NRC presets [licensing], Victor AI)*

## EPIC 5 — Stocks & Traitements
Models: `stock_item`, `stock_movement`, `treatment`, `prophylaxis`.
- **#5.1** Stocks `/stock-items` (alimentation + vétérinaire types), search, low-stock flag, movement history. `[L]`
  - Payload: nom*, categorie, quantite, unite, date, seuil_alerte?, prix_unitaire?, notes?.
- **#5.2** Treatments — lot `/treatments` (type=lot): lot*, date*, cout_total, produit*, dose, soignant, tel, notes. `[M]`
- **#5.3** Treatments — individual (type=individual): pig*, date*, type*, produit*, dose, soignant, tel, motif, cout, date_rappel, notes. `[M]`
- **#5.4** Prophylaxie: schedule + reminders. `[M]`
- **#5.5** Treatment/feed consumption → stock decrement + movement log (transaction). `[M]`

## EPIC 6 — Finances & Salaires
Models: `sale`, `sale_item`, `expense`, `expense_category`, `salary`, `amortization`, `budget`.
- **#6.1** Ventes `/sales`: CRUD, filters, links sold pigs/lots → "sujets vendus". `[L]`
  - Payload: date_vente, items[{type_porc, quantite, poids_total_kg, prix_kg, age_mois?, remise_pct?, taxe_pct?}], acheteur, tel, email, mode_paiement(enum), statut_paiement(enum), notes.
- **#6.2** Dépenses `/expenses`: CRUD + category breakdown. `[M]`
  - Payload: date, montant, categorie(enum), description, lot_id?, notes.
- **#6.3** Salaires `/salaries`: payroll per worker/month. `[M]`
- **#6.4** Amortissements + budget prévisionnel. `[M]`
- **#6.5** Finance overview endpoint: CA / dépenses / bénéfice brut, monthly series, category breakdown, period filter. `[L]`
- **#6.6** `seuil de rentabilité` (break-even) from farm settings. `[S]`

## EPIC 7 — Settings, Referral, Tutoriels
- **#7.1** Farm settings `/farm`: logo upload, name/phone/email/address, payment_info, devise, seuil rentabilité, receipt color/branding fields. `[L]`
- **#7.2** Team `/farm/members`: invite (name, email, password, access level), list, remove. `[M]`
- **#7.3** Notification preferences endpoint. `[S]`
- **#7.4** Referral: code gen, estimated gains, filleuls list, payout request. `[M]`
- **#7.5** Tutoriels: curated video list (static/seeded). `[S]`

## EPIC 8 — Notifications engine
- **#8.1** Scheduled jobs (Horizon): vaccines, expected farrowings, heat returns, weaning, low stock. `[L]`
- **#8.2** Notification model + `GET /notifications` + read state; email channel (+SMS optional). `[M]`

## EPIC 9 — PDF & receipts
- **#9.1** PDF engine (dompdf/snappy) w/ farm branding (logo, color, header). `[M]`
- **#9.2** PDF endpoints: sale receipt/invoice, batch summary, repro report, formula sheet, finance report. `[L]`

## EPIC 10 — Storefront API
Models: `product`, `listing`, `cart`, `order`, `order_item`, `buyer`, `payment`.
- **#10.1** Catalog: live pigs (weight/age/breed) + pork cuts; linked to herd batches. `[L]`
- **#10.2** Public browse/search + product detail endpoints. `[M]`
- **#10.3** Cart + checkout + payment (mobile-money/card gateway). `[L]`
- **#10.4** Order lifecycle (placed→confirmed→ready→delivered) + seller notifications. `[M]`
- **#10.5** Buyer accounts + order history. `[M]`
- **#10.6** Seller endpoints: listings, stock, incoming orders. **Sale must atomically update herd count + inventory (one txn).** `[L]`

## EPIC 11 — Cross-cutting & launch
- **#11.1** Offline sync endpoints for field entry (weights/mortality): batch upsert + conflict rule. **Scope before building.** `[L]`
- **#11.2** API-boundary validation hardening + audit log. `[M]`
- **#11.3** API integration tests over critical flows: batch→weigh→growth; saillie→mise bas; LP solve; vente→finance; order→pay. `[L]`
- **#11.4** Sentry, backups, prod deploy, SSL/DNS. `[M]`

---

## Build order
0 → 1 → 2 → (3 ∥ 4 ∥ 5) → 6 → 7 → 8 → 9 → 10 → 11.
Herd (2) blocks repro(3)/treatments(5)/sales(6)/storefront(10). Finance(6) needs sales+expenses+salaries. Storefront(10) needs herd+finance.

## Open spec gaps (capture from live app before building)
- Repro sub-form payloads (#3.3–3.7): need a sow in app.
- Enum values: sexe, categorie porc (#2.2), type_porc / mode_paiement / statut_paiement (#6.1), access level (#7.2), phase physiologique.
- Mortalité payload fields (#2.5).
