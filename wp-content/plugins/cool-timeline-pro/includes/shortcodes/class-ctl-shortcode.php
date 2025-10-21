<?php
/**
 * CTL Shortcode.
 *
 * @package CTL
 */

/**
 * Do not access the page directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create shortcode class if class not exists
 */
if ( ! class_exists( 'CTL_Shortcode' ) ) {

	/**
	 * Class Shortcode.
	 */
	class CTL_Shortcode {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Shortcode assets object variable
		 *
		 * @var object
		 */
		public $ctl_asset_obj = array();

		/**
		 * Configure settings array
		 *
		 * @var settings
		 */
		public $settings = array();

		/**
		 * Configure config layout array
		 *
		 * @var config_layout
		 */
		public $config_layout = array();

		/**
		 * Shortcode attribute array configure
		 *
		 * @var attribute
		 */
		public $attributes = array();

		/**
		 * Gets an instance of our plugin.
		 *
		 * @param object $settings_obj timeline settings object.
		 */
		public static function get_instance( $settings_obj ) {
			if ( null === self::$instance ) {
				self::$instance = new self( $settings_obj );
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @param object $settings_obj timeline settings object.
		 */
		public function __construct( $settings_obj ) {
			
			$this->settings = $settings_obj->ctl_get_settings();
    

			$this->ctl_register_shortcode();			
			$this->ctl_layout_loader();
			$this->ctl_asset_obj = new CTL_Assets_Loader();
		}	

		/**
		 * Load all layouts files
		 */
		public function ctl_layout_loader() {
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-loop-helpers.php';
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-layout-manager.php';
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-query-builder.php';
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-assets-loader.php';
			require_once CTP_PLUGIN_DIR . 'includes/shortcodes/class-ctl-styles-generator.php';
		}

		/**
		 * Add shortcode
		 */
		public function ctl_register_shortcode() {
			add_shortcode( 'cool-timeline', array( $this, 'ctl_shortcode_handler' ) );
		}

		/**
		 * Set shortcode attribute
		 * render shortcode block
		 *
		 * @param array  $atts shortcode attributes.
		 * @param string $content shortcode content.
		 */
		public function ctl_shortcode_handler( $atts, $content = null ) {
			/**
			 * Demo shortcode attributes
			 * [cool-timeline category="" layout="vertical" designs="elegant" skin="dark" show-posts="3" date-format="F j" icons="NO" animation="none" story-content="short" based="default" compact-ele-pos="main-date" pagination="default" filters="no" line-filling="false" order="DESC"]
			 * [cool-timeline based="default" layout="default" designs="default" skin="default" date-format="F j" pagination="default" filters="no" icons="NO" animations="none" show-posts="10" story-content="short" order="DESC"]
			 */

			// animations,animation.

			$default_attr = array(
				'based'               => 'default',
				'category'            => 0,
				'story-content'       => '',
				'show-posts'          => '',
				'pagination'          => 'default',
				'order'               => '',
				'date-format'         => '',
				'icons'               => '',
				'animation'           => '',
				'filters'             => 'no',
				'filter-categories'   => '',

				// config layout/styles.
				'layout'              => 'default',
				'skin'                => '',
				'designs'             => 'design-1',

				// horizontal layout settings.
				'items'               => '',
				'autoplay'            => 'false',
				'autoplay-speed'      => 3000,
				'start-on'            => 0,

				// compact layout settings.
				'compact-ele-pos'     => 'main-date',

				// Common settings.
				'year-label'          => '',
				'year-navigation'     => '',
				'navigation-position' => 'right',
				'type'                => '', // deprecated.
				'line-filling'        => 'false',

				// New attribute story-date.
				'story-date'          => 'show',
				'custom-date-format'  => '',
				'read-more'           => 'show',
				'content-length'      => '50',
				'timeline-title'      => '',
				'navigation-style'    => 'style-1',
			);

			// Set shortcode attribute.
			$filter_attr                 = $this->ctl_attr_filter( $atts );
			$this->attributes            = shortcode_atts( $default_attr, $filter_attr );
			$this->attributes['designs'] = 'design-1' === $this->attributes['designs'] && 'horizontal' !== $this->attributes['layout'] ? 'default' : $this->attributes['designs'];

			// save page if for later use in enqueue function.
			CTL_Helpers::has_shortcode_added( get_the_ID(), $this->attributes['layout'] );

			// Timeline attribute migration with cooltimeline settings.
			$settings_migration = get_option( 'ctl-attribute-migration', false );
			if ( $settings_migration ) {
				$this->setting_migration( $atts );
			}

			// Shortcode type define.
			$this->attributes['ctl_type'] = 'story_timeline';

			// Shortcode animation attribute migration with animations attribute.
			$this->attributes['animation'] = is_array( $atts ) && array_key_exists( 'animations', $atts ) ? sanitize_text_field( $atts['animations'] ) : $this->attributes['animation'];

			// load timeline global assets.
			$this->ctl_asset_obj->ctl_global_assets( $this->attributes );

			$this->attributes['config'] = $this->ctl_config_layouts( $this->attributes );

			$year_label_visibility               = 'horizontal' !== $this->attributes['layout'] ? 'show' : 'hide';
			$this->attributes['year-label']      = $this->ctl_set_val( $this->attributes['year-label'], $year_label_visibility );
			$this->attributes['year-navigation'] = $this->ctl_set_val( $this->attributes['year-navigation'], $year_label_visibility );

			// pass attribute to load more JS file if load more enabled.
			if ( 'ajax_load_more' === $this->attributes['pagination'] || 'yes' === $this->attributes['filters'] ) {
				$object_id = 'config_' . $this->attributes['config']['wrapper_id'];
				wp_localize_script(
					'ctl_load_more',
					$object_id,
					array(
						'attributes' => wp_json_encode( $this->attributes ),
					)
				);
			}

			$paged = 1;
			// include pagination arguments.
			if ( get_query_var( 'paged' ) ) {
				$paged = get_query_var( 'paged' );
			} elseif ( get_query_var( 'page' ) ) {
				$paged = get_query_var( 'page' );
			}
			$this->attributes['paged'] = $paged;
			$wp_query                  = CTL_Query_Builder::story_get_query( $this->attributes, $this->settings );

			// timeline html based on layout.
			$layout_manager_object = new CTL_Layout_Manager( $this->attributes, $wp_query, $this->settings );
			$output                = $layout_manager_object->render_layout();
			return $output;
		}

		/**
		 * Migrates old setting values.
		 *
		 * @param array $atts shortcode attributes.
		 */
		public function setting_migration( $atts ) {
			$valid_icons        = array( 'yes', 'YES', 'Yes', 'icon' );
			$old_icons_value    = array( 'no', 'NO', 'No', 'yes', 'YES', 'Yes', 'icon' );
			$settings_update    = isset( $atts['setting-migration'] ) ? filter_var( $atts['setting-migration'], FILTER_VALIDATE_BOOLEAN ) : false;
			$default_pagination = 'yes' === $this->settings['pagination'] ? 'default' : 'off';
			$layout             = $this->attributes['layout'];
			$ctl_free           = get_option( 'ctl-migration-free', false );

			if ( ! isset( $atts['layout'] ) && isset( $atts['type'] ) ) {
				$this->attributes['layout'] = sanitize_text_field( $atts['type'] );
			}

			$old_attributes = array(
				'date-format'        => isset( $atts['date-format'] ) && ! empty( $atts['date-format'] ) && 'default' !== $atts['date-format'] ? $atts['date-format'] : $this->settings['date_format'],
				'custom-date-format' => $this->settings['custom_date_format'],
				'read-more'          => in_array( $this->settings['display_readmore'], array( 'yes', 'Yes' ), true ) ? 'show' : 'hide',
				'content-length'     => $this->settings['story_content_length'],
				'story-date'         => in_array( $this->settings['disable_months'], array( 'yes', 'Yes' ), true ) ? 'hide' : 'show',
				'pagination'         => isset( $atts['pagination'] ) && 'ajax_load_more' === $atts['pagination'] ? 'ajax_load_more' : $default_pagination,
				'icons'              => isset( $atts['icons'] ) && 'none' === $atts['icons'] ? 'none' : ( isset( $atts['icons'] ) && in_array( $atts['icons'], $valid_icons, true ) ? 'icon' : 'dot' ),
				'timeline-title'     => 'yes' === $this->settings['timeline_title_enable'] ? $this->settings['timeline_title'] : '',
			);

			if ( 'horizontal' !== $layout ) {
				$old_attributes['year-navigation']     = 'yes' === $this->settings['year_navigation'] ? 'show' : 'hide';
				$old_attributes['year-label']          = $this->settings['year_label'] ? 'show' : 'hide';
				$old_attributes['navigation-position'] = $this->settings['year_navigation_position'];
			} else {
				$old_attributes['filters']           = 'no';
				$old_attributes['filter-categories'] = '';
			}

			if ( 'design-1' === $this->attributes['designs'] && 'horizontal' === $layout ) {
				$this->attributes['designs'] = ( $settings_update && 'horizontal' === $layout && ! $ctl_free ) ? 'default' : 'design-1';
			} elseif ( 'default' === $this->attributes['designs'] && 'horizontal' === $layout && $ctl_free ) {
				$this->attributes['designs'] = 'design-1';
			}

			foreach ( $old_attributes as $key => $value ) {
				if ( ! isset( $atts[ $key ] ) || $settings_update ) {
					$this->attributes[ $key ] = $value;
				}

				if ( isset( $atts['icons'] ) && 'icons' === $key && in_array( $atts['icons'], $old_icons_value, true ) ) {
					$this->attributes[ $key ] = $value;
				}
				if ( isset( $atts['date-format'] ) && 'default' === $atts['date-format'] && 'date-format' === $key ) {
					$this->attributes[ $key ] = $value;
				}
			}
		}

		/**
		 * Configure layout based on the settings.
		 *
		 * @param object $attributes shortcode attributes.
		 */
		public function ctl_config_layouts( $attributes ) {
			$config_arr                   = array();
			$main_wrapper_cls             = 'ctl-wrapper';
			$wrapper_cls                  = array( 'cool-timeline-wrapper' );
			$container_cls                = array( '' );
			$story_cls                    = array();
			$ctl_animation                = '';
			$config_arr['data_attribute'] = array();

			$layout        = $this->ctl_set_val( $attributes['layout'], 'default' );
			$active_design = $this->ctl_set_val( $attributes['designs'], 'default' );
			$skin          = $this->ctl_set_val( $attributes['skin'], 'default' );

			if ( isset( $attributes['type'] ) && ! empty( $attributes['type'] ) ) {
				if ( 'default' === $attributes['type'] ) {
					$layout = $attributes['layout'];
				} else {
					$layout = $attributes['type'];
				}
			}

			// create wrapper class based upon layout.
			switch ( $layout ) {
				case 'one-side':
					$wrapper_cls[]                 = 'ctl-one-sided';
					$wrapper_cls['ctl_design_cls'] = 'ctl-vertical-wrapper';
					break;
				case 'compact':
					$wrapper_cls[]                 = 'ctl-both-sided';
					$wrapper_cls[]                 = 'ctl-vertical-wrapper';
					$wrapper_cls['ctl_design_cls'] = 'ctl-compact-wrapper';
					break;
				case 'horizontal':
					$wrapper_cls[]                 = 'ctl-horizontal';
					$wrapper_cls['ctl_design_cls'] = 'ctl-horizontal-wrapper';
					$wrapper_cls[]                 = 'ctl-horizontal-timeline';
					break;
				default:
					$wrapper_cls[]                 = 'ctl-both-sided';
					$wrapper_cls['ctl_design_cls'] = 'ctl-vertical-wrapper';
					break;
			}

			$wrapper_cls['ctl_layout_cls'] = 'ctl-' . $active_design;

			if ( 'light' === $skin ) {
				$wrapper_cls[]   = 'light-skin';
				$container_cls[] = 'light-skin-timeline';
				$story_cls[]     = 'light-skin-story';
			} elseif ( 'dark' === $skin ) {
				$wrapper_cls[]   = 'dark-skin';
				$container_cls[] = 'dark-skin-timeline';
				$story_cls[]     = 'dark-skin-story';
			}

			// create here a deprcated logic.

			// Deprecated animations attribute setting if the user has configured the shortcode with animations attribute.
			if ( ! empty( $attributes['animations'] ) ) {
				$ctl_animation = sanitize_text_field( $attributes['animations'] );
			} elseif ( ! empty( $attributes['animation'] ) ) {
				$ctl_animation = sanitize_text_field( $attributes['animation'] );
			}

			if ( in_array( $ctl_animation, CTL_Helpers::get_deprecated_animations(), true ) ) {
				$ctl_animation = 'fade-in';
			}

			if ( 'horizontal' === $layout ) {
				$load_more                      = 'ajax_load_more' === $this->attributes['pagination'] ? 'yes' : '';
				$config_arr['data_attribute'][] = 'data-items="' . esc_attr( $this->attributes['items'] ) . '"';
				$config_arr['data_attribute'][] = 'data-start-on="' . esc_attr( $this->attributes['start-on'] ) . '"';
				$config_arr['data_attribute'][] = 'data-autoplay="' . esc_attr( $this->attributes['autoplay'] ) . '"';
				$config_arr['data_attribute'][] = 'data-autoplay-speed="' . esc_attr( $this->attributes['autoplay-speed'] ) . '"';
				$config_arr['data_attribute'][] = 'data-load="' . esc_attr( $load_more ) . '"';
			}

			if ( is_rtl() ) {
				$config_arr['data_attribute'][] = 'data-dir="rtl"';
			}

			$config_arr['layout']           = $layout;
			$config_arr['active_year']      = '';
			$config_arr['active_design']    = $active_design;
			$config_arr['active_skin']      = $skin;
			$config_arr['main_wrp_cls']     = $main_wrapper_cls;
			$config_arr['wrapper_cls']      = $wrapper_cls;
			$config_arr['wrapper_id']       = wp_unique_id( 'cool_timeline_' );
			$config_arr['container_cls']    = $container_cls;
			$config_arr['story_cls']        = $story_cls;
			$config_arr['animation']        = ! empty( $ctl_animation ) ? $ctl_animation : 'none';
			$config_arr['data_attribute'][] = 'data-nav="' . esc_attr( $this->attributes['year-navigation'] ) . '"';
			$config_arr['data_attribute'][] = 'data-line-filling="' . esc_attr( $this->attributes['line-filling'] ) . '"';
			$config_arr['data_attribute'][] = 'data-nav-pos="' . esc_attr( $this->attributes['navigation-position'] ) . '"';
			if ( 'show' === $this->attributes['year-navigation'] && 'horizontal' !== $layout ) {
				$config_arr['data_attribute'][] = 'data-nav-style="' . esc_attr( $this->attributes['navigation-style'] ) . '"';
			}
			return $config_arr;
		}

		/**
		 * Filter All Shortcode Attribute
		 *
		 * @param object $attr shortcode attribute value.
		 */
		public function ctl_attr_filter( $attr ) {
			if ( is_array( $attr ) ) {
				$symbols    = array( '*', '(', ')', '[', ']', '{', '}', '"', "'", '\\', '/', ';', '$', '<', '>', '.', '”', '#', '!', '@', '^' );
				$attributes = array();
				foreach ( $attr as $key => $values ) {
					if ( 'date-format' === $key || 'custom-date-format' === $key || 'timeline-title' === $key ) {
						$attributes[ $key ] = sanitize_text_field( $values );
					} else {
						$value              = str_replace( $symbols, '', $values );
						$value              = esc_html( $value );
						$value              = preg_replace( '/\s+/', '', $value );
						$attributes[ $key ] = $value;
					}
				}
				return $attributes;
			} else {
				return $attr;
			}
		}

		/**
		 * Configure default values
		 *
		 * @param string $value shortcode attribute new value.
		 * @param string $default shortcode attribute default value.
		 */
		public function ctl_set_val( $value, $default ) {
			if ( isset( $value ) && ! empty( $value ) ) {
				return sanitize_text_field( $value );
			}
			return $default;
		}
	}

}
