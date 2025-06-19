<?php

if (!function_exists('themehunk_admin_menu')) {

    include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
    define('LEADCAMPAIGN_PURL', plugin_dir_url(__FILE__));
    define('LEADCAMPAIGN_PDIR', plugin_dir_path(__FILE__));
    add_action('admin_menu',  'themehunk_admin_menu');
    add_action( 'admin_enqueue_scripts', 'admin_scripts');
    include_once LEADCAMPAIGN_PDIR . '../lf-install.php';

    function themehunk_admin_menu(){
        if(false === sejolisa_lead_check_valid_license() ) :
            return;
        endif;
        add_menu_page(__('Lead Campaign', 'sejoli-lead-form'), __('Lead Campaign', 'sejoli-lead-form'), 'manage_options', 'lead-forms', 'lfb_lead_form_page',  LEADCAMPAIGN_PURL . 'th-option/assets/images/icon.png', 35);
    }

    function admin_scripts( $hook ) {
        if ($hook === 'toplevel_page_themehunk-plugins'){
            wp_enqueue_style( 'themehunk-plugin-css', LEADCAMPAIGN_PURL . '/th-option/assets/css/started.css' );
            wp_enqueue_script('themehunk-plugin-js', LEADCAMPAIGN_PURL . '/th-option/assets/js/th-options.js',array( 'jquery', 'updates' ),'1', true);
        }
    }
    
}