# AGENTS.md — VMS Elements Fastspring Woo Payment

Project-level guidance for AI coding agents working in this repository.
Read this end-to-end before making any non-trivial change.

> **Maintenance rule:** After any non-trivial code, naming, schema, hook, or workflow change, **update this file** (`AGENTS.md`) in the same PR/commit so it stays accurate. Do not leave AGENTS.md stale.

---

## 1. What this plugin is

**VMS Elements Fastspring Woo Payment for WooCommerce** is the **free WordPress.org plugin**. It provides the FastSpring core integration; advanced features ship in the separate **Pro add-on** (see §18).

The free plugin:

1. Adds a **WooCommerce payment gateway** that creates a [FastSpring](https://fastspring.com) checkout session and **redirects** the customer to the FastSpring storefront.
2. Listens for **FastSpring webhooks** (HMAC-SHA256 signed) and persists orders, subscriptions, refunds and events into custom DB tables.
3. Renders a **basic status dashboard** and **stored Orders** screen inside `wp-admin`.
4. Provides **Settings** (credentials, webhook URL, gateway options) with Live + Sandbox isolation.

The **Pro add-on** (`vms-elements-fastspring-woo-payment-pro`, sold on vmselements.com) adds analytics dashboard, overlay + Blocks checkout, catalog/subscription admin, shortcodes, product sync, refunds UI, and more — without bundling that code in the free ZIP.

On first load, `VMS_EFWP_Migrate::maybe_run()` performs a **one-time migration** from the legacy `wp-fastspring` plugin (options, tables, meta, WC payment method id). Do not add new backward-compat shims unless explicitly requested.

| Item | Value |
|------|--------|
| **Plugin name** | VMS Elements Fastspring Woo Payment for WooCommerce |
| **Directory slug** | `vms-elements-fastspring-woo-payment` (must match main file basename) |
| **Main file** | `vms-elements-fastspring-woo-payment.php` |
| **Text domain** | `vms-elements-fastspring-woo-payment` |
| **Helper function** | `vms_efwp()` |
| **Main class** | `VMS_EFWP` |
| **Constants prefix** | `VMS_EFWP_*` |
| **Min PHP / WP / WC** | 7.4 / 6.0 / 7.0 |
| **License** | GPL-2.0-or-later |
| **Version constant** | `VMS_EFWP_VERSION` |

### Naming prefix cheat sheet

| Layer | Prefix / id | Example |
|-------|-------------|---------|
| PHP classes | `VMS_EFWP_*` | `VMS_EFWP_Admin` |
| Functions / hooks | `vms_efwp_*` | `vms_efwp_event` |
| Options | `vms_efwp_*` | `vms_efwp_settings` |
| DB tables | `{prefix}vms_efwp_*` | `wp_vms_efwp_orders` |
| WC gateway id | `vms_efwp` | `woocommerce_vms_efwp_settings` |
| Admin menu / pages | `vms-efwp`, `vms-efwp-*` | `vms-efwp-settings` |
| Script/style handles | `vms-efwp-*` | `vms-efwp-admin` |
| CSS classes / vars | `vefwp-*`, `--vefwp-*` | `.vefwp-wrap` |
| JS global (localized) | `VMSEFWP` | `VMSEFWP.ajax_url` |
| Product meta | `_vms_efwp_product_path` | |
| Order meta | `_vms_efwp_session_id` | |
| Webhook query arg | `vms-efwp-webhook` | `?vms-efwp-webhook=1` |
| Class filenames | `class-vms-efwp-*.php` | `class-vms-efwp-api.php` |

**Never** use `wp-`, `wp_`, or `WP_` in plugin slugs, prefixes, or branding — WordPress.org rejects that pattern.

---

## 2. File map

### Free plugin (`vms-elements-fastspring-woo-payment/`)

```
vms-elements-fastspring-woo-payment.php   # Bootstrap, constants, vms_efwp(), features helper
uninstall.php                             # Drops custom tables + options unless opted out
readme.txt                                # WordPress.org readme
README.md                                 # GitHub readme
distignore.example                        # Excludes docs/, dev files, and ../pro plugin from org ZIP
LICENSE                                   # GPL-2.0-or-later

includes/
  class-vms-efwp.php                      # Singleton loader (free core only)
  class-vms-efwp-features.php             # Pro gate helpers (vms_efwp_is_pro, upgrade UI)
  class-vms-efwp-install.php                # Creates 4 custom tables + default options
  class-vms-efwp-migrate.php              # One-time migration from legacy wp-fastspring plugin
  class-vms-efwp-settings.php             # Wrapper over vms_efwp_settings (mode-aware)
  class-vms-efwp-api.php                  # FastSpring REST client (api.fastspring.com)
  class-vms-efwp-logger.php               # DB-backed logger (vms_efwp_log)
  class-vms-efwp-assets.php               # Shared checkout i18n + SBL tag helper (Pro registers assets)
  class-vms-efwp-webhook-permissions.php  # Syncs subscribed webhook events from GET /webhooks
  class-vms-efwp-webhook.php              # Webhook listener + HMAC verify + event dispatch
  class-vms-efwp-data-store.php           # Persistence helpers (orders, subs, events)
  class-vms-efwp-wc-gateway-loader.php    # Registers WC gateway (redirect checkout only)
  class-vms-efwp-wc-gateway.php           # WC_Payment_Gateway (id: vms_efwp)
  admin/
    class-vms-efwp-admin.php              # Free menu, assets, test-connection AJAX
    class-vms-efwp-admin-resource-base.php
    class-vms-efwp-admin-dashboard.php    # Free status view; delegates to Pro dashboard when licensed
    class-vms-efwp-admin-orders.php       # Stored orders list
    class-vms-efwp-admin-settings.php

assets/
  css/admin.css                           # Admin UI (vefwp-* classes)
  js/admin.js                             # Admin JS (Chart.js + dashboard AJAX when Pro is active)

docs/                                     # Dev docs + local FastSpring OpenAPI copies (excluded from org ZIP)
  AGENTS.md                               # This file
  Products.md, Orders.md, Subscriptions.md, …

languages/
  vms-elements-fastspring-woo-payment.pot
```

### Pro add-on (`../vms-elements-fastspring-woo-payment-pro/` — separate plugin, not in free ZIP)

```
vms-elements-fastspring-woo-payment-pro.php
includes/
  class-vms-efwp-pro.php                  # Pro bootstrap + dependency check
  class-vms-efwp-pro-license.php          # EDD Software Licensing (vmselements.com)
  class-vms-efwp-pro-modules.php          # Loads Pro classes when license valid
  class-vms-efwp-pro-wc-gateway-loader.php # Overlay checkout + WC Blocks
  class-vms-efwp-stats.php                # Dashboard aggregations
  class-vms-efwp-product-sync.php         # One-way WC → FS product sync
  class-vms-efwp-checkout-overlay.php     # REST + transients for popup checkout
  class-vms-efwp-checkout-links.php       # Payment link pages / endpoints
  class-vms-efwp-payment-success.php      # Payment success page
  class-vms-efwp-shortcodes.php           # Checkout shortcodes
  class-vms-efwp-wc-account-subscriptions.php
  class-vms-efwp-wc-blocks.php            # Cart/Checkout Block payment method type
  admin/
    class-vms-efwp-pro-admin.php          # License screen
    class-vms-efwp-pro-admin-features.php # Pro admin menus
    class-vms-efwp-pro-admin-dashboard.php
    class-vms-efwp-pro-admin-ajax.php
    class-vms-efwp-admin-subscriptions.php
    class-vms-efwp-admin-products.php
    … (coupons, invoices, quotes, returns, sessions, accounts, events, reports, webhooks, tools, shortcodes)

assets/
  css/checkout-popup.css, payment-success.css, shortcode-checkout.css, account-subscriptions.css
  js/checkout-popup.js, overlay-shell.js, shortcode-checkout.js
  js/blocks/checkout-block.js
```

> The `class-vms-efwp-*` filename convention is **mandatory**. Free `VMS_EFWP::includes()` only requires free-core paths; Pro modules are loaded from `VMS_EFWP_Pro_Modules` in the sibling Pro plugin.

---

## 3. Runtime architecture

### 3.1 Bootstrap order

```
vms-elements-fastspring-woo-payment.php
  └── requires class-vms-efwp-features.php + class-vms-efwp.php
  └── vms_efwp() → VMS_EFWP::instance()
        └── includes() requires free-core classes only
        └── init_hooks():
              - plugins_loaded(10)  → on_plugins_loaded()
              - init(5)             → on_init()
              - admin_notices       → maybe_render_woocommerce_notice()
              - plugin_action_links → add_action_links()

on_plugins_loaded() [free]:
  - VMS_EFWP_Migrate::maybe_run()
  - new VMS_EFWP_Settings   (cached in vms_efwp()->settings)
  - new VMS_EFWP_API        (cached in vms_efwp()->api)
  - new VMS_EFWP_Webhook    (internally uses VMS_EFWP_Webhook_Permissions)
  - if WooCommerce active   → new VMS_EFWP_WC_Gateway_Loader (redirect only)
  - if is_admin()           → new VMS_EFWP_Admin
  - do_action( 'vms_efwp_loaded' )

vms_efwp_loaded [Pro add-on, priority 20]:
  - VMS_EFWP_Pro_Modules::boot() when license valid
  - loads stats, product sync, overlay, admin screens, Pro gateway loader
  - do_action( 'vms_efwp_init' ) from free on_init; Pro hooks pages/modules there
```

### 3.2 Singleton access

```php
$plugin   = vms_efwp();       // VMS_EFWP instance
$settings = $plugin->settings; // VMS_EFWP_Settings
$api      = $plugin->api;      // VMS_EFWP_API
```

`$settings` and `$api` are only available **after** `plugins_loaded` (priority 10). Guard early-running code with `function_exists( 'vms_efwp' )` and null checks.

---

## 4. Data layer

### 4.1 Custom tables (created on activation)

| Table | Purpose |
|-------|---------|
| `{prefix}vms_efwp_orders` | One row per FastSpring order (unique `fs_order_id`) |
| `{prefix}vms_efwp_subscriptions` | One row per FastSpring subscription (unique `fs_subscription_id`) |
| `{prefix}vms_efwp_events` | Raw webhook events, processed flag, error_message |
| `{prefix}vms_efwp_log` | Internal log lines (level, channel, message, context) |

Schemas live in `includes/class-vms-efwp-install.php`. **If you change a schema, bump `VMS_EFWP_VERSION` and run `dbDelta` from `VMS_EFWP_Install::install()` — do not write ad-hoc `ALTER TABLE` statements.**

### 4.2 Options

| Option key | Purpose |
|------------|---------|
| `vms_efwp_settings` | All plugin settings (see `VMS_EFWP_Settings::defaults()`) — includes `webhook_enabled_events_live/sandbox` + `_synced_at` |
| `vms_efwp_db_version` | Tracks installed schema version |
| `vms_efwp_keep_data_on_uninstall` | If truthy, `uninstall.php` keeps tables/options |
| `woocommerce_vms_efwp_settings` | WC per-gateway option (enabled/title/description) |
| `vms_efwp_migrated_from_wp_fastspring` | Set after one-time legacy migration completes |

### 4.3 Order / subscription persistence

- Source of truth = **webhook events**, not the redirect return.
- `VMS_EFWP_Data_Store::upsert_order()` is idempotent on `fs_order_id`.
- `VMS_EFWP_Data_Store::upsert_subscription()` is idempotent on `fs_subscription_id`.
- Test-mode events set `is_test = 1`; dashboard queries default to `is_test = 0`.

---

## 5. Modes, credentials, secrets

`VMS_EFWP_Settings` is **mode-aware** (`live` or `sandbox`):

```php
$settings->api_username();   // sandbox_username  OR  live_username
$settings->api_password();   // sandbox_password  OR  live_password
$settings->storefront();     // sandbox_storefront OR live_storefront
$settings->webhook_secret(); // webhook_secret_sandbox OR webhook_secret_live
$settings->has_credentials();
$settings->is_sandbox();
$settings->webhook_url();    // home_url() + ?vms-efwp-webhook=1
```

**Never** read raw credentials from `get_option( 'vms_efwp_settings' )`. Always use `VMS_EFWP_Settings` accessors.

---

## 6. FastSpring REST API client

`VMS_EFWP_API` wraps `https://api.fastspring.com` with HTTP Basic Auth.

- Public methods return `array` or `WP_Error` — always `is_wp_error()`.
- Non-2xx responses log to `vms_efwp_log` under channel `api`.
- Sections grouped by resource: Accounts, Coupons, Products, Orders, Subscriptions, Invoices, Quotes, Returns, Sessions (V1 + V2), Events, Data/Reports, Webhooks.
- `User-Agent`: `VMS-Elements-Fastspring-Woo-Payment/{version}; {home_url}`.
- FastSpring often returns **HTTP 200 with `"result":"error"`** in the JSON body. `detect_result_error()` converts these to `WP_Error`. Per-item errors in arrays like `products[]` are also detected.

```php
$result = vms_efwp()->api->get_order( 'ORDER_ID' );
if ( is_wp_error( $result ) ) {
    VMS_EFWP_Logger::error( $result->get_error_message(), 'api' );
    return;
}
```

When extending:

```php
public function some_action( $id, $payload = array() ) {
    return $this->request( 'POST', '/resource/' . rawurlencode( $id ) . '/action', $payload );
}
```

### 6.1 Official API reference (`docs/`)

The `docs/` folder contains **local copies** of FastSpring's official OpenAPI documentation (exported from [developer.fastspring.com](https://developer.fastspring.com)). **Always consult these files** before adding query params or POST body fields — the live API is stricter than some schema sections suggest.

| File | Resource |
|------|----------|
| `docs/Products.md` | Products CRUD, pricing, offers |
| `docs/Orders.md` | Orders list/search/get |
| `docs/Subscriptions.md` | Subscriptions list/update |
| `docs/Coupons.md` | Coupons |
| `docs/events.md` | Events API |
| `docs/webhook.md` | Webhook setup + event types |
| `docs/FastSpring-Accounts.md` | Accounts |
| `docs/invoice.md` | Invoices |
| `docs/Quotes.md` | Quotes |
| `docs/Returns.md` | Returns |
| `docs/Sessions.md` | Checkout sessions |
| `docs/Data.md` | Reports / data jobs |

**Rule:** If a field appears in a GET response schema but not in POST create/update examples, assume it is **read-only** until proven otherwise. Example: `visibility` on products (see §6.5).

### 6.2 Query parameter filtering

`VMS_EFWP_API::filter_query_params( $params, $allowed )` strips unknown or empty query keys before `request()`. **Never pass raw `$_GET` / admin filter arrays** to the API without filtering — FastSpring returns `"Pagination is not supported for this endpoint"` for unsupported `page` / `limit`.

### 6.3 Endpoint pagination & allowed query params

| Method | Endpoint | Pagination | Allowed query params |
|--------|----------|------------|----------------------|
| `list_products()` | `GET /products` | **None** — returns all paths in one response | *(none — `$params` ignored)* |
| `get_coupons()` | `GET /coupons` | **None** | *(none — `$params` ignored)* |
| `list_subscriptions()` | `GET /subscriptions` | **None** | `accountId`, `begin`, `end`, `event`, `products`, `scope`, `status` |
| `list_events()` | `GET /events/{type}` | **None** (max ~25 events per call) | `days` (required, 1–30, default 7), `begin`, `end` |
| `list_orders()` / `search_orders()` | `GET /orders` | **Yes** | `begin`, `end`, `days`, `limit`, `page`, `products`, `rebill`, `returns`, `scope` |
| `list_product_prices()` | `GET /products/price` | **Yes** | `country`, `currency`, `page`, `limit` |
| `get_accounts()` | `GET /accounts` | **Yes** | `email`, `begin`, `end`, `days`, `products`, `subscriptions`, `refunds`, `limit`, `page` |
| `list_quotes()` | `GET /quotes` | Filter only | `createdEmail`, `onlyQuoteId` |

**Important:** `search_orders()` delegates to `list_orders()` — there is **no** `/orders/search` endpoint. Do not reintroduce it.

Admin screens that call non-paginated list endpoints use **client-side pagination** via `VMS_EFWP_API::paginate_items()`:

- Products catalog (`class-vms-efwp-admin-products.php`) — fetches all paths, then pages locally.
- Subscriptions catalog tab (`class-vms-efwp-admin-subscriptions.php`) — same pattern for product paths; subscription search uses API filters without `page`/`limit`.
- Events admin — no `page` param; note in UI that FastSpring returns up to ~25 events per request.

### 6.4 Product POST payload sanitization

**Problem fixed (2025):** Editing a product and saving sent `visibility` from the GET response back to `POST /products`, causing:

```json
{"product":"my-product","action":"product.update","result":"error","error":{"visibility":"Field was not recognized"}}
```

**Root cause:** `visibility` (and `quotable`) appear in **GET** `/products/{path}` schemas in `docs/Products.md` but are **not accepted** on `POST /products`. Visibility must be changed in the FastSpring app.

**Fix — always route product upserts through the sanitizer:**

- `VMS_EFWP_API::sanitize_product_upsert_payload( $product )` — public; strips read-only fields.
- `upsert_products()` / `upsert_product()` call the sanitizer before every `POST /products`.
- Applies to admin saves, WooCommerce product sync, and `ensure_catch_all_product()`.

**Allowed top-level POST fields** (whitelist):

`product`, `display`, `pricing`, `description`, `fulfillment`, `attributes`, `image`, `format`, `sku`, `badge`, `rank`

**Stripped / never send:** `visibility`, `quotable`, `offers` (use `upsert_product_offers()` on `/products/offers/{path}` instead), and any GET-only metadata (`action`, `result`, `created.id`, etc.).

**Format enum** (product level): `digital`, `physical`, `digital-and-physical` — **not** `service`.

**Admin UI (`class-vms-efwp-admin-products.php`):**

- Removed editable Visibility field; shows read-only note + catalog table column from GET data.
- Format `<select>` matches docs enum.
- `assets/js/admin.js` `prefillProductForm()` no longer sets visibility; maps legacy `service` format to `digital`.

**When adding new product fields:** verify against `docs/Products.md` POST examples first, then add to `sanitize_product_upsert_payload()` — do not pass through raw GET objects.

### 6.5 Error message formatting

`extract_error_message()` prefixes per-item errors with the resource id when present:

- Products: `plejd-termostat: {"visibility":"Field was not recognized"}`
- Coupons: `{coupon_path}: …`

Nested `error` objects are JSON-encoded for display.

---

## 7. Webhook flow

Endpoint: `https://<site>/?vms-efwp-webhook=1`

`VMS_EFWP_Webhook::maybe_handle()` on `parse_request`:

1. Bails unless `?vms-efwp-webhook=1`.
2. Returns 403 if `enable_webhook` is not `yes`.
3. Reads `php://input`, verifies `X-FS-Signature` header against `base64( hmac_sha256( body, webhook_secret() ) )` via `hash_equals`.
4. JSON-decodes; for each event in `payload['events']`:
   - **Always** records into `vms_efwp_events` (idempotent on `event_id`) — regardless of permissions.
   - Checks `VMS_EFWP_Webhook_Permissions::is_event_enabled( $type )`:
     - If **enabled** → runs plugin business handlers via `apply_event_handlers()`.
     - If **disabled** → skips handlers, logs info, still marks event processed.
   - **Always** fires `do_action( 'vms_efwp_event_' . $type, … )` and `do_action( 'vms_efwp_event', … )` (extension hooks run even when handlers skipped).
   - Marks processed or stores `error_message` on `Exception`.
5. Returns `200 { "ok": true }`.

WC order linking uses `tags.wc_order_id` (set in gateway session payload) or numeric `reference` fallback.

### 7.1 Webhook event permissions

Class: `VMS_EFWP_Webhook_Permissions` (instantiated inside `VMS_EFWP_Webhook`).

FastSpring lets merchants choose which event types each webhook URL receives. The plugin mirrors that configuration so it does not run order/subscription logic for events the merchant disabled (e.g. `subscription.trial.reminder`).

**Sync flow:**

1. `GET /webhooks` via `VMS_EFWP_API::get_webhooks()`.
2. `extract_webhook_event_permissions( $response, $settings->webhook_url() )` finds the hook whose URL matches this site's receiver URL.
3. Enabled event types stored per mode in `vms_efwp_settings`:
   - `webhook_enabled_events_live` / `webhook_enabled_events_sandbox`
   - `webhook_enabled_events_*_synced_at` timestamps
4. Cached in transients (`vms_efwp_webhook_events_{mode}`, TTL 600s).

**Admin UI:** Webhooks screen (`class-vms-efwp-admin-webhooks.php`) — "Event permissions" table, **Refresh from FastSpring** button, highlights matching receiver URL. Badge styles in `assets/css/admin.css` (`.vefwp-badge--*`).

**`is_event_enabled( $event_type )` logic:**

| State | Behavior |
|-------|----------|
| Permissions never synced | **Permissive** — all events run handlers (backward compatible) |
| Synced list contains `*` or `"all"` | All events enabled |
| Synced list is explicit types | Only listed types run handlers |
| Unknown / empty type | Handlers skipped |

**Handler catalog:** `VMS_EFWP_Webhook_Permissions::handler_catalog()` documents every event type the plugin understands, with `required` flag for events critical to WooCommerce (e.g. `order.completed`, `return.created`).

**Do not** bypass permissions in `process_event()` unless explicitly requested — merchants disable events in FastSpring for a reason.

### 7.2 Localhost dev webhook inject

FastSpring cannot POST to `127.0.0.1` / `localhost`. On local dev hosts only, use:

`?vms-efwp-webhook-dev=1`

| Input | Parameter |
|-------|-----------|
| POST body | Raw FastSpring JSON (`{"events":[...]}`) |
| Inline JSON | `payload` (plain, URL-encoded, or base64) |
| Remote file | `url` or `source` (http/https JSON URL) |

- Detected via `VMS_EFWP_Webhook::is_localhost_environment()` (`localhost`, `127.0.0.1`, `127.x.x.x`, `::1`).
- **Returns 403 on live domains** — never enabled in production.
- Skips HMAC verification; runs handlers even if FastSpring permissions would skip them (`process_event( $event, true )`).
- Documented with curl examples on **FastSpring → Tools** when localhost is detected.

---

## 8. WooCommerce gateway

Class: `VMS_EFWP_WC_Gateway` — id **`vms_efwp`**, supports `products`, `refunds`.

- Loaded only if WooCommerce is active (see `VMS_EFWP_WC_Gateway_Loader` in free; overlay + Blocks in `VMS_EFWP_Pro_WC_Gateway_Loader`).
- Declares HPOS + Cart/Checkout Blocks compatibility on `before_woocommerce_init`.
- **Free:** classic checkout redirect via `woocommerce_payment_gateways`.
- **Pro:** overlay popup checkout + `VMS_EFWP_WC_Blocks` (handle `vms-efwp-blocks`, setting key `vms_efwp_data`).

`process_payment()`:

1. Validates credentials + storefront.
2. Builds line items according to the active **pricing strategy**:
   - `catalog` — send slug + qty only; use FastSpring catalog price.
   - `per_product_override` — send slug + qty + per-unit price (WC line total / qty).
   - `single_custom_price` — send a single configured "Custom Price" product path with the order subtotal − discount.
3. POSTs to `/sessions` with `tags.wc_order_id = order_id`.
4. Persists `_vms_efwp_session_id` on the WC order, sets order to `pending`, and redirects to `https://{storefront}/session/{session_id}`.

- Error messages are humanised by `humanize_fastspring_error()` (currency / product / price-override / auth diagnostics).
- `process_refund()` calls `VMS_EFWP_API::create_return()` with the FastSpring order id stored on the WC order's transaction id.
- The FastSpring storefront host is whitelisted via `allowed_redirect_hosts` so `wp_safe_redirect` doesn't strip it.
- Return URL hook: `woocommerce_api_vms_efwp_return`.

**Product path resolution:**

```php
$slug = get_post_meta( $product->get_id(), '_vms_efwp_product_path', true );
if ( ! $slug ) {
    $slug = sanitize_title( $product->get_slug() );
}
```

Always set `_vms_efwp_product_path` explicitly if the FastSpring product path differs from the WC slug.

---

## 9. Admin UI

`VMS_EFWP_Admin::MENU_SLUG` = **`vms-efwp`**. Subpages use `vms-efwp-{resource}` (e.g. `vms-efwp-settings`).

Top-level menu label: **FastSpring** (`dashicons-chart-area`, position 56).

**Free submenus:** Dashboard (status), Orders (stored), Settings, Upgrade to Pro (hidden when licensed).

**Pro submenus** (registered on `vms_efwp_register_admin_menu` from `VMS_EFWP_Pro_Admin_Features`): Subscriptions, Invoices, Quotes, Returns (Sales); Products, Subscription Products, Coupons, Shortcodes (Catalog); Accounts, Sessions (Customers); Events, Reports, Webhooks (Integrations); Tools (System).

**Subscription product catalog** (Pro) lives under **Catalog → Subscription Products** (`vms-efwp-subscription-catalog` → `admin.php?page=vms-efwp-subscriptions&tab=catalog`). Customer subscription management stays on **Sales → Subscriptions**.

Each submenu page is rendered by a `VMS_EFWP_Admin_*::render()` static method. Resource screens extend `VMS_EFWP_Admin_Resource_Base`.

**Product path (slug) UX:** Create forms put **Display name** first. Typing a name auto-generates the slug in the path field (`assets/js/admin.js` → `vefwpSlugify()`). Manual path edits are preserved until reset; blur always normalizes (e.g. `VMS Fastspring Plugin` → `vms-fastspring-plugin`). Server-side fallback: `VMS_EFWP_Admin_Resource_Base::sanitize_product_path()`. Forms use `data-vefwp-slug-form`, `data-vefwp-slug-source`, and `data-vefwp-slug-target`. On **edit**, the path field is readonly and slug auto-sync stops.

### 9.1 Assets

`enqueue_assets()` runs when `$hook` contains `vms-efwp` or `fastspring`:

| Handle | File | When |
|--------|------|------|
| `vms-efwp-admin` | `assets/css/admin.css` | All plugin admin pages |
| `vms-efwp-chartjs` | Chart.js 4.4.4 CDN (filterable via `vms_efwp_chartjs_url`) | Pro analytics dashboard only |
| `vms-efwp-admin` (JS) | `assets/js/admin.js` | All plugin admin pages |

Pro checkout frontend assets (`checkout-popup.css`, `overlay-shell.js`, etc.) register from the Pro plugin via `vms_efwp_pro_asset_url()`.

Localized object: **`VMSEFWP`** (`ajax_url`, `nonce`, `currency`, `i18n`).

### 9.2 AJAX / admin_post

All AJAX requires `manage_options` + nonce `vms_efwp_admin`:

| Action | Purpose | Plugin |
|--------|---------|--------|
| `vms_efwp_test_connection` | API ping | Free |
| `vms_efwp_dashboard_data` | KPIs, charts, recent orders | Pro |
| `vms_efwp_sync_subscription` | Pull + upsert subscription | Pro |
| `vms_efwp_cancel_subscription` | Cancel at period end | Pro |

Settings save: `admin_post_vms_efwp_save_settings` (nonce `vms_efwp_settings_save`).

---

## 10. Filters and actions (extension points)

| Hook | Type | Args |
|------|------|------|
| `vms_efwp_event` | action | `( string $type, array $data, array $event )` |
| `vms_efwp_event_{type}` | action | `( array $data, array $event )` |
| `vms_efwp_session_payload` | filter | `( array $payload, WC_Order $order, $gateway, string $strategy )` |
| `vms_efwp_chartjs_url` | filter | `( string $url )` |
| `plugin_action_links_{basename}` | filter | Dashboard + Settings links |
| `allowed_redirect_hosts` | filter | Storefront + fastspring.com hosts |

Document new hooks here. Prefix with `vms_efwp_`.

---

## 11. Logging

```php
VMS_EFWP_Logger::info( $message, $channel, $context = array() );
VMS_EFWP_Logger::warning( $message, $channel, $context = array() );
VMS_EFWP_Logger::error( $message, $channel, $context = array() ); // always logged
```

Channels: `api`, `webhook`, `gateway`, `sync`, `general`. Context must be JSON-encodable.

---

## 12. Coding conventions

- **WordPress Coding Standards** (PHPCS). Tabs for PHP indentation.
- Every class file: `defined( 'ABSPATH' ) || exit;`
- Sanitise all input; nonces on state-changing handlers.
- Text domain **`vms-elements-fastspring-woo-payment`** on **every** i18n string in both free and Pro PHP/JS; Pro does **not** ship its own `.pot` or `languages/` folder.
- Regenerate the shared template in the free plugin after adding strings (see **§12.1**).
- Use `wp_remote_*`, never raw `curl_*`.
- `$wpdb->prepare()` for dynamic SQL values; table names may be interpolated.
- DB writes via `VMS_EFWP_Data_Store::*` or install routine only.
- No composer, no build step. PHP + assets ship as-is.
- Avoid narrating comments; explain non-obvious constraints only.

### 12.1 Translations (free + Pro, one catalog)

| Item | Location |
|------|----------|
| **Text domain** | `vms-elements-fastspring-woo-payment` (free **and** Pro — never `…-pro`) |
| **POT / MO / JSON** | `languages/` in the **free** plugin only |
| **Pro add-on** | No `languages/` folder; `VMS_EFWP_Pro::load_shared_textdomain()` loads MO files from the free plugin path |

Regenerate the merged POT after string changes:

```bash
wp i18n make-pot wp-content/plugins/vms-elements-fastspring-woo-payment-pro languages/.pro-merge-temp.pot --domain=vms-elements-fastspring-woo-payment
wp i18n make-pot wp-content/plugins/vms-elements-fastspring-woo-payment languages/vms-elements-fastspring-woo-payment.pot --domain=vms-elements-fastspring-woo-payment --exclude=docs --merge=languages/.pro-merge-temp.pot
```

WordPress.org language packs for the free slug apply to Pro strings that share the same domain. Ship compiled `.mo` / `.json` with Pro downloads only if you mirror the free `languages/` files on vmselements.com — do not duplicate the POT in the Pro ZIP.

---

## 13. Common tasks — quick recipes

### Add a FastSpring API endpoint

1. Method on `VMS_EFWP_API` in the matching section.
2. `rawurlencode()` path segments.
3. Docblock + optional admin consumer.

### Add an admin page

1. `includes/admin/class-vms-efwp-admin-{name}.php` extending `VMS_EFWP_Admin_Resource_Base`.
2. `require_once` in `VMS_EFWP::includes()` (`is_admin()` block).
3. Entry in `VMS_EFWP_Admin::register_menu()` `$pages` with slug `vms-efwp-{name}`.

### Handle a new webhook event

1. Add entry to `VMS_EFWP_Webhook_Permissions::handler_catalog()` (label, category, required, description).
2. Add `case` in `VMS_EFWP_Webhook::apply_event_handlers()`.
3. Persist via `VMS_EFWP_Data_Store` where applicable.
4. `vms_efwp_event_{type}` fires automatically (even if handler skipped by permissions).
5. Document in §7.1; merchant must enable the event in FastSpring webhook settings.

### Add or change a product POST field

1. Confirm field is in `docs/Products.md` **POST** examples (not GET-only).
2. Add to `sanitize_product_upsert_payload()` whitelist + nested sanitizer if needed.
3. Update admin form in `class-vms-efwp-admin-products.php` and `admin.js` prefill if user-editable.
4. Never round-trip GET-only fields like `visibility` or `quotable`.

### Add a paginated admin list

1. Check `docs/` for whether the endpoint supports `page`/`limit`.
2. If yes → pass only allowed keys via `filter_query_params()`.
3. If no → fetch full list, paginate with `VMS_EFWP_API::paginate_items()`.
4. Document allowed params in §6.3 table.

### Add a setting

1. `VMS_EFWP_Settings::defaults()` + install seed if needed.
2. Form + `handle_save()` in `class-vms-efwp-admin-settings.php`.
3. Read via `$settings->get()`.

### Bump schema

1. Edit `CREATE TABLE` in `VMS_EFWP_Install::create_tables()`.
2. Bump version in main plugin file header + `VMS_EFWP_VERSION`.
3. Reactivate to run `dbDelta`.

---

## 14. Gotchas

### API & admin screens

- **Do not send `page` / `limit`** to `GET /products`, `GET /subscriptions`, `GET /coupons`, or `GET /events/*` — FastSpring rejects them. Use `filter_query_params()` and/or client-side `paginate_items()`.
- **Do not send `visibility` (or other GET-only product fields) on `POST /products`** — use `sanitize_product_upsert_payload()`; visibility is managed in the FastSpring app only.
- **Product `format`** must be `digital`, `physical`, or `digital-and-physical` — not `service`.
- **Offers** belong on `POST /products/offers/{path}` via `upsert_product_offers()`, not the main product upsert.
- **`search_orders()`** is an alias for `list_orders()` — there is no `/orders/search` route.
- **OpenAPI schemas in `docs/` can over-document GET fields** — trust POST examples and live API errors over response-only schema properties.

### Webhooks

- **Event recording ≠ handler execution** — all events are stored in `vms_efwp_events`; handlers run only when permissions allow.
- **Refresh webhook permissions** after changing subscribed events in FastSpring Integrations → Webhooks.
- **Permissive fallback** when permissions were never synced — do not assume an empty list means "all disabled".

### General

- **`vms_efwp()->settings` / `->api`** are null before `plugins_loaded` priority 10.
- **Blocks + classic gateway** both required; Blocks `is_active()` is permissive — cart gating is in `VMS_EFWP_WC_Gateway::is_available()`.
- **Plugin directory name** must be `vms-elements-fastspring-woo-payment` (matches text domain / main file for WordPress.org).
- **Storefront redirect**: whitelist in `allowed_redirect_hosts` (`{storefront}`, `fastspring.com`, `onfastspring.com`).
- **Sandbox storefront**: `*.test.onfastspring.com`. **Live**: `*.onfastspring.com`.
- **Empty webhook secret** accepts unsigned webhooks with a warning log — do not change silently.
- **`tags.wc_order_id`** is required for WC order completion linking.
- **Refunds**: WC `process_refund()` → FastSpring `/returns` → webhook sets WC status; don't double-apply.
- **Unique product paths** — one `_vms_efwp_product_path` per FastSpring slug.
- **Dashboard** defaults to `is_test = 0`; pass `include_test=1` in AJAX for sandbox data.

---

## 15. Plugin Check (WordPress.org)

Before shipping, run **Plugin Check** with **zero errors**:

```bash
wp plugin check vms-elements-fastspring-woo-payment --path=/path/to/wordpress
```

Notes:

- **Text domain** must match the plugin folder slug.
- **No `wp-` prefix** in slug, functions, or classes.
- **`.gitignore`** is dev-only — use `gitignore.example`; dotfiles flag errors in production mode unless `WP_DEBUG` / non-production environment.
- Fix **ERROR** level findings; **WARNING** on direct DB queries is expected for custom tables.

---

## 16. Manual QA checklist

Before shipping a non-trivial change:

- [ ] Activate/deactivate without PHP notices; tables created.
- [ ] Sandbox + Live **Test connection** in Settings.
- [ ] Classic + Blocks checkout redirect to FastSpring.
- [ ] Webhook completes WC order; row in `vms_efwp_orders`; dashboard updates.
- [ ] WC refund → FastSpring return → webhook sets **Refunded**.
- [ ] Subscription appears; MRR updates; cancel from admin works.
- [ ] Tampered webhook body → signature failure (401).
- [ ] WooCommerce deactivated → no fatals; gateway hidden; dashboard still loads.
- [ ] **Products admin:** catalog loads without pagination errors; create/update product succeeds (no `visibility` error).
- [ ] **Subscriptions / Events admin:** list views load without `"Pagination is not supported"` errors.
- [ ] **Webhooks admin:** Refresh permissions; disabled FastSpring events skip handlers but still appear in Events log.

---

## 17. Recent fixes log (for agent context)

Summary of non-trivial API/admin fixes — read before reworking these areas:

| Date | Area | Issue | Fix |
|------|------|-------|-----|
| 2025 | Webhooks | Plugin ran handlers for all event types even when merchant disabled them in FastSpring | `VMS_EFWP_Webhook_Permissions` syncs from `GET /webhooks`; handlers gated by `is_event_enabled()` |
| 2025 | API pagination | Admin sent `page`/`limit` to endpoints that reject pagination | `filter_query_params()` + endpoint-specific allowed keys; client-side `paginate_items()` for products/subscriptions |
| 2025 | Orders search | Used non-existent `/orders/search` | `search_orders()` → `list_orders()` on `GET /orders` |
| 2025 | Products POST | `visibility: Field was not recognized` on update | Removed from admin payload; `sanitize_product_upsert_payload()` strips GET-only fields before every upsert |
| 2025 | Product format | Invalid `service` option in admin form | Replaced with `digital-and-physical` per `docs/Products.md` |
| 2026 | Free vs Pro | Pro code was feature-gated inside free ZIP (crackable) | Pro implementation moved to separate `vms-elements-fastspring-woo-payment-pro` plugin; free ships gate helpers only |

**When changing API integration:** update this table and the relevant §6 / §7 sections in the same commit.

---

## 18. Free vs Pro split

| Plugin | Slug | Distribution |
|--------|------|--------------|
| **Free** | `vms-elements-fastspring-woo-payment` | WordPress.org |
| **Pro add-on** | `vms-elements-fastspring-woo-payment-pro` | [vmselements.com](https://vmselements.com/product/vms-elements-fastspring-woo-payment-pro) — **separate folder, never bundled in the free ZIP** |

Pro **requires** the free plugin. Pro defines `VMS_EFWP_PRO_VERSION`, `VMS_EFWP_PRO_PATH`, `VMS_EFWP_PRO_URL` and validates an EDD Software Licensing key via `VMS_EFWP_Pro_License` (store URL `https://vmselements.com`, item name `VMS Elements Fastspring Woo Payment Pro`).

### Architecture (physical separation)

Pro implementation files **do not ship in the free plugin**. The free build only contains gate helpers (`includes/class-vms-efwp-features.php`) and shared utilities (`VMS_EFWP_Assets::checkout_js_i18n()`, `enhance_sbl_script_tag()`). Pro checkout CSS/JS is registered via `vms_efwp_pro_asset_url()` when the Pro add-on is active.

**Free includes:** gateway (redirect only), webhooks, stored orders, settings, `single_custom_price` strategy, test connection, basic dashboard, Orders admin, Upgrade submenu.

**Pro add-on includes (loaded only with valid license):** analytics dashboard (`VMS_EFWP_Pro_Admin_Dashboard`, `VMS_EFWP_Stats`), overlay + Blocks checkout, shortcodes, checkout links, payment success page, My Account subscriptions, product sync, full admin menus (catalog, subscriptions, invoices, tools, etc.), WC refunds via FastSpring, Pro assets under `vms-elements-fastspring-woo-payment-pro/assets/`.

**Bootstrap order:**

1. Free: `vms-elements-fastspring-woo-payment.php` → `VMS_EFWP::on_plugins_loaded()` → `do_action( 'vms_efwp_loaded' )`.
2. Pro: `plugins_loaded` priority 5 → `VMS_EFWP_Pro::init()` → license filter on `vms_efwp_is_pro`.
3. Pro modules: `vms_efwp_loaded` priority 20 → `VMS_EFWP_Pro_Modules::boot()` (requires valid license).

### Feature gates (free plugin)

Class: `VMS_EFWP_Features` in `includes/class-vms-efwp-features.php`.

```php
vms_efwp_is_pro();           // true when Pro add-on has a valid license
vms_efwp_feature( 'slug' );  // free features always true; Pro features need license
VMS_EFWP_Features::require( 'slug' ); // wp_die / JSON 403 when missing
vms_efwp_pro_asset_url( 'assets/css/checkout-popup.css' ); // Pro asset URL when Pro is loaded
```

Free admin registers Dashboard, Orders, Settings (+ Upgrade when not Pro). Pro registers Sales / Catalog / Integrations submenus on `vms_efwp_register_admin_menu` from `VMS_EFWP_Pro_Admin_Features` (includes **Catalog → Subscription Products**).

Hooks: `vms_efwp_is_pro`, `vms_efwp_pro_url`, `vms_efwp_loaded`, `vms_efwp_init`, `vms_efwp_register_admin`, `vms_efwp_register_admin_menu`, `vms_efwp_pro_modules_loaded`, `vms_efwp_pro_license_valid` (dev override).

---

## 19. License & contribution

- GPL-2.0-or-later only.
- External calls: `api.fastspring.com` + optional Chart.js CDN (filterable).
- No telemetry, auto-updaters, or remote includes.
