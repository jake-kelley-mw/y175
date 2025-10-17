<?php
/**
 * Static Module class.
 *
 * @package TMDIVI\Modules\TimeilneD5;
 */

namespace TMDIVI\Modules\TimeilneD5;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use TMDIVI\Modules\TimeilneD5\TimeilneD5Traits;

/**
 * Class TimeilneD5
 *
 * @package TMDIVI\Modules\TimeilneD5
 */
class TimeilneD5 implements DependencyInterface {

  use TimeilneD5Traits\RenderCallbackTrait;
  use TimeilneD5Traits\ModuleClassnamesTrait;
  use TimeilneD5Traits\ModuleStylesTrait;
  use TimeilneD5Traits\ModuleScriptDataTrait;

  public function load() {
    $module_json_folder_path = dirname( __DIR__, 3 ) . '/visual-builder/src/modules/Timeline';

    add_action(
      'init',
      function() use ( $module_json_folder_path ) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [ TimeilneD5::class, 'render_callback' ],
          ]
        );
      }
    );
  }
}