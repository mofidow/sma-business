# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Local development
```bash
npm run dev          # Vite dev server with HMR
npm run build        # Production build (clears public/assets first)
npm run lint         # ESLint fix on resources/
npm run format       # Prettier format on resources/
```

### PHP (VPS uses php8.4, locally use your installed version)
```bash
php artisan migrate --force
php artisan db:seed --class=DemoProductSeeder --force
php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:clear
php artisan tinker
```

### Testing
```bash
./vendor/bin/pest                        # All tests
./vendor/bin/pest tests/Feature/Foo.php  # Single file
./vendor/bin/pest --filter "test name"   # Single test
php artisan pint                         # PHP code style fixer
```

### VPS deployment
```bash
bash deploy/deploy.sh   # Full deploy: git pull → composer → migrate → build → cache → restart
```

## Architecture

### Dual-frontend split
The app has two completely separate frontends sharing one Laravel backend:

**Admin panel** (`/admin/*`) — Laravel + Inertia.js + Vue 3 + Tailwind CSS v4
- Pages: `resources/js/Pages/Sma/**/*.vue`
- Layout: `resources/js/Layouts/AppLayout.vue`
- Global shared props injected by `HandleInertiaRequests` (auth user, settings, stores, permissions, Ziggy routes)
- Vue global mixin at `resources/js/Core/mixin.js` adds `$currency`, `$date`, `$can`, etc. to all components
- i18n: `resources/js/Core/i18n.js` — fetches `/lang/{locale}` lazily, falls back to English for missing keys

**Shop frontend** (`/`) — Laravel + Livewire v4 + Alpine.js + Blade templates
- Module directory: `modules/Shop/`
- Livewire components: `modules/Shop/Http/Livewire/`
- Blade views/components: `modules/Shop/resources/`
- Shop-specific CSS: `modules/Shop/resources/assets/app.css` (compiled separately by Vite)
- The `ShopServiceProvider` auto-discovers and registers all Livewire components by scanning the directory

### Module system
`packages/installer/src/Http/Helpers/Module.php` controls which modules are active. `get_module($name)` returns `true` for all modules unconditionally (license check removed). Both `pos` and `shop` are always enabled.

The Shop module boots only if `get_module('shop')` is truthy — its routes, migrations, Livewire components, and Blade namespaces are all registered in `ShopServiceProvider::boot()`.

### Settings system
All app configuration lives in the `settings` table as key/value rows. Keys listed in `json_settings_fields()` are JSON-decoded automatically. The `get_settings($keys)` helper is the single access point — never query the `settings` table directly. Settings are shared to the Vue frontend via `HandleInertiaRequests::share()`.

The `demo()` function reads `env('DEMO', false)`. When true it used to lock shop settings — **this guard has been removed** from `Settings.php` but the env var still needs to be `false` or absent in production.

### Route groups
Admin routes are split into files under `routes/groups/`:
- `sma.php` — main CRUD (products, sales, purchases, people, etc.)
- `pos.php` — POS register and sale endpoints
- `reports.php`, `accounting.php`, `hr.php`, `settings.php` — feature groups
- The `extendedResources()` macro adds `restore` and `destroyPermanently` routes beyond standard CRUD

Shop routes live in `modules/Shop/routes/web.php` with prefix `''` (root domain).

### Stock / inventory ledger
Stock balances are **not stored as a column**. The `stocks` table holds one row per (product, store) and the `Trackable` trait computes `balance` by summing `tracks.value` via a morph relationship. Every stock change (purchase receipt, sale, adjustment) inserts a signed `tracks` row. Call `$stock->setBalance($qty, [...])` or `$stock->increaseBalance()` / `$stock->decreaseBalance()` — never write raw balance math.

### Pivot table naming
Laravel auto-generates pivot table names alphabetically from the two model class basenames. The base schema used non-standard names; corrective migrations exist:
- `product_store` (renamed from `product_stores`) — Product ↔ Store
- `product_promotion`, `category_promotion` — Promotion relationships
- `sale_item_variation`, `purchase_item_variation`, etc. — order item ↔ Variation
- See `database/migrations/2026_05_11_000004_*` and `2026_05_12_000005_*` for the full list

When adding a new `belongsToMany` without specifying a table, verify the expected name (`Model1` + `Model2` alphabetically, singular snake_case) and ensure a migration creates that exact table.

### Payment gateways (plugins)
Payment methods live under `plugins/Payments/Gateways/`. Each implements `PaymentInterface` and is registered in `PaymentMethods::$registry`. The settings UI renders `settingFields()` dynamically — no UI changes needed when adding a gateway. WAAFI Pay is included for Somali mobile-money payments.

### CSS — Tailwind v4 constraints
- `@apply` **cannot** use structural HTML utilities (`group`, `peer`). Use plain CSS descendant selectors instead.
- Brand colors are defined as CSS custom properties in the `@theme {}` block in `modules/Shop/resources/assets/app.css`.
- The admin panel uses a separate Tailwind build entry at `resources/css/app.css`.

### Language / i18n
- Translation files: `lang/{locale}.json`
- Available locales declared in `lang/languages.json`
- To add a new language: add the JSON file + append an entry to `languages.json`; the frontend loads it automatically via `loadLocaleMessages()`

### Key seeder
`database/seeders/DemoProductSeeder.php` — seeds 36 products across 8 categories, 10 brands, 6 units with 50 units stock each. Re-runnable (slug-based uniqueness checks). Run with `--force` in production.
