<?php
/**
 * CTL Settings.
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
if ( ! class_exists( 'CTL_Settings' ) ) {

	/**
	 * Class Shortcode.
	 */
	class CTL_Settings {

		/**
		 * Configure settings array
		 *
		 * @var settings
		 */
		public $settings = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->settings = get_option( 'cool_timeline_settings' );

			// Validate settings to prevent security issues
			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}
		}

		/**
		 * Get Settings and set default values
		 */
		public function ctl_get_settings() {
			$settings     = array();
			$settings_arr = $this->settings;

			$settings['timeline_bg_color'] = isset( $settings_arr['timeline_bg_color'] ) ? sanitize_hex_color( $settings_arr['timeline_bg_color'] ) : '';

			$settings['timeline_title_enable'] = isset( $settings_arr['timeline_header']['display_title'] ) ? sanitize_text_field( $settings_arr['timeline_header']['display_title'] ) : 'yes';

			$settings['timeline_title'] = isset( $settings_arr['timeline_header']['title_text'] ) ? $settings_arr['timeline_header']['title_text'] : '';

			$settings['timeline_image'] = isset( $settings_arr['timeline_header']['user_avatar'] ) ? $settings_arr['timeline_header']['user_avatar'] : '';

			$settings['default_icon']  = isset( $settings_arr['story_content_settings']['default_icon'] ) && ! empty( $settings_arr['story_content_settings']['default_icon'] ) ? sanitize_text_field( $settings_arr['story_content_settings']['default_icon'] ) : '';
			$settings['post_per_page'] = isset( $settings_arr['post_per_page'] ) ? absint( $settings_arr['post_per_page'] ) : 0;

			$settings['story_content'] = isset( $settings_arr['desc_type'] ) ? sanitize_text_field( $settings_arr['desc_type'] ) : '';

			$settings['story_content_length'] = isset( $settings_arr['story_content_settings']['content_length'] ) ? absint( $settings_arr['story_content_settings']['content_length'] ) : 50;

			$settings['story_orders'] = isset( $settings_arr['posts_orders'] ) ? sanitize_text_field( $settings_arr['posts_orders'] ) : 'DESC';

			$settings['disable_months'] = isset( $settings_arr['story_date_settings']['disable_months'] ) ? sanitize_text_field( $settings_arr['story_date_settings']['disable_months'] ) : 'no';

			$settings['year_label'] = isset( $settings_arr['story_date_settings']['year_label_visibility'] ) ? sanitize_text_field( $settings_arr['story_date_settings']['year_label_visibility'] ) : 1;

			$settings['date_format'] = isset( $settings_arr['story_date_settings']['ctl_date_formats'] ) ? sanitize_text_field( $settings_arr['story_date_settings']['ctl_date_formats'] ) : 'M d';

			$settings['custom_date_format'] = isset( $settings_arr['story_date_settings']['custom_date_formats'] ) ? sanitize_text_field( $settings_arr['story_date_settings']['custom_date_formats'] ) : 'M d';

			$settings['year_navigation'] = isset( $settings_arr['navigation_settings']['enable_navigation'] ) ? sanitize_text_field( $settings_arr['navigation_settings']['enable_navigation'] ) : 'yes';

			$settings['year_navigation_position'] = isset( $settings_arr['navigation_settings']['navigation_position'] ) ? sanitize_text_field( $settings_arr['navigation_settings']['navigation_position'] ) : 'right';

			$settings['pagination'] = isset( $settings_arr['pagination_settings']['enable_pagination'] ) ? sanitize_text_field( $settings_arr['pagination_settings']['enable_pagination'] ) : 'yes';

			$settings['stories_images_link'] = isset( $settings_arr['story_media_settings']['stories_images'] ) ? sanitize_text_field( $settings_arr['story_media_settings']['stories_images'] ) : 'popup';

			$settings['story_link_target'] = isset( $settings_arr['story_content_settings']['story_link_target'] ) ? sanitize_text_field( $settings_arr['story_content_settings']['story_link_target'] ) : '_self';

			$settings['slideshow']       = isset( $settings_arr['story_media_settings']['ctl_slideshow'] ) ? filter_var( $settings_arr['story_media_settings']['ctl_slideshow'], FILTER_VALIDATE_BOOLEAN ) : true;
			$settings['animation_speed'] = isset( $settings_arr['story_media_settings']['animation_speed'] ) ? absint( $settings_arr['story_media_settings']['animation_speed'] ) : 7000;

			$settings['display_readmore'] = isset( $settings_arr['story_content_settings']['display_readmore'] ) ? sanitize_text_field( $settings_arr['story_content_settings']['display_readmore'] ) : 'yes';

			$settings['read_more_lbl'] = isset( $settings_arr['story_content_settings']['read_more_lbl'] ) ? sanitize_text_field( $settings_arr['story_content_settings']['read_more_lbl'] ) : __( 'Read More', 'cool-timeline' );

			$settings['display_post_meta'] = isset( $settings_arr['blog_post_settings']['post_meta'] ) ? sanitize_text_field( $settings_arr['blog_post_settings']['post_meta'] ) : 'yes';

			$settings['first_story_position'] = isset( $settings_arr['first_story_position'] ) ? sanitize_text_field( $settings_arr['first_story_position'] ) : 'right';

			$settings['timeline_title_tag'] = isset( $settings_arr['title_tag'] ) ? sanitize_text_field( $settings_arr['title_tag'] ) : 'H2';

			$settings['title_color'] = isset( $settings_arr['title_color'] ) ? sanitize_hex_color( $settings_arr['title_color'] ) : '#fff';

			$settings['year_bg_color'] = isset( $settings_arr['circle_border_color'] ) ? sanitize_hex_color( $settings_arr['circle_border_color'] ) : '#333333';

			$settings['year_label_color'] = isset( $settings_arr['year_label_color'] ) ? sanitize_hex_color( $settings_arr['year_label_color'] ) : '#ffffff';

			$settings['disable_fontawesome'] = isset( $settings_arr['disable_FA'] ) ? sanitize_text_field( $settings_arr['disable_FA'] ) : 'no';
			
			$settings['aria_label_value'] = isset( $settings_arr['aria_label_value'] ) ? absint( $settings_arr['aria_label_value'] ) : 2;
			
			return $settings;
		}

		/**
		 * Get Style Settings
		 */
		public function ctl_get_style_settings() {

			$settings     = array();
			$settings_arr = $this->settings;

			$settings['default_icon'] = isset( $settings_arr['story_content_settings']['default_icon'] ) ? $settings_arr['story_content_settings']['default_icon'] : '';
			return $settings;

		}

	}

}
