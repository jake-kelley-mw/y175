<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * This file is responsible for creating all admin settings in Timeline Builder (post)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Can not load script outside of WordPress Enviornment!' );
}

if ( ! class_exists( 'CTL_shortcode_generator' ) ) {
	class CTL_shortcode_generator {


		/**
		 * The unique instance of the plugin.
		 */
		private static $instance;

		/**
		 * Gets an instance of our plugin.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * The Constructor
		 */
		public function __construct() {
			 // register actions
			$this->CTL_shortcode_generator();
			add_action( 'admin_print_styles', array( $this, 'ctl_custom_shortcode_style' ) );
			add_action( 'admin_print_scripts', array( $this, 'ctl_custom_shortcode_script' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'ctl_preview_script' ) );
			add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'ctl_preview_script' ) );
		}

		public function ctl_custom_shortcode_style() {
		$timeline_icon1 = esc_url( CTP_PLUGIN_URL . 'assets/images/timeline-icon2-32x32.png' );
		$timeline_icon2 = esc_url( CTP_PLUGIN_URL . 'assets/images/timeline-icon-222.png' );
		echo '<style>
        span.dashicon.dashicons.dashicons-ctl-custom-icon:before {
        content:"";
        background: url(' . $timeline_icon1 . ');
        background-size: contain;
        background-repeat: no-repeat;
        height: 20px;
        display: block;
        }
        #wp-content-wrap a[data-modal-id="ctl_timeline_shortcode"]:before {
        content: "";
        background: url(' . $timeline_icon2 . ');
        background-size: contain;
        background-repeat: no-repeat;
        height: 17px;
        display: inline-block;
        margin: 0px 1px -3px 0;
        width: 20px;
        }    
        #ctl-modal-ctl_timeline_shortcode .ctl-modal-inner {
            height: 500px !important;       
        }
        #ctl-modal-ctl_timeline_shortcode .ctl-modal-content {
            height:400px !important;
        }
        </style>';
		}

		public function ctl_custom_shortcode_script() {
			echo '<script>
			window.addEventListener("load", ()=>{
					const inputField=()=>{
						const hiddenInputFields=jQuery("#ctl-modal-ctl_timeline_shortcode select").closest(".ctl-field.ctl-depend-hidden.ctl-depend-on");
						if(hiddenInputFields.length > 0){
							hiddenInputFields.each((index,ele)=>{
								const selectName=jQuery(ele).find("select").attr("name");
								const duplicateField=jQuery(`#ctl-modal-ctl_timeline_shortcode select[name="${selectName}"],#ctl-modal-ctl_timeline_shortcode select[name="${selectName}_ctl_hidden"]`);
								if(duplicateField.length > 1){
									duplicateField.attr("name",selectName);
									const hiddenField=jQuery(`#ctl-modal-ctl_timeline_shortcode select[name="${selectName}"]`).closest(".ctl-field.ctl-depend-hidden.ctl-depend-on");
									hiddenField.find("select").attr("name",`${selectName}_ctl_hidden`);
								}
							});
						}
					}
					jQuery(document).on("change","#ctl-modal-ctl_timeline_shortcode select", (e)=>{
						inputField();
					});
					inputField();
				})
			</script>';
		}

		// Preview Scripts Loaded
		public function ctl_preview_script() {
			wp_enqueue_script( 'ctl_preview_scripts', esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/assets/js/ctl-preview.min.js' ), array( 'jquery' ), CTLPV, false );
			wp_localize_script(
				'ctl_preview_scripts',
				'ctlPreviewData',
				array(
					'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonce'   => wp_create_nonce( 'ctl_preview' ),
				)
			);
		}

		public function CTL_shortcode_generator() {
			$id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : '';
			$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : get_post_type( $id );
			
			if ( $post_type !== 'page' && $post_type !== 'post' && $post_type != '' ) {
				return;
			}
			if ( class_exists( 'CTL' ) ) {
				// Set a unique slug-like ID
				$prefix = 'ctl_timeline_shortcode';
				// Get All Post Types as List
				// Create a shortcoder
				CTL::createShortcoder(
					$prefix,
					array(
						'button_title' => 'Add Timeline',
						'insert_title' => 'Insert shortcode',
						'gutenberg'    => array(
							'title'        => 'Cool Timeline Shortcode Generator',
							'icon'         => 'ctl-custom-icon',
							'description'  => 'Create/Configure Story Timeline Shortcode.',
							'category'     => 'widgets',
							'keywords'     => array( 'shortcode', 'ctl', 'timeline', 'cooltimeline' ),
							'previewImage' => CTP_PLUGIN_URL . 'includes/cool-timeline-block/images/timeline.27d3f3c7.png',
						),
					)
				);

				//
				// A basic shortcode

				CTL::createSection(
					$prefix,
					array(
						'title'     => 'Story Timeline',
						'view'      => 'normal', // View model of the shortcode. `normal` `contents` `group` `repeater`
						'shortcode' => 'cool-timeline', // Set a unique slug-like name of shortcode.
						'fields'    => array(
							array(
								'id'   => 'ctl-story-tab',
								'type' => 'tabbed',
								'tabs' => array(
									// Timeline General Tabs Start
									array(
										'title'  => 'General',
										// 'icon'      => 'fa fa-heart',
										'fields' => array(
											// Timeline General settings field start
											array(
												'id'      => 'layout',
												'type'    => 'select',
												'title'   => 'Select Layout',
												'placeholder' => 'Select Layout',
												'default' => 'default',
												'desc'    => 'Select your timeline Layout',
												'options' => array(
													'default'  => 'Vertical',
													'horizontal' => 'Horizontal',
													'one-side' => 'One Side Layout',
													'compact'      => 'Compact Layout',
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'designs',
												'type'    => 'select',
												'title'   => 'Select Design',
												'placeholder' => 'Select Design',
												'default' => 'default',
												'desc'    => 'Select your timeline Design',
												'options' => array(
													'design-1' => 'Default',
													'design-2' => 'Flat Design',
													'design-3' => 'Classic Design',
													'design-4' => 'Elegant Design',
													'design-5' => 'Clean Design',
													'design-6' => 'Modern Design',
													'design-7' => 'Minimal Design',
													'design-8' => 'Simple Design',
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array( 'layout', '==', 'horizontal' ),
											),

											array(
												'id'      => 'designs',
												'type'    => 'select',
												'title'   => 'Select Design',
												'placeholder' => 'Select Design',
												'default' => 'default',
												'desc'    => 'Select your timeline Design',
												'options' => array(
													'design-1' => 'Default',
													'design-2' => 'Flat Design',
													'design-3' => 'Classic Design',
													'design-4' => 'Elegant Design',
													'design-5' => 'Clean Design',
													'design-6' => 'Modern Design',
													'design-7' => 'Minimal Design',
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array( 'layout', '!=', 'horizontal' ),
											),

											array(
												'id'      => 'skin',
												'type'    => 'select',
												'title'   => 'Select Skin',
												'default' => 'default',
												// 'dependency' => array( 'layout', '!=', 'horizontal' ),
												'options' => array(
													'default' => 'Default',
													'dark' => 'Dark',
													'light' => 'Light',
												),
												'desc'    => 'Create Light, Dark or Colorful Timeline',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'       => 'category',
												'type'     => 'select',
												'title'    => 'Category',
												'placeholder' => 'Select a Category',
												'settings' => array(
													'width'  => '50%',
												),
												'chosen'   => true,
												'multiple' => true,
												'options'  => 'ctl_select_category',
												'attributes' => array(
													'style' => 'width: 50%;',
												),

											),

											array(
												'id'      => 'based',
												'type'    => 'select',
												'title'   => 'Timeline Based On',
												'default' => 'default',
												'options' => array(
													'default' => 'Default(Date Based)',
													'custom'  => 'Custom Order',
												),
												'desc'    => 'Show either date or custom label/text along with timeline stories.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'story-content',
												'type'    => 'select',
												'title'   => 'Stories Description?',
												'default' => 'short',
												'options' => array(
													'short' => 'Summary',
													'full' => 'Full Text',
												),
												'desc'    => '<span>Summary:- Short description<br>Full:- All content with formated text.</span>',
												'attributes' => array(
													'style' => 'width: 50%;',
												),

											),

											array(
												'id'      => 'show-posts',
												'type'    => 'text',
												'title'   => 'Display Per Page?',
												'default' => '10',
												'desc'    => 'You Can Show Pagination After These Posts In Vertical Timeline.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'items',
												'type'    => 'select',
												'title'   => 'Slide To Show?',
												'default' => '',
												'placeholder' => 'Select number of items',
												'dependency' => array( 'layout', '==', 'horizontal' ),
												'options' => array(
													'1' => '1',
													'2' => '2',
													'3' => '3',
													'4' => '4',

												),
												'desc'    => 'Number of slide to show',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'year-label',
												'type'    => 'button_set',
												'title'   => 'Year Label Setting',
												'default' => 'show',
												'options' => array(
													'hide' => 'Hide',
													'show' => 'Show',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array(
													array( 'layout', '!=', 'compact' ),
												),
											),

											array(
												'id'      => 'year-navigation',
												'type'    => 'button_set',
												'title'   => 'Year Navigation Setting',
												'default' => 'hide',
												'options' => array(
													'hide' => 'Hide',
													'show' => 'Show',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array(
													array( 'layout', '==', 'compact' ),
												),
											),

											array(
												'id'      => 'year-navigation',
												'type'    => 'button_set',
												'title'   => 'Year Navigation Setting',
												'default' => 'hide',
												'options' => array(
													'hide' => 'Hide',
													'show' => 'Show',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array(
													array( 'year-label', '==', 'show' ),
													array( 'layout', '!=', 'compact' ),
												),
											),

											array(
												'id'      => 'navigation-position',
												'type'    => 'button_set',
												'title'   => 'Year Navigation Position',
												'default' => 'center',
												'options' => array(
													'left' => 'Left',
													'center' => 'Center',
													'right' => 'Right',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array(
													array( 'year-label', '==', 'show' ),
													array( 'year-navigation', '==', 'show' ),
													array( 'layout', '==', 'horizontal' ),
												),
											),
											array(
												'id'      => 'navigation-position',
												'type'    => 'button_set',
												'title'   => 'Year Navigation Position',
												'default' => 'right',
												'options' => array(
													'left' => 'Left',
													'right' => 'Right',
													'bottom' => 'Bottom',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array(
													array( 'year-navigation', '==', 'show' ),
													array( 'layout', '!=', 'horizontal' ),
												),
											),
											array(
												'id'      => 'navigation-style',
												'type'    => 'select',
												'title'   => 'Year Navigation Style',
												'default' => 'right',
												'options' => array(
													'style-1' => 'Style 1',
													'style-2' => 'Style 2',
													'style-3' => 'Style 3',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array(
													array( 'year-navigation', '==', 'show' ),
													array( 'layout', '!=', 'horizontal' ),
													array( 'navigation-position', '!=', 'bottom' ),
												),
											),

											array(
												'id'      => 'autoplay',
												'type'    => 'select',
												'title'   => 'Autoplay Settings?',
												'default' => 'false',
												'dependency' => array( 'layout', '==', 'horizontal' ),
												'options' => array(
													'true' => 'True',
													'false' => 'False',
												),
												'desc'    => 'Number of slide to show',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'start-on',
												'type'    => 'text',
												'title'   => 'Timeline Starting from Story e.g(2)',
												'dependency' => array( 'layout', '==', 'horizontal' ),
												'default' => '0',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'autoplay-speed',
												'type'    => 'text',
												'title'   => 'Slideshow Speed ?',
												'dependency' => array( 'layout', '==', 'horizontal' ),
												'default' => '3000',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'compact-ele-pos',
												'type'    => 'select',
												'title'   => 'Compact Layout Date&Title positon',
												'default' => 'main-date',
												'dependency' => array( 'layout', '==', 'compact' ),
												'options' => array(
													'main-date' => 'On top date/label below title',
													'main-title' => 'On top title below date/label',

												),
											),

											array(
												'id'      => 'animations',
												'type'    => 'select',
												'title'   => 'Animation',
												'default' => 'none',
												'options' => array(
													'none' => 'none',
													'fade' => 'fade',
													'zoom-in' => 'zoom-in',
													'flip-right' => 'flip-right',
													'zoom-out' => 'zoom-out',
													'fade-up' => 'fade-up',
													'fade-down' => 'fade-down',
													'fade-left' => 'fade-left',
													'fade-right' => 'fade-right',
													'fade-up-right' => 'fade-up-right',
													'fade-up-left' => 'fade-up-left',
													'fade-down-right' => 'fade-down-right',
													'fade-down-left' => 'fade-down-left',
													'flip-up' => 'flip-up',
													'flip-down' => 'flip-down',
													'flip-left' => 'flip-left',
													'slide-up' => 'slide-up',
													'slide-left' => 'slide-left',
													'slide-right' => 'slide-right',
													'zoom-in-up' => 'zoom-in-up',
													'zoom-in-down' => 'zoom-in-down',
													'slide-down' => 'slide-down',
													'zoom-in-left' => 'zoom-in-left',
													'zoom-in-right' => 'zoom-in-right',
													'zoom-out-up' => 'zoom-out-up',
													'zoom-out-down' => 'zoom-out-down',
													'zoom-out-left' => 'zoom-out-left',
													'zoom-out-right' => 'zoom-out-right',

												),
												'dependency' => array( 'layout', '!=', 'horizontal' ),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'timeline-title',
												'type'    => 'text',
												'title'   => 'Timeline Title',
												'default' => '',
												'dependency' => array( 'layout', '!=', 'horizontal' ),
											),

											// Timeline General settings field end
										),
									),
									// Timeline General Tabs end

									// Timeline Advanced Tabs Start
									array(
										'title'  => 'Advanced',
										// 'icon'      => 'fa fa-heart',
										'fields' => array(
											// Timeline Advanced settings field start
											array(
												'id'      => 'order',
												'type'    => 'select',
												'title'   => 'Stories Order?',
												'default' => 'DESC',
												'options' => array(
													'DESC' => 'DESC',
													'ASC'  => 'ASC',
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'desc'    => '<span>Timeline Stories order like:- DESC(2017-1900) , ASC(1900-2017)</span>',
											),

											array(
												'id'      => 'story-date',
												'type'    => 'button_set',
												'title'   => 'Stories Date?',
												'options' => array(
													'show' => 'Show',
													'hide' => 'Hide',
												),
												'default' => 'show',
											),

											array(
												'id'      => 'date-format',
												'type'    => 'select',
												'title'   => 'Select Date Formats',
												'default' => 'F j',
												'options' => array(
													'F j' => date_i18n( 'F j' ),
													'F j Y' => date_i18n( 'F j Y' ),
													'Y-m-d' => date_i18n( 'Y-m-d' ),
													'm/d/Y' => date_i18n( 'm/d/Y' ),
													'd/m/Y' => date_i18n( 'd/m/Y' ),
													'F j Y g:i A' => date_i18n( 'F j Y g:i A' ),
													'Y'   => date_i18n( 'Y' ),
													'custom' => 'Custom',
												),
												'desc'    => 'Timeline Stories dates formats',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array( 'story-date', '==', 'show' ),
											),

											array(
												'id'      => 'custom-date-format',
												'type'    => 'text',
												'title'   => 'Custom Date Format',
												'default' => '',
												'dependency' => array( 'date-format', '==', 'custom' ),
											),

											array(
												'id'      => 'icons',
												'type'    => 'button_set',
												'title'   => 'Icons',
												'options' => array(
													'icon' => 'ICON',
													'dot'  => 'DOT',
													'none' => 'NONE',
												),
												'default' => 'dot',
												'desc'    => 'Display Icons In Timeline Stories. By default Is Dot',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'pagination',
												'type'    => 'select',
												'title'   => 'Pagination Settings',
												'default' => 'default',
												'options' => array(
													'off' => 'Off',
													'default' => 'Default',
													'ajax_load_more' => 'Ajax Load More',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'filters',
												'type'    => 'button_set',
												'title'   => 'Category Filters Settings',
												'default' => 'no',
												'options' => array(
													'no'  => 'NO',
													'yes' => 'YES',
												),
												'desc'    => '',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'       => 'filter-categories',
												'type'     => 'select',
												'title'    => 'Add categories slug for filters eg(stories,our-history)',
												'chosen'   => true,
												'multiple' => true,
												'placeholder' => 'Select an option',
												'options'  => 'ctl_select_category',
												'dependency' => array( 'filters', '==', 'yes' ),
											),

											array(
												'id'      => 'line-filling',
												'type'    => 'button_set',
												'title'   => 'Line Filling',
												'default' => 'false',
												'options' => array(
													'true' => 'True',
													'false' => 'False',
												),
												'desc'    => 'Center Line Filling In Timeline Stories. By default Is False',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),

											array(
												'id'      => 'read-more',
												'type'    => 'button_set',
												'title'   => 'Display read more?',
												'options' => array(
													'show' => 'Show',
													'hide' => 'Hide',
												),
												'default' => 'show',
											),

											array(
												'id'      => 'content-length',
												'type'    => 'text',
												'title'   => 'Content Length',
												'default' => '50',
												'dependency' => array( 'read-more', '==', 'show' ),
											),

											// Timeline Advanced settings field end
										),
									),
									// Timeline Advanced Tabs end

									// Preview Tab Start
									array(
										'title'  => 'Preview',
										'fields' => array(
											array(
												'id'      => 'preview',
												'type'    => 'content',
												'content' => '<iframe id="ctl_preview" name="my_iframe" src="' . esc_url(CTP_PLUGIN_URL . 'includes/shortcodes/class-ctl-shortcode-preview.php') . '" title="preview iframe" scrolling="auto" frameborder="0" data-preloader="' . esc_url(CTP_PLUGIN_URL . 'assets/images/order-preloader.gif') . '"></iframe>',
											),
										),
									),
									// Preview Tab End
								),
							),

						),
					)
				);

					// content timeline shortcode

					CTL::createSection(
						$prefix,
						array(
							'title'     => 'Post Timeline',
							'view'      => 'normal', // View model of the shortcode. `normal` `contents` `group` `repeater`
							'shortcode' => 'cool-content-timeline', // Set a unique slug-like name of shortcode.
							'fields'    => array(
								array(
									'id'   => 'ctl-post-tab',
									'type' => 'tabbed',
									'tabs' => array(
										// General tab start
												 array(
													 'title'  => 'General',
													 // 'icon'      => 'fa fa-heart',
													 'fields' => array(

														 array(
															 'id' => 'layout',
															 'type' => 'select',
															 'title' => 'Select Layout',
															 'placeholder' => 'Select Layout',
															 'default' => 'default',
															 'desc' => 'Select your timeline Layout',
															 'options' => array(
																 'default'    => 'Vertical',
																 'horizontal' => 'Horizontal',
																 'one-side'   => 'One Side Layout',
																 'compact' => 'Compact Layout',
															 ),
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

														 // array(
														 // 'id' => 'compact-ele-pos',
														 // 'type' => 'select',
														 // 'title' => 'Compact Layout Date&Title positon',
														 // 'default' => 'main-date',
														 // 'dependency' => array( 'layout', '==', 'compact' ),
														 // 'options' => array(
														 // 'main-date' => __( "On top date/label below title",'cool-timeline' ) ,
														 // 'main-title' => __( "On top title below date/label",'cool-timeline')

														 // ),
														 // ),

														 array(
															 'id'      => 'designs',
															 'type'    => 'select',
															 'title'   => 'Select Design',
															 'placeholder' => 'Select Design',
															 'default' => 'default',
															 'desc'    => 'Select your timeline Design',
															 'options' => array(
																 'design-1' => 'Default',
																 'design-2' => 'Flat Design',
																 'design-3' => 'Classic Design',
																 'design-4' => 'Elegant Design',
																 'design-5' => 'Clean Design',
																 'design-6' => 'Modern Design',
																 'design-7' => 'Minimal Design',
																 'design-8' => 'Simple Design',
															 ),
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 'dependency' => array( 'layout', '==', 'horizontal' ),
														 ),

														 array(
															 'id'      => 'designs',
															 'type'    => 'select',
															 'title'   => 'Select Design',
															 'placeholder' => 'Select Design',
															 'default' => 'default',
															 'desc'    => 'Select your timeline Design',
															 'options' => array(
																 'design-1' => 'Default',
																 'design-2' => 'Flat Design',
																 'design-3' => 'Classic Design',
																 'design-4' => 'Elegant Design',
																 'design-5' => 'Clean Design',
																 'design-6' => 'Modern Design',
																 'design-7' => 'Minimal Design',
															 ),
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 'dependency' => array( 'layout', '!=', 'horizontal' ),
														 ),

														 array(
															 'id' => 'skin',
															 'type' => 'select',
															 'title' => 'Select Skin',
															 'default' => 'default',
															 'dependency' => array( 'layout', '!=', 'horizontal' ),
															 'options' => array(
																 'default' => 'Default',
																 'dark'    => 'Dark',
																 'light'   => 'Light',
															 ),
															 'desc' => 'Create Light, Dark or Colorful Timeline',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

														 array(
															 'id' => 'post-type',
															 'type' => 'text',
															 'title' => 'Select Post Type',
															 'default' => 'post',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 // 'options'     => 'post_types',

															 'desc' => 'Dont Change This If You Are Creating Blog Posts Timeline or Define Content Type Of Your Timeline Like:- Posts',
														 ),

														 array(
															 'id' => 'taxonomy',
															 'type' => 'text',
															 'title' => 'Taxonomy Name',
															 'default' => 'category',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 // 'options'     => 'ctl_get_all_texonomy',
															 'desc' => 'Dont Change This If You Are Creating Blog Posts Timeline or Define Content Type Taxonomy.',
														 ),

														 array(
															 'id' => 'post-category',
															 'type' => 'select',
															 'title' => 'Specific category(s) (Add category(s) slug - comma separated)',
															 'default' => '',
															 'placeholder' => 'Select Categorys',
															 'chosen' => true,
															 'multiple' => true,
															 'options' => 'get_all_cate',
															 'settings' => array(
																 'width' => '50%',
															 ),

															 'desc' => 'Show Category Specific Blog Posts. Like For cooltimeline.com/category/fb-history/ it will be fb-history',
														 ),

														 array(
															 'id' => 'tags',
															 'type' => 'select',
															 'title' => 'Specific tags(add tags slug)',
															 'placeholder' => 'Select tags',
															 'default' => '',
															 'chosen' => true,
															 'multiple' => true,
															 'settings' => array(
																 'width' => '50%',
															 ),
															 'options' => 'tags',
															 'desc' => 'Show Tag Specific Blog Posts. Like For cooltimeline.com/tag/fb-history/ it will be fb-history.',
														 ),

														 array(
															 'id' => 'filter-categories',
															 'type' => 'select',
															 'title' => 'Add categories slug for filters',
															 'default' => '',
															 'dependency' => array( 'filters', '==', 'yes' ),
															 'placeholder' => 'Select Categorys',
															 'chosen' => true,
															 'multiple' => true,
															 'options' => 'ctl_get_post_cat',
															 'settings' => array(
																 'width' => '50%',
															 ),
															 'desc' => 'eg(stories,our-history)',
														 ),

														 array(
															 'id'  => 'story-content',
															 'type' => 'select',
															 'title' => 'Stories Description?',
															 'default' => 'short',
															 'options' => array(
																 'short' => 'Summary',
																 'full'  => 'Full Text',
															 ),
															 'desc' => '<span>Summary:- Short description<br>Full:- All content with formated text.</span>',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),

														 ),

														 array(
															 'id' => 'show-posts',
															 'type' => 'text',
															 'title' => 'Display Per Page?',
															 'default' => '10',
															 'desc' => 'You Can Show Pagination After These Posts In Vertical Timeline.',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

														 array(
															 'id' => 'items',
															 'type' => 'select',
															 'title' => 'Slide To Show?',
															 'default' => '',
															 'placeholder' => 'Select number of items',
															 'dependency' => array( 'layout', '==', 'horizontal' ),
															 'options' => array(
																 '1' => '1',
																 '2' => '2',
																 '3' => '3',
																 '4' => '4',

															 ),
															 'desc' => 'Number of slide to show',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

														 array(
															 'id' => 'year-label',
															 'type' => 'button_set',
															 'title' => 'Year Label Setting',
															 'default' => 'show',
															 'options' => array(
																 'hide' => 'Hide',
																 'show' => 'Show',
															 ),
															 'desc' => '',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 'dependency' => array(
																 array( 'layout', '!=', 'compact' ),
															 ),
														 ),

														 array(
															 'id' => 'year-navigation',
															 'type' => 'button_set',
															 'title' => 'Year Navigation Setting',
															 'default' => 'hide',
															 'options' => array(
																 'hide' => 'Hide',
																 'show' => 'Show',
															 ),
															 'desc' => '',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 'dependency' => array(
																 array( 'layout', '==', 'compact' ),
															 ),
														 ),

														 array(
															 'id' => 'year-navigation',
															 'type' => 'button_set',
															 'title' => 'Year Navigation Setting',
															 'default' => 'hide',
															 'options' => array(
																 'hide' => 'Hide',
																 'show' => 'Show',
															 ),
															 'desc' => '',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 'dependency' => array(
																 array( 'year-label', '==', 'show' ),
																 array( 'layout', '!=', 'compact' ),
															 ),
														 ),

														 array(
															 'id' => 'navigation-position',
															 'type' => 'button_set',
															 'title' => 'Year Navigation Position',
															 'default' => 'center',
															 'options' => array(
																 'left'   => 'Left',
																 'center' => 'Center',
																 'right'  => 'Right',
															 ),
															 'desc' => '',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 'dependency' => array(
																 array( 'year-label', '==', 'show' ),
																 array( 'year-navigation', '==', 'show' ),
																 array( 'layout', '==', 'horizontal' ),
															 ),
														 ),
														 array(
															 'id' => 'navigation-position',
															 'type' => 'button_set',
															 'title' => 'Year Navigation Position',
															 'default' => 'right',
															 'options' => array(
																 'left'   => 'Left',
																 'right'  => 'Right',
																 'bottom' => 'Bottom',
															 ),
															 'desc' => '',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
															 'dependency' => array(
																 array( 'year-navigation', '==', 'show' ),
																 array( 'layout', '!=', 'horizontal' ),
															),
														 ),

														 array(
															'id'      => 'navigation-style',
															'type'    => 'select',
															'title'   => 'Year Navigation Style',
															'default' => 'right',
															'options' => array(
																'style-1' => 'Style 1',
																'style-2' => 'Style 2',
																'style-3' => 'Style 3',
															),
															'desc'    => '',
															'attributes' => array(
																'style' => 'width: 50%;',
															),
															'dependency' => array(
																array( 'year-navigation', '==', 'show' ),
																array( 'layout', '!=', 'horizontal' ),
																array( 'navigation-position', '!=', 'bottom' ),
															),
														),

														 array(
															 'id' => 'autoplay',
															 'type' => 'select',
															 'title' => 'Autoplay Settings?',
															 'default' => 'false',
															 'dependency' => array( 'layout', '==', 'horizontal' ),
															 'options' => array(
																 'true'  => 'True',
																 'false' => 'False',

															 ),
															 'desc' => 'Number of slide to show',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

														 array(
															 'id' => 'autoplay-speed',
															 'type' => 'text',
															 'title' => 'Slideshow Speed ?',
															 'dependency' => array( 'layout', '==', 'horizontal' ),
															 'default' => '3000',
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

														 array(
															 'id' => 'start-on',
															 'type' => 'text',
															 'title' => 'Timeline Starting from Story e.g(2)',
															 'dependency' => array( 'layout', '==', 'horizontal' ),
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

														 array(
															 'id' => 'animation',
															 'type' => 'select',
															 'title' => 'Animation',
															 'default' => 'none',
															 'options' => array(
																 'none'     => 'none',
																 'fade'     => 'fade',
																 'zoom-in'  => 'zoom-in',
																 'flip-right' => 'flip-right',
																 'zoom-out' => 'zoom-out',
																 'fade-up'  => 'fade-up',
																 'fade-down' => 'fade-down',
																 'fade-left' => 'fade-left',
																 'fade-right' => 'fade-right',
																 'fade-up-right' => 'fade-up-right',
																 'fade-up-left' => 'fade-up-left',
																 'fade-down-right' => 'fade-down-right',
																 'fade-down-left' => 'fade-down-left',
																 'flip-up'  => 'flip-up',
																 'flip-down' => 'flip-down',
																 'flip-left' => 'flip-left',
																 'slide-up' => 'slide-up',
																 'slide-left' => 'slide-left',
																 'slide-right' => 'slide-right',
																 'zoom-in-up' => 'zoom-in-up',
																 'zoom-in-down' => 'zoom-in-down',
																 'slide-down' => 'slide-down',
																 'zoom-in-left' => 'zoom-in-left',
																 'zoom-in-right' => 'zoom-in-right',
																 'zoom-out-up' => 'zoom-out-up',
																 'zoom-out-down' => 'zoom-out-down',
																 'zoom-out-left' => 'zoom-out-left',
																 'zoom-out-right' => 'zoom-out-right',

															 ),
															 'dependency' => array( 'layout', '!=', 'horizontal' ),
															 'attributes' => array(
																 'style' => 'width: 50%;',
															 ),
														 ),

													 ),
												 ),

										// General tab end

										// Advance tab start
										array(
											'title'  => 'Advance',
											// 'icon'      => 'fa fa-heart',
											'fields' => array(

												array(
													'id'   => 'order',
													'type' => 'select',
													'title' => 'Stories Order?',
													'default' => 'DESC',
													'options' => array(
														'DESC' => 'DESC',
														'ASC'  => 'ASC',
													),
													'attributes' => array(
														'style' => 'width: 50%;',
													),
													'desc' => '<span>Timeline Stories order like:- DESC(2017-1900) , ASC(1900-2017)</span>',
												),

												array(
													'id'   => 'story-date',
													'type' => 'button_set',
													'title' => 'Stories Date?',
													'options' => array(
														'show' => 'Show',
														'hide' => 'Hide',
													),
													'default' => 'show',
												),

												array(
													'id'   => 'date-format',
													'type' => 'select',
													'title' => 'Select Date Formats',
													'default' => 'F j',
													'options' => array(
														'F j'    => date_i18n( 'F j' ),
														'F j Y'  => date_i18n( 'F j Y' ),
														'Y-m-d'  => date_i18n( 'Y-m-d' ),
														'm/d/Y'  => date_i18n( 'm/d/Y' ),
														'd/m/Y'  => date_i18n( 'd/m/Y' ),
														'F j Y g:i A' => date_i18n( 'F j Y g:i A' ),
														'Y'      => date_i18n( 'Y' ),
														'custom' => 'Custom',
													),
													'desc' => 'Timeline Stories dates formats',
													'attributes' => array(
														'style' => 'width: 50%;',
													),
													'dependency' => array( 'story-date', '==', 'show' ),
												),

												array(
													'id'   => 'custom-date-format',
													'type' => 'text',
													'title' => 'Custom Date Format',
													'default' => '',
													'dependency' => array( 'date-format', '==', 'custom' ),
												),

												array(
													'id'   => 'icons',
													'type' => 'button_set',
													'title' => 'Icons',
													'options' => array(
														'icon' => 'ICON',
														'dot' => 'DOT',
														'none' => 'NONE',
													),
													'default' => 'dot',
													'desc' => 'Display Icons In Timeline Stories. By default Is Dot',
													'attributes' => array(
														'style' => 'width: 50%;',
													),
												),

												array(
													'id'   => 'pagination',
													'type' => 'select',
													'title' => 'Pagination Settings',
													'default' => 'default',
													'options' => array(
														'off'     => 'Off',
														'default' => 'Default',
														'ajax_load_more' => 'Ajax Load More',
													),
													'desc' => '',
													'attributes' => array(
														'style' => 'width: 50%;',
													),
												),

												array(
													'id'   => 'filters',
													'type' => 'button_set',
													'title' => 'Category Filters Settings',
													'default' => 'no',
													'options' => array(
														'no'  => 'NO',
														'yes' => 'YES',
													),
													'desc' => '',
													'attributes' => array(
														'style' => 'width: 50%;',
													),
												),

												array(
													'id'   => 'filter-categories',
													'type' => 'text',
													'title' => 'Add categories slug for filters eg(stories,our-history)',
													'default' => '',
													'dependency' => array( 'filters', '==', 'yes' ),

												),

												array(
													'id'   => 'line-filling',
													'type' => 'button_set',
													'title' => 'Line Filling',
													'default' => 'false',
													'options' => array(
														'true'  => 'True',
														'false' => 'False',
													),
													'desc' => 'Center Line Filling In Timeline Stories. By default Is False',
													'attributes' => array(
														'style' => 'width: 50%;',
													),
												),

												array(
													'id'   => 'read-more',
													'type' => 'button_set',
													'title' => 'Display read more?',
													'options' => array(
														'show' => 'Show',
														'hide' => 'Hide',
													),
													'default' => 'show',
												),

												array(
													'id'   => 'content-length',
													'type' => 'text',
													'title' => 'Content Length',
													'default' => '50',
													'dependency' => array( 'read-more', '==', 'show' ),

												),

												array(
													'id'   => 'post-meta',
													'type' => 'button_set',
													'title' => 'Display Post Meta?',
													'options' => array(
														'show' => 'Show',
														'hide' => 'Hide',
													),
													'default' => 'show',
												),

												array(
													'id'   => 'meta-key',
													'type' => 'text',
													'title' => 'Meta Key',
													'default' => '',
												),

											),
										),
										// Advance tab end

										// Preview Tab Start
										array(
											'title'  => 'Preview',
											'fields' => array(
												array(
													'id'   => 'preview',
													'type' => 'content',
													'content' => sprintf(
														'<iframe id="ctl_preview" name="my_iframe" src="%s" title="preview iframe" scrolling="auto" frameborder="0" data-preloader="%s"></iframe>',
														esc_url( CTP_PLUGIN_URL . 'includes/shortcodes/class-ctl-shortcode-preview.php' ),
														esc_url( CTP_PLUGIN_URL . 'assets/images/order-preloader.gif' )
													),
												),
											),
										),
										// Preview Tab End
									),

								),

							),
						)
					);

			}

			 /**
			  * Fetch all timeline items for shortcode builder options
			  *
			  * @return array $ids An array of timeline item's ID & title
			  */

			function get_all_tagss() {
									$args  = array(
										'public' => true,
									);
									$posts = array();
									$i     = 1;
									foreach ( get_taxonomies( $args, 'names' ) as $post_type ) {

										$posts[ $post_type ] = get_tags(
											array(
												'taxonomy' => $post_type,
												'hide_empty' => false,
											)
										);

									}

									$ctl_categories = array();
									if ( ! empty( $posts ) || ! is_wp_error( $posts ) ) {
										foreach ( $posts as $key => $term ) {
											// $ctl_categories[$i]=!empty($term[0]->taxonomy)?$term[0]->taxonomy .' (Category)':"";
											foreach ( $term as $keys => $value ) {
												$ctl_categories[ $value->slug ] = $value->name;

											}
											// $i++;

										}
									}

									return $ctl_categories;

			}
			function get_all_cate() {
									$args  = array(
										'public' => true,
									);
									$posts = array();
									$i     = 1;
									foreach ( get_taxonomies( $args, 'names' ) as $post_type ) {

										$posts[ $post_type ] = get_terms(
											array(
												'taxonomy' => $post_type,
												'hide_empty' => false,
											)
										);

									}

									$ctl_categories = array();
									if ( ! empty( $posts ) || ! is_wp_error( $posts ) ) {
										foreach ( $posts as $key => $term ) {
											// $ctl_categories[$i]=!empty($term[0]->taxonomy)?$term[0]->taxonomy .' (Category)':"";
											foreach ( $term as $keys => $value ) {
												$ctl_categories[ $value->slug ] = $value->name;

											}
											// $i++;

										}
									}

									return $ctl_categories;

			}
			function ctl_get_all_texonomy() {
				$args  = array(
					'public' => true,
				);
				$posts = array();
				foreach ( get_taxonomies( $args, 'names' ) as $post_type ) {
					if ( $post_type == 'post_tag' || $post_type == 'post_format' ) {

					} else {

						$posts[ $post_type ] = $post_type;
					}
				}
				return $posts;
			}

			function ctl_get_all_post() {
				$args  = array(
					'public' => true,
				);
				$posts = array();
				foreach ( get_post_types( $args, 'names' ) as $post_type ) {
					$posts[ $post_type ] = $post_type;

				}
				return $posts;
			}

			function ctl_select_category() {

				$terms              = get_terms(
					array(
						'taxonomy'   => 'ctl-stories',
						'hide_empty' => false,
					)
				);
				$ctl_categories     = array();
				$ctl_categories[''] = 'All Categories';

				if ( ! empty( $terms ) || ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$ctl_categories[ $term->slug ] = $term->name;
					}
				}

				return $ctl_categories;

			}
			function ctl_select_category_slug() {

				$terms          = get_terms(
					array(
						'taxonomy'   => 'ctl-stories',
						'hide_empty' => false,
					)
				);
				$ctl_categories = array();

				if ( ! empty( $terms ) || ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$ctl_categories[ $term->slug ] = $term->name;
					}
				}

				return $ctl_categories;

			}

			function ctl_get_post_cat() {

				$terms          = get_terms(
					array(
						'taxonomy'   => 'category',
						'hide_empty' => false,
					)
				);
				$ctl_categories = array();

				if ( ! empty( $terms ) || ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$ctl_categories[ $term->slug ] = $term->name;
					}
				}

				return $ctl_categories;

			}
			function ctl_get_post_tags() {

				$tags     = get_tags(
					array(
						'taxonomy'   => 'category',
						'hide_empty' => false,
					)
				);
				$tag_list = array();
				foreach ( $tags as $tag ) {
					$tag_list[ $tag->name ] = $tag->name;

				}

				return $tag_list;

			}

		}

	}

}
new CTL_shortcode_generator();
