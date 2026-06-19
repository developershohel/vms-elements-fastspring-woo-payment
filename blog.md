# Sell Globally with FastSpring — Without Leaving WordPress

**VMS Elements Fastspring Woo Payment** brings FastSpring’s merchant-of-record checkout, subscriptions, and tax compliance into WooCommerce — and gives you a full FastSpring command center inside `wp-admin`. No juggling dashboards. No duct-taped integrations. Just a clean path from cart to payment to analytics.

If you sell software, digital products, or subscriptions internationally, you already know the trade-off: WooCommerce is brilliant for your storefront, but global payments, VAT, and recurring billing are a different beast. FastSpring solves that on the payment side. This plugin connects the two worlds properly.

---

## The problem we set out to solve

Most store owners end up in one of these situations:

- They run WooCommerce for the site and FastSpring for payments — but orders live in two places.
- They rely on a basic redirect integration that breaks when subscriptions renew, refunds happen, or sandbox testing gets messy.
- They have no visibility into MRR, top countries, or subscription health without logging into another platform.

**VMS Elements Fastspring Woo Payment** was built to fix all three. Checkout stays on-brand. Webhooks keep WordPress in sync. And your team gets real analytics where they already work — inside WordPress.

---

## What the plugin does (in plain English)

At its core, the plugin:

1. **Adds FastSpring as a WooCommerce payment method** — customers pay on FastSpring’s PCI-compliant checkout (redirect or popup overlay).
2. **Listens for signed webhooks** — when FastSpring completes an order, activates a subscription, or issues a refund, WordPress knows about it immediately.
3. **Stores everything locally** — orders, subscriptions, and events land in dedicated database tables for fast reporting.
4. **Surfaces a full admin hub** — dashboard, orders, subscriptions, products, coupons, invoices, quotes, returns, sessions, events, reports, and webhooks — all under one **FastSpring** menu in WordPress.

You get the power of FastSpring’s global infrastructure without giving up the WordPress workflow your team depends on.

---

## Feature highlights

### WooCommerce payment gateway (Classic + Blocks)

The gateway supports both the classic checkout and the WooCommerce Cart/Checkout blocks. When a customer clicks **Place order**, the plugin creates a FastSpring session and sends them to checkout — or opens a **popup overlay** if you prefer to keep shoppers on your page.

Three **pricing strategies** cover almost every catalog setup:

| Strategy | Best for |
|----------|----------|
| **Single custom price** | WooCommerce is the source of truth — one catch-all FastSpring product carries the real order total (coupons, dynamic pricing, everything). No need to mirror your entire catalog. |
| **Per-product override** | You have FastSpring products but want WooCommerce to set the price per line item. |
| **Catalog** | FastSpring catalog prices are authoritative; WooCommerce sends product paths and quantities only. |

The catch-all product for custom pricing? The plugin can **create it automatically** on first checkout. You focus on selling; it handles the plumbing.

### Popup checkout overlay

For a smoother UX, configure a **Popup checkout path** from FastSpring’s Store Builder Library. Shoppers stay on your site while FastSpring handles payment in a secure overlay — works with Gutenberg checkout and the classic `[woocommerce_checkout]` shortcode.

### Webhooks you can trust

Every webhook is verified with **HMAC-SHA256** before anything runs. The plugin records all incoming events and only executes business logic for event types **enabled in your FastSpring webhook configuration** — so you stay aligned with what you actually subscribed to in the FastSpring app.

Handled events include:

- `order.completed`, `order.canceled`, and pending payment states
- `subscription.activated`, renewals, trial reminders, and cancellations
- `return.created` and refunds (synced back to WooCommerce orders)
- Account and mailing-list events (acknowledged and logged)

Linking WooCommerce orders to FastSpring is automatic when you tag sessions with the WC order ID — completion and refund flows just work.

### Analytics dashboard inside WordPress

Open **FastSpring → Dashboard** and see:

- Revenue KPIs (today, 7 days, 30 days, all time)
- Daily revenue trend chart
- Subscription status breakdown
- **MRR estimate**
- Top products and top countries
- Recent orders at a glance

Sandbox data can be included or excluded — handy when your team is testing without polluting live numbers.

### Full FastSpring admin toolkit

Beyond the dashboard, dedicated screens let you work with FastSpring resources without opening another tab:

- **Orders** — search, filter, paginate, sync
- **Subscriptions** — status badges, cancel actions, subscription product catalog
- **Products** — create, edit, and manage catalog items with smart slug generation from the display name
- **Coupons, Invoices, Quotes, Returns**
- **Sessions** — create and inspect checkout sessions
- **Events** — processed and unprocessed webhook/API events
- **Reports** — generate FastSpring data reports
- **Webhooks** — receiver URL, permission sync, event status table
- **Tools** — connection testing, logs, migration utilities

Creating a product? Type **“VMS Fastspring Plugin”** as the display name and the slug becomes `vms-fastspring-plugin` automatically. Edit it if you want — the plugin normalizes it for FastSpring’s API either way.

### Live + Sandbox isolation

Toggle between **Live** and **Sandbox** mode in settings. Each mode has its own:

- API username and password
- Storefront ID
- Webhook secret
- Webhook event permissions cache

Test freely in sandbox, flip to live when you’re ready — no credential cross-contamination.

### Optional WooCommerce → FastSpring product sync

Enable one-way sync and every published WooCommerce product pushes to FastSpring when saved. Product paths follow your WooCommerce slugs (or custom meta), keeping catalogs aligned when you want FastSpring to own pricing.

### Refunds from WooCommerce

Issue a refund on a WooCommerce order and the plugin calls FastSpring’s **Returns API**. When FastSpring confirms, the webhook updates the order status in WordPress. One flow, two systems, zero double-entry.

---

## Who is this for?

- **SaaS and software vendors** selling licenses or subscriptions through WooCommerce
- **Digital product creators** who need global tax and compliance handled by FastSpring
- **Agencies** managing WooCommerce stores for clients on FastSpring
- **Teams that want WordPress as the operational hub** — not just the storefront

The plugin works **without WooCommerce** for dashboard, webhooks, and API features. Only the payment gateway requires WooCommerce to be active.

---

## Getting started in three steps

### 1. Create FastSpring API credentials

In the FastSpring app, go to **Integrations → API Credentials** and create credentials for both **Test** and **Live** environments.

### 2. Configure your webhook

Under **Integrations → Webhooks**, add a new endpoint pointing to the URL shown on **FastSpring → Settings** in WordPress (it looks like `https://yoursite.com/?vms-efwp-webhook=1`).

Copy the **HMAC SHA256** secret into the plugin settings. Select the event types you need — at minimum, enable `order.completed` and `return.created` for WooCommerce order completion and refunds.

Click **Refresh from FastSpring** on the Webhooks screen so the plugin knows which events are active.

### 3. Connect and test

Paste credentials, storefront IDs, and secrets into **FastSpring → Settings**. Choose your mode (start with Sandbox), click **Test connection**, and run a test checkout.

Use the built-in **checkout diagnostics** on the settings page if the gateway doesn’t appear — the plugin will tell you whether blocks, credentials, or popup configuration need attention.

---

## Built for reliability

Behind the polished admin UI is integration code shaped by real FastSpring API behavior:

- Query parameters are filtered per endpoint — no more “pagination not supported” errors on products or subscriptions lists.
- Product payloads are sanitized before every create/update — read-only API fields like `visibility` are never sent back by mistake.
- Webhook permissions respect your FastSpring configuration instead of blindly processing every event type.
- A one-time migration path exists from the legacy `wp-fastspring` plugin — options, tables, and meta come along for the ride.

Everything is **GPL-licensed**, with no telemetry, no phone-home, and no hidden dependencies. External calls go to `api.fastspring.com` and an optional Chart.js CDN for dashboard charts (filterable if you self-host).

---

## Security and compliance

- **No card data touches your server.** Payments run entirely on FastSpring’s infrastructure.
- **Webhook signatures are mandatory** when a secret is configured — tampered payloads are rejected.
- **Credentials live in WordPress options** and are accessed through mode-aware settings helpers — never hard-coded, never logged in plain text.

Your compliance story stays clean: WordPress runs the store; FastSpring runs payments.

---

## Try it on your store

**VMS Elements Fastspring Woo Payment** is available for WordPress 6.0+ and WooCommerce 7.0+ (PHP 7.4+).

Whether you’re launching a new digital product line or migrating an existing FastSpring setup into WooCommerce, this plugin gives you a production-ready bridge — with the dashboard and admin tools to run the business from WordPress.

**[Get VMS Elements Fastspring Woo Payment →](https://vmselements.com/product/vms-elements-fastspring-woo-payment)**

---

## Quick reference

| | |
|---|---|
| **Plugin name** | VMS Elements Fastspring Woo Payment for WooCommerce |
| **Text domain** | `vms-elements-fastspring-woo-payment` |
| **Gateway ID** | `vms_efwp` |
| **Webhook URL** | `?vms-efwp-webhook=1` |
| **Admin menu** | FastSpring (wp-admin) |
| **License** | GPL-2.0-or-later |

---

*Questions about setup, pricing strategies, or webhook configuration? Visit [VMS Elements](https://vmselements.com/product/vms-elements-fastspring-woo-payment) for documentation and support.*
