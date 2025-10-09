<?php
/**
 * Plugin Name: Find a Y Location Finder
 * Description: YMCA location finder with smart CSV/Excel import and update capabilities
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

class Find_A_Y_Plugin {
    
    private $table_name;
    private $version = '1.0.0';
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ymca_locations';
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_find_ymca_locations', array($this, 'ajax_find_locations'));
        add_action('wp_ajax_nopriv_find_ymca_locations', array($this, 'ajax_find_locations'));
        add_action('wp_ajax_preview_ymca_import', array($this, 'ajax_preview_import'));
        add_action('wp_ajax_process_ymca_import', array($this, 'ajax_process_import'));
        add_action('wp_ajax_save_google_api_key', array($this, 'ajax_save_api_key'));
        add_shortcode('find_a_y', array($this, 'render_search_form'));
    }
    
    public function activate() {
        $this->create_database_table();
        flush_rewrite_rules();
    }
    
    private function create_database_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            y_name varchar(255) NOT NULL,
            address varchar(255) NOT NULL,
            address2 varchar(255) DEFAULT NULL,
            city varchar(100) NOT NULL,
            state varchar(50) NOT NULL,
            zip_code varchar(20) NOT NULL,
            website varchar(500) DEFAULT NULL,
            latitude decimal(10, 8) DEFAULT NULL,
            longitude decimal(11, 8) DEFAULT NULL,
            geocoded tinyint(1) DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_zip (zip_code),
            KEY idx_location (latitude, longitude),
            KEY idx_name_zip (y_name(100), zip_code),
            KEY idx_state_city (state, city)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Find a Y Locations',
            'Find a Y',
            'manage_options',
            'find-a-y',
            array($this, 'render_admin_page'),
            'dashicons-location',
            30
        );
        
        add_submenu_page(
            'find-a-y',
            'Import/Update Locations',
            'Import/Update',
            'manage_options',
            'find-a-y-import',
            array($this, 'render_import_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'find-a-y') === false) {
            return;
        }
        
        wp_enqueue_style('find-a-y-admin', plugins_url('css/admin-style.css', __FILE__), array(), $this->version);
        wp_enqueue_script('find-a-y-admin', plugins_url('js/admin-script.js', __FILE__), array('jquery'), $this->version, true);
        
        wp_localize_script('find-a-y-admin', 'findAYAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('find_a_y_admin')
        ));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('find-a-y-frontend', plugins_url('css/frontend-style.css', __FILE__), array(), $this->version);
        wp_enqueue_script('find-a-y-frontend', plugins_url('js/frontend-script.js', __FILE__), array('jquery'), $this->version, true);
        
        wp_localize_script('find-a-y-frontend', 'findAY', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('find_a_y_search')
        ));
    }
    
    public function render_admin_page() {
        global $wpdb;
        
        $total_locations = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $geocoded_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE geocoded = 1");
        $missing_websites = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE website IS NULL OR website = ''");
        
        ?>
        <div class="wrap">
            <h1>Find a Y - Location Management</h1>
            
            <div class="find-a-y-stats">
                <div class="stat-box">
                    <h3><?php echo number_format($total_locations); ?></h3>
                    <p>Total Locations</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo number_format($geocoded_count); ?></h3>
                    <p>Geocoded Locations</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo number_format($total_locations - $geocoded_count); ?></h3>
                    <p>Need Geocoding</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo number_format($missing_websites); ?></h3>
                    <p>Missing Websites</p>
                </div>
            </div>
            
            <h2>Recent Locations</h2>
            <?php
            $recent = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY last_updated DESC LIMIT 10");
            
            if ($recent) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Y Name</th><th>City</th><th>State</th><th>ZIP</th><th>Geocoded</th><th>Last Updated</th></tr></thead>';
                echo '<tbody>';
                foreach ($recent as $location) {
                    $geocoded = $location->geocoded ? '✓' : '✗';
                    echo "<tr>
                        <td>{$location->y_name}</td>
                        <td>{$location->city}</td>
                        <td>{$location->state}</td>
                        <td>{$location->zip_code}</td>
                        <td>{$geocoded}</td>
                        <td>{$location->last_updated}</td>
                    </tr>";
                }
                echo '</tbody></table>';
            }
            ?>
            
            <h2>Shortcode Usage</h2>
            <p>Add the location finder to any page using: <code>[find_a_y]</code></p>
        </div>
        <?php
    }
    
    public function render_import_page() {
        include plugin_dir_path(__FILE__) . 'includes/import-page.php';
    }
    
    public function ajax_save_api_key() {
        check_ajax_referer('find_a_y_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        update_option('find_a_y_google_api_key', $api_key);
        
        wp_send_json_success('API key saved');
    }
    
    public function ajax_preview_import() {
        check_ajax_referer('find_a_y_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!isset($_FILES['import_file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        require_once plugin_dir_path(__FILE__) . 'includes/class-import-handler.php';
        $importer = new Find_A_Y_Import_Handler($this->table_name);
        
        $result = $importer->preview_import($_FILES['import_file']);
        
        if ($result['success']) {
            wp_