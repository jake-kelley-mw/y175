<?php
namespace TMDIVI\Modules\TimeilneD5\TimeilneD5Traits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;

trait ModuleClassnamesTrait {
  public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		$text_options_classnames = TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] );

		if ( $text_options_classnames ) {
			$classnames_instance->add( $text_options_classnames, true );
		}
	}
}