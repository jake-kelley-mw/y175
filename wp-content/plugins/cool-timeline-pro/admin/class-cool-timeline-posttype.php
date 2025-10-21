<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoolTimelinePosttype' ) ) {

	class CoolTimelinePosttype {

		/**
		 * The Constructor
		 */
		public function __construct() {
			
			// register actions
			add_action( 'init', array( $this, 'cooltimeline_custom_post_type' ) );

			if ( is_admin() ) {
				add_filter( 'manage_edit-cool_timeline_columns', array( $this, 'add_new_cool_timeline_columns' ) );
				add_action( 'manage_cool_timeline_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
				add_action( 'wp_ajax_ctl_change_story_order', array( $this, 'ctl_change_story_order' ) );
			}

			add_action( 'init', array( $this, 'ctl_taxonomy' ), 0 );
			add_action( 'init', array( $this, 'ctl_insert_category' ), 0 );
			add_action( 'init', array( $this, 'add_custom_rewrite_rule' ) );
			add_action( 'save_post_cool_timeline', array( $this, 'mfields_set_default_object_terms' ), 100, 2 );

			add_action( 'template_redirect', array( $this, 'ctl_template_redirect' ) );

			add_filter( 'parse_query', array( $this, 'ctl_convert_term_id_to_slug' ) );
			add_action( 'restrict_manage_posts', array( $this, 'ctl_filter_by_taxonomy' ) );

			add_action( 'save_post', array( $this, 'save_timeline_story_meta' ), 10, 3 );

		}

		// Cool Timeline post show in menu for.
		public function ctl_post_user_role( $type ) {
			$ctl_options_arr = get_option( 'cool_timeline_settings' );
			$show_in_menu    = 'cool-plugins-timeline-addon';
			$capability      = 'page';
			if ( is_user_logged_in() && isset( $ctl_options_arr['ctl_user_role'] ) && ! empty( $ctl_options_arr ) ) {
				$ctluser_role = $ctl_options_arr['ctl_user_role'];
				$current_user = wp_get_current_user()->roles[0];
				if ( $ctluser_role === $current_user ) {
					$show_in_menu = true;
					$capability   = 'post';
				}
			}
			if ( 'menu' === $type ) {
				return $show_in_menu;
			} elseif ( 'capability' === $type ) {
				return $capability;
			}
		}

		// END public function __construct())

		// Register Custom Post Type
		function cooltimeline_custom_post_type() {

			$labels = array(
				'name'               => _x( 'Timeline Stories', 'Post Type General Name', 'cool-timeline' ),
				'singular_name'      => _x( 'Timeline Stories', 'Post Type Singular Name', 'cool-timeline' ),
				'menu_name'          => __( 'Timeline Stories', 'cool-timeline' ),
				'name_admin_bar'     => __( 'Timeline Stories', 'cool-timeline' ),
				'parent_item_colon'  => __( 'Parent Item:', 'cool-timeline' ),
				'all_items'          => __( 'Cool Timeline Stories', 'cool-timeline' ),
				'add_new_item'       => __( 'Add New Story', 'cool-timeline' ),
				'add_new'            => __( 'Add New', 'cool-timeline' ),
				'new_item'           => __( 'New Story', 'cool-timeline' ),
				'edit_item'          => __( 'Edit Story', 'cool-timeline' ),
				'update_item'        => __( 'Update Story', 'cool-timeline' ),
				'view_item'          => __( 'View Story', 'cool-timeline' ),
				'search_items'       => __( 'Search Story', 'cool-timeline' ),
				'not_found'          => __( 'Not found', 'cool-timeline' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'cool-timeline' ),
			);

			 $ctl_options_arr = get_option( 'cool_timeline_settings' );
			if ( isset( $ctl_options_arr['post_type_slug'] ) && ! empty( $ctl_options_arr['post_type_slug'] ) ) {
				$slug_arr = array( 'slug' => $ctl_options_arr['post_type_slug'] );
			} else {
				$slug_arr = array( 'slug' => 'timeline' );
			}

			$args = array(
				'label'               => __( 'cool-timeline', 'cool-timeline' ),
				'description'         => __( 'Timeline Post Type Description', 'cool-timeline' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'author' ),
				'taxonomies'          => array(),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => $this->ctl_post_user_role( 'menu' ),
				'menu_position'       => 11,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'rewrite'             => $slug_arr,
				'menu_icon'           => CTP_PLUGIN_URL . 'assets/images/timeline-icon-222.png',
				'capability_type'     => $this->ctl_post_user_role( 'capability' ),
			);

			register_post_type( 'cool_timeline', $args );
		}

		/**
		 * Save post metadata when a story is saved.
		 *
		 * @param int  $post_id The post ID.
		 * @param post $post The post object.
		 * @param bool $update Whether this is an existing post being updated or not.
		 */
		function save_timeline_story_meta( $post_id, $post, $update ) {
			$post_type = get_post_type( $post_id );
			// If this isn't a 'cool_timeline' post, don't update it.
			if ( 'cool_timeline' != $post_type ) {
				return;
			}
			// - Update the post's metadata.

			if ( isset( $_POST['ctl_post_meta']['story_type']['ctl_story_date'] ) ) {
				$story_date      = sanitize_text_field( $_POST['ctl_post_meta']['story_type']['ctl_story_date'] );
				$story_timestamp = CTL_helpers::ctl_generate_custom_timestamp( $story_date );
				update_post_meta( $post_id, 'ctl_story_timestamp', $story_timestamp );
				update_post_meta( $post_id, 'ctl_story_date', $story_date );
			}
			if ( isset( $_POST['ctl_post_meta']['story_type']['story_based_on'] ) ) {
				$story_based_on = sanitize_text_field( $_POST['ctl_post_meta']['story_type']['story_based_on'] );
				update_post_meta( $post_id, 'story_based_on', $story_based_on );
			}
			if ( isset( $_POST['ctl_post_meta']['story_type']['ctl_story_order'] ) ) {
				$ctl_story_order = intval( $_POST['ctl_post_meta']['story_type']['ctl_story_order'] );
				update_post_meta( $post_id, 'ctl_story_order', $ctl_story_order );
			}
		}

		// Register Custom Taxonomy
		function ctl_taxonomy() {

			$labels = array(
				'name'                       => _x( 'Categories', 'Taxonomy General Name', 'cool-timeline' ),
				'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'cool-timeline' ),
				'menu_name'                  => __( 'Categories', 'cool-timeline' ),
				'all_items'                  => __( 'All Items', 'cool-timeline' ),
				'parent_item'                => __( 'Parent Item', 'cool-timeline' ),
				'parent_item_colon'          => __( 'Parent Item:', 'cool-timeline' ),
				'new_item_name'              => __( 'New Item Name', 'cool-timeline' ),
				'add_new_item'               => __( 'Add New Item', 'cool-timeline' ),
				'edit_item'                  => __( 'Edit Item', 'cool-timeline' ),
				'update_item'                => __( 'Update Item', 'cool-timeline' ),
				'view_item'                  => __( 'View Item', 'cool-timeline' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'cool-timeline' ),
				'add_or_remove_items'        => __( 'Add or remove items', 'cool-timeline' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'cool-timeline' ),
				'popular_items'              => __( 'Popular Items', 'cool-timeline' ),
				'search_items'               => __( 'Search Items', 'cool-timeline' ),
				'not_found'                  => __( 'Not Found', 'cool-timeline' ),
				'no_terms'                   => __( 'No items', 'cool-timeline' ),
				'items_list'                 => __( 'Items list', 'cool-timeline' ),
				'items_list_navigation'      => __( 'Items list navigation', 'cool-timeline' ),
			);
			$args   = array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
				'query_var'         => true,
			);
			register_taxonomy( 'ctl-stories', array( 'cool_timeline' ), $args );
		}

		// insert default category
		public function ctl_insert_category() {
			if ( ! term_exists( 'timeline-stories', 'ctl-stories' ) ) {
				$r = wp_insert_term(
					'Timeline Stories', // the term
					'ctl-stories', // the taxonomy
					array(
						'description' => 'All timeline stories.',
						'slug'        => 'timeline-stories',
					)
				);
			}
		}

		function mfields_set_default_object_terms( $post_id, $post ) {
			if ( 'cool_timeline' === $post->post_type ) {
				if ( 'publish' === $post->post_status ) {
					$defaults   = array(
						'ctl-stories' => array( 'timeline-stories' ),
					);
					$taxonomies = get_object_taxonomies( $post->post_type );
					foreach ( (array) $taxonomies as $taxonomy ) {
						$terms = wp_get_post_terms( $post_id, $taxonomy );
						if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
							wp_set_object_terms( $post_id, $defaults[ $taxonomy ], $taxonomy );
						}
					}
				}
			}
		}

		// register custom column for timeline stories
		function add_new_cool_timeline_columns( $gallery_columns ) {
			$new_columns['cb'] = '<input type="checkbox" />';

			$new_columns['title']      = _x( 'Story Title', 'column name', 'cool-timeline' );
			$new_columns['category']   = _x( 'Story Category', 'column name', 'cool-timeline' );
			$new_columns['story_date'] = __( 'Story Date', 'cool-timeline' );

			$new_columns['label'] = __( 'Story Custom Label', 'cool-timeline' );
			$new_columns['order'] = __( 'Story Custom Order', 'cool-timeline' );
			return $new_columns;
		}

		// columns handler function
		function custom_columns( $column, $post_id ) {
			
			global   $post;
			
			$ctl_story_type = get_post_meta( $post_id, 'story_type', true );
			$story_based_on = isset( $ctl_story_type['story_based_on'] ) ? $ctl_story_type['story_based_on'] : 'default';

			switch ( $column ) {

				case 'story_date':
					if ( $story_based_on == 'default' ) {
						$ctl_story_date = isset( $ctl_story_type['ctl_story_date'] ) ? $ctl_story_type['ctl_story_date'] : '';
						echo '<p><strong>' . esc_html( $ctl_story_date ) . '</strong></p>';
					}
					break;
				case 'label':
					if ( $story_based_on == 'custom' ) {
						$ctl_story_lbl = isset( $ctl_story_type['ctl_story_lbl'] ) ? $ctl_story_type['ctl_story_lbl'] : '';
						echo '<p><strong>' . wp_kses_post( $ctl_story_lbl ) . '</strong></p>';
						
						

					}
					break;
				case 'order':
					if ( $story_based_on == 'custom' ) {
						$ctl_story_order = isset( $ctl_story_type['ctl_story_order'] ) ? $ctl_story_type['ctl_story_order'] : '';
						echo '<div class="quick-order-update"><input size="5" value="' . esc_attr( $ctl_story_order ) . '" type="text" data-id="#pld-' . esc_attr( $post_id ) . '" name="clt_story_order" class="custom_story_order" data-post-id="' . esc_attr( $post_id ) . '"><img style="width:16px;display:none;margin-left:5px;" class="od_preloader" id="pld-' . esc_attr( $post_id ) . '" src="' . esc_url( CTP_PLUGIN_URL . 'assets/images/order-preloader.gif' ) . '"></div>';
					}
					break;
				case 'category':
					$terms = get_the_terms( $post_id, 'ctl-stories' );

					if ( ! empty( $terms ) ) {
						$out = array();
						foreach ( $terms as $term ) {
							$out[] = sprintf(
								'<a href="%s">%s</a>',
								esc_url(
									add_query_arg(
										array(
											'post_type'   => $post->post_type,
											'ctl-stories' => $term->slug,
										),
										'edit.php'
									)
								),
								esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'ctl-stories', 'display' ) )
							);
						}
						echo join( ', ', $out );
					} else {
						_e( '', 'cool-timeline' );
					}
					break;
				default:
					echo '<p>' . esc_html__( '', 'cool-timeline' ) . '</p>';
			}
		}

			function ctl_change_story_order() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ctl_change_story_order_nonce' ) ) {
			wp_send_json_error( array( 'error' => 'Invalid nonce' ) );
			wp_die();
		}
		
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'error' => 'Insufficient permissions' ) );
			wp_die();
		}
		
		$new_order = intval( $_POST['order'] );
		$p_id      = intval( $_POST['post_id'] );

			$rs                                = update_post_meta( $p_id, 'ctl_story_order', $new_order );
			$ctl_story_type                    = get_post_meta( $p_id, 'story_type', true );
			$ctl_story_type['ctl_story_order'] = $new_order;
			$rs                                = update_post_meta( $p_id, 'story_type', $ctl_story_type );

		if ( $rs ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		wp_die(); // this is required to terminate immediately and return a proper response
	}

		function add_custom_rewrite_rule() {
			if ( ( $current_rules = get_option( 'rewrite_rules' ) ) ) {
				$ctl_options_arr = get_option( 'cool_timeline_settings' );
				if ( isset( $ctl_options_arr['post_type_slug'] ) && ! empty( $ctl_options_arr['post_type_slug'] ) ) {
					$c_slug = sanitize_title( $ctl_options_arr['post_type_slug'] );
				} else {
					$c_slug = 'timeline';
				}

				foreach ( $current_rules as $key => $val ) {
					if ( strpos( $key, 'cool_timeline' ) !== false ) {
						add_rewrite_rule( str_ireplace( 'cool_timeline', $c_slug, $key ), $val, 'top' );
					}
				}
			}
		}

		function ctl_template_redirect() {
			if ( is_post_type_archive( 'cool_timeline' ) ) {
				wp_redirect( home_url( '/' ) );
				exit();
			}
		}

		/**
		 * Display a custom taxonomy dropdown in admin
		 *
		 * @author coolhappy
		 */
		function ctl_filter_by_taxonomy() {
			global $typenow;
			$post_type = 'cool_timeline'; // change to your post type
			$taxonomy  = 'ctl-stories'; // change to your taxonomy
			if ( $typenow == $post_type ) {
				$selected      = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) ) : '';
				$info_taxonomy = get_taxonomy( $taxonomy );
				wp_dropdown_categories(
					array(
						'show_option_all' => __( "Show All {$info_taxonomy->label}" ),
						'taxonomy'        => $taxonomy,
						'name'            => $taxonomy,
						'orderby'         => 'name',
						'selected'        => $selected,
						'show_count'      => true,
						'hide_empty'      => true,
					)
				);
			}
		}

		/**
		 * Filter posts by taxonomy in admin
		 *
		 * @author  coolhappy
		 */
		function ctl_convert_term_id_to_slug( $query ) {
			global $pagenow;
			$post_type = 'cool_timeline'; // change to your post type
			$taxonomy  = 'ctl-stories'; // change to your taxonomy
			$q_vars    = &$query->query_vars;
			if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
				$term                = get_term_by( 'id', $q_vars[ $taxonomy ], $taxonomy );
				$q_vars[ $taxonomy ] = $term->slug;
			}
		}

	} //class end

} // main
