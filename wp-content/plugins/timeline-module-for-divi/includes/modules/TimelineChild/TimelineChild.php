<?php
/**
 *  This class contains the repeated child (Timeline story) for the module.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

class TMDIVI_TimelineChild extends TMDIVI_Builder_Module{
    private static $story_order = 0;


    protected $options_tabs;
    // Module slug (also used as shortcode tag)
    public $slug = 'tmdivi_timeline_story';

    // Module item has to use `child` as its type property
    public $type = 'child';

    // Module item's attribute that will be used for module item label on modal
    public $child_title_var = 'story_title';


    // Full Visual Builder support
    public $vb_support = 'on';

    /**
     * Module properties initialization
     *
     * @since 1.0.0
     *
     */
    public function init(){
        // Module name
        $this->name = esc_html__('Timeline Story', 'timeline-module-for-divi');

        // Default label for module item. Basically if $this->child_title_var and $this->child_title_fallback_var
        // attributes are empty, this default text will be used instead as item label
        $this->advanced_setting_title_text = esc_html__('Timeline Story', 'timeline-module-for-divi');

        // Module item's modal title
        $this->settings_text = esc_html__('Timeline Story Settings', 'timeline-module-for-divi');

        $this->options_tabs = array(
            'demo' => array(
                'name' => esc_html__('Demo', 'timeline-module-for-divi'),
            ),
        );


        // Toggle settings
        // Toggles are grouped into array of tab name > toggles > toggle definition
        $this->settings_modal_toggles = array(
            // Content tab's slug is "general"
            'general' => array(
                'toggles' => array(
                    'story_content' => esc_html('Story Content', 'timeline-module-for-divi'),
                    'story_label' => esc_html('Story Label', 'timeline-module-for-divi'),
                    'story_media' => esc_html('Story Media', 'timeline-module-for-divi'),
                    'story_icon' => esc_html('Story Icon', 'timeline-module-for-divi'),
                ),
            ),
            'advanced' => array(
                'toggles' => array(
                    'story' => array(
                        'title' => esc_html__('Style', 'timeline-module-for-divi'),
                    ),
                    'texts' => array(
                        'title' => esc_html__('Texts', 'timeline-module-for-divi'),
                        'tabbed_subtoggles' => true,
                        'sub_toggles' => array(
                            'title_tab' => array(
                                'name' => esc_html__('Title', 'timeline-module-for-divi'),
                            ),
                            'subtitle_tab' => array(
                                'name' => esc_html__('Subtitle', 'timeline-module-for-divi'),
                            ),
                        ),
                    ),
                    'borders' => array(
                        'title' => esc_html__('Border', 'timeline-module-for-divi'),
                    ),
                ),
            ),
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
            'show_label' => array(
                'label' => esc_html__('Display Label / Year', 'timeline-module-for-divi'),
                'type' => 'yes_no_button',
                'options' => array(
                    'on' => esc_html__('Show', 'timeline-module-for-divi'),
                    'off' => esc_html__('Hide', 'timeline-module-for-divi'),
                ),
                'description' => esc_html__('Enter Label / Year.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_label',
                'tab_slug' => 'general',
            ),
            'label_text' => array(
                'label' => esc_html__('Label / Year', 'timeline-module-for-divi'),
                'type' => 'text',
                'description' => esc_html__('Enter Label / Year.', 'timeline-module-for-divi'),
                'show_if' => array(
                    'show_label' => 'on',
                ),
                'toggle_slug' => 'story_label',
                'tab_slug' => 'general',
            ),
            'label_date' => array(
                'label' => esc_html__('Label / Date', 'timeline-module-for-divi'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'description' => esc_html__('Enter Label / Date.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_label',
                'tab_slug' => 'general',
            ),
            'sub_label' => array(
                'label' => esc_html__('Sub Label', 'timeline-module-for-divi'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'description' => esc_html__('Enter Sub Label.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_label',
                'tab_slug' => 'general',
            ),
            'show_story_icon' => array(
                'label' => esc_html__('Display Story Icon', 'timeline-module-for-divi'),
                'type' => 'yes_no_button',
                'options' => array(
                    'on' => esc_html__('Show Icon', 'timeline-module-for-divi'),
                    'off' => esc_html__('Hide Icon', 'timeline-module-for-divi'),
                ),
                'default' => 'off',
                'description' => esc_html__('Show custom milestone icon with story.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_icon',
                'tab_slug' => 'general',
            ),
            'story_icons' => array(
                'label' => esc_html__('Select Icon', 'timeline-module-for-divi'),
                'type' => 'et_font_icon_select',
                'renderer' => 'select_icon',
                'renderer_with_field' => true,
                'show_if' => array(
                    'show_story_icon' => 'on',
                ),
                'toggle_slug' => 'story_icon',
                'tab_slug' => 'general',
            ),
            'story_title' => array(
                'label' => esc_html__('Story Title', 'timeline-module-for-divi'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'description' => esc_html__('Story Title.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_content',
                'tab_slug' => 'general',
            ),
            'media' => array(
                'label' => esc_html__('Upload', 'timeline-module-for-divi'),
                'type' => 'upload',
                'upload_button_text' => esc_html__('Upload an image', 'timeline-module-for-divi'),
                'choose_text' => esc_html__('Choose an Image', 'timeline-module-for-divi'),
                'update_text' => esc_html__('Set As Image', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_media',
                'tab_slug' => 'general',
            ),
            'media_alt_tag' => array(
                'label' => esc_html__('Alt Text', 'timeline-module-for-divi'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'description' => esc_html__('Image Alt Text.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_media',
                'tab_slug' => 'general',
            ),
            'content' => array(
                'label' => esc_html__('Content', 'timeline-module-for-divi'),
                'type' => 'tiny_mce',
                'option_category' => 'basic_option',
                'description' => esc_html__('Enter description for the story.', 'timeline-module-for-divi'),
                'toggle_slug' => 'story_content',
                'tab_slug' => 'general',
            ),
            'child_story_background_color' => array(
                'label' => esc_html__('Story Background Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'option_category' => 'basic_option',
                'toggle_slug' => 'story',
                'tab_slug' => 'advanced',
            ),
            'child_story_heading_color' => array(
                'label' => esc_html__('Story Title Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'option_category' => 'basic_option',
                'toggle_slug' => 'story',
                'tab_slug' => 'advanced',
            ),
            'child_story_description_color' => array(
                'label' => esc_html__('Story Description Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'option_category' => 'basic_option',
                'toggle_slug' => 'story',
                'tab_slug' => 'advanced',
            ),
            'child_story_label_color' => array(
                'label' => esc_html__('Story Label Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'option_category' => 'basic_option',
                'toggle_slug' => 'story',
                'tab_slug' => 'advanced',
            ),
            'child_story_sub_label_color' => array(
                'label' => esc_html__('Story Sub Label Color', 'timeline-module-for-divi'),
                'type' => 'color-alpha',
                'option_category' => 'basic_option',
                'toggle_slug' => 'story',
                'tab_slug' => 'advanced',
            ),
        );
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
    public function render($attrs, $content = null, $render_slug = "") {

        static $last_timeline_order = null;

        if ($last_timeline_order !== TMDIVI_Timeline::$timeline_order) {
            // New timeline detected, reset story order
            self::$story_order = 1;
            $last_timeline_order = TMDIVI_Timeline::$timeline_order;
        } else {
            // Increment the story order within the same timeline
            self::$story_order++;
        }    

        // Sanitize all attributes
        $parent_module = self::get_parent_modules('page')['tmdivi_timeline'];
        $timeline_layout = sanitize_text_field($parent_module->shortcode_atts['timeline_layout']);

        $props = $this->props;

        $this->generate_styles(
            array(
                'utility_arg' => 'icon_font_family',
                'render_slug' => $render_slug,
                'base_attr_name' => 'story_icons',
                'important' => true,
                'selector' => '%%order_class%% .tmdivi-story .tmdivi-icon .et-tmdivi-icon',
                'processor' => array(
                    'ET_Builder_Module_Helper_Style_Processor',
                    'process_extended_icon',
                ),
            )
        );

        TMDIVI_ModulesHelper::ChildStaticCssLoader($props, $render_slug);

        // Escape and sanitize
        $title = sanitize_text_field($props['label_date']);
        $subtitle = sanitize_text_field($props['sub_label']);
        $story_title = sanitize_text_field($props['story_title']);
        $story_icon = $this->render_icons(sanitize_text_field($props['story_icons']));
        $image = $props['media'] != '' ? '<img decoding="async" src="' . esc_url($props['media']) . '" alt="'. esc_attr($props['media_alt_tag']) .'" />' : '';
        $icon_class = $story_icon == '' ? 'tmdivi-icondot' : 'tmdivi-icon';

        $container_class = "";
        if($timeline_layout === "both-side"){
            $container_class = self::$story_order % 2 ? "tmdivi-story-right" : "tmdivi-story-left";
        }

        $year_label = $this->year_label(
            sanitize_text_field($props['show_label']),
            sanitize_text_field($props['label_text'])
        );
   
        // Render module content
    $story_container = $this->render_story_container(
        $this->render_story_labels($title, $subtitle, $icon_class, $story_icon), 
        $this->render_story_content($story_title, $image, et_core_sanitized_previously($this->content), $title, $subtitle),
        $container_class
    );
        
    return $year_label . $story_container;
    }
        
    public function render_story_container($story_labels, $story_content, $container_class) {
        if ($story_content !== "") {
            $html = '<div class="tmdivi-story tmdivi-story-icon ' . esc_attr($container_class) . '">
                        ' . $story_labels . '
                        ' . $story_content . '
                    </div>';
        } else {
            $html = "";
        }
        return $html;
    }    

    public function render_story_labels($label_big, $label_small, $icon_class, $story_icon) {
        $big_label_html = ($label_big !== "") ? '<div class="tmdivi-label-big">' . esc_html($label_big) . '</div>' : "";
        $small_label_html = ($label_small !== "") ? '<div class="tmdivi-label-small">' . esc_html($label_small) . '</div>' : "";
        $story_icon_html = '<div class="' . esc_attr($icon_class) . '">' . $story_icon . '</div>';

        $content_html =  '<div class="tmdivi-labels">' . $big_label_html . $small_label_html . '</div>' . $story_icon_html;

        return $content_html;
    }

    public function render_story_content($story_title, $image, $content, $title, $subtitle) {
        $content_html = "";
        if (!($story_title === "" && $image === "" && $content === "" && $title === "" && $subtitle === "")) {
            $title_html = ($story_title !== "") ? '<div class="tmdivi-title">' . esc_html($story_title) . '</div>' : "";
            $image_html = ($image !== "") ? '<div class="tmdivi-media full">' . $image . '</div>' : "";
            $description_html = ($content !== "") ? '<div class="tmdivi-description">' . $content . '</div>' : "";
            $content_html = '<div class="tmdivi-arrow"></div>
                            <div class="tmdivi-content">
                                ' . $title_html . $image_html . $description_html . '
                            </div>';
        }
        return $content_html;
    }

    public function render_icons($icon = '') {
        $parts = explode( '||', $icon );
        if(isset($parts[1]) && $parts[1] === 'fa'){
            if (!wp_style_is('tmdivi-fontawesome-css', 'enqueued')) {
                wp_enqueue_style('tmdivi-fontawesome-css');
            }
        }

        if ($this->props['show_story_icon'] === 'on' && $icon === "") {
            $icon = "}||divi||400";
        }
        if ($icon == '') {
            $render_html = '';
        } else {
            $render_html = $this->render_prop($icon, 'select_icons');
        }
        return $render_html;
    }

    protected function year_label($enabled, $label) {
        if ($label == '' || $enabled != 'on') {
            return '';
        }
        $html = '<div class="tmdivi-year tmdivi-year-container">
                    <div class="tmdivi-year-label tmdivi-year-text">' . esc_html($label) . '</div>
                </div>';
        return $html;
    }

    // protected function _render_module_wrapper( $output = '', $render_slug = '' ) {
    //     return $output;
    // } 
}

new TMDIVI_TimelineChild;
