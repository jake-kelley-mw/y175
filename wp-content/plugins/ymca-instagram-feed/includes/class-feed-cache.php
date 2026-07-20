<?php
/**
 * Feed Cache Handler
 * 
 * Manages caching of Instagram posts using WordPress transients.
 * Handles cache refresh, storage, and retrieval.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YMCA_IG_Feed_Cache {

    /**
     * Transient key for cached posts
     */
    private $cache_key = 'ymca_ig_feed_posts';

    /**
     * Plugin settings
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option( 'ymca_ig_feed_settings', array() );
    }

    /**
     * Get cached posts
     * 
     * @param int $count Number of posts to return (0 = all)
     * @return array Array of cached posts
     */
    public function get_posts( $count = 0 ) {
        $cached = get_transient( $this->cache_key );

        if ( false === $cached || ! is_array( $cached ) ) {
            // No cache, attempt to refresh
            $this->refresh();
            $cached = get_transient( $this->cache_key );
        }

        if ( false === $cached || ! is_array( $cached ) ) {
            return array();
        }

        // Return limited count if specified
        if ( $count > 0 && count( $cached ) > $count ) {
            return array_slice( $cached, 0, $count );
        }

        return $cached;
    }

    /**
     * Refresh the cache from Instagram API
     * 
     * @return bool Success status
     */
    public function refresh() {
        $api = new YMCA_IG_Feed_API();

        if ( ! $api->is_configured() ) {
            $this->log( 'Cache refresh skipped: API not configured.' );
            return false;
        }

        // Also check/refresh token if needed
        $api->maybe_refresh_token();

        // Fetch hashtag-matching posts, paging deep enough to satisfy the display
        // count even when tagged posts are sparse among the most recent media.
        // (Previously we fetched a single page of post_count*3 and filtered it,
        // which under-filled the feed whenever fewer than post_count of those
        // recent posts carried a target hashtag.)
        $display_count = isset( $this->settings['post_count'] )
            ? intval( $this->settings['post_count'] )
            : 12;

        $filtered = $api->fetch_filtered_posts( $display_count );

        if ( is_wp_error( $filtered ) ) {
            $this->log( 'Cache refresh failed: ' . $filtered->get_error_message() );
            return false;
        }

        // Store in transient
        $cache_duration = $this->get_cache_duration();
        set_transient( $this->cache_key, $filtered, $cache_duration );

        // Update last refresh time
        update_option( 'ymca_ig_feed_last_refresh', current_time( 'mysql' ) );

        // Clear any previous error since refresh succeeded
        delete_option( 'ymca_ig_feed_last_error' );

        // Clear page caches so fresh content is served
        $this->clear_page_cache();

        $this->log( sprintf(
            'Cache refreshed: %d hashtag-matching posts stored (requested %d).',
            count( $filtered ),
            $display_count
        ) );

        return true;
    }

    /**
     * Clear the cache
     */
    public function clear() {
        delete_transient( $this->cache_key );
        $this->clear_page_cache();
        $this->log( 'Cache cleared.' );
    }

    /**
     * Clear page/edge caches (Pantheon, other hosts)
     * Call this after updating the feed to ensure fresh content is served
     */
    public function clear_page_cache() {
        // Clear Pantheon edge cache
        if ( function_exists( 'pantheon_wp_clear_edge_all' ) ) {
            pantheon_wp_clear_edge_all();
            $this->log( 'Pantheon edge cache cleared.' );
        }

        // Clear Pantheon Redis object cache
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
            $this->log( 'Object cache flushed.' );
        }

        // Trigger action for other caching plugins
        do_action( 'ymca_ig_feed_cache_cleared' );
    }

    /**
     * Get cache duration in seconds
     * 
     * @return int Cache duration
     */
    private function get_cache_duration() {
        // Cache should last slightly longer than refresh interval
        // to prevent gaps if cron is delayed
        $interval = isset( $this->settings['refresh_interval'] ) 
            ? intval( $this->settings['refresh_interval'] ) 
            : 30;

        // Cache for 1.5x the refresh interval (minimum 1 hour)
        return max( $interval * 90, HOUR_IN_SECONDS );
    }

    /**
     * Check if cache exists and is valid
     * 
     * @return bool
     */
    public function has_cache() {
        return false !== get_transient( $this->cache_key );
    }

    /**
     * Get cache info for admin display
     * 
     * @return array
     */
    public function get_cache_info() {
        $posts = get_transient( $this->cache_key );
        $last_refresh = get_option( 'ymca_ig_feed_last_refresh', '' );

        return array(
            'has_cache'    => false !== $posts,
            'post_count'   => is_array( $posts ) ? count( $posts ) : 0,
            'last_refresh' => $last_refresh,
        );
    }

    /**
     * Log message
     */
    private function log( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[YMCA IG Feed] ' . $message );
        }
    }
}
