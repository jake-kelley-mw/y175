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

if ( ! class_exists( 'CTL_Loop_Helpers' ) ) {

	/**
	 * Class Loop Helpers.
	 */
	class CTL_Loop_Helpers {

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
		public $tm_type = 'Story';

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
		 * Renders the timeline Single Story.
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
			$layout     = sanitize_text_field( $attributes['layout'] );
			$design     = sanitize_text_field( $attributes['designs'] );
			$media      = $this->get_media( $post_id );

			// Sanitize icon attribute
			$icon_type = isset( $attributes['icons'] ) ? sanitize_text_field( $attributes['icons'] ) : 'none';
			if ( 'dot' === $icon_type ) {
				$classes[] = 'ctl-story-dot-icon';
			} elseif ( 'none' !== $icon_type ) {
				$classes[] = 'ctl-story-icon';
			} else {
				$classes[] = 'ctl-story-no-icon';
			}

			if ( 'horizontal' === $layout ) {
				$classes[] = 'swiper-slide';
				$classes[] = 0 === $index % 2 ? 'even' : 'odd';
			} else {
				// dynamic alternate class.
				$condition = 0 === $index % 2;

				$classes[] = $condition ? 'even' : 'odd';

				if ( isset( $this->settings['first_story_position'] ) && 'left' === $this->settings['first_story_position'] ) {
					$condition = 0 !== $index % 2;
				}

				if ( $condition ) {
					$classes[] = 'one-side' !== $layout ? 'ctl-story-left' : 'ctl-story-right';
				} else {
					$classes[] = 'ctl-story-right';
				}
			}
			if ( 'design-7' !== $design && empty( $media ) ) {
				$classes[] = 'ctl-no-media';
			}

			if ( 'compact' === $layout ) {
				$classes[] = 'ctl-' . sanitize_text_field( $attributes['compact-ele-pos'] );
			}

			$classes = apply_filters( 'ctl_story_class', $classes, $post );

			$nav_design = array( 'default', 'design-6', 'design-8' );

			// Story Year Label.
			if ( 'default' === $attributes['based'] && 'horizontal' !== $layout && 'compact' !== $layout && 'show' === $attributes['year-label'] ) {
				$output .= $this->ctl_get_year_label( $post_id );
			}
			$output .= '<!-- Timeline Content --><div id="ctl-story-' . esc_attr( $post_id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-story-index="' . esc_attr( $index ) . '" role="article">';

			// Story Year Label.
			if ( ( ! in_array( $design, $nav_design, true ) && 'design-5' !== $design && 'horizontal' === $layout && 'default' === $attributes['based'] && 'show' === $attributes['year-label'] ) || 'compact' === $layout && 'show' === $attributes['year-navigation'] ) {
				$output .= $this->ctl_get_year_label( $post_id );
			}
			if ( ( ! in_array( $design, $nav_design, true ) && 'horizontal' === $layout ) || 'horizontal' !== $layout ) {
				if ( 'compact' !== $layout ) {
					if ( 'custom' === $attributes['based'] ) {
						$output .= $this->ctl_get_labels( $post_id );
					} else {
						if ( 'compact' !== $layout && 'hide' !== $attributes['story-date'] ) {
							$output .= $this->ctl_get_date( $post_id, sanitize_text_field( $attributes['date-format'] ) );
						
						}
					}
				}
				if ( 'dot' === $icon_type ) {
					$output .= '<!-- ' . $this->tm_type . ' IconDot --><div class="ctl-icondot"></div> ';
				} elseif ( 'none' !== $icon_type ) {
					$output .= '<!-- ' . $this->tm_type . ' Icon -->' . $this->ctl_get_icon( $post_id, $this->settings['default_icon'] );
				}
			}

			$output .= '<!-- ' . $this->tm_type . ' Arrow --><div class="ctl-arrow"></div>
			<!-- ' . $this->tm_type . ' Content --><div class="ctl-content">';
			$output .= $this->ctl_get_title( $post_id );

			// Grabing story content based upon content type.

			if ( 'design-7' !== $attributes['designs'] ) {
				$output .= $media;
			}

			if ( 'compact' === $layout ) {
				if ( 'custom' === $attributes['based'] ) {
					$output .= $this->ctl_get_labels( $post_id );
				} else {
					if ( 'show' === $attributes['story-date'] ) {  // ← Added this check
						
				
					$output .= $this->ctl_get_date( $post_id, sanitize_text_field( $attributes['date-format'] ) );
					}
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
		 * Get Story Title
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function ctl_get_title( $post_id ) {
			$output = '';
			if ( ! empty( get_the_title() ) ) {
				$design      = sanitize_text_field( $this->attributes['designs'] );
				$title_class = 'story-' . esc_attr( $post_id );
				$re_more     = ( ( isset( $this->attributes['read-more'] ) && 'show' === $this->attributes['read-more'] ) || 'design-7' === $this->attributes['designs'] );
				$aria_level  = isset( $this->settings['aria_label_value'] ) ? absint( $this->settings['aria_label_value'] ) : 2;
				$output     .= '<!-- ' . $this->tm_type . ' Title --> <div class="ctl-title ' . esc_attr( $title_class ) . '" role="heading" aria-level="' . $aria_level . '">';
				$output     .= $re_more ? $this->get_story_link( $post_id ) : '';
				$output     .= wp_kses_post( get_the_title() );
				$output     .= $re_more ? '</a>' : '';
				$read_more_lbl   = ! empty( $this->settings['read_more_lbl'] ) ? esc_html( $this->settings['read_more_lbl'] ) : __( 'Read More', 'cool-timeline' );

				if ( 'design-7' === $design ) {
					$output .= $re_more ? $this->get_story_link( $post_id ) : '';
					$output .= '<p class="ctl_read_more">' . $read_more_lbl . '</p>';
					$output .= $re_more ? '</a>' : '';
				}
				$output .= '</div>';
			}
			return $output;
		}

		/**
		 * Get Story Date
		 *
		 * @param int    $post_id Current Post ID.
		 * @param string $date_formats Timeline date format.
		 * @return string Formatted date HTML output.
		 */
		public function ctl_get_date( $post_id, $date_formats, $popup = false ) {
			$date_format = ( 'custom' !== $date_formats ) ? sanitize_text_field( $date_formats ) : $this->attributes['custom-date-format'];

			$ctl_story_type = get_post_meta( $post_id, 'story_type', true );
			$story_date     = isset( $ctl_story_type['ctl_story_date'] ) ? sanitize_text_field( $ctl_story_type['ctl_story_date'] ) : '';
			$layout         = sanitize_text_field( $this->attributes['layout'] );
			$re_more        = ( 'horizontal' === $layout && 'design-7' === $this->attributes['designs'] && ! $popup );
			$output         = '';
			if ( $story_date ) {
				$formatted_date = '';
				$timestamp      = strtotime( $story_date );

				if ( false !== $timestamp ) {
					$formatted_date = date_i18n( $date_format, $timestamp );
				} else {
					// Replace 'am' and 'pm' from the story date string.
					$story_date   = trim( str_ireplace( array( 'am', 'pm' ), '', $story_date ) );
					$dateTimeZone = new DateTimeZone( $this->ctl_wp_get_timezone_string() );
					$dateObj      = DateTime::createFromFormat( 'm/d/Y H:i', $story_date, $dateTimeZone );

					if ( false !== $dateObj ) {
						$formatted_date = $dateObj->format( $date_format );
					}
				}
				if ( ! empty( $formatted_date ) ) {
					$output .= '<!-- ' . $this->tm_type . ' Date --><div class="ctl-labels">';
					$output .= '<div class="ctl-label-big story-date">';
					$output .= $re_more ? $this->get_story_link( $post_id ) : '';
					$output .= esc_html( $formatted_date );
					$output .= $re_more ? '</a>' : '';
					$output .= '</div>';
					$output .= '</div>';
				}
			}

			return $output;
		}

		/**
		 * Get Story Custom Labels
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function ctl_get_labels( $post_id, $popup = false ) {
			$story_type        = get_post_meta( $post_id, 'story_type', true );
			$ctl_story_lbl     = isset( $story_type['ctl_story_lbl'] ) ? wp_kses_post( $story_type['ctl_story_lbl'] ) : '';
			$ctl_story_sub_lbl = isset( $story_type['ctl_story_lbl_2'] ) ? wp_kses_post( $story_type['ctl_story_lbl_2'] ) : '';
			$label_cls         = ! empty( $ctl_story_lbl ) && ! empty( $ctl_story_sub_lbl ) ? ' ctl-label-full' : '';
			$layout            = sanitize_text_field( $this->attributes['layout'] );
			$re_more           = ( 'horizontal' === $layout && 'design-7' === $this->attributes['designs'] && ! $popup );
			$output            = '';
			if ( ! empty( $ctl_story_lbl ) || ! empty( $ctl_story_sub_lbl ) ) {
				$output .= '<!-- ' . $this->tm_type . ' Labels --><div class="ctl-labels' . esc_attr( $label_cls ) . '">';
				$output .= '<div class="ctl-label-big">';
				$output .= $re_more ? $this->get_story_link( $post_id ) : '';
				$output .= wp_kses_post( $ctl_story_lbl );
				$output .= $re_more ? '</a>' : '';
				$output .= '</div>';
				$output .= '<div class="ctl-label-small">';
				$output .= $re_more ? $this->get_story_link( $post_id ) : '';
				$output .= wp_kses_post( $ctl_story_sub_lbl );
				$output .= $re_more ? '</a>' : '';
				$output .= '</div>';
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
				$output .= '<!-- ' . $this->tm_type . ' Description --><div class="ctl-description">' . $content . '</div>';
			}
			return $output;
		}

		/**
		 * Get media
		 */
		public function get_media( $post_id ) {
			$attributes      = $this->attributes;
			$ctl_story_media = get_post_meta( $post_id, 'story_media', true );
			$story_format    = isset( $ctl_story_media['story_format'] ) ? sanitize_text_field( $ctl_story_media['story_format'] ) : '';
			$output          = '';
			if ( 'design-7' !== $attributes['designs'] ) {
				if ( 'video' === $story_format ) {
					$output .= $this->ctl_get_video( $post_id );
				} elseif ( 'slideshow' === $story_format ) {
					$output .= $this->ctl_get_slideshow( $post_id );
				} else {
					$output .= $this->ctl_get_featured_image( $post_id );
				}
			}

			return $output;
		}

		/**
		 * Get Story Featured Image
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

			// Get story media and image container size.
			$ctl_story_media = get_post_meta( $post_id, 'story_media', true );
			$img_cont_size   = isset( $ctl_story_media['img_cont_size'] ) ? sanitize_text_field( $ctl_story_media['img_cont_size'] ) : '';
			$img_html        = '';

			// Get stories images link setting.
			$stories_images_link = $this->settings['stories_images_link'];

			// Get alternative text for the image.
			$img_alt  = get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true );
			$alt_text = $img_alt ? sanitize_text_field( $img_alt ) : get_the_title( $post_id );

			// Generate image link based on stories images link setting.
			$story_img_link = '';
			$img_f_url      = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
			if ( 'popup' === $stories_images_link ) {
				$story_img_link = '<a data-glightbox="' . esc_attr( get_the_title( $post_id ) ) . '" href="' . esc_url( $img_f_url ) . '" class="ctl_glightbox">';
			} elseif ( 'single' === $stories_images_link ) {
				$story_img_link = $this->get_story_link( $post_id );
			} elseif ( 'disable_links' === $stories_images_link ) {
				$story_img_link = '';
			} elseif ( 'theme-popup' === $stories_images_link ) {
				$story_img_link = '<a title="' . esc_attr( get_the_title( $post_id ) ) . '" href="' . esc_url( $img_f_url ) . '">';
			} else {
				$story_img_link = $this->get_story_link( $post_id );
			}

			$image_size        = '';
			$image_wrapper_cls = '';
			if ( 'small' === $img_cont_size ) {
				$image_size        = 'medium';
				$image_wrapper_cls = 'small';
			} else {
				$image_size        = 'large';
				$image_wrapper_cls = 'full';
			}

			if ( 'design-7' === $attributes['designs'] ) {
				$img_html .= '<div class="ctl_popup_img" data-popup-image="' . esc_url( $img_f_url ) . '"></div>';
			} else {
				// Generate image HTML based on image container size.
				if ( get_the_post_thumbnail_url( $post_id ) ) {
					$img_html .= '<!-- ' . $this->tm_type . ' Media --><div class="ctl-media ' . esc_attr( $image_wrapper_cls ) . '">';
					$img_html .= $story_img_link;
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
		 * Get Timeline Story links
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function get_story_link( $post_id ) {
			$attributes = $this->attributes;
			$story_link = '';
			$url        = '';
			// Get custom link settings.
			if ( 'design-7' === $attributes['designs'] ) {
				$story_link = '<a class="minimal_glightbox" data-popup-id="#ctl-popup-' . esc_attr( $post_id ) . '" role="button">';
			} else {
				$target             = ! empty( $this->settings['story_link_target'] ) ? sanitize_text_field( $this->settings['story_link_target'] ) : '_self';
				$ctl_extra_settings = get_post_meta( $post_id, 'extra_settings', true );

				if ( isset( $ctl_extra_settings['story_custom_link'] )
				&& ! empty( $ctl_extra_settings['story_custom_link']['url'] ) ) {
					$url    = esc_url( $ctl_extra_settings['story_custom_link']['url'] );
					$target = ! empty( $ctl_extra_settings['story_custom_link']['target'] ) ? sanitize_text_field( $ctl_extra_settings['story_custom_link']['target'] ) : $target;
				} else {
					$url = get_the_permalink( $post_id );
				}

				$story_link = '<a target="' . esc_attr( $target ) . '" title="' . esc_attr( get_the_title( $post_id ) ) . '" href="' . esc_url( $url ) . '" class="story-link">';
			}

			return $story_link;
		}

		/**
		 * Get timeline stories video.
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function ctl_get_video( $post_id ) {

			$ctl_story_media     = get_post_meta( $post_id, 'story_media', true );
			$ctl_video           = isset( $ctl_story_media['ctl_video'] ) ? sanitize_text_field( $ctl_story_media['ctl_video'] ) : '';
			$popup               = $this->settings['stories_images_link'];
			$disable_fontawesome = 'yes' !== $this->settings['disable_fontawesome'] && ( 'icon' === $this->attributes['icons'] || ! empty( $this->settings['default_icon'] ) );
			$play_button         = $disable_fontawesome ? '<i class="fab fa-youtube"></i>' : CTL_Helpers::ctl_static_svg_icons( 'play_button' );

			if ( empty( $ctl_video ) ) {
				return '<div class="full-width">No Video URL Provided</div>';
			}

			$video_type = $this->get_video_type( $ctl_video );

			if ( 'design-7' === $this->attributes['designs'] ) {
				return '<div class="ctl_popup_video" data-popup-video="' . esc_url( $ctl_video ) . '"></div>';
			} elseif ( $video_type === 'youtube' ) {
				preg_match( '/[\\?\\&]v=([^\\?\\&]+)/', $ctl_video, $matches );
				if ( isset( $matches[1] ) ) {
					$id   = sanitize_text_field( $matches[1] );
					$data = $this->ctl_youtube_video( $id, $popup, $play_button );
					return $data;
				}
			} elseif ( $video_type === 'vimeo' ) {
				$video_id = $this->ctl_vimeo_id( $ctl_video );
				$data     = $this->ctl_vimeo_video( $video_id, $popup, $play_button );
				return $data;
			} else {
				return '<div class="full-width">Not Correct URL</div>';
			}
		}

		/**
		 * The video id extracted from url
		 *
		 * @param string $id Youtube video Id.
		 * @param string $popup timeline popup setting.
		 */
		private function ctl_youtube_video( $id, $popup, $btn ) {
			if ( 'popup' !== $popup ) {
				return '<!-- ' . $this->tm_type . ' Media --><div class="full-width">
				<iframe width="100%" 
				src="https://www.youtube-nocookie.com/embed/' . esc_attr( $id ) . '" 
				frameborder="0" allowfullscreen></iframe></div>';
			} else {
				return '<div class="full-width ctl_youtube_thumbnail glightbox-video" data-video-id="' . esc_attr( $id ) . '">
				<div class="ctl-video-btn">' . $btn . '</div><img src="https://img.youtube.com/vi/' . esc_attr( $id ) . '/hqdefault.jpg">
				</div>';
			}
		}

		/**
		 * The video id extracted from url
		 *
		 * @param string $id Vimeo video Id.
		 * @param string $popup timeline popup setting.
		 */
		private function ctl_vimeo_video( $video_id, $popup, $btn ) {
			if ( 'popup' !== $popup ) {
				return '<div class="full-width">
				<div style="padding:42.5% 0 0 0;position:relative;">
					<iframe src="https://player.vimeo.com/video/' . esc_attr( $video_id ) . '?color=0041ff&title=0&byline=0&portrait=0&badge=0"
						style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen>
					</iframe>
				</div>
				<script src="https://player.vimeo.com/api/player.js"></script>
				</div>';
			} else {
				return '<div class="full-width ctl_vimeo_thumbnail glightbox-video" data-video-id="' . esc_attr( $video_id ) . '">
				<div class="ctl-video-btn">' . $btn . '</div><img src="https://vumbnail.com/' . esc_attr( $video_id ) . '.jpg">
				</div>';
			}
		}

		/**
		 * Check video type
		 *
		 * @param string $url video URL.
		 */
		public function get_video_type( $url ) {
			if ( strpos( $url, 'youtube' ) > 0 ) {
				return 'youtube';
			} elseif ( strpos( $url, 'vimeo' ) > 0 ) {
				return 'vimeo';
			} else {
				return 'unknown';
			}
		}

		/**
		 * The video id extracted from url
		 *
		 * @param string $url The URL.
		 */
		public function ctl_vimeo_id( $url = '' ) {
			$regs = array();
			$id   = '';
			if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $regs ) ) {
				$id = sanitize_text_field( $regs[3] );
			}
			return $id;
		}

		/**
		 * Get Story Icon
		 *
		 * @param int    $post_id Current Post ID.
		 * @param string $default_icon default icon.
		 */
		public function ctl_get_icon( $post_id, $default_icon ) {
			$output              = '';
			$icon                = '';
			$extra_class         = '';
			$disable_fontawesome = $this->settings['disable_fontawesome'];
			$re_more             = ( ( isset( $this->attributes['read-more'] ) && 'show' === $this->attributes['read-more'] ) || 'design-7' === $this->attributes['designs'] );
			if ( get_post_type( $post_id ) === 'cool_timeline' ) {
				// Story Icon.
				$story_icon = get_post_meta( $post_id, 'story_icon', true );
				$icon_type  = isset( $story_icon['story_icon_type'] ) ? sanitize_text_field( $story_icon['story_icon_type'] ) : 'fontawesome';

				if ( 'fontawesome' === $icon_type ) {
					if ( 'yes' !== $disable_fontawesome ) {
						$ctl_fontawesome_icon = isset( $story_icon['fa_field_icon'] ) ? sanitize_text_field( $story_icon['fa_field_icon'] ) : '';
						$extra_class          = '';
						if ( '' !== $ctl_fontawesome_icon ) {
							if ( strpos( $ctl_fontawesome_icon, 'fab' ) === false ) {
								$extra_class = 'fa';
							}
							$icon = '<i class= "' . esc_attr( $extra_class ) . ' ' . esc_attr( $ctl_fontawesome_icon ) . '" aria-hidden ="true" ></i>';
						} else {
							if ( isset( $default_icon ) && ! empty( $default_icon ) ) {
								if ( strpos( $default_icon, 'fab' ) === false ) {
									$extra_class = 'fa';
								}
								$icon = '<i class= "' . esc_attr( $extra_class ) . ' ' . esc_attr( $default_icon ) . '" aria-hidden= "true"></i> ';
							} else {
								$icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
							}
						}
					} else {
						$icon = CTL_Helpers::ctl_static_svg_icons( 'clock' );
					}
				} elseif ( 'custom_image' === $icon_type ) {
					$icon = $this->get_story_custom_icon( $story_icon, $default_icon );
				}
			}
			$output .= '<div class="ctl-icon">';
			$output .= $icon;
			$output .= '</div> ';
			return $output;
		}
		/**
		 * Get Story Custom Image icon
		 *
		 * @param array  $story_icon story icon custom meta field.
		 * @param string $default_icon story icon custom meta field.
		 */
		public function get_story_custom_icon( $story_icon, $default_icon ) {
			$ctl_image_icon_id   = isset( $story_icon['story_img_icon']['id'] ) ? sanitize_text_field( $story_icon['story_img_icon']['id'] ) : '';
			$disable_fontawesome = $this->settings['disable_fontawesome'];
			$icon                = null;
			if ( '' !== $ctl_image_icon_id ) {
				$icon = wp_get_attachment_image( $ctl_image_icon_id, 'thumbnail', false, array( 'class' => 'ctp-icon-img' ) );
				if ( '' === $icon ) {
					if ( 'yes' === $disable_fontawesome ) {
						$icon = CTL_Helpers::ctl_static_svg_icons( 'clock' );
					} elseif ( ! empty( $default_icon ) ) {
						$icon = '<i class="' . esc_attr( $default_icon ) . '" aria-hidden="true"></i>';
					} else {
						$icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
					}
				}
			} elseif ( 'yes' === $disable_fontawesome ) {
				$icon = CTL_Helpers::ctl_static_svg_icons( 'clock' );
			} else {
				$icon = '<i class="' . esc_attr( $default_icon ) . '" aria-hidden="true"></i>';
			}
			return $icon;
		}

		/**
		 * Get Story Year and create highlighted Year section in timeline
		 *
		 * @param int $post_id    Current Post ID.
		 * @return string Output HTML.
		 */
		public function ctl_get_year_label( $post_id ) {
			$attributes = $this->attributes;
			$output     = '';
			$wrp_cls    = 'horizontal' === $attributes['layout'] ? 'horizontal' : 'vertical';
			$design     = 'light';
			$visibility = isset( $attributes['year-label'] ) ? sanitize_text_field( $attributes['year-label'] ) : 'show';
			if ( 'show' === $visibility || ( 'compact' === $attributes['layout'] && 'show' === $attributes['year-navigation'] ) ) {
				// Get story date.
				$ctl_story_type = get_post_meta( $post_id, 'story_type', true );
				$ctl_story_date = isset( $ctl_story_type['ctl_story_date'] ) ? sanitize_text_field( $ctl_story_type['ctl_story_date'] ) : '';
				$pattern        = "/\b\d{4}\b/"; // Regular expression pattern to match a four-digit number.
				preg_match( $pattern, $ctl_story_date, $matches );

				// Get Year value.
				if ( isset( $matches[0] ) ) {
					$story_year = sanitize_text_field( $matches[0] );
					// Display the year label if it is different from the previous year.
					if ( $story_year !== $this->active_year ) {

						$story_year_label = sprintf( '<div class="ctl-year-label ctl-year-text"><span>%s</span></div>', esc_html( $story_year ) );

						$this->active_year = $story_year;
						if ( 'compact' === $attributes['layout'] ) {
							$output .= '<span class="scrollable-section ctl-year-container" data-section-title="' . esc_attr( $story_year ) . '"></span>';
						} else {
							$output .= sprintf(
								'<!-- ' . $this->tm_type . ' Year Section --><div data-cls="sc-nv-%s %s" class="timeline-year scrollable-section ctl-year ctl-year-container %s-year" data-section-title="%s" id="year-%s">%s</div>',
								esc_attr( $design ),
								esc_attr( $wrp_cls ),
								esc_attr( $design ),
								esc_attr( $story_year ),
								esc_attr( $story_year ),
								apply_filters( 'ctl_story_year', $story_year_label )
							);
						}
					}
				}
			}

			return $output;
		}

			/**
			 * Get timeline stories slideshow
			 *
			 * @param int $post_id    Current Post ID.
			 * @return string Generated slideshow HTML.
			 */
		public function ctl_get_slideshow( $post_id ) {
			// Story Media.
			$attributes      = $this->attributes;
			$output          = '';
			$minimal_html    = '';
			$slides_html     = '';
			$active_design   = sanitize_text_field( $attributes['designs'] );
			$image_size      = 'design-7' === $active_design ? 'full' : 'large';
			$ctl_story_media = get_post_meta( $post_id, 'story_media', true );

			if ( empty( $ctl_story_media['ctl_slide'] ) ) {
				return '';
			}

			$slides = explode( ',', $ctl_story_media['ctl_slide'] );

			// Get slideshow settings.
			$slideshow_settings = $this->settings['slideshow'];
			$animation_speed    = $this->settings['animation_speed'];

			foreach ( $slides as $index ) {
				$slide = wp_get_attachment_image_src( $index, $image_size );

				if ( wp_get_attachment_image_src( $index ) !== false && isset( $slide[0] ) ) {

					$stories_images_link     = $this->settings['stories_images_link'];
					$image_gallery_open_tag  = '';
					$image_gallery_close_tag = '';

					if ( 'popup' === $stories_images_link ) {
						$image_gallery_open_tag  = '<a class="ctl_glightbox_gallery" data-gallery="ctl_glightbox_gallery[g_gallery-' . esc_attr( $post_id ) . ']" href="' . esc_url( wp_get_attachment_image_src( $index, 'full' )[0] ) . '">';
						$image_gallery_close_tag = '</a>';
					} elseif ( 'theme-popup' === $stories_images_link ) {
						$image_gallery_open_tag  = '<a class="" href="' . esc_url( wp_get_attachment_image_src( $index, 'full' )[0] ) . '">';
						$image_gallery_close_tag = '</a>';
					}

					if ( is_array( $slide ) && $slide[0] ) {
						$minimal_html .= '<div class="ctp_popup_slide" data-popup-image="' . esc_attr( $slide[0] ) . '"></div>';
						$image         = '<img class="story-image" src="' . esc_url( $slide[0] ) . '" class="no-lazyload skip-lazy">';
						$slides_html  .= '<div class="story-slide swiper-slide">' . $image_gallery_open_tag . $image . $image_gallery_close_tag . '</div>';
					}
				}
			}
			if ( ! empty( $slides_html ) ) {
				if ( 'design-7' === $active_design ) {
					$output .= '<div class="ctp_popup_slides">' . $minimal_html . '</div>';
				} else {
					$output .= '<div class="ctp-media-slider">';
					$output .= '<!-- ' . $this->tm_type . ' Slider Container -->
						<div id="story-' . esc_attr( $post_id ) . '" class="ctp-story-slider story-swiper-' . esc_attr( $post_id ) . '" data-swiper-autoplay="' . esc_attr( $slideshow_settings ) . '" data-swiper-speed="' . esc_attr( $animation_speed ) . '">
							<div class="story-swiper-wrapper swiper-wrapper">
								<!-- Slides -->';
					$output .= $slides_html;
					$output .= '</div>
							<div class="story-swiper-pagination"></div>
							<div class="story-swiper-button-prev">' . CTL_Helpers::ctl_static_svg_icons( 'chevron_left' ) . '</div>
							<div class="story-swiper-button-next">' . CTL_Helpers::ctl_static_svg_icons( 'chevron_right' ) . '</div>
						</div></div>';
				}
			}

			return $output;
		}

			/**
			 * Returns the timezone string for a site, even if it's set to a UTC offset
			 * Adapted from http : // www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
			 *
			 * @return string valid PHP timezone string
			 */
		public function ctl_wp_get_timezone_string() {
			// if site timezone string exists, return it.
			if ( $timezone = get_option( 'timezone_string' ) ) {
				return $timezone;
			}
				// get UTC offset, if it isn't set then return UTC.
			if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
				return 'UTC';
			}
				// adjust UTC offset from hours to seconds.
				$utc_offset *= 3600;
				// attempt to guess the timezone string from the UTC offset.
			if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
				return $timezone;
			}
				// last try, guess timezone string manually.
				$is_dst = date( 'I' );
			foreach ( timezone_abbreviations_list() as $abbr ) {
				foreach ( $abbr as $city ) {
					if ( $city['dst'] === $is_dst && $city['offset'] === $utc_offset ) {
						return $city['timezone_id'];
					}
				}
			}
				// fallback to UTC.
				return 'UTC';
		}

		/**
		 * Timeline Horizontal Nav Slider
		 *
		 * @param int $index current story index.
		 * @param int $id Current Post ID.
		 */
		public function ctl_hr_nav_slider( $index, $id ) {
			$post_id            = $id;
			$output             = '';
			$attributes         = $this->attributes;
			$nav_slider_designs = array( 'default', 'design-6', 'design-8' );
			if ( in_array( $attributes['designs'], $nav_slider_designs, true ) && 'horizontal' === $attributes['layout'] ) {
				$output .= '<div id="ctl-year-slide-' . esc_attr( $post_id ) . '" class="ctl-year-swiper-slide swiper-slide" data-story-index="' . esc_attr( $index ) . '">';
				if ( 'custom' === $attributes['based'] ) {
					$output .= $this->ctl_get_labels( $post_id );
				} else {
					if ( 'show' === $attributes['year-label'] ) {
						$output .= $this->ctl_get_year_label( $post_id );
					}
					$output .= $this->ctl_get_date( $post_id, sanitize_text_field( $attributes['date-format'] ) );
				}
				if ( 'dot' === $attributes['icons'] ) {
					$output .= '<div class="ctl-icondot"></div> ';
				} elseif ( 'none' !== $attributes['icons'] ) {
					$output .= $this->ctl_get_icon( $post_id, $this->settings['default_icon'] );
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

			$ctl_story_media = get_post_meta( $post_id, 'story_media', true );
			$media_format    = isset( $ctl_story_media['story_format'] ) ? sanitize_text_field( $ctl_story_media['story_format'] ) : '';

			$output .= '<div id="ctl-popup-' . esc_attr( $post_id ) . '" class="ctl_popup_hide">
				<div class="ctl_popup_container">
					<div class="ctl_popup_date">';
			$output .= 'custom' === $attributes['based'] ? $this->ctl_get_labels( $post_id, true ) : $this->ctl_get_date( $post_id, sanitize_text_field( $attributes['date-format'] ), true );
			$output .= '</div>
					<h2 class="ctl_popup_title">';
			$output .= wp_kses_post( get_the_title() );
			$output .= '</h2>
					<div class="ctl_popup_media">';
			switch ( $media_format ) {
				case 'video':
					$output .= $this->ctl_get_video( $post_id );
					break;
				case 'slideshow':
					$output .= $this->ctl_get_slideshow( $post_id );
					break;
				default:
					$output .= $this->ctl_get_featured_image( $post_id );
					break;
			}
			$output .= '</div>
					<div class="ctl_popup_desc">';
			$output .= $this->ctl_get_content();
			$output .= '</div>
				</div>
			</div>';

			return $output;
		}

		/**
		 * Timeline Stories Color
		 *
		 * @param int $post_id Current Post ID.
		 */
		public function ctl_story_color( $post_id ) {
			$style              = '';
			$ctl_extra_settings = get_post_meta( $post_id, 'extra_settings', true );
			$ctl_story_color    = isset( $ctl_extra_settings['ctl_story_color'] ) ? sanitize_text_field( $ctl_extra_settings['ctl_story_color'] ) : '';
			$cls                = 'horizontal' !== $this->attributes['layout'] ? '#ctl-story-' . esc_attr( $post_id ) . '.ctl-story' : ':where(#ctl-year-slide-' . esc_attr( $post_id ) . ',#ctl-story-' . esc_attr( $post_id ) . '.ctl-story)';

			if ( isset( $ctl_story_color ) && ! empty( $ctl_story_color ) && '#' !== $ctl_story_color ) {
				$style .= '.ctl-wrapper ' . $cls . '{
					--ctw-second-story-color : ' . esc_attr( $ctl_story_color ) . ';
					--ctw-first-story-color: ' . esc_attr( $ctl_story_color ) . ';
				}';
			}

			return $style;
		}

	}

}
