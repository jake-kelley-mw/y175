<?php
/**
 * Plugin Name: YMCA Instagram Feed
 * Description: Displays Instagram posts filtered by hashtags with server-side caching for optimal performance.
 * Version: 1.4.1
 * Author: Jake
 * Text Domain: ymca-instagram-feed
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'YMCA_IG_FEED_VERSION', '1.4.1' );
define( 'YMCA_IG_FEED_PATH', plugin_dir_path( __FILE__ ) );
define( 'YMCA_IG_FEED_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class
 */
class YMCA_Instagram_Feed {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once YMCA_IG_FEED_PATH . 'includes/class-instagram-api.php';
        require_once YMCA_IG_FEED_PATH . 'includes/class-feed-cache.php';
        require_once YMCA_IG_FEED_PATH . 'includes/class-feed-display.php';

        if ( is_admin() ) {
            require_once YMCA_IG_FEED_PATH . 'admin/settings-page.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register shortcode
        add_shortcode( 'ymca_instagram_feed', array( $this, 'render_shortcode' ) );

        // Enqueue front-end styles (only when shortcode is used)
        add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );

        // Cron hook for refreshing feed
        add_action( 'ymca_ig_feed_cron_refresh', array( $this, 'cron_refresh_feed' ) );

        // Self-heal: make sure the refresh event is scheduled even if activation
        // never ran (e.g. code deployed via git rather than (re)activated).
        add_action( 'init', array( $this, 'maybe_schedule_cron' ) );

        // REST API endpoint for external cron (Pantheon)
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // Schedule cron on plugin activation
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route( 'ymca-ig-feed/v1', '/refresh', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_refresh_feed' ),
            'permission_callback' => array( $this, 'verify_refresh_token' ),
        ) );
    }

    /**
     * Verify the refresh token for REST API
     */
    public function verify_refresh_token( $request ) {
        $options = get_option( 'ymca_ig_feed_settings', array() );
        $stored_token = isset( $options['cron_token'] ) ? $options['cron_token'] : '';
        
        // If no token set, deny access
        if ( empty( $stored_token ) ) {
            return false;
        }

        $provided_token = $request->get_param( 'token' );
        return hash_equals( $stored_token, $provided_token );
    }

    /**
     * REST API callback to refresh feed
     */
    public function rest_refresh_feed( $request ) {
        // Check/refresh token before refreshing feed
        $api = new YMCA_IG_Feed_API();
        if ( $api->can_refresh_token() ) {
            $api->maybe_refresh_token();
        }

        $cache = new YMCA_IG_Feed_Cache();
        $result = $cache->refresh();

        return new WP_REST_Response( array(
            'success' => $result,
            'time'    => current_time( 'mysql' ),
        ), $result ? 200 : 500 );
    }

    /**
     * Register styles (but don't enqueue yet)
     */
    public function register_styles() {
        wp_register_style(
            'ymca-instagram-feed',
            YMCA_IG_FEED_URL . 'assets/css/style.css',
            array(),
            YMCA_IG_FEED_VERSION
        );
    }

    /**
     * Render the shortcode
     * 
     * Usage: [ymca_instagram_feed]
     * Optional attributes: [ymca_instagram_feed count="12" columns="4"]
     */
    public function render_shortcode( $atts ) {
        // Enqueue styles only when shortcode is actually used
        wp_enqueue_style( 'ymca-instagram-feed' );

        $atts = shortcode_atts( array(
            'count'   => 0,  // 0 = use admin setting
            'columns' => 0,  // 0 = use admin setting
            'heading'       => '',
            'heading_color' => '',
        ), $atts, 'ymca_instagram_feed' );

        $display = new YMCA_IG_Feed_Display();
        return $display->render( $atts );
    }

    /**
     * Cron job to refresh the feed cache
     */
    public function cron_refresh_feed() {
        $cache = new YMCA_IG_Feed_Cache();
        $cache->refresh();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Get refresh interval from options (default 30 minutes)
        $options = get_option( 'ymca_ig_feed_settings', array() );
        $interval = isset( $options['refresh_interval'] ) ? intval( $options['refresh_interval'] ) : 30;

        // Clear any existing scheduled event
        $this->clear_scheduled_cron();

        // Schedule the cron event
        if ( ! wp_next_scheduled( 'ymca_ig_feed_cron_refresh' ) ) {
            wp_schedule_event( time(), 'ymca_ig_feed_interval', 'ymca_ig_feed_cron_refresh' );
        }
    }

    /**
     * Ensure the recurring refresh event is scheduled.
     *
     * Runs on every load (cheap no-op once scheduled). This is what recovers
     * sites where the event went missing because the cron_schedules filter
     * used to fatal (see add_cron_interval) and WP-Cron stopped running.
     */
    public function maybe_schedule_cron() {
        if ( ! wp_next_scheduled( 'ymca_ig_feed_cron_refresh' ) ) {
            wp_schedule_event( time(), 'ymca_ig_feed_interval', 'ymca_ig_feed_cron_refresh' );
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        $this->clear_scheduled_cron();
    }

    /**
     * Clear scheduled cron event
     */
    private function clear_scheduled_cron() {
        $timestamp = wp_next_scheduled( 'ymca_ig_feed_cron_refresh' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'ymca_ig_feed_cron_refresh' );
        }
    }

    /**
     * Add custom cron interval
     *
     * Static because it is registered as a global `cron_schedules` filter
     * (see bottom of file). Registering a non-static method via a class-name
     * callback previously caused a fatal TypeError on PHP 8, which killed
     * wp_get_schedules() and broke WP-Cron site-wide.
     */
    public static function add_cron_interval( $schedules ) {
        $options = get_option( 'ymca_ig_feed_settings', array() );
        $interval = isset( $options['refresh_interval'] ) ? intval( $options['refresh_interval'] ) : 30;

        $schedules['ymca_ig_feed_interval'] = array(
            'interval' => $interval * 60, // Convert minutes to seconds
            'display'  => sprintf( __( 'Every %d minutes', 'ymca-instagram-feed' ), $interval ),
        );

        return $schedules;
    }
}

// Add cron interval filter early (static callback — must not be an instance method)
add_filter( 'cron_schedules', array( 'YMCA_Instagram_Feed', 'add_cron_interval' ) );

// Initialize plugin
add_action( 'plugins_loaded', array( 'YMCA_Instagram_Feed', 'get_instance' ) );
