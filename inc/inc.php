<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    if(false === sejolisa_lead_check_valid_license() ) :
        return;
    endif;

    global $wpdb;
    
    // file include
    define('LFB_FORM_FIELD_TBL', $wpdb->prefix . 'lead_form');
    define('LFB_FORM_DATA_TBL', $wpdb->prefix . 'lead_form_data');
    include_once( plugin_dir_path(__FILE__) . 'lf-install.php' );
    include_once( plugin_dir_path(__FILE__) . 'lf-shortcode.php' );

    if ( is_admin() ) {
        include_once( plugin_dir_path(__FILE__) . 'edit-delete-form.php' );
        include_once( plugin_dir_path(__FILE__) . 'create-lead-form.php' );
    }

    include_once( plugin_dir_path(__FILE__) . 'email.php' );
    include_once( plugin_dir_path(__FILE__) . 'email-setting.php' );
    include_once( plugin_dir_path(__FILE__) . 'whatsapp.php' );
    include_once( plugin_dir_path(__FILE__) . 'whatsapp-setting.php' );
    // include_once( plugin_dir_path(__FILE__) . 'sms.php' );
    // include_once( plugin_dir_path(__FILE__) . 'sms-setting.php' );
    include_once( plugin_dir_path(__FILE__) . 'autoresponder-setting.php' );
    // include_once( plugin_dir_path(__FILE__) . 'follow-up-setting.php' );
    include_once( plugin_dir_path(__FILE__) . 'customer-setting.php' );
    include_once( plugin_dir_path(__FILE__) . 'show-forms-backend.php' );
    include_once( plugin_dir_path(__FILE__) . 'front-end.php' );
    include_once( plugin_dir_path(__FILE__) . 'show-lead.php' );
    include_once( plugin_dir_path(__FILE__) . 'lead-store-type.php' );
    include_once( plugin_dir_path(__FILE__) . 'ajax-functions.php' );
    include_once( plugin_dir_path(__FILE__) . 'member.php' );
    include_once( plugin_dir_path(__FILE__) . 'affiliate.php' );
?>