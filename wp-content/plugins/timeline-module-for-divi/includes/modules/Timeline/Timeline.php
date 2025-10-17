<?php
/**
 *  This is the primary divi module 'Timeline'.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

class TMDIVI_Timeline extends TMDIVI_Builder_Module{
    // Module slug (also used as shortcode tag)
    public $slug = 'tmdivi_timeline';

    public $icon_path;

    public static $timeline_order = 1;

    // Full Visual Builder support
    public $vb_support = 'on';

    // Module item's slug
    public $child_slug = 'tmdivi_timeline_story';

    /**
     * Module properties initialization
     *
     * @since 1.0.0
     */
    public function init(){

        // Module name
        $this->name = esc_html__('Timeline', 'timeline-module-for-divi');

        // Replacing `Add New Item` to `Add New `Story`;
        $this->child_item_text = esc_html__('Story', 'timeline-module-for-divi');

        // Module Icon
        // Load customized svg icon and use it on builder as module icon. If you don't have svg icon, you can use
        // $this->icon for using etbuilder font-icon. (See CustomCta / DICM_CTA class)
        // $this->icon_path = TMDIVI_URL . 'assets/image/Timeline-logo.svg';

        // Toggle settings
        $this->settings_modal_toggles = array(
            'general' => array(
                'toggles' => array(
                    'main_content' => array(
                        'title' => esc_html__('Timeline Settings', 'timeline-module-for-divi')
                    ),
                    'main_content_background' => array(
                        'title' => esc_html__('Background', 'timeline-module-for-divi')
                    ),
                ),
            ),
            'advanced' => [
                'toggles' => [
                    'layout_setting' => [
                        'title' => esc_html__('Timeline Layouts', 'timeline-module-for-divi'),
                    ],
                    'label_settings' => [
                        'title' => esc_html__('Labels Settings', 'timeline-module-for-divi'),
                    ],
                    'story_settings' => [
                        'title' => esc_html__('Container Settings', 'timeline-module-for-divi'),
                    ],
                    'heading_settings' => [
                        'title' => esc_html__('Title Settings', 'timeline-module-for-divi'),
                    ],
                    'description_settings' => [
                        'title' => esc_html__('Description Settings', 'timeline-module-for-divi'),
                    ],
                    'line_settings' => [
                        'title' => esc_html__('Line Settings', 'timeline-module-for-divi'),
                    ],
                    'icon_settings' => [
                        'title' => esc_html__('Icon Settings', 'timeline-module-for-divi'),
                    ],
                    'spacing_settings' => [
                        'title' => esc_html__('Spacing And Position Settings', 'timeline-module-for-divi'),
                    ],
                ],
            ],
        );

    }

    /**
     * Module's specific fields
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_fields(){
        return array(
            'timeline_layout' => array(
                'label' => esc_html__('Timeline Layout', 'timeline-module-for-divi'),
                'type' => 'select',
                'option_category' => 'basic_option',
			    'default'              => 'both-side',
                'options'              => array(
                    'both-side'           => esc_html__('Both Side', 'timeline-module-for-divi'),
                    'one-side-left'        => esc_html__('One Side (left)', 'timeline-module-for-divi'),
                    'one-side-right'        => esc_html__('One Side (right)', 'timeline-module-for-divi'),
                    // 'horizontal'        => esc_html__('Horizontal', 'timeline-module-for-divi'),
                ),
                'toggle_slug' => 'layout_setting',
                'tab_slug' => 'advanced',
                ),
            'background_main' => array(
                'label' => esc_html__('Background', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'option_category' => 'basic_option',
                'toggle_slug' => 'main_content_background'
                ),
            'spacing_positioning_tabs' => array(
                'label' => esc_html__('Spacing And Postion Settings', 'timeline-module-for-divi'),
                'tab_slug' => 'advanced',
                'toggle_slug' => 'spacing_settings',
                'attr_suffix' => '',
                'type' => 'composite',
                'composite_type' => 'default',
                'composite_structure' => array(
                    'tab_1'  => array(
                        'label' => esc_html('Labels Spacing', 'timeline-module-for-divi'),
                        'controls' => array(
                            'labels_position' => array(
                                'label' => esc_html__('Labels/Icons Position', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '0',
                                'range_settings' => array(
                                    'min' => '0',
                                    'max' => '86',
                                    'step' => '2',
                                    )
                            ),
                            'labels_spacing_bottom' => array(
                                'label' => esc_html__('Gap Between Labels', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '0px',
                                'range_settings' => array(
                                    'min' => '8px',
                                    'max' => '86px',
                                    'step' => '2px',
                                    )
                            ),
                        )
                    ),
                    'tab_2'  => array(
                        'label' => esc_html('Story Spacing', 'timeline-module-for-divi'),
                        'controls' => array(
                            'story_spacing_top' => array(
                                'label' => esc_html__('Spacing Top', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '0px',
                                'range_settings' => array(
                                    'min' => '0px',
                                    'max' => '86px',
                                    'step' => '2px',
                                    )
                            ),
                            'story_spacing_bottom' => array(
                                'label' => esc_html__('Spacing Bottom', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '0px',
                                'range_settings' => array(
                                    'min' => '0px',
                                    'max' => '86px',
                                    'step' => '2px',
                                    )
                            ),
                        )
                    ),
                ),
            ),
            'timeline_color' => array(
                'label' => esc_html__('Line Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'description' => esc_html__('Timeline color.', 'timeline-module-for-divi'),
                'toggle_slug' => 'line_settings',
                'tab_slug' => 'advanced',
            ),
            'timeline_line_width' => array(
                'label' => esc_html__('Line Width', 'timeline-module-for-divi'),
                'type' => 'range',
                'default' => '4px',
                'range_settings' => array(
                    'min' => '1',
                    'max' => '10',
                    'step' => '1',
                ),
                'toggle_slug' => 'line_settings',
                'tab_slug' => 'advanced',
            ),
            'timeline_fill_setting' => array(
                'label' => esc_html__('Show Line Filling', 'timeline-module-for-divi'),
                'type' => 'yes_no_button',
                'options' => array(
                    'on' => esc_html__('Show', 'timeline-module-for-divi'),
                    'off' => esc_html__('Hide', 'timeline-module-for-divi'),
                ),
                'toggle_slug' => 'line_settings',
                'tab_slug' => 'advanced',
            ),
            'timeline_fill_color' => array(
                'label' => esc_html__('Line fill Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'description' => esc_html__('Timeline fill effect color.', 'timeline-module-for-divi'),
                'toggle_slug' => 'line_settings',
                'tab_slug' => 'advanced',
                'show_if'  => array(
                    'timeline_fill_setting' => 'on'
                )
            ),
            'icon_background_color' => array(
                'label' => esc_html__('Icon / Dot Background Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'description' => esc_html__('Icon / Dot Background Color.', 'timeline-module-for-divi'),
                'toggle_slug' => 'icon_settings',
                'tab_slug' => 'advanced',
            ),
            'icon_color' => array(
                'label' => esc_html__('Icon / Text Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'description' => esc_html__('Icon / Text Color.', 'timeline-module-for-divi'),
                'toggle_slug' => 'icon_settings',
                'tab_slug' => 'advanced',
            ),
            'label_style_tabs' => array(
                'label' => esc_html__('Label Styling', 'timeline-module-for-divi'),
                'tab_slug' => 'advanced',
                'toggle_slug' => 'label_settings',
                'attr_suffix' => '',
                'type' => 'composite',
                'composite_type' => 'default',
                'composite_structure' => array(
                    'tab_1' => array(
                        'label' => esc_html('Date Label', 'timeline-module-for-divi'),
                        'controls' => array(
                            'label_font' => array(
                                'label' => esc_html__('Date Label Font', 'timeline-module-for-divi'),
                                'type' => 'font',
                            ),
                            'label_font_color' => array(
                                'label' => esc_html__('Date Label Font Color', 'timeline-module-for-divi'),
                                'type' => 'color',
                            ),
                            'label_font_size' => array(
                                'label' => esc_html__('Date Label Font Size', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '24px',
                                'range_settings' => array(
                                    'min' => '8px',
                                    'max' => '86px',
                                    'step' => '2px',
                                ),
                            ),
                        ),
                    ),
                    'tab_2' => array(
                        'label' => esc_html('Sub Label', 'timeline-module-for-divi'),
                        'controls' => array(
                            'sub_label_font' => array(
                                'label' => esc_html__('Sub Label Font', 'timeline-module-for-divi'),
                                'type' => 'font',
                            ),
                            'sub_label_font_color' => array(
                                'label' => esc_html__('Sub Label Font Color', 'timeline-module-for-divi'),
                                'type' => 'color',
                            ),
                            'sub_label_font_size' => array(
                                'label' => esc_html__('Sub Label Font Size', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '16px',
                                'range_settings' => array(
                                    'min' => '8px',
                                    'max' => '86px',
                                    'step' => '2px',
                                ),
                            ),
                        ),
                    ),
                    'tab_3' => array(
                        'label' => esc_html('Year Label (on-line)', 'timeline-module-for-divi'),
                        'controls' => array(
                            'year_label_font' => array(
                                'label' => esc_html__('Label / Year Font', 'timeline-module-for-divi'),
                                'type' => 'font',
                            ),
                            'year_label_font_color' => array(
                                'label' => esc_html__('Year Label Font Color', 'timeline-module-for-divi'),
                                'type' => 'color',
                            ),
                            'year_label_bg_color' => array(
                                'label' => esc_html__('Year Label Background Color', 'timeline-module-for-divi'),
                                'type' => 'color',
                            ),
                            'year_label_box_size' => array(
                                'label' => esc_html__('Year Label Box Size', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '83px',
                                'range_settings' => array(
                                    'min' => '36px',
                                    'max' => '128px',
                                    'step' => '1px',
                                ),
                            ),
                            'year_label_font_size' => array(
                                'label' => esc_html__('Year Label Font Size', 'timeline-module-for-divi'),
                                'type' => 'range',
                                'default' => '24px',
                                'range_settings' => array(
                                    'min' => '36px',
                                    'max' => '128px',
                                    'step' => '1px',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'story_background_color' => array(
                'label' => esc_html__('Story Background Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'default' => 'white',
                'description' => esc_html__('Stroy Background Color.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_settings',
                'tab_slug' => 'advanced',
            ),
            'story_padding' => array(
                'label' => esc_html__('Story Padding', 'timeline-module-for-divi'),
                'type' => 'custom_padding',
                'description' => esc_html__('Stroy Padding.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_settings',
                'tab_slug' => 'advanced',
            ),
            'heading_font_color' => array(
                'label' => esc_html__('Title Font Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'heading_settings',
            ),
            'heading_background_color' => array(
                'label' => esc_html__('Title Background Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'heading_settings',
            ),
            'heading_text_align' => array(
                'label' => esc_html__('Title Align', 'timeline-module-for-divi'),
                'type' => 'text_align',
                'option_category' => 'layout',
                'options'         => et_builder_get_text_orientation_options(array('justified')),
                'options_icon'    => 'text_align',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'heading_settings',
            ),
            'heading_text_size' => array(
                'label' => esc_html__('Title Text Size', 'timeline-module-for-divi'),
                'type' => 'range',
                'default' => '24px',
                'range_settings' => array(
                    'min' => '8px',
                    'max' => '86px',
                    'step' => '2px',
                ),
                'tab_slug' => 'advanced',
                'toggle_slug' => 'heading_settings',
            ),
            'heading_line_height' => array(
                'label' => esc_html__('Title Line Height', 'timeline-module-for-divi'),
                'type' => 'range',
                'default' => '24px',
                'range_settings' => array(
                    'min' => '8px',
                    'max' => '86px',
                    'step' => '2px',
                ),
                'tab_slug' => 'advanced',
                'toggle_slug' => 'heading_settings',
            ),
            'heading_custom_padding' => array(
                'label' => esc_html__('Title Custom Padding', 'timeline-module-for-divi'),
                'type' => 'custom_padding',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'heading_settings',
            ),
            'description_font_color' => array(
                'label' => esc_html__('Description Font Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'description_settings',
            ),
            'description_background_color' => array(
                'label' => esc_html__('Description Background Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'description_settings',
            ),
            'description_text_align' => array(
                'label' => esc_html__('Description Align', 'timeline-module-for-divi'),
                'type' => 'text_align',
                'option_category' => 'layout',
                'options'         => et_builder_get_text_orientation_options(array('justified')),
                'options_icon'    => 'text_align',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'description_settings',
            ),
            'description_text_size' => array(
                'label' => esc_html__('Description Text Size', 'timeline-module-for-divi'),
                'type' => 'range',
                'default' => '20px',
                'range_settings' => array(
                    'min' => '8px',
                    'max' => '86px',
                    'step' => '2px',
                ),
                'tab_slug' => 'advanced',
                'toggle_slug' => 'description_settings',
            ),
            'description_line_height' => array(
                'label' => esc_html__('Description Line Height', 'timeline-module-for-divi'),
                'type' => 'range',
                'default' => '24px',
                'range_settings' => array(
                    'min' => '8px',
                    'max' => '86px',
                    'step' => '2px',
                ),
                'tab_slug' => 'advanced',
                'toggle_slug' => 'description_settings',
            ),
            'description_custom_padding' => array(
                'label' => esc_html__('Description Custom Padding', 'timeline-module-for-divi'),
                'type' => 'custom_padding',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'description_settings',
            ),
        );
    }

    public function get_advanced_fields_config(){

        $advanced_fields                = array();
        $advanced_fields['background'] = false;
        $advanced_fields['animation'] = false;
        $advanced_fields['text'] = false;
        $advanced_fields['margin_padding'] = false;
        $advanced_fields['transform'] = false;
        $advanced_fields['filter'] = false;
        $advanced_fields['box_shadow'] = false;

        $advanced_fields['borders']['story_settings'] = array(
			'label_prefix' => esc_html__('Story', 'timeline-module-for-divi'),
			'css'          => array(
                'main'      => array(
					// 'border_radii'  => '%%order_class%% .tmdivi-story .tmdivi-content, %%order_class%% .tmdivi-arrow, .tmdivi-story.tmdivi-story-left .tmdivi-content',
					'border_radii'  => '%%order_class%% .tmdivi-story .tmdivi-content, 
                    %%order_class%% .tmdivi-story.tmdivi-story-left .tmdivi-content',
					// 'border_styles' => '%%order_class%% .tmdivi-story .tmdivi-content, %%order_class%% .tmdivi-arrow, .tmdivi-story.tmdivi-story-left .tmdivi-content',
					'border_styles' => '%%order_class%% .tmdivi-story .tmdivi-content, 
                    %%order_class%% .tmdivi-story.tmdivi-story-left .tmdivi-content',
				),
				// 'important' => 'all',
			),
			'tab_slug'     => 'advanced',
            'toggle_slug' => 'story_settings',
		);

        $advanced_fields['fonts']['heading_settings'] = array(
			'label_prefix' => esc_html__('Title Font', 'timeline-module-for-divi'),
			'css'          => array(
                'main'      => '%%order_class%% .tmdivi-wrapper .tmdivi-title',
			),
			'tab_slug'     => 'advanced',
            'toggle_slug' => 'heading_settings',
            'hide_text_align' => true,
			'hide_line_height' => true,
			'hide_letter_spacing' => true,
			'hide_font_size' => true,
			'hide_text_color' => true,
			'hide_text_shadow' => true,
		);

        $advanced_fields['fonts']['description_settings'] = array(
			'label_prefix' => esc_html__('Description Font', 'timeline-module-for-divi'),
			'css'          => array(
                'main'      => '%%order_class%% .tmdivi-wrapper .tmdivi-description',
			),
			'tab_slug'     => 'advanced',
            'toggle_slug' => 'description_settings',
			'hide_text_align' => true,
			'hide_line_height' => true,
			'hide_letter_spacing' => true,
			'hide_font_size' => true,
			'hide_text_color' => true,
			'hide_text_shadow' => true,
		);

		return $advanced_fields;
    }
    /**
     * Render module output
     *
     * @since 1.0.0
     *
     * @param array  $attrs       List of unprocessed attributes
     * @param string $content     Content being processed
     * @param string $render_slug Slug of module that is used for rendering output
     *
     * @return string module's rendered output
     */
    public function render($attrs, $content = null, $render_slug = ""){
        self::$timeline_order++;

        $props = $this->props;
        TMDIVI_ModulesHelper::StaticCssLoader($props, $render_slug);

        wp_enqueue_script('tmdivi-vertical');
        
        $timeline_layout = sanitize_text_field($props['timeline_layout']);
        $timeline_fill_setting = sanitize_text_field($props['timeline_fill_setting']);

        $instanceNum = self::$timeline_order - 2;
        $wrapper_id = "tmdivi-wrapper-$instanceNum";    

    switch($timeline_layout){
        case "one-side-left":
            $timelineLayout = "tmdivi-vertical-right";
            break;
        case "one-side-right":
            $timelineLayout = "tmdivi-vertical-left";
            break;
        default:
            $timelineLayout = "both-side";
    }
        // Render module content
        $output = sprintf(
            ' <div id="%4$s" class="tmdivi-vertical tmdivi-wrapper %3$s style-1 tmdivi-bg-simple" data-line-filling="%2$s">
            <div class="tmdivi-start"></div>
            <div class="tmdivi-line tmdivi-timeline"> %1$s
            <div class="tmdivi-inner-line" style="height:0px" data-line-fill="%2$s"></div>
            </div>
            <div class="tmdivi-end"></div>
            </div>',
            et_core_sanitized_previously($this->content),
            ($timeline_fill_setting === "on")? 'true':'false',
            esc_attr($timelineLayout),
            $wrapper_id
        );

        if($props['content'] !== ""){
            return $this->_render_module_wrapper($output, $render_slug);
        }
    }

}

new TMDIVI_Timeline;
