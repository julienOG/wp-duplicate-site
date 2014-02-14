<?php

/** 
 * General
 */
// ERROR
define( 'WPDS_GAL_ERROR_CAPABILITIES', __( 'Sorry, you don\'t have permissions to use this page.', WPDS_DOMAIN ) );
define( 'WPDS_GAL_ERROR_NO_SITE', __( 'Sorry, you have to create at least one site before using this module', WPDS_DOMAIN ) );

/** 
 * Menu
 */
define( 'WPDS_NETWORK_MENU_DUPLICATE_TITLE', __( 'Duplicate', WPDS_DOMAIN ) );

/** 
 * Admin Page Duplicate
 */
define( 'WPDS_NETWORK_PAGE_DUPLICATE_TITLE', __( 'Duplicate Site', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_FIELD_SOURCE', __( 'Source Site to copy', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS', __( 'New Site - Address', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS_INFO', __( 'Only lowercase letters (a-z) and numbers are allowed.', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_FIELD_TITLE', __( 'New Site - Title', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADMIN', __( 'New Site - Admin Email', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_BUTTON_COPY', __( 'Copy Site', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_RESERVED_WORDS', __( 'The following words are reserved for use by WordPress functions and cannot be used as blog names: <code>%s</code>', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_DOMAIN_ERROR_REQUIRE', __( 'Missing or invalid site address.', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_TITLE_ERROR_REQUIRE', __( 'Missing title.', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_ADMIN_ERROR_REQUIRE', __( 'Missing Admin Email.', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_ADMIN_ERROR_FORMAT', __( 'Invalid email address.', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_ADMIN_ERROR_CREATE_USER', __( 'There was an error creating the user.', WPDS_DOMAIN ) );
define( 'WPDS_NETWORK_PAGE_DUPLICATE_NOTICE_CREATED', __( 'New site was created', WPDS_DOMAIN ) );

/** 
 * Email
 */
define( 'WPDS_EMAIL_FROM', __( 'Site Admin' , WPDS_DOMAIN ) );
define( 'WPDS_EMAIL_CREATE_SITE_SUBJECT', __( '[%s] New Site Created' , WPDS_DOMAIN ) );
define( 'WPDS_EMAIL_CREATE_SITE_CONTENT', __( 

'New site created by %1$s

Address: %2$s
Name: %3$s', 

WPDS_DOMAIN ) );