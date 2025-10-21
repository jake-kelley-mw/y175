<?php
/**
 * CTL Layout Manager.
 *
 * @package CTL
 */

/**
 * Cool timeline posts loop helper class
 */

/**
 * Do not access the page directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create Cool Timeline layouts manager class.
 * Create shortcode HTML according to the layout.
 */
if ( ! class_exists( 'CTL_Layout_Manager' ) ) {

	/**
	 * Class Layouts Manager.
	 *
	 * @package CTL
	 */
	class CTL_Layout_Manager {

		/**
		 * Member Variable.
		 *
		 * @var array
		 */
		public $attributes;

		/**
		 * Member Variable.
		 *
		 * @var WP_Query
		 */
		public $wp_query;

		/**
		 * Member Variable.
		 *
		 * @var array
		 */
		public $settings;

		/**
		 * Member Variable.
		 *
		 * @var CTL_Loop_Helpers
		 */
		public $content_instance;

		/**
		 * Member Variable.
		 *
		 * @var Timeline_Id
		 */
		public $timeline_id;

		/**
		 * Constructor.
		 *
		 * @param array    $attributes Shortcode attributes.
		 * @param WP_Query $wp_query   WP_Query object.
		 * @param array    $settings   Plugin settings.
		 */
		public function __construct( $attributes, $wp_query, $settings ) {

			$this->attributes  = $attributes;
			$this->wp_query    = $wp_query;
			$this->settings    = $settings;
			$this->timeline_id = uniqid();
			$this->initialize_content_instance();
		}

		/**
		 * Loop helper class object return depend on shortcode type.
		 */
		private function initialize_content_instance() {
			$ctl_type = isset( $this->attributes['ctl_type'] ) ? sanitize_text_field( $this->attributes['ctl_type'] ) : '';

			if ( 'post_timeline' === $ctl_type ) {
				$this->content_instance = new CTL_Post_Loop_Helpers( $this->attributes, $this->settings );
			} else {
				$this->content_instance = new CTL_Loop_Helpers( $this->attributes, $this->settings );
			}
		}

		/**
		 * Render the layout based on the shortcode attributes.
		 *
		 * @return string The rendered layout HTML.
		 */
		public function render_layout() {
			ob_start();

			$layout   = isset( $this->attributes['layout'] ) ? sanitize_text_field( $this->attributes['layout'] ) : 'vertical';
			$response = $this->ctl_render_loop( $this->wp_query );
			if ( 'horizontal' === $layout ) {
				$this->render_horizontal_layout( $response );
			} else {
				$this->render_vertical_layout( $response );
			}

			return ob_get_clean();
		}

		/**
		 * Renders the story timeline.
		 *
		 * @param WP_Query $query WP_Query object.
		 *
		 * @return string The rendered stories HTML.
		 */
		private function ctl_render_loop( $query ) {
			$output            = '';
			$story_styles      = '';
			$hr_default_slider = '';
			$index             = 1;
			// The Loop.
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					global $post;

					$output .= $this->content_instance->ctl_render( $index, $post );
					if ( 'post_timeline' !== $this->attributes['ctl_type'] ) {
						$story_styles .= $this->content_instance->ctl_story_color( $post->ID );
					}
					$hr_default_slider .= $this->content_instance->ctl_hr_nav_slider( $index, $post->ID );

					$index++;
				}
			} else {
				$output .= '<div class="no-content"><h4>';
				$output .= __( 'Sorry,You have not added any story yet', 'cool-timeline' );
				$output .= '</h4></div>';
			}
			wp_reset_postdata();
			return array(
				'HTML'          => $output,
				'CSS'           => $story_styles,
				'HR_NAV_SLIDER' => $hr_default_slider,
			);
		}
		/**
		 * Render horizontal timeline layout.
		 *
		 * @param string $response The content HTML.
		 */
		private function render_horizontal_layout( $response ) {
			$attributes          = $this->attributes;
			$wrapper_cls         = implode( ' ', $attributes['config']['wrapper_cls'] );
			$disable_fontawesome = isset( $this->settings['disable_fontawesome'] ) ? $this->settings['disable_fontawesome'] : 'no';
			$ctl_type            = isset( $attributes['ctl_type'] ) ? sanitize_text_field( $attributes['ctl_type'] ) : '';
			$default_icon        = isset( $this->settings['default_icon'] ) ? $this->settings['default_icon'] : '';
			$svg_icon            = 'yes' !== $disable_fontawesome && 'icon' === $attributes['icons'] && ( 'story_timeline' === $ctl_type || ! empty( $default_icon ) );
			$swiper_left_arrow   = $svg_icon ? '<i class="fas fa-chevron-left"></i>' : CTL_Helpers::ctl_static_svg_icons( 'chevron_left' );
			$swiper_right_arrow  = $svg_icon ? '<i class="fas fa-chevron-right"></i>' : CTL_Helpers::ctl_static_svg_icons( 'chevron_right' );
			$timeline_custom_css = CTL_Helpers::ctl_timeline_custom_style( $attributes, $this->settings );
			?>
			<!-- Cool Timeline PRO V<?php echo esc_html( CTLPV ); ?> -->
			<div class="<?php echo esc_attr( $attributes['config']['main_wrp_cls'] ); ?>" role="region" aria-label="Timeline">
				<?php
				$filter_categories = ! empty( $attributes['filter-categories'] ) ? explode( ',', sanitize_text_field( $attributes['filter-categories'] ) ) : '';
				if ( 'yes' === $attributes['filters'] ) {
					$ctl_filter_based      = isset( $ctl_type ) && 'post_timeline' === $ctl_type ? null : sanitize_text_field( $attributes['based'] );
					$ctl_category_taxonomy = isset( $ctl_type ) && 'post_timeline' === $ctl_type ? null : 'ctl-stories';
					$ctl_timeline_type     = isset( $ctl_type ) && 'post_timeline' === $ctl_type ? 'content-tm' : 'story-tm';
					$category_filter       = CTL_Helpers::ctl_category_filter( $ctl_category_taxonomy, $attributes['category'], $ctl_timeline_type, $filter_categories, $ctl_filter_based, $attributes['designs'], $attributes['config']['wrapper_id'] );
					echo $category_filter;
				}
				if ( 'show' === $attributes['year-navigation'] ) {
					?>
					<div class="ctl-navigation-bar ctl-nav-<?php echo esc_attr( $attributes['designs'] ); ?>" role="navigation">
					</div>
					<?php
				}
				?>
				<div id="<?php echo esc_attr( $attributes['config']['wrapper_id'] ); ?>" class="<?php echo esc_attr( $wrapper_cls ); ?>" <?php echo implode( ' ', $attributes['config']['data_attribute'] ); ?> role="region" aria-label="Timeline">
					<div class="ctl-wrapper-inside">
						<?php
						$nav_slider_designs = array( 'default', 'design-6', 'design-8' );
						if ( in_array( $attributes['designs'], $nav_slider_designs, true ) ) {
							?>
							<div class="ctl-nav-slider-container swiper-container ctl-nav-swiper">
								<div class="ctl-nav-slider-wrapper swiper-wrapper">
									<?php echo $response['HR_NAV_SLIDER']; ?>
								</div>
							</div>
							<?php
						}
						?>
						<div id="ctl-slider-container" class="ctl-slider-container swiper-container ctl-line-filler swiper-container-horizontal" aria-live="polite">
							<!-- Timeline Container -->
							<div class="ctl-slider-wrapper ctl-timeline-container swiper-wrapper">
								<?php echo $response['HTML']; ?>
							</div>
							<span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
						</div>
					</div>
					<!-- Swiper Next button -->
					<div class="ctl-button-prev swiper-button-disabled" tabindex="0" role="button" aria-label="Previous slide" aria-disabled="true">
						<?php echo $swiper_left_arrow; ?>
					</div>
					<!-- Swiper Previous Button -->
					<div class="ctl-button-next" tabindex="0" role="button" aria-label="Next slide" aria-disabled="false">
						<?php echo $swiper_right_arrow; ?>
					</div>
					<!-- Swiper Horizontal line -->
					<div class="ctl-h-line"></div>
					<?php
					if ( 'true' === $attributes['line-filling'] ) {
						?>
						<div class="ctl-line-fill swiper-pagination-progressbar">
							<span class="swiper-pagination-progressbar-fill"></span>
						</div>
						<?php
					}
					if ( $this->wp_query->max_num_pages > 1 ) {
						$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
						if ( 'ajax_load_more' === $attributes['pagination'] ) {
							echo CTL_Helpers::ctl_load_more( $this->wp_query, $paged );
						}
					}
					if ( ! empty( $response['CSS'] ) || ! empty( $timeline_custom_css ) ) {
						?>
						<style id="<?php echo esc_attr( $attributes['config']['wrapper_id'] ); ?>-styles" type="text/css">
							<?php
							if ( ! empty( $timeline_custom_css ) ) {
								echo $timeline_custom_css;
							}
							?>
							<?php 
							echo '/* Specific Story Style Start */'; 
							echo CTL_Styles_Generator::clt_minify_css( $response['CSS'] ); 
							echo '/* Specific Story Style End */'; 
							?>
						</style>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}


		/**
		 * Render vertical timeline layout (both-sided and one-sided).
		 *
		 * @param string $response The content HTML.
		 */
		private function render_vertical_layout( $response ) {
			$attributes          = $this->attributes;
			$wrapper_cls         = implode( ' ', $attributes['config']['wrapper_cls'] );
			$timeline_custom_css = CTL_Helpers::ctl_timeline_custom_style( $attributes, $this->settings );
			$disable_fontawesome = isset( $this->settings['disable_fontawesome'] ) ? $this->settings['disable_fontawesome'] : 'no';
			$ctl_type            = isset( $attributes['ctl_type'] ) ? sanitize_text_field( $attributes['ctl_type'] ) : '';
			$default_icon        = isset( $this->settings['default_icon'] ) ? $this->settings['default_icon'] : '';
			$svg_icon            = 'yes' !== $disable_fontawesome && 'icon' === $attributes['icons'] && ( 'story_timeline' === $ctl_type || ! empty( $default_icon ) );
			?>
			<!-- Cool Timeline PRO V<?php echo esc_html( CTLPV ); ?> -->
			<div class="<?php echo esc_attr( $attributes['config']['main_wrp_cls'] ); ?>" role="region" aria-label="Timeline">
				<?php
				if ( 'story_timeline' === $attributes['ctl_type'] ) {
					$timeline_content = CTL_Helpers::timeline_before_content( sanitize_text_field( $attributes['timeline-title'] ), $this->settings );
					if ( ! empty( $timeline_content ) ) {
						echo $timeline_content;
					}
				}

				$filter_categories = ! empty( $attributes['filter-categories'] ) ? explode( ',', sanitize_text_field( $attributes['filter-categories'] ) ) : '';

				if ( 'yes' === $attributes['filters'] ) {
					$ctl_filter_based      = isset( $attributes['ctl_type'] ) && 'post_timeline' === $attributes['ctl_type'] ? null : sanitize_text_field( $attributes['based'] );
					$ctl_category_taxonomy = isset( $attributes['ctl_type'] ) && 'post_timeline' === $attributes['ctl_type'] ? sanitize_text_field( $attributes['taxonomy'] ) : 'ctl-stories';
					$ctl_timeline_type     = isset( $attributes['ctl_type'] ) && 'post_timeline' === $attributes['ctl_type'] ? 'content-tm' : 'story-tm';
					$category_filter       = CTL_Helpers::ctl_category_filter( $ctl_category_taxonomy, $attributes['category'], $ctl_timeline_type, $filter_categories, $ctl_filter_based, $attributes['designs'], $attributes['config']['wrapper_id'] );
					echo $category_filter;
				}
				?>
				<div id="<?php echo esc_attr( $attributes['config']['wrapper_id'] ); ?>" class="<?php echo esc_attr( $wrapper_cls ); ?>" <?php echo implode( ' ', $attributes['config']['data_attribute'] ); ?>>
					<div class="ctl-start"></div>
					<!-- Timeline Container -->
					<div class="ctl-timeline ctl-timeline-container" data-animation="<?php echo esc_attr( $attributes['config']['animation'] ); ?>" aria-live="polite">
						<!-- Center Line -->
						<div class="ctl-inner-line" role="presentation"></div>
						<?php echo $response['HTML']; ?>
					</div>
					<div class="ctl-end"></div>
					<?php
					if ( $this->wp_query->max_num_pages > 1 ) {
						$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
						if ( 'ajax_load_more' === $attributes['pagination'] ) {
							echo CTL_Helpers::ctl_load_more( $this->wp_query, $paged );
						} elseif ( 'off' !== $attributes['pagination'] ) {
							echo CTL_Helpers::ctl_pagination( $this->wp_query, $paged, $svg_icon );
						}
					}
					if ( ! empty( $response['CSS'] ) || ! empty( $timeline_custom_css ) ) {
						?>
					<!-- Each Timeline Style -->
					<style id="<?php echo esc_attr( $attributes['config']['wrapper_id'] ); ?>-styles" type="text/css">
						<?php
						if ( ! empty( $timeline_custom_css ) ) {
							echo $timeline_custom_css;
						}
						?>
						<?php 
							echo '/* Specific Story Style Start */'; 
							echo CTL_Styles_Generator::clt_minify_css( $response['CSS'] ); 
							echo '/* Specific Story Style End */'; 
						?>
					</style>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}


	}

}
