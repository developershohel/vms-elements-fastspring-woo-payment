# VMS Elements Fastspring Woo Payment for WooCommerce

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
vms-elements-fastspring-woo-payment.php                    # Plugin bootstrap
includes/
  class-vms-efwp.php            # Main loader (singleton)
  class-vms-efwp-install.php    # Tables + default options
  class-vms-efwp-settings.php   # Settings store + mode-aware accessors
  class-vms-efwp-api.php        # FastSpring REST API client
  class-vms-efwp-logger.php     # DB-backed logger
  class-vms-efwp-webhook.php    # Webhook listener + HMAC verification
  class-vms-efwp-product-sync.php # WC -> FS product sync
  class-vms-efwp-data-store.php # Persistence helpers
  class-vms-efwp-stats.php      # Dashboard aggregations
  class-vms-efwp-wc-gateway-loader.php
  class-vms-efwp-wc-gateway.php # WC payment gateway
  admin/
    class-vms-efwp-admin.php
    class-vms-efwp-admin-dashboard.php
    class-vms-efwp-admin-orders.php
    class-vms-efwp-admin-subscriptions.php
    class-vms-efwp-admin-settings.php
    class-vms-efwp-admin-tools.php
assets/
  css/admin.css
  js/admin.js
languages/vms-elements-fastspring-woo-payment.pot
uninstall.php
```

## License

GPL-2.0-or-later
