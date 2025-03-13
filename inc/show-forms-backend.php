<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

Class LFB_SHOW_FORMS {

    /**
     * Form Nonce
     * @since   1.0.0
     */
    function lfb_show_form_nonce(){

        $nonce = wp_create_nonce( '_nonce_verify' );
        
        return $nonce;
        
    }

    /**
     * Show All Forms
     * @since   1.0.0
     */
    function lfb_show_all_forms($id) {

        $lfb_admin_url = admin_url();

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/show-all-forms.php' );

        echo $html;
        
    }

}