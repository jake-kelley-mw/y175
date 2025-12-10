<?php
/**
 * Instagram API Handler
 * 
 * Handles all communication with the Instagram Graph API,
 * including fetching posts and managing token refresh.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YMCA_IG_Feed_API {

    /**
     * Instagram Graph API base URL
     */
    private $api_base = 'https://graph.facebook.com/v19.0';

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
     * Check if API credentials are configured
     */
    public function is_configured() {
        return ! empty( $this->settings['access_token'] ) 
            && ! empty( $this->settings['instagram_id'] );
    }

    /**
     * Fetch posts from Instagram
     * 
     * @param int $limit Maximum number of posts to fetch from API (before filtering)
     * @return array|WP_Error Array of posts or error
     */
    public function fetch_posts( $limit = 50 ) {
        if ( ! $this->is_configured() ) {
            return new WP_Error( 'not_configured', __( 'Instagram API credentials not configured.', 'ymca-instagram-feed' ) );
        }

        $instagram_id = sanitize_text_field( $this->settings['instagram_id'] );
        $access_token = sanitize_text_field( $this->settings['access_token'] );

        // Fields to retrieve for each post
        $fields = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp';

        $url = add_query_arg( array(
            'fields'       => $fields,
            'access_token' => $access_token,
            'limit'        => $limit,
        ), "{$this->api_base}/{$instagram_id}/media" );

        $response = wp_remote_get( $url, array(
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->log_error( 'API request failed: ' . $response->get_error_message() );
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 ) {
            $error_message = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown API error';
            $this->log_error( "API error (HTTP {$code}): {$error_message}" );
            
            // Check if token needs refresh
            if ( $this->is_token_error( $data ) ) {
                $this->attempt_token_refresh();
            }

            return new WP_Error( 'api_error', $error_message );
        }

        if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
            return new WP_Error( 'invalid_response', __( 'Invalid API response format.', 'ymca-instagram-feed' ) );
        }

        return $data['data'];
    }

    /**
     * Filter posts by hashtags
     * 
     * @param array $posts Array of posts from API
     * @return array Filtered posts containing at least one target hashtag
     */
    public function filter_by_hashtags( $posts ) {
        if ( empty( $posts ) || ! is_array( $posts ) ) {
            return array();
        }

        $hashtags = $this->get_target_hashtags();

        if ( empty( $hashtags ) ) {
            // No hashtag filter configured, return all posts
            return $posts;
        }

        $filtered = array();

        foreach ( $posts as $post ) {
            if ( $this->post_has_hashtag( $post, $hashtags ) ) {
                $filtered[] = $post;
            }
        }

        return $filtered;
    }

    /**
     * Check if a post contains any of the target hashtags
     * 
     * @param array $post Single post data
     * @param array $hashtags Array of hashtags to match
     * @return bool
     */
    private function post_has_hashtag( $post, $hashtags ) {
        if ( ! isset( $post['caption'] ) || empty( $post['caption'] ) ) {
            return false;
        }

        $caption = strtolower( $post['caption'] );

        foreach ( $hashtags as $hashtag ) {
            // Match hashtag with word boundary (to avoid partial matches)
            $pattern = '/#' . preg_quote( $hashtag, '/' ) . '(?:\s|$|[^a-z0-9])/i';
            if ( preg_match( $pattern, $caption ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get configured target hashtags
     * 
     * @return array Array of hashtags (without # symbol, lowercase)
     */
    private function get_target_hashtags() {
        if ( empty( $this->settings['hashtags'] ) ) {
            return array();
        }

        $raw = $this->settings['hashtags'];
        $tags = array_map( 'trim', explode( ',', $raw ) );
        $tags = array_filter( $tags ); // Remove empty values

        // Normalize: remove # if present, lowercase
        $normalized = array();
        foreach ( $tags as $tag ) {
            $tag = ltrim( $tag, '#' );
            $tag = strtolower( $tag );
            if ( ! empty( $tag ) ) {
                $normalized[] = $tag;
            }
        }

        return $normalized;
    }

    /**
     * Check if API error is related to token expiration
     * 
     * @param array $data API response data
     * @return bool
     */
    private function is_token_error( $data ) {
        if ( ! isset( $data['error']['code'] ) ) {
            return false;
        }

        // OAuth/token-related error codes
        $token_error_codes = array( 190, 102, 463, 467 );
        return in_array( $data['error']['code'], $token_error_codes, true );
    }

    /**
     * Attempt to refresh the access token
     * 
     * @return bool Success status
     */
    public function attempt_token_refresh() {
        if ( empty( $this->settings['access_token'] ) ) {
            return false;
        }

        $access_token = sanitize_text_field( $this->settings['access_token'] );

        // Long-lived tokens can be refreshed by calling this endpoint
        // Token must be at least 24 hours old and not expired
        $url = add_query_arg( array(
            'grant_type'   => 'ig_refresh_token',
            'access_token' => $access_token,
        ), "{$this->api_base}/refresh_access_token" );

        $response = wp_remote_get( $url, array(
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->log_error( 'Token refresh failed: ' . $response->get_error_message() );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['access_token'] ) ) {
            // Update stored token
            $this->settings['access_token'] = $data['access_token'];
            $this->settings['token_updated'] = current_time( 'mysql' );
            update_option( 'ymca_ig_feed_settings', $this->settings );

            $this->log_info( 'Access token refreshed successfully.' );
            return true;
        }

        $error = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown error';
        $this->log_error( 'Token refresh failed: ' . $error );
        return false;
    }

    /**
     * Check token health and refresh if needed
     * Called periodically via cron
     */
    public function maybe_refresh_token() {
        if ( empty( $this->settings['token_updated'] ) ) {
            return;
        }

        $last_update = strtotime( $this->settings['token_updated'] );
        $days_since = ( time() - $last_update ) / DAY_IN_SECONDS;

        // Refresh if token is older than 50 days (expires at 60)
        if ( $days_since > 50 ) {
            $this->attempt_token_refresh();
        }
    }

    /**
     * Test API connection
     * 
     * @return array Status info
     */
    public function test_connection() {
        if ( ! $this->is_configured() ) {
            return array(
                'success' => false,
                'message' => __( 'API credentials not configured.', 'ymca-instagram-feed' ),
            );
        }

        $posts = $this->fetch_posts( 1 );

        if ( is_wp_error( $posts ) ) {
            return array(
                'success' => false,
                'message' => $posts->get_error_message(),
            );
        }

        return array(
            'success' => true,
            'message' => __( 'Connection successful!', 'ymca-instagram-feed' ),
        );
    }

    /**
     * Log error message
     */
    private function log_error( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[YMCA IG Feed] ERROR: ' . $message );
        }

        // Store last error for admin display
        update_option( 'ymca_ig_feed_last_error', array(
            'message' => $message,
            'time'    => current_time( 'mysql' ),
        ) );
    }

    /**
     * Log info message
     */
    private function log_info( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[YMCA IG Feed] INFO: ' . $message );
        }
    }
}
