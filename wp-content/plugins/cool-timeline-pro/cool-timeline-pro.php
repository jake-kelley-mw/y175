<?php
/*
  Plugin Name: Cool Timeline Pro
  Plugin URI: https://cooltimeline.com/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=product_site&utm_content=dashboard_pro
  Description: Cool Timeline Pro, #1 WordPress timeline plugin to showcase your life story or your company history in a vertical or horizontal timeline format. You can also create a content timeline using your blog-posts or any post-type.
  Version: 4.9.1
  Author: Cool Plugins
  Author URI: https://coolplugins.net/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard_pro
  License: GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain: cool-timeline
 */
/** Configuration * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CTLPV' ) ) {
	define( 'CTLPV', '4.9.1' );
}
/*
 Defined constant for later use
 */
define( 'CTP_FILE', __FILE__ );
define( 'CTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTP_DEMO_URL', 'https://cooltimeline.com/demo/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard_pro' );
define( 'CTP_FEEDBACK_API', 'https://feedback.coolplugins.net/' );
if ( ! class_exists( 'CoolTimelinePro' ) ) {

	final class CoolTimelinePro {

		/**
		 * The unique instance of the plugin.
		 */
		private static $instance;

		/**
		 * Gets an instance of our plugin.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		 /**
		  * Construct the plugin objects
		  */
		private function __construct() {
             
				 $this->ctp_load_file();
				 add_action('ctl_cool_timeline_settings_save_after', array($this,'ctp_plugin_settings_saved'));
		
		}

		public function ctp_load_file(){
			
				if(!class_exists('CPFM_Feedback_Notice')){
						require_once __DIR__ . '/admin/feedback/cpfm-feedback-notice.php';		
					}

				require_once __DIR__ . '/includes/cron/class-ctp-cron.php';
			
		}

		public function ctp_plugin_settings_saved(){
			
			$data = get_option('cool_timeline_settings'); 

 			$opt_in = !empty($data['ctl_cpfm_feedback_data']) ? $data['ctl_cpfm_feedback_data']:'';
			
			if (!empty($opt_in)) {
				if(!wp_next_scheduled('ctl_extra_data_update')){
                wp_schedule_event(time(), 'every_30_days', 'ctl_extra_data_update');
				}
           
			}else {

				if (wp_next_scheduled('ctl_extra_data_update')) {
					wp_clear_scheduled_hook('ctl_extra_data_update');
				}
				
			}
		}

		

		public function registers() {
			$thisIns = self::$instance;
			// contain common function for plugin
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-helpers.php';

			if ( is_admin() ) {
				add_action( 'plugins_loaded', array( $thisIns, 'ctl_admin_files' ) );
				$thisIns->admin_registers();
			}

			// included all files
			add_action( 'plugins_loaded', array( $thisIns, 'ctl_frontend_files' ) );

			// Hooked plugin translation function
			add_action( 'init', array( $thisIns, 'ctl_load_textdomain' ) );

			/*
			@since version 2.8
			*/
			  // registering custom route for categories
			 add_action( 'rest_api_init', array( 'CTL_Helpers', 'ctl_register_routes' ) );
			  // flush_rewrite rules
			 add_action( 'init', array( $thisIns, 'clt_flush_rewrite_rules_after_activation' ) );

			// add_action( 'init', array( $thisIns,'clt_load_settings' ) );
			 add_action( 'admin_menu', array( $thisIns, 'ctl_add_new_item' ) );

			 $thisIns->includesOnInit();

		}
		public function ctl_add_new_item() {
			add_submenu_page( 'cool-plugins-timeline-addon', 'Add New Story', 'Add New Story', 'manage_options', 'post-new.php?post_type=cool_timeline', false, 15 );
			add_submenu_page( 'cool-plugins-timeline-addon', 'Categories', 'Categories', 'manage_options', 'edit-tags.php?taxonomy=ctl-stories&post_type=cool_timeline', false, 15 );
		}

		public function ctl_admin_files() {
			 require_once CTP_PLUGIN_DIR . 'admin/registration/registration-settings.php';
			require_once CTP_PLUGIN_DIR . 'admin/registration/init-api.php';
			require_once CTP_PLUGIN_DIR . 'admin/class-migration.php';
			/* Plugin Settings panel */
			$current_page = CTL_Helpers::ctl_get_ctp();
			// if($current_page!="cool_timeline" ){
			require_once CTP_PLUGIN_DIR . 'admin/ctl-framework/ctl-framework.php';
			// }

			require_once CTP_PLUGIN_DIR . 'admin/ctl-shortcode-generator.php';

			// Vc addon for timeline shortcode
			require_once CTP_PLUGIN_DIR . 'admin/class-cool-vc-addon.php';

			/*** Plugin review notice file */
			require_once CTP_PLUGIN_DIR . '/admin/notices/admin-notices.php';
			 new CoolVCAddon();
			 require_once __DIR__ . '/admin/timeline-addon-page/timeline-addon-page.php';
			cool_plugins_timeline_addons_settings_page( 'timeline', 'cool-plugins-timeline-addon', 'Timeline Addons', ' Timeline Addons', CTP_PLUGIN_URL . 'assets/images/cool-timeline-icon.svg' );
		}

		// includes files on plugin loaded hook
		public function ctl_frontend_files() {

			// Register cooltimeline post type for timeline
			require_once CTP_PLUGIN_DIR . 'admin/class-cool-timeline-posttype.php';
			new CoolTimelinePosttype();

			// initilize shortcodes
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-shortcode.php';
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-post-shortcode.php';
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-settings.php';
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-ajax-handler.php';

			/// user opt :---
			add_action('cpfm_register_notice', function () {
            
				if (!class_exists('CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
					return;
				}
	
		$notice = [
	
			'title' => __('Timeline Plugins by Cool Plugins', 'ctp'),
			'message' => __('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'cool-plugins-feedback'),
			'pages' => ['cool_timeline_settings', 'cool-plugins-timeline-addon'],
			'always_show_on' => ['cool_timeline_settings', 'cool-plugins-timeline-addon'],
			'plugin_name'=>'ctp'
		];
	
				
	
				CPFM_Feedback_Notice::cpfm_register_notice('cool-timeline', $notice);
	
					if (!isset($GLOBALS['cool_plugins_feedback'])) {
						$GLOBALS['cool_plugins_feedback'] = [];
					}
					
				
					$GLOBALS['cool_plugins_feedback']['cool-timeline'][] = $notice;
		   
			});
			add_action('cpfm_after_opt_in_ctp', function($category) {
				

				if ($category === 'cool-timeline') {
					$data = get_option('cool_timeline_settings'); 
					$data['ctl_cpfm_feedback_data'] = true;
					update_option('cool_timeline_settings', $data);
					
					require_once CTP_PLUGIN_DIR . '/includes/cron/class-ctp-cron.php';
					CTP_CRONJOB::ctp_send_data();
					
				}
			});


		}

		public function includesOnInit() {
			 // check if elementor is installed
			if ( file_exists( plugin_dir_path( __DIR__ ) . 'elementor/elementor.php' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				// Check if elementor is in the list of active plugins?
			}
			 require_once CTP_PLUGIN_DIR . 'includes/shortcode-block/ctl-block.php';

			 // cool timeline block
			 require CTP_PLUGIN_DIR . 'includes/cool-timeline-block/src/init.php';

		}

		public function admin_registers() {
			$thisIns = self::$instance;
			// Installation and uninstallation hooks
			register_activation_hook( __FILE__, array( $thisIns, 'ctp_activation_before' ) );
			register_deactivation_hook( __FILE__, array( $thisIns, 'ctp_deactivation_before' ) );

			// update attribute migration meta key.
			add_action( 'init', array( $thisIns, 'ctl_plugin_update' ) );

			// Adding plugin settings link
			$plugin_path = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin_path", array( $thisIns, 'plugin_settings_link' ) );
			// Fixed bridge theme confliction using this action hook
			add_action( 'wp_print_scripts', array( $thisIns, 'ctl_deregister_javascript' ), 100 );
			add_action( 'admin_enqueue_scripts', array( $thisIns, 'ctl_custom_order_js' ) );

			// add a tinymce button that generates our shortcode for the user
			add_action( 'after_setup_theme', array( $thisIns, 'ctl_add_tinymce' ) );

			add_action( 'admin_init', array( $thisIns, 'onInit' ) );
			add_action( 'wp_loaded', array( $this, 'ctl_plugin_demo_page' ) );
		}

		public function onInit() {
			/*** Plugin review notice file */
			ctl_create_admin_notice(
				array(
					'id'              => 'ctl_review_box',  // required and must be unique
					'slug'            => 'ctl',      // required in case of review box
					'review'          => true,     // required and set to be true for review box
					'review_url'      => esc_url( 'https://wordpress.org/support/plugin/cool-timeline/reviews/#new-post' ), // required
					'plugin_name'     => 'Cool Timeline PRO',    // required
					'review_interval' => 3,        // optional: this will display review notice
						 // after 5 days from the installation_time
					 // default is 3
				)
			);
		}

		// flushed rewrite rules after plugin activations
		function clt_flush_rewrite_rules_after_activation() {
			 // flush rewrite rules after activation
			if ( get_option( 'ctl_flush_rewrite_rules_flag' ) ) {
				flush_rewrite_rules();
				delete_option( 'ctl_flush_rewrite_rules_flag' );
			}
		}

		function ctl_custom_order_js( $hook ) {
			$current_page = CTL_Helpers::ctl_get_ctp();
			if ( $current_page != 'cool_timeline' ) {
				return;
			}
			wp_enqueue_script( 'ctl-admin-js', esc_url( CTP_PLUGIN_URL . 'assets/js/ctl_admin.js' ), array( 'jquery' ) );
					wp_localize_script(
			'ctl-admin-js',
			'ajax_object',
			array( 
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'ctl_change_story_order_nonce' )
			)
		);
		}



		/*
		Perform some actions on plugin activation time
		*/
		public function ctp_activation_before() {
			if ( file_exists( plugin_dir_path( __DIR__ ) . 'cool-timeline/cooltimeline.php' ) ) {
				 include_once ABSPATH . 'wp-admin/includes/plugin.php';
				if ( is_plugin_active( 'cool-timeline/cooltimeline.php' ) ) {
					deactivate_plugins( 'cool-timeline/cooltimeline.php' );
				}
			}

			$this->ctl_plugin_update();

			// for rating notice
			update_option( 'cool-timelne-pro-installDate', date( 'Y-m-d h:i:s' ) );
			add_option( 'cool-timeline-pro-already-rated', 'no' );
			update_option( 'ctl_flush_rewrite_rules_flag', true );
			update_option( 'cool-timelne-pro-v', CTLPV );
			update_option( 'cool-timelne-plugin-type', 'PRO' );
			update_option( 'ctl_demo_page', true );
			$ctl_settings = get_option( 'cool_timeline_options' );

			if ( is_array( $ctl_settings ) && ! empty( $ctl_settings ) ) {
				if ( isset( $ctl_settings['enable_navigation'] ) && in_array( 'enable_navigation', $ctl_settings ) ) {
					update_option( 'ctl-can-migrate', 'no' );
				} else {
					update_option( 'ctl-can-migrate', 'yes' );
				}
			} else {
				update_option( 'ctl-can-migrate', 'yes' );
			}


			if (!get_option( 'ctp_initial_save_version' ) ) {
				add_option( 'ctp_initial_save_version', CTLPV );
			}
	
			if(!get_option( 'ctp-install-date' ) ) {
				add_option( 'ctp-install-date', gmdate('Y-m-d h:i:s') );
			}


			$data = get_option('cool_timeline_settings'); 

			$opt_in = !empty($data['ctl_cpfm_feedback_data']) ? $data['ctl_cpfm_feedback_data']:'';
			
		   if($opt_in){

			if (!wp_next_scheduled('ctl_extra_data_update')) {
	
				wp_schedule_event(time(), 'every_30_days', 'ctl_extra_data_update');
	
			}
		   }
		}


		/**
		 * Update data after update the plugin
		 */
		public function ctl_plugin_update() {
			$ctl_version        = get_option( 'cool-timelne-pro-v', false );
			$ctl_attr_migration = get_option( 'ctl-migration-free', false );
			$ctl_free_version   = get_option( 'cool-free-timeline-v', false );

			if ( ! $ctl_version && $ctl_free_version ) {
				update_option( 'ctl-migration-free', true );
			};
			if ( ( version_compare( $ctl_version, '4.5', '<' ) || ( ! $ctl_version && $ctl_free_version ) ) && ! $ctl_attr_migration ) {
				update_option( 'ctl-attribute-migration', true );
			};
		}

		/*
			Loading translation files of plugin
		 */

		function ctl_load_textdomain() {
			load_plugin_textdomain( 'cool-timeline', false, basename( dirname( __FILE__ ) ) . '/languages/' );
			if (!get_option( 'ctp_initial_save_version' ) ) {
				add_option( 'ctp_initial_save_version', CTLPV );
			}
	
			if(!get_option( 'ctp-install-date' ) ) {
				add_option( 'ctp-install-date', gmdate('Y-m-d h:i:s') );
			}
			if (is_admin()) {
				require_once CTP_PLUGIN_DIR . 'admin/ctl-admin-settings.php';
				require_once CTP_PLUGIN_DIR . 'admin/ctl-meta-fields.php';		
			}

			$settingsObj = new CTL_Settings();
			CTL_Shortcode::get_instance( $settingsObj );
			CTL_Post_Shortcode::get_instance( $settingsObj );
			CTL_Ajax_Handler::get_instance( $settingsObj );
		}

		// Add the settings link to the plugins page
		function plugin_settings_link( $links ) {
			  $settings_link = '<a href="admin.php?page=cool_timeline_settings">Settings</a>';
			  array_unshift( $links, $settings_link );
			  return $links;
		}

		function ctl_plugin_demo_page() {
			$show_demo_page = get_option( 'ctl_demo_page', false );
			if ( $show_demo_page ) {
				update_option( 'ctl_demo_page', false );
				$timeline_demo_page = 'admin.php?page=cool_timeline_settings#tab=get-started';
				wp_safe_redirect( $timeline_demo_page );
				exit; // Ensure no further code is executed after redirect
			}
		}

		/*
		* Fixed Bridge theme confliction
		*/
		function ctl_deregister_javascript() {

			if ( is_admin() ) {
				global $post;
				if ( function_exists( 'get_current_screen' ) ) {
					$screen = get_current_screen();
					if ( $screen != null && $screen->base == 'toplevel_page_cool_timeline_page' ) {
						wp_deregister_script( 'bridge-admin-default' );
						wp_deregister_script( 'default' );
						wp_deregister_script( 'subway-admin-default' );
						wp_deregister_script( 'strata-admin-default' );
						wp_deregister_script( 'stockholm-admin-default' );// for Stockholm Theme
					}
				}
				if ( isset( $post ) && isset( $post->post_type ) && $post->post_type == 'cool_timeline' ) {
					wp_deregister_script( 'acf-timepicker' );
					wp_deregister_script( 'acf-input' ); // datepicker translaton issue
					wp_deregister_script( 'acf' ); // datepicker translaton issue
					// wp_deregister_script('lvca-timepicker-addon'); // datepicker confict Livemesh Addons for WPBakery Page Builder plugin
					// wp_deregister_style('lvca-timepicker-addon-css');// datepicker confict Livemesh Addons for WPBakery Page Builder plugin
					wp_deregister_script( 'thrive-admin-datetime-picker' ); // datepicker conflict with Rise theme
					wp_deregister_script( 'et_bfb_admin_date_addon_js' ); // datepicker conflict with Divi theme
					wp_deregister_script( 'zeen-engine-admin-vendors-js' ); // datepicker conflict with zeen engine plugin
				}
			}
		}

		/*
			Adding shortcode generator in TinyMCE editor
		 */
		public function ctl_add_tinymce() {
			global $typenow;
			$thisIns = self::$instance;
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				  return;
			}
		}

		public function ctp_deactivation_before(){
			if (wp_next_scheduled('ctl_extra_data_update')) {
				wp_clear_scheduled_hook('ctl_extra_data_update');
			}
		}

		 public static function ctp_get_user_info() {

			global $wpdb;
		
			// Server and WP environment details
			$server_info = [
				'server_software'        => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field($_SERVER['SERVER_SOFTWARE']) : 'N/A',
				'mysql_version'          => $wpdb ? sanitize_text_field($wpdb->get_var("SELECT VERSION()")) : 'N/A',
				'php_version'            => sanitize_text_field(phpversion() ?: 'N/A'),
				'wp_version'             => sanitize_text_field(get_bloginfo('version') ?: 'N/A'),
				'wp_debug'               => (defined('WP_DEBUG') && WP_DEBUG) ? 'Enabled' : 'Disabled',
				'wp_memory_limit'        => sanitize_text_field(ini_get('memory_limit') ?: 'N/A'),
				'wp_max_upload_size'     => sanitize_text_field(ini_get('upload_max_filesize') ?: 'N/A'),
				'wp_permalink_structure' => sanitize_text_field(get_option('permalink_structure') ?: 'Default'),
				'wp_multisite'           => is_multisite() ? 'Enabled' : 'Disabled',
				'wp_language'            => sanitize_text_field(get_option('WPLANG') ?: get_locale()),
				'wp_prefix'              => isset($wpdb->prefix) ? sanitize_key($wpdb->prefix) : 'N/A',
			];
		
			// Theme details
			$theme = wp_get_theme();
			$theme_data = [
				'name'      => sanitize_text_field($theme->get('Name')),
				'version'   => sanitize_text_field($theme->get('Version')),
				'theme_uri' => esc_url($theme->get('ThemeURI')),
			];
		
	
			if (!function_exists('get_plugins')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if (!function_exists('get_plugin_data')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
		
	
			$plugin_data = [];
			$active_plugins = get_option('active_plugins', []);
		
			foreach ($active_plugins as $plugin_path) {
				$plugin_file = WP_PLUGIN_DIR . '/' . ltrim($plugin_path, '/');
		
				if (file_exists($plugin_file)) {
	
					$plugin_info = get_plugin_data($plugin_file, false, false);
					$plugin_url = !empty($plugin_info['PluginURI']) ? esc_url($plugin_info['PluginURI']) : (!empty($plugin_info['AuthorURI']) ? esc_url($plugin_info['AuthorURI']) : 'N/A');
					$plugin_data[] = [
						'name'       => sanitize_text_field($plugin_info['Name']),
						'version'    => sanitize_text_field($plugin_info['Version']),
						'plugin_uri' => !empty($plugin_url) ? $plugin_url : 'N/A',
					];
				}
			}
		
			return [
				'server_info'   => $server_info,
				'extra_details' => [
					'wp_theme'       => $theme_data,
					'active_plugins' => $plugin_data,
				],
			];
		}
	}//end class

	
	 
}

// instantiate the plugin class

$ctl = CoolTimelinePro::get_instance();
$ctl->registers();
/*** THANKS - CoolPlugins.net ) */
