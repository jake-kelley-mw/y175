<?php
/**
 * All modules.
 *
 * @package TMDIVI\Modules;
 */

namespace TMDIVI\Modules;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

use TMDIVI\Modules\TimeilneD5\TimeilneD5;
use TMDIVI\Modules\TimelineD5item\TimelineD5item;

add_action(
    'divi_module_library_modules_dependency_tree',
    function( $dependency_tree ) {
        $dependency_tree->add_dependency( new TimeilneD5() );
        $dependency_tree->add_dependency( new TimelineD5item() );
    }
);