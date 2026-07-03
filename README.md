# VMS Elements Payment Gateway with FastSpring for WooCommerce

Integrate [FastSpring](https://fastspring.com) with WooCommerce — popup overlay checkout (classic + blocks), webhooks, stored orders, and core settings.

## Features

- **WooCommerce payment gateway** — classic and block checkout with FastSpring Store Builder popup overlay.
- **Three pricing strategies** — single custom price, per-product override, and FastSpring catalog pricing.
- **Webhook listener** — HMAC-SHA256 signature verification; persists orders, subscriptions, and events to custom tables.
- **Stored Orders admin** — browse webhook-synced FastSpring orders; resend invoice emails from WordPress.
- **Analytics dashboard** — KPIs, charts, and recent orders.
- **Live + Sandbox isolation** — independent API credentials, storefronts, and webhook secrets per mode.
- **Connection tester** on the Settings screen.

## Configuration

1. In the FastSpring App, create API credentials under **Integrations → API Credentials** for both Test and Live.
2. Configure a webhook under **Integrations → Webhooks** with the URL shown on the plugin's Settings page and HMAC SHA256 signing.
3. Paste credentials, storefront ids and webhook secrets into **FastSpring → Settings** in WordPress, choose your active mode, and click **Test connection**.

## File map

```
vms-elements-fastspring-payment-gateway.php
includes/
  class-vms-efpg.php
  class-vms-efpg-helpers.php
  class-vms-efpg-install.php
  class-vms-efpg-migrate.php
  class-vms-efpg-settings.php
  class-vms-efpg-api.php
  class-vms-efpg-logger.php
  class-vms-efpg-assets.php
  class-vms-efpg-webhook-permissions.php
  class-vms-efpg-webhook.php
  class-vms-efpg-data-store.php
  class-vms-efpg-wc-gateway-loader.php
  class-vms-efpg-wc-gateway.php
  class-vms-efpg-wc-blocks.php
  class-vms-efpg-checkout-overlay.php
  class-vms-efpg-checkout-loader.php
  admin/
    class-vms-efpg-admin.php
    class-vms-efpg-admin-dashboard.php
    class-vms-efpg-admin-orders.php
    class-vms-efpg-admin-settings.php
    class-vms-efpg-admin-invoice-actions.php
    class-vms-efpg-admin-resource-base.php
assets/
  css/admin.css
  css/checkout-popup.css
  js/admin.js
  js/blocks/checkout-block.js
  js/vendor/chart.umd.min.js
languages/vms-elements-fastspring-payment-gateway.pot
uninstall.php
```

## License

GPL-2.0-or-later
