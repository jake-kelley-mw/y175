<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('CTP_CRONJOB')) {
    class CTP_CRONJOB
    {
    

        public function __construct() {
           
       
          // Register cron jobs
            add_filter('cron_schedules', array($this, 'ctp_cron_schedules'));
            
    
            add_action('ctl_extra_data_update', array($this, 'ctp_cron_extra_data_autoupdater'));
        }
        
        function ctp_cron_extra_data_autoupdater() {
       
                if (class_exists('CTP_CRONJOB')) {
                    CTP_CRONJOB::ctp_send_data();
                }

        }
           
       static public function ctp_send_data() {
                   
            $feedback_url = CTP_FEEDBACK_API.'wp-json/coolplugins-feedback/v1/site';
            
            
            if (!defined('CTP_PLUGIN_DIR')  ) {
                
                return;
            }
           
            $extra_data_details = CoolTimelinePro::ctp_get_user_info();

            $server_info    = $extra_data_details['server_info'];
            $extra_details  = $extra_data_details['extra_details'];
            $site_url       = get_site_url();
            $install_date   = get_option('ctp-install-date');
            $uni_id         = '32';
            $site_id        = $site_url . '-' . $install_date . '-' . $uni_id;
            $initial_version = get_option('ctp_initial_save_version');
            $initial_version = is_string($initial_version) ? sanitize_text_field($initial_version) : 'N/A';
            $plugin_version = defined('CTLPV') ? CTLPV : 'N/A';
            $admin_email    = sanitize_email(get_option('admin_email') ?: 'N/A');
                  
            $post_data = array(

                'site_id'           => md5($site_id),
                'plugin_version'    => $plugin_version,
                'plugin_name'       => 'Cool Timeline Pro ',
                'plugin_initial'    => $initial_version,
                'email'             => $admin_email,
                'site_url'          => esc_url_raw($site_url),
                'server_info'       => $server_info,
                'extra_details'     => $extra_details,
            );
         
            
            
            $response = wp_remote_post($feedback_url, array(

                'method'    => 'POST',
                'timeout'   => 30,
                'headers'   => array(
                    'Content-Type' => 'application/json',
                ),
                'body'      => wp_json_encode($post_data),
            ));
          
            
            if (is_wp_error($response)) {

                error_log('ctp Feedback Send Failed: ' . $response->get_error_message());
                return;
            }
            
            $response_body  = wp_remote_retrieve_body($response);
            $decoded        = json_decode($response_body, true);
            if (!wp_next_scheduled('ctl_extra_data_update')) {

                wp_schedule_event(time(), 'every_30_days', 'ctl_extra_data_update');

            }
            
        }
          
        /**
         * Cron status schedule(s).
         */
        public function ctp_cron_schedules($schedules)
        {
           
            if (!isset($schedules['every_30_days'])) {

                $schedules['every_30_days'] = array(
                    'interval' => 30 * 24 * 60 * 60, // 2,592,000 seconds
                    'display'  => __('Once every 30 days'),
                );
            }

            return $schedules;
        }

      
    }

    $cron_init = new CTP_CRONJOB();
}
