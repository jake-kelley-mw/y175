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
     *
     * @param array|null $settings Optional settings to seed (used when validating
     *                             a not-yet-saved token during admin save). Falls
     *                             back to the stored option.
     */
    public function __construct( $settings = null ) {
        $this->settings = is_array( $settings ) ? $settings : get_option( 'ymca_ig_feed_settings', array() );
    }

    /**
     * Whether outbound calls to Facebook are permitted in this environment.
     *
     * Disabled automatically in Local development so a cloned copy of the site
     * never hits the production Facebook app or rotates the live access token.
     * Define YMCA_IG_FEED_ALLOW_REMOTE (true/false) in wp-config to override.
     *
     * @return bool
     */
    public function remote_allowed() {
        if ( defined( 'YMCA_IG_FEED_ALLOW_REMOTE' ) ) {
            return (bool) YMCA_IG_FEED_ALLOW_REMOTE;
        }

        return 'local' !== wp_get_environment_type();
    }

    /**
     * Call Facebook's debug_token endpoint and return the `data` payload.
     *
     * @param string $token Token to inspect.
     * @return array Token metadata (empty array on failure).
     */
    private function debug_token( $token ) {
        $url = add_query_arg( array(
            'input_token'  => $token,
            'access_token' => sanitize_text_field( $this->settings['app_id'] ?? '' ) . '|' . sanitize_text_field( $this->settings['app_secret'] ?? '' ),
        ), "{$this->api_base}/debug_token" );

        $response = wp_remote_get( $url, array( 'timeout' => 20 ) );

        if ( is_wp_error( $response ) ) {
            return array();
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        return isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
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

        // In isolated environments (local), assume valid without calling out.
        if ( ! $this->remote_allowed() ) {
            return array(
                'is_valid'      => true,
                'never_expires' => false,
                'skipped'       => true,
                'error'         => null,
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
            // Network/transport failure — we did not get a verdict from Facebook.
            // Mark inconclusive so a transient blip is never treated as an
            // invalid token (which would fire a false-alarm alert email).
            return array(
                'is_valid'      => false,
                'indeterminate' => true,
                'error'         => $response->get_error_message(),
            );
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // A non-200 status, or a body with no `data` payload, means Facebook did
        // not actually evaluate the token (5xx, rate limit, empty/garbled body).
        // This is NOT the same as Facebook reporting the token invalid — a truly
        // expired/invalid token still comes back as HTTP 200 with
        // data.is_valid=false. Treat the ambiguous case as inconclusive.
        if ( 200 !== $code || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
            return array(
                'is_valid'      => false,
                'indeterminate' => true,
                'error'         => isset( $data['error']['message'] ) ? $data['error']['message'] : 'Invalid response from Facebook',
            );
        }

        $token_data = $data['data'];
        $is_valid = ! empty( $token_data['is_valid'] );
        $expires_at = isset( $token_data['expires_at'] ) ? (int) $token_data['expires_at'] : null;
        $type = isset( $token_data['type'] ) ? $token_data['type'] : null;

        // expires_at === 0 is Facebook's signal for a non-expiring token
        // (a Page access token derived from a long-lived User token).
        $never_expires = ( 0 === $expires_at );

        $days_remaining = null;
        if ( $expires_at && $expires_at > 0 ) {
            $days_remaining = ( $expires_at - time() ) / DAY_IN_SECONDS;
        }

        return array(
            'is_valid'       => $is_valid,
            'type'           => $type,
            'expires_at'     => $expires_at,
            'never_expires'  => $never_expires,
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

        if ( ! $this->remote_allowed() ) {
            return new WP_Error( 'remote_disabled', __( 'Instagram API calls are disabled in this environment. Cached posts are served instead.', 'ymca-instagram-feed' ) );
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
     * Fetch hashtag-matching posts, paging through the account's media until we
     * have at least $needed matches (or run out of posts / hit the scan cap).
     *
     * The Graph API returns media newest-first with no server-side hashtag
     * filter. When tagged posts are sparse among recent media, a single page of
     * ~$needed items yields fewer than $needed matches (this is exactly why the
     * feed was showing 14 of a requested 16). Following the `paging.next` cursor
     * lets us look deeper until the display count is satisfied.
     *
     * @param int $needed   Desired number of matching posts (0 = collect as many
     *                       as found, up to the scan cap).
     * @param int $max_scan Safety ceiling on raw posts paged through.
     * @return array|WP_Error Matching posts (may be fewer than $needed if the
     *                        account genuinely has fewer), or WP_Error if the
     *                        very first request fails.
     */
    public function fetch_filtered_posts( $needed, $max_scan = 500 ) {
        if ( ! $this->is_configured() ) {
            return new WP_Error( 'not_configured', __( 'Instagram API credentials not configured.', 'ymca-instagram-feed' ) );
        }

        if ( ! $this->remote_allowed() ) {
            return new WP_Error( 'remote_disabled', __( 'Instagram API calls are disabled in this environment. Cached posts are served instead.', 'ymca-instagram-feed' ) );
        }

        $instagram_id = sanitize_text_field( $this->settings['instagram_id'] );
        $access_token = sanitize_text_field( $this->settings['access_token'] );
        $fields       = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp';
        $page_size    = 50;
        $max_pages    = 10; // hard stop alongside $max_scan

        // First page. Subsequent pages reuse Facebook's paging.next URL, which
        // already carries the access token, fields and the `after` cursor.
        $url = add_query_arg( array(
            'fields'       => $fields,
            'access_token' => $access_token,
            'limit'        => $page_size,
        ), "{$this->api_base}/{$instagram_id}/media" );

        $matched = array();
        $scanned = 0;
        $pages   = 0;

        while ( $url && $pages < $max_pages && $scanned < $max_scan ) {
            $response = wp_remote_get( $url, array( 'timeout' => 30 ) );

            if ( is_wp_error( $response ) ) {
                $this->log_error( 'API request failed: ' . $response->get_error_message() );
                // Return whatever we already gathered rather than losing the feed.
                return ! empty( $matched ) ? $matched : $response;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            if ( 200 !== (int) $code ) {
                $error_message = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown API error';
                $this->log_error( "API error (HTTP {$code}): {$error_message}" );
                if ( $this->is_token_error( $data ) ) {
                    $this->attempt_token_refresh();
                }
                return ! empty( $matched ) ? $matched : new WP_Error( 'api_error', $error_message );
            }

            if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
                return ! empty( $matched ) ? $matched : new WP_Error( 'invalid_response', __( 'Invalid API response format.', 'ymca-instagram-feed' ) );
            }

            $batch    = $data['data'];
            $scanned += count( $batch );
            $matched  = array_merge( $matched, $this->filter_by_hashtags( $batch ) );

            // Got enough — stop paging.
            if ( $needed > 0 && count( $matched ) >= $needed ) {
                break;
            }

            $url = isset( $data['paging']['next'] ) ? $data['paging']['next'] : '';
            $pages++;
        }

        // Trim any overshoot from the final page so we store exactly what's asked.
        if ( $needed > 0 && count( $matched ) > $needed ) {
            $matched = array_slice( $matched, 0, $needed );
        }

        return $matched;
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
        if ( ! $this->remote_allowed() ) {
            return false;
        }

        if ( ! $this->can_refresh_token() ) {
            $error_msg = 'Token refresh failed: App ID and App Secret are required.';
            $this->log_error( $error_msg );
            $this->send_failure_notification( $error_msg );
            return false;
        }

        // First check if current token is still valid (can't refresh expired tokens)
        $validation = $this->validate_token();
        if ( ! empty( $validation['indeterminate'] ) ) {
            // Couldn't confirm token state right now; don't rotate or alarm on a blip.
            $this->log_info( 'Skipping token refresh: validation inconclusive (transient) — ' . ( $validation['error'] ?? 'unknown' ) . '.' );
            return false;
        }
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

            if ( ! empty( $new_validation['indeterminate'] ) ) {
                // Couldn't confirm the new token (transient); keep the old token
                // and try again later rather than emailing over a blip.
                $this->settings['access_token'] = $old_token;
                $this->log_info( 'New token validation inconclusive (transient) — keeping existing token, will retry next cycle.' );
                return false;
            }

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
        // Skip if we can't refresh anyway, or if remote calls are disabled here.
        if ( ! $this->can_refresh_token() || ! $this->remote_allowed() ) {
            return;
        }

        // Validate token with Facebook to get real expiration
        $validation = $this->validate_token();

        // Inconclusive check (transport error, 5xx, rate limit, garbled body):
        // Facebook never gave a verdict, so the token is presumed fine. Log it
        // and try again next cycle — do NOT alarm the admin over a transient blip.
        if ( ! empty( $validation['indeterminate'] ) ) {
            $this->log_info( 'Token check inconclusive (transient): ' . ( $validation['error'] ?? 'unknown' ) . ' — leaving token unchanged, will recheck next cycle.' );
            return;
        }

        if ( empty( $validation['is_valid'] ) ) {
            // Facebook explicitly reports the token is invalid/expired.
            $error_msg = 'Token is invalid: ' . ( $validation['error'] ?? 'Unknown error' );
            $this->log_error( $error_msg );
            $this->send_failure_notification( $error_msg );
            return;
        }

        // A never-expiring Page access token needs no maintenance. This is the
        // healthy steady state once the token has been converted (see
        // convert_to_page_token) — do nothing.
        if ( ! empty( $validation['never_expires'] ) ) {
            return;
        }

        $days_remaining = $validation['days_remaining'];

        // Valid but expiry unknown: leave it alone (can't act sensibly).
        if ( null === $days_remaining ) {
            return;
        }

        // The token still has a finite life. IMPORTANT: a long-lived *User*
        // token cannot be extended by re-exchanging it via fb_exchange_token —
        // Facebook returns a new string with the SAME expiry. The durable fix is
        // to convert to a non-expiring Page token. Try that within the buffer
        // window; if it can't yield a permanent token, alert so a human can
        // generate a fresh token.
        if ( $days_remaining <= 10 ) {
            $converted = $this->convert_to_page_token();

            if ( ! is_wp_error( $converted ) && ! empty( $converted['never_expires'] ) ) {
                $this->store_converted_token( $converted );
                $this->log_info( 'Converted access token to a permanent Page access token.' );
                return;
            }

            $this->log_error( 'Token expiring and could not be auto-converted to a permanent Page token.' );
            $this->send_failure_notification( sprintf(
                'Access token expires in about %d day(s) and could not be renewed automatically. Generate a fresh token to switch to a permanent Page token.',
                max( 0, (int) round( $days_remaining ) )
            ) );
        }
    }

    /**
     * Convert the current (or a supplied) access token into a non-expiring
     * Facebook Page access token for the configured Instagram account.
     *
     * Flow: exchange for a long-lived User token, locate the Page connected to
     * the Instagram Business account, and read that Page's access token. A Page
     * token derived from a long-lived User token does not expire.
     *
     * @param string|null $source_token Token to convert (defaults to stored token).
     * @return array|WP_Error { access_token, page_id, token_type, token_expires, never_expires }
     */
    public function convert_to_page_token( $source_token = null ) {
        if ( ! $this->remote_allowed() ) {
            return new WP_Error( 'remote_disabled', __( 'Remote API calls are disabled in this environment.', 'ymca-instagram-feed' ) );
        }

        if ( ! $this->can_refresh_token() ) {
            return new WP_Error( 'no_app_creds', __( 'App ID and App Secret are required to obtain a Page token.', 'ymca-instagram-feed' ) );
        }

        $source = $source_token ? $source_token : ( $this->settings['access_token'] ?? '' );
        if ( empty( $source ) ) {
            return new WP_Error( 'no_token', __( 'No access token to convert.', 'ymca-instagram-feed' ) );
        }

        $instagram_id = sanitize_text_field( $this->settings['instagram_id'] ?? '' );
        if ( empty( $instagram_id ) ) {
            return new WP_Error( 'no_ig', __( 'Instagram Business Account ID is required.', 'ymca-instagram-feed' ) );
        }

        // 1) Ensure a long-lived User token (no-op-ish if already long-lived).
        $long_lived = $this->get_long_lived_user_token( $source );
        if ( is_wp_error( $long_lived ) ) {
            return $long_lived;
        }

        // 2) Find the Page connected to the Instagram account + its Page token.
        $page = $this->find_instagram_page_token( $long_lived, $instagram_id );
        if ( is_wp_error( $page ) ) {
            return $page;
        }

        // 3) Inspect the resulting Page token's real expiry.
        $info       = $this->debug_token( $page['access_token'] );
        $expires_at = isset( $info['expires_at'] ) ? (int) $info['expires_at'] : 0;

        return array(
            'access_token'  => $page['access_token'],
            'page_id'       => $page['id'],
            'token_type'    => isset( $info['type'] ) ? $info['type'] : 'PAGE',
            'token_expires' => $expires_at,
            'never_expires' => ( 0 === $expires_at ),
        );
    }

    /**
     * Exchange any User token for a long-lived User token.
     *
     * @param string $token
     * @return string|WP_Error Long-lived token string.
     */
    private function get_long_lived_user_token( $token ) {
        $url = add_query_arg( array(
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => sanitize_text_field( $this->settings['app_id'] ),
            'client_secret'     => sanitize_text_field( $this->settings['app_secret'] ),
            'fb_exchange_token' => $token,
        ), "{$this->api_base}/oauth/access_token" );

        $response = wp_remote_get( $url, array( 'timeout' => 30 ) );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $data['access_token'] ) ) {
            $msg = isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'Could not exchange for a long-lived token.', 'ymca-instagram-feed' );
            return new WP_Error( 'exchange_failed', $msg );
        }

        return $data['access_token'];
    }

    /**
     * Locate the Page access token for the Page linked to $ig_id.
     *
     * Tries /me/accounts first; falls back to the Page IDs embedded in the
     * token's granular_scopes (Business-Manager / granular permissions often
     * leave /me/accounts empty).
     *
     * @param string $user_token Long-lived User token.
     * @param string $ig_id      Instagram Business Account ID.
     * @return array|WP_Error { id, access_token }
     */
    private function find_instagram_page_token( $user_token, $ig_id ) {
        // Preferred path: list managed Pages.
        $response = wp_remote_get( add_query_arg( array(
            'access_token' => $user_token,
            'fields'       => 'id,access_token,instagram_business_account',
            'limit'        => 100,
        ), "{$this->api_base}/me/accounts" ), array( 'timeout' => 30 ) );

        if ( ! is_wp_error( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $data['data'] ) ) {
                foreach ( $data['data'] as $pg ) {
                    if ( isset( $pg['instagram_business_account']['id'] )
                        && (string) $pg['instagram_business_account']['id'] === (string) $ig_id
                        && ! empty( $pg['access_token'] ) ) {
                        return array( 'id' => $pg['id'], 'access_token' => $pg['access_token'] );
                    }
                }
            }
        }

        // Fallback: candidate Page IDs from granular scopes.
        $info     = $this->debug_token( $user_token );
        $page_ids = array();
        if ( ! empty( $info['granular_scopes'] ) ) {
            foreach ( $info['granular_scopes'] as $scope ) {
                if ( ! empty( $scope['target_ids'] ) ) {
                    $page_ids = array_merge( $page_ids, $scope['target_ids'] );
                }
            }
        }
        $page_ids = array_values( array_unique( $page_ids ) );

        foreach ( $page_ids as $pid ) {
            $response = wp_remote_get( add_query_arg( array(
                'access_token' => $user_token,
                'fields'       => 'id,access_token,instagram_business_account',
            ), "{$this->api_base}/{$pid}" ), array( 'timeout' => 30 ) );

            if ( is_wp_error( $response ) ) {
                continue;
            }

            $pg = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $pg['error'] ) || empty( $pg['access_token'] ) ) {
                continue;
            }

            $linked = isset( $pg['instagram_business_account']['id'] ) ? (string) $pg['instagram_business_account']['id'] : '';
            if ( $linked === (string) $ig_id ) {
                return array( 'id' => $pg['id'], 'access_token' => $pg['access_token'] );
            }
        }

        return new WP_Error( 'no_page', __( 'Could not find a Facebook Page linked to this Instagram account. Ensure the token has instagram_basic, pages_show_list, pages_read_engagement and business_management permissions.', 'ymca-instagram-feed' ) );
    }

    /**
     * Convert the current/supplied token to a Page token AND persist it.
     *
     * @param string|null $source_token
     * @return array|WP_Error The converted token data, or WP_Error on failure.
     */
    public function convert_and_store( $source_token = null ) {
        $converted = $this->convert_to_page_token( $source_token );
        if ( is_wp_error( $converted ) ) {
            return $converted;
        }
        $this->store_converted_token( $converted );
        return $converted;
    }

    /**
     * Persist a converted Page token into the stored settings.
     *
     * @param array $converted Output of convert_to_page_token().
     */
    private function store_converted_token( array $converted ) {
        $this->settings['access_token']  = $converted['access_token'];
        $this->settings['page_id']       = $converted['page_id'];
        $this->settings['token_type']    = $converted['token_type'];
        $this->settings['token_expires'] = $converted['token_expires'];
        $this->settings['token_updated'] = current_time( 'mysql' );

        update_option( 'ymca_ig_feed_settings', $this->settings );
        delete_option( 'ymca_ig_feed_last_error' );
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

        if ( ! $this->remote_allowed() ) {
            return array(
                'status'  => 'unknown',
                'message' => __( 'Token checks are disabled in this (local) environment.', 'ymca-instagram-feed' ),
            );
        }

        $validation = $this->validate_token();

        if ( ! empty( $validation['indeterminate'] ) ) {
            return array(
                'status'  => 'unknown',
                'message' => __( 'Could not reach Facebook to check the token just now (temporary). The saved token is unchanged.', 'ymca-instagram-feed' ),
            );
        }

        if ( ! $validation['is_valid'] ) {
            return array(
                'status'  => 'expired',
                'message' => __( 'Token is invalid or expired. Generate a new token.', 'ymca-instagram-feed' ),
            );
        }

        // The desired steady state: a non-expiring Page access token.
        if ( ! empty( $validation['never_expires'] ) ) {
            return array(
                'status'  => 'valid',
                'message' => __( 'Permanent Page access token — does not expire. ✓', 'ymca-instagram-feed' ),
            );
        }

        $days_remaining = $validation['days_remaining'];

        if ( $days_remaining === null ) {
            return array(
                'status'  => 'valid',
                'message' => __( 'Token is valid (expiration unknown).', 'ymca-instagram-feed' ),
            );
        }

        // A token with a finite life means we are still on a User token (or a
        // non-permanent Page token) and the expiry cycle will recur.
        if ( $validation['type'] !== 'PAGE' ) {
            return array(
                'status'  => 'expiring_soon',
                'message' => sprintf( __( 'Using a temporary User token (expires in %d days). Re-save your token to convert it to a permanent Page token.', 'ymca-instagram-feed' ), round( $days_remaining ) ),
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
        } elseif ( $days_remaining <= 50 ) {
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
