# WP FastSpring for WooCommerce

A WordPress + WooCommerce integration for [FastSpring](https://fastspring.com), bundled with an advanced analytics dashboard inside `wp-admin`.

## Features

- **WooCommerce payment gateway** that creates FastSpring checkout sessions and redirects customers to the FastSpring storefront.
- **Webhook listener** with HMAC-SHA256 signature verification for `order.*`, `subscription.*`, `return.*`, and `account.*` events.
- **Advanced analytics dashboard** with KPI cards (today / 7d / 30d / all-time), revenue trend chart, subscription donut chart, top products, top countries, recent orders, MRR estimate.
- **Orders and Subscriptions screens** with filters, search, pagination, and per-row sync/cancel actions.
- **Live + Sandbox isolation** — independent API credentials, storefronts and webhook secrets per mode, with a one-click toggle.
- **Refunds** routed through FastSpring's Returns API directly from the WooCommerce order screen.
- **One-way product sync** (optional) from WooCommerce to FastSpring on save.
- **Event + log inspector** for debugging integrations.

## Configuration

1. In the FastSpring App, create API credentials under **Integrations → API Credentials** for both Test and Live.
2. Configure a webhook under **Integrations → Webhooks** with the URL shown on the plugin's Settings page and HMAC SHA256 signing.
3. Paste credentials, storefront ids and webhook secrets into **FastSpring → Settings** in WordPress, choose your active mode, and click **Test connection**.

## File map

```
wp-fastspring.php                    # Plugin bootstrap
includes/
  class-wp-fastspring.php            # Main loader (singleton)
  class-wp-fastspring-install.php    # Tables + default options
  class-wp-fastspring-settings.php   # Settings store + mode-aware accessors
  class-wp-fastspring-api.php        # FastSpring REST API client
  class-wp-fastspring-logger.php     # DB-backed logger
  class-wp-fastspring-webhook.php    # Webhook listener + HMAC verification
  class-wp-fastspring-product-sync.php # WC -> FS product sync
  class-wp-fastspring-data-store.php # Persistence helpers
  class-wp-fastspring-stats.php      # Dashboard aggregations
  class-wp-fastspring-wc-gateway-loader.php
  class-wp-fastspring-wc-gateway.php # WC payment gateway
  admin/
    class-wp-fastspring-admin.php
    class-wp-fastspring-admin-dashboard.php
    class-wp-fastspring-admin-orders.php
    class-wp-fastspring-admin-subscriptions.php
    class-wp-fastspring-admin-settings.php
    class-wp-fastspring-admin-tools.php
assets/
  css/admin.css
  js/admin.js
languages/wp-fastspring.pot
uninstall.php
```

## License

GPL-2.0-or-later
