<?php
namespace TMDIVI\Modules\TimelineD5item\TimelineD5itemTraits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use TMDIVI\Modules\TimelineD5item\TimelineD5item;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

trait RenderCallbackTrait {

	public static function render_callback( $attrs, $content, $block, $elements ) {
	  $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

	  $story_order = array_search($block->parsed_block['id'], array_column($parent->innerBlocks, 'id'));

	  $parent_attrs = $parent->attrs ?? [];

	  $timeline_layout = $parent_attrs['timeline_layout']['advanced']['layout']['desktop']['value']['timeline_layout'] ?? 'both-side';

	  $story_wrapper_class = self::get_story_wrapper_class( $timeline_layout, $story_order);
	  $icon_classes = self::get_icon_classes( $attrs['show_story_icon']['innerContent']['enable']['desktop']['value'] ?? '');
	  $parent_default_attributes = ModuleRegistration::get_default_attrs( 'example/parent-module' );
	  $parent_attrs_with_default = array_replace_recursive( $parent_default_attributes, $parent_attrs );

	  $story_html = self::render_story(
		  $attrs,
		  $elements,
		  $icon_classes,
		  $story_wrapper_class,
		  $timeline_layout,
		  $story_order,
		  $parent_attrs
	  );

	  $content_container = HTMLUtility::render(
		  [
			  'tag'               => 'div',
			  'attributes'        => [
				  'class' => 'et_pb_module_inner',
			  ],
			  'childrenSanitizer' => 'et_core_esc_previously',
			  'children'          => $story_html,
		  ]
	  );

	  return Module::render(
		  [
			  'orderIndex'         => $block->parsed_block['orderIndex'],
			  'storeInstance'      => $block->parsed_block['storeInstance'],
			  'id'                 => $block->parsed_block['id'],
			  'name'               => $block->block_type->name,
			  'moduleCategory'     => $block->block_type->category,
			  'attrs'              => $attrs,
			  'elements'           => $elements,
			  'classnamesFunction' => [ TimelineD5item::class, 'module_classnames' ],
			  'stylesComponent'    => [ TimelineD5item::class, 'module_styles' ],
			  'parentAttrs'        => $parent_attrs,
			  'parentId'           => $parent->id ?? '',
			  'parentName'         => $parent->blockName ?? '',
			  'children'           => ElementComponents::component(
				  [
					  'attrs'         => $attrs['module']['decoration'] ?? [],
					  'id'            => $block->parsed_block['id'],
					  'orderIndex'    => $block->parsed_block['orderIndex'],
					  'storeInstance' => $block->parsed_block['storeInstance'],
				  ]
			  ) . $content_container,
		  ]
	  );
  }

  private static function render_story_year_container($label_text){
	$sanitize_label_text_tag = wp_kses_post($label_text);
	$html = "
	<div class='tmdivi-year tmdivi-year-container'>
		  {$sanitize_label_text_tag}
	</div>";
	return $html;
  }

  private static function render_story_labels($label_date,$sub_label){
	$sanitize_label_date_tag = wp_kses_post($label_date);
	$sanitize_sub_label_tag = wp_kses_post($sub_label);

	$html = "
	<div class='tmdivi-labels'>
		{$sanitize_label_date_tag}
		{$sanitize_sub_label_tag}
  	</div>";
	return $html;
  }

  private static function render_story_icons($icon_classes,$story_icon,$show_icon){

	$html = '';
	switch ($show_icon) {
        case 'on':
            $html .= "<div class='{$icon_classes}'>
                        {$story_icon}
                      </div>";
            break;
        default:
            $html = "<div class='{$icon_classes}'></div>";
    }
	return $html;
  }

  private static function render_story_content($story_title,$media,$alt_tag,$content){
	$sanitize_alt_tag = wp_kses_post($alt_tag);
	$sanitize_story_title_tag = wp_kses_post($story_title);
	$content = wp_kses_post($content);
	
	$media_html = ($media !== '') ? "<div class='tmdivi-media full'>
			<img decoding='async' src='{$media}' alt='{$sanitize_alt_tag}' />
		</div>" : '';

	$sanitize_media_html_tag = wp_kses_post($media_html);

	$html = "
	<div class='tmdivi-content'>
		{$sanitize_story_title_tag}
		{$sanitize_media_html_tag}
		{$content}
	</div>";
	return $html;
  }

  private static function get_story_wrapper_class( $timeline_layout, $story_order ) {
	$story_order++;
	  if ( $timeline_layout === 'both-side' ) {
		  return $story_order % 2 ? "tmdivi-story-right" : "tmdivi-story-left";
	  }
	  return '';
  }

  private static function get_icon_classes( $show_icon = 'dot') {
	$icon_class = "";
	$story_icon_class = "";
	switch ( $show_icon ) {
        case "on":
            $icon_class = "tmdivi-icon";
            $story_icon_class = 'tmdivi-story-icon';
            break;
        default:
            $icon_class = "tmdivi-icondot"; 
            $story_icon_class = 'tmdivi-story-no-icon';
            break;
    }
	return compact( 'icon_class', 'story_icon_class' );
  }

  private static function render_story( $attrs, $elements, $icon_classes, $story_wrapper_class, $timeline_layout, $story_order, $parent_attrs ) {
		$media = $attrs['media']['innerContent']['desktop']['value']['src'] ?? '';
		$alt_tag = $attrs['media_alt_tag']['innerContent']['desktop']['value'] ?? '';
		$label_text = $elements->render( [ 'attrName' => 'label_text', 'hoverSelector' => '{{parentSelector}}' ] );
		$label_date = $elements->render( [ 'attrName' => 'label_date', 'hoverSelector' => '{{parentSelector}}' ] );
		$sub_label = $elements->render( [ 'attrName' => 'sub_label', 'hoverSelector' => '{{parentSelector}}' ] );
		$story_title = $elements->render( [ 'attrName' => 'story_title', 'hoverSelector' => '{{parentSelector}}' ] );
		$content = $elements->render( [ 'attrName' => 'content', 'hoverSelector' => '{{parentSelector}}' ] );

		$select_icon_type = $attrs['select_icon_type']['innerContent']['desktop']['value'] ?? '';
		$story_icons_image = $attrs['story_icons_image']['innerContent']['desktop']['value'] ?? '';
		$story_icons_custom_text = $attrs['story_icons_custom_text']['innerContent']['desktop']['value'] ?? '';
		$show_icon = $attrs['show_story_icon']['innerContent']['enable']['desktop']['value'] ?? '';

		if(isset($attrs['icon'])){
			$icon_lib_type = $attrs['icon']['innerContent']['desktop']['value']['type'] ?? '';
			$icon_val = $attrs['icon']['innerContent']['desktop']['value'];
			
			if ( ! is_array( $icon_val ) ) {
				$parts = explode( '||', $icon_val );
				$convertedIcon = [
					'unicode' => isset( $parts[0] ) ? $parts[0] : '',
					'type'    => isset( $parts[1] ) ? $parts[1] : '',
					'weight'  => isset( $parts[2] ) ? $parts[2] : '',
				];
				$icon_val = $convertedIcon;
			}
			if (isset($icon_val['type']) && $icon_val['type'] === 'fa') {
				if (!wp_style_is('tmdivi-fontawesome-css', 'enqueued')) {
					wp_enqueue_style('tmdivi-fontawesome-css');
				}
			}
			
			$story_icon = HTMLUtility::render(
				[
					'tag'               => 'i',
					'attributes'        => [ 'class' => ($icon_val['type'] === 'divi') ? 'et-tmdivi-icon' : 'et-tmdivi-icon-fa'],
					'childrenSanitizer' => 'esc_html',
					'children'          => Utils::process_font_icon( $icon_val ?? ''),
				]
			);
		}else{
			$story_icon = '';
		}

		$story_connector_style = $parent_attrs['story_connector_style']['advanced']['desktop']['value'] ?? '';


		$arrow_css_class = '';
		switch($story_connector_style){
			case 'arrow':
				$arrow_css_class = 'tmdivi-arrow';
				break;
			  case 'line':
				$arrow_css_class = 'tmdivi-arrow-line';
				break;
			  case 'none':
				$arrow_css_class = 'tmdivi-arrow-none';
				break;
			  default:
				$arrow_css_class = 'tmdivi-arrow';
		}

		$story_year_container_html = self::render_story_year_container($label_text);
		$story_labels_html = self::render_story_labels($label_date,$sub_label);
		$story_icons_html = self::render_story_icons($icon_classes['icon_class'],$story_icon,$show_icon);
		$story_content_html = self::render_story_content($story_title,$media,$alt_tag,$content);

		return "
			{$story_year_container_html}
			<div class='tmdivi-story {$story_wrapper_class} {$icon_classes['story_icon_class']}'>
				{$story_labels_html}
				{$story_icons_html}
				<div class='{$arrow_css_class}'></div>
				{$story_content_html}
			</div>
		";
  }
}
