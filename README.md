# VMS Elements Fastspring Woo Payment for WooCommerce

The **free WordPress.org plugin** for integrating [FastSpring](https://fastspring.com) with WooCommerce — redirect checkout, webhooks, stored orders, and core settings.

Advanced analytics, overlay checkout, catalog tools, and subscription management ship in the separate **[Pro add-on](https://vmselements.com/product/vms-elements-fastspring-woo-payment-pro)** (not bundled in this repository's org ZIP).

## Free plugin features

- **WooCommerce payment gateway** — creates FastSpring checkout sessions and redirects customers to your FastSpring storefront.
- **Webhook listener** — HMAC-SHA256 signature verification; persists orders, subscriptions, and events to custom tables.
- **Stored Orders admin** — browse webhook-synced FastSpring orders from WordPress.
- **Status dashboard** — credentials check, mode indicator, recent stored orders.
- **Live + Sandbox isolation** — independent API credentials, storefronts, and webhook secrets per mode.
- **Connection tester** on the Settings screen.

## Pro add-on (separate plugin)

Install **VMS Elements Fastspring Woo Payment Pro** from [VMS Elements](https://vmselements.com/product/vms-elements-fastspring-woo-payment-pro) to unlock:

- Advanced analytics dashboard (KPIs, charts, MRR, top products/countries)
- Overlay popup checkout + WooCommerce Blocks support
- Catalog admin (Products, Subscription Products, Coupons, Shortcodes)
- Subscriptions, Invoices, Quotes, Returns, Accounts, Sessions, Events, Reports, Webhooks, Tools
- Payment links, payment success pages, My Account subscriptions, product sync, WC refunds via FastSpring

## Configuration

1. In the FastSpring App, create API credentials under **Integrations → API Credentials** for both Test and Live.
2. Configure a webhook under **Integrations → Webhooks** with the URL shown on the plugin's Settings page and HMAC SHA256 signing.
3. Paste credentials, storefront ids and webhook secrets into **FastSpring → Settings** in WordPress, choose your active mode, and click **Test connection**.

## File map (free plugin only)

```
vms-elements-fastspring-woo-payment.php
includes/
  class-vms-efwp.php
  class-vms-efwp-features.php     # Pro gate helpers
  class-vms-efwp-install.php
  class-vms-efwp-migrate.php
  class-vms-efwp-settings.php
  class-vms-efwp-api.php
  class-vms-efwp-logger.php
  class-vms-efwp-assets.php       # Shared checkout i18n helpers
  class-vms-efwp-webhook-permissions.php
  class-vms-efwp-webhook.php
  class-vms-efwp-data-store.php
  class-vms-efwp-wc-gateway-loader.php
  class-vms-efwp-wc-gateway.php
  admin/
    class-vms-efwp-admin.php
    class-vms-efwp-admin-dashboard.php
    class-vms-efwp-admin-orders.php
    class-vms-efwp-admin-settings.php
    class-vms-efwp-admin-resource-base.php
assets/
  css/admin.css
  js/admin.js
languages/vms-elements-fastspring-woo-payment.pot
uninstall.php
docs/AGENTS.md                    # Agent/developer guide (excluded from WordPress.org ZIP)
```

Pro implementation files live in the sibling folder `vms-elements-fastspring-woo-payment-pro/` (separate product).

## License

GPL-2.0-or-later
