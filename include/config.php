<?php
/**
 * Remember plugin path & URL
 */
define( 'WPDS_PATH', plugin_basename( realpath( dirname( __FILE__ ).'/..') ) );
define( 'WPDS_COMPLETE_PATH', WP_PLUGIN_DIR.'/'.WPDS_PATH );
define( 'WPDS_URL', WP_PLUGIN_URL.'/'.WPDS_PATH );


/**
 * Domaine
 */
define( 'WPDS_DOMAIN', 'wp-duplicate-site' );


/**
 * Slugs
 */

define( 'WPDS_SLUG_NETWORK_ACTION', 'wp-duplicate-site' );
define( 'WPDS_SLUG_ACTION_DUPLICATE', 'duplicate-site' );

/**
 * Filters
 */
define( 'WPDS_FILTERS_OPTION', 'wpds-define_options' );

/**
 * Others confs
 */
// Site to excude
define( 'WPDS_SITE_DUPLICATION_EXCLUDE', '1' );
