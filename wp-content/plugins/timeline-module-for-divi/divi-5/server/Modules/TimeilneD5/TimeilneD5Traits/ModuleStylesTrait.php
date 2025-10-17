<?php
namespace TMDIVI\Modules\TimeilneD5\TimeilneD5Traits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait {

  use CustomCssTrait;

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

  public static function module_styles( $args ) {
		$attrs        = $args['attrs'] ?? [];
		$parent_attrs = $args['parentAttrs'] ?? [];
		$order_class  = $args['orderClass'];
		$elements     = $args['elements'];
		$settings     = $args['settings'] ?? [];

		$icon_selector = "{$order_class} .et-pb-icon";

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					// Module.
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'disabledOn' => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
							],
						]
					),
					CssStyle::style(
						[
							'selector'  => $args['orderClass'],
							'attr'      => $attrs['css'] ?? [],
							'cssFields' => self::custom_css(),
						]
					),

					// Old module css migration start

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								// Retrieve the raw string; adjust the key as necessary.
								$raw = $args['attrs']['unknownAttributes']['heading_custom_padding'] ?? '';
								$parts = explode('|', $raw);
								// Use default "5px" if any part is empty
								$top = (isset($parts[0]) && $parts[0] !== '') ? trim($parts[0]) : '5px';
								$right = (isset($parts[1]) && $parts[1] !== '') ? trim($parts[1]) : '5px';
								$bottom = (isset($parts[2]) && $parts[2] !== '') ? trim($parts[2]) : '5px';
								$left = (isset($parts[3]) && $parts[3] !== '') ? trim($parts[3]) : '5px';
								return "--tw-cbx-title-padding: {$top} {$right} {$bottom} {$left};";
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								// Retrieve the raw string; adjust the key as necessary.
								$raw = $args['attrs']['unknownAttributes']['description_custom_padding'] ?? '';
								$parts = explode('|', $raw);
								// Use default "5px" if any part is empty
								$top = (isset($parts[0]) && $parts[0] !== '') ? trim($parts[0]) : '5px';
								$right = (isset($parts[1]) && $parts[1] !== '') ? trim($parts[1]) : '5px';
								$bottom = (isset($parts[2]) && $parts[2] !== '') ? trim($parts[2]) : '5px';
								$left = (isset($parts[3]) && $parts[3] !== '') ? trim($parts[3]) : '5px';
								return "--tw-cbx-des-padding: {$top} {$right} {$bottom} {$left};";
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								// Retrieve the raw string; adjust the key as necessary.
								$raw = $args['attrs']['unknownAttributes']['story_padding'] ?? '';
								$parts = explode('|', $raw);
								// Use default "5px" if any part is empty
								$top = (isset($parts[0]) && $parts[0] !== '') ? trim($parts[0]) : '0.75em';
								$right = (isset($parts[1]) && $parts[1] !== '') ? trim($parts[1]) : '0.75em';
								$bottom = (isset($parts[2]) && $parts[2] !== '') ? trim($parts[2]) : '2px';
								$left = (isset($parts[3]) && $parts[3] !== '') ? trim($parts[3]) : '0.75em';
								return "--tw-cbx-padding: {$top} {$right} {$bottom} {$left};";
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-content',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								$raw = $args['attrs']['unknownAttributes']['border_radii_story_settings'] ?? '';
								// Expected format: "off|top|right|bottom|left"
								$parts = explode('|', $raw);
								$top = isset($parts[1]) ? trim($parts[1]) : '';
								$right = isset($parts[2]) ? trim($parts[2]) : '';
								$bottom = isset($parts[3]) ? trim($parts[3]) : '';
								$left = isset($parts[4]) ? trim($parts[4]) : '';
								return "border-radius: {$top} {$right} {$bottom} {$left};";
							},
						]
					),					

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								$data = $args['attrs']['unknownAttributes']['year_label_box_size'] ?? '80px';
								return "--tw-ybx-size:{$data};";
							},
						]
					),
					
					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-story',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								$data = $args['attrs']['unknownAttributes']['story_spacing_top'] ?? '';
								return "margin-top:{$data};";
							},
						]
					),
					
					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-story',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								$data = $args['attrs']['unknownAttributes']['story_spacing_bottom'] ?? '';
								return "margin-bottom:{$data};";
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-vertical',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								if(!empty(($args['attrs']['labels_position']['advanced']['desktop']['value']))){
									return '';
								}

								$data = $args['attrs']['unknownAttributes']['labels_position'] ?? '0';
								return "--tw-ibx-position:{$data};";
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ($declaration_function_args) use ($args) {
								$data = $args['attrs']['unknownAttributes']['labels_spacing_bottom'] ?? '';
								if($data === ""){
									return '';
								}
								return "--tw-lbl-gap:{$data};";
							},
						]
					),



					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								if(!empty($args['attrs']['unknownAttributes'])){
									
									$data = $args['attrs']['unknownAttributes']['background_main'] ?? '';

									return "--tw-tw-main-bc:{$data};";
								}
							},
						]
					),

					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper .tmdivi-content .tmdivi-title',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								if(!empty($args['attrs']['unknownAttributes']['heading_font_color'])){

									$data = $args['attrs']['unknownAttributes']['heading_font_color'];

									return "color:{$data};";
								}
							},
						]
					),

					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper .tmdivi-content .tmdivi-title',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['heading_background_color'] ?? '';
								return "background-color:{$data};";
							},
						]
					),
					
					// Description Font Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['description_font_color'] ?? '#000000';
								return "--tw-cbx-des-color:{$data};";
							},
						]
					),

					// Description Background Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['description_background_color'] ?? '';
								return "--tw-cbx-des-background:{$data};";
							},
						]
					),

					// Label Font Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['label_font_color'] ?? '#222';
								return "--tw-lbl-big-color:{$data};";
							},
						]
					),

					// Sub Label Font Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['sub_label_font_color'] ?? '#222';
								return "--tw-lbl-small-color:{$data};";
							},
						]
					),

					// Year Label Font Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['year_label_font_color'] ?? '#ffffff';
								return "--tw-ybx-text-color:{$data};";
							},
						]
					),

					// Year Label Background Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['year_label_bg_color'] ?? '#54595f';
								return "--tw-ybx-bg:{$data};";
							},
						]
					),

					// Label Font Size
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['label_font_size'] ?? '24px';
								return "--tw-lbl-big-size:{$data};";
							},
						]
					),

					// Sub Label Font Size
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['sub_label_font_size'] ?? '16px';
								return "--tw-lbl-small-size:{$data};";
							},
						]
					),

					// Year Label Font Size
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['year_label_font_size'] ?? '24px';
								return "--tw-ybx-text-size:{$data};";
							},
						]
					),

					// Timeline Line Width
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['timeline_line_width'] ?? '4px';
								return "--tw-line-width:{$data};";
							},
						]
					),

					// Heading Text Size
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['heading_text_size'] ?? '24px';
								return "--tw-cbx-title-font-size:{$data};";
							},
						]
					),

					// Heading Text Align
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['heading_text_align'] ?? '';
								return "--tw-cbx-text-align:{$data};";
							},
						]
					),

					// Heading Line Height (applied on the title element)
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper .tmdivi-content .tmdivi-title',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['heading_line_height'] ?? '';
								return "line-height:{$data};";
							},
						]
					),

					// Description Text Size
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['description_text_size'] ?? '20px';
								return "--tw-cbx-des-text-size:{$data};";
							},
						]
					),

					// Description Text Align
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['description_text_align'] ?? '';
								if($data === ""){
									return '';
								}
								return "--tw-cbx-des-text-align:{$data};";
							},
						]
					),

					// Description Line Height (applied on the description element)
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper .tmdivi-content .tmdivi-description',
							'attr'                => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['description_line_height'] ?? '';
								return "line-height:{$data};";
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['label_font'] ?? '';
							
								self::enqueue_google_font($data);
								$data = self::extractFontProperties($data);

								$font_family = (!empty($data) && !empty($data['fontFamily'])) ? $data['fontFamily'] : 'Sans serif'; 	
								$font_weight = (!empty($data['fontFamily']) && !empty($data['fontWeight'])) ? $data['fontWeight'] : 'bold'; 	

								$css = "
									--tw-lbl-big-font:{$font_family};
									--tw-lbl-big-style:{$data['fontStyle']};
									--tw-lbl-big-weight:{$font_weight};
									--tw-lbl-big-text-decoration:{$data['textDecoration']};
									--tw-lbl-big-text-decoration-color:{$data['textDecorationLineColor']};
									--tw-lbl-big-text-decoration-style:{$data['textDecorationStyle']};
									--tw-lbl-big-text-transform:{$data['textTransform']};
								";
								return $css;
							},
						]
					),					

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['sub_label_font'] ?? '';

								self::enqueue_google_font($data);
								$data = self::extractFontProperties($data);

								$font_family = (!empty($data) && !empty($data['fontFamily'])) ? $data['fontFamily'] : 'Sans serif'; 	
								$font_weight = (!empty($data['fontFamily']) && !empty($data['fontWeight'])) ? $data['fontWeight'] : 'bold'; 	

								$css = "
									--tw-lbl-small-font:{$font_family};
									--tw-lbl-small-style:{$data['fontStyle']};
									--tw-lbl-small-weight:{$font_weight};
									--tw-lbl-small-text-decoration:{$data['textDecoration']};
									--tw-lbl-small-text-decoration-color:{$data['textDecorationLineColor']};
									--tw-lbl-small-text-decoration-style:{$data['textDecorationStyle']};
									--tw-lbl-small-text-transform:{$data['textTransform']};
								";
								return $css;
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['year_label_font'] ?? '';
 
								self::enqueue_google_font($data);
								$data = self::extractFontProperties($data);

								$font_family = (!empty($data) && !empty($data['fontFamily'])) ? $data['fontFamily'] : 'Sans serif'; 	
								$font_weight = (!empty($data['fontFamily']) && !empty($data['fontWeight'])) ? $data['fontWeight'] : 'bold'; 	

								$css = "
									--tw-ybx-font:{$font_family};
									--tw-ybx-text-style:{$data['fontStyle']};
									--tw-ybx-text-weight:{$font_weight};
									--tw-ybx-text-text-decoration:{$data['textDecoration']};
									--tw-ybx-text-text-decoration-color:{$data['textDecorationLineColor']};
									--tw-ybx-text-text-decoration-style:{$data['textDecorationStyle']};
									--tw-ybx-text-text-transform:{$data['textTransform']};
								";
								return $css;
							},
						]
					),					

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-content .tmdivi-title',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['heading_settings_font'] ?? '';

								self::enqueue_google_font($data);
								$data = self::extractFontProperties($data);

								$font_family = (!empty($data) && !empty($data['fontFamily'])) ? $data['fontFamily'] : 'Sans serif'; 	
								$font_weight = (!empty($data['fontFamily']) && !empty($data['fontWeight'])) ? $data['fontWeight'] : 'bold'; 	
								$css = "
									--tw-cbx-title-font-family:{$font_family};
									--tw-cbx-title-font-style:{$data['fontStyle']};
									--tw-cbx-title-font-weight:{$font_weight};
									--tw-cbx-title-text-decoration:{$data['textDecoration']};
									--tw-cbx-title-text-decoration-color:{$data['textDecorationLineColor']};
									--tw-cbx-title-text-decoration-style:{$data['textDecorationStyle']};
									--tw-cbx-title-text-transform:{$data['textTransform']};
								";
								return $css;
							},
						]
					),
					
					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-content .tmdivi-description',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$data = $args['attrs']['unknownAttributes']['description_settings_font'] ?? '';

								self::enqueue_google_font($data);
								$data = self::extractFontProperties($data);

								$font_family = (!empty($data) && !empty($data['fontFamily'])) ? $data['fontFamily'] : 'Sans serif'; 	

								$css = "
									--tw-cbx-des-font-family:{$font_family};
									--tw-cbx-des-font-style:{$data['fontStyle']};
									--tw-cbx-des-font-weight:{$data['fontWeight']};
									--tw-cbx-des-text-decoration:{$data['textDecoration']};
									--tw-cbx-des-text-decoration-color:{$data['textDecorationLineColor']};
									--tw-cbx-des-text-decoration-style:{$data['textDecorationStyle']};
									--tw-cbx-des-text-transform:{$data['textTransform']};
								";
								return $css;
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-content',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$border_style_all = $args['attrs']['unknownAttributes']['border_style_all_story_settings'] ?? '';
								$border_width_all = $args['attrs']['unknownAttributes']['border_width_all_story_settings'] ?? '';
								$border_color_all = $args['attrs']['unknownAttributes']['border_color_all_story_settings'] ?? '';
								$css = "
									border-width:{$border_width_all};
									border-style:{$border_style_all};
									border-color:{$border_color_all};
								";
								return $css;
							},
						]
					),
					
					// right story arrow
					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-story-right .tmdivi-arrow',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$border_style_all = $args['attrs']['unknownAttributes']['border_style_all_story_settings'] ?? '';
								$border_width_all = $args['attrs']['unknownAttributes']['border_width_all_story_settings'] ?? '';
								$border_color_all = $args['attrs']['unknownAttributes']['border_color_all_story_settings'] ?? '';

								$border_style_all = ($border_width_all !== '0px' && $border_style_all === '') ? 'solid' : $border_style_all;

								$css = "
									border-width:0px 0px {$border_width_all} {$border_width_all};
									border-style:{$border_style_all};
									border-color:{$border_color_all};
								";
								return $css;
							},
						]
					),

					// left story arrow
					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {

								if(isset($args['attrs']['story_border_settings'])){
									return;
								}

								$border_style_all = $args['attrs']['unknownAttributes']['border_style_all_story_settings'] ?? '';
								$border_width_all = $args['attrs']['unknownAttributes']['border_width_all_story_settings'] ?? '';
								$border_color_all = $args['attrs']['unknownAttributes']['border_color_all_story_settings'] ?? '';

								$border_style_all = ($border_width_all !== '0px' && $border_style_all === '') ? 'solid' : $border_style_all;

								$border_width_all = $border_width_all === '' ? '0px' : $border_width_all; 

								$css = "
									border-width:{$border_width_all} {$border_width_all} 0px 0px;
									border-style:{$border_style_all};
									border-color:{$border_color_all};
								";
								return $css;
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper.tmdivi-vertical-right .tmdivi-arrow',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$border_style_all = $args['attrs']['unknownAttributes']['border_style_all_story_settings'] ?? '';
								$border_width_all = $args['attrs']['unknownAttributes']['border_width_all_story_settings'] ?? '';
								$border_color_all = $args['attrs']['unknownAttributes']['border_color_all_story_settings'] ?? '';

								$border_style_all = ($border_width_all !== '0px' && $border_style_all === '') ? 'solid' : $border_style_all;

								$css = "
									border-width:0px 0px {$border_width_all} {$border_width_all} ;
									border-style:{$border_style_all};
									border-color:{$border_color_all};
								";
								return $css;
							},
						]
					),

					CommonStyle::style(
						[
							'selector' => $order_class . ' .tmdivi-wrapper.tmdivi-vertical-left .tmdivi-arrow',
							'attr'     => $attrs['story_background_color']['advanced'] ?? $attrs['timeline_layout']['advanced']['layout'],
							'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
								$border_style_all = $args['attrs']['unknownAttributes']['border_style_all_story_settings'] ?? '';
								$border_width_all = $args['attrs']['unknownAttributes']['border_width_all_story_settings'] ?? '';
								$border_color_all = $args['attrs']['unknownAttributes']['border_color_all_story_settings'] ?? '';

								$border_style_all = ($border_width_all !== '0px' && $border_style_all === '') ? 'solid' : $border_style_all;

								$css = "
									border-width:{$border_width_all} {$border_width_all} 0px 0px;
									border-style:{$border_style_all};
									border-color:{$border_color_all};
								";
								return $css;
							},
						]
					),
					
					
					// old module css migration end

					// Timeline Story background color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-story .tmdivi-content, '.$order_class . ' .tmdivi-story > .tmdivi-arrow',
							'attr'                => $attrs['story_background_color']['advanced'] ?? [],
							'declarationFunction' => function ( $declaration_function_args ) {
								$attr_value = $declaration_function_args['attrValue'] ?? [];
								return "--tw-cbx-bgc: {$attr_value};";
								// return "background: {$attr_value};";
							},
						]
					),
					CommonStyle::style([
						'selector'            => $order_class . ' .tmdivi-story .tmdivi-content',
						'attr'                => $attrs['story_border_settings']['advanced'] ?? [],
						'declarationFunction' => function ($declaration_function_args) {
							$css = '';
							$attr_value = $declaration_function_args['attrValue'] ?? [];
							
							// Extract `styles.all` and side-specific styles
							$all_styles = $attr_value['styles']['all'] ?? [];
							$top_styles = $attr_value['styles']['top'] ?? [];
							$right_styles = $attr_value['styles']['right'] ?? [];
							$bottom_styles = $attr_value['styles']['bottom'] ?? [];
							$left_styles = $attr_value['styles']['left'] ?? [];
							
							// Default global border styles (styles.all)
							if (!empty($all_styles)) {
								$width = $all_styles['width'] ?? '0px';
								$color = $all_styles['color'] ?? '#666666';
								$style = $all_styles['style'] ?? 'solid';
					
								// Ensure a valid width before applying global styles
								if ((int) str_replace('px', '', $width) > 0) {
									$css .= "border-width: {$width}; border-color: {$color}; border-style: {$style};";
								} else {
									$css .= "border-width: {$width}; border-color: transparent; border-style: {$style};";
								}
							}
					
							// Override global styles with individual side-specific settings
							if (!empty($top_styles) || !empty($right_styles) || !empty($bottom_styles) || !empty($left_styles)) {
								$top_width = $top_styles['width'] ?? $all_styles['width'] ?? '0px';
								$right_width = $right_styles['width'] ?? $all_styles['width'] ?? '0px';
								$bottom_width = $bottom_styles['width'] ?? $all_styles['width'] ?? '0px';
								$left_width = $left_styles['width'] ?? $all_styles['width'] ?? '0px';
					
								$top_color = $top_styles['color'] ?? $all_styles['color'] ?? 'transparent';
								$right_color = $right_styles['color'] ?? $all_styles['color'] ?? 'transparent';
								$bottom_color = $bottom_styles['color'] ?? $all_styles['color'] ?? 'transparent';
								$left_color = $left_styles['color'] ?? $all_styles['color'] ?? 'transparent';
					
								$top_style = $top_styles['style'] ?? $all_styles['style'] ?? 'solid';
								$right_style = $right_styles['style'] ?? $all_styles['style'] ?? 'solid';
								$bottom_style = $bottom_styles['style'] ?? $all_styles['style'] ?? 'solid';
								$left_style = $left_styles['style'] ?? $all_styles['style'] ?? 'solid';
					
								$css .= "
									border-top: {$top_width} {$top_style} {$top_color};
									border-right: {$right_width} {$right_style} {$right_color};
									border-bottom: {$bottom_width} {$bottom_style} {$bottom_color};
									border-left: {$left_width} {$left_style} {$left_color};
								";
							}
					
							// Border radius settings
							$radius = $attr_value['radius'] ?? [];
							if (!empty($radius)) {
								$top_left = $radius['topLeft'] ?? '0';
								$top_right = $radius['topRight'] ?? '0';
								$bottom_right = $radius['bottomRight'] ?? '0';
								$bottom_left = $radius['bottomLeft'] ?? '0';
					
								$css .= "border-radius: {$top_left} {$top_right} {$bottom_right} {$bottom_left};";
							}
					
							return $css;
						},
					]),
					
					// Story Right arrow border
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-story.tmdivi-story-right > .tmdivi-arrow',
							'attr'                => $attrs['story_border_settings']['advanced'] ?? [],
							'declarationFunction' => function ($declaration_function_args) {
								$css = '';
								$attr_value = $declaration_function_args['attrValue'] ?? [];
					
								// Extract `styles.all`
								$all_styles = $attr_value['styles']['all'] ?? [];
					
								if (!empty($all_styles)) {
									$width = $all_styles['width'] ?? '0px';
									$style = $all_styles['style'] ?? 'solid';
									$color = ($width !== '0px') ? ($all_styles['color'] ?? '#666666') : 'transparent';
					
									$css .= "border-width: 0px 0px {$width} {$width}; border-style: {$style}; border-color: {$color};";
								}
					
								return $css;
							},
						]
					),					
					// Story left arrow border 
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-story.tmdivi-story-left > .tmdivi-arrow',
							'attr'                => $attrs['story_border_settings']['advanced'] ?? [],
							'declarationFunction' => function ($declaration_function_args) {
								$css = '';
								$attr_value = $declaration_function_args['attrValue'] ?? [];
					
								// Extract `styles.all`
								$all_styles = $attr_value['styles']['all'] ?? [];
					
								if (!empty($all_styles)) {
									$width = $all_styles['width'] ?? '0px';
									$style = $all_styles['style'] ?? 'solid';
									$color = ($width !== '0px') ? ($all_styles['color'] ?? '#666666') : 'transparent';
					
									$css .= "border-width: {$width} {$width} 0px 0px; border-style: {$style}; border-color: {$color};";
								}
					
								return $css;
							},
						]
					),					
					// Horizontal arrow border
					CommonStyle::style(
						[
							'selector'            => $order_class . ' #tmdivi-slider-container .tmdivi-story > .tmdivi-arrow',
							'attr'                => $attrs['story_border_settings']['advanced'] ?? [],
							'declarationFunction' => function ($declaration_function_args) {
								$css = '';
								$attr_value = $declaration_function_args['attrValue'] ?? [];
					
								// Extract `styles.all`
								$all_styles = $attr_value['styles']['all'] ?? [];
					
								if (!empty($all_styles)) {
									$width = $all_styles['width'] ?? '0px';
									$style = $all_styles['style'] ?? 'solid';
									$color = ($width !== '0px') ? ($all_styles['color'] ?? '#666666') : 'transparent';
					
									$css .= "border-width: {$width} 0px 0px {$width}; border-style: {$style}; border-color: {$color};";
								}
					
								return $css;
							},
						]
					),					
					// Right side story arrow border
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-vertical-right .tmdivi-story > .tmdivi-arrow',
							'attr'                => $attrs['story_border_settings']['advanced'] ?? [],
							'declarationFunction' => function ($declaration_function_args) {
								$css = '';
								$attr_value = $declaration_function_args['attrValue'] ?? [];
					
								// Extract `styles.all`
								$all_styles = $attr_value['styles']['all'] ?? [];
					
								if (!empty($all_styles)) {
									$width = $all_styles['width'] ?? '0px';
									$style = $all_styles['style'] ?? 'solid';
									$color = ($width !== '0px') ? ($all_styles['color'] ?? '#666666') : 'transparent';
					
									$css .= "border-width: 0px 0px {$width} {$width}; border-style: {$style}; border-color: {$color};";
								}
					
								return $css;
							},
						]
					),					
					// Left side story arrow border
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-vertical-left .tmdivi-story > .tmdivi-arrow',
							'attr'                => $attrs['story_border_settings']['advanced'] ?? [],
							'declarationFunction' => function ($declaration_function_args) {
								$css = '';
								$attr_value = $declaration_function_args['attrValue'] ?? [];
					
								// Extract `styles.all`
								$all_styles = $attr_value['styles']['all'] ?? [];
					
								if (!empty($all_styles)) {
									$width = $all_styles['width'] ?? '0px';
									$style = $all_styles['style'] ?? 'solid';
									$color = ($width !== '0px') ? ($all_styles['color'] ?? '#666666') : 'transparent';
					
									$css .= "border-width:{$width} {$width} 0px 0px; border-style: {$style}; border-color: {$color};";
								}
					
								return $css;
							},
						]
					),					
					// Timeline Story padding
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-story .tmdivi-content',
							'attr'                => $attrs['story_padding']['advanced'] ?? [],
							'declarationFunction' => function ($declaration_function_args) {
								$css = '';
								$padding = $declaration_function_args['attrValue']['padding'] ?? [];
								// Default padding values
								$top    = $padding['top'] ?? '0px';
								$right  = $padding['right'] ?? '5px';
								$bottom = $padding['bottom'] ?? '0px';
								$left   = $padding['left'] ?? '5px';
								// Generate CSS
								$css .= "padding: {$top} {$right} {$bottom} {$left};";
					
								return $css;
							},
						]
					),	
					// Timeline Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['timeline_color']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-line-bg: {$data};";
								}
								return $css;
							},
						]
					),

					// Timeline Line Width
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['timeline_line_width']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-line-width: {$data};";
								}
								return $css;
							},
						]
					),

					// Timeline Fill Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['timeline_fill_color']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-line-filling-color: {$data};";
								}
								return $css;
							},
						]
					),
					// Icon Background Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['icon_background_color']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-ibx-bg: {$data};";
								}
								return $css;
							},
						]
					),

					// Icon Color
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['icon_color']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-ibx-color: {$data};";
								}
								return $css;
							},
						]
					),

					// Labels Position
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['labels_position']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$data = str_replace('px', '', $data);
									$css = "--tw-ibx-position: {$data};";
								}
								return $css;
							},
						]
					),

					// Labels Spacing Bottom
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['labels_spacing_bottom']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-lbl-gap: {$data};";
								}
								return $css;
							},
						]
					),

					// Story Spacing Top
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper .tmdivi-story',
							'attr'                => $attrs['story_spacing_top']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "margin-top: {$data};";
								}
								return $css;
							},
						]
					),

					// Story Spacing Bottom
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['story_spacing_bottom']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-cbx-bottom-margin: {$data};";
								}
								return $css;
							},
						]
					),

					// year label box size
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							'attr'                => $attrs['year_label_box_size']['advanced'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if (!empty($data)) {
									$css = "--tw-ybx-size: {$data};";
								}
								return $css;
							},
						]
					),

					// Story Title Align
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .tmdivi-wrapper',
							// 'attr'                => $attrs['story_title']['decoration']['font'] ?? '',
							'attr'                => $attrs['story_title']['decoration']['font']['font'] ?? '',
							'declarationFunction' => function ($declaration_function_args) {
								$data = $declaration_function_args['attrValue'] ?? '';
								$css = '';
								if(!empty($data['textAlign'])){
									$title_align_ment = $data['textAlign'];
									$css = "--tw-cbx-text-align: {$title_align_ment};";
								}
								return $css;
							},
						]
					),

					// Label Date.
					$elements->style(
						[
							'attrName' => 'label_date',
						]
					),

					// Sub Label.
					$elements->style(
						[
							'attrName' => 'sub_label',
						]
					),

					// Year Label.
					$elements->style(
						[
							'attrName' => 'label_text',
						]
					),

					// Title.
					$elements->style(
						[
							'attrName' => 'story_title',
						]
					),

					// Content.
					$elements->style(
						[
							'attrName' => 'content',
						]
					),
				],
			]
		);
	}
}