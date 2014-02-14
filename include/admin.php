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
		
		
		add_action( 'log', array(__CLASS__, 'logger'), '10', 2  );
    }
	
	/**	
    * Logging action for debugging 
    */
	public static function logger($a, $b){
		
		// prevoir un fichier log
		//print_r($a);
		//print_r($b);
		//echo "________________________________<br/><br/><br/>";
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
        $site_list = WPDuplicate_Site_Admin::wp_get_sites();

        // Init message
        $msg = null;

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

            // format and check title
            $from_site_id = $data['source'];
            if ( empty( $from_site_id ) ) {
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
            $to_site_id = wpmu_create_blog( $newdomain, $path, $title, $user_id , array( 'public' => 1 ), $current_site->id );
            $wpdb->show_errors();
            if ( !is_wp_error( $to_site_id ) ) {

                // User rights adjustments
                if ( !is_super_admin( $user_id ) && !get_user_option( 'primary_blog', $user_id ) )
                    update_user_option( $user_id, 'primary_blog', $to_site_id, true );

                // Copy Site - Data
                WPDuplicate_Site_Admin::copy_blog_data($from_site_id, $to_site_id);
                WPDuplicate_Site_Admin::copy_blog_files( $from_site_id, $to_site_id );
				echo $from_site_id . ' - ' . $to_site_id ;
                WPDuplicate_Site_Admin::replace_content_urls( $from_site_id, $to_site_id );

                // mail to user
                $content_mail = sprintf( WPDS_EMAIL_CREATE_SITE_CONTENT, $current_user->user_login , get_site_url( $to_site_id ), wp_unslash( $title ) );
                $subject_mail = sprintf( WPDS_EMAIL_CREATE_SITE_SUBJECT, $current_site->site_name );
                $msg = WPDS_NETWORK_PAGE_DUPLICATE_NOTICE_CREATED;
                wp_mail( get_site_option('admin_email'),$subject_mail, $content_mail, 'From: "' . WPDS_EMAIL_FROM . '" <' . get_site_option( 'admin_email' ) . '>' );
                do_action( 'log', __( 'Copy Complete!', WPDS_DOMAIN ), WPDS_DOMAIN, 
                    sprintf(__( 'Copied: %s in %s seconds', WPDS_DOMAIN ),'<a href="http://' . $newdomain . '" target="_blank">' . $title . '</a>', number_format_i18n(timer_stop())));

            } else {
                wp_die( $to_site_id->get_error_message() );
            }
            
        }

        // Load template
        if( $site_list ) {

            $nonce_string = WPDS_SLUG_NETWORK_ACTION;
            require_once WPDS_COMPLETE_PATH . '/template/network_admin_duplicate_site.php';
        }
        else {
            wp_die(WPDS_GAL_ERROR_NO_SITE);
        }    
    }

    /**
     * Copy site data from one blog to another
     *
     * @param int $from_blog_id ID of the blog being copied from.
     * @param int $to_blog_id ID of the blog being copied to.
     */
    public static function copy_blog_data( $from_blog_id, $to_blog_id ) {
        global $wpdb, $wp_version;
        if( $from_blog_id ) {
            $from_blog_prefix = WPDuplicate_Site_Admin::get_blog_prefix( $from_blog_id );
            $to_blog_prefix = WPDuplicate_Site_Admin::get_blog_prefix( $to_blog_id );
            $from_blog_prefix_length = strlen($from_blog_prefix);
            $to_blog_prefix_length = strlen($to_blog_prefix);
            $from_blog_escaped_prefix = str_replace( '_', '\_', $from_blog_prefix );

            // Grab key options from new blog.
            $saved_options = array(
                'siteurl'=>'',
                'home'=>'',
                'upload_path'=>'',
                'fileupload_url'=>'',
                'upload_url_path'=>'',
                'admin_email'=>'',
                'blogname'=>''
            );
            // Options that should be preserved in the new blog.
            $saved_options = apply_filters('copy_blog_data_saved_options', $saved_options);
            foreach($saved_options as $option_name => $option_value) {
                $saved_options[$option_name] = get_blog_option( $to_blog_id, $option_name );
            }

            // Copy over ALL the tables.
            $query = $wpdb->prepare('SHOW TABLES LIKE %s',$from_blog_escaped_prefix.'%');
            do_action( 'log', $query, WPDS_DOMAIN);
            $old_tables = $wpdb->get_col($query);

            foreach ($old_tables as $k => $table) {
                $raw_table_name = substr( $table, $from_blog_prefix_length );
                $newtable = $to_blog_prefix . $raw_table_name;

                $query = "DROP TABLE IF EXISTS {$newtable}";
                do_action( 'log', $query, WPDS_DOMAIN);
                $wpdb->get_results($query);

                $query = "CREATE TABLE IF NOT EXISTS {$newtable} LIKE {$table}";
                do_action( 'log', $query, WPDS_DOMAIN);
                $wpdb->get_results($query);

                $query = "INSERT {$newtable} SELECT * FROM {$table}";
                do_action( 'log', $query, WPDS_DOMAIN);
                $wpdb->get_results($query);
            }

            // apply key opptions from new blog.
            switch_to_blog( $to_blog_id );
            foreach( $saved_options as $option_name => $option_value ) {
                update_option( $option_name, $option_value );
            }

            /// fix all options with the wrong prefix...
            $query = $wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s",$from_blog_escaped_prefix.'%');
            $options = $wpdb->get_results( $query );
            do_action( 'log', $query, WPDS_DOMAIN, count($options).' results found.');
            if( $options ) {
                foreach( $options as $option ) {
                    $raw_option_name = substr($option->option_name,$from_blog_prefix_length);
                    $wpdb->update( $wpdb->options, array( 'option_name' => $to_blog_prefix . $raw_option_name ), array( 'option_id' => $option->option_id ) );
                }
                wp_cache_flush();
            }

            // Fix GUIDs on copied posts
            WPDuplicate_Site_Admin::replace_guid_urls( $from_blog_id, $to_blog_id );

            restore_current_blog();
        }
    }

    /**
     * Copy files from one blog to another.
     *
     * @param int $from_blog_id ID of the blog being copied from.
     * @param int $to_blog_id ID of the blog being copied to.
     */
    public static function copy_blog_files( $from_blog_id, $to_blog_id ) {
        set_time_limit( 2400 ); // 60 seconds x 10 minutes
        @ini_set('memory_limit','2048M');

        // Path to source blog files.
        switch_to_blog($from_blog_id);
        $dir_info = wp_upload_dir();
        $from = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']).'*'); // * necessary with GNU cp, doesn't hurt anything with BSD cp
        restore_current_blog();
        $from = apply_filters('copy_blog_files_from', $from, $from_blog_id);

        // Path to destination blog files.
        switch_to_blog($to_blog_id);
        $dir_info = wp_upload_dir();
        $to = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']));
        restore_current_blog();
        $to = apply_filters('copy_blog_files_to', $to, $to_blog_id);

        // Shell command used to copy files.
        $command = apply_filters('copy_blog_files_command', sprintf("cp -Rfp %s %s", $from, $to), $from, $to );
        exec($command);
    }

    /**
     * Replace URLs in post content and image src
     *
     * @param int $from_blog_id ID of the blog being copied from.
     * @param int $to_blog_id ID of the blog being copied to.
     */
    public static function replace_content_urls( $from_blog_id, $to_blog_id ) {
        global $wpdb;
        $to_blog_prefix = WPDuplicate_Site_Admin::get_blog_prefix( $to_blog_id );
        $from_blog_url = get_blog_option( $from_blog_id, 'siteurl' );
        $to_blog_url = get_blog_option( $to_blog_id, 'siteurl' );
        $query = $wpdb->prepare( "UPDATE {$to_blog_prefix}posts SET post_content = REPLACE(post_content, '%s', '%s')", $from_blog_url, $to_blog_url );
        do_action( 'log', $query, WPDS_DOMAIN);
		
		// Recherche des repertoires uploads associé a chaque blog
		switch_to_blog($from_blog_id);
		$dir = wp_upload_dir();
		$from_upload_url = str_replace(network_site_url(), get_bloginfo('url').'/',$dir['baseurl']);
		
		switch_to_blog($to_blog_id);
		$dir = wp_upload_dir();
		$to_upload_url = str_replace(network_site_url(), get_bloginfo('url').'/', $dir['baseurl']);
		
		$query = $wpdb->prepare( "UPDATE {$to_blog_prefix}posts SET post_content = REPLACE(post_content, '%s', '%s')", $from_upload_url, $to_upload_url );
        $wpdb->query( $query );
		do_action( 'log', $query, WPDS_DOMAIN);
		
		do_action( WPDS_DOMAIN.'_update_db', $from_blog_id, $to_blog_id);
    }

    /**
     * Replace URLs in post GUIDs
     *
     * @param int $from_blog_id ID of the blog being copied from.
     * @param int $to_blog_id ID of the blog being copied to.
     */
    public static function replace_guid_urls( $from_blog_id, $to_blog_id ) {
        global $wpdb;
        $to_blog_prefix = WPDuplicate_Site_Admin::get_blog_prefix( $to_blog_id );
        $from_blog_url = get_blog_option( $from_blog_id, 'siteurl' );
        $to_blog_url = get_blog_option( $to_blog_id, 'siteurl' );
        $query = $wpdb->prepare( "UPDATE {$to_blog_prefix}posts SET guid = REPLACE(guid, '%s', '%s')", $from_blog_url, $to_blog_url );
        do_action( 'log', $query, WPDS_DOMAIN);
        $wpdb->query( $query );
    }

    /**
     * Get the database prefix for a blog
     *
     * @param int $blog_id ID of the blog.
     * @return string prefix
     */
    public static function get_blog_prefix( $blog_id ) {
        global $wpdb;
        if( is_callable( array( &$wpdb, 'get_blog_prefix' ) ) ) {
            $prefix = $wpdb->get_blog_prefix( $blog_id );
        } else {
            $prefix = $wpdb->base_prefix . $blog_id . '_';
        }
        return $prefix;
    }

    /**
     * Get sit could be duplicate
     */
    public static function wp_get_sites() {
        global $wpdb;

        $query = 'SELECT * FROM ' . $wpdb->blogs . ' WHERE blog_id <> ' . WPDS_SITE_DUPLICATION_EXCLUDE ;

        $site_results = $wpdb->get_results( $query, ARRAY_A );

        return $site_results;
    }

}