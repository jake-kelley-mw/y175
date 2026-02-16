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
     * Check if token refresh credentials are configured
     */
    public function can_refresh_token() {
        return ! empty( $this->settings['app_id'] ) 
            && ! empty( $this->settings['app_secret'] )
            && ! empty( $this->settings['access_token'] );
    }

    /**
     * Validate the current token with Facebook's debug endpoint
     * Returns actual expiration info from Facebook, not our stored timestamp
     * 
     * @return array Token info including 'is_valid', 'expires_at', 'days_remaining', 'error'
     */
    public function validate_token() {
        if ( ! $this->is_configured() ) {
            return array(
                'is_valid' => false,
                'error'    => 'API credentials not configured',
            );
        }

        if ( ! $this->can_refresh_token() ) {
            return array(
                'is_valid' => false,
                'error'    => 'App ID and App Secret required for token validation',
            );
        }

        $access_token = sanitize_text_field( $this->settings['access_token'] );
        $app_id = sanitize_text_field( $this->settings['app_id'] );
        $app_secret = sanitize_text_field( $this->settings['app_secret'] );

        // Use Facebook's token debug endpoint
        $url = add_query_arg( array(
            'input_token'  => $access_token,
            'access_token' => $app_id . '|' . $app_secret,
        ), "{$this->api_base}/debug_token" );

        $response = wp_remote_get( $url, array( 'timeout' => 15 ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'is_valid' => false,
                'error'    => $response->get_error_message(),
            );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! isset( $data['data'] ) ) {
            return array(
                'is_valid' => false,
                'error'    => 'Invalid response from Facebook',
            );
        }

        $token_data = $data['data'];
        $is_valid = ! empty( $token_data['is_valid'] );
        $expires_at = isset( $token_data['expires_at'] ) ? $token_data['expires_at'] : null;
        
        $days_remaining = null;
        if ( $expires_at && $expires_at > 0 ) {
            $days_remaining = ( $expires_at - time() ) / DAY_IN_SECONDS;
        }

        return array(
            'is_valid'       => $is_valid,
            'expires_at'     => $expires_at,
            'days_remaining' => $days_remaining,
            'error'          => isset( $token_data['error']['message'] ) ? $token_data['error']['message'] : null,
        );
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
     * For Facebook Graph API tokens, we use the fb_exchange_token endpoint
     * which requires App ID and App Secret.
     * 
     * @return bool Success status
     */
    public function attempt_token_refresh() {
        if ( ! $this->can_refresh_token() ) {
            $error_msg = 'Token refresh failed: App ID and App Secret are required.';
            $this->log_error( $error_msg );
            $this->send_failure_notification( $error_msg );
            return false;
        }

        // First check if current token is still valid (can't refresh expired tokens)
        $validation = $this->validate_token();
        if ( ! $validation['is_valid'] ) {
            $error_msg = 'Token refresh failed: Current token is already invalid or expired. A new token must be generated manually.';
            $this->log_error( $error_msg );
            $this->send_failure_notification( $error_msg );
            return false;
        }

        $app_id = sanitize_text_field( $this->settings['app_id'] );
        $app_secret = sanitize_text_field( $this->settings['app_secret'] );
        $access_token = sanitize_text_field( $this->settings['access_token'] );

        // Facebook Graph API token refresh endpoint
        $url = add_query_arg( array(
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $app_id,
            'client_secret'     => $app_secret,
            'fb_exchange_token' => $access_token,
        ), "{$this->api_base}/oauth/access_token" );

        $response = wp_remote_get( $url, array(
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            $error_msg = 'Token refresh request failed: ' . $response->get_error_message();
            $this->log_error( $error_msg );
            $this->send_failure_notification( $error_msg );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['access_token'] ) ) {
            $new_token = $data['access_token'];
            $old_token = $this->settings['access_token'];
            
            // Temporarily set new token to validate it
            $this->settings['access_token'] = $new_token;
            $new_validation = $this->validate_token();
            
            if ( ! $new_validation['is_valid'] ) {
                // New token is invalid, restore old one
                $this->settings['access_token'] = $old_token;
                $error_msg = 'Token refresh failed: New token failed validation.';
                $this->log_error( $error_msg );
                $this->send_failure_notification( $error_msg );
                return false;
            }

            // New token is valid, save everything
            $this->settings['token_updated'] = current_time( 'mysql' );
            
            if ( ! empty( $new_validation['expires_at'] ) ) {
                $this->settings['token_expires'] = $new_validation['expires_at'];
            }
            
            update_option( 'ymca_ig_feed_settings', $this->settings );
            
            // Clear error since we succeeded
            delete_option( 'ymca_ig_feed_last_error' );

            $this->log_info( 'Access token refreshed and verified successfully.' );
            return true;
        }

        $error = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown error';
        $error_msg = 'Token refresh failed: ' . $error;
        $this->log_error( $error_msg );
        $this->send_failure_notification( $error_msg );
        return false;
    }

    /**
     * Send email notification when token refresh fails
     * 
     * @param string $error_message The error details
     */
    private function send_failure_notification( $error_message ) {
        // Only send once per day to avoid spam
        $last_notification = get_option( 'ymca_ig_feed_last_notification', 0 );
        if ( ( time() - $last_notification ) < DAY_IN_SECONDS ) {
            return;
        }

        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );
        $settings_url = admin_url( 'options-general.php?page=ymca-instagram-feed' );

        $subject = "[{$site_name}] ACTION REQUIRED: Instagram Feed Token Issue";
        
        $message = "The Instagram Feed plugin encountered a problem with its access token.\n\n";
        $message .= "Error: {$error_message}\n\n";
        $message .= "ACTION REQUIRED: The Instagram feed may stop working soon if this is not addressed.\n\n";
        $message .= "To fix this, generate a new access token:\n\n";
        $message .= "1. Go to: https://developers.facebook.com/tools/explorer\n";
        $message .= "2. Select your app from the dropdown\n";
        $message .= "3. Add permissions: instagram_basic, pages_read_engagement, pages_show_list, business_management\n";
        $message .= "4. Click 'Generate Access Token' and complete the authorization\n";
        $message .= "5. Exchange for long-lived token using the URL format in your documentation\n";
        $message .= "6. Paste the new token here: {$settings_url}\n\n";
        $message .= "This email is sent at most once per day while the issue persists.";

        wp_mail( $admin_email, $subject, $message );
        
        update_option( 'ymca_ig_feed_last_notification', time() );
        $this->log_info( 'Failure notification email sent to ' . $admin_email );
    }

    /**
     * Check token health and refresh if needed
     * Called periodically via cron
     */
    public function maybe_refresh_token() {
        // Skip if we can't refresh anyway
        if ( ! $this->can_refresh_token() ) {
            return;
        }

        // Validate token with Facebook to get real expiration
        $validation = $this->validate_token();
        
        if ( ! $validation['is_valid'] ) {
            // Token is already invalid
            $error_msg = 'Token is invalid: ' . ( $validation['error'] ?? 'Unknown error' );
            $this->log_error( $error_msg );
            $this->send_failure_notification( $error_msg );
            return;
        }

        // Check days remaining from Facebook's response
        $days_remaining = $validation['days_remaining'];
        
        if ( $days_remaining === null ) {
            // Couldn't determine expiration, try to refresh anyway if our timestamp is old
            if ( ! empty( $this->settings['token_updated'] ) ) {
                $last_update = strtotime( $this->settings['token_updated'] );
                $days_since = ( time() - $last_update ) / DAY_IN_SECONDS;
                
                if ( $days_since > 45 ) {
                    $this->attempt_token_refresh();
                }
            }
            return;
        }

        // Refresh at 45 days remaining (gives 15 days buffer)
        if ( $days_remaining <= 45 ) {
            $this->log_info( "Token expires in {$days_remaining} days, attempting refresh." );
            $this->attempt_token_refresh();
        }
    }

    /**
     * Get token expiration status based on actual Facebook validation
     * 
     * @return array Status info
     */
    public function get_token_status() {
        if ( ! $this->can_refresh_token() ) {
            return array(
                'status'  => 'unknown',
                'message' => __( 'Add App ID and App Secret to enable token monitoring.', 'ymca-instagram-feed' ),
            );
        }

        $validation = $this->validate_token();

        if ( ! $validation['is_valid'] ) {
            return array(
                'status'  => 'expired',
                'message' => __( 'Token is invalid or expired. Generate a new token.', 'ymca-instagram-feed' ),
            );
        }

        $days_remaining = $validation['days_remaining'];

        if ( $days_remaining === null ) {
            return array(
                'status'  => 'valid',
                'message' => __( 'Token is valid (expiration unknown).', 'ymca-instagram-feed' ),
            );
        }

        if ( $days_remaining <= 0 ) {
            return array(
                'status'  => 'expired',
                'message' => __( 'Token has expired. Generate a new token.', 'ymca-instagram-feed' ),
            );
        } elseif ( $days_remaining <= 10 ) {
            return array(
                'status'  => 'expiring_soon',
                'message' => sprintf( __( 'Token expires in %d days. Auto-refresh will attempt soon.', 'ymca-instagram-feed' ), round( $days_remaining ) ),
            );
        } elseif ( $days_remaining <= 45 ) {
            return array(
                'status'  => 'valid',
                'message' => sprintf( __( 'Token valid for %d days. Auto-refresh scheduled.', 'ymca-instagram-feed' ), round( $days_remaining ) ),
            );
        }

        return array(
            'status'  => 'valid',
            'message' => sprintf( __( 'Token valid for %d days.', 'ymca-instagram-feed' ), round( $days_remaining ) ),
        );
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
