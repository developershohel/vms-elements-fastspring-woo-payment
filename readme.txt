=== VMS Elements Fastspring Woo Payment ===
Contributors: vmsuniverse
Tags: woocommerce, fastspring, payments, subscriptions, ecommerce
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WooCommerce to FastSpring checkout (classic + blocks), webhooks, and stored orders. Pro adds full catalog and subscription management.

== Description ==

VMS Elements Fastspring Woo Payment is the **free** WordPress plugin that connects WooCommerce to FastSpring checkout and subscription webhooks.

= Free features =

* WooCommerce payment gateway — **classic and block checkout** with FastSpring popup overlay.
* HMAC-verified webhook listener for `order.completed`, `subscription.activated`, `return.created`, and more.
* Stored FastSpring orders inside WordPress (`FastSpring → Orders`).
* Analytics dashboard and connection tester.
* Live + Sandbox mode with separate credentials, storefronts, and webhook secrets.

= Pro add-on =

Advanced **management** features — catalog/subscription admin, payment links, shortcodes, product sync, invoices, coupons, reports, and more — are available in the separate **VMS Elements Fastspring Woo Payment Pro** add-on from [VMS Elements](https://vmselements.com/product/vms-elements-fastspring-woo-payment-pro). Pro code is not bundled in this free plugin.

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

Partially. The webhook listener, stored orders, settings, and API client work without WooCommerce. The WC payment gateway requires WooCommerce.

= Where are catalog tools and subscription management? =

Those management features require the separate Pro add-on from VMS Elements. The free plugin includes WooCommerce checkout (classic and block) and core webhook integration.

== Screenshots ==

1. Analytics dashboard with credential check and stored order summary.
2. Settings screen with live/sandbox mode toggle, API credentials, popup checkout path, and webhook configuration.
3. Stored FastSpring orders list in wp-admin.
4. WooCommerce checkout (classic or block) with FastSpring gateway selected.

== Changelog ==

= 1.0.0 =
* Free core: WooCommerce classic + block checkout, popup overlay, webhooks, stored orders, analytics dashboard, and settings.
* Pro add-on: full FastSpring management (catalog, subscriptions, payment links, shortcodes, and more).

= 1.0.7 =
* Split Pro features into a separate add-on plugin; free build ships core gateway, webhooks, and stored orders only.

= 1.0.6 =
* Previous stable release.

= 1.0.0 =
* Initial release.
