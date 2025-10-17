<?php
/**
 * Static Module class.
 *
 * @package TMDIVI\Modules\TimelineD5item;
 */

namespace TMDIVI\Modules\TimelineD5item;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use TMDIVI\Modules\TimelineD5item\TimelineD5itemTraits;

/**
 * Class TimelineD5item
 *
 * @package TMDIVI\Modules\TimelineD5item
 */
class TimelineD5item implements DependencyInterface {

  use TimelineD5itemTraits\RenderCallbackTrait;
  use TimelineD5itemTraits\ModuleClassnamesTrait;
  use TimelineD5itemTraits\ModuleStylesTrait;

  public function load() {
    
    $module_json_folder_path = dirname( __DIR__, 3 ) . '/visual-builder/src/modules/Timeline-item';

    add_action(
      'init',
      function() use ( $module_json_folder_path ) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [ TimelineD5item::class, 'render_callback' ],
          ]
        );
      }
    );
  }
}