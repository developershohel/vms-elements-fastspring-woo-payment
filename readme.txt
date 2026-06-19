=== VMS Elements Fastspring Woo Payment ===
Contributors: vmsuniverse
Tags: woocommerce, fastspring, payments, subscriptions, dashboard
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate FastSpring as a WooCommerce payment processor and unlock an advanced analytics dashboard for sales, subscriptions, refunds and customers.

== Description ==

VMS Elements Fastspring Woo Payment connects WooCommerce to FastSpring's hosted checkout and subscription engine, then layers a polished analytics dashboard inside `wp-admin` so you can see how your business is doing without ever leaving WordPress.

= Highlights =

* WooCommerce payment gateway powered by FastSpring sessions (cards, PayPal, Apple Pay, Google Pay, more).
* HMAC-verified webhook listener for `order.completed`, `subscription.activated`, `return.created`, and many more.
* Optional one-way product sync: WooCommerce -> FastSpring on save.
* Advanced dashboard: revenue trend, subscription breakdown, MRR, top products, top countries, recent orders.
* Dedicated screens for FastSpring Orders and Subscriptions with sync/cancel actions.
* Full Live + Sandbox isolation: separate API credentials, storefronts, and webhook secrets.
* Refunds via the FastSpring Returns API directly from the WooCommerce order screen.
* Built-in event log and connection tester for fast debugging.

= Configuration =

1. In FastSpring App, create an API username and password under *Integrations > API Credentials*. Repeat for the test account.
2. Add a webhook in *Integrations > Webhooks* pointing to the URL shown on the plugin's Settings screen and copy the HMAC SHA256 secret.
3. Paste credentials, storefront ids and webhook secrets into *FastSpring > Settings* in WordPress, choose your active mode, and click *Test connection*.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/vms-elements-fastspring-woo-payment`.
2. Activate the plugin in *Plugins > Installed Plugins*.
3. Visit *FastSpring > Settings* and enter your live and sandbox credentials.

== Frequently Asked Questions ==

= Does this plugin store sensitive payment data? =

No. All payment processing happens on FastSpring's PCI compliant infrastructure. The plugin only stores order and subscription metadata sent via webhooks.

= Can I use this without WooCommerce? =

Yes. The dashboard, settings, webhook listener and API client all work without WooCommerce. Only the WC payment gateway requires it.

== Screenshots ==

1. Analytics dashboard with revenue KPIs, trend charts, top products and recent orders.
2. Settings screen with live/sandbox mode toggle, API credentials and webhook configuration.
3. FastSpring orders list with search, filters and sync actions.
4. Subscriptions management with status badges, MRR and cancel actions.
5. WooCommerce checkout with FastSpring as the selected payment method.

== Changelog ==

= 1.0.6 =
* Current stable release.

= 1.0.0 =
* Initial release.
