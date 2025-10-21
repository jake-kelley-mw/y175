<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: link
 *
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! class_exists( 'CTL_Field_link' ) ) {
	class CTL_Field_link extends CTL_Fields {


		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			 parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {

			$args = wp_parse_args(
				$this->field,
				array(
					'add_title'    => esc_html__( 'Add Link', 'cool-timeline' ),
					'edit_title'   => esc_html__( 'Edit Link', 'cool-timeline' ),
					'remove_title' => esc_html__( 'Remove Link', 'cool-timeline' ),
				)
			);

			$default_values = array(
				'url'    => '',
				'text'   => '',
				'target' => '',
			);

			$value = wp_parse_args( $this->value, $default_values );

			$hidden = ( ! empty( $value['url'] ) || ! empty( $value['url'] ) || ! empty( $value['url'] ) ) ? ' hidden' : '';

			$maybe_hidden = ( empty( $hidden ) ) ? ' hidden' : '';

			echo $this->field_before();

			echo '<textarea readonly="readonly" class="ctl--link hidden"></textarea>';

			echo '<div class="' . esc_attr( $maybe_hidden ) . '"><div class="ctl--result">' . sprintf( '{url:"%s", text:"%s", target:"%s"}', $value['url'], $value['text'], $value['target'] ) . '</div></div>';

			echo '<input type="hidden" name="' . esc_attr( $this->field_name( '[url]' ) ) . '" value="' . esc_attr( $value['url'] ) . '"' . $this->field_attributes( array( 'class' => 'ctl--url' ) ) . ' />';
			echo '<input type="hidden" name="' . esc_attr( $this->field_name( '[text]' ) ) . '" value="' . esc_attr( $value['text'] ) . '" class="ctl--text" />';
			echo '<input type="hidden" name="' . esc_attr( $this->field_name( '[target]' ) ) . '" value="' . esc_attr( $value['target'] ) . '" class="ctl--target" />';

			echo '<a href="#" class="button button-primary ctl--add' . esc_attr( $hidden ) . '">' . $args['add_title'] . '</a> ';
			echo '<a href="#" class="button ctl--edit' . esc_attr( $maybe_hidden ) . '">' . $args['edit_title'] . '</a> ';
			echo '<a href="#" class="button ctl-warning-primary ctl--remove' . esc_attr( $maybe_hidden ) . '">' . $args['remove_title'] . '</a>';

			echo $this->field_after();

		}

		public function enqueue() {

			if ( ! wp_script_is( 'wplink' ) ) {
				  wp_enqueue_script( 'wplink' );
			}

			if ( ! wp_script_is( 'jquery-ui-autocomplete' ) ) {
				wp_enqueue_script( 'jquery-ui-autocomplete' );
			}

			add_action( 'admin_print_footer_scripts', array( $this, 'add_wp_link_dialog' ) );

		}

		public function add_wp_link_dialog() {

			if ( ! class_exists( '_WP_Editors' ) ) {
				require_once ABSPATH . WPINC . '/class-wp-editor.php';
			}

			wp_print_styles( 'editor-buttons' );

			_WP_Editors::wp_link_dialog();

		}

	}
}

