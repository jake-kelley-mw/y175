<?php
/**
 * CTL Helper.
 *
 * @package CTL
 */

/**
 * Do not access the page directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CTL_Helpers' ) ) {
	/**
	 * Class Helpers
	 */
	class CTL_Helpers {

		/**
		 * Timeline Stories Load More Pagination
		 *
		 * @param string $current_page_id current page id.
		 * @param string $layout shortcode layout.
		 */
		public static function has_shortcode_added( $current_page_id, $layout ) {
			// Get the list of saved page IDs.
			$ctl_shortcode_page_ids = get_option( 'ctl_shortcode_page_ids', array() );

			// Add the current page ID to the array if it's not already there.
			if ( ! in_array( $current_page_id, $ctl_shortcode_page_ids, true ) ) {
				$ctl_shortcode_page_ids[] = $current_page_id;
				update_option( 'ctl_shortcode_page_ids', $ctl_shortcode_page_ids );
				$ctl_used_layout                       = get_option( 'ctl_layout_used', array() ); // Ensure we retrieve existing layout data
				$ctl_used_layout[ $current_page_id ][] = $layout === 'default' ? 'vertical' : $layout;
				update_option( 'ctl_layout_used', $ctl_used_layout );
			}
		}

		/**
		 * Get post type from url
		 */
		public static function ctl_get_ctp() {
			global $post, $typenow, $current_screen;
			if ( $post && $post->post_type ) {
				return $post->post_type;
			} elseif ( $typenow ) {
				return $typenow;
			} elseif ( $current_screen && $current_screen->post_type ) {
				return $current_screen->post_type;
			} elseif ( isset( $_REQUEST['post_type'] ) ) {
				return sanitize_key( $_REQUEST['post_type'] );
			}
			return null;
		}

		/**
		 * Old version deprecated animation list.
		 */
		public static function get_deprecated_animations() {
			$deprecated_animations = array(
				'bounceInUp',
				'bounceInDown',
				'bounceInLeft',
				'bounceInRight',
				'slideInDown',
				'slideInUp',
				'bounceIn',
				'slideInLeft',
				'slideInRight',
				'shake',
				'wobble',
				'swing',
				'jello',
				'flip',
				'fadein',
				'rotatein',
				'zoomIn',
			);
			return $deprecated_animations;
		}

		/**
		 * Get Timeline Excerpt Content.
		 *
		 * @param array $attributes timeline attributes.
		 * @param array $settings settings settings.
		 */
		public static function ctl_get_excerpt( $attributes, $settings ) {
			global $post;
			$post_id           = get_the_ID();
			$post_content      = ! empty( $post->post_excerpt ) && 'story_timeline' !== $attributes['ctl_type'] ? $post->post_excerpt : apply_filters( 'the_content', get_the_content() );
			$length            = isset( $attributes['content-length'] ) ? intval( $attributes['content-length'] ) : 55; // Ensure length is an integer
			$display_read_more = isset( $attributes['read-more'] ) ? $attributes['read-more'] : 'hide'; // Default to hide if not set
			$read_more_link    = '';

			// display read more btn if read more yes.
			if ( 'show' === $display_read_more ) {
				$ctl_extra_settings = get_post_meta( $post_id, 'extra_settings', true );

				if ( isset( $ctl_extra_settings['story_custom_link']['url'] ) && ! empty( $ctl_extra_settings['story_custom_link']['url'] ) && 'story_timeline' === $attributes['ctl_type'] ) {
					$url = esc_url( $ctl_extra_settings['story_custom_link']['url'] ); // Sanitize URL
				} else {
					$url = esc_url( get_the_permalink( $post_id ) ); // Sanitize URL
				}
				$read_more_lbl   = ! empty( $settings['read_more_lbl'] ) ? esc_html( $settings['read_more_lbl'] ) : __( 'Read More', 'cool-timeline' );
				$target          = ! empty( $settings['story_link_target'] ) ? esc_attr( $settings['story_link_target'] ) : '_self';
				$read_more_link .= '&hellip;<br><a class="read_more ctl_read_more" target="' . esc_attr( $target ) . '" href="' . esc_url( $url ) . '" role="link">' . esc_html( $read_more_lbl ) . '</a>';
			}

			$excerpt = wpautop(
				// wp_trim_words() gets the first X words from a text string.
				wp_trim_words(
					$post_content, // We'll use the post's content as our text string.
					$length, // We want the first X words.
					$read_more_link // This is what comes after the first X words.
				)
			);

			return $excerpt;

		}

		/**
		 * Grab taxonomy list
		 */
		public static function ctl_entry_taxonomies() {
			$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'cool-timeline' ) );
			$cat_meta        = '';
			if ( $categories_list ) {
				$cat_meta .= sprintf(
					'<i class="fa fa-folder-open" aria-hidden="true"></i><span class="cat-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
					_x( 'Categories', 'Used before category names.', 'cool-timeline' ),
					$categories_list
				);
			}
			return $cat_meta;
		}

		/**
		 * Grab post types
		 */
		public static function ctl_post_tags() {
			$tags_list = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'cool-timeline' ) );
			if ( $tags_list ) {
				return sprintf(
					'<span class="tags-links"><i class="fa fa-bookmark"></i><span class="screen-reader-text">%1$s </span>%2$s</span>',
					_x( 'Tags', 'Used before tag names.', 'cool-timeline' ),
					$tags_list
				);
			}
		}

		/**
		 * Category filter HTML.
		 *
		 * @param string $taxonomy story taxonomy.
		 * @param string $select_cat user selected category.
		 * @param string $type story type.
		 * @param array  $filter_categories category filter array.
		 * @param string $based story based on.
		 * @param int    $timeline_id timeline id.
		 */
		public static function ctl_category_filter( $taxonomy, $select_cat, $type, $filter_categories, $based, $design, $timeline_id = null ) {
			$filters_html = '';
			$selected     = '';

			if ( isset( $select_cat ) && '' !== $select_cat ) {
				$selected = sanitize_key( $select_cat ); // Sanitize selected category
			}

			if ( 'story-tm' === $type ) {
				$taxonomy = $taxonomy ? $taxonomy : 'ctl-stories';
				$action   = 'story_timeline_cat_filters';
			} else {
				$taxonomy = $taxonomy ? $taxonomy : 'category';
				$action   = 'post_timeline_cat_filters';
			}

			$taxonomy = apply_filters( 'ctl-taxonomy-filter', $taxonomy );

			$args  = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			);
			$terms = get_terms( $args );

			$terms          = apply_filters( 'ctl-category-filter', $terms );
			$total_posts    = 0;
			$categories_arr = array();
			$category_arr   = array();
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_slug = sanitize_key( $term->slug ); // Sanitize term slug

					if ( $term_slug && 'uncategorized' !== $term_slug ) {
						$total_posts                 += $term->count;
						$categories_arr[ $term_slug ] = array(
							'name'   => esc_html( $term->name ), // Sanitize term name
							'count'  => $term->count,
							'id'     => $term->term_id,
							'active' => ( $term_slug === $selected ) ? 'active-category' : '',
						);
					}
				}
			}

			if ( is_array( $filter_categories ) && ! empty( $filter_categories ) ) {
				$filter_categories = array_unique( $filter_categories );
				foreach ( $filter_categories as $cat_slug ) {
					if ( isset( $categories_arr[ $cat_slug ] ) ) {
						$category_arr[ $cat_slug ] = $categories_arr[ $cat_slug ];
					}
				}
			} else {
				$category_arr = $categories_arr;
			}

			$display_cat = ! empty( $filter_categories ) || 'story-tm' !== $type ? array_keys( $category_arr ) : self::category_post_count( $categories_arr, $based );
			$total_cat   = count( $display_cat );

			$dropdown_cls = $total_cat > 4 ? 'ctl-category-dropdown ' : '';

			// Use ternary operators to simplify the assignments.
			$btn_selected_category = ( ( ! empty( $filter_categories ) && ! in_array( $select_cat, $filter_categories, true ) ) || strpos( $select_cat, ',' ) !== false ) ? '' : $select_cat;
			$button_text           = ! empty( $btn_selected_category ) ? esc_html( $btn_selected_category ) : __( 'SELECT CATEGORY', 'cool-timeline' );

			$filters_html .= '<div data-timeline-type="' . esc_attr( $type ) . '" data-action="' . esc_attr( $action ) . '" class="' . esc_attr( $dropdown_cls ) . 'ctl-category-container ctl-category-' . esc_attr( $design ) . '" data-id="' . esc_attr( $timeline_id ) . '">';

			$filters_html .= '<button aria-haspopup="true" role="button" data-value="" class="ctl-category-dropdown-button"><span>' . $button_text . '</span> ' . self::ctl_static_svg_icons( 'chevron_up' ) . '</button>';

			$filters_html .= '<ul class="ctl-category" role="menu">';

			if ( ! in_array( 'all', array_keys( $category_arr ) ) ) {
				$filters_html .= sprintf(
					'<li class="ctl-category-item" role="menuitem"><a data-post-count="%1$s" href="#" data-tm-type="%2$s" data-action="%3$s" data-term-slug="all" aria-label="%4$s - %1$s posts">%4$s</a></li>',
					$total_posts,
					esc_attr( $type ),
					$action,
					__( 'All', 'cool-timeline' )
				);
			}

			if ( ! empty( $category_arr ) ) {
				foreach ( $category_arr as $cat_slug => $cat_info ) {
					if ( in_array( $cat_slug, $display_cat, true ) ) {
						$active        = $cat_info['active'];
						$count_attr    = esc_attr( $cat_info['count'] );
						$slug_attr     = esc_attr( $cat_slug );
						$name_attr     = esc_html( $cat_info['name'] );
						$active_attr   = $active ? ' active-category' : '';
						$filters_html .= sprintf(
							'<li class="ctl-category-item" role="menuitem"><a data-post-count="%1$s" href="#" %2$s data-term-slug="%3$s" aria-label="%4$s - %1$s posts">%4$s</a></li>',
							$count_attr,
							$active_attr,
							$slug_attr,
							$name_attr
						);
					}
				}
			}

			$filters_html .= '</ul></div>';

			return $filters_html;
		}

		/**
		 * Get Category Post Counts with Custom Meta Value
		 *
		 * @param array  $categories An array of category data containing 'id' and 'slug' keys.
		 * @param string $based story based on.
		 * @return array Associative array with category slugs as keys and post counts as values.
		 */
		public static function category_post_count( $categories, $based ) {
			global $wpdb;

			$meta_key             = 'story_based_on';
			$meta_value           = $based;
			$category_post_counts = array();

			// Extract category IDs into an array.
			$category_ids = wp_list_pluck( $categories, 'id' );
			if ( empty( $category_ids ) ) {
				return array(); // No categories provided, return an empty array.
			}

			$category_ids = implode( ',', $category_ids );

			$query = "SELECT t.slug AS category_slug, COUNT(*) AS post_count
            FROM {$wpdb->prefix}term_relationships AS tr
            INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->prefix}terms AS t ON tt.term_id = t.term_id
            INNER JOIN {$wpdb->prefix}posts AS p ON tr.object_id = p.ID
            INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
            WHERE tr.term_taxonomy_id IN ($category_ids)";
			if ( null !== $based ) {
				$query .= ' AND pm.meta_key = %s
            	AND pm.meta_value = %s';
			}
			$query .= ' GROUP BY tr.term_taxonomy_id';

			if ( null !== $based ) {
				$query = $wpdb->prepare( $query, $meta_key, $meta_value );
			}

			$results = $wpdb->get_results( $query, ARRAY_A );

			// Store the category slugs and their corresponding post counts in an associative array.
			foreach ( $results as $result ) {
				$category_post_counts[] = sanitize_text_field( $result['category_slug'] ); // Ensure post count is an integer
			}

			return $category_post_counts;
		}

		/**
		 * Timeline before title and image
		 *
		 * @param object $settings Timeline settings object.
		 */
		public static function timeline_before_content( $title, $settings ) {
			$output         = '';
			$title_enable   = isset( $settings['timeline_title_enable'] ) ? $settings['timeline_title_enable'] : 'no'; // Default to 'no'
			$title_text     = $title;
			$timeline_image = isset( $settings['timeline_image'] ) ? $settings['timeline_image'] : array();
			$title_tag      = isset( $settings['timeline_title_tag'] ) ? $settings['timeline_title_tag'] : 'h2'; // Default to 'h2'

			// if ( ! empty( $timeline_image['id'] ) || ( 'yes' === $title_enable && ! empty( $title_text ) ) ) {
			if ( ! empty( $title_text ) ) {
				$output .= '<div class="ctl-before-content">';

				// if ( ! empty( $timeline_image['id'] ) ) {
				// $user_avatar = wp_get_attachment_image_src( $timeline_image['id'], 'ctl_avatar' );
				// $output     .= sprintf(
				// '<div class="ctl-avatar"><span title="%s"><img src="%s" alt="%s"></span></div>',
				// esc_attr( $title_text ),
				// esc_url( $user_avatar[0] ),
				// esc_attr( $title_text )
				// );
				// }

				if ( 'yes' === $title_enable && ! empty( $title_text ) ) {
					$output .= '<div class="timeline-main-title"><' . esc_html( $title_tag ) . '>' . esc_html( $title_text ) . '</' . esc_html( $title_tag ) . '></div>';
				}

				$output .= '</div>';
			}

			return $output;
		}

		/**
		 * Timeline Stories Default Pagination
		 *
		 * @param WP_Query $wp_query WP_Query object.
		 * @param int      $paged current page number.
		 */
		public static function ctl_pagination( $wp_query, $paged, $fontawesome ) {
			$output   = '';
			$numpages = $wp_query->max_num_pages;
			if ( ! $numpages ) {
				$numpages = 1;
			}
			$big        = 999999999;
			$of_lbl     = __( ' of ', 'cool-timeline' );
			$page_lbl   = __( ' Page ', 'cool-timeline' );
			$prev_arrow = $fontawesome ? '<i class="fas fa-angle-double-left"></i>' : self::ctl_static_svg_icons( 'double_left' );
			$next_arrow = $fontawesome ? '<i class="fas fa-angle-double-right"></i>' : self::ctl_static_svg_icons( 'double_right' );

			$pagination_args = array(
				'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'       => '?paged=%#%',
				'total'        => $numpages,
				'current'      => $paged,
				'show_all'     => false,
				'end_size'     => 1,
				'mid_size'     => 1,
				'prev_next'    => true,
				'prev_text'    => $prev_arrow,
				'next_text'    => $next_arrow,
				'type'         => 'plain',
				'add_args'     => false,
				'add_fragment' => '',
			);

			$paginate_links = paginate_links( $pagination_args );

			if ( $paginate_links ) {
				$output  = '<nav class="ctl-pagination" aria-label="Timeline Navigation">';
				$output .= '<span class="page-numbers ctl-page-num" role="status">' . $page_lbl . esc_html( $paged ) . $of_lbl . esc_html( $numpages ) . '</span> ';
				$output .= $paginate_links;
				$output .= '</nav>';
				return $output;
			}
			return '';
		}

		/**
		 * Timeline Stories Load More Pagination
		 *
		 * @param WP_Query $wp_query WP_Query object.
		 * @param int      $paged current page number.
		 */
		public static function ctl_load_more( $wp_query, $paged ) {
			$load_more = '<div class="ctl_load_more_pagination">
				<button data-max-num-pages="' . esc_attr( $wp_query->max_num_pages ) . '" 
				data-page="' . esc_attr( $paged ) . '"
				class="ctl_load_more" role="button">
				<span class="clt_loading_state" style="display:none">' . self::ctl_static_svg_icons( 'spinner' ) . __( ' Loading', 'cool-timeline' ) . '</span> <span class="default_state">' . __( 'Load More', 'cool-timeline' ) . '</span></button></div>';

			return $load_more;
		}

		/**
		 * Adjust the title color based on shortcode attributes.
		 *
		 * @param array  $attr        Shortcode attributes.
		 * @param string $settings settings object.
		 * @return string Generated CSS style.
		 */
		public static function ctl_timeline_custom_style( $attr, $settings ) {
			// Extract attributes and settings.
			$ctl_version     = get_option( 'cool-timelne-pro-v', false );
			$layout          = isset( $attr['layout'] ) ? $attr['layout'] : 'default'; // Default to 'default'
			$design          = isset( $attr['designs'] ) ? $attr['designs'] : 'default'; // Default to 'default'
			$title_color     = isset( $settings['title_color'] ) ? $settings['title_color'] : '#fff'; // Default to black
			$year_bg_color   = isset( $settings['year_bg_color'] ) ? $settings['year_bg_color'] : '#fff'; // Default to white
			$year_text_color = isset( $settings['year_label_color'] ) ? $settings['year_label_color'] : '#fff'; // Default to black

			// Use more meaningful variable names.
			$design_wrapper_class = isset( $attr['config']['wrapper_cls']['ctl_design_cls'] ) ? $attr['config']['wrapper_cls']['ctl_design_cls'] : '';
			$layout_wrapper_class = isset( $attr['config']['wrapper_cls']['ctl_layout_cls'] ) ? $attr['config']['wrapper_cls']['ctl_layout_cls'] : '';

			$style             = '';
			$output            = '';
			$horizontal_layout = ( 'default' === $design && 'horizontal' === $layout );
			$vertical_layout   = ( 'design-6' === $design && ( 'default' === $layout || 'one-side' === $layout ) );
			$compact_layout    = ( 'compact' === $layout && ( in_array( $design, array( 'default', 'design-2', 'design-4' ), true ) && 'main-title' !== $attr['compact-ele-pos'] ) || 'design-6' === $design );

			$data = '.ctl-wrapper .cool-timeline-wrapper.' . esc_attr( $design_wrapper_class ) . '.' . esc_attr( $layout_wrapper_class ) . ' {';
			// Check if the title color is white.
			if ( '#fff' === $title_color || '#ffffff' === $title_color ) {
				// Define conditions where the title color should be adjusted.
				if ( 'design-3' === $design || $horizontal_layout || $compact_layout || $vertical_layout ) {
					// Append CSS styles for adjusting the title color.
					$style .= '--ctw-cbx-title-color: #666666;';
				}
			}

			// Design-5 default color replace.
			if ( 'design-5' === $design ) {
				// year label background color replace.
				if ( '#38aab7' === $year_bg_color || '#025149' === $year_bg_color ) {
					$style .= '--ctw-ybx-bg: #ffffff;';
				}

				// year label text color replace.
				if ( '#ffffff' === $year_text_color ) {
					$style .= '--ctw-ybx-text-color: var(--ctw-line-bg);';
				}
			} else {
				if ( version_compare( $ctl_version, '4.5', '<' ) && '#38aab7' === $year_bg_color ) {
					$style .= '--ctw-ybx-bg: #025149;';
				}
			}

			if ( ! empty( $style ) ) {
				$output = $data . $style . '}';
			}

			// Return the generated style string.
			return $output;
		}

		/**
		 * Static svg icon html
		 *
		 * @param string $icon icon type.
		 */
		public static function ctl_static_svg_icons( $icon ) {
			$icons_arr = array(
				'clock'         => '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
				<path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8L334.6 349c-3.9 5.3-11.4 6.5-16.8 2.6z"/>
				</svg>',
				'chevron_left'  => '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
				<path d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z"/>
				</svg>',
				'chevron_right' => '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
				<path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/>
				</svg>',
				'chevron_up'    => '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512">
				<path d="M240.971 130.524l194.343 194.343c9.373 9.373 9.373 24.569 0 33.941l-22.667 22.667c-9.357 9.357-24.522 9.375-33.901.04L224 227.495 69.255 381.516c-9.379 9.335-24.544 9.317-33.901-.04l-22.667-22.667c-9.373-9.373-9.373-24.569 0-33.941L207.03 130.525c9.372-9.373 24.568-9.373 33.941-.001z"/>
				</svg>',
				'double_right'  => '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512">
				<path d="M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34zm192-34l-136-136c-9.4-9.4-24.6-9.4-33.9 0l-22.6 22.6c-9.4 9.4-9.4 24.6 0 33.9l96.4 96.4-96.4 96.4c-9.4 9.4-9.4 24.6 0 33.9l22.6 22.6c9.4 9.4 24.6 9.4 33.9 0l136-136c9.4-9.2 9.4-24.4 0-33.8z"/>
				</svg>',
				'double_left'   => '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512">
				<path d="M223.7 239l136-136c-9.4-9.4-24.6-9.4-33.9 0l-22.6 22.6c-9.4 9.4-9.4 24.6 0 33.9L319.9 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9L393.7 409c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34zm-192 34l136 136c9.4 9.4 24.6 9.4 33.9 0l22.6-22.6c9.4-9.4 9.4-24.6 0-33.9L127.9 256l96.4-96.4c9.4-9.4 9.4-24.6 0-33.9L201.7 103c-9.4-9.4-24.6-9.4-33.9 0l-136 136c-9.5 9.4-9.5 24.6-.1 34z"/>
				</svg>',
				'play_button'   => '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512">
				<path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/>
				</svg>',
				'spinner'       => '<svg class="ctl-loader-spinner" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
				<path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/>
				</svg>',
			);

			$data = isset( $icons_arr[ $icon ] ) ? $icons_arr[ $icon ] : '';
			return $data;
		}

		/**
		 * Create own custom timestamp for stories
		 *
		 * @param string $story_date Get Story Date.
		 */
		public static function ctl_generate_custom_timestamp( $story_date ) {
			if ( ! empty( $story_date ) ) {
				$ctl_story_date = strtotime( $story_date );
				if ( $ctl_story_date !== false ) {
					$story_timestamp = date( 'YmdHi', $ctl_story_date );
				} else {
					$split_date = explode( ' ', $story_date );
					if ( is_array( $split_date ) && count( $split_date ) > 1 ) {
						// grab story date
						$date_arr = explode( '/', $split_date[0] );
						// convert into 24 format
						$time           = $split_date[1] . ' ' . $split_date[2];
						$converted_time = date( 'Hi', strtotime( $time ) );
						if ( is_array( $date_arr ) ) {
							// create custom timestamps
							$story_timestamp = $date_arr[2] . $date_arr[0] . $date_arr[1] . $converted_time;
						}
					}
				}
				return isset( $story_timestamp ) ? $story_timestamp : ''; // Ensure we return a value
			}
			return ''; // Return empty if no date provided
		}

		/**
		 * Integrated custom route for timeline categories
		 */
		public static function ctl_register_routes() {
			register_rest_route(
				'cooltimeline/v1',
				'/categories',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'ctl_route_callback' ),
					'permission_callback' => '__return_true',
				)
			);
		}

		/**
		 * endpoint callback handlers
		 */
		public static function ctl_route_callback() {
			$category = array();
			if ( version_compare( get_bloginfo( 'version' ), '4.5.0', '>=' ) ) {
				$terms = get_terms(
					array(
						'taxonomy'   => 'ctl-stories',
						'hide_empty' => false,
					)
				);
			} else {
				$terms = get_terms(
					'ctl-stories',
					array(
						'hide_empty' => false,
					)
				);
			}
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$ctl_terms_l[ sanitize_key( $term->slug ) ] = esc_html( $term->name ); // Sanitize and escape term data
				}
			}
			if ( isset( $ctl_terms_l ) && array_filter( $ctl_terms_l ) != null ) {
				$category['categories'] = $ctl_terms_l;
			} else {
				$category['categories'] = array( '0' => 'No category' );
			}
			return $category;
		}

		/**
		 * Migrate stories from old PRO version
		 */
		public static function ctl_migrate_pro_old_stories() {
			$args  = array(
				'post_type'   => 'cool_timeline',
				'post_status' => array( 'publish', 'future' ),
				'numberposts' => -1,
			);
			$posts = get_posts( $args );
			if ( isset( $posts ) && is_array( $posts ) && ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$ctl_story_date = get_post_meta( $post->ID, 'ctl_story_date', true );
					if ( ! empty( $ctl_story_date ) ) {
						$story_timestamp = self::ctl_generate_custom_timestamp( $ctl_story_date );
						update_post_meta( $post->ID, 'ctl_story_timestamp', $story_timestamp );
					}
				}
			}
		}

		/**
		 * Migrate stories from cool timeline free version
		 *
		 * @param string $version get free timeline version.
		 */
		public static function ctl_migrate_from_free( $version ) {
			$args  = array(
				'post_type'   => 'cool_timeline',
				'post_status' => array( 'publish', 'future' ),
				'numberposts' => -1,
			);
			$posts = get_posts( $args );

			if ( isset( $posts ) && is_array( $posts ) && ! empty( $posts ) ) {
				foreach ( $posts as $post ) {

					if ( $version === 'old' ) {
						$published_date = get_the_date( 'm/d/Y h:i a', $post->ID );
						if ( $published_date ) {
							update_post_meta( $post->ID, 'ctl_story_date', $published_date );
							$story_timestamp = self::ctl_generate_custom_timestamp( $published_date );
							update_post_meta( $post->ID, 'ctl_story_timestamp', $story_timestamp );
						}
					}

					if ( ! get_post_meta( $post->ID, 'story_based_on', true ) ) {
						update_post_meta( $post->ID, 'story_based_on', 'default' );
					}
					$post_categories = get_the_terms( $post->ID, 'ctl-stories' );
					if ( empty( $post_categories ) || is_wp_error( $post_categories ) ) {
						wp_set_object_terms( $post->ID, 'timeline-stories', 'ctl-stories', true );
					}
				}
			}
		}

	}

}
