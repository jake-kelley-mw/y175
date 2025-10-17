<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

class TMDIVI_TimelineModuleForDivi extends DiviExtension {

	// protected function _enqueue_bundles() {
	// 	// Frontend Bundle
	// 	$site_url       = wp_parse_url( get_site_url() );
	// 	$hot_bundle_url = "http://localhost:3000/static/js/frontend-bundle.js";
	// 	wp_enqueue_script( "{$this->name}-frontend-bundle", $hot_bundle_url, $this->_bundle_dependencies['frontend'], $this->version, true );
	// 	if ( et_core_is_fb_enabled() ) {
	// 		// Builder Bundle
	// 		$hot_bundle_url = "http://localhost:3000/static/js/builder-bundle.js";
	// 		wp_enqueue_script( "{$this->name}-builder-bundle", $hot_bundle_url, $this->_bundle_dependencies['builder'], $this->version, true );
	// 	}
	// }
	
	// protected function _enqueue_debug_bundles() {
	// 	// Frontend Bundle
	// 	$site_url       = wp_parse_url( get_site_url() );
	// 	$hot_bundle_url = "http://localhost:3000/static/js/frontend-bundle.js";
	// 	wp_enqueue_script( "{$this->name}-frontend-bundle", $hot_bundle_url, $this->_bundle_dependencies['frontend'], $this->version, true );
	// 	if ( et_core_is_fb_enabled() ) {
	// 		// Builder Bundle
	// 		$hot_bundle_url = "http://localhost:3000/static/js/builder-bundle.js";
	// 		wp_enqueue_script( "{$this->name}-builder-bundle", $hot_bundle_url, $this->_bundle_dependencies['builder'], $this->versiosn, true );
	// 	}
	// }
	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'timeline-module-for-divi';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'timeline-module-for-divi';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = TMDIVI_V;

	/**
	 * TMDIVI_TimelineModuleForDivi constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'timeline-module-for-divi', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
	}
}

new TMDIVI_TimelineModuleForDivi;
