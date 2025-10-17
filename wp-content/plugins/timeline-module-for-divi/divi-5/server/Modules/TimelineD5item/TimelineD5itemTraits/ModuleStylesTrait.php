<?php
namespace TMDIVI\Modules\TimelineD5item\TimelineD5itemTraits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

trait ModuleStylesTrait {

  use CustomCssTrait;
  use StyleDeclarationTrait;


  public static function module_styles( $args ) {
    $attrs        = $args['attrs'] ?? [];
    $order_class  = $args['orderClass'];
    $elements     = $args['elements'];
    $settings     = $args['settings'] ?? [];
    $parent_attrs = $args['parentAttrs'] ?? [];

    $parent_default_attributes = ModuleRegistration::get_default_attrs( 'tmdivi/timeline-story' );
    $parent_attrs_with_default = array_replace_recursive( $parent_default_attributes, $parent_attrs );

    $icon_selector              = "{$order_class} .example_child_module__icon.et-pb-icon";
    $content_container_selector = "{$order_class} .example_child_module__content-container";


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
                TextStyle::style(
                    [
                        'selector' => $content_container_selector,
                        'attr'     => $attrs['module']['advanced']['text'] ?? [],
                    ]
                ),

                // old module migration css start!

                CommonStyle::style(
                    [
                        'selector' => $order_class . ' .tmdivi-story .tmdivi-content div.tmdivi-title',
                        'attr'     => $parent_attrs['story_background_color']['advanced'] ?? $parent_attrs['timeline_layout']['advanced']['layout'] ?? '',
                        'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {                            

                            $data = $args['attrs']['unknownAttributes']['child_story_heading_color'] ?? '';
                            return "color:{$data};";
                        },
                    ]
                ),

                CommonStyle::style(
                    [
                        'selector' => $order_class . ' .tmdivi-story .tmdivi-content .tmdivi-description,' .
                                      $order_class . ' .tmdivi-story .tmdivi-content .tmdivi-description p',
                        'attr'     => $parent_attrs['story_background_color']['advanced'] ?? $parent_attrs['timeline_layout']['advanced']['layout'] ?? '',
                        'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
                            $data = $args['attrs']['unknownAttributes']['child_story_description_color'] ?? '';
                            return "color:{$data};";
                        },
                    ]
                ),

                CommonStyle::style(
                    [
                        'selector' => $order_class . ' .tmdivi-story div.tmdivi-content, ' .
                                      $order_class . ' .tmdivi-story > div.tmdivi-arrow',
                        'attr'     => $parent_attrs['story_background_color']['advanced'] ?? $parent_attrs['timeline_layout']['advanced']['layout'] ?? '',
                        'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
                            $data = $args['attrs']['unknownAttributes']['child_story_background_color'] ?? '';
                            return "background:{$data};";
                        },
                    ]
                ),

                CommonStyle::style(
                    [
                        'selector' => $order_class . ' .tmdivi-story div.tmdivi-label-big',
                        'attr'     => $parent_attrs['story_background_color']['advanced'] ?? $parent_attrs['timeline_layout']['advanced']['layout'] ?? '',
                        'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
                            $data = $args['attrs']['unknownAttributes']['child_story_label_color'] ?? '';
                            return "color:{$data};";
                        },
                    ]
                ),

                CommonStyle::style(
                    [
                        'selector' => $order_class . ' .tmdivi-story div.tmdivi-label-small',
                        'attr'     => $parent_attrs['story_background_color']['advanced'] ?? $parent_attrs['timeline_layout']['advanced']['layout'] ?? '',
                        'declarationFunction' => function ( $declaration_function_args ) use ( $args ) {
                            $data = $args['attrs']['unknownAttributes']['child_story_sub_label_color'] ?? '';
                            return "color:{$data};";
                        },
                    ]
                ),                
                
                // old module migration css end!
                
                CommonStyle::style(
                    [
                        'selector'            => $order_class . ' .tmdivi-story .tmdivi-content, '.$order_class . ' .tmdivi-story > .tmdivi-arrow',
                        'attr'                => $attrs['child_story_background_color']['advanced'] ?? [],
                        'declarationFunction' => function ( $declaration_function_args ) {
                            $attr_value = $declaration_function_args['attrValue'] ?? [];
                            return "background: {$attr_value} !important;";
                        },
                    ]
                ),
                CommonStyle::style(
                    [
                        'selector'            => $order_class . ' .tmdivi-story .tmdivi-content .tmdivi-title',
                        'attr'                => $attrs['child_story_heading_color']['advanced'] ?? [],
                        'declarationFunction' => function ( $declaration_function_args ) {
                            $attr_value = $declaration_function_args['attrValue'] ?? [];
                            return "color: {$attr_value} !important;";
                        },
                    ]
                ),
                CommonStyle::style(
                    [
                        'selector'            => $order_class . ' .tmdivi-story .tmdivi-content .tmdivi-description,'.$order_class . ' .tmdivi-story .tmdivi-content .tmdivi-description p',
                        'attr'                => $attrs['child_story_description_color']['advanced'] ?? [],
                        'declarationFunction' => function ( $declaration_function_args ) {
                            $attr_value = $declaration_function_args['attrValue'] ?? [];
                            return "color:{$attr_value}; !important";
                        },
                    ]
                ),
                CommonStyle::style(
                    [
                        'selector'            => $order_class . ' .tmdivi-story div.tmdivi-label-big',
                        'attr'                => $attrs['child_story_label_color']['advanced'] ?? [],
                        'declarationFunction' => function ( $declaration_function_args ) {
                            $attr_value = $declaration_function_args['attrValue'] ?? [];
                            return "color: {$attr_value} !important;";
                        },
                    ]
                ),
                CommonStyle::style(
                    [
                        'selector'            => $order_class . ' .tmdivi-story div.tmdivi-label-small',
                        'attr'                => $attrs['child_story_sub_label_color']['advanced'] ?? [],
                        'declarationFunction' => function ( $declaration_function_args ) {
                            $attr_value = $declaration_function_args['attrValue'] ?? [];
                            return "color: {$attr_value} !important;";
                        },
                    ]
                ),
                CommonStyle::style(
                    [
                        'selector'            => $order_class . ' .tmdivi-story .tmdivi-icon',
                        'attr'                => $attrs['child_story_icon_background_color']['advanced'] ?? [],
                        'declarationFunction' => function ( $declaration_function_args ) {
                            $attr_value = $declaration_function_args['attrValue'] ?? [];
                            return "background-color: {$attr_value} !important;";
                        },
                    ]
                ),
                CssStyle::style(
                    [
                        'selector'  => $args['orderClass'],
                        'attr'      => $attrs['css'] ?? [],
                        'cssFields' => self::custom_css(),
                    ]
                ),

                // Title.
                $elements->style(
                    [
                        'attrName' => 'title',
                    ]
                ),

                // Content.
                $elements->style(
                    [
                        'attrName' => 'content',
                    ]
                ),

                // Icon.
                // CommonStyle::style(
                //     [
                //         'selector'            => $icon_selector,
                //         'attr'                => $attrs['icon']['innerContent'] ?? $parent_attrs_with_default['icon']['innerContent'] ?? [],
                //         'declarationFunction' => [ TimelineD5item::class, 'icon_font_declaration' ],
                //     ]
                // ),
                // CommonStyle::style(
                //     [
                //         'selector' => $icon_selector,
                //         'attr'     => $attrs['icon']['advanced']['color'] ?? $parent_attrs_with_default['icon']['advanced']['color'] ?? [],
                //         'property' => 'color',
                //     ]
                // ),
                // CommonStyle::style(
                //     [
                //         'selector' => $icon_selector,
                //         'attr'     => $attrs['icon']['advanced']['size'] ?? $parent_attrs_with_default['icon']['advanced']['size'] ?? [],
                //         'property' => 'font-size',
                //     ]
                // ),

                // ATTENTION: The code is intentionally added and commented in FE only as an example of expected value format.
                // If you have custom style processing, the style output should be passed as an `array` of style declarations
                // to the `styles` property of the `Style::add` method. For example:
                // [
                // 	[
                // 		'atRules'     => false,
                // 		'selector'    => $icon_selector,
                // 		'declaration' => 'color: red;'
                // 	],
                // 	[
                // 		'atRules'     => '@media only screen and (max-width: 767px)',
                // 		'selector'    => $icon_selector,
                // 		'declaration' => 'color: green;'
                // 	],
                // ],
            ],
        ]
        );  

    }
}