<?php

use Lbtx\Installer;

/**
 *      * @wordpress-plugin
 *      * Plugin Name:       WooCommerce Order Export
 *      * Version:           1.0.0
 *      * Description:       Export your WooCommerce order products in a PDF file
 *      * Author:            Vladyslav Nahornyi | Pryvus
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
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init() {
        add_action( "add_meta_boxes_shop_order", array( $this, "add_order_export" ) );
    }

    public function add_order_export($type) {
        add_meta_box(
            'wooexp',
            '',
            array( $this, add_order_export_layout ),
            $type,
        );
    }

    /**
     * @return void
     */
    public function add_order_export_layout() {
        require WP_PLUGIN_DIR . '/layout/layout.php';

        if ( function_exists( 'get_order_export_layout' ) ) {
            get_order_export_layout();
        } else {
            return wp_error(0)
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