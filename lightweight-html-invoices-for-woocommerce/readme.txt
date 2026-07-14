=== Lightweight HTML Invoices & Packing Slips for WooCommerce ===
Contributors: murali-sugumar
Tags: woocommerce, invoice, packing slip, print, html
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Secure, responsive HTML invoices and packing slips that use the browser's native print dialog. No server-side PDFs are created.

== Description ==

Lightweight HTML Invoices & Packing Slips gives WooCommerce stores a simple way to prepare invoices and packing slips without installing a PDF engine. Documents are rendered as secure HTML pages, and printing is handled by the browser you already use.

Administrators can open individual documents or prepare several orders in one printable view. Eligible signed-in customers can also access their own invoices from My Account when customer access is enabled.

Features:

* Invoice and packing-slip layouts for A4, A5, and 6 × 4 inch paper.
* Independent portrait or landscape orientation.
* HPOS-compatible WooCommerce CRUD access.
* Optional sequential invoice numbers stored as order metadata.
* Admin, My Account, order-screen, and bulk actions.
* Theme overrides in `your-theme/lightweight-html-invoices/`.
* No PDF engine, temporary PDF, remote font, tracking, telemetry, or external service.

The plugin does not generate or attach PDFs to email. Use Print or Save as PDF in the browser. Output can vary slightly between browser print engines.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install its ZIP in Plugins > Add New.
2. Activate WooCommerce, then activate this plugin.
3. Open WooCommerce > Invoices & Packing Slips and review the settings.
4. Open a WooCommerce order and choose a document action.

== Frequently Asked Questions ==

= Are PDF files created on the server? =

No. Documents are HTML. The browser's native print dialog handles printing or Save as PDF.

= Are PDF email attachments supported? =

No. The plugin deliberately has no PDF generator.

= Can guests access invoices? =

No. Version 1 requires a signed-in customer whose user ID, order key, status, and nonce all match the order. Administrators can still print guest orders.

= Can templates be overridden? =

Copy an allowlisted template from `templates/` to `your-theme/lightweight-html-invoices/`. Keep all output properly escaped.

= Which extension hooks are available? =

Filters include `lhiwc_store_logo_url`, `lhiwc_invoice_number`, `lhiwc_document_title`, `lhiwc_customer_order_statuses`, and `lhiwc_bulk_print_maximum`. Actions include `lhiwc_before_document`, `lhiwc_after_document`, `lhiwc_before_product_table`, and `lhiwc_after_product_table`.

== Screenshots ==

1. Responsive HTML invoice preview and print controls.
2. Compact packing slip layout.
3. WooCommerce settings tab.
4. Orders list document actions and bulk printing.

== Privacy ==

The plugin processes existing WooCommerce order information locally. It does not transmit customer or document data to third parties, create server-side PDF files, add analytics, or add tracking. Logo and product images use local WordPress media URLs.

== Credits ==

Created and maintained by Murali Sugumar.

Website: https://muralisugumar.com/

== Changelog ==

= 1.0.4 =
* Added packing-slip options for customer mobile number and product weight.
* Customer mobile number is shown on packing slips by default.

= 1.0.3 =
* Added WordPress.org and GitHub documentation.
* Added author credit and website information.

= 1.0.2 =
* Open invoice and packing-slip links in a new browser tab.
* Remove the unnecessary Close button from document previews.

= 1.0.1 =
* Replaced the fragile WooCommerce settings-tab integration with a dedicated WooCommerce submenu.
* Added native Settings API validation, settings-page-only assets, privacy response headers, and safer store-state handling.

= 1.0.0 =
* Initial release with secure HTML invoices, packing slips, bulk printing, customer access controls, HPOS compatibility, and responsive print layouts.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Development and testing ==

Run PHP syntax checks:
`find . -name "*.php" -print0 | xargs -0 -n1 php -l`

Install WordPress Coding Standards in a development environment, configure PHPCS, and run:
`phpcs --standard=WordPress lightweight-html-invoices-for-woocommerce`

Run the official Plugin Check plugin against a staging installation. Test all six paper/orientation combinations for both documents in current Chrome, Edge, Firefox, and Safari; HPOS and legacy order storage; taxes, discounts, fees, refunds, variations, deleted products, long orders, RTL, customer ownership, nonce and capability failures, bulk limits, guest admin orders, and WooCommerce deactivation.

Create a release ZIP from the parent directory:
`zip -r lightweight-html-invoices-for-woocommerce.zip lightweight-html-invoices-for-woocommerce -x "*/.DS_Store"`

On multisite, uninstall cleanup applies only to the site on which uninstall runs; network administrators should repeat cleanup per site when removal was enabled.
