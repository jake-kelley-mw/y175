<?php
/**
 * CTL Loop Helper.
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
 * Cool timeline loop helper class
 */

if ( ! class_exists( 'CTL_Post_Loop_Helpers' ) ) {

	/**
	 * Class Loop Helpers.
	 */
	class CTL_Post_Loop_Helpers {

		/**
		 * Member Variable
		 *
		 * @var attributes
		 */
		public $attributes;

		/**
		 * Member Variable
		 *
		 * @var settings
		 */
		public $settings;

		/**
		 * Used To stop duplicate year label
		 *
		 * @var active_year
		 */
		public $active_year = '';

		/**
		 * Define Story Type
		 *
		 * @var tm_type
		 */
		public $tm_type = 'Post';

		/**
		 * Constructor
		 *
		 * @param array $attributes shortcode attributes.
		 * @param array $settings timeline settings.
		 */
		public function __construct( $attributes, $settings ) {
			// Plugin initialization.
			$this->attributes  = $attributes;
			$this->settings    = $settings;
			$this->active_year = isset( $attributes['config']['active_year'] ) ? sanitize_text_field( $attributes['config']['active_year'] ) : '';
		}

		/**
		 * Renders the timeline Single Post.
		 *
		 * @param int    $index Index value of current post.
		 * @param object $post Current Post object.
		 *
		 * @since 0.0.1
		 */
		public function ctl_render( $index, $post ) {

			$output     = '';
			$post_id    = get_the_ID();
			$classes    = array( 'ctl-story' );
			$attributes = $this->attributes;
			$layout     = $attributes['layout'];
			$design     = $attributes['designs'];
			$media      = $this->ctl_get_featured_image( $post_id );

			if ( 'dot' === $attributes['icons'] ) {
				$classes[] = 'ctl-story-no-icon';
			} elseif ( 'none' !== $attributes['icons'] ) {
				$classes[] = 'ctl-story-icon';
			}
			if ( 'horizontal' === $layout ) {
				$classes[] = 'swiper-slide';
				$classes[] = 0 === $index % 2 ? 'even' : 'odd';
			} else {
				// dynamic alternate class.
				$condition = 0 === $index % 2;
				if ( isset( $this->settings['first_story_position'] ) && 'left' === $this->settings['first_story_position'] ) {
					$condition = 0 !== $index % 2;
				}
				if ( $condition ) {
					$classes[] = 'one-side' !== $this->attributes['layout'] ? 'ctl-story-left' : 'ctl-story-right';
					$classes[] = 'even';
				} else {
					$classes[] = 'ctl-story-right';
					$classes[] = 'odd';
				}
			}

			if ( 'design-7' !== $design && empty( $media ) ) {
				$classes[] = 'ctl-no-media';
			}

			$classes = apply_filters( 'ctl_story_class', $classes, $post );

			$nav_design = array( 'default', 'design-6', 'design-8' );

			// Post Year Label.
			if ( empty( $attributes['meta-key'] ) && 'horizontal' !== $layout && 'compact' !== $layout && 'show' === $attributes['year-label'] ) {
				$output .= $this->ctl_get_year_label( $post_id );
			}

			$output .= '<!-- Timeline Content --><div  id="ctl-post-' . esc_attr( $post_id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"  data-story-index="' . esc_attr( $index ) . '" role="article">';

			// Story Year Label.
			if ( ( ! in_array( $design, $nav_design, true ) && 'design-5' !== $design && 'horizontal' === $layout && empty( $attributes['meta-key'] ) && 'show' === $attributes['year-label'] ) || 'compact' === $layout && 'show' === $attributes['year-navigation'] ) {
				$output .= $this->ctl_get_year_label( $post_id );
			}
			if ( ( ! in_array( $design, $nav_design, true ) && 'horizontal' === $layout ) || 'horizontal' !== $layout ) {
				if ( 'compact' !== $layout ) {
					if ( 'compact' !== $layout && 'hide' !== $attributes['story-date'] ) {
						
						$output .= $this->ctl_get_date( $post_id, $attributes['date-format'] );
						
					}
				}
				if ( 'dot' === $attributes['icons'] ) {
					$output .= '<!-- ' . $this->tm_type . ' IconDot --><div class="ctl-icondot"></div> ';
				} elseif ( 'none' !== $attributes['icons'] ) {
					$output .= '<!-- ' . $this->tm_type . ' Icon -->' . $this->ctl_get_icon( $this->settings['default_icon'] );
				}
			}

			$output .= '<!-- ' . $this->tm_type . ' Arrow --><div class="ctl-arrow"></div>
			<!-- ' . $this->tm_type . ' Content --><div class="ctl-content">';
			$output .= $this->ctl_get_title( $post_id );

			// Grabing Post content based upon content type.

			if ( 'design-7' !== $attributes['designs'] ) {
					$output .= $media;
			}

			if ( 'compact' === $layout ) {
				if ( 'show' === $attributes['story-date'] ) {  // ← Added this check
				$output .= $this->ctl_get_date( $post_id, $attributes['date-format'] );
				}
			}
			if ( 'design-7' !== $attributes['designs'] ) {
				$output .= $this->ctl_get_content();
			}

			$output .= '</div>';

			if ( 'design-7' === $attributes['designs'] ) {
				$output .= $this->ctl_minimal_content();
			}
			$output .= '</div>';

			return $output;
		}

		/**
		 * Get Post Title
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function ctl_get_title( $post_id ) {
			$output = '';
			if ( ! empty( get_the_title() ) ) {
				$design      = $this->attributes['designs'];
				$title_class = 'story-' . esc_attr( $post_id );
				$re_more     = ( ( isset( $this->attributes['read-more'] ) && 'show' === $this->attributes['read-more'] ) || 'design-7' === $this->attributes['designs'] );
				$aria_level  = isset( $this->settings['aria_label_value'] ) ? absint( $this->settings['aria_label_value'] ) : 2;
				$output     .= '<!-- ' . $this->tm_type . ' Title --> <div class="ctl-title ' . esc_attr( $title_class ) . '" role="heading" aria-level="' . $aria_level . '">';
				$output     .= $re_more ? $this->get_post_link( $post_id ) : '';
				$output     .= wp_kses_post( get_the_title() );
				$output     .= $re_more ? '</a>' : '';
				$read_more_lbl   = ! empty( $this->settings['read_more_lbl'] ) ? esc_html( $this->settings['read_more_lbl'] ) : __( 'Read More', 'cool-timeline' );

				if ( 'design-7' === $design ) {
					$output .= $re_more ? $this->get_post_link( $post_id ) : '';
					$output .= '<p class="ctl_read_more">' . $read_more_lbl . '</p>';
					$output .= $re_more ? '</a>' : '';
				}
				$output .= '</div>';
			}
			return $output;
		}

		/**
		 * Get stories content
		 */
		public function ctl_get_content() {
			$attributes = $this->attributes;
			$output     = '';
			$content    = '';
			if ( 'full' === $attributes['story-content'] ) {
				$content .= apply_filters( 'the_content', get_the_content() );
			} else {
				$content .= CTL_Helpers::ctl_get_excerpt( $attributes, $this->settings );
			}

			if ( ! empty( $content ) ) {
				$output .= '<!-- ' . $this->tm_type . ' Description --><div class="ctl-description">' . $content;
				if ( 'show' === $attributes['post-meta'] ) {
					$output .= $this->ctl_get_post_meta();
				}
				$output .= '</div>';
			}

			return $output;
		}

		/**
		 * Get Post Date
		 *
		 * @param int    $post_id Current Post ID.
		 * @param string $date_formats Timeline date format.
		 * @return string Formatted date HTML output.
		 */
		public function ctl_get_date( $post_id, $date_formats, $popup = false ) {
			$posted_date        = null;
			$date_format        = ( 'custom' !== $date_formats ) ? $date_formats : $this->attributes['custom-date-format'];
			$ctl_meta_key_value = get_post_meta( $post_id, $this->attributes['meta-key'] );
			$layout             = $this->attributes['layout'];
			$re_more            = ( 'horizontal' === $layout && 'design-7' === $this->attributes['designs'] && ! $popup );
			if ( isset( $this->attributes['meta-key'] ) && ! empty( $this->attributes['meta-key'] ) ) {
				if ( isset( $ctl_meta_key_value ) && ! empty( $ctl_meta_key_value ) ) {
					$ctl_meta_time = strtotime( $ctl_meta_key_value[0] );
					if ( $ctl_meta_time ) {
						$posted_date = date( $date_format, $ctl_meta_time );
					} else {
						$posted_date = sanitize_text_field( $ctl_meta_key_value[0] );
					}
				}
			} else {
				$posted_date = get_the_date( __( "$date_format", 'cool-timeline' ) );
			}

			$output = '';
			if ( $posted_date ) {
					$output .= '<!-- ' . $this->tm_type . ' Label --><div class="ctl-labels">';
					$output .= '<div class="ctl-label-big story-date">';
					$output .= $re_more ? $this->get_post_link( $post_id ) : '';
					$output .= esc_html( $posted_date );
					$output .= $re_more ? '</a>' : '';
					$output .= '</div>';
					$output .= '</div>';
			}

			return $output;
		}

		/**
		 * Get Post Year and create highlighted Year section in timeline
		 *
		 * @return string Output HTML.
		 */
		public function ctl_get_year_label() {
			$attributes = $this->attributes;
			$output     = '';
			$wrp_cls    = 'horizontal' === $attributes['layout'] ? 'horizontal' : 'vertical';
			$design     = 'light';
			$visibility = isset( $attributes['year-label'] ) ? $attributes['year-label'] : 'show';
			if ( 'show' === $visibility || ( 'compact' === $attributes['layout'] && 'show' === $attributes['year-navigation'] ) ) {
				// Get story date.
				$posted_year = get_the_date( __( 'Y', 'cool-timeline' ) );
				// Get Year value.
				if ( $posted_year ) {
					$post_year = $posted_year;
					// Display the year label if it is different from the previous year.
					if ( $post_year !== $this->active_year ) {

						$post_year_label = sprintf( '<div class="ctl-year-label ctl-year-text"><span>%s</span></div>', esc_html( $post_year ) );

						$this->active_year = $post_year;
						if ( 'compact' === $attributes['layout'] ) {
							$output .= '<span class="scrollable-section ctl-year-container" data-section-title="' . esc_attr( $post_year ) . '"></span>';
						} else {
							$output .= sprintf(
								'<!-- ' . $this->tm_type . ' Year Section --><div data-cls="sc-nv-%s %s" class="timeline-year scrollable-section ctl-year ctl-year-container %s-year" data-section-title="%s" id="year-%s">%s</div>',
								esc_attr( $design ),
								esc_attr( $wrp_cls ),
								esc_attr( $design ),
								esc_attr( $post_year ),
								esc_attr( $post_year ),
								apply_filters( 'ctl_story_year', $post_year_label )
							);
						}
					}
				}
			}

			return $output;
		}

		/**
		 * Get Post Featured Image
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function ctl_get_featured_image( $post_id ) {
			$attributes = $this->attributes;
			global $post;
			$post_id = $post->ID;

			if ( ! get_the_post_thumbnail_url( $post_id ) ) {
				return;
			}

			$img_html = '';

			// Get stories images link setting.
			$stories_images_link = $this->settings['stories_images_link'];

			// Get alternative text for the image.
			$img_alt  = get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true );
			$alt_text = $img_alt ? esc_attr( $img_alt ) : esc_attr( get_the_title( $post_id ) );

			// Generate image link based on stories images link setting.
			$post_img_link = '';
			$img_f_url     = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
			if ( 'popup' === $stories_images_link ) {
				$post_img_link = '<a data-glightbox="' . esc_attr( get_the_title( $post_id ) ) . '" href="' . esc_url( $img_f_url ) . '" class="ctl_glightbox">';
			} elseif ( 'single' === $stories_images_link ) {
				$post_img_link = $this->get_post_link( $post_id );
			} elseif ( 'disable_links' === $stories_images_link ) {
				$post_img_link = '';
			} elseif ( 'theme-popup' === $stories_images_link ) {
				$post_img_link = '<a title="' . esc_attr( get_the_title( $post_id ) ) . '" href="' . esc_url( $img_f_url ) . '">';
			} else {
				$post_img_link = $this->get_post_link( $post_id );
			}

			$image_size        = 'large';
			$image_wrapper_cls = 'full';

			if ( 'design-7' === $attributes['designs'] ) {
				$img_html .= '<div class="ctl_popup_img" data-popup-image="' . esc_url( $img_f_url ) . '"></div>';
			} else {
				// Generate image HTML based on image container size.
				if ( get_the_post_thumbnail_url( $post_id ) ) {
					$img_html .= '<!-- ' . $this->tm_type . ' Media --><div class="ctl-media ' . esc_attr( $image_wrapper_cls ) . '">';
					$img_html .= $post_img_link;
					$img_html .= get_the_post_thumbnail(
						$post_id,
						apply_filters( 'cool_timeline_story_img_size', $image_size ),
						array(
							'class' => 'story-img',
							'alt'   => esc_attr( $alt_text ),
						)
					);
					$img_html .= '</a>';
					$img_html .= '</div>';
				}
			}

			return $img_html;
		}

		/**
		 * Get Timeline Post links
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function get_post_link( $post_id ) {
			$attributes = $this->attributes;
			$post_link  = '';
			$url        = '';
			// Get custom link settings.
			if ( 'design-7' === $attributes['designs'] ) {
				$post_link = '<a class="minimal_glightbox" data-popup-id="#ctl-popup-' . esc_attr( $post_id ) . '" role="button">';
			} else {
				$target = isset( $this->settings['story_link_target'] ) ? esc_attr( $this->settings['story_link_target'] ) : '_self';
				$url    = get_the_permalink( $post_id );

				$post_link = '<a target="' . esc_attr( $target ) . '" title="' . esc_attr( get_the_title( $post_id ) ) . '" href="' . esc_url( $url ) . '" class="story-link">';
			}

			return $post_link;
		}

		/**
		 * Get Post Icon
		 *
		 * @param string $default_icon default icon.
		 */
		public function ctl_get_icon( $default_icon ) {
			$output              = '';
			$icon                = '';
			$extra_class         = '';
			$disable_fontawesome = isset( $this->settings['disable_fontawesome'] ) ? $this->settings['disable_fontawesome'] : 'no';

			if ( isset( $default_icon ) && ! empty( $default_icon ) && 'yes' !== $disable_fontawesome ) {
				if ( strpos( $default_icon, 'fab' ) === false ) {
					$extra_class = 'fa';
				}
				$icon = '<i class= "' . esc_attr( $extra_class ) . ' ' . esc_attr( $default_icon ) . '" aria-hidden= "true"></i> ';
			} else {
				$icon = CTL_Helpers::ctl_static_svg_icons( 'clock' );
			}

			$output .= '<div class="ctl-icon">';
			$output .= $icon;
			$output .= '</div> ';
			return $output;
		}

		/**
		 * Get Post Timeline Post Meta Tags
		 */
		public function ctl_get_post_meta() {
			$ctl_v_html    = '';
			$post_taxonomy = isset( $this->attributes['taxonomy'] ) ? $this->attributes['taxonomy'] : '';
			if ( ! empty( $post_taxonomy ) && 'category' === $post_taxonomy ) {
				$ctl_v_html .= '<div class="ctl_meta_tags">';
				$ctl_v_html .= CTL_Helpers::ctl_entry_taxonomies();
				$ctl_v_html .= CTL_Helpers::ctl_post_tags();
				$ctl_v_html .= '</div>';
			}
			return $ctl_v_html;
		}

		/**
		 * Timeline Horizontal Nav Slider
		 *
		 * @param int $index current Post index.
		 * @param int $id Current Post ID.
		 */
		public function ctl_hr_nav_slider( $index, $id ) {
			$post_id            = $id;
			$output             = '';
			$attributes         = $this->attributes;
			$nav_slider_designs = array( 'default', 'design-6', 'design-8' );
			if ( in_array( $attributes['designs'], $nav_slider_designs, true ) && 'horizontal' === $attributes['layout'] ) {
				$output .= '<div id="ctl-year-slide-' . esc_attr( $post_id ) . '" class="ctl-year-swiper-slide swiper-slide" data-story-index="' . esc_attr( $index ) . '">';
				if ( 'show' === $attributes['year-label'] && empty( $attributes['meta-key'] ) ) {
					$output .= $this->ctl_get_year_label( $post_id );
				}
				$output .= $this->ctl_get_date( $post_id, $attributes['date-format'] );
				if ( 'dot' === $attributes['icons'] ) {
					$output .= '<div class="ctl-icondot"></div> ';
				} elseif ( 'none' !== $attributes['icons'] ) {
					$output .= $this->ctl_get_icon( $this->settings['default_icon'] );
				}
				$output .= '</div>';
			}

			return $output;
		}

		/**
		 * Minimal Popup Content
		 */
		public function ctl_minimal_content() {
			$post_id    = get_the_ID();
			$output     = '';
			$attributes = $this->attributes;

			$output .= '<div id="ctl-popup-' . esc_attr( $post_id ) . '" class="ctl_popup_hide">
				<div class="ctl_popup_container">
					<div class="ctl_popup_date">';
			$output .= $this->ctl_get_date( $post_id, $attributes['date-format'], true );
			$output .= '</div>
					<h2 class="ctl_popup_title">';
			$output .= wp_kses_post( get_the_title() );
			$output .= '</h2>
					<div class="ctl_popup_media">';
			$output .= $this->ctl_get_featured_image( $post_id );
			$output .= '</div>
					<div class="ctl_popup_desc">';
			$output .= $this->ctl_get_content();
			$output .= '</div>
				</div>
			</div>';

			return $output;
		}

	}
}
