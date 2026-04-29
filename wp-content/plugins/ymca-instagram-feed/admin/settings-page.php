<?php
/**
 * Admin Settings Page
 * 
 * Provides interface for configuring Instagram API credentials,
 * hashtag filters, display options, and manual cache refresh.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YMCA_IG_Feed_Admin {

    /**
     * Option name for settings
     */
    private $option_name = 'ymca_ig_feed_settings';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'daily_token_health_check' ) );
        add_action( 'admin_post_ymca_ig_feed_refresh', array( $this, 'handle_manual_refresh' ) );
        add_action( 'admin_post_ymca_ig_feed_test', array( $this, 'handle_test_connection' ) );
    }

    /**
     * Check token health once per day on admin load.
     * Fallback for wp-cron failures on cached hosting (Pantheon).
     */
    public function daily_token_health_check() {
        $last_check = get_option( 'ymca_ig_feed_last_admin_token_check', 0 );

        // Only run once per day
        if ( ( time() - $last_check ) < DAY_IN_SECONDS ) {
            return;
        }

        update_option( 'ymca_ig_feed_last_admin_token_check', time(), false );

        $api = new YMCA_IG_Feed_API();
        if ( $api->can_refresh_token() ) {
            $api->maybe_refresh_token();
        }
    }

    /**
     * Add menu page under Settings
     */
    public function add_menu_page() {
        add_options_page(
            __( 'Instagram Feed Settings', 'ymca-instagram-feed' ),
            __( 'Instagram Feed', 'ymca-instagram-feed' ),
            'manage_options',
            'ymca-instagram-feed',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 
            'ymca_ig_feed_settings_group', 
            $this->option_name,
            array( $this, 'sanitize_settings' )
        );

        // API Credentials Section
        add_settings_section(
            'ymca_ig_feed_api_section',
            __( 'API Credentials', 'ymca-instagram-feed' ),
            array( $this, 'render_api_section' ),
            'ymca-instagram-feed'
        );

        add_settings_field(
            'instagram_id',
            __( 'Instagram Business Account ID', 'ymca-instagram-feed' ),
            array( $this, 'render_text_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_api_section',
            array( 
                'field' => 'instagram_id',
                'description' => __( 'The numeric ID of the Instagram Business account.', 'ymca-instagram-feed' ),
            )
        );

        add_settings_field(
            'access_token',
            __( 'Access Token', 'ymca-instagram-feed' ),
            array( $this, 'render_textarea_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_api_section',
            array( 
                'field' => 'access_token',
                'description' => __( 'Long-lived access token from Facebook Developer.', 'ymca-instagram-feed' ),
            )
        );

        add_settings_field(
            'app_id',
            __( 'Facebook App ID', 'ymca-instagram-feed' ),
            array( $this, 'render_text_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_api_section',
            array( 
                'field' => 'app_id',
                'description' => __( 'Required for automatic token refresh. Found in your Facebook Developer App settings.', 'ymca-instagram-feed' ),
            )
        );

        add_settings_field(
            'app_secret',
            __( 'Facebook App Secret', 'ymca-instagram-feed' ),
            array( $this, 'render_password_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_api_section',
            array( 
                'field' => 'app_secret',
                'description' => __( 'Required for automatic token refresh. Keep this secret!', 'ymca-instagram-feed' ),
            )
        );

        // Filter Section
        add_settings_section(
            'ymca_ig_feed_filter_section',
            __( 'Hashtag Filter', 'ymca-instagram-feed' ),
            array( $this, 'render_filter_section' ),
            'ymca-instagram-feed'
        );

        add_settings_field(
            'hashtags',
            __( 'Target Hashtags', 'ymca-instagram-feed' ),
            array( $this, 'render_text_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_filter_section',
            array( 
                'field' => 'hashtags',
                'description' => __( 'Comma-separated list of hashtags (without #). Leave empty to show all posts.', 'ymca-instagram-feed' ),
                'placeholder' => 'ymca175, anniversary, ymcastrong',
            )
        );

        // Display Section
        add_settings_section(
            'ymca_ig_feed_display_section',
            __( 'Display Options', 'ymca-instagram-feed' ),
            null,
            'ymca-instagram-feed'
        );

        add_settings_field(
            'post_count',
            __( 'Number of Posts', 'ymca-instagram-feed' ),
            array( $this, 'render_number_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_display_section',
            array( 
                'field' => 'post_count',
                'description' => __( 'Number of posts to display. Set to 0 for all matching posts.', 'ymca-instagram-feed' ),
                'default' => 12,
                'min' => 0,
                'max' => 100,
            )
        );

        add_settings_field(
            'columns',
            __( 'Grid Columns', 'ymca-instagram-feed' ),
            array( $this, 'render_select_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_display_section',
            array( 
                'field' => 'columns',
                'description' => __( 'Number of columns on desktop. Automatically adjusts for smaller screens.', 'ymca-instagram-feed' ),
                'options' => array(
                    2 => '2 columns',
                    3 => '3 columns',
                    4 => '4 columns',
                    5 => '5 columns',
                    6 => '6 columns',
                ),
                'default' => 4,
            )
        );

        add_settings_field(
            'show_captions',
            __( 'Show Captions on Hover', 'ymca-instagram-feed' ),
            array( $this, 'render_checkbox_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_display_section',
            array( 
                'field' => 'show_captions',
                'description' => __( 'Display caption preview when hovering over posts.', 'ymca-instagram-feed' ),
                'default' => 1,
            )
        );

        // Cache Section
        add_settings_section(
            'ymca_ig_feed_cache_section',
            __( 'Cache Settings', 'ymca-instagram-feed' ),
            null,
            'ymca-instagram-feed'
        );

        add_settings_field(
            'refresh_interval',
            __( 'Refresh Interval (minutes)', 'ymca-instagram-feed' ),
            array( $this, 'render_number_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_cache_section',
            array( 
                'field' => 'refresh_interval',
                'description' => __( 'How often to check for new posts. Recommended: 15-30 minutes.', 'ymca-instagram-feed' ),
                'default' => 30,
                'min' => 5,
                'max' => 1440,
            )
        );

        add_settings_field(
            'cron_token',
            __( 'External Cron Token', 'ymca-instagram-feed' ),
            array( $this, 'render_cron_token_field' ),
            'ymca-instagram-feed',
            'ymca_ig_feed_cache_section',
            array( 
                'field' => 'cron_token',
                'description' => __( 'Token for external cron refresh (Pantheon).', 'ymca-instagram-feed' ),
            )
        );
    }

    /**
     * Sanitize settings on save
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        // Preserve existing values
        $existing = get_option( $this->option_name, array() );
        
        $sanitized['instagram_id'] = isset( $input['instagram_id'] ) 
            ? sanitize_text_field( $input['instagram_id'] ) 
            : '';

        $sanitized['access_token'] = isset( $input['access_token'] ) 
            ? sanitize_textarea_field( $input['access_token'] ) 
            : '';

        // If token changed, update timestamp
        if ( $sanitized['access_token'] !== ( $existing['access_token'] ?? '' ) ) {
            $sanitized['token_updated'] = current_time( 'mysql' );
        } else {
            $sanitized['token_updated'] = $existing['token_updated'] ?? '';
        }

        $sanitized['app_id'] = isset( $input['app_id'] ) 
            ? sanitize_text_field( $input['app_id'] ) 
            : '';

        $sanitized['app_secret'] = isset( $input['app_secret'] ) 
            ? sanitize_text_field( $input['app_secret'] ) 
            : '';

        $sanitized['hashtags'] = isset( $input['hashtags'] ) 
            ? sanitize_text_field( $input['hashtags'] ) 
            : '';

        $sanitized['post_count'] = isset( $input['post_count'] ) 
            ? absint( $input['post_count'] ) 
            : 12;

        $sanitized['columns'] = isset( $input['columns'] ) 
            ? absint( $input['columns'] ) 
            : 4;

        $sanitized['show_captions'] = isset( $input['show_captions'] ) ? 1 : 0;

        $sanitized['refresh_interval'] = isset( $input['refresh_interval'] ) 
            ? max( 5, min( 1440, absint( $input['refresh_interval'] ) ) ) 
            : 30;

        // Handle cron token - generate if empty
        if ( ! empty( $input['cron_token'] ) ) {
            $sanitized['cron_token'] = sanitize_text_field( $input['cron_token'] );
        } elseif ( ! empty( $existing['cron_token'] ) ) {
            $sanitized['cron_token'] = $existing['cron_token'];
        } else {
            $sanitized['cron_token'] = wp_generate_password( 32, false );
        }

        // Clear cache when settings change
        $cache = new YMCA_IG_Feed_Cache();
        $cache->clear();

        return $sanitized;
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = get_option( $this->option_name, array() );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <?php $this->render_status_box(); ?>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'ymca_ig_feed_settings_group' );
                do_settings_sections( 'ymca-instagram-feed' );
                submit_button( __( 'Save Settings', 'ymca-instagram-feed' ) );
                ?>
            </form>

            <hr>

            <h2><?php _e( 'Shortcode Usage', 'ymca-instagram-feed' ); ?></h2>
            <p><?php _e( 'Add the Instagram feed to any page or post using:', 'ymca-instagram-feed' ); ?></p>
            <code>[ymca_instagram_feed]</code>

            <p><?php _e( 'Optional attributes:', 'ymca-instagram-feed' ); ?></p>
            <code>[ymca_instagram_feed count="12" columns="4"]</code>
        </div>
        <?php
    }

    /**
     * Render status/actions box
     */
    private function render_status_box() {
        $cache = new YMCA_IG_Feed_Cache();
        $cache_info = $cache->get_cache_info();
        $api = new YMCA_IG_Feed_API();
        $last_error = get_option( 'ymca_ig_feed_last_error', array() );
        $settings = get_option( $this->option_name, array() );
        $token_status = $api->get_token_status();
        ?>
        <div class="card" style="max-width: 600px; margin-bottom: 20px; padding: 15px;">
            <h2 style="margin-top: 0;"><?php _e( 'Status', 'ymca-instagram-feed' ); ?></h2>
            
            <table class="widefat" style="border: 0;">
                <tr>
                    <td><strong><?php _e( 'API Configured', 'ymca-instagram-feed' ); ?></strong></td>
                    <td>
                        <?php if ( $api->is_configured() ) : ?>
                            <span style="color: green;">&#10004; <?php _e( 'Yes', 'ymca-instagram-feed' ); ?></span>
                        <?php else : ?>
                            <span style="color: red;">&#10008; <?php _e( 'No', 'ymca-instagram-feed' ); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php _e( 'Token Refresh Enabled', 'ymca-instagram-feed' ); ?></strong></td>
                    <td>
                        <?php if ( $api->can_refresh_token() ) : ?>
                            <span style="color: green;">&#10004; <?php _e( 'Yes', 'ymca-instagram-feed' ); ?></span>
                        <?php else : ?>
                            <span style="color: orange;">&#10008; <?php _e( 'No - Add App ID and App Secret', 'ymca-instagram-feed' ); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php _e( 'Token Status', 'ymca-instagram-feed' ); ?></strong></td>
                    <td>
                        <?php 
                        $color = 'gray';
                        if ( $token_status['status'] === 'valid' ) $color = 'green';
                        if ( $token_status['status'] === 'expiring_soon' ) $color = 'orange';
                        if ( $token_status['status'] === 'expired' ) $color = 'red';
                        ?>
                        <span style="color: <?php echo $color; ?>;"><?php echo esc_html( $token_status['message'] ); ?></span>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php _e( 'Cached Posts', 'ymca-instagram-feed' ); ?></strong></td>
                    <td><?php echo esc_html( $cache_info['post_count'] ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e( 'Last Refresh', 'ymca-instagram-feed' ); ?></strong></td>
                    <td>
                        <?php 
                        echo $cache_info['last_refresh'] 
                            ? esc_html( $cache_info['last_refresh'] ) 
                            : __( 'Never', 'ymca-instagram-feed' ); 
                        ?>
                    </td>
                </tr>
                <?php if ( ! empty( $settings['token_updated'] ) ) : ?>
                <tr>
                    <td><strong><?php _e( 'Token Last Updated', 'ymca-instagram-feed' ); ?></strong></td>
                    <td><?php echo esc_html( $settings['token_updated'] ); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ( ! empty( $last_error['message'] ) ) : ?>
                <tr>
                    <td><strong><?php _e( 'Last Error', 'ymca-instagram-feed' ); ?></strong></td>
                    <td style="color: red;">
                        <?php echo esc_html( $last_error['message'] ); ?>
                        <br><small><?php echo esc_html( $last_error['time'] ); ?></small>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <p style="margin-bottom: 0;">
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ymca_ig_feed_test' ), 'ymca_ig_feed_test' ) ); ?>" class="button">
                    <?php _e( 'Test Connection', 'ymca-instagram-feed' ); ?>
                </a>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ymca_ig_feed_refresh' ), 'ymca_ig_feed_refresh' ) ); ?>" class="button button-primary">
                    <?php _e( 'Refresh Feed Now', 'ymca-instagram-feed' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Render API section description
     */
    public function render_api_section() {
        echo '<p>' . __( 'Enter your Instagram Graph API credentials. App ID and App Secret are required for automatic token refresh.', 'ymca-instagram-feed' ) . '</p>';
    }

    /**
     * Render filter section description
     */
    public function render_filter_section() {
        echo '<p>' . __( 'Only posts containing at least one of these hashtags will be displayed.', 'ymca-instagram-feed' ) . '</p>';
    }

    /**
     * Render text input field
     */
    public function render_text_field( $args ) {
        $settings = get_option( $this->option_name, array() );
        $field = $args['field'];
        $value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
        $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
        ?>
        <input 
            type="text" 
            name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            placeholder="<?php echo esc_attr( $placeholder ); ?>"
            class="regular-text"
        >
        <?php if ( isset( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif;
    }

    /**
     * Render password input field
     */
    public function render_password_field( $args ) {
        $settings = get_option( $this->option_name, array() );
        $field = $args['field'];
        $value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
        ?>
        <input 
            type="password" 
            name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
            autocomplete="off"
        >
        <?php if ( isset( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif;
    }

    /**
     * Render textarea field
     */
    public function render_textarea_field( $args ) {
        $settings = get_option( $this->option_name, array() );
        $field = $args['field'];
        $value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
        ?>
        <textarea 
            name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
            rows="3"
            class="large-text code"
        ><?php echo esc_textarea( $value ); ?></textarea>
        <?php if ( isset( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif;
    }

    /**
     * Render number input field
     */
    public function render_number_field( $args ) {
        $settings = get_option( $this->option_name, array() );
        $field = $args['field'];
        $value = isset( $settings[ $field ] ) ? $settings[ $field ] : $args['default'];
        $min = isset( $args['min'] ) ? $args['min'] : 0;
        $max = isset( $args['max'] ) ? $args['max'] : 9999;
        ?>
        <input 
            type="number" 
            name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            min="<?php echo esc_attr( $min ); ?>"
            max="<?php echo esc_attr( $max ); ?>"
            class="small-text"
        >
        <?php if ( isset( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif;
    }

    /**
     * Render select field
     */
    public function render_select_field( $args ) {
        $settings = get_option( $this->option_name, array() );
        $field = $args['field'];
        $value = isset( $settings[ $field ] ) ? $settings[ $field ] : $args['default'];
        $options = $args['options'];
        ?>
        <select name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>">
            <?php foreach ( $options as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ( isset( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif;
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field( $args ) {
        $settings = get_option( $this->option_name, array() );
        $field = $args['field'];
        $value = isset( $settings[ $field ] ) ? $settings[ $field ] : $args['default'];
        ?>
        <label>
            <input 
                type="checkbox" 
                name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
                value="1"
                <?php checked( $value, 1 ); ?>
            >
            <?php if ( isset( $args['description'] ) ) : ?>
                <?php echo esc_html( $args['description'] ); ?>
            <?php endif; ?>
        </label>
        <?php
    }

    /**
     * Render cron token field with URL display
     */
    public function render_cron_token_field( $args ) {
        $settings = get_option( $this->option_name, array() );
        $field = $args['field'];
        $value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
        
        // Generate token if not set
        if ( empty( $value ) ) {
            $value = wp_generate_password( 32, false );
        }

        $cron_url = rest_url( 'ymca-ig-feed/v1/refresh' ) . '?token=' . $value;
        ?>
        <input 
            type="text" 
            name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text code"
            readonly
        >
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <p class="description">
            <strong><?php _e( 'External Cron URL:', 'ymca-instagram-feed' ); ?></strong><br>
            <code style="word-break: break-all;"><?php echo esc_url( $cron_url ); ?></code>
        </p>
        <p class="description">
            <?php _e( 'For Pantheon: Add this URL to a server cron job or use a service like cron-job.org to call it every 15-30 minutes.', 'ymca-instagram-feed' ); ?>
        </p>
        <?php
    }

    /**
     * Handle manual refresh action
     */
    public function handle_manual_refresh() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'ymca-instagram-feed' ) );
        }

        check_admin_referer( 'ymca_ig_feed_refresh' );

        $cache = new YMCA_IG_Feed_Cache();
        $result = $cache->refresh();

        $redirect = add_query_arg(
            array(
                'page' => 'ymca-instagram-feed',
                'refreshed' => $result ? '1' : '0',
            ),
            admin_url( 'options-general.php' )
        );

        wp_redirect( $redirect );
        exit;
    }

    /**
     * Handle test connection action
     */
    public function handle_test_connection() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'ymca-instagram-feed' ) );
        }

        check_admin_referer( 'ymca_ig_feed_test' );

        $api = new YMCA_IG_Feed_API();
        $result = $api->test_connection();

        $redirect = add_query_arg(
            array(
                'page' => 'ymca-instagram-feed',
                'test' => $result['success'] ? '1' : '0',
                'test_message' => urlencode( $result['message'] ),
            ),
            admin_url( 'options-general.php' )
        );

        wp_redirect( $redirect );
        exit;
    }
}

// Initialize admin
new YMCA_IG_Feed_Admin();
