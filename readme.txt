=== VMS Elements Payment Gateway with FastSpring for WooCommerce ===
Contributors: vmsuniverse
Tags: woocommerce, fastspring, payments, subscriptions, ecommerce
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WooCommerce to FastSpring checkout (classic + blocks), webhooks, and stored orders.

== Description ==

VMS Elements Payment Gateway with FastSpring for WooCommerce is a WordPress plugin that connects WooCommerce to FastSpring checkout and subscription webhooks.

= Features =

* WooCommerce payment gateway — **classic and block checkout** with FastSpring popup overlay.
* HMAC-verified webhook listener for `order.completed`, `subscription.activated`, `return.created`, and more.
* Stored FastSpring orders inside WordPress (`FastSpring → Orders`) with invoice resend.
* Three pricing strategies: single custom price, per-product override, and FastSpring catalog pricing.
* Analytics dashboard and connection tester.
* Live + Sandbox mode with separate credentials, storefronts, and webhook secrets.

= Configuration =

1. In FastSpring App, create an API username and password under *Integrations > API Credentials*. Repeat for the test account.
2. Add a webhook in *Integrations > Webhooks* pointing to the URL shown on the plugin's Settings screen and copy the HMAC SHA256 secret.
3. Paste credentials, storefront ids and webhook secrets into *FastSpring > Settings* in WordPress, choose your active mode, and click *Test connection*.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/vms-elements-fastspring-payment-gateway`.
2. Activate the plugin in *Plugins > Installed Plugins*.
3. Visit *FastSpring > Settings* and enter your live and sandbox credentials.

== Privacy Policy ==

VMS Elements Payment Gateway with FastSpring for WooCommerce does not collect, store, or send any data to VMS Elements servers. Every outbound network call goes to FastSpring (Bright Market, LLC) — the payment processor you have chosen to integrate — and happens only after a site administrator has configured FastSpring API credentials in the plugin settings.

= Third-party services used by this plugin =

The plugin relies on FastSpring services to sell products and receive payment/subscription events. FastSpring is the payment processor and merchant of record; it is contacted whenever the plugin is configured with FastSpring credentials.

* **FastSpring API** — Sends server-side HTTPS requests to `https://api.fastspring.com` to test the connection, create checkout sessions, provision products, resend invoices, and confirm order status. Provided by Bright Market, LLC (FastSpring). [Terms of Service](https://fastspring.com/terms-use/) · [Privacy Policy](https://fastspring.com/privacy/).
* **FastSpring Store Builder Library** — Loads JavaScript from `https://sbl.onfastspring.com/` in the shopper's browser during checkout so the FastSpring popup overlay can render. Provided by Bright Market, LLC (FastSpring). [Terms of Service](https://fastspring.com/terms-use/) · [Privacy Policy](https://fastspring.com/privacy/).
* **FastSpring webhooks** — Receives inbound HTTPS POST requests from FastSpring when payment, subscription, or refund events occur. Each request is HMAC-SHA256 verified against the webhook secret you configure. Provided by Bright Market, LLC (FastSpring). [Terms of Service](https://fastspring.com/terms-use/) · [Privacy Policy](https://fastspring.com/privacy/).

= What data is sent =

For the FastSpring services the plugin sends ONLY:

* Your FastSpring API username and password (server-to-server HTTPS Basic Auth) when calling the FastSpring API.
* Cart line items (product paths, quantities, currency, and — when using the *single custom price* or *per-product override* pricing strategy — a price the merchant configured) to open a checkout session.
* Customer contact details the shopper enters into the FastSpring popup (email address, name, and billing/shipping fields required by FastSpring to complete the sale).
* Order and subscription IDs, plus WordPress order/reference tags, so FastSpring events can be matched back to WooCommerce orders.

The plugin does NOT send WordPress user accounts, IP addresses, or any data unrelated to the transaction to FastSpring.

= Data stored locally =

The plugin writes only to the WordPress site database. The following options and custom tables may be created:

* `vms_efpg_settings` — API credentials, storefront IDs, webhook secrets, and gateway settings.
* `{prefix}vms_efpg_orders` — FastSpring order records synced from webhooks (order ID, customer email, totals, currency, status, timestamps).
* `{prefix}vms_efpg_subscriptions` — Subscription state received from FastSpring webhook events.
* `{prefix}vms_efpg_events` — Raw webhook event log used to make webhook processing idempotent.
* `{prefix}vms_efpg_log` — Optional plugin diagnostics log (enable/disable in Settings).

You can choose to keep or delete this data on uninstall via the *Keep data on uninstall* setting.

= Cookies =

The plugin itself does not set any cookies. The FastSpring popup overlay (loaded from `sbl.onfastspring.com`) may set its own cookies as documented in FastSpring's [Privacy Policy](https://fastspring.com/privacy/).

= GDPR =

Because the plugin only communicates with FastSpring after you configure FastSpring API credentials, your site becomes a "data processor" toward FastSpring only after that opt-in. FastSpring acts as the merchant of record for transactions completed through the popup overlay. We recommend disclosing FastSpring as a payment processor in your site's own privacy policy.

== External services ==

This plugin connects to FastSpring services to process payments, sync order data, and load the checkout experience. Each service is contacted only after a site administrator has configured FastSpring API credentials in the plugin settings.

1. **FastSpring API** — Used to test API credentials, create checkout sessions, provision products, and confirm payments. Server-side HTTPS requests are sent to `https://api.fastspring.com` each time an admin saves credentials, a shopper opens the popup checkout, or a WooCommerce order is confirmed. Data sent: API credentials (Basic Auth), order totals, product paths, customer contact details supplied at checkout, and order metadata tags. Provided by Bright Market, LLC (FastSpring). [Terms of Service](https://fastspring.com/terms-use/) — [Privacy Policy](https://fastspring.com/privacy/).
2. **FastSpring Store Builder Library** — Used to render the FastSpring popup checkout on WooCommerce classic/block checkout and the checkout bridge page. JavaScript is loaded from `https://sbl.onfastspring.com/` in the shopper's browser. Data sent: storefront identifier, Store Builder access key, cart line items, and customer contact details required to open checkout. Provided by Bright Market, LLC (FastSpring). [Terms of Service](https://fastspring.com/terms-use/) — [Privacy Policy](https://fastspring.com/privacy/).
3. **FastSpring webhooks** — Used to receive payment, subscription, and refund events. FastSpring sends inbound HTTPS POST requests to your WordPress site; each is HMAC-SHA256 verified with the webhook secret you configure and stored locally. Data received: order IDs, customer email, payment status, subscription state, and refund events. Provided by Bright Market, LLC (FastSpring). [Terms of Service](https://fastspring.com/terms-use/) — [Privacy Policy](https://fastspring.com/privacy/).

== Frequently Asked Questions ==

= Does this plugin store sensitive payment data? =

No. All payment processing happens on FastSpring's PCI compliant infrastructure. The plugin only stores order and subscription metadata sent via webhooks.

= Can I use this without WooCommerce? =

Partially. The webhook listener, stored orders, settings, and API client work without WooCommerce. The WC payment gateway requires WooCommerce.

== Screenshots ==

1. Analytics dashboard with credential check and stored order summary.
2. Settings screen with live/sandbox mode toggle, API credentials, popup checkout path, and webhook configuration.
3. Stored FastSpring orders list in wp-admin.
4. WooCommerce checkout (classic or block) with FastSpring gateway selected.

== Changelog ==

= 1.0.0 =
* Initial WordPress.org release: WooCommerce classic + block checkout, popup overlay, webhooks, stored orders, analytics dashboard, and settings.
* All pricing strategies included (single custom price, per-product override, and FastSpring catalog pricing).
* Invoice resend on stored orders.
* Documented external services (FastSpring API, Store Builder Library, webhooks) in readme.
* Chart.js 4.5.1 bundled locally for the admin dashboard.
* Stricter REST permission checks for checkout overlay payment completion.
