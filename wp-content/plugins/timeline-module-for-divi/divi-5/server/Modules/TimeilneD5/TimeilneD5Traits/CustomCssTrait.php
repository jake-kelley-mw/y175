<?php
namespace TMDIVI\Modules\TimeilneD5\TimeilneD5Traits;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

trait CustomCssTrait {
  public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'tmdivi/timeline' )->customCssFields;
	}
}