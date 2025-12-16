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

        // Fetch more posts than we might display to allow for hashtag filtering
        $fetch_limit = $this->get_fetch_limit();
        $posts = $api->fetch_posts( $fetch_limit );

        if ( is_wp_error( $posts ) ) {
            $this->log( 'Cache refresh failed: ' . $posts->get_error_message() );
            return false;
        }

        // Filter by hashtags
        $filtered = $api->filter_by_hashtags( $posts );

        // Store in transient
        $cache_duration = $this->get_cache_duration();
        set_transient( $this->cache_key, $filtered, $cache_duration );

        // Update last refresh time
        update_option( 'ymca_ig_feed_last_refresh', current_time( 'mysql' ) );

        // Clear page caches so fresh content is served
        $this->clear_page_cache();

        $this->log( sprintf( 
            'Cache refreshed: %d posts fetched, %d posts after filtering.',
            count( $posts ),
            count( $filtered )
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
     * Get number of posts to fetch from API
     * 
     * Since we filter by hashtags, we need to fetch more than we display.
     * 
     * @return int Fetch limit
     */
    private function get_fetch_limit() {
        $display_count = isset( $this->settings['post_count'] ) 
            ? intval( $this->settings['post_count'] ) 
            : 12;

        // If set to 0 (show all), fetch maximum
        if ( $display_count === 0 ) {
            return 100; // API limit per request
        }

        // Fetch 3x what we want to display to account for filtering
        // (assuming roughly 1/3 of posts might have the target hashtag)
        return min( $display_count * 3, 100 );
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
