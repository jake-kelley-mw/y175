<?php
namespace CoolTimelineProREG;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
|--------------------------------------
|   API DATA VERIFICATION SETTINGS PAGE
|--------------------------------------
*/
class CTP_Settings {
		private $verification_status;
		private $PREFIX;
		private $PLUGIN_NAME;
		private $PLUGIN_VER;
		private $AUTH_PAGE;
		private $PLUGIN_URL;
		private $settings_api;
		private $licenseMessage;
		private $Response;
		private $Base_File;
		private $plugin_purchase_url;
		private $plugin_documentation_url;
	public function __construct() {
		 $this->Base_File               = CTP_FILE;
		$this->PLUGIN_NAME              = CTP_ApiConf::PLUGIN_NAME;
		$this->PREFIX                   = CTP_ApiConf::PLUGIN_PREFIX;
		$this->AUTH_PAGE                = CTP_ApiConf::PLUGIN_AUTH_PAGE;
		$this->PLUGIN_URL               = CTP_ApiConf::PLUGIN_URL;
		$this->PLUGIN_VER               = CTP_ApiConf::PLUGIN_VERSION;
		$this->plugin_purchase_url      = 'https://1.envato.market/ct';
		$this->plugin_documentation_url = 'https://docs.coolplugins.net/doc/cool-timeline-pro/';
		$this->verification_status      = 'License is not verified yet! ';
		   $this->settings_api          = \cool_plugins_timeline_registration_Settings::init();
			   $this->settings_api->add_registration_page();
		add_action( 'admin_enqueue_scripts', array( $this, 'load_settings_scripts' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		 $this->settings_api->add_section( $this->PREFIX . '_license_registration', __( 'Cool Timeline Pro', 'cmb2' ) );
		$this->settings_api->add_field(
			$this->PREFIX . '_license_registration',
			array(
				array(
					'name'        => $this->PREFIX . '-purchase-code',
					'id'          => $this->PREFIX . '-purchase-code',
					'class'       => $this->PREFIX . '-settings-field required',
					'label'       => 'Enter License Key',
					'placeholder' => __( 'Enter Your License Key', 'cmb2' ),
					'type'        => 'text',
					'default'     => '',
				),
				array(
					'name'        => $this->PREFIX . '-client-emailid',
					'id'          => $this->PREFIX . '-client-emailid',
					'class'       => $this->PREFIX . '-settings-field required',
					'label'       => 'Enter Email Id',
					'desc'        => $this->save_purchase_code(),
					'placeholder' => get_option( 'admin_email' ),
					'type'        => 'text',
					'default'     => get_option( 'admin_email' ),
				),
				array(
					'name'    => $this->PREFIX . '-validate-purchase-code',
					'id'      => $this->PREFIX . '-validate-purchase-code',
					'class'   => $this->PREFIX . '-settings-field',
					'desc'    => $this->ValidatePurchase(),
					'type'    => 'html',
					'default' => '',
				),
				array(
					'name'    => $this->PREFIX . '-find-purchase-code',
					'id'      => $this->PREFIX . '-find-purchase-code',
					'class'   => $this->PREFIX . '-settings-field',
					'label'   => 'Important Points',
					'desc'    => $this->find_purchase_code(),
					'type'    => 'html',
					'default' => '',
				),
			)
		);
		add_action( 'admin_notices', array( $this, 'admin_registration_notice' ) );
		add_action( 'wp_ajax_' . $this->PREFIX . '_uninstall_license', array( $this, 'uninstall_license' ) );
	}
	/*
	|---------------------------------------------------
	|   Initialize settings
	|---------------------------------------------------
	*/
	public function admin_init() {
		  // initialize settings
			$this->settings_api->admin_init();
	}
	function find_purchase_code() {
		$html = '
		<h4 class="cool-license-q">Q1) Where can I find my license key?</h4>
		<p class="cool-license-a">
		<ol>
		<li>You can find license key inside your purchase order email or <strong>my-account</strong> section in the website from where you purchased the plugin, <a href="https://my.coolplugins.net/account/manage-license/" target="_blank">Check here</a>.</li>
		<li>Codecanyon users can find their license key by following these steps - <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank">check here</a>.</li>
		</ol>
		<br/>
		</p>

		<h4 class="cool-license-q">Q2) Can I use single site license on another domain?</h4>
		<p class="cool-license-a">You need to deactivate license from current active site to use it on another domain. Remember to deactivate license before moving your site to another domain or server.</p>

		<h4 class="cool-license-q">Q3) Having trouble in license activation?</h4>
		<p class="cool-license-a">Please contact support at <a href="mailto:contact@coolplugins.net?subject=License Activation Issue" target="_blank">contact@coolplugins.net</a> along with your license key and domain url.</p>
		';
		return $html;
	}
	function trouble_with_activation() {
		$html = '<div id="' . $this->PREFIX . '_registration_help_notice">Please contact support along with your license key and domain url at <a href="mailto:contact@coolplugins.net;">contact@coolplugins.net</a>.</div>';
		return $html;
	}
	function save_purchase_code() {
		$html = "<div id='" . $this->PREFIX . "-verify-permission'><span class='" . $this->PREFIX . "-notice-red'>&#9989; I agree to share my purchase code and email for plugin verification and to receive future updates notifications!</span></div><div id='" . $this->PREFIX . "-activation-button'>" . $this->settings_api->_return_submit_button( 'Verify Key' ) . "</div>
            <div id='" . $this->PREFIX . "-deactivation-button'><a id='" . $this->PREFIX . "-uninstall-license' class='button button-secondary button-hero'>Uninstall Licence</a><br/><span class='" . $this->PREFIX . "-notice-red uninstall'>(* Uninstall license to use it on other website or hosting.)</span></div>";
		  return $html;
	}
		/*
		|---------------------------------------------------------------
		| This function generate custom message on loading the settings
		|---------------------------------------------------------------
		*/
	public function ValidatePurchase() {
		$purchase    = $this->ce_get_option( $this->PREFIX . '-purchase-code' );
		$admin_email = $this->ce_get_option( $this->PREFIX . '-client-emailid' );
		if ( isset( $_GET['settings-updated'] ) || ! empty( $purchase ) ) {
			if ( ! empty( $purchase ) ) {
				$registration = "<div class='wrap'>";
				$registration = "<div class='" . $this->PREFIX . "-verification-notice'>
                    <p><strong>License Verification Status:</strong>";
				$verified     = CoolTimelineProBase::CheckWPPlugin( $purchase, $admin_email, $this->licenseMessage, $this->Response, $this->Base_File );
				if ( $verified && $this->Response->is_valid ) {
					$this->verification_status = 'Verified!';
					set_transient( $this->PREFIX . '_api_data_verification', 'done', 0 );
					$registration .= "<span class='" . $this->PREFIX . "_verification_enable'>&nbsp; &#9989; &nbsp;</span>";
				} else {
					$registration .= "<span class='" . $this->PREFIX . "_verification_disable'>&nbsp; &#10060; &nbsp;</span>";
					$this->flush_cache();
					$this->verification_status .= $this->licenseMessage;
				}
				$registration .= $this->verification_status;
				$registration .= '</p></div>';
				$registration .= "<p><strong>Developer's Support Validity Status:</strong>";
				$support_end   = isset( $this->Response->support_end ) ? $this->Response->support_end : '';

				if ( $support_end == 'Unlimited' ) {
					$registration .= "<span class='" . $this->PREFIX . "_verification_enable'>&nbsp; &#9989; Unlimited!</span>";
				} elseif ( $support_end == 'No Support' ) {
					$registration .= "<span class='" . $this->PREFIX . "_verification_disable'>&nbsp; &#10060; &nbsp; N/A</span>";
				} elseif ( $verified && $support_end !== '' ) {
					$date    = new \DateTime( $support_end );
					$nowDate = new \DateTime();
					$diff    = $date->diff( $nowDate );
					if ( ( $diff->y > 0 || $diff->m > 0 || $diff->d > 0 || $diff->h > 0 || $diff->i > 0 ) && $diff->invert == 0 ) {
						$registration .= "<span class='" . $this->PREFIX . "_verification_disable'>&nbsp; &#10060; &nbsp;</span>";
					} else {
						$registration .= "<span class='" . $this->PREFIX . "_verification_enable'>&nbsp; &#9989; &nbsp;</span>";
					}
					$registration .= $support_end . '</p></div>';
				} else {
					$registration .= "<span class='" . $this->PREFIX . "_verification_disable'>&nbsp; &#10060; &nbsp; N/A</span>";
				}

				$current_date      = date( 'Y-m-d H:i:s' );
				$current_date_time = strtotime( $current_date );
				$support_time      = ! empty( $this->Response->support_end ) ? strtotime( $this->Response->support_end ) : '';
				if ( ! empty( $support_time ) && $support_time < $current_date_time ) {
					$registration .= "<span class='" . $this->PREFIX . "-renew-support-notice'><ol><li>Your support validity has expired, please renew it to get support for plugin bugs and updates.</li><li>If you have already renewed your support then uninstall and install license once to update your support status.</li></ol></span>";
				}
				return $registration;
			} else {
				$empty_code = "<span class='" . $this->PREFIX . "-notice-red'>**Purchase code can not be empty!</span>";
				return $empty_code;
			}
		}
	}
		/*
		|---------------------------------------------------------
		|   Gather settings field-values like get_options()
		|---------------------------------------------------------
		*/
	public function ce_get_option( $option, $default = '' ) {
		$section = $this->PREFIX . '_license_registration';
		$options = get_option( $section );
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}
		return $default;
	}
		/*
		|---------------------------------------------------------
		|   Function accessed through AJAX
		|---------------------------------------------------------
		|	uninstall license
		|---------------------------------------------------------
		*/
	function uninstall_license() {
		$message = '';
		if ( wp_verify_nonce( $_REQUEST['_password'], 'purchase-verify' ) == true ) {
			$response = CoolTimelineProBase::RemoveLicenseKey( $this->Base_File, $message );
			if ( $response == false ) {
				die(
					json_encode(
						array(
							'Response' => '403',
							'Message'  => 'Unable to contact to the server at the moment.',
						)
					)
				);
			}
		} else {
			die(
				json_encode(
					array(
						'Response' => '403',
						'Message'  => 'Access denied due to expired/unauthorized url access.',
					)
				)
			);
		}
		$this->flush_cache();
		die(
			json_encode(
				array(
					'Response' => '200',
					'Message'  => $message,
				)
			)
		);
	}
		/*
		|----------------------------------------------------------------
		|   Admin registration notice for un-registered admin users only
		|----------------------------------------------------------------
		*/
	function admin_registration_notice() {
		if ( ! current_user_can( 'manage_options' ) || get_transient( $this->PREFIX . '_api_data_verification' ) == 'done' ) {
			return;
		}
		$curren_page       = get_current_screen();
		$parent_page_title = $curren_page->parent_base;
		$post_type         = $curren_page->post_type;

		$current_user = wp_get_current_user();
		$user_name    = $current_user->display_name;

		if ( $parent_page_title == 'cool-plugins-timeline-addon' || $post_type == 'cool_timeline' ) {
			?>			
			<div class="license-warning notice notice-error is-dismissible">
				<p>Hi, <strong><?php echo ucwords( $user_name ); ?></strong>! Please <strong><a href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=' . $this->AUTH_PAGE . '#' . $this->PREFIX . '_license_registration' ) ); ?>">enter and activate</a></strong> your license key for <strong><?php echo $this->PLUGIN_NAME; ?></strong> plugin for unrestricted and full access of all premium features.</p>
			</div>
			<?php
		}
	}
		/*
		|------------------------------------------------------------
		|   Load css/js script(s) file(s) for settings admin page
		|------------------------------------------------------------
		*/
	function load_settings_scripts() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->AUTH_PAGE ) {
			wp_enqueue_style( $this->PREFIX . '-settings-style', $this->PLUGIN_URL . 'admin/registration/css/api-auth-settings.css', null, $this->PLUGIN_VER );
			wp_enqueue_script( $this->PREFIX . '-settings-script', $this->PLUGIN_URL . 'admin/registration/js/api-auth-settings.js', array( 'jquery' ), $this->PLUGIN_VER );
			wp_localize_script(
				$this->PREFIX . '-settings-script',
				'ajax_object',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'verify'   => wp_create_nonce( 'purchase-verify' ),
				)
			);
		}

	}
		/*
		|-----------------------------------------------------------|
		|   Flush cache: All Home sweeping code must be here        |
		|   Run after license uninstall or failed verification      |
		|-----------------------------------------------------------|
		*/
	function flush_cache() {
		$settings = get_option( $this->PREFIX . '_license_registration' );
		unset( $settings[ $this->PREFIX . '-purchase-code' ] );
		update_option( $this->PREFIX . '_license_registration', $settings );
		delete_transient( $this->PREFIX . '_api_data_verification' );
	}

}   // end of class
