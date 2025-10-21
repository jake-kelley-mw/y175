<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: icon
 *
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! class_exists( 'CTL_Field_icon' ) ) {
	class CTL_Field_icon extends CTL_Fields {


		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			 parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {

			$args = wp_parse_args(
				$this->field,
				array(
					'button_title' => esc_html__( 'Add Icon', 'cool-timeline' ),
					'remove_title' => esc_html__( 'Remove Icon', 'cool-timeline' ),
				)
			);

			echo $this->field_before();

			$nonce  = wp_create_nonce( 'ctl_icon_nonce' );
			$hidden = ( empty( $this->value ) ) ? ' hidden' : '';

			echo '<div class="ctl-icon-select">';
			echo '<span class="ctl-icon-preview' . esc_attr( $hidden ) . '"><i class="' . esc_attr( $this->value ) . '"></i></span>';
			echo '<a href="#" class="button button-primary ctl-icon-add" data-nonce="' . esc_attr( $nonce ) . '">' . $args['button_title'] . '</a>';
			echo '<a href="#" class="button ctl-warning-primary ctl-icon-remove' . esc_attr( $hidden ) . '">' . $args['remove_title'] . '</a>';
			echo '<input type="hidden" name="' . esc_attr( $this->field_name() ) . '" value="' . esc_attr( $this->value ) . '" class="ctl-icon-value"' . $this->field_attributes() . ' />';
			echo '</div>';

			echo $this->field_after();

		}

		public function enqueue() {

			add_action( 'admin_footer', array( 'CTL_Field_icon', 'add_footer_modal_icon' ) );
			add_action( 'customize_controls_print_footer_scripts', array( 'CTL_Field_icon', 'add_footer_modal_icon' ) );
		}

		public static function add_footer_modal_icon() {
			?>
	  <div id="ctl-modal-icon" class="ctl-modal ctl-modal-icon hidden">
		<div class="ctl-modal-table">
		  <div class="ctl-modal-table-cell">
			<div class="ctl-modal-overlay"></div>
			<div class="ctl-modal-inner">
			  <div class="ctl-modal-title">
					<?php esc_html_e( 'Add Icon', 'cool-timeline' ); ?>
				<div class="ctl-modal-close ctl-icon-close"></div>
			  </div>
			  <div class="ctl-modal-header">
				<input type="text" placeholder="<?php esc_html_e( 'Search...', 'cool-timeline' ); ?>" class="ctl-icon-search" />
			  </div>
			  <div class="ctl-modal-content">
				<div class="ctl-modal-loading"><div class="ctl-loading"></div></div>
				<div class="ctl-modal-load"></div>
			  </div>
			</div>
		  </div>
		</div>
	  </div>
			  <?php

		}

	}
}

