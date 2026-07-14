# Lightweight HTML Invoices & Packing Slips for WooCommerce

A focused WooCommerce plugin for creating clean invoices and packing slips as secure HTML pages.

It does not generate PDFs on the server. When you are ready to print, the plugin opens your browser's normal print dialog. From there you can print to a physical printer or use the browser's Save as PDF option.

## Why this plugin exists

Many invoice plugins include large PDF libraries, write temporary files to the server, or depend on an external service. This plugin takes a simpler approach. WooCommerce already stores the order information, and modern browsers already know how to print a well-designed page.

The result is a small plugin with fewer moving parts and no server-side PDF files to manage.

## Features

- HTML invoices and packing slips
- A4, A5, and 6 by 4 inch paper sizes
- Portrait and landscape orientation
- Compact layouts designed for smaller paper
- Individual and bulk printing
- WooCommerce HPOS support
- Admin order actions
- Customer My Account actions with access controls
- Optional sequential invoice numbering
- Store logo and address from existing WordPress and WooCommerce settings
- Product variations, SKUs, taxes, shipping, fees, discounts, and refunds
- Optional product weight and customer mobile number on packing slips
- Theme template overrides
- Local CSS and JavaScript only
- No telemetry, tracking, remote fonts, or external API calls

## Requirements

- WordPress 6.5 or newer
- PHP 7.4 or newer
- WooCommerce

## Installation

1. Download or build the plugin ZIP.
2. In WordPress, go to Plugins, then Add New Plugin, then Upload Plugin.
3. Select the ZIP file and activate the plugin.
4. Go to WooCommerce, then Invoices & Packing Slips.
5. Review the invoice, packing slip, customer access, and printing settings.

## Using the plugin

Open the WooCommerce Orders screen and use the invoice or packing-slip action for an order. The document opens in a new browser tab.

Use the Print button to open the browser print dialog. The exact printer settings and Save as PDF options depend on your browser, operating system, and printer driver.

For several orders, select them in the WooCommerce Orders list and choose Print invoices or Print packing slips from the bulk actions menu.

## Customer access

Signed-in customers can view eligible documents for orders that belong to their account. Access checks include the current user, order ownership, order key, order status, and a request nonce.

Guest frontend document access is not included. Store administrators can still print documents for guest orders.

Packing-slip access for customers is disabled by default.

## Template overrides

Templates can be copied from the plugin's `templates` directory to:

```text
your-theme/lightweight-html-invoices/
```

Keep the original filenames. Theme overrides are useful when a store needs document-specific markup changes. Any custom output should remain properly escaped.

## Developer hooks

Available filters include:

- `lhiwc_store_logo_url`
- `lhiwc_invoice_number`
- `lhiwc_document_title`
- `lhiwc_customer_order_statuses`
- `lhiwc_bulk_print_maximum`

Available actions include:

- `lhiwc_before_document`
- `lhiwc_after_document`
- `lhiwc_before_product_table`
- `lhiwc_after_product_table`

## Privacy

Order information is processed locally using existing WooCommerce data. The plugin does not send customer information to third parties, create public invoice files, load tracking scripts, or collect telemetry.

## Development checks

Check PHP syntax:

```bash
find . -name "*.php" -print0 | xargs -0 -n1 php -l
```

Run WordPress Coding Standards after installing PHPCS and the WordPress ruleset:

```bash
phpcs --standard=WordPress lightweight-html-invoices-for-woocommerce
```

The plugin should also be checked with the official WordPress Plugin Check plugin and tested with HPOS enabled and disabled in a development store.

## License

Licensed under GPLv2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

## Credits

Created and maintained by [Murali Sugumar](https://muralisugumar.com/).
