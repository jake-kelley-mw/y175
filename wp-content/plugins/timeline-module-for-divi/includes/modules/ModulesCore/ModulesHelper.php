<?php

if( !defined('ABSPATH') ){
    exit;
}

class TMDIVI_ModulesHelper{
    public function __construct(){

    }

    public static function enqueue_google_font($font_family) {
        $font_parts = explode('|', $font_family);
        $font_family_name = $font_parts[0];
        if ($font_family_name) {
            wp_enqueue_style('tmdivi-gfonts-' . $font_family_name, "https://fonts.googleapis.com/css2?family=$font_family_name&display=swap", array(),TMDIVI_V, null);
        }
    }

    public static function extractFontProperties($fontString) {
        $fontParts = explode('|', $fontString);
        $fontFamily = $fontParts[0];
        $fontWeight = !empty($fontParts[1]) ? $fontParts[1] : '';
        $fontStyle = !empty($fontParts[2]) ? "italic" : 'normal'; 
    
        // Determine text transform
        if (!empty($fontParts[3])) {
            $textTransform = "uppercase";
        } elseif (!empty($fontParts[5])) {
            $textTransform = "capitalize";
        } else {
            $textTransform = "none";
        }
    
        // Determine text decoration
        if (!empty($fontParts[4]) && !empty($fontParts[6])) {
            $textDecoration = "line-through";
        } elseif (!empty($fontParts[4])) {
            $textDecoration = "underline";
        } elseif (!empty($fontParts[6])) {
            $textDecoration = "line-through";
        } else {
            $textDecoration = "none";
        }
    
        $textDecorationLineColor = (!empty($fontParts[7])) ? $fontParts[7] : ''; 
        $textDecorationStyle = (!empty($fontParts[8])) ? $fontParts[8] : ''; 

        return array(
            'fontFamily' => $fontFamily,
            'fontWeight' => $fontWeight,
            'fontStyle' => $fontStyle,
            'textTransform' => $textTransform,
            'textDecoration' => $textDecoration,
            'textDecorationLineColor' => $textDecorationLineColor,
            'textDecorationStyle' => $textDecorationStyle,
        );
    }

    public static function StaticCssLoader($props, $render_slug){
        // Module specific props added on $this->get_fields()
        $story_padding = $props['story_padding'];
        $story_background_color = $props['story_background_color'];
        $timeline_color = $props['timeline_color'];
        $timeline_fill_setting = $props['timeline_fill_setting'];
        $timeline_fill_color = $props['timeline_fill_color'];
        $icon_bg_color = $props['icon_background_color'];
        $icon_color = $props['icon_color'];
        $label_font = $props['label_font'];
        $label_fsize = $props['label_font_size'];
        $label_fcolor = $props['label_font_color'];
        $sub_label_font = $props['sub_label_font'];
        $sub_label_fsize = $props['sub_label_font_size'];
        $sub_label_fcolor = $props['sub_label_font_color'];
        $year_label_font = $props['year_label_font'];
        $year_label_color = $props['year_label_font_color'];
        $year_label_boxsize = $props['year_label_box_size'];
        $year_label_font_size = $props['year_label_font_size'];
        $year_label_bgcolor = $props['year_label_bg_color'];
        $heading_text_size = $props['heading_text_size'];
        $heading_line_height = $props['heading_line_height'];
        $heading_font_color = $props['heading_font_color'];
        $heading_background_color = $props['heading_background_color'];
        $heading_text_align = $props['heading_text_align'];
        $heading_custom_padding = $props['heading_custom_padding'];
        $description_text_size = $props['description_text_size'];
        $description_line_height = $props['description_line_height'];
        $description_font_color = $props['description_font_color'];
        $description_background_color = $props['description_background_color'];
        $description_text_align = $props['description_text_align'];
        $description_custom_padding = $props['description_custom_padding'];
        $labels_position = $props['labels_position'];
        $labels_spacing_bottom = $props['labels_spacing_bottom'];
        $story_spacing_top = $props['story_spacing_top'];
        $story_spacing_bottom = $props['story_spacing_bottom'];
        $background_main = $props['background_main'];
        $timeline_line_width = $props['timeline_line_width'];
        $heading_settings_font = $props['heading_settings_font'];

        if ($timeline_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-line-bg: %1$s;', esc_attr($timeline_color)),
                ]
            );
        }

        if ($timeline_fill_color != '' && $timeline_fill_setting === "on") {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-line-filling-color: %1$s;', esc_attr($timeline_fill_color)),
                ]
            );
        }

        if ($icon_bg_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ibx-bg: %1$s;', esc_attr($icon_bg_color)),
                ]
            );
        }

        if ($icon_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ibx-color: %1$s;', esc_attr($icon_color)),
                ]
            );
        }

        ET_Builder_Element::set_style(
            $render_slug,
            [
                'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story-right .tmdivi-labels",
                'declaration' => 'text-align: right;',
            ]
        );

        ET_Builder_Element::set_style(
            $render_slug,
            [
                'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story-left .tmdivi-labels",
                'declaration' => 'text-align: left;',
            ]
        );

        if ($label_font != '') {

            self::enqueue_google_font($label_font);
            $Font_properties = self::extractFontProperties($label_font);
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-font: %1$s;', esc_attr($Font_properties['fontFamily'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-weight: %1$s;', esc_attr($Font_properties['fontWeight'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-text-transform: %1$s;', esc_attr($Font_properties['textTransform'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-text-decoration: %1$s;', esc_attr($Font_properties['textDecoration'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-style: %1$s;', esc_attr($Font_properties['fontStyle'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-text-decoration-color: %1$s;', esc_attr($Font_properties['textDecorationLineColor'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-text-decoration-style: %1$s;', esc_attr($Font_properties['textDecorationStyle'])),
                ]
            );
        }
        if ($label_fsize != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-size: %1$s;', esc_attr($label_fsize)),
                ]
            );
        }

        if ($label_fcolor != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-big-color: %1$s;', esc_attr($label_fcolor)),
                ]
            );
        }

        if ($sub_label_font != '') {

            self::enqueue_google_font($sub_label_font);
            $Font_properties = self::extractFontProperties($sub_label_font);
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-font: %1$s;', esc_attr($Font_properties['fontFamily'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-weight: %1$s;', esc_attr($Font_properties['fontWeight'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-text-transform: %1$s;', esc_attr($Font_properties['textTransform'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-text-decoration: %1$s;', esc_attr($Font_properties['textDecoration'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-style: %1$s;', esc_attr($Font_properties['fontStyle'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-text-decoration-color: %1$s;', esc_attr($Font_properties['textDecorationLineColor'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-text-decoration-style: %1$s;', esc_attr($Font_properties['textDecorationStyle'])),
                ]
            );
        }

        if ($sub_label_fsize != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-size: %1$s;', esc_attr($sub_label_fsize)),
                ]
            );
        }

        if ($sub_label_fcolor != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-lbl-small-color: %1$s;', esc_attr($sub_label_fcolor)),
                ]
            );
        }

        if ($year_label_font) {
            
            self::enqueue_google_font($year_label_font);
            $Font_properties = self::extractFontProperties($year_label_font);
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-font: %1$s;', esc_attr($Font_properties['fontFamily'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-weight: %1$s;', esc_attr($Font_properties['fontWeight'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-text-transform: %1$s;', esc_attr($Font_properties['textTransform'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-text-decoration: %1$s;', esc_attr($Font_properties['textDecoration'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-style: %1$s;', esc_attr($Font_properties['fontStyle'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-text-decoration-color: %1$s;', esc_attr($Font_properties['textDecorationLineColor'])),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-text-decoration-style: %1$s;', esc_attr($Font_properties['textDecorationStyle'])),
                ]
            );
        }

        if ($year_label_color) {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-color: %1$s;', esc_attr($year_label_color)),
                ]
            );
        }

        if ($year_label_boxsize != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-size: %1$s;', esc_attr($year_label_boxsize)),
                ]
            );
        } 

        if ($year_label_font_size != '') {

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-text-size: %1$s;', esc_attr($year_label_font_size)),
                ]
            );
        } 

        if ($year_label_bgcolor != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    // 'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-ybx-bg: %1$s;', esc_attr($year_label_bgcolor)),
                ]
            );
        }


        if ($story_padding != '') {

            $padding_part = explode('|', $story_padding);

            $padding_top = ($padding_part[0] !== "") ? esc_attr($padding_part[0]) : "0.75em";
            $padding_right = ($padding_part[1] !== "") ? esc_attr($padding_part[1]) : "0.75em";
            $padding_bottom = ($padding_part[2] !== "") ? esc_attr($padding_part[2]) : "2px";
            $padding_left = ($padding_part[3] !== "") ? esc_attr($padding_part[3]) : "0.75em";

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-padding: %1$s %2$s %3$s %4$s;', $padding_top, $padding_right, $padding_bottom, $padding_left),
                ],
            );
        }

        if ($story_background_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-bgc: %1$s;', esc_attr($story_background_color)),
                ],
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-arrow",
                    'declaration' => sprintf('background: %1$s;', esc_attr($story_background_color)),
                ],
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                    'declaration' => sprintf('background: %1$s;', esc_attr($story_background_color)),
                ],
            );
        }
        if ($heading_text_size != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-title-font-size: %1$s;', esc_attr($heading_text_size)),
                ],
            );
        }
        if ($heading_line_height != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-title",
                    'declaration' => sprintf('line-height: %1$s;', esc_attr($heading_line_height)),
                ],
            );
        }
        if ($heading_font_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-title-color: %1$s;', esc_attr($heading_font_color)),
                ],
            );
        }
        if ($heading_background_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-title-background-color: %1$s;', esc_attr($heading_background_color)),
                ],
            );
        }
        if ($heading_text_align != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-text-align: %1$s;', esc_attr($heading_text_align)),
                ],
            );
        }

        if ($heading_custom_padding != '') {

            $padding_part = explode('|', $heading_custom_padding);

            $padding_top = ($padding_part[0] !== "") ? esc_attr($padding_part[0]) : "5px";
            $padding_right = ($padding_part[1] !== "") ? esc_attr($padding_part[1]) : "5px";
            $padding_bottom = ($padding_part[2] !== "") ? esc_attr($padding_part[2]) : "5px";
            $padding_left = ($padding_part[3] !== "") ? esc_attr($padding_part[3]) : "5px";

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-title-padding: %1$s %2$s %3$s %4$s;',  $padding_top, $padding_right, $padding_bottom, $padding_left),
                ],
            );
        }

        if ($heading_settings_font != '') {
            $Font_properties = self::extractFontProperties($year_label_font);
            $font_weight = $Font_properties['fontWeight'] === null ? 'normal' : $Font_properties['fontWeight'];

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-title-font-weight: %1$s;',esc_attr($font_weight)),
                ],
            );
        }


        if ($description_text_size != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-des-text-size: %1$s;', esc_attr($description_text_size)),
                ],
            );
        }

        if ($description_line_height != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-description",
                    'declaration' => sprintf('line-height: %1$s;', esc_attr($description_line_height)),
                ],
            );
        }

        if ($description_font_color != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-des-color: %1$s;', esc_attr($description_font_color)),
                ],
            );
        }

        if ($description_background_color != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-des-background: %1$s;', esc_attr($description_background_color)),
                ],
            );
        }

        if ($description_text_align != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-des-text-align: %1$s;', esc_attr($description_text_align)),
                ],
            );
        }

        if ($description_custom_padding != '') { 
            
            $padding_part = explode('|', $description_custom_padding);

            $padding_top = ($padding_part[0] !== "") ? esc_attr($padding_part[0]) : "5px";
            $padding_right = ($padding_part[1] !== "") ? esc_attr($padding_part[1]) : "0px";
            $padding_bottom = ($padding_part[2] !== "") ? esc_attr($padding_part[2]) : "0px";
            $padding_left = ($padding_part[3] !== "") ? esc_attr($padding_part[3]) : "0px";

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-cbx-des-padding: %1$s %2$s %3$s %4$s;',  $padding_top, $padding_right, $padding_bottom, $padding_left),
                ],
            );
        }

        if ($labels_position != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-vertical",
                    'declaration' => sprintf('--tw-ibx-position: %1$s;', str_replace("px", "", esc_attr($labels_position))),
                ],
            );
        }

        if ($labels_spacing_bottom != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-labels",
                    'declaration' => sprintf('gap: %1$s;',  esc_attr($labels_spacing_bottom)),
                ],
            );
        }

        if ($story_spacing_top != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story",
                    'declaration' => sprintf('margin-top: %1$s;',  esc_attr($story_spacing_top)),
                ],
            );
        }

        if ($story_spacing_bottom != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story",
                    'declaration' => sprintf('margin-bottom: %1$s;',  esc_attr($story_spacing_bottom)),
                ],
            );
        }

        if ($background_main != '') { 
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-tw-main-bc: %1$s;',  esc_attr($background_main)),
                ],
            );
        }

        if (!empty($props['border_style_all_story_settings'])) {
            $border_style_all = esc_attr($props['border_style_all_story_settings']);
        
            $screen_width_condition = '@media (min-width: 768px)';
            $border_color_declaration = sprintf('border-style: %1$s %2$s hidden hidden;', $border_style_all, $border_style_all);
        
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    'declaration' => sprintf('border-style: hidden hidden %1$s %2$s;', $border_style_all, $border_style_all),
                ]
            );

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-vertical-left.tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    'declaration' => $screen_width_condition . '{' . $border_color_declaration . '}',
                ]
            );
        
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                    'declaration' => $screen_width_condition . '{' . $border_color_declaration . '}',
                ]
            );
        } else  {
            if((!empty($props['border_width_all_story_settings']))){

                $screen_width_condition = '@media (min-width: 768px)';
                $border_color_declaration = sprintf('border-style: solid solid hidden hidden;');

                ET_Builder_Element::set_style(
                    $render_slug,
                    [
                        'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                        'declaration' => 'border-style: hidden hidden solid solid;',
                    ]
                );
                
                ET_Builder_Element::set_style(
                    $render_slug,
                    [
                        'selector' => "%%order_class%% .tmdivi-vertical-left.tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                        'declaration' => $screen_width_condition . '{' . $border_color_declaration . '}',

                    ]
                );
            
                ET_Builder_Element::set_style(
                    $render_slug,
                    [
                        'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                        'declaration' => $screen_width_condition . '{' . $border_color_declaration . '}',
                    ]
                );

                ET_Builder_Element::set_style(
                    $render_slug,
                    [
                        'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                        'declaration' => sprintf('border-width: %1$s;',esc_attr($props['border_width_all_story_settings'])),
                    ]
                );

                ET_Builder_Element::set_style(
                    $render_slug,
                    [
                        'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                        'declaration' => sprintf('border-width: %1$s;',esc_attr($props['border_width_all_story_settings'])),
                    ]
                );
            }
        }

        if ($props['border_color_all_story_settings'] != '') { 
            $border_color_all = esc_attr($props['border_color_all_story_settings']);

            $screen_width_condition = '@media (min-width: 768px)';
            $border_color_declaration = sprintf('border-color: %1$s %2$s transparent transparent;',  $border_color_all, $border_color_all);

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    'declaration' => sprintf('border-color: transparent transparent %1$s %2$s;',  $border_color_all, $border_color_all),
                ],
            );

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-vertical-left.tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    'declaration' => $screen_width_condition . '{' . $border_color_declaration . '}',
                ],
            );

            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                    // 'declaration' => sprintf('border-color: %1$s %2$s transparent transparent;',  $border_color_all, $border_color_all),
                    'declaration' => $screen_width_condition . '{' . $border_color_declaration . '}',
                ],
            );

        }
        if ($props['timeline_line_width'] != '') { 
           
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-wrapper",
                    'declaration' => sprintf('--tw-line-width : %1$s;', esc_attr($timeline_line_width)),
                ],
            );
        }

}

    public static function ChildStaticCssLoader($props, $render_slug){

        $child_story_background_color = esc_attr($props['child_story_background_color']);
        $child_story_heading_color = esc_attr($props['child_story_heading_color']);
        $child_story_description_color = esc_attr($props['child_story_description_color']);
        $child_story_label_color = esc_attr($props['child_story_label_color']);
        $child_story_sub_label_color = esc_attr($props['child_story_sub_label_color']);



        if ($child_story_background_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-story .tmdivi-content",
                    'declaration' => sprintf('background-color: %1$s;', $child_story_background_color),
                ]
            );
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-story > .tmdivi-arrow",
                    'declaration' => sprintf('background: %1$s !important;', $child_story_background_color),
                ],
            );
        }

        if ($child_story_heading_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-story .tmdivi-content .tmdivi-title",
                    'declaration' => sprintf('--tw-cbx-title-color: %1$s;', $child_story_heading_color),
                ]
            );
        }

        if ($child_story_description_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-story .tmdivi-content .tmdivi-description",
                    'declaration' => sprintf('--tw-cbx-des-color: %1$s;', $child_story_description_color),
                ]
            );
        }

        if ($child_story_label_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-story div.tmdivi-label-big",
                    'declaration' => sprintf('--tw-lbl-big-color: %1$s;', $child_story_label_color),
                ]
            );
        }

        if ($child_story_sub_label_color != '') {
            ET_Builder_Element::set_style(
                $render_slug,
                [
                    'selector' => "%%order_class%% .tmdivi-story div.tmdivi-label-small",
                    'declaration' => sprintf('--tw-lbl-small-color: %1$s;', $child_story_sub_label_color),
                ]
            );
        }
    } 

}
