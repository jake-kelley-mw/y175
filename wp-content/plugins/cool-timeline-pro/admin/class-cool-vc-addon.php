<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoolVCAddon' ) ) {

	class CoolVCAddon {

		/**
		 * The Constructor
		 */
		public function __construct() {
			 // We safely integrate with VC with this hook
			add_action( 'init', array( $this, 'ctl_vc_addon' ) );
		}

		function ctl_vc_addon() {
			if ( defined( 'WPB_VC_VERSION' ) ) {

				$terms                          = get_terms(
					array(
						'taxonomy'   => 'ctl-stories',
						'hide_empty' => false,
					)
				);
				$ctl_terms_l                    = array();
				$ctl_terms_l['Select Category'] = '';

				if ( ! empty( $terms ) || ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$ctl_terms_l[ $term->name ] = $term->slug;
					}
				}

				$date_formats = array(
					'F j'         => 'F j',
					'F j Y'       => 'F j Y',
					'Y-m-d'       => 'Y-m-d',
					'm/d/Y'       => 'm/d/Y',
					'd/m/Y'       => 'd/m/Y',
					'F j Y g:i A' => 'F j Y g:i A',
					'Y'           => 'Y',
					'Custom'      => 'custom',
				);
					$designs  = array(
						'Default'        => 'design-1',
						'Flat Design'    => 'design-2',
						'Classic Design' => 'design-3',
						'Elegant Design' => 'design-4',
						'Clean Design'   => 'design-5',
						'Modern Design'  => 'design-6',
						'Minimal Design' => 'design-7',
						'Simple Design'  => 'design-8',
					);

					$animation_effects = array(
						'none'            => 'none',
						'fade'            => 'fade',
						'zoom-in'         => 'zoom-in',
						'flip-right'      => 'flip-right',
						'zoom-out'        => 'zoom-out',
						'fade-up'         => 'fade-up',
						'fade-down'       => 'fade-down',
						'fade-left'       => 'fade-left',
						'fade-right'      => 'fade-right',
						'fade-up-right'   => 'fade-up-right',
						'fade-up-left'    => 'fade-up-left',
						'fade-down-right' => 'fade-down-right',
						'fade-down-left'  => 'fade-down-left',
						'flip-up'         => 'flip-up',
						'flip-down'       => 'flip-down',
						'flip-left'       => 'flip-left',
						'slide-up'        => 'slide-up',
						'slide-left'      => 'slide-left',
						'slide-right'     => 'slide-right',
						'zoom-in-up'      => 'zoom-in-up',
						'zoom-in-down'    => 'zoom-in-down',
						'slide-down'      => 'slide-down',
						'zoom-in-left'    => 'zoom-in-left',
						'zoom-in-right'   => 'zoom-in-right',
						'zoom-out-up'     => 'zoom-out-up',
						'zoom-out-down'   => 'zoom-out-down',
						'zoom-out-left'   => 'zoom-out-left',
						'zoom-out-right'  => 'zoom-out-right',
					);
					vc_map(
						array(
							'name'        => __( 'Cool Timeline Default', 'cool-timeline' ),
							'description' => __( 'Create Stories Timeline', 'cool-timeline' ),
							'base'        => 'cool-timeline',
							'class'       => '',
							'controls'    => 'full',
							'icon'        => CTP_PLUGIN_URL . 'assets/images/timeline-icon2-32x32.png', // or css class name which you can reffer in your css file later. Example: "cool-timeline_my_class"
							'category'    => __( 'Cool Timeline', 'cool-timeline' ),
							// 'admin_enqueue_js' => array(plugins_url('assets/cool-timeline.js', __FILE__)), // This will load js file in the VC backend editor
							// 'admin_enqueue_css' => array(plugins_url('assets/cool-timeline_admin.css', __FILE__)), // This will load css file in the VC backend editor

							'params'      => array(

								// < ------  general tab start   ------->
								array(
									'type'       => 'vc_tab',
									'heading'    => __( 'General Settings Tab', 'cool-timeline' ),
									'param_name' => 'general_tab',
									'group'      => 'General Settings', // Group name for the tab
									'value'      => array(
										__( 'General Settings Tab', 'cool-timeline' ) => 'general_tab', // Default tab label
									),
								),

								array(
									'type'        => 'dropdown',
									'class'       => 'ctl_vc_layout',
									'heading'     => __( 'Timeline Layout', 'cool-timeline' ),
									'param_name'  => 'layout',
									'value'       => array(
										__( 'Vertical Both side', 'cool-timeline' ) => 'default',
										__( 'Horizontal Timeline', 'cool-timeline' ) => 'horizontal',
										__( 'Vertical one sided', 'cool-timeline' ) => 'one-side',
										__( 'Compact Layout', 'cool-timeline' ) => 'compact',
									),
									'save_always' => true,
									'group'       => 'General Settings',
									'description' => __( 'Select your timeline layout ', 'cool-timeline' ),
								),

								array(
									'type'        => 'dropdown',
									'class'       => 'ctl_vc_design',
									'heading'     => __( 'Timeline Designs', 'cool-timeline' ),
									'param_name'  => 'designs',
									'value'       => $designs,
									'save_always' => true,
									'group'       => 'General Settings',
									'description' =>
										'Choose Timeline Designs (Check Vertical Designs & Horizontal Designs )
                       <br><a target="_blank" href="' . CTP_DEMO_URL . '">Vertical Timeline demos</a>
                          |   <a target="_blank" href="' . CTP_DEMO_URL . '">Horizontal Timeline demos</a>.<br>
						  Simple Design Only Available in Horizontal Layout',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Timeline skin', 'cool-timeline' ),
									'param_name'  => 'skin',
									'value'       => array(
										__( 'Default', 'cool-timeline' ) => 'default',
										__( 'Light', 'cool-timeline' ) => 'light',
										__( 'dark', 'cool-timeline' ) => 'dark',
									),
									'description' => __( 'Create Light, Dark or Colorful Timeline', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'General Settings',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Select Stories Category', 'cool-timeline' ),
									'param_name'  => 'category',
									'value'       => $ctl_terms_l,
									'description' => __( 'Create Category Specific Timeline (By Default - All Categories)', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'General Settings',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Timeline Based On', 'cool-timeline' ),
									'param_name'  => 'based',
									'value'       => array(
										__( 'Default (Date Based)', 'cool-timeline' ) => 'default',
										__( 'Custom Order Number', 'cool-timeline' ) => 'custom',
									),
									'description' => __( 'Show either date or custom label/text along with timeline stories.', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'General Settings',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Story Content', 'cool-timeline' ),
									'param_name'  => 'story-content',
									'value'       => array(
										__( 'Summary', 'cool-timeline' ) => 'short',
										__( 'Full Text', 'cool-timeline' ) => 'full',

									),
									'description' => __( '', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'General Settings',
								),

								array(
									'type'        => 'textfield',
									'class'       => '',
									'heading'     => __( 'Show number of Stories', 'cool-timeline' ),
									'param_name'  => 'show-posts',
									'value'       => __( 10, 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'General Settings',
									'description' => __( 'You Can Show Pagination After These Posts In Vertical Timeline.', 'cool-timeline' ),
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Display Stories Blocks/Columns', 'cool-timeline' ),
									'param_name'  => 'items',
									'value'       => array(
										__( 'Select number of items', 'cool-timeline' ) => '',
										__( '1', 'cool-timeline' ) => 1,
										__( '2', 'cool-timeline' ) => 2,
										__( '3', 'cool-timeline' ) => 3,
										__( '4', 'cool-timeline' ) => 4,
									),
									'description' => '*This Options Is Not For Default Design. <a href="' . esc_url( CTP_DEMO_URL ) . '">Horizontal Timeline</a>',
									'save_always' => true,
									'group'       => 'General Settings',
									'description' => __( 'This options is not for default design.', 'cool-timeline' ),
									'dependency'  => array(
										'element' => 'layout',
										'value'   => array( 'horizontal' ),
									),
								),

								// <------ general tab end ------->

								// <------ Advance tab start ------->

								array(
									'type'       => 'vc_tab',
									'heading'    => __( 'Advance Settings Tab', 'cool-timeline' ),
									'param_name' => 'advance_tab',
									'group'      => 'Advance Settings', // Group name for the tab
									'value'      => array(
										__( 'Advance Settings Tab', 'cool-timeline' ) => 'advance_tab', // Default tab label
									),
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Order', 'cool-timeline' ),
									'param_name'  => 'order',
									'value'       => array(
										__( 'DESC', 'cool-timeline' ) => 'DESC',
										__( 'ASC', 'cool-timeline' ) => 'ASC',
									),
									'description' => __( 'Timeline Stories order like:- DESC(2017-1900) , ASC(1900-2017)', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Stories Date?', 'cool-timeline' ),
									'param_name'  => 'story-date',
									'value'       => array(
										__( 'Show', 'cool-timeline' ) => 'show',
										__( 'Hide', 'cool-timeline' ) => 'hide',
									),
									'description' => __( 'Display Icons In Timeline Stories. By default Is Dot.', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Date Formats', 'cool-timeline' ),
									'param_name'  => 'date-format',
									'value'       => $date_formats,
									'description' => __( 'Timeline Stories dates custom formats', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'story-date',
										'value'   => array( 'show' ),
									),
								),

								array(
									'type'        => 'textfield',
									'class'       => '',
									'heading'     => __( 'Custom Date Format', 'cool-timeline' ),
									'param_name'  => 'custom-date-format',
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'date-format',
										'value'   => array( 'custom' ),
									),
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Icons', 'cool-timeline' ),
									'param_name'  => 'icons',
									'value'       => array(
										__( 'Dot', 'cool-timeline' ) => 'dot',
										__( 'Icon', 'cool-timeline' ) => 'icon',
										__( 'No', 'cool-timeline' )  => 'none',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Pagination ?', 'cool-timeline' ),
									'param_name'  => 'pagination',
									'value'       => array(
										__( 'Default', 'cool-timeline' ) => 'default',
										__( 'Ajax Load More', 'cool-timeline' ) => 'ajax_load_more',
										__( 'Off', 'cool-timeline' ) => 'off',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'type',
										'value'   => array( 'default' ),
									),

								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Enable category filters ?', 'cool-timeline' ),
									'param_name'  => 'filters',
									'value'       => array(
										__( 'No', 'cool-timeline' ) => 'no',
										__( 'Yes', 'cool-timeline' ) => 'yes',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
									// "description" => __( " ",'cool-timeline' ),
									'dependency'  => array(
										'element' => 'type',
										'value'   => array( 'default' ),

									),
								),

								array(
									'type'        => 'textfield',
									'class'       => '',
									'heading'     => __( 'Add categories slug for filters', 'cool-timeline' ),
									'param_name'  => 'filter-categories',
									'value'       => __( '', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'description' => __( ' eg(stories,our-history)', 'cool-timeline' ),
									'dependency'  => array(
										'element' => 'filters',
										'value'   => 'yes',
	
									),
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Year Label Setting', 'cool-timeline' ),
									'param_name'  => 'year-label',
									'value'       => array(
										__( 'Show', 'cool-timeline' ) => 'show',
										__( 'Hide', 'cool-timeline' ) => 'hide',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'layout',
										'value'   => array( 'default', 'horizontal', 'one-side' ),
									),
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Year Navigation Setting', 'cool-timeline' ),
									'param_name'  => 'year-navigation',
									'value'       => array(
										__( 'Hide', 'cool-timeline' ) => 'hide',
										__( 'Show', 'cool-timeline' ) => 'show',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Year Navigation Position', 'cool-timeline' ),
									'param_name'  => 'navigation-position',
									'value'       => array(
										__( 'Left', 'cool-timeline' ) => 'left',
										__( 'Right', 'cool-timeline' ) => 'right',
										__( 'Bottom', 'cool-timeline' ) => 'bottom',
										__( 'Center', 'cool-timeline' ) => 'center',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'year-navigation',
										'value'   => array( 'show' ),
									),

								),
								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Year Navigation Style', 'cool-timeline' ),
									'param_name'  => 'navigation-style',
									'value'       => array(
										__( 'Style 1', 'cool-timeline' ) => 'style-1',
										__( 'Style 2', 'cool-timeline' ) => 'style-2',
										__( 'Style 3', 'cool-timeline' ) => 'style-3',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'year-navigation',
										'value'   => array( 'show' ),
									),
									'description' => __( 'This setting not working in horizontal layout and navigation position bottom', 'cool-timeline' ),

								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Line Filling', 'cool-timeline' ),
									'param_name'  => 'line-filling',
									'value'       => array(
										__( 'False', 'cool-timeline' ) => 'false',
										__( 'True', 'cool-timeline' ) => 'true',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
									// "description" => __( " ",'cool-timeline' ),
									'description' => __( 'Center Line Filling In Timeline Stories. By default Is False', 'cool-timeline' ),
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Display read more?', 'cool-timeline' ),
									'param_name'  => 'read-more',
									'value'       => array(
										__( 'Show', 'cool-timeline' ) => 'show',
										__( 'Hide', 'cool-timeline' ) => 'hide',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
								  // "description" => __( " ",'cool-timeline' ),
								),

								array(
									'type'        => 'textfield',
									'class'       => '',
									'heading'     => __( 'Content Length', 'cool-timeline' ),
									'param_name'  => 'content-length',
									'value'       => __( 50, 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'read-more',
										'value'   => array( 'show' ),
									),
								),

								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Autoplay Stories settings ?', 'cool-timeline' ),
									'param_name'  => 'autoplay',
									'value'       => array(
										__( 'False', 'cool-timeline' ) => 'false',
										__( 'True', 'cool-timeline' ) => 'true',
									),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'layout',
										'value'   => array( 'horizontal' ),
									),
								),

								array(
									'type'        => 'textfield',
									'class'       => '',
									'heading'     => __( 'Slideshow Speed', 'cool-timeline' ),
									'param_name'  => 'autoplay-speed',
									'value'       => __( 10, 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'layout',
										'value'   => array( 'horizontal' ),
									),
								),

								array(
									'type'        => 'textfield',
									'class'       => '',
									'heading'     => __( 'Timeline Starting from Story e.g(2)', 'cool-timeline' ),
									'param_name'  => 'start-on',
									'value'       => __( 0, 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
									'description' => __( '', 'cool-timeline' ),
									'dependency'  => array(
										'element' => 'layout',
										'value'   => array( 'content-timeline', 'horizontal' ),
									),

								),
								array(
									'type'        => 'dropdown',
									'class'       => '',
									'heading'     => __( 'Animations Effect', 'cool-timeline' ),
									'param_name'  => 'animation',
									'value'       => $animation_effects,
									'description' => "Add Animations Effect Inside Timeline. You Can Check Effects Demo From <a  target='_blank' href='http://michalsnik.github.io/aos/'>AOS</a>",
									'save_always' => true,
									'group'       => 'Advance Settings',
									'dependency'  => array(
										'element' => 'layout',
										'value'   => array( 'default' ),
									),
								),

								array(
									'type'        => 'textfield',
									'class'       => '',
									'heading'     => __( 'Timeline Title', 'cool-timeline' ),
									'param_name'  => 'timeline-title',
									'value'       => __( '', 'cool-timeline' ),
									'save_always' => true,
									'group'       => 'Advance Settings',
								),

								// array(
								// "type" => "dropdown",
								// "class" => "",
								// "heading" => __( "Compact Layout Date&Title positon",'cool-timeline'),
								// "param_name" => "compact-ele-pos",
								// "value" => array(
								// __( "On top date/label below title",'cool-timeline' ) => "main-date",
								// __( "On top title below date/label",'cool-timeline') => "main-title",
								// ),
								// "description" => __( "",'cool-timeline' ),
								// 'save_always' => true,
								// ),
							),

						// <------ Advance tab end ------->

						)
					);

				/*
				 * content timeline shortcode
				 */

				vc_map(
					array(
						'name'        => esc_html__( 'Cool Content Timeline', 'cool-timeline' ),
						'description' => esc_html__( 'Create Blog Posts Timeline', 'cool-timeline' ),
						'base'        => 'cool-content-timeline',
						'class'       => '',
						'controls'    => 'full',
						'icon'        => esc_url( CTP_PLUGIN_URL . '/assets/images/timeline-icon2-32x32.png' ), // or css class name which you can refer in your css file later. Example: "cool-timeline_my_class"
						'category'    => esc_html__( 'Cool Timeline', 'js_composer' ),
						// 'admin_enqueue_js' => array(plugins_url('assets/cool-timeline.js', __FILE__)), // This will load js file in the VC backend editor
						// 'admin_enqueue_css' => array(plugins_url('assets/cool-timeline_admin.css', __FILE__)), // This will load css file in the VC backend editor
						'params'      => array(

							// <------ general tab start ------->

							array(
								'type'       => 'vc_tab',
								'heading'    => __( 'General Settings Tab', 'cool-timeline' ),
								'param_name' => 'general_tab',
								'group'      => 'General Settings', // Group name for the tab
								'value'      => array(
									__( 'General Settings Tab', 'cool-timeline' ) => 'general_tab', // Default tab label
								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Timeline Layout', 'cool-timeline' ),
								'param_name'  => 'layout',
								'value'       => array(
									__( 'Default Layout (Vertical Both side)', 'cool-timeline' ) => 'default',
									__( 'One Side Layout (Vertical one sided)', 'cool-timeline' ) => 'one-side',
									__( 'Compact Layout', 'cool-timeline' ) => 'compact',
									__( 'Horizontal Layout', 'cool-timeline' ) => 'horizontal',
								),
								'save_always' => true,
								'group'       => 'General Settings',
								'description' => __( 'Select your timeline layout ', 'cool-timeline' ),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Display Stories Blocks/Columns', 'cool-timeline' ),
								'param_name'  => 'items',
								'value'       => array(
									__( 'Select no of items', 'cool-timeline' ) => '',
									__( 1, 'cool-timeline' ) => 1,
									__( 2, 'cool-timeline' ) => 2,
									__( 3, 'cool-timeline' ) => 3,
									__( 4, 'cool-timeline' ) => 4,
								),
								'description' => __( 'Horizontal Layout (This option only for content timeline)', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
								'description' => __( '*This Options Is Not For Default Design. (Check Demo Here)', 'cool-timeline' ),
								'dependency'  => array(
									'element' => 'layout',
									'value'   => array( 'horizontal' ),
								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Autoplay Stories settings ?', 'cool-timeline' ),
								'param_name'  => 'autoplay',
								'value'       => array(
									__( 'False', 'cool-timeline' ) => 'false',
									__( 'True', 'cool-timeline' ) => 'true',
								),
								'save_always' => true,
								'group'       => 'General Settings',
								'dependency'  => array(
									'element' => 'layout',
									'value'   => array( 'horizontal' ),
								),
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Timeline Starting From Story e.g(2)', 'cool-timeline' ),
								'param_name'  => 'start-on',
								'value'       => __( 0, 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
								'description' => __( '', 'cool-timeline' ),
								'dependency'  => array(
									'element' => 'layout',
									'value'   => array( 'horizontal' ),
								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Timeline Designs', 'cool-timeline' ),
								'param_name'  => 'designs',
								'value'       => $designs,
								'save_always' => true,
								'group'       => 'General Settings',
								'description' =>
									'Choose Timeline Designs (Check Vertical Designs & Horizontal Designs )
                       				<br><a target="_blank" rel="noopener noreferrer" href="' . esc_url( CTP_DEMO_URL ) . '">Vertical Timeline demos</a>
                          			|   <a target="_blank" rel="noopener noreferrer" href="' . esc_url( CTP_DEMO_URL ) . '">Horizontal Timeline demos</a>.<br>
						  			Simple Design Only Available in Horizontal Layout',
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Timeline skin', 'cool-timeline' ),
								'param_name'  => 'skin',
								'value'       => array(
									__( 'Default', 'cool-timeline' ) => 'default',
									__( 'Light', 'cool-timeline' ) => 'light',
									__( 'dark', 'cool-timeline' ) => 'dark',
								),
								'description' => __( 'Create Light, Dark or Colorful Timeline.', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Content Post type', 'cool-timeline' ),
								'param_name'  => 'post-type',
								'value'       => __( 'post', 'cool-timeline' ),
								'description' => __( 'Don\'t Change This If You Are Creating Blog Posts Timeline or Define Content Type Of Your Timeline Like:- Posts', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Taxonomy Name ', 'cool-timeline' ),
								'param_name'  => 'taxonomy',
								'value'       => __( 'category', 'cool-timeline' ),
								'description' => __( "Don't Change This If You Are Creating Blog Posts Timeline or Define Content Type Taxonomy.", 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Category Specific Timeline (Add category(s) slug - comma separated)', 'cool-timeline' ),
								'param_name'  => 'post-category',
								'value'       => __( '', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
								'description' => ( 'Show Category Specific Blog Posts. Like For cooltimeline.com/category/fb-history/ it will be <b>fb-history</b>' ),
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Tag Specific Timeline (Add Category Slug)', 'cool-timeline' ),
								'param_name'  => 'tags',
								'value'       => __( '', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
								'description' => ( 'Show Tag Specific Blog Posts. Like For cooltimeline.com/tag/fb-history/ it will be <b>fb-history</b>.' ),
							),

							// array(
							// "type" => "dropdown",
							// "class" => "",
							// "heading" => __( "Timeline Based On",'cool-timeline'),
							// "param_name" => "based",
							// "value" => array(
							// __( "Default (Date Based)",'cool-timeline' ) => "default",
							// __( "Custom Order Number",'cool-timeline') => "custom",
							// ),
							// "description" => __( "Show either date or custom label/text along with timeline stories.",'cool-timeline' ),
							// 'save_always' => true,
							// "group" => "General Settings",
							// "dependency" => array("element" => "general_tab", "value" => "general_tab")
							// ),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Story Content', 'cool-timeline' ),
								'param_name'  => 'story-content',
								'value'       => array(
									__( 'Summary', 'cool-timeline' ) => 'short',
									__( 'Full Text', 'cool-timeline' ) => 'full',

								),
								'description' => __( '', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Show number of posts', 'cool-timeline' ),
								'param_name'  => 'show-posts',
								'value'       => __( 20, 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'General Settings',
								'description' => __( 'You Can Show Pagination After These Posts In Vertical Timeline.', 'cool-timeline' ),

							),

							// <------ general tab end ------->

							// <----- advacnce tab start ------->

							array(
								'type'       => 'vc_tab',
								'heading'    => __( 'Advance Settings Tab', 'cool-timeline' ),
								'param_name' => 'advance_tab',
								'group'      => 'Advance Settings', // Group name for the tab
								'value'      => array(
									__( 'Advance Settings Tab', 'cool-timeline' ) => 'advance_tab', // Default tab label
								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Order', 'cool-timeline' ),
								'param_name'  => 'order',
								'value'       => array(
									__( 'DESC', 'cool-timeline' ) => 'DESC',
									__( 'ASC', 'cool-timeline' ) => 'ASC',
								),
								'description' => __( 'Timeline Stories order like:- DESC(2017-1900) , ASC(1900-2017)', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'Advance Settings',
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Stories Date?', 'cool-timeline' ),
								'param_name'  => 'story-date',
								'value'       => array(
									__( 'Show', 'cool-timeline' ) => 'show',
									__( 'Hide', 'cool-timeline' ) => 'hide',
								),
								'description' => __( 'Display Icons In Timeline Stories. By default Is Dot.', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'Advance Settings',
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Date Formats', 'cool-timeline' ),
								'param_name'  => 'date-format',
								'value'       => $date_formats,
								'description' => __( 'Timeline Stories dates custom formats', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'dependency'  => array(
									'element' => 'story-date',
									'value'   => array( 'show' ),
								),
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Custom Date Format', 'cool-timeline' ),
								'param_name'  => 'custom-date-format',
								'save_always' => true,
								'group'       => 'Advance Settings',
								'dependency'  => array(
									'element' => 'date-format',
									'value'   => array( 'custom' ),
								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Icons', 'cool-timeline' ),
								'param_name'  => 'icons',
								'value'       => array(
									__( 'Dot', 'cool-timeline' ) => 'dot',
									__( 'Icon', 'cool-timeline' ) => 'icon',
									__( 'No', 'cool-timeline' )  => 'none',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
							),
							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Pagination ?', 'cool-timeline' ),
								'param_name'  => 'pagination',
								'value'       => array(
									__( 'Default', 'cool-timeline' ) => 'default',
									__( 'Ajax Load More', 'cool-timeline' ) => 'ajax_load_more',
									__( 'Off', 'cool-timeline' ) => 'off',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'description' => __( 'Note:-Ajax Load More Is Not Available For Horizontal layout.', 'cool-timeline' ),
								'dependency'  => array(
									'element' => 'layout',
									'value'   => array( 'default', 'one-side', 'compact' ),

								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Enable category filters ?', 'cool-timeline' ),
								'param_name'  => 'filters',
								'value'       => array(
									__( 'No', 'cool-timeline' ) => 'no',
									__( 'Yes', 'cool-timeline' ) => 'yes',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'description' => __( 'Note:-Please add value in Taxonomy field before using it.', 'cool-timeline' ),
								'dependency'  => array(
									'element' => 'layout',
									'value'   => array( 'default', 'one-side', 'compact' ),

								),
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Add categories slug for filters', 'cool-timeline' ),
								'param_name'  => 'filter-categories',
								'value'       => __( '', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'description' => __( ' eg(stories,our-history)', 'cool-timeline' ),
								'dependency'  => array(
									'element' => 'filters',
									'value'   => 'yes',

								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Year Label Setting', 'cool-timeline' ),
								'param_name'  => 'year-label',
								'value'       => array(
									__( 'Show', 'cool-timeline' ) => 'show',
									__( 'Hide', 'cool-timeline' ) => 'hide',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'dependency'  => array(
									'element' => 'layout',
									'value'   => array( 'default', 'horizontal', 'one-side' ),
								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Year Navigation Setting', 'cool-timeline' ),
								'param_name'  => 'year-navigation',
								'value'       => array(
									__( 'Hide', 'cool-timeline' ) => 'hide',
									__( 'Show', 'cool-timeline' ) => 'show',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Year Navigation Position', 'cool-timeline' ),
								'param_name'  => 'navigation-position',
								'value'       => array(
									__( 'Left', 'cool-timeline' ) => 'left',
									__( 'Right', 'cool-timeline' ) => 'right',
									__( 'Bottom', 'cool-timeline' ) => 'bottom',
									__( 'Center', 'cool-timeline' ) => 'center',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'dependency'  => array(
									'element' => 'year-navigation',
									'value'   => array( 'show' ),
								),

							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Year Navigation Style', 'cool-timeline' ),
								'param_name'  => 'navigation-style',
								'value'       => array(
									__( 'Style 1', 'cool-timeline' ) => 'style-1',
									__( 'Style 2', 'cool-timeline' ) => 'style-2',
									__( 'Style 3', 'cool-timeline' ) => 'style-3',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'dependency'  => array(
									'element' => 'year-navigation',
									'value'   => array( 'show' ),
								),
								'description' => __( 'This setting not working in horizontal layout and navigation position bottom', 'cool-timeline' ),

							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Line Filling', 'cool-timeline' ),
								'param_name'  => 'line-filling',
								'value'       => array(
									__( 'True', 'cool-timeline' ) => 'true',
									__( 'False', 'cool-timeline' ) => 'false',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
								// "description" => __( " ",'cool-timeline' ),
								'description' => __( 'Center Line Filling In Timeline Stories. By default Is False', 'cool-timeline' ),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Display read more?', 'cool-timeline' ),
								'param_name'  => 'read-more',
								'value'       => array(
									__( 'Show', 'cool-timeline' ) => 'show',
									__( 'Hide', 'cool-timeline' ) => 'hide',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
							  // "description" => __( " ",'cool-timeline' ),
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Content Length', 'cool-timeline' ),
								'param_name'  => 'content-length',
								'value'       => __( 50, 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'Advance Settings',
								'dependency'  => array(
									'element' => 'read-more',
									'value'   => array( 'show' ),
								),
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Display Post Meta?', 'cool-timeline' ),
								'param_name'  => 'post-meta',
								'value'       => array(
									__( 'Show', 'cool-timeline' ) => 'show',
									__( 'Hide', 'cool-timeline' ) => 'hide',
								),
								'save_always' => true,
								'group'       => 'Advance Settings',
							),

							array(
								'type'        => 'textfield',
								'class'       => '',
								'heading'     => __( 'Meta Key', 'cool-timeline' ),
								'param_name'  => 'meta-key',
								'value'       => __( '', 'cool-timeline' ),
								'save_always' => true,
								'group'       => 'Advance Settings',
							),

							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => __( 'Animations Effect', 'cool-timeline' ),
								'param_name'  => 'animation',
								'value'       => $animation_effects,
								'description' => "Add Animations Effect Inside Timeline. You Can Check Effects Demo From <a  target='_blank' href='http://michalsnik.github.io/aos/'>AOS</a>",
								'save_always' => true,
								'group'       => 'Advance Settings',
								'dependency'  => array(
									'element' => 'layout',
									'value'   => array( 'default' ),
								),
							),
						),
					)
				);

			}
		}
	}
}
