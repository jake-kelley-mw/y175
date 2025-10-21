<?php
/**
 * CTL Query Builder Class.
 *
 * @package CTL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CTL_Query_Builder' ) ) {

	/**
	 * Class CTL_Query_Builder.
	 */
	final class CTL_Query_Builder {

		/**
		 * Returns Query.
		 *
		 * @param array $attributes The shortcode attributes.
		 * @param array $settings The timeline settings.
		 */
		public static function story_get_query( $attributes, $settings ) {

			// prepare query args for timeline stories.
			$query_args = array(
				'post_status'    => array( 'publish', 'future' ),
				'post_type'      => 'cool_timeline',
				'date_query'     => array( 'before' => 'now' ),
				'order'          => isset( $attributes['order'] ) ? sanitize_text_field( $attributes['order'] ) : sanitize_text_field( $settings['story_orders'] ),
				'orderby'        => isset( $attributes['orderBy'] ) ? sanitize_text_field( $attributes['orderBy'] ) : 'date',
				'posts_per_page' => isset( $attributes['show-posts'] ) ? intval( $attributes['show-posts'] ) : intval( $settings['post_per_page'] ),
				'paged'          => isset( $attributes['paged'] ) ? absint( $attributes['paged'] ) : 1,
				'post__not_in'   => array( get_the_ID() ),
			);

			if ( ( isset( $attributes['category'] ) && ! empty( $attributes['category'] ) ) || ( isset( $attributes['filter_category'] ) && ! empty( $attributes['filter_category'] ) ) ) {

				$selected_cats = '';
				if ( isset( $attributes['filter_category'] ) && ! empty( $attributes['filter_category'] ) && 'all' !== $attributes['filter_category'] ) {
					$selected_cats = $attributes['filter_category'];
				} elseif ( ! empty( $attributes['category'] ) ) {
					$selected_cats = $attributes['category'];
					if ( strpos( $selected_cats, ',' ) !== false ) {
						$cat_arr       = explode( ',', $selected_cats );
						$selected_cats = array_map( 'trim', $cat_arr );
					}
				}

				if ( is_numeric( $selected_cats ) ) {
						$query_args['tax_query'] = array(
							array(
								'taxonomy' => 'ctl-stories',
								'field'    => 'term_id',
								'terms'    => $selected_cats,
							),
						);
				} elseif ( ! empty( $selected_cats ) ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'ctl-stories',
							'field'    => 'slug',
							'terms'    => $selected_cats,
						),
					);
				}
			}

			if ( isset( $attributes['based'] ) && 'custom' === $attributes['based'] ) {
				$query_args['meta_query'] = array(
					'ctl_story_order' => array(
						'key'     => 'ctl_story_order',
						'compare' => 'EXISTS',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => 'story_based_on',
						'value'   => 'custom',
						'compare' => '=',
					),
				);
				$query_args['orderby']    = array(
					'ctl_story_order' => sanitize_text_field( $attributes['order'] ),
				);
			} else {
				$query_args['meta_query'] = array(
					array(
						'key'     => 'ctl_story_timestamp',
						'compare' => 'EXISTS',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => 'story_based_on',
						'value'   => 'default',
						'compare' => '=',
					),

				);

				$query_args['orderby'] = array(
					'ctl_story_timestamp' => sanitize_text_field( $attributes['order'] ),
				);

			}

			$query_args = apply_filters( 'ctp_story_query_args', $query_args, $attributes );
			return new WP_Query( $query_args );
		}

		/**
		 * Post timeline query
		 *
		 * @param array $attributes The shortcode attributes.
		 * @param array $settings The timeline settings.
		 *
		 * Return posts object
		 */
		public static function post_get_query( $attributes, $settings ) {
			$meta_key = isset( $attributes['meta-key'] ) && ! empty( $attributes['meta-key'] ) ? sanitize_text_field( $attributes['meta-key'] ) : '';
			// prepare query args for timeline stories.
			$query_args = array(
				'post_status'    => array( 'publish' ),
				'post_type'      => sanitize_text_field( $attributes['post-type'] ),
				'date_query'     => array( 'before' => 'now' ),
				'order'          => isset( $attributes['order'] ) ? sanitize_text_field( $attributes['order'] ) : sanitize_text_field( $settings['story_orders'] ),
				'orderby'        => isset( $attributes['orderBy'] ) ? sanitize_text_field( $attributes['orderBy'] ) : 'date',
				'posts_per_page' => isset( $attributes['show-posts'] ) ? intval( $attributes['show-posts'] ) : intval( $settings['post_per_page'] ),
				'paged'          => isset( $attributes['paged'] ) ? absint( $attributes['paged'] ) : 1,
				'post__not_in'   => array( get_the_ID() ),
			);

			$post_category = isset( $attributes['post-category'] ) ? sanitize_text_field( $attributes['post-category'] ) : '';
			$post_taxonomy = isset( $attributes['taxonomy'] ) ? sanitize_text_field( $attributes['taxonomy'] ) : '';
			if ( ! empty( $post_taxonomy ) && ! empty( $post_category ) ) {

				if ( strpos( $post_category, ',' ) !== false ) {
					$post_category = explode( ',', $post_category );
					$post_category = array_map( 'trim', $post_category );
				} else {
					$post_category = $post_category;
				}
				if ( empty( $attributes['category'] ) || 'all' === $attributes['category'] ) {
					$cate = $post_category;
				} else {
					$cate = sanitize_text_field( $attributes['category'] );
				}
				$query_args['tax_query'] = array(
					array(
						'taxonomy' => $post_taxonomy,
						'field'    => 'slug',
						'terms'    => $cate,
					),
				);
			}
			if ( ! empty( $attributes['tags'] ) ) {
				$query_args['tag'] = sanitize_text_field( $attributes['tags'] );
			}
			if ( ! empty( $meta_key ) ) {
				$query_args['meta_query'] = array(
					'key'     => $meta_key,
					'compare' => 'EXISTS',
				);
				$query_args['meta_key']   = $meta_key;
				$query_args['orderby']    = array(
					'meta_value' => sanitize_text_field( $query_args['order'] ),
				);
			}

			$query_args = apply_filters( 'ctp_post_query_args', $query_args, $attributes );

			return new WP_Query( $query_args );
		}
	}

}
