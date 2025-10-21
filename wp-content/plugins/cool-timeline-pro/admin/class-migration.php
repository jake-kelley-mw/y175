<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CTL_migrations {

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Migration from old version since version 3.0.
		add_action( 'init', array( $this, 'ctl_migrate_old_content' ) );

		// migrate old version stories
		add_action( 'admin_init', array( $this, 'ctl_postmeta_migration' ) ); // Post meta migration > 3.5.3
		add_action( 'admin_init', array( $this, 'ctl_settings_migration' ) );
		add_action( 'wp_ajax_ctl_migrate_stories', array( $this, 'ctl_migrate_stories' ) );
		

		
	}

	/**
	 * Run migration from old version since version 3.0
	 */
	public function ctl_migrate_old_content() {

		if ( get_option( 'ctl-migrated-content' ) !== false ) {
			return;
		}
		$ctl_version = get_option( 'cool-timelne-v' );
		$ctl_type    = get_option( 'cool-timelne-type' );

		if ( ! empty( $ctl_type ) && $ctl_type == 'FREE' ) {
			if ( version_compare( $ctl_version, '1.7', '<' ) ) {
				CTL_Helpers::ctl_migrate_from_free( 'old' );
			} else {
				CTL_Helpers::ctl_migrate_from_free( 'latest' );
			}

			update_option( 'ctl-can-migrate', 'no' );
		} elseif ( ! empty( $ctl_type ) && $ctl_type == 'PRO' && version_compare( $ctl_version, '3.0', '<' ) ) {
			CTL_Helpers::ctl_migrate_pro_old_stories();
		} else {
			update_option( 'ctl-can-migrate', 'no' );
		}
		update_option( 'ctl-migrated-content', 'yes' );
	}

	// migrate stories from cool timeline free version
	function ctl_postmeta_migration() {

		// return if pro migration done
		if ( get_option( 'ctl-pro-postmeta-migration' ) || get_option( 'ctl-postmeta-migration' ) ) {
			return;
		}

		// old free to new pro
		$free_version = get_option( 'cool-free-timeline-v', 2.1 );
		if ( version_compare( $free_version, '2.1', '>' ) && ! get_option( 'ctl-postmeta-migration' ) ) {
			return;
		}

		// direct new pro version
		$pro_version = get_option( 'cool-timelne-pro-v' );
		if ( version_compare( $pro_version, '4.0', '>' ) && ! get_option( 'ctl-postmeta-migration' ) ) {
			return;
		}

		$args = array(
			'post_type'   => 'cool_timeline',
			'post_status' => array( 'publish', 'future' ),
			'numberposts' => -1,
		);
		$posts = get_posts( $args );

		// Story Type
		$story_type_key = array(
			'story_based_on',
			'ctl_story_date',
			'ctl_story_timestamp',
			'ctl_story_lbl',
			'ctl_story_lbl_2',
			'ctl_story_order',
		);

		// Story Media
		$story_media_key = array(
			'story_format',
			'ctl_video',
			'img_cont_size',
			're_',
		);

		$story_icon_key = array(
			'fa_field_icon',
			'use_img_icon',
			'story_img_icon',
		);

		$story_extra_key = array(
			'story_custom_link',
			'ctl_story_color',
		);

		if ( isset( $posts ) && is_array( $posts ) && ! empty( $posts ) ) {
			foreach ( $posts as $post ) {

				$img_as_icon    = get_post_meta( $post->ID, 'use_img_icon', true );
				$story_img_icon = get_post_meta( $post->ID, 'story_img_icon', true );

				foreach ( $story_icon_key as $item ) {
					$item_value = get_post_meta( $post->ID, $item, true );
					if ( $img_as_icon == 'on' && ! empty( $story_img_icon ) ) {
						$array_icon_type['story_icon_type'] = 'custom_image';
						if ( ! empty( $story_img_icon ) ) {
							$thumbnail_img = wp_get_attachment_image_src( $story_img_icon['id'], 'thumbnail' );
							$array_icon_type['story_img_icon'] = $story_img_icon;
							if ( isset( $thumbnail_img[0] ) ) {
								$array_icon_type['story_img_icon'] += array(
									'thumbnail' => esc_url( $thumbnail_img[0] ),
									'width'     => '843',
									'height'    => '450',
								);
							}
						}
					} else {
						$array_icon_type['story_icon_type'] = 'fontawesome';
						$array_icon_type[ $item ]           = $item_value;
					}
				}

				foreach ( $story_type_key as $item ) {
					$item_value                = get_post_meta( $post->ID, $item, true );
					$array_story_type[ $item ] = $item_value;
				}

				$slideshow_ids = array();
				foreach ( $story_media_key as $item ) {
					$item_value = get_post_meta( $post->ID, $item, true );
					if ( $item == 're_' ) {
						if ( is_array( $item_value ) ) {
							$total_slides = count( $item_value );
							for ( $i = 0; $i < $total_slides; $i++ ) {
								array_push( $slideshow_ids, intval( $item_value[ $i ]['ctl_slide']['id'] ) );
							}
							if ( ! empty( $slideshow_ids ) ) {
								$array_story_media['ctl_slide'] = implode( ',', $slideshow_ids );
							}
						} else {
							$array_story_media['ctl_slide'] = '';
						}
					} else {
						$array_story_media[ $item ] =  $item_value;
					}
				}

				foreach ( $story_extra_key as $item ) {
					$item_value = get_post_meta( $post->ID, $item, true );
					if ( $item == 'story_custom_link' ) {
						$array_extra_key[ $item ]['url'] = esc_url( $item_value );
					} else {
						$array_extra_key[ $item ] = $item_value;
					}
				}

				update_post_meta( $post->ID, 'story_type', $array_story_type );
				update_post_meta( $post->ID, 'story_media', $array_story_media );
				update_post_meta( $post->ID, 'story_icon', $array_icon_type );
				update_post_meta( $post->ID, 'extra_settings', $array_extra_key );
				update_option( 'ctl-pro-postmeta-migration', 'done' );
				update_option( 'ctl-postmeta-migration', 'done' );
			}
		}
	}

	function ctl_settings_migration() {
		if ( ! get_option( 'cool_timeline_options' ) ) {
			return;
		}

		$old_settings = get_option( 'cool_timeline_options' );
		$new_settings = $this->ctl_recursive_change_key(
			$old_settings,
			array(
				'face'   => 'font-family',
				'size'   => 'font-size',
				'weight' => 'font-weight',
				'src'    => 'url',
			)
		);

		update_option( 'cool_timeline_settings', $new_settings );
		update_option( 'ctl_settings_migration_status', 'done' );
		delete_option( 'cool_timeline_options' );
	}

	function recursive_change_key( $arr, $set ) {
		if ( is_array( $arr ) && is_array( $set ) ) {
			$newArr = array();
			foreach ( $arr as $k => $v ) {
				$key            = array_key_exists( $k, $set ) ? $set[ $k ] : $k;
				$newArr[ $key ] = is_array( $v ) ? $this->recursive_change_key( $v, $set ) : $v;
				if ( $key == 'font-size' ) {
					$newArr[ $key ] = str_replace( 'px', '', $v );
				}
			}
			return $newArr;
		}
		return $arr;
	}

	function ctl_recursive_change_key( $arr, $set ) {
		if ( is_array( $arr ) && is_array( $set ) ) {
			$newArr                 = array();
			$timeline_header        = array();
			$navigation_settings    = array();
			$pagination_settings    = array();
			$blog_post_settings     = array();
			$story_date_settings    = array();
			$story_content_settings = array();
			$story_media_settings   = array();

			$timeline_header_key        = array( 'display_title', 'title_text', 'user_avatar' );
			$navigation_settings_key    = array( 'enable_navigation', 'navigation_position' );
			$pagination_settings_key    = array( 'enable_pagination' );
			$blog_post_settings_key     = array( 'post_meta' );
			$story_date_settings_key    = array( 'year_label_visibility', 'disable_months', 'ctl_date_formats', 'custom_date_style', 'custom_date_formats' );
			$story_content_settings_key = array( 'content_length', 'display_readmore', 'read_more_lbl', 'story_link_target', 'default_icon' );
			$story_media_settings_key   = array( 'stories_images', 'ctl_slideshow', 'animation_speed' );

			$arr = $this->recursive_change_key( $arr, $set );
			foreach ( $arr as $key => $value ) {
				if ( in_array( $key, $timeline_header_key ) ) {
					if ( $key == 'user_avatar' ) {
						if ( ! empty( $value ) ) {
							$value            = $this->recursive_change_key( $value, array( 'src' => 'url' ) );
							$thumbnail_img    = wp_get_attachment_image_src( $value['id'], 'thumbnail' );
							$value           += array(
								'thumbnail' => null != $thumbnail_img[0] && '' != $thumbnail_img[0] ? esc_url( $thumbnail_img[0] ) : '',
								'width'     => '843',
								'height'    => '450',
							);
							$timeline_header += array( $key => $value );
						}
					} else {
						$timeline_header += array( $key => $value );
					}
				} elseif ( in_array( $key, $navigation_settings_key ) ) {
					$navigation_settings += array( $key => $value );
				} elseif ( in_array( $key, $pagination_settings_key ) ) {
					$pagination_settings += array( $key => $value );
				} elseif ( in_array( $key, $blog_post_settings_key ) ) {
					$blog_post_settings += array( $key => $value );
				} elseif ( in_array( $key, $story_date_settings_key ) ) {
					$story_date_settings += array( $key => $value );
				} elseif ( in_array( $key, $story_content_settings_key ) ) {
					$story_content_settings += array( $key => $value );
				} elseif ( in_array( $key, $story_media_settings_key ) ) {
					$story_media_settings += array( $key => $value );
				} elseif ( $key == 'main_title_typo' ) {
					$title_alignment           = isset( $arr['title_alignment'] ) ? $arr['title_alignment'] : 'center';
					$value                    += array(
						'text-align' => $title_alignment,
						'type'       => 'google',
					);
					$newArr['main_title_typo'] = $value;
				} elseif ( $key == 'post_title_text_style' ) {
					$newArr['post_title_typo']['text-transform'] = sanitize_text_field( $value );
				} elseif ( $key == 'background' ) {
					if ( isset( $value['enabled'] ) ) {
						$newArr['timeline_background'] = '1';
						$newArr['timeline_bg_color']   = sanitize_hex_color( $value['bg_color'] );
					} else {
						$newArr['timeline_background'] = '0';
					}
				} elseif ( $key == 'post_title_typo' ) {
					$value                    += array( 'type' => 'google' );
					$newArr['post_title_typo'] = $value;
				} elseif ( $key == 'ctl_date_typo' ) {
					$value                  += array( 'type' => 'google' );
					$newArr['ctl_date_typo'] = $value;
				} elseif ( $key == 'post_content_typo' ) {
					$value                      += array( 'type' => 'google' );
					$newArr['post_content_typo'] = $value;
				} else {
					$newArr[ $key ] = $value;
				}
			}

			$newArr['timeline_header']        = $timeline_header;
			$newArr['navigation_settings']    = $navigation_settings;
			$newArr['pagination_settings']    = $pagination_settings;
			$newArr['blog_post_settings']     = $blog_post_settings;
			$newArr['story_date_settings']    = $story_date_settings;
			$newArr['story_content_settings'] = $story_content_settings;
			$newArr['story_media_settings']   = $story_media_settings;
			return $newArr;
		}
		return $arr;
	}

	/**
	 * Migrate data from Timeline Express to Cool Timeline
	 */
	public function migrate_timeline_express_to_cool_timeline() {

		if ( get_option( 'timeline_express_migrated' ) ) {
			return;
		}
	
		$args = array(
			'post_type'      => 'te_announcements',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		
		$timeline_express_posts = get_posts( $args );
		if ( empty( $timeline_express_posts ) ) {
			return;
		}

		$migrate_stories = 0;

		$timeline_settings     = get_option('timeline_express_storage');
		$cooltimeline_settings = get_option('cool_timeline_settings', []);

		if (!is_array($timeline_settings)) {
			$timeline_settings = array();
		}
		
		$cooltimeline_settings = (array) get_option('cool_timeline_settings', []);
		
		// Initialize all required array keys
		if (!isset($cooltimeline_settings['story_content_settings']) || !is_array($cooltimeline_settings['story_content_settings'])) {
			$cooltimeline_settings['story_content_settings'] = array();
		}
		
		// Ensure all settings are properly initialized
		$cooltimeline_settings = array_merge(array(
			'story_content_settings' => array(),
			'first_post' => '',
			'content_bg_color' => '',
			'line_color' => ''
		), $cooltimeline_settings);
		
		if ( ! empty( $timeline_settings['excerpt-trim-length'] ) ) {
			$cooltimeline_settings['story_content_settings']['content_length'] = (string) (int) $timeline_settings['excerpt-trim-length'];
		}
		
		if (isset($timeline_settings['read-more-visibility'])) {
			$cooltimeline_settings['story_content_settings']['display_readmore'] = $timeline_settings['read-more-visibility'] === '1' ? 'yes' : 'no';
		}

		if (isset($timeline_settings['default-announcement-color'])) {
			$cooltimeline_settings['first_post'] = $timeline_settings['default-announcement-color'];
		}

		if (isset($timeline_settings['announcement-bg-color'])) {
			$cooltimeline_settings['content_bg_color'] = $timeline_settings['announcement-bg-color'];
		}
		
		if (isset($timeline_settings['announcement-background-line-color'])) {
			$cooltimeline_settings['line_color'] = $timeline_settings['announcement-background-line-color'];
		}
       	
		foreach ( $timeline_express_posts as $old_post ) {
			if ( empty( $old_post->ID ) ) {
				continue;
			}

			$migrate_stories++;
			$event_timestamp = intval( get_post_meta( $old_post->ID, 'announcement_date', true ) );
			$icon_raw        = get_post_meta( $old_post->ID, 'announcement_icon', true );
			$color_raw       = get_post_meta( $old_post->ID, 'announcement_color', true );

			$attachment_id   = intval( get_post_meta( $old_post->ID, 'announcement_image_id', true ) );
			$excerpt         = wp_kses_post(get_post_meta($old_post->ID,'announcement_custom_excerpt',true));
			
			$formatted_for_meta = $event_timestamp ? date( 'm/d/Y h:i A', $event_timestamp ) : '';
			$color = sanitize_text_field( $color_raw );

			if (strpos($icon_raw, 'fa-') === false) {
				$icon_class = 'fa fa-' . sanitize_html_class($icon_raw);
			} else {
				$icon_class = 'fa ' . sanitize_html_class($icon_raw);
			}
			
			$new_post = array(
				'post_title'   => sanitize_text_field( $old_post->post_title ),
				'post_content' => wp_kses_post( $old_post->post_content ),
				'post_excerpt'=>$excerpt,
				'post_type'    => 'cool_timeline',
				'post_status'  => 'publish',
				'post_date'    => $old_post->post_date,
				'post_name'    => sanitize_title( $old_post->post_title ),
			);

			$new_post_id = wp_insert_post( $new_post );
			
			if ( ! is_wp_error( $new_post_id ) ) {
				clean_post_cache( $new_post_id );
				
				if ( $attachment_id && get_post_type( $attachment_id ) === 'attachment' ) {
					set_post_thumbnail( $new_post_id, $attachment_id );
				}

				// Set story to be visible on frontend
				update_post_meta( $new_post_id, '_ctl_visible', 'yes' );
				update_post_meta( $new_post_id, 'story_based_on', 'default'  );
				update_post_meta( $new_post_id, 'story_format', 'image' );
				
				if ( ! empty( $formatted_for_meta ) ) {

					$story_type_serialized = [
						'ctl_story_date' => $formatted_for_meta,
					];

					update_post_meta( $new_post_id, 'ctl_story_timestamp', $event_timestamp );
					update_post_meta( $new_post_id, 'story_date', $formatted_for_meta );
					update_post_meta( $new_post_id, 'story_type', $story_type_serialized );
				}

				if ( ! empty( $color ) ) {
					$extra_settings = array(
						'ctl_story_color' => $color
					);
					update_post_meta( $new_post_id, 'extra_settings', $extra_settings );
				}

				if ( ! empty( $icon_class ) ) {

					$story_icon_serialized = [
						'fa_field_icon' => $icon_class,
					];
					update_post_meta( $new_post_id, 'story_icon', $story_icon_serialized );
				}
			}
		}
		
		update_option( 'timeline_express_migrated', 1 );
		update_option('cool_timeline_settings', $cooltimeline_settings);
		return $migrate_stories;
	}
	
	public function ctl_migrate_stories() {

		check_ajax_referer( 'ctl_migrate_nonce', 'nonce' );
	
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'cool-timeline' ) ] );
			wp_die();
		}
	
		$total_stories = $this->migrate_timeline_express_to_cool_timeline();
	
		if ( empty( $total_stories ) || $total_stories === 0 ) {
		
			wp_send_json_error( [ 'message' => __( 'No Attachemnt Found To Migrate.', 'cool-timeline' ) ] );
			wp_die();
		}
	
		wp_send_json_success([
			'message' => __( 'Migration Completed', 'cool-timeline' ),
			'total_stories' => $total_stories
		]);
	}
}
new CTL_migrations();
