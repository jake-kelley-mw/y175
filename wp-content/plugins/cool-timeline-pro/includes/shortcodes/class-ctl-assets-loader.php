<?php

/**
 * CTL Assets Loader.
 *
 * @package CTL
 */

/**
 * Do not access the page directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CTL_Assets_Loader' ) ) {

	/**
	 * CTL Assets loader Class.
	 */
	class CTL_Assets_Loader {

		/**
		 * Shortcode attribute
		 *
		 * @var ctl_attr
		 */
		public $ctl_attr = array();

		/**
		 * Timeline setting option array
		 *
		 * @var ctl_options
		 */
		public $ctl_options = array();

		/**
		 * CTL_Assets_Loader Constructor Function
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'ctl_common_assets' ) );

			add_action( 'wp_enqueue_scripts', array( 'CTL_Styles_Generator', 'ctl_global_style' ) );
		}


		/**
		 * Loaded Timeline Global Assets
		 *
		 * @param object $attr timeline attributes.
		 */
		public function ctl_global_assets( $attr ) {
			// Load common assets required for all cases.
			$this->ctl_common_assets();

			// Get the Cool Timeline options from the database.
			$ctl_options_arr = get_option( 'cool_timeline_settings' );

			// Validate and sanitize attributes
			$attr = array_map( 'sanitize_text_field', $attr );

			// Check if Google Fonts should be disabled.
			$disable_gf = isset( $ctl_options_arr['disable_GF'] ) ? sanitize_text_field( $ctl_options_arr['disable_GF'] ) : 'no';

			// Check Lightbox settings for stories images.
			$ctl_glightbox = isset( $ctl_options_arr['story_media_settings']['stories_images'] ) ? sanitize_text_field( $ctl_options_arr['story_media_settings']['stories_images'] ) : 'popup';

			// Check for the default icon.
			$default_icon = isset( $ctl_options_arr['story_content_settings']['default_icon'] ) ? sanitize_text_field( $ctl_options_arr['story_content_settings']['default_icon'] ) : '';

			// Check if the Vertical Slider should be disabled.
			$disable_vr_slider = isset( $ctl_options_arr['disable_vr_slider'] ) ? sanitize_text_field( $ctl_options_arr['disable_vr_slider'] ) : 'no';

			// Check fontawesome disable or not.
			$disable_fontawesome = isset( $ctl_options_arr['disable_FA'] ) ? sanitize_text_field( $ctl_options_arr['disable_FA'] ) : 'no';

			// Enqueue Google Fonts styles if not disabled.
			if ( $disable_gf !== 'yes' ) {
				wp_enqueue_style( 'ctl-gfonts' );
			}

			// Enqueue Lightbox scripts and styles if needed.
			if ( 'popup' === $ctl_glightbox || 'design-7' === $attr['designs'] ) {
				wp_enqueue_script( 'ctl_glightbox' );
				wp_enqueue_style( 'ctl_glightbox_css' );
			}
			// Enqueue FontAwesome styles for icons if needed.
			if ( 'yes' !== $disable_fontawesome && 'icon' === $attr['icons'] && ( 'story_timeline' === $attr['ctl_type'] || ! empty( $default_icon ) ) ) {
				wp_enqueue_style( 'ctl_font_awesome' );
				wp_enqueue_style( 'ctl_font_shims', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css' );
			}

			// Enqueue Swiper styles and scripts if needed.
			if ( 'horizontal' === $attr['layout'] || ( 'story_timeline' === $attr['ctl_type'] && 'no' === $disable_vr_slider ) ) {
				wp_enqueue_style( 'ctl_swiper_style' );
				wp_enqueue_script( 'ctl_swiper_script' );
			}

			// Enqueue animation styles and scripts if needed.
			if ( isset( $attr['animation'] ) && 'none' !== $attr['animation'] && ! empty( $attr['animation'] ) ) {
				wp_enqueue_style( 'aos_css' );
				wp_enqueue_script( 'aos_js' );
				wp_dequeue_script( 'ctl_vertical_script' );
			}

			// Enqueue common styles and scripts.
			wp_enqueue_style( 'ctl_common_style' );
			wp_enqueue_script( 'ctl_common_script' );

			// Enqueue conditional assets based on attributes.
			$this->ctl_conditional_assets( $attr );
		}


		/**
		 * Timeline Vertical Assets Loaded
		 */
		public function ctl_vr_assets() {
			wp_enqueue_style( 'ctl_vertical_style' );
		}

		/**
		 * Timeline Horizontal Assets Loaded
		 */
		public function ctl_hr_assets() {
			wp_enqueue_style( 'ctl_horizontal_style' );
		}


		/**
		 * Timeline Compact Assets Loaded
		 */
		public function ctl_cpt_assets() {
			wp_enqueue_style( 'ctl_compact_style' );
			wp_enqueue_style( 'ctl_vertical_style' );
			if ( ! wp_script_is( 'ctl-masonry', 'enqueued' ) ) {
				wp_enqueue_script( 'ctl-masonry' );
				wp_enqueue_script( 'ctl-imagesloaded' );
			}
		}


		/**
		 * Enqueue load more script if load more and category filter enable
		 */
		public function load_more_script() {
			wp_enqueue_script( 'ctl_load_more' );

			wp_localize_script(
				'ctl_load_more',
				'ctl_ajax_object',
				array(
					'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
					'ajax_nonce' => wp_create_nonce( 'ctp_load_more_nonce' ),
				)
			);
		}

		/**
		 * Timeline Typography font family
		 */
		public function ctl_google_fonts() {
			$google_fonts    = array();
			$fonts_settings  = array( 'post_content_typo', 'post_title_typo', 'main_title_typo', 'ctl_date_typo' );
			$ctl_options_arr = get_option( 'cool_timeline_settings' );

			foreach ( $fonts_settings as $font_setting ) {
				if ( isset( $ctl_options_arr[ $font_setting ]['font-family'] ) ) {
					$post_content_typo = $ctl_options_arr[ $font_setting ];
					if ( isset( $post_content_typo['type'] )
					&& 'google' === $post_content_typo['type'] ) {
						$fonts = sanitize_text_field( $post_content_typo['font-family'] );
						if ( $fonts && 'inhert' !== $fonts ) {
							if ( 'Raleway' === $fonts ) {
								$fonts = 'Raleway:100';
							}
							$fonts = str_replace( ' ', '+', $fonts );
						}
						$google_fonts[] = $fonts;
					}
				}
			}

			$google_fonts = array_unique( $google_fonts );
			if ( is_array( $google_fonts ) && ! empty( $google_fonts ) ) {
				$allfonts = implode( '|', $google_fonts );
				wp_register_style( 'ctl-gfonts', "https://fonts.googleapis.com/css?family=$allfonts", false, CTLPV, 'all' );
			}
		}

		/**
		 * Timeline All Assets Registered
		 */
		public function ctl_common_assets() {
			$ext = '.min';
			wp_register_script( 'ctl_glightbox', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/jquery.glightbox.min.js' ), array( 'jquery' ), CTLPV, true );

			wp_register_style( 'ctl_glightbox_css', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/glightbox.min.css' ), null, CTLPV, 'all' );

			wp_register_style( 'aos_css', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/aos.css' ), null, CTLPV, 'all' );

			wp_register_script( 'aos_js', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/aos.js' ), array( 'jquery' ), CTLPV, true );

			wp_register_style( 'rtl_styles', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/rtl-styles.css' ), null, CTLPV, 'all' );

			wp_register_style( 'ctl_font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', null, CTLPV, 'all' );

			// New cool timeline shortcode files.
			wp_register_style( 'ctl_common_style', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/ctl-common-styles' . $ext . '.css' ), null, CTLPV, 'all' );
			wp_register_style( 'ctl_swiper_style', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/swiper.min.css' ), null, CTLPV, 'all' );
			wp_register_script( 'ctl_swiper_script', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/swiper.min.js' ), array( 'jquery' ), CTLPV, false );
			wp_register_style( 'ctl_vertical_style', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/ctl-vertical-timeline' . $ext . '.css' ), null, CTLPV, 'all' );
			wp_register_style( 'ctl_horizontal_style', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/ctl-horizontal-timeline' . $ext . '.css' ), null, CTLPV, 'all' );
			wp_register_script( 'ctl_common_script', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/ctl-common' . $ext . '.js' ), array( 'jquery' ), CTLPV, true );
			wp_register_script( 'ctl_vertical_script', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/ctl-vertical' . $ext . '.js' ), array( 'jquery' ), CTLPV, true );
			wp_register_script( 'ctl_horizontal_script', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/ctl-horizontal' . $ext . '.js' ), array( 'jquery' ), CTLPV, true );

			// Compact layout masonry and image loaded library.
			wp_register_script( 'ctl-masonry', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/masonry.pkgd.min.js' ), array( 'jquery' ), CTLPV, false );
			wp_register_script( 'ctl-imagesloaded', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/imagesloaded.pkgd.min.js' ), array( 'jquery' ), CTLPV, false );
			wp_register_style( 'ctl_compact_style', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/css/ctl-compact-style' . $ext . '.css' ), null, CTLPV, 'all' );
			/**
			 * Frontend ajax requests.
			 */
			wp_register_script( 'ctl_load_more', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/ctl-load-more' . $ext . '.js' ), array( 'jquery' ), CTLPV, true );

			$this->ctl_google_fonts();

			$this->load_assets_on_shortcode_pages();
		}


		public function load_assets_on_shortcode_pages() {
			$ctl_shortcode_page_ids = get_option( 'ctl_shortcode_page_ids', array() );
			$current_page_id        = get_the_ID();

			// Check if the current page ID is in the array of saved IDs
			if ( in_array( $current_page_id, $ctl_shortcode_page_ids ) ) {

				wp_enqueue_style( 'ctl_common_style' );

				$ctl_layout_used = get_option( 'ctl_layout_used' );
				if ( isset( $ctl_layout_used[ $current_page_id ] ) ) {
					$active_layout = $ctl_layout_used[ $current_page_id ];
					if ( in_array( 'compact', $active_layout ) ) {
						wp_enqueue_style( 'ctl_compact_style' );
						wp_enqueue_style( 'ctl_vertical_style' );
						if ( ! wp_script_is( 'ctl-masonry', 'enqueued' ) ) {
							wp_enqueue_script( 'ctl-masonry' );
							wp_enqueue_script( 'ctl-imagesloaded' );
						}
					} elseif ( in_array( 'horizontal', $active_layout ) ) {
						wp_enqueue_style( 'ctl_horizontal_style' );
						wp_enqueue_style( 'ctl_swiper_style' );
						wp_enqueue_script( 'ctl_swiper_script' );
					} else {
						wp_enqueue_style( 'ctl_vertical_style' );
					}
				}
			}
		}
		/**
		 * Timeline Conditional Assets Loaded
		 *
		 * @param object $attributes timeline attributes.
		 */
		public function ctl_conditional_assets( $attributes ) {
			$this->ctl_attr  = $attributes;
			$design          = sanitize_text_field( $this->ctl_attr['layout'] );
			$pagination      = sanitize_text_field( $this->ctl_attr['pagination'] );
			$category_filter = sanitize_text_field( $this->ctl_attr['filters'] );

			if ( 'horizontal' === $design ) {
				wp_enqueue_script( 'ctl_horizontal_script' );
				$this->ctl_hr_assets();
			} elseif ( 'compact' === $design ) {
				wp_enqueue_script( 'ctl_vertical_script' );
				$this->ctl_cpt_assets();
			} else {
				wp_enqueue_script( 'ctl_vertical_script' );
				$this->ctl_vr_assets();
			}

			if ( 'yes' === $category_filter || 'ajax_load_more' === $pagination ) {
				$this->load_more_script();
			}
		}
	}
};
