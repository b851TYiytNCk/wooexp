<?php

/**
 *      * @wordpress-plugin
 *      * Plugin Name:       WooCommerce Order Export
 *      * Version:           1.0.1
 *      * Description:       Export your WooCommerce order products in a PDF file
 *      * Author:            Vladyslav Nahornyi
 *      * Author URI:        https://github.com/b851TYiytNCk
 *      * Update URI:        https://github.com/b851TYiytNCk/wooexp
 *      * License:           GPL-3.0+
 *      * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

defined('ABSPATH') || exit;

/**
 * @class Main plugin class
 */

final class WooOrderExport {
    /**
     * Class instance
     *
     * @var WooOrderExport
     */
    private static $instance = null;

    /**
     * Retrieve main LabelTraxx instance.
     *
     * Ensure only one instance is loaded or can be loaded.
     */
    public static function get() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof WooOrderExport ) ) {
            self::$instance = new WooOrderExport();
            self::$instance->define_constants();
            self::$instance->init();
        }

        return self::$instance;
    }

    private function define_constants() {
        define( 'WOOEXP_DIR', __DIR__ );
    }

    private function init() {
		add_action( 'admin_head', array( $this, 'add_order_export' ) );
    }

    public function add_order_export() {
		if ( 'shop_order' === get_post_type() ) {
			require WOOEXP_DIR . '/layout/layout.php';

			if ( function_exists( 'get_order_export_layout' ) ) {
				get_order_export_layout();
			}
		}
    }
}

/**
 * Helper function to retrieve class instance
 * Used to avoid global variable usage
 *
 * @return WooOrderExport
 */
function woo_export() {
    return WooOrderExport::get();
}

// Initialize the plugin
if ( is_admin() ) {
    woo_export();
}