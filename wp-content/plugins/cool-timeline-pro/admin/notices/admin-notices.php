<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Admin notice class for WordPress plugin.
 * This class can not be initialized or extended.
 */

/**************************************************************************************************
 *  HOW TO USE.
 * After including this file, use the below example to start creating admin notice / review box
 *
 * Two arguments, id & message are required and can not be omitted.
 * id must be unique for every message or it will override the previous message with same id.
 *
 *               create a simple admin text message
 *   ctl_create_admin_notice( array('id'=>'bp-greeting-message','message'=>'Hey there!') );
 *
 *              create a admin text error message
 * ctl_create_admin_notice( array('id'=>'bp-error-message','message'=>'this is an example of error!','type'=>'error') );
 * The argument 'type' can be: error, success, warning
 *
 *              create a review box by passing minimum arguments
 * $slug = 'bp';
 * update_option($slug . '_activation_time', strtotime('now') ); // must create an activation time
 * ctl_create_admin_notice(
 *          array(
 *              'id'=>'bp_review_box',  // required and must be unique
 *              'slug'=>$slug,      // required in case of review box
 *              'review'=>true,     // required and set to be true for review box
 *              'review_url'=>'http://coolplugins.net', // required
 *              'plugin_name'=>'Boiler Plate Plugin',    // required
 *              'logo'=>'http://example.com/logo.png',   // optional: it will display logo
 *              'review_interval'=>5                    // optional: this will display review notice
 *                                                      //   after 5 days from the installation_time
 *                                                      // default is 3
 *          )
 * );
 *
 * NOTE: Review box does not be displayed unless the $slug _activation_time is equals or
 * more than the 3 days from current time. This can also be changed by setting 'review_interval' arguments
 *****************************************************************************************************
 */
if ( ! class_exists( 'ctl_admin_notices' ) ) :

	final class ctl_admin_notices {

		private static $instance = null;
		private $messages        = array();
		private $version         = '1.0.0';

		/**
		 * initialize the class with single instance
		 */
		public static function ctl_create_notice() {
			if ( ! empty( self::$instance ) ) {
				return self::$instance;
			}
			return self::$instance = new self();
		}

		/**
		 * add messages for admin notice
		 *
		 * @param array $notice this array contains $id,$message,$type,$class,$id
		 */
		public function ctl_add_message( $notice ) {
			if ( ! isset( $notice['id'] ) || empty( $notice['id'] ) ) {
				$this->ctl_show_error( 'id is required for integrating admin notice.' );
				return;
			}

			if ( isset( $notice['review'] ) && true != (bool) $notice['review'] && ( ! isset( $notice['message'] ) || empty( $notice['message'] ) ) ) {
				$this->ctl_show_error( 'message can not be null. You must provide some text for message field' );
				return;
			}

			$message         = ( isset( $notice['message'] ) && ! empty( $notice['message'] ) ) ? wp_kses_post( $notice['message'] ) : null;
			$type            = ( isset( $notice['type'] ) && ! empty( $notice['type'] ) ) ? 'notice-' . sanitize_key( $notice['type'] ) : 'notice-success';
			$class           = ( isset( $notice['class'] ) && ! empty( $notice['class'] ) ) ? sanitize_html_class( $notice['class'] ) : '';
			$review          = (bool) ( isset( $notice['review'] ) && ! empty( $notice['review'] ) ) ? true : false;
			$slug            = ( isset( $notice['slug'] ) && ! empty( $notice['slug'] ) ) ? sanitize_key( $notice['slug'] ) : '';
			$plugin_name     = ( isset( $notice['plugin_name'] ) && ! empty( $notice['plugin_name'] ) ) ? sanitize_text_field( $notice['plugin_name'] ) : '';
			$logo            = ( isset( $notice['logo'] ) && ! empty( $notice['logo'] ) ) ? esc_url_raw( $notice['logo'] ) : null;
			$review_url      = ( isset( $notice['review_url'] ) && ! empty( $notice['review_url'] ) ) ? esc_url_raw( $notice['review_url'] ) : '';
			$review_interval = ( isset( $notice['review_interval'] ) && ! empty( $notice['review_interval'] ) ) ? absint( $notice['review_interval'] ) : 3;

			if ( $review && ( empty( $slug ) || empty( $plugin_name ) || empty( $review_url ) ) ) {
				$this->ctl_show_error( 'slug / plugin_name / review_url can not be empty if admin notice is set to review' );
				return;
			}

			$this->messages[ $notice['id'] ] = array(
				'message'         => $message,
				'type'            => $type,
				'class'           => $class,
				'review'          => $review,
				'logo'            => $logo,
				'slug'            => $slug,
				'plugin_name'     => $plugin_name,
				'review_url'      => $review_url,
				'review_interval' => $review_interval,
			);

			add_action( 'admin_notices', array( $this, 'ctl_show_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'ctl_load_script' ) );
			add_action( 'wp_ajax_ctl_pro_admin_review_notice_dismiss', array( $this, 'ctl_admin_review_notice_dismiss' ) );
		}

		/**
		 * Load script to dismiss notices.
		 *
		 * @return void
		 */
		public function ctl_load_script() {
			wp_register_style( 'ctl-feedback-notice-styles', CTP_PLUGIN_URL . 'assets/css/ctl-admin-notices.css', array(), CTLPV, 'all' );
			wp_register_script( 'admin-notices-js', CTP_PLUGIN_URL . 'admin/notices/admin-notices.js', array( 'jquery' ), CTLPV, true );
			wp_enqueue_style( 'ctl-feedback-notice-styles' );
			wp_enqueue_script( 'admin-notices-js' );

		   if (!empty($this->messages)) {
            foreach ($this->messages as $id => $message) {
                $nonce = $message['review']
                    ? wp_create_nonce($id . '_review_nonce')
                    : wp_create_nonce($id . '_notice_nonce');

            $localized_data = array(
        'id'            => $id,
        'ajax_url'      => admin_url( 'admin-ajax.php' ),
        'wp_nonce'      => $nonce,
        'plugin_slug'   => $message['slug'] ?? $id,
        'review'        => $message['review'],
       'ajax_callback' => 'ctl_pro_admin_review_notice_dismiss',

    );
                $js_safe_id = str_replace('-', '_', $id);

                wp_localize_script('admin-notices-js', 'CtlNoticeData_' . $js_safe_id, $localized_data);
            }
    }
		
		}

		/**
		 * Create simple admin notice
		 */
		public function ctl_show_notice() {
			if ( count( $this->messages ) > 0 ) {
				foreach ( $this->messages as $id => $message ) {
					if ( true == (bool) $message['review'] ) {
						$this->ctl_admin_notice_for_review( $id, $message );
					} 
				}
			}
		}


		/**
		 * This function decides if it's good to show the review notice or not
		 * Review notice will only be displayed if $slug_activation_time is greater or equals to the 3 days
		 */
		private function ctl_admin_notice_for_review( $id, $messageObj ) {
			// Everyone should not be able to see the review message
			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}
			$slug = $messageObj['slug'];
			$days = $messageObj['review_interval'];

			if ( get_option( 'cool-timelne-pro-installDate' ) ) {
				$installation_date = date( 'Y-m-d h:i:s', strtotime( get_option( 'cool-timelne-pro-installDate' ) ) );
			} else {
				return;
			}

			$old_alreadyRated = get_option( 'cool-timelne-pro-ratingDiv' ) != false ? get_option( 'cool-timelne-pro-ratingDiv' ) : 'no';
			$alreadyRated     = get_option( 'cool-timeline-pro-already-rated' ) != false ? get_option( 'cool-timeline-pro-already-rated' ) : 'no';

			// check user already rated
			if ( $old_alreadyRated == 'yes' || $alreadyRated == 'yes' ) {
				return;
			}

			// grab plugin installation date and compare it with current date
			$display_date = date( 'Y-m-d h:i:s' );
			$install_date = new DateTime( $installation_date );
			$current_date = new DateTime( $display_date );
			$difference   = $install_date->diff( $current_date );
			$diff_days    = $difference->days;

			// check if installation days is greater than or equal to the review interval
			if ( isset( $diff_days ) && $diff_days >= $days ) {
				echo $this->ctl_create_notice_content( $id, $messageObj );
			}
		}

		/**
		 * Generate review notice HTML with all required CSS & JS
		 *
		 * @param array $messageObj array of a message object
		 **/
		function ctl_create_notice_content( $id, $messageObj ) {
			$ajax_url           = admin_url( 'admin-ajax.php' );
			$ajax_callback      = 'ctl_pro_admin_review_notice_dismiss';
			$wrap_cls           = 'notice notice-info is-dismissible';
			$img_path           = ( isset( $messageObj['logo'] ) && ! empty( $messageObj['logo'] ) ) ? esc_url( $messageObj['logo'] ) : null;
			$slug               = esc_attr( $messageObj['slug'] );
			$plugin_name        = esc_html( $messageObj['plugin_name'] );
			$like_it_text       = esc_html__( 'Rate Now! ★★★★★', 'cool-timeline' );
			$already_rated_text = esc_html__( 'Already Reviewed', 'cool-timeline' );
			$not_like_it_text   = esc_html__( 'Not Interested', 'cool-timeline' );
			$plugin_link        = esc_url( $messageObj['review_url'] );

			$review_nonce = wp_create_nonce( $id . '_review_nonce' );
			$message      = sprintf(
				"Thanks for using <b>%s</b> WordPress plugin. We hope you liked it ! <br> Please give us a quick rating, it works as a boost for us to keep working on more <a href='https://coolplugins.net' target='_blank'><strong>Cool Plugins</strong></a>!<br/>",
				$plugin_name,
				$plugin_link,
			);
			$html         = '<div data-ajax-url="%8$s" data-plugin-slug="%11$s" data-wp-nonce="%12$s" id="%13$s" data-ajax-callback="%9$s" class="%11$s-feedback-notice-wrapper %1$s">';

			if ( $img_path != null ) {
				$html .= '<div class="logo_container"><a href="%5$s"><img src="%2$s" alt="%3$s" style="max-width:80px;"></a></div>';
			}

			$html  .= '<div class="message_container">%4$s
        <div class="callto_action">
        <ul>
            <li class="love_it"><a href="%5$s" class="like_it_btn button button-primary" target="_new" title="%6$s">%6$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button %11$s_dismiss_notice" title="%7$s">%7$s</a></li>  
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button %11$s_dismiss_notice" title="%10$s">%10$s</a></li>    
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';


			return sprintf(
				$html,
				$wrap_cls,
				$img_path,
				$plugin_name,
				$message,
				$plugin_link,
				$like_it_text,
				$already_rated_text,
				$ajax_url, // 8
				$ajax_callback, // 9
				$not_like_it_text, // 10
				$slug, // 11
				$review_nonce, // 12
				$id // 13
			);
		}

		/**
		 * This function will dismiss the review notice.
		 * This is called by a WordPress ajax hook
		 */
		public function ctl_admin_review_notice_dismiss() {
			$slug      = isset( $_REQUEST['slug'] ) ? sanitize_text_field( $_REQUEST['slug'] ) : '';
			$id        = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
			$nonce_key = $id . '_review_nonce';

			if ( isset( $_REQUEST['_nonce'] ) && ! empty( $_REQUEST['_nonce'] ) && wp_verify_nonce( $_REQUEST['_nonce'], $nonce_key ) ) {
				update_option( 'cool-timeline-pro-already-rated', 'yes' );
				wp_send_json_success();
			} else {
				wp_send_json_error( array( 'message' => 'nonce verification failed!' ) );
			}
		}

	
		/**************************************************************
		 * This function is used by the class for displaying error    *
		 *  in case of wrong implementation of the class.             *
		 **************************************************************/
		private function ctl_show_error( $error_text ) {
			$er  = "<div style='text-align:center;margin-left:20px;padding:10px;background-color: #cc0000; color: #fce94f; font-size: x-large;'>";
			$er .= 'Error: ' . esc_html( $error_text );
			$er .= '</div>';
			echo $er;
		}

	}   // end of main class ctl_admin_notices;
endif;

/********************************************************************************
 * A global function to create admin notice/review box using the above class.   *
 * This function makes it easy to use above class                               *
 ********************************************************************************/
function ctl_create_admin_notice( $notice ) {
	// Do not initialize anything if it's not WordPress admin dashboard
	if ( ! is_admin() ) {
		return;
	}

	$main_class = ctl_admin_notices::ctl_create_notice();
	$main_class->ctl_add_message( $notice );
	return $main_class;
}
