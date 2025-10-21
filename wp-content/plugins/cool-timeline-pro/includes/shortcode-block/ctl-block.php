<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hook scripts function into block editor hook
add_action( 'enqueue_block_editor_assets', 'ctl_pro_gutenberg_scripts' );

function ctl_pro_gutenberg_scripts() {
	$blockPath = '/dist/block.js';
	$stylePath = '/dist/block.css';

	if ( is_admin() ) {
		// Enqueue the bundled block JS file
		wp_enqueue_script(
			'ctl-block-js',
			plugins_url( $blockPath, __FILE__ ),
			array( 'wp-i18n', 'wp-blocks', 'wp-edit-post', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-plugins', 'wp-edit-post', 'wp-api' ),
			filemtime( plugin_dir_path( __FILE__ ) . $blockPath )
		);
		// Enqueue frontend and editor block styles
		wp_enqueue_style(
			'ctl-block-css',
			plugins_url( $stylePath, __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . $stylePath )
		);
		$urls = array(
			'baseURL'        => esc_url( home_url( '/' ) ), // Ensure URL is properly escaped
			'CTP_PLUGIN_URL' => esc_url( CTP_PLUGIN_URL ), // Ensure URL is properly escaped
		);

		wp_localize_script( 'ctl-block-js', 'ctlUrl', $urls );
	}
}

/**
 * Block Initializer.
 */
add_action(
	'plugins_loaded',
	function () {
		if ( function_exists( 'register_block_type' ) ) {
			// Hook server side rendering into render callback
			register_block_type(
				'cool-timleine/shortcode-block',
				array(
					'render_callback' => 'ctl_pro_block_callback',
					'attributes'      => array(
						'layout'               => array(
							'type'    => 'string',
							'default' => 'default',
						),
						'skin'                 => array(
							'type'    => 'string',
							'default' => 'default',
						),
						'storyDateVisibility'  => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'dateformat'           => array(
							'type'    => 'string',
							'default' => 'F j',
						),
						'customDateFormat'     => array(
							'type'    => 'string',
							'default' => '',
						),
						'postperpage'          => array(
							'type'    => 'string',
							'default' => '10',
						),
						'animation'            => array(
							'type'    => 'string',
							'default' => 'none',
						),
						'icons'                => array(
							'type'    => 'string',
							'default' => 'dot',
						),
						'designs'              => array(
							'type'    => 'string',
							'default' => 'design-1',
						),
						'storycontent'         => array(
							'type'    => 'string',
							'default' => 'short',
						),
						'category'             => array(
							'type'    => 'string',
							'default' => '',
						),
						'based'                => array(
							'type'    => 'string',
							'default' => 'default',
						),
						'compactelepos'        => array(
							'type'    => 'string',
							'default' => 'main-date',
						),
						'pagination'           => array(
							'type'    => 'string',
							'default' => 'main-date',
						),
						'hrNavigation'         => array(
							'type'    => 'string',
							'default' => 'hide',
						),
						'hrNavigationPosition' => array(
							'type'    => 'string',
							'default' => '',
						),
						'hrYearLabel'          => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'navigationStyle'      => array(
							'type'    => 'string',
							'default' => 'style-1',
						),
						'filters'              => array(
							'type'    => 'string',
							'default' => 'NO',
						),
						'filtercategories'     => array(
							'type'    => 'string',
							'default' => '',
						),
						'items'                => array(
							'type'    => 'string',
							'default' => '',
						),
						'starton'              => array(
							'type'    => 'string',
							'default' => '0',
						),
						'lineFilling'          => array(
							'type'    => 'string',
							'default' => 'false',
						),
						'readMoreVisibility'   => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'contentLength'        => array(
							'type'    => 'string',
							'default' => '50',
						),
						'autoplay'             => array(
							'type'    => 'string',
							'default' => 'false',
						),
						'autoplayspeed'        => array(
							'type'    => 'string',
							'default' => '3000',
						),
						'order'                => array(
							'type'    => 'string',
							'default' => 'DESC',
						),
						'timelineTitle'        => array(
							'type'    => 'string',
							'default' => '',
						),
						'settingsMigration'    => array(
							'type'    => 'string',
							'default' => 'true',
						),
					),
				)
			);
			// content timeline block
			register_block_type(
				'cool-content-timeline/ctl-shortcode-block',
				array(
					'render_callback' => 'ctl_content_tm_block_callback',
					'attributes'      => array(
						'layout'               => array(
							'type'    => 'string',
							'default' => 'default',
						),
						'skin'                 => array(
							'type'    => 'string',
							'default' => 'default',
						),
						'storyDateVisibility'  => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'dateformat'           => array(
							'type'    => 'string',
							'default' => 'F j',
						),
						'customDateFormat'     => array(
							'type'    => 'string',
							'default' => '',
						),
						'postperpage'          => array(
							'type'    => 'string',
							'default' => '10',
						),
						'animation'            => array(
							'type'    => 'string',
							'default' => 'none',
						),
						'icons'                => array(
							'type'    => 'string',
							'default' => 'dot',
						),
						'designs'              => array(
							'type'    => 'string',
							'default' => 'design-1',
						),
						'storycontent'         => array(
							'type'    => 'string',
							'default' => 'short',
						),
						'category'             => array(
							'type'    => 'string',
							'default' => '',
						),
						'compactelepos'        => array(
							'type'    => 'string',
							'default' => 'main-date',
						),
						'pagination'           => array(
							'type'    => 'string',
							'default' => 'main-date',
						),
						'hrNavigation'         => array(
							'type'    => 'string',
							'default' => 'hide',
						),
						'hrNavigationPosition' => array(
							'type'    => 'string',
							'default' => '',
						),
						'hrYearLabel'          => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'navigationStyle'      => array(
							'type'    => 'string',
							'default' => 'style-1',
						),
						'filters'              => array(
							'type'    => 'string',
							'default' => 'NO',
						),
						'filtercategories'     => array(
							'type'    => 'string',
							'default' => '',
						),
						'items'                => array(
							'type'    => 'string',
							'default' => '',
						),
						'starton'              => array(
							'type'    => 'string',
							'default' => '0',
						),
						'lineFilling'          => array(
							'type'    => 'string',
							'default' => 'false',
						),
						'readMoreVisibility'   => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'contentLength'        => array(
							'type'    => 'string',
							'default' => '50',
						),
						'postMetaVisibility'   => array(
							'type'    => 'string',
							'default' => 'hide',
						),
						'autoplay'             => array(
							'type'    => 'string',
							'default' => 'false',
						),
						'autoplayspeed'        => array(
							'type'    => 'string',
							'default' => '3000',
						),
						'order'                => array(
							'type'    => 'string',
							'default' => 'DESC',
						),
						'posttype'             => array(
							'type'    => 'string',
							'default' => 'post',
						),
						'taxonomy'             => array(
							'type'    => 'string',
							'default' => 'category',
						),
						'postcategory'         => array(
							'type'    => 'string',
							'default' => '',
						),
						'tags'                 => array(
							'type'    => 'string',
							'default' => '',
						),
						'metaKey'              => array(
							'type'    => 'string',
							'default' => '',
						),
						'settingsMigration'    => array(
							'type'    => 'string',
							'default' => 'true',
						),
					),
				)
			);
		}
	}
);

/**
 * Block Output.
 */
function ctl_pro_block_callback( $attr ) {
	extract( $attr );
	$hrNavPosition = ! empty( $attr['hrNavigationPosition'] ) ? $attr['hrNavigationPosition'] : ( 'horizontal' === $attr['layout'] ? 'left' : 'right' );
	if ( $layout == 'horizontal' ) {
		$shortcode_string = '[cool-timeline layout="%s" skin="%s" show-posts="%s" story-date="%s" date-format="%s" custom-date-format="%s" 
		icons="%s" designs="%s" category="%s" story-content="%s" based="%s" line-filling="%s" read-more="%s" content-length="%s"
		autoplay="%s" start-on="%s" items="%s" pagination="%s" year-navigation="%s" navigation-position="%s" year-label="%s" filters="%s" 
		filter-categories="%s" order="%s" autoplay-speed="%s" setting-migration="%s"]';
		$shortcode        = sprintf(
			$shortcode_string,
			esc_attr( $layout ),
			esc_attr( $skin ),
			esc_attr( $postperpage ),
			esc_attr( $storyDateVisibility ),
			sanitize_text_field( $dateformat ),
			sanitize_text_field( $customDateFormat ),
			esc_attr( $icons ),
			esc_attr( $designs ),
			esc_attr( $category ),
			esc_attr( $storycontent ),
			esc_attr( $based ),
			esc_attr( $lineFilling ),
			esc_attr( $readMoreVisibility ),
			intval( $contentLength ),
			esc_attr( $autoplay ),
			esc_attr( $starton ),
			esc_attr( $items ),
			esc_attr( $pagination ),
			esc_attr( $hrNavigation ),
			esc_attr( $hrNavPosition ),
			esc_attr( $hrYearLabel ),
			esc_attr( $filters ),
			esc_attr( $filtercategories ),
			esc_attr( $order ),
			esc_attr( $autoplayspeed ),
			esc_attr( $settingsMigration )
		);
		return $shortcode;
	} else {
		$shortcode_string = '[cool-timeline layout="%s" skin="%s" show-posts="%s" story-date="%s" date-format="%s" custom-date-format="%s"
		 icons="%s" animations="%s" designs="%s" category="%s" story-content="%s" based="%s" compact-ele-pos="%s" pagination="%s" 
		 year-navigation="%s" navigation-position="%s" year-label="%s" navigation-style="%s" filters="%s" filter-categories="%s" line-filling="%s" order="%s" 
		 read-more="%s" content-length="%s" timeline-title="%s" setting-migration="%s"]';
		$shortcode        = sprintf(
			$shortcode_string,
			esc_attr( $layout ),
			esc_attr( $skin ),
			esc_attr( $postperpage ),
			esc_attr( $storyDateVisibility ),
			sanitize_text_field( $dateformat ),
			sanitize_text_field( $customDateFormat ),
			esc_attr( $icons ),
			esc_attr( $animation ),
			esc_attr( $designs ),
			esc_attr( $category ),
			esc_attr( $storycontent ),
			esc_attr( $based ),
			esc_attr( $compactelepos ),
			esc_attr( $pagination ),
			esc_attr( $hrNavigation ),
			esc_attr( $hrNavPosition ),
			esc_attr( $hrYearLabel ),
			esc_attr( $navigationStyle ),
			esc_attr( $filters ),
			esc_attr( $filtercategories ),
			esc_attr( $lineFilling ),
			esc_attr( $order ),
			esc_attr( $readMoreVisibility ),
			intval( $contentLength ),
			esc_attr( $timelineTitle ),
			esc_attr( $settingsMigration )
		);
		return $shortcode;
	}
}

/**
 * Block Output.
 */
function ctl_content_tm_block_callback( $attr ) {
	extract( $attr );
	$hrNavPosition = ! empty( $attr['hrNavigationPosition'] ) ? $attr['hrNavigationPosition'] : ( 'horizontal' === $attr['layout'] ? 'left' : 'right' );
	if ( $layout == 'horizontal' ) {
		$shortcode_string = '[cool-content-timeline  layout="%s" skin="%s" show-posts="%s" story-date="%s" date-format="%s" custom-date-format="%s"
		 icons="%s" designs="%s" category="%s" story-content="%s" line-filling="%s" read-more="%s" content-length="%s" post-meta="%s" autoplay="%s"
		  start-on="%s" items="%s" pagination="%s" year-navigation="%s" navigation-position="%s" year-label="%s" filters="%s" filter-categories="%s"
		   order="%s" autoplay-speed="%s" post-type="%s" post-category="%s" tags="%s" taxonomy="%s" meta-key="%s" setting-migration="%s"]';
		$shortcode        = sprintf(
			$shortcode_string,
			esc_attr( $layout ),
			esc_attr( $skin ),
			esc_attr( $postperpage ),
			esc_attr( $storyDateVisibility ),
			sanitize_text_field( $dateformat ),
			sanitize_text_field( $customDateFormat ),
			esc_attr( $icons ),
			esc_attr( $designs ),
			esc_attr( $category ),
			esc_attr( $storycontent ),
			esc_attr( $lineFilling ),
			esc_attr( $readMoreVisibility ),
			intval( $contentLength ),
			esc_attr( $postMetaVisibility ),
			esc_attr( $autoplay ),
			esc_attr( $starton ),
			esc_attr( $items ),
			esc_attr( $pagination ),
			esc_attr( $hrNavigation ),
			esc_attr( $hrNavPosition ),
			esc_attr( $hrYearLabel ),
			esc_attr( $filters ),
			esc_attr( $filtercategories ),
			esc_attr( $order ),
			esc_attr( $autoplayspeed ),
			esc_attr( $posttype ),
			esc_attr( $postcategory ),
			esc_attr( $tags ),
			esc_attr( $taxonomy ),
			esc_attr( $metaKey ),
			esc_attr( $settingsMigration )
		);
		return $shortcode;
	} else {
		$shortcode_string = '[cool-content-timeline layout="%s" skin="%s" show-posts="%s" story-date="%s" date-format="%s" custom-date-format="%s"
		 icons="%s" animations="%s" designs="%s" category="%s" story-content="%s" compact-ele-pos="%s" pagination="%s" year-navigation="%s" navigation-position="%s" year-label="%s" navigation-style="%s" filters="%s" filter-categories="%s" line-filling="%s" order="%s" read-more="%s" content-length="%s"
		   post-meta="%s" post-type="%s" post-category="%s" tags="%s" taxonomy="%s" meta-key="%s" setting-migration="%s"]';
		$shortcode        = sprintf(
			$shortcode_string,
			esc_attr( $layout ),
			esc_attr( $skin ),
			esc_attr( $postperpage ),
			esc_attr( $storyDateVisibility ),
			sanitize_text_field( $dateformat ),
			sanitize_text_field( $customDateFormat ),
			esc_attr( $icons ),
			esc_attr( $animation ),
			esc_attr( $designs ),
			esc_attr( $category ),
			esc_attr( $storycontent ),
			esc_attr( $compactelepos ),
			esc_attr( $pagination ),
			esc_attr( $hrNavigation ),
			esc_attr( $hrNavPosition ),
			esc_attr( $hrYearLabel ),
			esc_attr( $navigationStyle ),
			esc_attr( $filters ),
			esc_attr( $filtercategories ),
			esc_attr( $lineFilling ),
			esc_attr( $order ),
			esc_attr( $readMoreVisibility ),
			intval( $contentLength ),
			esc_attr( $postMetaVisibility ),
			esc_attr( $posttype ),
			esc_attr( $postcategory ),
			esc_attr( $tags ),
			esc_attr( $taxonomy ),
			esc_attr( $metaKey ),
			esc_attr( $settingsMigration )
		);
		return $shortcode;
	}
}
