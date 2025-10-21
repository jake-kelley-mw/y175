<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Control core classes for avoid errors
if ( class_exists( 'CTL' ) ) {

	$user_roles = array();
	if ( is_user_logged_in() ) {
		global $wp_roles;
		$user_roles_arr = $wp_roles->get_names();
		$user_roles     = array_filter(
			$user_roles_arr,
			function ( $v, $k ) {
				return ! in_array( $v, array( 'Administrator', 'Subscriber', 'Translator' ) );
			},
			ARRAY_FILTER_USE_BOTH
		);
	}

	// Add admin notice for timeline express migration
function ctl_admin_notice_for_migration() {
    // Check if we're on the Cool Timeline settings page and Get Started tab
	if (!isset($_GET['page']) || $_GET['page'] !== 'cool_timeline_settings') {
	
        return;
    }

    // Check if timeline express is installed and migration is not completed
    if (file_exists(WP_PLUGIN_DIR . '/timeline-express/timeline-express.php')) {
        $migration_completed = get_option('timeline_express_migrated');
        
        // Only show notice if migration is not completed
        if (!$migration_completed) {
            ?>
            <div class="notice ctl_migration notice-info is-dismissible">
                <div class="migration_message_container">
                    <p>
                        <?php echo esc_html__('Timeline Express plugin is installed on your site. To move your announcements into Cool Timeline, you can now start the migration process.', 'cool-timeline'); ?> 
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cool_timeline_settings#tab=migration-settings' ) ); ?>" class="button button-small ctl_migration_btn"><?php echo esc_html__('Start Migration', 'cool-timeline'); ?></a>
                    </p>
                </div>
            </div>
            <?php
        }
    }
}
add_action('admin_notices', 'ctl_admin_notice_for_migration');

	//
	// Set a unique slug-like ID
	$prefix = 'cool_timeline_settings';

	//
	// Create options
	CTL::createOptions(
		$prefix,
		array(
			'framework_title'    => 'Cool Timeline Pro Settings',
			'menu_title'         => 'Timeline Settings',
			'menu_slug'          => 'cool_timeline_settings',
			'menu_type'          => 'submenu',
			'menu_parent'        => 'cool-plugins-timeline-addon',
			'nav'                => 'inline',
			'menu_icon'          => esc_url( CTP_PLUGIN_URL . 'assets/images/cool-timeline-icon.svg' ),
			'menu_position'      => 6,
			'show_reset_section' => false,
			'show_reset_all'     => false,
			'show_bar_menu'      => false,
		)
	);


	

	$fields=array(
				// Create a Fieldset

				array(
					'id'      => 'post_type_slug',
					'type'    => 'text',
					'title'   => __( 'Custom slug of timeline stories', 'cool-timeline1' ),
					'default' => '',
					'desc'    => __( 'Remember to save the permalink again in settings -> Permalinks.', 'cool-timeline1' ),
				),

				array(
					'id'     => 'story_content_settings',
					'type'   => 'fieldset',
					'title'  => 'Story Content',
					'fields' => array(
						array(
							'id'      => 'read_more_lbl',
							'type'    => 'text',
							'title'   => 'Stories Read more Text',
							'default' => '',
						),
						array(
							'id'        => 'story_link_target',
							'type'      => 'radio',
							'title'     => 'Open read more link in ? ',
							'options'   => array(
								'_self'  => 'Same Tab',
								'_blank' => 'new Tab',
							),
							'inline'    => true,
							'()default' => '_self',
						),

						array(
							'id'    => 'default_icon',
							'type'  => 'icon',
							'title' => 'Stories default Icon',
							'std'   => '',

						),

					),
				), // End Fieldset

			// Create a Fieldset
				array(
					'id'     => 'story_media_settings',
					'type'   => 'fieldset',
					'title'  => 'Story Media',
					'fields' => array(

						// Create a Fieldset
						array(
							'id'      => 'stories_images',
							'type'    => 'radio',
							'title'   => 'Stories Images ? ',
							'options' => array(
								'popup'         => 'In Popup( CT Lightbox )',
								'theme-popup'   => 'In Popup( Theme Lightbox )',
								'single'        => 'Story detail link',
								'disable_links' => 'Disable links',
							),
							'inline'  => true,
							'default' => 'popup',
							'desc'    => ' * Choose theme lightbox if your theme supports an image lightbox . ',
						),

						array(
							'id'      => 'ctl_slideshow',
							'type'    => 'radio',
							'title'   => 'Stories Slideshow ? ',
							'options' => array(
								true  => 'Enable',
								false => 'Disable',
							),
							'inline'  => true,
							'default' => 'true',
							'desc'    => ' * Enable or Disable Media slider autoplay . ',
						),

						array(
							'id'         => 'animation_speed',
							'type'       => 'text',
							'title'      => 'Slide Show Speed( for Image Slideshow in Vertical Timeline ) ? ',
							'default'    => '5000',
							'desc'       => 'Enter the speed in milliseconds 1000 = 1 second',
							'dependency' => array( 'ctl_slideshow', ' == ', 'true' ),
						),

					),
				), // End Fieldset

				array(
					'id'      => 'disable_FA',
					'type'    => 'radio',
					'title'   => 'Disable Font Awesome CSS ? ',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
					'inline'  => true,
					'default' => 'no',
					'desc'    => 'Remove Font Awesome icons CSS from all pages',
				),

				array(
					'id'      => 'disable_GF',
					'type'    => 'radio',
					'title'   => 'Disable Google Font ? ',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
					'inline'  => true,
					'default' => 'no',
					'desc'    => 'Remove google fonts CSS from all pages',
				),

				array(
					'id'      => 'disable_vr_slider',
					'type'    => 'radio',
					'title'   => 'Disable Slideshow in Vertical Layout ? ',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
					'inline'  => true,
					'default' => 'no',
					'desc'    => 'Remove Swiper JS and CSS from all pages',
				),

				array(
					'id'          => 'ctl_user_role',
					'type'        => 'select',
					'title'       => 'Timeline User Roles',
					'placeholder' => 'Select User Role',
					'options'     => $user_roles,
				),

				
				array(
					'id'      => 'aria_label_value',
					'type'    => 'select',
					'title'   => 'Heading Level ',
					'options' => array(
						1  => 'One',
						2 => 'Two',
						3   => 'Three',
						4   => 'Four',
						5   => 'Five',
						6   => 'Six',
					),
					'default' => '2',
					'desc'    => 'Choose the ARIA heading level (e.g., 1 for h1, 2 for h2, etc.) to define how this title is announced by screen readers.',
				),
			);


			$review_option_ctl = get_option( 'cpfm_opt_in_choice_cool-timeline' );
					
					if($review_option_ctl){

		$fields[]= array(
			'id'      => 'ctl_cpfm_feedback_data',
			'type'    => 'checkbox',
			'title'   => __('Usage Data Sharing', 'ctp'),
			'default' =>$review_option_ctl === 'yes' ? true : false,
			'desc'    => 'Help us make this plugin more compatible with your site by sharing non-sensitive site data. 
				<a href="#" class="cpfm-see-terms">[See terms]</a>
				<div id="termsBox" style="display: none; margin-top: 10px; ">
					' . sprintf(
        __('Opt in to receive email updates about security improvements, new features, helpful tutorials, and occasional special offers. We\'ll collect: <a href="%s" target="_blank" rel="noopener noreferrer">click here</a>', 'ccpw'),
        esc_url('https://my.coolplugins.net/terms/usage-tracking/')
    ) . '
					<ul class="ctl_data_share" >
						<li>' . esc_html__('1. Your website home URL and WordPress admin email.', 'ccpw') . '</li>
						<li>' . esc_html__('2. To check plugin compatibility, we will collect the following: list of active plugins and themes, server type, MySQL version, WordPress version, memory limit, site language and database prefix.', 'ccpw') . '</li>
					</ul>
				</div>',
		);
	}
		










			CTL::createSection(
				$prefix,
				array(
					'title'  => 'General Settings',
					'fields' =>$fields,
				));
	

	$timeline_express_installed = file_exists(WP_PLUGIN_DIR . '/timeline-express/timeline-express.php');
	
	
	
	if ($timeline_express_installed  && ! get_option( 'timeline_express_migrated')) {
		CTL::createSection(
			$prefix,
			array(
				'title'  => 'Migration Settings',
				'fields' => array(
					array(
						'id'     => 'migration_fieldset',
						'type'   => 'fieldset',
						'title'  => 'Timeline Express Migration',
						'fields' => array(
							array(
								'id'      => 'migrate_stories',
								'type'    => 'content',								
									'content' => '
										<div class="ctl-buttons-migrate">
										<button class="button button-primary ctl-migrate">Migrate Content</button>
										<div class="ctl-progress-bar">
											<div class="ctl-progress-bar-inner">
											</div>
										</div>
									</div>',

								'desc'    => is_plugin_active('timeline-express/timeline-express.php') 
									? 'Timeline Express is active. You can migrate stories now.' 
									: 'Timeline Express is installed but not active. Please activate it to migrate stories.',
								
								
							)
						)
					)
				)
			)
		);
	}


	// Create a section
	CTL::createSection(
		$prefix,
		array(
			'title'  => 'Style Settings',
			'fields' => array(

				array(
					'id'      => 'first_story_position',
					'type'    => 'button_set',
					'title'   => 'Vertical Timeline Stories Starts From',
					'desc'    => 'Not for Compact and Horizontal layout',
					'options' => array(
						'left'  => 'Left',
						'right' => 'Right',
					),
					'default' => 'right',
				),

				array(
					'id'      => 'content_bg_color',
					'type'    => 'color',
					'title'   => 'Story Background Color',
					'default' => '#ffffff',
				),

				array(
					'id'      => 'content_color',
					'type'    => 'color',
					'title'   => 'Content Font Color',
					'default' => '#666666',
				),

				array(
					'id'      => 'title_color',
					'type'    => 'color',
					'title'   => 'Story Title Color',
					'default' => '#ffffff',
				),

				array(
					'id'      => 'circle_border_color',
					'type'    => 'color',
					'title'   => 'Year Background Color',
					'default' => '#025149',
				),

				array(
					'id'      => 'year_label_color',
					'type'    => 'color',
					'title'   => 'Year Label Color',
					'default' => '#ffffff',
				),

				array(
					'id'      => 'line_color',
					'type'    => 'color',
					'title'   => 'Line Color',
					'default' => '#025149',
				),

				array(
					'id'      => 'line_filling_color',
					'type'    => 'color',
					'title'   => 'Line Filling Color',
					'default' => '#38aab7',
				),

				array(
					'id'      => 'first_post',
					'type'    => 'color',
					'title'   => 'First Color',
					'default' => '#02C5BE',
				),

				array(
					'id'      => 'second_post',
					'type'    => 'color',
					'title'   => 'Second Color',
					'default' => '#F12945',
				),
				array(
					'id'      => 'custom_date_color',
					'type'    => 'radio',
					'title'   => 'Enable custom date color',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No(Default style)',
					),
					'inline'  => true,
					'default' => 'no',
				),

				array(
					'id'         => 'ctl_date_color',
					'type'       => 'color',
					'title'      => 'Stories date color',
					'default'    => '#000000',
					'dependency' => array( 'custom_date_color', '==', 'yes' ),
				),

				array(
					'id'       => 'custom_styles',
					'type'     => 'code_editor',
					'title'    => 'Custom Styles',
					'settings' => array(
						'theme' => 'mbo',
						'mode'  => 'css',
					),
				),

			),
		)
	);


	

	// Create a section
	CTL::createSection(
		$prefix,
		array(
			'title'  => 'Typography Setings',
			'fields' => array(


				array(
					'id'         => 'ctl_date_typo',
					'type'       => 'typography',
					'title'      => 'Story Date',
					'default'    => array(
						'font-family' => 'Maven Pro',
						'font-size'   => '21',
						'line-height' => '',
						'unit'        => 'px',
						'type'        => 'google',
						'text-align'  => 'center',
						'font-weight' => '700',
					),
					'text_align' => false,
					'color'      => false,
				),

				array(
					'id'      => 'post_title_typo',
					'type'    => 'typography',
					'title'   => 'Story Title',
					'default' => array(
						'font-family' => 'Maven Pro',
						'font-size'   => '20',
						'line-height' => '',
						'unit'        => 'px',
						'type'        => 'google',
						'font-weight' => '700',
					),
					'color'   => false,
				),

				// A textarea field

				array(
					'id'      => 'post_content_typo',
					'type'    => 'typography',
					'title'   => 'Story Content',
					'default' => array(
						'font-family' => 'Maven Pro',
						'font-size'   => '16',
						'line-height' => '',
						'unit'        => 'px',
						'type'        => 'google',
					),
					'color'   => false,
				),

			),
		)
	);

	// Create a section
	CTL::createSection(
		$prefix,
		array(
			'title'  => 'Get Started',
			'fields' => array(
				array(
					'id'      => 'timeline_display',
					'type'    => 'content',
					'content' => ctl_demo_page_content(),
				),
			),
		)
	);

	// End Section
	
}


function ctl_demo_page_content() {
	$data = '<div class="ctl_started-section">

			<a class="button button-primary" href="https://cooltimeline.com/docs/cool-timeline-pro/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=get_started_pro" target="_blank">' . esc_html__( 'Check Full Documentation', 'cool-timeline' ) . '</a>

            <div class="ctl_step">
                <div class="ctl_step-content">
                    <div class="ctl_steps-title">
                        <h2>' . esc_html__( 'Add License Key', 'cool-timeline' ) . '</h2>
                    </div>
                    <div class="ctl_steps-list">
                        <ol>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' .
							 esc_html__( 'Navigate to the License settings page inside Timeline Addons Section', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' .
							sprintf( esc_html__( 'Enter your %1$slicense key%2$s.', 'cool-timeline' ), '<strong>', '</strong>' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . sprintf( esc_html__( 'Please enter the %1$semail%2$s you used to buy the plugin.', 'cool-timeline' ), '<strong>', '</strong>' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . sprintf( esc_html__( 'Click on the %1$sVerify Key%2$s button.', 'cool-timeline' ), '<strong>', '</strong>' ) . '</span>
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="ctl_video-section">
                    <video class="ctl_timeline-video" controls="">
                        <source src="' . esc_url( 'https://cooltimeline.com/wp-content/uploads/2023/09/Cool-Timeline-Product-Registration-‹-test-—-WordPress.mp4' ) . '" type="video/mp4">
                    </video>
                </div>
            </div>

            <div class="ctl_step ctl_col-rev">
                <div class="ctl_video-section">
                    <video class="ctl_timeline-video" controls="">
                        <source src="' . esc_url( 'https://cooltimeline.com/wp-content/uploads/2023/05/cool_timeline_add_new_story.mp4' ) . '" type="video/mp4">
                    </video>
                </div>
                <div class="ctl_step-content">
                    <div class="ctl_steps-title">
                        <h2>' . esc_html__( 'Add Timeline Stories', 'cool-timeline' ) . '</h2>
                    </div>
                    <div class="ctl_steps-list">
                        <ol>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . sprintf( esc_html__( 'After activating Cool Timeline Pro, you will see a new menu item called %1$sTimeline Stories%2$s in your WordPress Dashboard.', 'cool-timeline' ), '<strong>“', '”</strong>' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . sprintf( esc_html__( 'To create a new story for your timeline, go to “Timeline Addons” and select %1$sAdd New Story%2$s.', 'cool-timeline' ), '<strong>“', '”</strong>' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'You can add details about your story, such as the title, date, image, and description.', 'cool-timeline' ) . '</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="ctl_step">
                <div class="ctl_step-content">
                    <div class="ctl_steps-title">
                        <h2>' . esc_html__( 'Add Timeline Inside The Page', 'cool-timeline' ) . '</h2>
                        <div class="ctl_high-txt">' . esc_html__( 'Using shortcodes', 'cool-timeline' ) . ':</div>
                    </div>

                    <div class="ctl_steps-list">
                        <ol>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'Just Copy Shortcode from the Demo website and Paste it to your Page or Post.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'You can also generate shortcodes using Shortcodes Generator.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'Using Gutenberg Timeline Story Block.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'Using Visual Composer Timeline Stories Addon.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'Using ShortCode in Elementor.', 'cool-timeline' ) . '</span>
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="ctl_video-section">
                    <video class="ctl_timeline-video" controls="">
                        <source src="' . esc_url( 'https://cooltimeline.com/wp-content/uploads/2023/05/how-to-add-ctl-timeline-inside-the-page.mp4' ) . '" type="video/mp4">
                    </video>
                </div>
            </div>

            <div class="ctl_step ctl_col-rev">
                <div class="ctl_video-section">
                    <video class="ctl_timeline-video" controls="">
                        <source src="' . esc_url( 'https://cooltimeline.com/wp-content/uploads/2023/05/ctl-timeline-settings.mp4' ) . '" type="video/mp4">
                    </video>
                </div>
                <div class="ctl_step-content">
                    <div class="ctl_steps-title">
                        <h2>' . esc_html__( 'Configure Timeline Stories', 'cool-timeline' ) . '</h2>
                        <div class="ctl_high-txt">' . esc_html__( 'Settings (Layout / Design etc.)', 'cool-timeline' ) . ':</div>
                    </div>

                    <div class="ctl_steps-list">
                        <ol>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'The Cool Timeline Pro setting tab is located on the right-hand side of the WordPress editor.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'It allows you to adjust attribute values inside your shortcodes.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'You can use it for customization of various options, such as Layout, Design, Category, Stories Per Page, and Order.', 'cool-timeline' ) . '</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="ctl_step ctl_row-rev">
                <div class="ctl_video-section">
                    <video class="ctl_timeline-video" controls="">
                        <source src="' . esc_url( 'https://cooltimeline.com/wp-content/uploads/2023/09/post-timeline.mp4' ) . '" type="video/mp4">
                    </video>
                </div>
                <div class="ctl_step-content">
                    <div class="ctl_steps-title">
                        <h2>' . esc_html__( 'Configure Post Timeline', 'cool-timeline' ) . '</h2>
                        <div class="ctl_high-txt">' . esc_html__( 'Settings (Post / pages etc.)', 'cool-timeline' ) . ':</div> 
                    </div>

                    <div class="ctl_steps-list">
                        <ol>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'The Post Timeline block setting tab is located on the right-hand side of the WordPress editor.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'You can create a timeline based on Posts and Pages.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'Allows you to display custom post type using post timeline block.', 'cool-timeline' ) . '</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="ctl_step ctl_col-rev">
                <div class="ctl_video-section">
                    <video class="ctl_timeline-video" controls="">
                        <source src="' . esc_url( 'https://cooltimeline.com/wp-content/uploads/2023/09/Untitled-design-3-2.mp4' ) . '" type="video/mp4">
                    </video>
                </div>
                <div class="ctl_step-content">
                    <div class="ctl_steps-title">
                        <h2>' . esc_html__( 'Configure Timeline Block', 'cool-timeline' ) . '</h2>
                    </div>

                    <div class="ctl_steps-list">
                        <ol>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'You can use the Cool Timeline Block to instantly edit your timeline stories with the power of Gutenberg.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'The timeline block consists of two elements: parent and child.', 'cool-timeline' ) . '</span>
                            </li>
                            <li class="ctl_step-data">
                                <span class="ctl_list-text">' . esc_html__( 'You can customize several options, including colors, layout, design, and media.', 'cool-timeline' ) . '</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
			
        </div>';
		
	return $data;
	
}

