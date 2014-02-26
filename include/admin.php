<?php
class WPDuplicate_Site_Admin {
   

    /**
     * Register hooks used on admin side by the plugin
     */
    public static function hooks() {
        
        // Network admin case
        if (is_network_admin()) {
            add_action( 'network_admin_menu', array( __CLASS__, 'network_menu_add_duplicate' ) );
        }
    }
		
    /**	
    * Add duplication option in menu
    */

    public static function network_menu_add_duplicate() {
        add_submenu_page( 'sites.php', WPDS_NETWORK_PAGE_DUPLICATE_TITLE, WPDS_NETWORK_MENU_DUPLICATE_TITLE, 'manage_sites', WPDS_SLUG_NETWORK_ACTION, array( __CLASS__, 'network_page_admin_duplicate_site' ) );
    }


    /**
     * Network Admin page - Duplicate
     */
    public static function network_page_admin_duplicate_site() {

        global $wpdb, $current_site, $current_user;

        // Capabilities test
        if( !current_user_can( 'manage_sites' ) ) {
            wp_die(WPDS_GAL_ERROR_CAPABILITIES);
        }

        // Getting Sites
        $site_list = WPDuplicate_Site_Admin::get_site_duplicate();

        // Init message
        $form_message = null;

        // Form Data
        $data = array(
            'source' => 0,
            'domain' => '',
            'title'  => '',
            'email'  => '',
        );

        // Manage Form Post
        if ( isset($_REQUEST['action']) && WPDS_SLUG_ACTION_DUPLICATE == $_REQUEST['action'] ) {

            // Merge $data / $_POST['site'] to get Posted data and fill form
            $data = array_merge($data, $_POST['site']);

            // Check referer and nonce
            check_admin_referer( 'wp-duplicate-site' );

            // format and check source
            $source_site_id = $data['source'];
            if ( empty( $source_site_id ) ) {
                wp_die( WPDS_NETWORK_PAGE_DUPLICATE_TITLE_ERROR_REQUIRE );
            }

            // format and check domain
            $domain = $data['domain'];
            if ( preg_match( '|^([a-zA-Z0-9-])+$|', $domain ) ) {
                $domain = strtolower( $domain );
            }

            // If not a subdomain install, make sure the domain isn't a reserved word
            if ( ! is_subdomain_install() ) {
                /** This filter is documented in wp-includes/ms-functions.php */
                $subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed' ) );
                if ( in_array( $domain, $subdirectory_reserved_names ) ) {
                    wp_die( sprintf( WPDS_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_RESERVED_WORDS , implode( '</code>, <code>', $subdirectory_reserved_names ) ) );
                }
            }
            if ( empty( $domain ) ) {
                wp_die( WPDS_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_REQUIRE );
            }
            if ( is_subdomain_install() ) {
                $newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
                $path      = $current_site->path;
            } else {
                $newdomain = $current_site->domain;
                $path      = $current_site->path . $domain . '/';
            }

            // format and check title
            $title = $data['title'];
            if ( empty( $title ) ) {
                wp_die( WPDS_NETWORK_PAGE_DUPLICATE_TITLE_ERROR_REQUIRE );
            }

            // format and check email admin
            $email = sanitize_email( $data['email'] );
            if ( empty( $email ) ) {
                wp_die( WPDS_NETWORK_PAGE_DUPLICATE_ADMIN_ERROR_REQUIRE );
            }
            if ( !is_email( $email ) ){
                wp_die( WPDS_NETWORK_PAGE_DUPLICATE_ADMIN_ERROR_REQUIRE );
            }
            
            // Create New site Admin if not exists
            $password = 'N/A';
            $user_id = email_exists($email);
            if ( !$user_id ) { // Create a new user with a random password
                $password = wp_generate_password( 12, false );
                $user_id = wpmu_create_user( $domain, $password, $email );
                if ( false == $user_id )
                    wp_die( WPDS_NETWORK_PAGE_DUPLICATE_ADMIN_ERROR_CREATE_USER );
                else
                    wp_new_user_notification( $user_id, $password );
            }

            // Create new site
            $wpdb->hide_errors();
            $new_site_id = wpmu_create_blog( $newdomain, $path, $title, $user_id , array( 'public' => 1 ), $current_site->id );
            $wpdb->show_errors();
            if ( !is_wp_error( $new_site_id ) ) {

                // User rights adjustments
                if ( !is_super_admin( $user_id ) && !get_user_option( 'primary_blog', $user_id ) ) {
                    update_user_option( $user_id, 'primary_blog', $new_site_id, true );
                }

                WPDuplicate_Site_Admin::bypass_server_limit ();

                // Copy Site - File
                WPDuplicate_Site_Admin::copy_file( $source_site_id, $new_site_id );

                // Copy Site - Data
                WPDuplicate_Site_Admin::copy_data($source_site_id, $new_site_id);

                // mail to user
                $content_mail = sprintf( WPDS_EMAIL_CREATE_SITE_CONTENT, $current_user->user_login , get_site_url( $new_site_id ), wp_unslash( $title ) );
                $subject_mail = sprintf( WPDS_EMAIL_CREATE_SITE_SUBJECT, $current_site->site_name );
                $form_message = WPDS_NETWORK_PAGE_DUPLICATE_NOTICE_CREATED;
                wp_mail( get_site_option('admin_email'),$subject_mail, $content_mail, 'From: "' . WPDS_EMAIL_FROM . '" <' . get_site_option( 'admin_email' ) . '>' );

            } else {
                wp_die( $new_site_id->get_error_message() );
            }
            
        }

        // Load template if at least one Site is available
        if( $site_list ) {
            $nonce_string = WPDS_SLUG_NETWORK_ACTION;
            require_once WPDS_COMPLETE_PATH . '/template/network_admin_duplicate_site.php';
        }
        else {
            wp_die(WPDS_GAL_ERROR_NO_SITE);
        }    
    }

    /**
     * Copy Site File
     */
    public static function copy_file( $from_site_id, $to_site_id ) {

        // Switch to Source site and get uploads info
        switch_to_blog($from_site_id);
        $wp_upload_info = wp_upload_dir();
        $from_dir = str_replace(' ', "\\ ", trailingslashit($wp_upload_info['basedir']));

        // Switch to Destination site and get uploads info
        switch_to_blog($to_site_id);
        $wp_upload_info = wp_upload_dir();
        $to_dir = str_replace(' ', "\\ ", trailingslashit($wp_upload_info['basedir']));
        restore_current_blog();

        WPDuplicate_Site_Admin::recurse_copy($from_dir,$to_dir);
    }

    /**
     * Copy Site Data
     */
    public static function copy_data( $from_site_id, $to_site_id ) {
        
        // Copy table
        WPDuplicate_Site_Admin::db_copy_table( $from_site_id, $to_site_id );

        // Update data
        WPDuplicate_Site_Admin::db_update_table( $from_site_id, $to_site_id );
    }

    /**
     * Copy Table
     */
    public static function db_copy_table( $from_blog_id, $to_blog_id ) {
        global $wpdb ;

        // Source Site information
        $from_site_prefix = $wpdb->get_blog_prefix( $from_site_id );                    // prefix 
        $from_site_prefix_length = strlen($from_site_prefix);                           // prefix length
        $from_site_escaped_prefix = str_replace( '_', '\_', $from_site_prefix );        // prefix escapedee

        // Destination Site information
        $to_site_prefix = $wpdb->get_blog_prefix( $to_site_id );                        // prefix
        $to_site_prefix_length = strlen($to_blog_prefix);                               // prefix length

        // Get sources Tables
        $query = $wpdb->prepare('SHOW TABLES LIKE %s',$from_blog_escaped_prefix.'%');
        $from_site_table = $wpdb->get_col($query);

        foreach ($from_site_table as $table) {

            $table_name = $to_blog_prefix . substr( $table, $from_blog_prefix_length );

            // Drop table if exists
            $query = $wpdb->prepare('DROP TABLE IF EXISTS `%s', $table_name);
            $wpdb->get_results($query);

            // Create new table from source table
            $query = $wpdb->prepare('CREATE TABLE `%s` LIKE `%s`', $table_name, $table);
            $wpdb->get_results($query);

            // Populate database with data from source table
            $query = $wpdb->prepare('INSERT `%s` SELECT * FROM `%s`', $table_name, $table);
            $wpdb->get_results($query);

        }
    }


    /**
     * Update data
     */
    public static function db_update_data( $from_blog_id, $to_blog_id ) {
        global $wpdb ;

        
    }

    /**
     * Recurse_copy using default Wordpress class WP_Filesystem_Direct
     */
    public static function recurse_copy($from_dir,$to_dir) { 

        global $wp_filesystem;
var_dump($wp_filesystem);
        // Open source directory
        $dir = opendir($from_dir);

        // Create base directory
        $wp_filesystem->mkdir($to_dir);

        // Walk through Source Directory
        while(false !== ( $file = readdir($dir)) ) {

            // squeeze . and ..
            if (( $file != '.' ) && ( $file != '..' )) {

                // Directory case
                if ( is_dir($from_dir . '/' . $file) ) { 
                    recurse_copy($from_dir . '/' . $file,$to_dir . '/' . $file);
                } 
                // File Case
                else { 
                    $wp_filesystem->copy($from_dir . '/' . $file,$to_dir . '/' . $file); 
                } 
            } 
        }

        // Close directory
        closedir($dir);

    }

    /**
     * Get site could be duplicate
     */
    public static function get_site_duplicate() {
        global $wpdb;

        // Request to get duplicated sites (return array format)
        $query = 'SELECT * FROM ' . $wpdb->blogs . ' WHERE blog_id <> ' . WPDS_SITE_DUPLICATION_EXCLUDE ;
        $site_results = $wpdb->get_results( $query, ARRAY_A );
        
        return $site_results;
    }

    /**
     * Bypass limit server if possible
     */
    public static function bypass_server_limit() {
        @ini_set('memory_limit','1024M');
        @ini_set('max_execution_time','0');
    }

}