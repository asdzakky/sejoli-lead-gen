<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
Class LFB_Show_Leads {

    /**
     * Set Allowed Tags
     * @since   1.0.0
     */
    function expanded_alowed_tags() {
        $allowed = wp_kses_allowed_html( 'post' );

        // form fields - input
        $allowed['a'] = array(
            'href' => array(),
            'class'    => array(),
            'onclick'  => array(),
        );

        // form fields - input
        $allowed['input'] = array(
            'class' => array(),
            'id'    => array(),
            'name'  => array(),
            'value' => array(),
            'type'  => array(),
            'onclick' => array(),
        );

        $allowed['option'] = array(
            'value'    => array(),
            'selected'   => array(),
        );

        return $allowed;
    }

    /**
     * Convert Number into Phone Number Format
     * @since   1.0.0
     */
    function phone_number_format($nomorhp) {

        // Terlebih dahulu kita trim dl
        $nomorhp = trim($nomorhp);
        // Bersihkan dari karakter yang tidak perlu
        $nomorhp = strip_tags($nomorhp);     
        // Berishkan dari spasi
        $nomorhp= str_replace(" ","",$nomorhp);
        // Bersihkan dari bentuk seperti  (022) 66677788
        $nomorhp= str_replace("(","",$nomorhp);
        // Bersihkan dari format yang ada titik seperti 0811.222.333.4
        $nomorhp= str_replace(".","",$nomorhp); 

        //cek apakah mengandung karakter + dan 0-9
        if(!preg_match('/[^+0-9]/',trim($nomorhp))){
            // cek apakah no hp karakter 1-3 adalah +62
            if(substr(trim($nomorhp), 0, 3) == '+62'){
                $nomorhp= trim($nomorhp);
            }
            // cek apakah no hp karakter 1 adalah 0
            elseif(substr($nomorhp, 0, 1) == '0'){
                $nomorhp= '+62'.substr($nomorhp, 1);
            }
        }

        return $nomorhp;

    }

    /**
     * Show Form Leads
     * @since   1.0.0
    */
    function lfb_show_form_leads() {
        global $wpdb;
        $option_form = '';
        $first_form=0;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_16 = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ORDER BY id DESC ",'ACTIVE');
        $posts = $th_save_db->lfb_get_form_content($prepare_16);
        if (!empty($posts)) {
            foreach ($posts as $results) {
                $first_form++;
                $form_title = $results->form_title;
                $form_id = $results->id;
                if($first_form==1){
                    $first_form_id = $results->id;
                    if (get_option('lf-remember-me-show-lead') !== false ) {
                        $first_form_id = get_option('lf-remember-me-show-lead');
                        $get_form = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d ORDER BY id DESC ", $first_form_id);
                        $query_forms = $th_save_db->lfb_get_form_content($get_form);
                    }
                }
                $option_form .= '<option ' . ($first_form_id == $form_id ? 'selected="selected"' : "" ) . ' value=' . $form_id . '>' . $form_title . '</option>';
            }
        }
        $rem_nonce = wp_create_nonce( 'rem-nonce' );

        include_once( plugin_dir_path(__FILE__) . 'header.php' );

        $start = 0;
        $user_ID = get_current_user_id(); 
        $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($first_form_id, $user_ID, $start);

        $fieldData  = $getArray['fieldId'];
        $id         = $headcount = 1;
        $fieldIdNew = array();

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/show-lead-entries.php' );

        echo $html;
    }

    /**
     * Show Leads First Form
     * @since   1.0.0
     */
    function lfb_show_leads_first_form($form_id){
        $start = 0;

        $th_save_db = new LFB_SAVE_DB();
        $getArray = $th_save_db->lfb_get_all_view_leads_db($form_id,$start);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts      = $getArray['posts'];
        $rows       = $getArray['rows'];
        $limit      = $getArray['limit'];
        $fieldData  = $getArray['fieldId'];
        $tableHead  = '';
        $sn_counter = 0;
        $headcount  = 1;
        $leadscount = 5;

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/lead-entries-data.php' );

        echo $html;
        
    }

    /**
     * Show Leads by Affiliate
     * @since   1.0.0
     */
    function lfb_show_form_leads_by_affiliate() {
        global $wpdb;
        $option_form = '';
        $first_form=0;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_16 = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ORDER BY id DESC ", 'ACTIVE');
        $posts = $th_save_db->lfb_get_form_content($prepare_16);
        if (!empty($posts)) {
            foreach ($posts as $results) {
                $first_form++;
                $form_title = $results->form_title;
                $form_id = $results->id;
                if($first_form==1){
                    $first_form_id = $results->id;
                    if (get_option('lf-remember-me-show-lead') !== false ) {
                        $first_form_id = get_option('lf-remember-me-show-lead');
                        $get_form = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d ORDER BY id DESC ", $first_form_id);
                        $query_forms = $th_save_db->lfb_get_form_content($get_form);
                    }
                }
                $option_form .= '<option ' . ($first_form_id == $form_id ? 'selected="selected"' : "" ) . ' value=' . $form_id . '>' . $form_title . '</option>';
            }
        }
        $rem_nonce = wp_create_nonce( 'rem-nonce' );

        include_once( plugin_dir_path(__FILE__) . 'header.php' );

        $start = 0;
        $user_ID = get_current_user_id(); 
        $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($first_form_id, $user_ID, $start);

        $fieldData  = $getArray['fieldId'];
        $id         = $headcount = 1;
        $fieldIdNew = array();

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/show-lead-entries-affiliate.php' );

        echo $html;
    }

    /**
     * Show First Lead Form by Affiliate
     * @since   1.0.0
     */
    function lfb_show_leads_first_form_by_affiliate($form_id){
        $start = 0;

        $th_save_db = new LFB_SAVE_DB();
        $user_ID = get_current_user_id(); 
        $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($form_id, $user_ID, $start);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts      = $getArray['posts'];
        $rows       = $getArray['rows'];
        $limit      = $getArray['limit'];
        $fieldData  = $getArray['fieldId'];
        $tableHead  = '';
        $sn_counter = 0;
        $headcount  = 1;
        $leadscount = 5;

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/lead-entries-data-affiliate.php' );

        echo $html;

    }

    /**
     * Show All Leads
     * @since   1.0.0
     */
    function lfb_show_form_leads_datewise($form_id,$leadtype){
        $th_save_db = new LFB_SAVE_DB();

        $getArray =  $th_save_db->lfb_get_all_view_date_leads_db($form_id,$leadtype);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts          = $getArray['posts'];
        $rows           = $getArray['rows'];
        $limit          = $getArray['limit'];
        $fieldData       = $getArray['fieldId'];
        $sn_counter     = 0;
        $detail_view    = '';
        $id             = $headcount = 1;
        $fieldIdNew     = array();

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/lead-entries-datewise.php' );

        echo $html;
        
    }
    
}
