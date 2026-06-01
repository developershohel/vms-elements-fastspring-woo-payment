# AGENTS.md — WP FastSpring for WooCommerce

Project-level guidance for AI coding agents working in this repository.
Read this end-to-end before making any non-trivial change.

---

## 1. What this plugin is

**WP FastSpring for WooCommerce** is a WordPress plugin that:

1. Adds a **WooCommerce payment gateway** that creates a [FastSpring](https://fastspring.com) checkout session and redirects the customer to the FastSpring storefront.
2. Listens for **FastSpring webhooks** (HMAC-SHA256 signed) and persists orders, subscriptions, refunds and events into custom DB tables.
3. Renders an **advanced analytics dashboard** inside `wp-admin` (KPI cards, daily revenue chart, subscription donut, top products/countries, recent orders, MRR estimate).
4. Provides full-featured admin resource screens for FastSpring Orders, Subscriptions, Accounts, Products, Coupons, Invoices, Quotes, Returns, Sessions, Events, Reports and Webhooks.
5. Supports **Live + Sandbox isolation** with independent credentials, storefronts and webhook secrets per mode.

- **Plugin slug / text domain:** `wp-fastspring`
- **Main file:** `wp-fastspring.php`
- **Min PHP:** 7.4 • **Min WP:** 6.0 • **Min WC:** 7.0
- **License:** GPL-2.0-or-later
- **Version constant:** `WP_FASTSPRING_VERSION`

---

## 2. File map

```
wp-fastspring.php                              # Plugin bootstrap, constants, activation hooks
uninstall.php                                  # Drops custom tables + options unless opted out
readme.txt                                     # wp.org-style readme
README.md                                      # GitHub-style readme
AGENTS.md                                      # This file
.gitignore                                     # WordPress-flavoured ignore list
LICENSE                                        # GPL-2.0-or-later

includes/
  class-wp-fastspring.php                      # Singleton loader; wires includes + WP hooks
  class-wp-fastspring-install.php              # Creates 4 custom tables + default options
  class-wp-fastspring-settings.php             # Wrapper over wp_fastspring_settings option (mode-aware)
  class-wp-fastspring-api.php                  # FastSpring REST client (api.fastspring.com)
  class-wp-fastspring-logger.php               # DB-backed logger (wp_fastspring_log)
  class-wp-fastspring-webhook.php              # Webhook listener + HMAC verify + event dispatch
  class-wp-fastspring-product-sync.php         # One-way WC -> FS product sync on save_post_product
  class-wp-fastspring-data-store.php           # Persistence helpers (orders, subs, events)
  class-wp-fastspring-stats.php                # Dashboard aggregations
  class-wp-fastspring-wc-gateway-loader.php    # Conditionally registers the WC gateway + Blocks integration
  class-wp-fastspring-wc-gateway.php           # WC_Payment_Gateway implementation
  class-wp-fastspring-wc-blocks.php            # Cart/Checkout Block payment method type
  admin/
    class-wp-fastspring-admin.php              # Menu, asset enqueue, AJAX endpoints
    class-wp-fastspring-admin-resource-base.php
    class-wp-fastspring-admin-dashboard.php
    class-wp-fastspring-admin-orders.php
    class-wp-fastspring-admin-subscriptions.php
    class-wp-fastspring-admin-accounts.php
    class-wp-fastspring-admin-products.php
    class-wp-fastspring-admin-coupons.php
    class-wp-fastspring-admin-invoices.php
    class-wp-fastspring-admin-quotes.php
    class-wp-fastspring-admin-returns.php
    class-wp-fastspring-admin-sessions.php
    class-wp-fastspring-admin-events.php
    class-wp-fastspring-admin-reports.php
    class-wp-fastspring-admin-webhooks.php
    class-wp-fastspring-admin-tools.php
    class-wp-fastspring-admin-settings.php

assets/
  css/admin.css
  js/admin.js                                  # Dashboard + AJAX + Chart.js charts
  js/blocks/checkout-block.js                  # WC Blocks payment method UI

languages/
  wp-fastspring.pot
```

> The `class-wp-fastspring-*` and `class-wp-fastspring-admin-*` naming convention is mandatory — `WP_FastSpring::includes()` and `WP_FastSpring_Admin` rely on these exact filenames.

---

## 3. Runtime architecture

### 3.1 Bootstrap order

```
wp-fastspring.php
  └── defines WP_FASTSPRING_VERSION / FILE / PATH / URL / BASENAME
  └── requires includes/class-wp-fastspring.php
  └── wp_fastspring() -> WP_FastSpring::instance()
        └── includes() requires all classes
        └── init_hooks():
              - plugins_loaded(10)  -> on_plugins_loaded()
              - init(5)             -> on_init()
              - plugins_loaded      -> load_textdomain()
              - admin_notices       -> maybe_render_woocommerce_notice()
              - plugin_action_links -> add_action_links()

on_plugins_loaded():
  - new WP_FastSpring_Settings   (cached in wp_fastspring()->settings)
  - new WP_FastSpring_API        (cached in wp_fastspring()->api)
  - new WP_FastSpring_Webhook
  - new WP_FastSpring_Product_Sync
  - if WooCommerce active        -> new WP_FastSpring_WC_Gateway_Loader
  - if is_admin()                -> new WP_FastSpring_Admin
```

### 3.2 Singleton access

Always go through the helper:

```php
$plugin   = wp_fastspring();        // WP_FastSpring instance
$settings = $plugin->settings;      // WP_FastSpring_Settings
$api      = $plugin->api;           // WP_FastSpring_API
```

`$settings` and `$api` are only available **after** `plugins_loaded` (priority 10). If you write code that may run earlier (cron, REST, early `init`), guard with `function_exists( 'wp_fastspring' )` and null checks.

---

## 4. Data layer

### 4.1 Custom tables (created on activation)

| Table                                 | Purpose                                      |
|--------------------------------------|----------------------------------------------|
| `{prefix}fastspring_orders`          | One row per FastSpring order (unique `fs_order_id`) |
| `{prefix}fastspring_subscriptions`   | One row per FastSpring subscription (unique `fs_subscription_id`) |
| `{prefix}fastspring_events`          | Raw webhook events, processed flag, error_message |
| `{prefix}fastspring_log`             | Internal log lines (level, channel, message, context) |

Schemas live in `includes/class-wp-fastspring-install.php`. **If you change a schema, bump `WP_FASTSPRING_VERSION` and run `dbDelta` from `WP_FastSpring_Install::install()` — do not write ad-hoc `ALTER TABLE` statements.**

### 4.2 Options

| Option key                                | Purpose                                |
|-------------------------------------------|----------------------------------------|
| `wp_fastspring_settings`                  | All plugin settings (see `Settings::defaults()`) |
| `wp_fastspring_db_version`                | Tracks installed schema version        |
| `wp_fastspring_keep_data_on_uninstall`    | If truthy, `uninstall.php` keeps tables/options |
| `woocommerce_wp_fastspring_settings`      | Standard WC per-gateway option (enabled/title/description) |

### 4.3 Order / subscription persistence

- Source of truth = **webhook events**, not the redirect return.
- `WP_FastSpring_Data_Store::upsert_order()` is idempotent on `fs_order_id`.
- `WP_FastSpring_Data_Store::upsert_subscription()` is idempotent on `fs_subscription_id`.
- Test-mode events set `is_test = 1`; dashboard queries default to `is_test = 0`.

---

## 5. Modes, credentials, secrets

`WP_FastSpring_Settings` is **mode-aware**. The same accessors return different values depending on `get_mode()` (`live` or `sandbox`):

```php
$settings->api_username();   // sandbox_username  OR  live_username
$settings->api_password();   // sandbox_password  OR  live_password
$settings->storefront();     // sandbox_storefront OR live_storefront
$settings->webhook_secret(); // webhook_secret_sandbox OR webhook_secret_live
$settings->has_credentials();
$settings->is_sandbox();
$settings->webhook_url();    // home_url() + ?wp-fastspring-webhook=1
```

**Never** read raw credentials directly out of `wp_fastspring_settings`. Always go through these methods so the active mode is honoured.

---

## 6. FastSpring REST API client

`WP_FastSpring_API` wraps `https://api.fastspring.com` using HTTP Basic Auth.

- All public methods return either a decoded `array` or `WP_Error` — **always** check with `is_wp_error()`.
- `request()` logs every non-2xx response into `wp_fastspring_log` under the `api` channel.
- Sections grouped by resource: Accounts, Coupons, Products, Orders, Subscriptions, Invoices, Quotes, Returns, Sessions (V1 + V2), Events, Data/Reports, Webhooks.

Example:

```php
$result = wp_fastspring()->api->get_order( 'ORDER_ID' );
if ( is_wp_error( $result ) ) {
    WP_FastSpring_Logger::error( $result->get_error_message(), 'api' );
    return;
}
```

When extending the client, follow the existing pattern:

```php
public function some_action( $id, $payload = array() ) {
    return $this->request( 'POST', '/resource/' . rawurlencode( $id ) . '/action', $payload );
}
```

`User-Agent` is set to `WP-FastSpring/{version}; {home_url}`.

---

## 7. Webhook flow

Endpoint: `https://<site>/?wp-fastspring-webhook=1`

`WP_FastSpring_Webhook::maybe_handle()` runs on `parse_request`:

1. Bails unless `?wp-fastspring-webhook=1` is set.
2. Returns 403 if `enable_webhook` is not `yes`.
3. Reads `php://input`, verifies `X-FS-Signature` header against `base64( hmac_sha256( body, webhook_secret() ) )` via `hash_equals`.
4. JSON-decodes; iterates `payload['events']`; for each event:
   - Records raw event into `wp_fastspring_events` (idempotent on `event_id`).
   - Dispatches by `type`:
     - `order.*`        -> `upsert_order` (+ maybe complete/cancel linked WC order via `tags.wc_order_id` or numeric reference).
     - `return.created` / `order.refund` -> `mark_order_refunded` + maybe refund WC order.
     - `subscription.*` -> `upsert_subscription` (+ set status canceled when applicable).
     - `account.*`, `mailingListEntry.updated` -> acknowledged only.
   - Fires `do_action( 'wp_fastspring_event_' . $type, $data, $event )` and `do_action( 'wp_fastspring_event', $type, $data, $event )`.
   - Marks event processed (or stores error_message on `Exception`).
5. Returns `200 { "ok": true }`.

**Linking back to WooCommerce orders** relies on `tags.wc_order_id` (set by the gateway when creating the FastSpring session) or, as a fallback, a numeric `reference`.

---

## 8. WooCommerce gateway

Class: `WP_FastSpring_WC_Gateway` (id: `wp_fastspring`, supports `products`, `refunds`).

- Loaded only if WooCommerce is active (see `WP_FastSpring_WC_Gateway_Loader`).
- Declares HPOS + Cart/Checkout Blocks compatibility on `before_woocommerce_init`.
- Registers itself for **classic checkout** via `woocommerce_payment_gateways` and for **Blocks checkout** via `WP_FastSpring_WC_Blocks` (script handle `wp-fastspring-blocks`).
- `process_payment()`:
  1. Validates credentials + storefront.
  2. Builds line items according to the active **pricing strategy**:
     - `catalog`              — send slug + qty only; use FastSpring catalog price.
     - `per_product_override` — send slug + qty + per-unit price (WC line total / qty).
     - `single_custom_price`  — send a single configured "Custom Price" product path with the order subtotal − discount.
  3. POSTs to `/sessions` with `tags.wc_order_id = order_id`.
  4. Persists `_fastspring_session_id` on the WC order, sets order to `pending`, and redirects to `https://{storefront}/session/{session_id}`.
- Error messages are humanised by `humanize_fastspring_error()` (currency / product / price-override / auth diagnostics).
- `process_refund()` calls `WP_FastSpring_API::create_return()` with the FastSpring order id stored on the WC order's transaction id.

The FastSpring storefront host is whitelisted via `allowed_redirect_hosts` so `wp_safe_redirect` doesn't strip it.

**Product slug resolution** in the gateway:

```php
$slug = get_post_meta( $product->get_id(), '_fastspring_product_path', true );
if ( ! $slug ) {
    $slug = sanitize_title( $product->get_slug() );
}
```

Always set `_fastspring_product_path` explicitly if the FastSpring product path differs from the WC slug.

---

## 9. Admin UI

`WP_FastSpring_Admin` registers a top-level menu (`dashicons-chart-area`, position 56) with submenus for:

`Dashboard, Orders, Subscriptions, Accounts, Products, Coupons, Invoices, Quotes, Returns, Sessions, Events, Reports, Webhooks, Tools, Settings`

Each submenu page is rendered by a `WP_FastSpring_Admin_*::render()` static method. Resource screens extend `WP_FastSpring_Admin_Resource_Base`.

### 9.1 Assets

`enqueue_assets( $hook )` only enqueues when `$hook` contains `wp-fastspring` or `fastspring`:

- `assets/css/admin.css`
- `assets/js/admin.js` (depends on `jquery`, `wp-fastspring-chartjs`)
- Chart.js 4.4.4 from jsDelivr by default; override URL via the `wp_fastspring_chartjs_url` filter for self-hosting.

The localized object is `WPFastSpring` containing `ajax_url`, `nonce`, `currency`, `i18n`.

### 9.2 AJAX endpoints (admin)

All require `manage_options` and `wp_fastspring_admin` nonce:

| Action                                  | Purpose                          |
|-----------------------------------------|----------------------------------|
| `wp_fastspring_dashboard_data`          | KPIs + daily + top + subs + recent |
| `wp_fastspring_test_connection`         | Calls `WP_FastSpring_API::test_connection()` |
| `wp_fastspring_sync_subscription`       | Pulls a subscription from FS and upserts |
| `wp_fastspring_cancel_subscription`     | Cancels at period end via API     |

`admin_post_wp_fastspring_save_settings` handles the settings form (nonce: `wp_fastspring_settings_save`).

---

## 10. Filters and actions (public extension points)

| Hook                                              | Type   | Args                                          |
|---------------------------------------------------|--------|-----------------------------------------------|
| `wp_fastspring_event`                             | action | `( string $type, array $data, array $event )` |
| `wp_fastspring_event_{type}`                      | action | `( array $data, array $event )`               |
| `wp_fastspring_session_payload`                   | filter | `( array $payload, WC_Order $order, $gateway, string $strategy )` |
| `wp_fastspring_chartjs_url`                       | filter | `( string $url )`                             |
| `plugin_action_links_{basename}`                  | filter | adds Dashboard + Settings links               |
| `allowed_redirect_hosts`                          | filter | whitelists storefront + fastspring.com hosts  |

When adding new extension points, prefix with `wp_fastspring_` and document them here.

---

## 11. Logging conventions

```php
WP_FastSpring_Logger::info(    $message, $channel, $context = array() );
WP_FastSpring_Logger::warning( $message, $channel, $context = array() );
WP_FastSpring_Logger::error(   $message, $channel, $context = array() ); // always logged, even if logging disabled
```

Standard channels:

- `api`      — API client transport / non-2xx responses
- `webhook`  — signature failures, unknown events, processing errors
- `gateway`  — payment gateway availability + session creation
- `sync`     — product sync results
- `general`  — fallback

Context arrays must be JSON-encodable (don't pass objects — pass scalar id + relevant fields).

---

## 12. Coding conventions

- **WordPress Coding Standards** (PHPCS). Use tabs for PHP indentation, Yoda conditions are not required but `if ( ! $thing )` style spacing is.
- Every class file starts with `defined( 'ABSPATH' ) || exit;`.
- All user input must be sanitised; nonces are required for any state-changing AJAX or admin_post handler (`check_ajax_referer( 'wp_fastspring_admin', 'nonce' )`).
- All translatable strings use the text domain `wp-fastspring`. Update `languages/wp-fastspring.pot` when adding new strings.
- Use `wp_remote_request` / `wp_remote_get` / `wp_remote_post` — **never** `curl_*` directly.
- Use `$wpdb->prepare()` for every dynamic SQL; table names are interpolated (and that's fine), values are not.
- All DB writes go through `WP_FastSpring_Data_Store::*` or the install routine. Don't sprinkle `$wpdb->insert` calls throughout business logic.
- Don't add comments that just narrate code. Comment only non-obvious intent / constraints (matching the existing style — see `class-wp-fastspring-wc-gateway.php`).
- Avoid heavy dependencies. Chart.js is the only external runtime asset (CDN-loaded, filterable).
- No composer, no build step. PHP files run as-is.

---

## 13. Common tasks — quick recipes

### Add a new FastSpring API endpoint

1. Add a method to `WP_FastSpring_API` in the matching section block.
2. Use `rawurlencode()` on any path segments.
3. Document the parameters + return type in the docblock.
4. Add a tiny consumer in the admin resource screen if user-visible.

### Add a new admin page

1. Create `includes/admin/class-wp-fastspring-admin-{name}.php` extending `WP_FastSpring_Admin_Resource_Base`.
2. `require_once` it in `WP_FastSpring::includes()` (inside the `is_admin()` block).
3. Add an entry to the `$pages` array in `WP_FastSpring_Admin::register_menu()`.

### Handle a new webhook event

1. Add a `case 'your.event':` branch in `WP_FastSpring_Webhook::process_event()`.
2. Persist via `WP_FastSpring_Data_Store` (extend it if needed).
3. The `do_action( 'wp_fastspring_event_your.event', ... )` will fire automatically.

### Add a new setting

1. Extend `WP_FastSpring_Settings::defaults()` (and the install seed if it must be present on first install).
2. Add the field to `class-wp-fastspring-admin-settings.php` form + `handle_save()`.
3. Read via `$settings->get( 'your_key', 'fallback' )` — never `get_option()` directly.

### Bump the schema

1. Modify the `CREATE TABLE` in `WP_FastSpring_Install::create_tables()`.
2. Bump `WP_FASTSPRING_VERSION` in `wp-fastspring.php` and the plugin header.
3. Re-running `dbDelta` on activation will ALTER existing tables.

---

## 14. Gotchas

- **Plugin instance availability**: `wp_fastspring()->settings` / `->api` are `null` until `plugins_loaded`. Code that runs earlier must null-check.
- **WC gateway in Blocks checkout**: requires *both* `woocommerce_payment_gateways` filter (classic) *and* the `AbstractPaymentMethodType` integration (`WP_FastSpring_WC_Blocks`). The latter intentionally returns `true` in `is_active()` whenever `enabled === 'yes'`; per-cart gating happens in `WP_FastSpring_WC_Gateway::is_available()` — don't duplicate the logic.
- **Storefront redirect host**: `allowed_redirect_hosts` is filtered to allow the configured storefront, `fastspring.com`, and `onfastspring.com`. If you change the redirect target, update this whitelist.
- **Sandbox vs Live storefront hostnames**:
  - Sandbox storefront looks like `yourcompany.test.onfastspring.com`.
  - Live storefront looks like `yourcompany.onfastspring.com`.
- **Signature verification**: if `webhook_secret()` returns empty, the listener accepts unsigned webhooks but emits a `warning` log. Do not change this default silently — users will lock themselves out.
- **`order.completed` → WC order linking**: relies on `tags.wc_order_id`. The gateway sets this; any custom session-creation path **must** keep it.
- **Refunds in WC** trigger `process_refund()`, which calls FastSpring's `/returns`. The webhook then sets the local WC order status to `refunded`. Don't fire both manually.
- **Product paths**: FastSpring uses the slug as primary key. Don't let two WC products map to the same `_fastspring_product_path`.
- **Test data isolation**: dashboard SQL defaults to `is_test = 0`. Pass `include_test=1` via the AJAX request (or set in the JS UI) to include sandbox data.

---

## 15. Manual QA checklist (no automated tests yet)

Before shipping a non-trivial change:

- [ ] Activate/deactivate plugin without PHP notices; tables are created.
- [ ] Switch between Sandbox and Live in **FastSpring → Settings**; **Test connection** succeeds for each mode.
- [ ] Place a test order from the front-end via **classic** checkout and via the **block** checkout. Both should redirect to FastSpring.
- [ ] Complete the test order on FastSpring; webhook flips WC order to **Completed**, row appears in `wp_fastspring_orders`, dashboard updates.
- [ ] Trigger a refund from WC admin; FastSpring `/returns` is called; webhook flips order to **Refunded**.
- [ ] Activate a subscription on FastSpring; subscription row appears, MRR card updates.
- [ ] Cancel a subscription from the Subscriptions screen; status becomes `canceled` on FS and locally.
- [ ] Re-deliver a webhook with a tampered body; signature verification fails (401).
- [ ] Disable WooCommerce; the gateway disappears but the dashboard, settings, webhook listener and API client still load without fatal errors.

---

## 16. License & contribution

- Code is GPL-2.0-or-later. Anything you add must be compatible.
- No external SaaS calls other than `api.fastspring.com` and the optional Chart.js CDN.
- Don't introduce telemetry, auto-updaters or remote includes.
