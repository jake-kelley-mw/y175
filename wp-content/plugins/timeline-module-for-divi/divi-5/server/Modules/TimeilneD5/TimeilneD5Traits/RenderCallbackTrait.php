<?php
namespace TMDIVI\Modules\TimeilneD5\TimeilneD5Traits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use TMDIVI\Modules\TimeilneD5\TimeilneD5;
use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait {
  	public static function render_callback( $attrs, $content, $block, $elements ) {

		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];	

		$instanceNum = $block->parsed_block['orderIndex'];
        $wrapper_id = "tmdivi-wrapper-$instanceNum";

		$timeline_layout = $attrs['timeline_layout']['advanced']['layout']['desktop']['value']['timeline_layout'] ?? 'both-side';


		$timeline_line_filling = $attrs['timeline_fill_setting']['advanced']['desktop']['value'] ?? 'off';

		if($timeline_line_filling === 'on'){
			wp_enqueue_script('d5-timeline-line-filling');
		}
		$timeline_layout_class;
		switch($timeline_layout){
		  case "one-side-left":
			$timeline_layout_class = "tmdivi-vertical-right";
			break;
		case "one-side-right":
			$timeline_layout_class = "tmdivi-vertical-left";
			break;
		default:
			$timeline_layout_class = "both-side";
		}

		switch($timeline_layout){
			case "one-side-left":
				$timelineLayoutClass = "tmdivi-vertical-right";
				break;
			case "one-side-right":
				$timelineLayoutClass = "tmdivi-vertical-left";
				break;
			default:
				$timelineLayoutClass = "both-side";
		}

		$layout_html = '';

		$layout_html .= 
			sprintf(
			'<div id="%4$s" class="tmdivi-vertical tmdivi-wrapper %3$s style-1 tmdivi-bg-simple" data-line-filling="%2$s">
					<div class="tmdivi-start"></div>
					<div class="tmdivi-line tmdivi-timeline"> %1$s
						<div class="tmdivi-inner-line" style="height:0px" data-line-fill="%2$s"></div>
					</div>
					<div class="tmdivi-end"></div>
			</div>',
			$content,
			($timeline_line_filling === 'on') ? 'true' : 'false',
			esc_attr($timelineLayoutClass),
			$wrapper_id
		);


		$parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'attrs'               => $attrs,
				'elements'            => $elements,
				'classnamesFunction'  => [ TimeilneD5::class, 'module_classnames' ],
				'scriptDataComponent' => [ TimeilneD5::class, 'module_script_data' ],
				'stylesComponent'     => [ TimeilneD5::class, 'module_styles' ],
				'parentAttrs'         => $parent_attrs,
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => ElementComponents::component(
					[
						'attrs'         => $attrs['module']['decoration'] ?? [],
						'id'            => $block->parsed_block['id'],

						// FE only.
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				// ) . $content,
				) . $layout_html,
				'childrenIds'         => $children_ids,
			]
		);
	}
}
