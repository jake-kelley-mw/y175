<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Require php files.
require_once TMDIVI_DIR . 'divi-5/vendor/autoload.php';
require_once TMDIVI_DIR . 'divi-5/server/Modules/Modules.php';

/**
 * Enqueue Divi 5 Visual Builder Assets
 *
 * @since 1.0.0
 */

class Divi5_Visual_Builder_Assets {

  public function __construct(){
    add_action( 'divi_visual_builder_assets_before_enqueue_styles', array($this,'tmdivi_divi5_enqueue_visual_builder_assets') );
  }

  public function tmdivi_divi5_enqueue_visual_builder_assets() {
    if ( et_core_is_fb_enabled() && et_builder_d5_enabled() ) {
      wp_enqueue_script(
        'timeline-module-for-divi-visual-builder',
        TMDIVI_URL . 'divi-5/visual-builder/build/tmdivi-timeline-module-for-divi-conversion.js',
        [
          'react',
          'jquery',
          'divi-module-library',
          'wp-hooks',
          'divi-rest',
        ],
        '1.0.0',
        // true
        false
      );
      if (!wp_style_is('tmdivi-fontawesome-css', 'enqueued')) {
        wp_enqueue_style('tmdivi-fontawesome-css');
      }
    }
  }
}

