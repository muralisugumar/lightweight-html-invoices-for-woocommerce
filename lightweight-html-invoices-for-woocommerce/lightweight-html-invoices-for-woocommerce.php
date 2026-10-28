<?php
/**
 * Plugin Name: Lightweight HTML Invoices & Packing Slips for WooCommerce
 * Description: Secure, printable HTML invoices and packing slips without server-side PDF generation.
 * Version: 1.0.5
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: Murali Sugumar
 * Author URI: https://muralisugumar.com/
 * Text Domain: lightweight-html-invoices-for-woocommerce
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'LHIWC_VERSION', '1.0.5' );
define( 'LHIWC_FILE', __FILE__ );
define( 'LHIWC_PATH', plugin_dir_path( __FILE__ ) );
define( 'LHIWC_URL', plugin_dir_url( __FILE__ ) );

require_once LHIWC_PATH . 'includes/class-lhiwc-settings.php';
require_once LHIWC_PATH . 'includes/class-lhiwc-numbering.php';
require_once LHIWC_PATH . 'includes/class-lhiwc-documents.php';
require_once LHIWC_PATH . 'includes/class-lhiwc-access.php';
require_once LHIWC_PATH . 'includes/class-lhiwc-plugin.php';

add_action( 'before_woocommerce_init', array( 'LHIWC_Plugin', 'declare_compatibility' ) );
add_action( 'plugins_loaded', array( 'LHIWC_Plugin', 'instance' ) );
