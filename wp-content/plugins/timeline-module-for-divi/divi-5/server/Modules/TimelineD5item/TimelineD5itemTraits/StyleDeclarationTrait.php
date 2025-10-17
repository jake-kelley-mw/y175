<?php
namespace TMDIVI\Modules\TimelineD5item\TimelineD5itemTraits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait StyleDeclarationTrait {

  public static function icon_font_declaration( $params ) {
		$icon_attr = $params['attrValue'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => [
					'font-family' => true,
					'content'     => true,
				],
			]
		);

		if ( ! empty( $icon_attr ) ) {
			$style_declarations->add( 'content', '"' . Utils::process_font_icon( $icon_attr ) . '"' );
			$font_family = isset( $icon_attr['type'] ) && 'fa' === $icon_attr['type'] ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', $font_family );
		}

		return $style_declarations->value();
	}

}