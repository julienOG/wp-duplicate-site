<?php
/**
 * Option management for the plugin
 */
class PNV_Option {
    /**
     * Returns a list of post types for which the plugin will do something
     * Default: returns the list of all registered post types
     */
    public static function get_post_types() {
        // @TODO: get the list of post types saved in admin
        $post_type = get_post_types();

        // Remove medias from supported post types
        if( $index = array_search( 'attachment', $post_type ) )
            unset( $post_type[$index] );

        return $post_type;
    }

    /**
     * Returns a list of taxonomies given a post type, for which the plugin will do something
     * Default: returns the list of all registered taxonomies on this post type
     */
    public static function get_object_taxonomies( $post_type ) {
        // @TODO: get the list of taxonomies saved in admin for that post type
        return get_object_taxonomies( $post_type );
    }
}