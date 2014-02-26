<?php
/**
 * Plugin Name:         Duplicate Website
 * Plugin URI:          
 * Description:         Duplicate website from existing website
 * Author:              Julien OGER, GLOBALIS media systems
 * Author URI:          http://www.globalis-ms.com
 *
 * Version:             0.1.0
 * Requires at least:   3.7.0
 * Tested up to:        3.7
 */

// Block direct requests
if ( !defined('ABSPATH') )
    die('-1');

if( !class_exists( 'WPDuplicate_Site' ) ) {
    // Load configuration
    require_once realpath( dirname( __FILE__ ) ) . '/include/config.php';
    require_once WPDS_COMPLETE_PATH . '/include/option.php';

    // Load textdomain
    load_plugin_textdomain( WPDS_DOMAIN, NULL, WPDS_PATH . '/language/' );

    // Load language
    require_once WPDS_COMPLETE_PATH . '/include/lang.php';

    if( is_admin() ) {
        require_once WPDS_COMPLETE_PATH . '/include/admin.php';
        WPDuplicate_Site_Admin::hooks();
    }

    /**
     * Main class of the plugin
     */
    class WPDuplicate_Site {
        /**
         * Register hooks used by the plugin
         */
        public static function hooks() {
            // Register (de)activation hook
            register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
            register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
            register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

            add_action( 'init', array( __CLASS__, 'init' ) );
        }

        /**
         * What to do on plugin activation
         */
        public static function activate() {
            // Nothing for now.
        }

        /**
         * What to do on plugin deactivation
         */
        public static function deactivate() {
            // Nothing for now.
        }

        /**
         * What to do on plugin uninstallation
         */
        public static function uninstall() {
            // Nothing for now.
        }

        /**
         * Plugin init: create 'duplicata' status
         */
        public static function init() {
             // Nothing for now.
        }
        
    }

    WPDuplicate_Site::hooks();
}