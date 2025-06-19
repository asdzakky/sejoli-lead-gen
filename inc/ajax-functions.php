<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Convert Number into Phone Number Format
 * @since   1.0.0
 */
function phone_number_format($nomorhp) {

    $nomorhp = trim($nomorhp);
    $nomorhp = strip_tags($nomorhp);     
    $nomorhp= str_replace(" ","",$nomorhp);
    $nomorhp= str_replace("(","",$nomorhp);
    $nomorhp= str_replace(".","",$nomorhp); 

    if(!preg_match('/[^+0-9]/',trim($nomorhp))) :
        if(substr(trim($nomorhp), 0, 3) == '+62') :
            $nomorhp= trim($nomorhp);
        elseif(substr($nomorhp, 0, 1) == '0') :
            $nomorhp= '+62'.substr($nomorhp, 1);
        endif;
    endif;

    return $nomorhp;

}

/**
 * Check User Permission
 * Hooked via action admin_menu
 * @since   1.0.0
 */
function lfb_user_permission_check(){

    $user = get_userdata(get_current_user_id());

    $user = wp_get_current_user();
    $allowed_roles = array('editor', 'administrator', 'lfb_role');
    return array_intersect($allowed_roles, $user->roles);

}

/**
 * File Upload Dir
 * @since   1.0.0
 */
function lfb_upload_dir($dirs){

    $dirs['subdir'] = '/sejoli-lead-campaign';
    $dirs['path'] = $dirs['basedir'] . '/sejoli-lead-campaign';
    $dirs['url'] = $dirs['baseurl'] . '/sejoli-lead-campaign';

    return $dirs;

}

/**
 * File Upload Process
 * Hooked via action wp_ajax_fileupload
 * @since   1.0.0
 */
function lfb_fileupload(){

    add_filter('upload_dir', 'lfb_upload_dir');

    $fileErrors = array(
        0 => __("There is no error, the file uploaded with success", "sejoli-lead-form"),
        1 => __("The uploaded file exceeds the upload_max_files in server settings", "sejoli-lead-form"),
        2 => __("The uploaded file exceeds the MAX_FILE_SIZE from html form", "sejoli-lead-form"),
        3 => __("The uploaded file uploaded only partially", "sejoli-lead-form"),
        4 => __("No file was uploaded", "sejoli-lead-form"),
        6 => __("Missing a temporary folder", "sejoli-lead-form"),
        7 => __("Failed to write file to disk", "sejoli-lead-form"),
        8 => __("A PHP extension stoped file to upload", "sejoli-lead-form")
    );

    $file_data = isset($_FILES) ? $_FILES : array();
    $overrides = array('test_form' => false);
    $response = array();

    foreach ($file_data as $key => $file) {

        $uploaded_file = wp_handle_upload($file, $overrides);

        if ($uploaded_file && !isset($uploaded_file['error'])) {
            $response[$key]['response'] = __('SUCCESS', 'sejoli-lead-form');
            $response[$key]['filename'] = basename($uploaded_file['url']);
            $response[$key]['url'] = $uploaded_file['url'];
            $response[$key]['type'] = $uploaded_file['type'];
        } else {
            $response[$key]['response'] = esc_html__("ERROR", 'sejoli-lead-form');
            $response[$key]['error'] = $uploaded_file['error'];
        }

    }

    $parse = http_build_query($response);

    wp_send_json($parse);

    remove_filter('upload_dir', 'lfb_upload_dir');

    die();

}
add_action('wp_ajax_fileupload', 'lfb_fileupload');
add_action('wp_ajax_nopriv_fileupload', 'lfb_fileupload');

/**
 * Save Lead collecting method
 * Hooked via action wp_ajax_SaveLeadSettings
 * @since   1.0.0
 */
function lfb_save_lead_settings(){

    $nonce = $_REQUEST['lrv_nonce_verify'];
    // Get all the user roles as an array.
    if (isset($_POST['action-lead-setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'lrv-nonce')) {

        global $wpdb;

        $data_recieve_method = intval($_POST['data-recieve-method']);
        $this_form_id = intval($_POST['action-lead-setting']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set storeType='" . $data_recieve_method . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveLeadSettings', 'lfb_save_lead_settings');

/**
 * Save Form Display Setting method
 * Hooked via action wp_ajax_SaveFormOptionSettings
 * @since   1.0.0
 */
function lfb_save_form_option_settings(){

    $nonce = $_REQUEST['fop_nonce_verify'];
    // Get all the user roles as an array.
    if (isset($_POST['action-form-option-setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'fop-nonce')) {
        
        global $wpdb;

        $data_form_option_method = intval($_POST['data-form-option-method']);
        $this_form_id = intval($_POST['action-form-option-setting']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set formDisplayOption='" . $data_form_option_method . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveFormOptionSettings', 'lfb_save_form_option_settings');

/**
 * Save Email Settings
 * Hooked via action wp_ajax_SaveEmailSettings
 * @since   1.0.0
 */
function lfb_save_email_settings(){

    $nonce = $_REQUEST['aes_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['email_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'aes-nonce')) {

        global $wpdb;

        $email_setting = array();
        $this_form_id = intval($_POST['email_setting']['form-id']);
        $email_setting['email_setting'] = isset($_POST['email_setting']) ? $_POST['email_setting'] : '';
        $serialize = maybe_serialize($email_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set mail_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }
        die();
    }

}
add_action('wp_ajax_SaveEmailSettings', 'lfb_save_email_settings');

/**
 * Save User Email Settings
 * Hooked via action wp_ajax_SaveUserEmailSettings
 * @since   1.0.0
 */
function lfb_SaveUserEmailSettings(){

    unset($_POST['action']);
    $mailArr = array();

    $nonce = $_REQUEST['ues_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['user_email_setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'ues-nonce')) {

        global $wpdb;

        $mailArr['user_email_setting'] = lfb_emailsettings_sanitize($_POST['user_email_setting']);

        $email_setting = maybe_serialize($mailArr);
        $this_form_id = intval($_POST['user_email_setting']['form-id']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set usermail_setting='" . $email_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            echo esc_html("updated");
        }
    }

    die();

}
add_action('wp_ajax_SaveUserEmailSettings', 'lfb_SaveUserEmailSettings');

/**
 * Save Affiliate Email Settings
 * Hooked via action wp_ajax_SaveAffiliateEmailSettings
 * @since   1.0.0
 */
function lfb_SaveAffiliateEmailSettings(){

    unset($_POST['action']);
    $mailArr = array();

    $nonce = $_REQUEST['affes_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['affiliate_email_setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'affes-nonce')) {

        global $wpdb;

        $mailArr['affiliate_email_setting'] = lfb_emailsettings_sanitize($_POST['affiliate_email_setting']);

        $email_setting = maybe_serialize($mailArr);
        $this_form_id = intval($_POST['affiliate_email_setting']['form-id']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set affiliatemail_setting='" . $email_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            echo esc_html("updated");
        }
    }

    die();

}
add_action('wp_ajax_SaveAffiliateEmailSettings', 'lfb_SaveAffiliateEmailSettings');

/**
 * Save Admin WhatsApp Settings
 * Hooked via action wp_ajax_SaveWaSettings
 * @since   1.0.0
 */
function lfb_save_wa_settings(){

    $nonce = $_REQUEST['awas_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['whatsapp_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'awas-nonce')) {

        global $wpdb;

        $whatsapp_setting = array();
        $this_form_id = intval($_POST['whatsapp_setting']['form-id']);
        $whatsapp_setting['whatsapp_setting'] = isset($_POST['whatsapp_setting']) ? $_POST['whatsapp_setting'] : '';
        $serialize = maybe_serialize($whatsapp_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set wa_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveWaSettings', 'lfb_save_wa_settings');

/**
 * Save User WhatsApp Settings
 * Hooked via action wp_ajax_SaveUserWaSettings
 * @since   1.0.0
 */
function lfb_save_user_wa_settings(){

    $nonce = $_REQUEST['uwas_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['user_wa_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'uwas-nonce')) {

        global $wpdb;

        $user_wa_setting = array();
        $this_form_id = intval($_POST['user_wa_setting']['form-id']);
        $user_wa_setting['user_wa_setting'] = isset($_POST['user_wa_setting']) ? $_POST['user_wa_setting'] : '';
        $serialize = maybe_serialize($user_wa_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set userwa_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveUserWaSettings', 'lfb_save_user_wa_settings');

/**
 * Save Affiliate WhatsApp Settings
 * Hooked via action wp_ajax_SaveAffiliateWaSettings
 * @since   1.0.0
 */
function lfb_save_affiliate_wa_settings(){

    $nonce = $_REQUEST['affwas_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['affiliate_wa_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'affwas-nonce')) {

        global $wpdb;

        $affiliate_wa_setting = array();
        $this_form_id = intval($_POST['affiliate_wa_setting']['form-id']);
        $affiliate_wa_setting['affiliate_wa_setting'] = isset($_POST['affiliate_wa_setting']) ? $_POST['affiliate_wa_setting'] : '';
        $serialize = maybe_serialize($affiliate_wa_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set affiliatewa_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveAffiliateWaSettings', 'lfb_save_affiliate_wa_settings');

/**
 * Save Admin SMS Settings
 * Hooked via action wp_ajax_SaveSMSSettings
 * @since   1.0.0
 */
function lfb_save_sms_settings(){

    $nonce = $_REQUEST['asmss_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['sms_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'asmss-nonce')) {

        global $wpdb;

        $sms_setting = array();
        $this_form_id = intval($_POST['sms_setting']['form-id']);
        $sms_setting['sms_setting'] = isset($_POST['sms_setting']) ? $_POST['sms_setting'] : '';
        $serialize = maybe_serialize($sms_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set sms_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
// add_action('wp_ajax_SaveSMSSettings', 'lfb_save_sms_settings');

/**
 * Save User SMS Settings
 * Hooked via action wp_ajax_SaveUserSMSSettings
 * @since   1.0.0
 */
function lfb_save_user_sms_settings(){

    $nonce = $_REQUEST['usmss_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['user_sms_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'usmss-nonce')) {

        global $wpdb;

        $user_sms_setting = array();
        $this_form_id = intval($_POST['user_sms_setting']['form-id']);
        $user_sms_setting['user_sms_setting'] = isset($_POST['user_sms_setting']) ? $_POST['user_sms_setting'] : '';
        $serialize = maybe_serialize($user_sms_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set usersms_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
// add_action('wp_ajax_SaveUserSMSSettings', 'lfb_save_user_sms_settings');

/**
 * Save Affiliate SMS Settings
 * Hooked via action wp_ajax_SaveAffiliateSMSSettings
 * @since   1.0.0
 */
function lfb_save_affiliate_sms_settings(){

    $nonce = $_REQUEST['affsmss_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['affiliate_sms_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'affsmss-nonce')) {

        global $wpdb;

        $affiliate_sms_setting = array();
        $this_form_id = intval($_POST['affiliate_sms_setting']['form-id']);
        $affiliate_sms_setting['affiliate_sms_setting'] = isset($_POST['affiliate_sms_setting']) ? $_POST['affiliate_sms_setting'] : '';
        $serialize = maybe_serialize($affiliate_sms_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set affiliatesms_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
// add_action('wp_ajax_SaveAffiliateSMSSettings', 'lfb_save_affiliate_sms_settings');

/**
 * Save Customer Email Settings
 * Hooked via action wp_ajax_SaveCustomerEmailSettings
 * @since   1.0.0
 */
function lfb_SaveCustomerEmailSettings(){

    unset($_POST['action']);
    $mailArr = array();

    $nonce = $_REQUEST['ces_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['customer_email_setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'ces-nonce')) {
        
        global $wpdb;

        $mailArr['customer_email_setting'] = lfb_emailsettings_sanitize($_POST['customer_email_setting']);

        $email_setting = maybe_serialize($mailArr);
        $this_form_id = intval($_POST['customer_email_setting']['form-id']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set customer_setting='" . $email_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            echo esc_html("updated");
        }

    }

    die();

}
add_action('wp_ajax_SaveCustomerEmailSettings', 'lfb_SaveCustomerEmailSettings');

/**
 * Save Customer WhatsApp Settings
 * Hooked via action wp_ajax_SaveCustomerWaSettings
 * @since   1.0.0
 */
function lfb_save_customer_wa_settings(){

    $nonce = $_REQUEST['cws_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['customer_wa_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'cws-nonce')) {

        global $wpdb;

        $customer_wa_setting = array();
        $this_form_id = intval($_POST['customer_wa_setting']['form-id']);
        $customer_wa_setting['customer_wa_setting'] = isset($_POST['customer_wa_setting']) ? $_POST['customer_wa_setting'] : '';
        $serialize = maybe_serialize($customer_wa_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set customer_wa_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveCustomerWaSettings', 'lfb_save_customer_wa_settings');

/**
 * Save Customer SMS Settings
 * Hooked via action wp_ajax_SaveCustomerSMSSettings
 * @since   1.0.0
 */
function lfb_save_customer_sms_settings(){

    $nonce = $_REQUEST['css_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['customer_sms_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'css-nonce')) {

        global $wpdb;

        $customer_sms_setting = array();
        $this_form_id = intval($_POST['customer_sms_setting']['form-id']);
        $customer_sms_setting['customer_sms_setting'] = isset($_POST['customer_sms_setting']) ? $_POST['customer_sms_setting'] : '';
        $serialize = maybe_serialize($customer_sms_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set customer_sms_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
// add_action('wp_ajax_SaveCustomerSMSSettings', 'lfb_save_customer_sms_settings');

/**
 * Save Captcha Keys
 * Hooked via action wp_ajax_SaveCaptchaSettings
 * @since   1.0.0
 */
function lfb_save_captcha_settings(){

    $nonce = $_POST['captcha_nonce'];

    if (isset($_POST['captcha-keys'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'captcha-nonce')) {

        $captcha_setting_sitekey = sanitize_text_field($_POST['captcha-setting-sitekey']);
        $captcha_setting_secret = sanitize_text_field($_POST['captcha-setting-secret']);

        if (get_option('captcha-setting-sitekey') !== false) {
            update_option('captcha-setting-sitekey', $captcha_setting_sitekey);
            update_option('captcha-setting-secret', $captcha_setting_secret);
        } else {
            add_option('captcha-setting-sitekey', $captcha_setting_sitekey);
            add_option('captcha-setting-secret', $captcha_setting_secret);
        }
    }

    die();

}
add_action('wp_ajax_SaveCaptchaSettings', 'lfb_save_captcha_settings');

/**
 * Save Autoresponder Settings
 * Hooked via action wp_ajax_SaveAutoresponderSettings
 * @since   1.0.0
 */
function lfb_save_autoresponder_settings(){

    $nonce = $_REQUEST['aaress_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['autoresponder_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'aaress-nonce')) {

        global $wpdb;

        $autoresponder_setting = isset($_POST['autoresponder_setting']['code']) ? $_POST['autoresponder_setting']['code'] : '';
        $this_form_id = intval($_POST['autoresponder_setting']['form-id']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set autoresponder_setting='" . $autoresponder_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveAutoresponderSettings', 'lfb_save_autoresponder_settings');

/**
 * Save Follow Up Settings
 * Hooked via action wp_ajax_SaveFollowUpSettings
 * @since   1.0.0
 */
function lfb_save_followup_settings(){

    $nonce = $_REQUEST['afs_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['followup_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'afs-nonce')) {

        global $wpdb;

        $followup_setting = isset($_POST['followup_setting']['message']) ? $_POST['followup_setting']['message'] : '';
        $this_form_id = intval($_POST['followup_setting']['form-id']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set followup_setting='" . $followup_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
// add_action('wp_ajax_SaveFollowUpSettings', 'lfb_save_followup_settings');

/**
 * Delete Leads from Backend
 * Hooked via action wp_ajax_delete_leads_backend
 * @since   1.0.0
 */
function lfb_delete_leads_backend(){

    $nonce = $_REQUEST['_lfbnonce'];
    // Get all the user roles as an array.

    $check = false;
    if (isset($_POST['lead_id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'lfb-nonce-rm')) {
        
        $check = true;

        global $wpdb;

        $this_lead_id = intval($_POST['lead_id']);
        $table_name = LFB_FORM_DATA_TBL;

        $update_query = $wpdb->prepare(" DELETE FROM $table_name WHERE id = %d ", $this_lead_id);

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_delete_form($update_query);

        echo esc_html($update_leads);

    }

    echo $check;

}
add_action('wp_ajax_delete_leads_backend', 'lfb_delete_leads_backend');

/**
 * Save captcha status for form ON/OFF
 * Hooked via action wp_ajax_SaveCaptchaOption
 * @since   1.0.0
 */
function lfb_save_captcha_option(){

    $nonce = $_POST['captcha_nonce'];

    if (isset($_POST['captcha_on_off_form_id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'captcha-nonce')) {

        global $wpdb;

        $captcha_option = sanitize_text_field($_POST['captcha-on-off-setting']);
        $this_form_id = intval($_POST['captcha_on_off_form_id']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set captcha_status='" . $captcha_option . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

    }

    die();

}
add_action('wp_ajax_SaveCaptchaOption', 'lfb_save_captcha_option');

/**
 * Save Thankyou Settings
 * Hooked via action wp_ajax_SaveThankyouSettings
 * @since   1.0.0
 */
function lfb_save_thankyou_settings(){

    $nonce = $_REQUEST['thankyou-nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['thankyou_settings']['form-id']) && wp_verify_nonce($nonce, 'thankyou-nonce')) {

        global $wpdb;

        $thankyou_settings = array();
        $this_form_id = intval($_POST['thankyou_settings']['form-id']);
        $thankyou_settings['thankyou_settings'] = isset($_POST['thankyou_settings']) ? $_POST['thankyou_settings'] : '';
        $serialize = maybe_serialize($thankyou_settings);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set multiData='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveThankyouSettings', 'lfb_save_thankyou_settings');

/**
 * Show all Leads column on Lead Page Based on form selection
 * Hooked via action wp_ajax_ShowAllLeadThisForm
 * @since   1.0.0
 */
function lfb_ShowAllLeadThisForm(){

    if ((isset($_POST['form_id']) && ($_POST['form_id'] != '')) || (isset($_GET['form_id']) && ($_GET['form_id'] != ''))) {

        global $wpdb, $wp;

        $table_name = LFB_FORM_DATA_TBL;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $nonce = wp_create_nonce('lfb-nonce-rm');
        $showLeadsObj = new LFB_Show_Leads();
        $start = 0;
        $limit = 10;
        $detail_view  = '';
        $slectleads = false;

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $start = ($id - 1) * $limit;
            $form_id = intval($_GET['form_id']);
            $sn_counter = $start;
        } else {
            $id = 1;
            $form_id = intval($_POST['form_id']);
            $sn_counter = 0;
        }

        if (isset($_GET['startDate'])) {
            $startDate = sanitize_text_field($_GET['startDate']);
        }

        if (isset($_GET['endDate'])) {
            $endDate = sanitize_text_field($_GET['endDate']);
        }

        if (isset($_GET['detailview'])) {
            $detail_view = sanitize_text_field($_GET['detailview']);
        }

        if (isset($_GET['detailview'])) {
            $detail_view = sanitize_text_field($_GET['detailview']);
        }

        if (isset($_POST['slectleads'])) {
            $slectleads = sanitize_text_field($_POST['slectleads']);
        }

        $user_ID = get_current_user_id();

        if(wp_get_referer() === home_url('member-area/lead-entries/')) {
            if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
                $getArray = $th_save_db->lfb_get_affiliate_filter_leads_db($form_id, $user_ID, $startDate, $endDate);
            } else {
                $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($form_id, $user_ID, $start);
            }
        } else {
            if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
                $getArray = $th_save_db->lfb_get_all_filter_leads_db($form_id, $startDate, $endDate);
            } else {
                $getArray = $th_save_db->lfb_get_all_view_leads_db($form_id, $start);
            }
        }

        $posts      = $getArray['posts'];
        $rows       = $getArray['rows'];
        $limit      = isset($getArray['limit']) ? $getArray['limit'] : '';
        $fieldData  = $getArray['fieldId'];
        $tableHead  = '';
        $headcount  = 1;
        $leadscount = 5;

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/ajax/show-all-leads-data.php' );

        echo $html;

        die();
    }

}
add_action('wp_ajax_ShowAllLeadThisForm', 'lfb_ShowAllLeadThisForm');

/**
 * Show all Leads by affiliate column on Lead Page Based on form selection
 * Hooked via action wp_ajax_ShowAllLeadThisFormByAffiliate
 * @since   1.0.0
 */
function lfb_ShowAllLeadThisFormByAffiliate(){

    if ((isset($_POST['form_id']) && ($_POST['form_id'] != '')) || (isset($_GET['form_id']) && ($_GET['form_id'] != ''))) {
        
        global $wpdb, $wp;
        
        $table_name = LFB_FORM_DATA_TBL;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $nonce = wp_create_nonce('lfb-nonce-rm');
        $showLeadsObj = new LFB_Show_Leads();
        $start = 0;
        $limit = 10;
        $detail_view  = '';
        $slectleads = false;

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $start = ($id - 1) * $limit;
            $form_id = intval($_GET['form_id']);
            $sn_counter = $start;
        } else {
            $id = 1;
            $form_id = intval($_POST['form_id']);
            $sn_counter = 0;
        }

        if (isset($_GET['detailview'])) {
            $detail_view = sanitize_text_field($_GET['detailview']);
        }

        if (isset($_POST['slectleads'])) {
            $slectleads = sanitize_text_field($_POST['slectleads']);
        }

        $user_ID = get_current_user_id(); 

        if(wp_get_referer() === home_url('member-area/lead-entries/')) {
            $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($form_id, $user_ID, $start);
        } else {
            $getArray = $th_save_db->lfb_get_all_view_leads_db($form_id, $start);
        }

        $posts      = $getArray['posts'];
        $rows       = $getArray['rows'];
        $limit      = $getArray['limit'];
        $fieldData  = $getArray['fieldId'];
        $tableHead  = '';
        $headcount  = 1;
        $leadscount = 5;

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/ajax/show-all-leads-affiliate-data.php' );

        echo $html;

        die();
    }

}
add_action('wp_ajax_ShowAllLeadThisFormByAffiliate', 'lfb_ShowAllLeadThisFormByAffiliate');

/**
 * Show Leads Page
 * Hooked via action wp_ajax_ShowALeadPagi
 * @since   1.0.0
 */
function lfb_ShowLeadPagi(){

    if ((isset($_POST['form_id']) && ($_POST['form_id'] != '')) || (isset($_GET['form_id']) && ($_GET['form_id'] != ''))) {
        
        global $wpdb;

        $table_name = LFB_FORM_DATA_TBL;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $showLeadsObj = new LFB_Show_Leads();
        $nonce = wp_create_nonce('lfb-nonce-rm');
        $start = 0;
        $limit = 10;
        $detail_view = '';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $start = ($id - 1) * $limit;
            $form_id = intval($_GET['form_id']);
            $sn_counter = $start;
        } else {
            $id = 1;
            $form_id = intval($_POST['form_id']);
            $sn_counter = 0;
        }
        if (isset($_GET['detailview'])) {
            $detail_view = isset($_GET['detailview']);
        }

        $getArray  = $th_save_db->lfb_get_all_view_leads_db($form_id, $start);
        $posts     = $getArray['posts'];
        $rows      = $getArray['rows'];
        $limit     = $getArray['limit'];
        $fieldData = $getArray['fieldId'];
        $tableHead = '';
        $headcount = 1;

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/ajax/show-all-leads-page-data.php' );

        echo $html;

        die();
    }

}
add_action('wp_ajax_ShowLeadPagi', 'lfb_ShowLeadPagi');

/**
 * Show Leads on Lead Page Based on form selection
 * Hooked via action wp_ajax_ShowAllLeadThisFormDate
 * @since   1.0.0
 */
function lfb_ShowAllLeadThisFormDate(){
   
    if ((isset($_POST['form_id']) && ($_POST['form_id'] != '')) || (isset($_GET['form_id']) && ($_GET['form_id'] != ''))) {
        
        global $wpdb;
        
        $nonce = wp_create_nonce('lfb-nonce-rm');
        $table_name = LFB_FORM_DATA_TBL;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $showLeadsObj = new LFB_Show_Leads();
        $start = 0;
        $limit = 10;
        $detail_view = '';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $datewise = sanitize_text_field($_GET['datewise']);
            $start = ($id - 1) * $limit;
            $form_id = intval($_GET['form_id']);
            $sn_counter = $start;
        } else {
            $id = 1;
            $datewise = '';
            $sn_counter = 0;
        }

        if (isset($_GET['detailview'])) {
            $detail_view = sanitize_text_field($_GET['detailview']);
        }

        $getArray   =  $th_save_db->lfb_get_all_view_date_leads_db($form_id, $datewise, $start);
        $posts      = $getArray['posts'];
        $rows       = $getArray['rows'];
        $limit      = $getArray['limit'];
        $fieldData  = $getArray['fieldId'];
        $fieldIdNew = array();
        $headcount  = 1;
        $tableHead  = '';

        $html = '';
                
        require_once( LFB_PLUGIN_DIR . 'template/ajax/show-all-leads-date-data.php' );

        echo $html;
        
        die();
    }

}
add_action('wp_ajax_ShowAllLeadThisFormDate', 'lfb_ShowAllLeadThisFormDate');

/**
 * Save from Data from front-end
 * @since   1.0.0
 */
function lfb_form_name_email_filter($form_data){
   
    $name_email = array();
    $e = false;
    $n = false;
    $p = false;
    foreach ($form_data as $key => $value) {
        $email = strpos($key, 'email_');
        $name = strpos($key, 'name_');
        $phone = strpos($key, 'phonenumber_');
        if ($email !== false) {
            $name_email['email'] = $value;
            $e = true;
        } elseif ($name !== false) {
            $name_email['name'] = $value;
            $n = true;
        }elseif ($phone !== false) {
            $name_email['phonenumber'] = phone_number_format($value);
            $p = true;
        }
        if ($e === true && $n === true && $p === true) {
            break;
        }
    }

    return $name_email;

}

/**
 * Show Leads Sanitize
 * @since   1.0.0
 */
function lfb_lead_sanitize($leads){

    if (is_array($leads)) {

        foreach ($leads as $key => $value) {

            $rKey = preg_replace("/[^a-zA-Z]+/", "", $key);

            if ($rKey === 'name' || $rKey === 'text' || $rKey === 'radio' || $rKey === 'option') {
                $leads[$key] = sanitize_text_field($value);
            } elseif ($rKey === 'email') {
                $leads[$key] = sanitize_email($value);
            } elseif ($rKey === 'number') {
                $leads[$key] = intval($value);
            } elseif ($rKey === 'message' || $rKey === 'textarea') {
                $leads[$key] = sanitize_textarea_field($value);
            } elseif ($rKey === 'date' || $rKey === 'dob') {
                $leads[$key] = sanitize_text_field($value);
            } elseif ($rKey === 'url') {
                $leads[$key] = esc_url_raw($value);
            } elseif ($rKey === 'checkbox') {
                foreach ($value as $ckey => $cvalue) {
                    $value[$ckey] = sanitize_text_field($cvalue);
                }
                $leads[$key] = $value;
            }

        } // end foreach

        return $leads;

    }

}

function lfb_get_previous_submit_data($form_product, $form_id) {

    global $wpdb;

    $limit_minute = 0;
    $unix_limit   = current_time( 'timestamp' ) - ( $limit_minute * 60 );
    $day_limit    = date('Y-m-d H:i:s', $unix_limit);

    $table_name = LFB_FORM_DATA_TBL;

    $rows = $wpdb->prepare(" SELECT * FROM $table_name WHERE form_id = %d AND product = %d AND `date` > '".$day_limit."'", $form_id, $form_product);
    $posts = $wpdb->get_results($rows);

    return $posts;

}

function lfb_check_user_has_submited_data($form_product, $form_id) {

    global $wpdb;

    $table_name = LFB_FORM_DATA_TBL;
    
    wp_parse_str($_POST['fdata'], $fromData);

    $en = lfb_form_name_email_filter($fromData);

    if( $en['email'] && $en['phonenumber'] ) :

        $rows = $wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE form_id = $form_id 
            AND product = $form_product
            AND (form_data LIKE '%" . $en['email'] . "%' AND form_data LIKE '%" . str_replace("+62","0", $en['phonenumber']) . "%')
            OR form_data LIKE '%" . $en['email'] . "%' 
            OR form_data LIKE '%" . str_replace("+62","0", $en['phonenumber']) . "%'
        ");

    elseif( $en['email'] ) :

        $rows = $wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE form_id = $form_id 
            AND product = $form_product
            AND (form_data LIKE '%" . $en['email'] . "%')
        ");

    elseif( $en['phonenumber'] ) :

        $rows = $wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE form_id = $form_id 
            AND product = $form_product
            AND (form_data LIKE '%" . str_replace("+62","0", $en['phonenumber']) . "%')
        ");

    endif;

    $posts = $wpdb->get_results($rows);

    return $posts;

}

/**
 * Save Form Data
 * Hooked via action wp_ajax_Save_Form_Data, wp_ajax_nopriv_Save_Form_Data
 * @since   1.0.0
 */
function lfb_Save_Form_Data(){

    if (isset($_POST['fdata'])) {

        wp_parse_str($_POST['fdata'], $fromData);

        $form_id = intval($fromData['hidden_field']);
        $form_title = $fromData['form_title'];
        $form_product = intval($fromData['product']);
        $form_affiliate = intval($fromData['affiliate_id']);
        unset($fromData['g-recaptcha-response']);
        unset($fromData['action']);
        unset($fromData['hidden_field']);
        unset($fromData['product']);
        unset($fromData['affiliate_id']);

        $en = lfb_form_name_email_filter($fromData);

        if ((isset($en['email'])) && ($en['email'] != '')) {
            $user_emailid = sanitize_email($en['email']);
        } else {
            $user_emailid = esc_html__('invalid_email', 'sejoli-lead-form');
        }

        $entries                     = lfb_get_previous_submit_data($form_product, $form_id);
        $get_previous_submit_data_ip = isset($entries[0]->ip_address) ? $entries[0]->ip_address : '';
        $user_entries                = lfb_check_user_has_submited_data($form_product, $form_id);
        $get_user_has_submit_data_ip = isset($user_entries[0]->ip_address) ? $user_entries[0]->ip_address : '';
        $lp                          = new LFB_LeadStoreType();

        if ( $get_previous_submit_data_ip ) {

            wp_send_json(__('sudah isi data'));

        } else if ( $get_user_has_submit_data_ip ) {

            wp_send_json(__('data sudah ada'));

        } else {

            $sanitize_leads =  lfb_lead_sanitize($fromData);
            $form_data = maybe_serialize($sanitize_leads);

            $lf_store   = new LFB_LeadStoreType();
            $th_save_db = new LFB_SAVE_DB();

            $lf_store->lfb_mail_type($form_id, $form_title, $form_product, $form_affiliate, $form_data, $th_save_db, $user_emailid);
            
            lfb_register_autoresponder($form_id, $form_product, $form_data);

        }

    }

    die();

}
add_action('wp_ajax_Save_Form_Data', 'lfb_Save_Form_Data');
add_action('wp_ajax_nopriv_Save_Form_Data', 'lfb_Save_Form_Data');

/**
 * Register buyer to selected autoresponder setup.
 * @param  array  $order_data [description]
 * @return [type]             [description]
 */
function lfb_register_autoresponder($form_id, $product, $form_data) {

    $product = sejolisa_get_product( intval($product) );

    $th_save_db = new LFB_SAVE_DB();
    $posts = $th_save_db->lfb_get_form_data($form_id);

    $code = $posts[0]->autoresponder_setting;
    if($code) :

        $autoresponder = sejolisa_parsing_form_html_code( $code );
        
        if( false !== $autoresponder['valid'] ) :

            $body_fields = [];

            $form = $th_save_db->lfb_get_form_data($form_id);
            $form_datas = maybe_unserialize($form[0]->form_data);

            $form_field = $th_save_db->lfb_admin_email_send($form_id);
            $form_data = maybe_unserialize($form_data);

            foreach ($form_datas as $results) {

                $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
                $field_id = $results['field_id'];

                $data_email = '';
                $data_name  = '';
                $data_phone  = '';

                if ($type === 'email') { 
                    $data_email = $form_data['email_'.$field_id];
                } elseif($type === 'name') { 
                    $data_name = $form_data['name_'.$field_id];
                } elseif ( $type === 'phonenumber' ) {
                    $data_phone = phone_number_format($form_data['phonenumber_'.$field_id]);
                }

            }

            foreach($autoresponder['fields'] as $field) :

                if('email' === $field['type']) :
                    $body_fields[$field['name']] = $data_email;
                elseif('name' === $field['type']) :
                    $body_fields[$field['name']] = $data_name;
                else :
                    $body_fields[$field['name']] = $field['value'];
                endif;

            endforeach;

            $response = wp_remote_post( $autoresponder['form']['action'][0], [
                'method'  => 'POST',
                'timeout' => 30,
                'headers' => array(
                    'Referer'    => site_url(),
                    'User-Agent' => $_SERVER['HTTP_USER_AGENT']
                ),
                'body' => $body_fields
            ]);

            do_action('sejoli/log/write', 'response autoresponder subscription', [
                'url'         => $autoresponder['form']['action'][0],
                'body_fields' => $body_fields,
                'response'    => strip_tags(wp_remote_retrieve_body($response))
            ]);

        endif;

    endif;

}

/**
 * Verify Form Captcha
 * Hooked via action wp_ajax_verifyFormCaptcha, wp_ajax_nopriv_verifyFormCaptcha
 * @since   1.0.0
 */
function lfb_verifyFormCaptcha(){
    
    if ((isset($_POST['captcha_res'])) && (!empty($_POST['captcha_res']))) {
        $captcha = stripslashes($_POST['captcha_res']);
        $secret_key = get_option('captcha-setting-secret');
        
        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            array(
                'method' => 'POST',
                'body' => array(
                    'secret' => $secret_key,
                    'response' => $captcha
                )
            )
        );
        
        $reply_obj = json_decode(wp_remote_retrieve_body($response));
        if (isset($reply_obj->success) && $reply_obj->success == 1) {
            esc_html_e('Yes', 'sejoli-lead-form');
        } else {
            esc_html_e('No', 'sejoli-lead-form');
        }
    } else {
        esc_html_e('Invalid', 'sejoli-lead-form');
    }

    die();

}
add_action('wp_ajax_verifyFormCaptcha', 'lfb_verifyFormCaptcha');
add_action('wp_ajax_nopriv_verifyFormCaptcha', 'lfb_verifyFormCaptcha');

/**
 * Remember This Form
 * Hooked via action wp_ajax_RememberMeThisForm
 * @since   1.0.0
 */
function lfb_RememberMeThisForm(){

    $nonce = $_POST['rem_nonce'];

    if (isset($_POST['form_id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'rem-nonce')) {

        $remember_me = intval($_POST['form_id']);

        if (get_option('lf-remember-me-show-lead') !== false) {
            update_option('lf-remember-me-show-lead', $remember_me);
        } else {
            add_option('lf-remember-me-show-lead', $remember_me);
        }
        echo esc_html(get_option('lf-remember-me-show-lead'));

        die();
    }

}
add_action('wp_ajax_RememberMeThisForm', 'lfb_RememberMeThisForm');

/**
 * Save Email Settings
 * @since   1.0.0
 */
function lfb_emailsettings_sanitize($email_settings){

    $email_settings['from'] = sanitize_email($email_settings['from']);
    $email_settings['header'] = sanitize_text_field($email_settings['header']);
    $email_settings['subject'] = sanitize_text_field($email_settings['subject']);
    $email_settings['message'] = sanitize_textarea_field($email_settings['message']);
    $email_settings['user-email-setting-option'] = sanitize_text_field($email_settings['user-email-setting-option']);
    $email_settings['affiliate-email-setting-option'] = sanitize_text_field($email_settings['affiliate-email-setting-option']);
    $email_settings['form-id'] = intval($email_settings['form-id']);

    return $email_settings;

}

/**
 * Send Data into Customer Email
 * @since   1.0.0
 */
function lfb_customeremail_send($form_data,$form_id,$lead_id,$form_product,$form_affiliate,$customer_emailid){

    $th_save_db = new LFB_SAVE_DB();
    $leadform_store = new LFB_LeadStoreType();
    $form_entry_data = $leadform_store->lfb_mail_filter($form_id,$form_data);
    $posts = $th_save_db->lfb_mail_store_type($form_id);
    $storeType = $posts[0]->storeType;
    $customer_setting = $posts[0]->customer_setting;
    $customermail = maybe_unserialize($customer_setting);
    $customer_email = array('customer_email_settings'=>$customermail,'emailid'=>$customer_emailid);
    $customer_email['leads'] = $form_entry_data;
    $form_entry_data .= "<br/>";

    $customer_setting = $customer_email['customer_email_settings'];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $to = $customer_email['emailid'];
    $header = (isset($customer_setting['customer_email_setting']['header']))?$customer_setting['customer_email_setting']['header']:'Submit Form';
    $subject = (isset($customer_setting['customer_email_setting']['subject']))?$customer_setting['customer_email_setting']['subject']:'';
    $message = (isset($customer_setting['customer_email_setting']['message']))?$customer_setting['customer_email_setting']['message']:'';
    $shortcodes_a = '[lf-new-form-data]';
    $shortcodes_b = $form_entry_data; 
    $shortcode_form_name = '[form-name]';
    $shortcode_lead_id = '[lead-id]';
    $shortcode_lead_name = '[lead-name]';
    $shortcode_lead_email = '[lead-email]';
    $shortcode_lead_phone = '[lead-phone]';
    $shortcode_affiliate_name = '[affiliate-name]';
    $shortcode_affiliate_phone = '[affiliate-phone]';
    $shortcode_affiliate_email = '[affiliate-email]';
    $shortcode_product_name = '[product-name]';
    $shortcode_product_price = '[product-price]';

    if($form_affiliate > 0) {
        $affiliate       = sejolisa_get_user($form_affiliate);
        $affiliate_email = $affiliate->user_email;
        $affiliate_name  = $affiliate->display_name;
        $affiliate_phone = $affiliate->meta->phone;
    } else {
        $affiliate_email = '';
        $affiliate_name  = '';
        $affiliate_phone = '';
    }            

    if($form_product > 0) {
        $product = sejolisa_get_product($form_product);
        $product_name  = $product->post_title;
        $product_price = trim(sejolisa_price_format($product->price));
    } else {
        $product_name  = '';
        $product_price = '';
    }
    $th_save_db = new LFB_SAVE_DB();
    $form = $th_save_db->lfb_get_form_data($form_id);
    $form_datas = maybe_unserialize($form[0]->form_data);
    $form_title = $form[0]->form_title;

    $form_field = $th_save_db->lfb_admin_email_send($form_id);
    $form_data  = maybe_unserialize($form_data);
    $lead_email = '';
    $lead_name  = '';
    $lead_phone = '';

    foreach ($form_datas as $results) {

        $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
        $field_id = $results['field_id'];

        if ($type === 'email') { 
            $lead_email = $form_data['email_'.$field_id];
        } elseif($type === 'name') { 
            $lead_name = $form_data['name_'.$field_id];
        } elseif ( $type === 'phonenumber' ) {
            $lead_phone = '+62'.$form_data['phonenumber_'.$field_id];
        }

    }

    $message = ($message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $message);
    $message = str_replace($shortcode_form_name, $form_title, $message);
    $message = str_replace($shortcode_lead_id, $lead_id, $message);
    $message = str_replace($shortcode_lead_name, $lead_name, $message);
    $message = str_replace($shortcode_lead_email, $lead_email, $message);
    $message = str_replace($shortcode_lead_phone, $lead_phone, $message);
    $message = str_replace($shortcode_affiliate_name, $affiliate_name, $message);
    $message = str_replace($shortcode_affiliate_email, $affiliate_email, $message);
    $message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $message);
    $message = str_replace($shortcode_product_name, $product_name, $message);
    $message = str_replace($shortcode_product_price, $product_price, $message);

    $customer_email_setting_form = (isset($customer_setting['customer_email_setting']['form']))?$customer_setting['customer_email_setting']['form']:'';

    $headers[] = "From:".$header." <".$customer_email_setting_form.">";
    $headers[] = "Reply-To:".$header." <".$customer_email_setting_form.">";  
    $attachments = [];
    
    if($message && $to && $subject) {
        $email = new LeadFormEmail();
        $email->send(
            array($to),
            $message,
            $subject, 
            $attachments
        );
    }

}

/**
 * Send Data into WhatsApp
 * @since   1.0.0
 */
function lfb_send_data_wa($form_id,$form_data,$lead_id,$form_product,$form_affiliate){
    
    $th_save_db           = new LFB_SAVE_DB();
    $leadform_store       = new LFB_LeadStoreType();
    $form_entry_data      = $leadform_store->lfb_data_filter($form_id,$form_data);
    $posts                = $th_save_db->lfb_mail_store_type($form_id);
    $storeType            = $posts[0]->storeType;
    $customer_wa_setting  = $posts[0]->customer_wa_setting;
    $customerwa           = maybe_unserialize($customer_wa_setting);
    $customer_wa          = array('customer_wa_settings'=>$customerwa);
    $customer_wa['leads'] = $form_entry_data;
    $form_entry_data     .= "<br/>";

    if(!empty($customer_wa['customer_wa_settings'])){

        $get_customer_wa_number = $leadform_store->lfb_wa_filter($form_id,$form_data);
        $th_save_db = new LFB_SAVE_DB();
        $user_message = $customer_wa['customer_wa_settings']['customer_wa_setting']['message'];

        $shortcodes_a = '[lf-new-form-data]';
        $shortcodes_b = $form_entry_data; 
        $shortcode_form_name = '[form-name]';
        $shortcode_lead_id = '[lead-id]';
        $shortcode_lead_name = '[lead-name]';
        $shortcode_lead_email = '[lead-email]';
        $shortcode_lead_phone = '[lead-phone]';
        $shortcode_affiliate_name = '[affiliate-name]';
        $shortcode_affiliate_phone = '[affiliate-phone]';
        $shortcode_affiliate_email = '[affiliate-email]';
        $shortcode_product_name = '[product-name]';
        $shortcode_product_price = '[product-price]';

        if($form_affiliate > 0) {
            $affiliate       = sejolisa_get_user($form_affiliate);
            $affiliate_email = $affiliate->user_email;
            $affiliate_name  = $affiliate->display_name;
            $affiliate_phone = $affiliate->meta->phone;
        } else {
            $affiliate_email = '';
            $affiliate_name  = '';
            $affiliate_phone = '';
        }            

        if($form_product > 0) {
            $product = sejolisa_get_product($form_product);
            $product_name  = $product->post_title;
            $product_price = trim(sejolisa_price_format($product->price));
        } else {
            $product_name  = '';
            $product_price = '';
        }

        $th_save_db = new LFB_SAVE_DB();
        $form = $th_save_db->lfb_get_form_data($form_id);
        $form_datas = maybe_unserialize($form[0]->form_data);
        $form_title = $form[0]->form_title;

        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data  = maybe_unserialize($form_data);
        $lead_email = '';
        $lead_name  = '';
        $lead_phone = '';

        foreach ($form_datas as $results) {

            $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
            $field_id = $results['field_id'];

            if ($type === 'email') { 
                $lead_email = $form_data['email_'.$field_id];
            } elseif($type === 'name') { 
                $lead_name = $form_data['name_'.$field_id];
            } elseif ( $type === 'phonenumber' ) {
                $lead_phone = '+62'.$form_data['phonenumber_'.$field_id];
            }

        }

        $user_message = ($user_message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $user_message);
        $user_message = str_replace($shortcode_form_name, $form_title, $user_message);
        $user_message = str_replace($shortcode_lead_id, $lead_id, $user_message);
        $user_message = str_replace($shortcode_lead_name, $lead_name, $user_message);
        $user_message = str_replace($shortcode_lead_email, $lead_email, $user_message);
        $user_message = str_replace($shortcode_lead_phone, $lead_phone, $user_message);
        $user_message = str_replace($shortcode_affiliate_name, $affiliate_name, $user_message);
        $user_message = str_replace($shortcode_affiliate_email, $affiliate_email, $user_message);
        $user_message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $user_message);
        $user_message = str_replace($shortcode_product_name, $product_name, $user_message);
        $user_message = str_replace($shortcode_product_price, $product_price, $user_message);

        if($user_message) {
            $whatsapp = new LeadFormWhatsApp();
            $whatsapp->send($get_customer_wa_number, strip_tags($user_message), $title = '');
        }

    }

}

/**
 * Send Data into SMS
 * @since   1.0.0
 */
function lfb_send_data_sms($form_id,$form_data,$lead_id,$form_product,$form_affiliate){
    
    $th_save_db            = new LFB_SAVE_DB();
    $leadform_store        = new LFB_LeadStoreType();
    $form_entry_data       = $leadform_store->lfb_data_filter($form_id,$form_data);
    $posts                 = $th_save_db->lfb_mail_store_type($form_id);
    $storeType             = $posts[0]->storeType;
    $customer_sms_setting  = $posts[0]->customer_sms_setting;
    $customersms           = maybe_unserialize($customer_sms_setting);
    $customer_sms          = array('customer_sms_settings'=>$customersms);
    $customer_sms['leads'] = $form_entry_data;
    $form_entry_data      .= "<br/>";

    //user sms send
    if(!empty($customer_sms['customer_sms_settings'])){
        $get_customer_sms_number = $leadform_store->lfb_wa_filter($form_id,$form_data);
        $th_save_db = new LFB_SAVE_DB();
        $user_message = $customer_sms['customer_sms_settings']['customer_sms_setting']['message'];
        
        $shortcodes_a = '[lf-new-form-data]';
        $shortcodes_b = $form_entry_data; 
        $shortcode_form_name = '[form-name]';
        $shortcode_lead_id = '[lead-id]';
        $shortcode_lead_name = '[lead-name]';
        $shortcode_lead_email = '[lead-email]';
        $shortcode_lead_phone = '[lead-phone]';
        $shortcode_affiliate_name = '[affiliate-name]';
        $shortcode_affiliate_phone = '[affiliate-phone]';
        $shortcode_affiliate_email = '[affiliate-email]';
        $shortcode_product_name = '[product-name]';
        $shortcode_product_price = '[product-price]';

        if($form_affiliate > 0) {
            $affiliate       = sejolisa_get_user($form_affiliate);
            $affiliate_email = $affiliate->user_email;
            $affiliate_name  = $affiliate->display_name;
            $affiliate_phone = $affiliate->meta->phone;
        } else {
            $affiliate_email = '';
            $affiliate_name  = '';
            $affiliate_phone = '';
        }            

        if($form_product > 0) {
            $product = sejolisa_get_product($form_product);
            $product_name  = $product->post_title;
            $product_price = trim(sejolisa_price_format($product->price));
        } else {
            $product_name  = '';
            $product_price = '';
        }

        $th_save_db = new LFB_SAVE_DB();
        $form = $th_save_db->lfb_get_form_data($form_id);
        $form_datas = maybe_unserialize($form[0]->form_data);
        $form_title = $form[0]->form_title;

        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data  = maybe_unserialize($form_data);
        $lead_email = '';
        $lead_name  = '';
        $lead_phone = '';

        foreach ($form_datas as $results) {

            $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
            $field_id = $results['field_id'];

            if ($type === 'email') { 
                $lead_email = $form_data['email_'.$field_id];
            } elseif($type === 'name') { 
                $lead_name = $form_data['name_'.$field_id];
            } elseif ( $type === 'phonenumber' ) {
                $lead_phone = '+62'.$form_data['phonenumber_'.$field_id];
            }

        }

        $user_message = ($user_message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $user_message);
        $user_message = str_replace($shortcode_form_name, $form_title, $user_message);
        $user_message = str_replace($shortcode_lead_id, $lead_id, $user_message);
        $user_message = str_replace($shortcode_lead_name, $lead_name, $user_message);
        $user_message = str_replace($shortcode_lead_email, $lead_email, $user_message);
        $user_message = str_replace($shortcode_lead_phone, $lead_phone, $user_message);
        $user_message = str_replace($shortcode_affiliate_name, $affiliate_name, $user_message);
        $user_message = str_replace($shortcode_affiliate_email, $affiliate_email, $user_message);
        $user_message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $user_message);
        $user_message = str_replace($shortcode_product_name, $product_name, $user_message);
        $user_message = str_replace($shortcode_product_price, $product_price, $user_message);

        if($user_message) {
            $sms = new LeadFormSMS();
            $sms->send($get_customer_sms_number, strip_tags($user_message), $title = '');
        }
    }

}


/**
 * Proceed to Customer
 * Hooked via action wp_ajax_ProceedToCustomer
 * @since   1.0.0
 */
function lfb_ProceedToCustomer(){

    global $wpdb;

    if ( isset( $_POST['lead-id'] )  && lfb_user_permission_check() ) {

        $leadID     = intval( $_POST['lead-id'] );
        $th_save_db = new LFB_SAVE_DB();
        $getArray   = $th_save_db->lfb_get_single_leads_db( $leadID );
        $posts      = $getArray['posts'];

        foreach ( $posts as $results ) {

            $form_data    = $results->form_data;
            $lead_id      = $results->id;
            $product_id   = $results->product;
            $affiliate_id = $results->affiliate;
            $affiliate    = sejolisa_get_user( $affiliate_id );
            $form_data    = maybe_unserialize( $form_data );
            $form_id      = $results->form_id;
            $lead_date    = date("j M Y", strtotime( $results->date ));
            $get_status   = $results->status;

            $form = $th_save_db->lfb_get_form_data( $results->form_id );
            $form_data_result = maybe_unserialize( $form[0]->form_data );
            $email = $phone_number = $email = '';

            foreach( $form_data_result as $results ) {

                $type = isset( $results['field_type']['type'] ) ? $results['field_type']['type'] : '';

                if ( $type === 'phonenumber' ) {

                    $field_id = $results['field_id'];
                    $phone_number = isset( $form_data['phonenumber_'.$field_id] ) ? phone_number_format($form_data['phonenumber_'.$field_id]) : '';
                
                }

                if ( $type === 'email' ) {
                
                    $field_id = $results['field_id'];
                    $email = isset( $form_data['email_'.$field_id] ) ? $form_data['email_'.$field_id] : '';
                
                }
                
                if ( $type === 'name' ) {
                
                    $field_id = $results['field_id'];
                    $name = isset( $form_data['name_'.$field_id] ) ? $form_data['name_'.$field_id] : '';
                
                }

            }

        }
        
        if($email) {
            $userMail = $email;
        } else {
            if($_SERVER['HTTP_HOST'] === 'localhost'){
                $userMail = $phone_number.'@'.$_SERVER['HTTP_HOST'].'.com';
            } else {
                $userMail = $phone_number.'@'.$_SERVER['HTTP_HOST'];
            }
        }

        $product = sejolisa_get_product( $product_id );
        $enable_register = $valid = true;

        if( $product->type === "physical" ) :
            $user = sejolisa_get_user( $phone_number );
        else:
            $user = sejolisa_get_user( $userMail );
        endif;

        $user_id = ($user) ? $user->ID : null;

        $post_data = [
            'product_id'    => $product_id,
            'user_id'       => $user_id,
            'user_name'     => $name,
            'user_email'    => $userMail,
            'user_password' => NULL,
            'user_phone'    => $phone_number,
            'affiliate_id'  => $affiliate_id
        ];

        $post_data = wp_parse_args($post_data, [
            'user_id'            => NULL,
            'affiliate_id'       => NULL,
            'coupon'             => NULL,
            'payment_gateway'    => 'manual',
            'quantity'           => 1,
            'user_email'         => NULL,
            'user_name'          => NULL,
            'user_password'      => NULL,
            'postal_code'        => NULL,
            'user_phone'         => NULL,
            'shipment'           => NULL,
            'markup_price'       => NULL,
            'shipping_own_value' => NULL,
            'product_id'         => NULL,
            'meta_data'          => [],
            'address'            => NULL,
            'variants'           => NULL,
            'wallet'             => NULL,
        ]);

        $product = sejolisa_get_product( $post_data['product_id'] );

        if( is_a( $product, 'WP_Post' ) ) :

            // validate product
            $valid = apply_filters( 'sejoli/checkout/is-product-valid', $valid, $product, $post_data );

            // get user data by checkout data
            // if the value is in valid, then later need to register
            // $user_data = apply_filters( 'sejoli/checkout/user-data', false, $post_data );
            $user_data = false;

            // validate shipping
            $valid = apply_filters( 'sejoli/checkout/is-shipping-valid', $valid, $product, $post_data );

            // validate coupon
            $valid = apply_filters( 'sejoli/checkout/is-coupon-valid', $valid, $product, $post_data, 'checkout' );

            // validate variant
            $valid = apply_filters( 'sejoli/variant/are-variants-valid', $valid, $post_data );

            $password_field = boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_password_field'));

            if ( !is_user_logged_in() && false !== $password_field && false === $user_data ) :
                   
                $valid = apply_filters( 'sejoli/checkout/is-user-data-valid', $valid, $post_data );
                    
            endif;

            // Processing checkout complete
            // Everything is valid
            // Now we move to order 

            if( false !== $valid ) :

                $order_data = [
                    'product_id'         => $product->ID,
                    'quantity'           => $post_data['quantity'],
                    'payment_gateway'    => $post_data['payment_gateway'],
                    'meta_data'          => $post_data['meta_data'],
                    'type'               => 'regular',
                    'affiliate_id'       => $affiliate_id,
                    'coupon'             => $post_data['coupon'],
                    'shipment'           => $post_data['shipment'],
                    'markup_price'       => $post_data['markup_price'],
                    'shipping_own_value' => $post_data['shipping_own_value'],
                    'wallet'             => $post_data['wallet']
                ];

                //affiliate link simulation
                if( defined( 'WP_CLI' ) && !empty( $post_data['affiliate_id'] ) ) :

                    do_action( 'sejoli/checkout/affiliate/set', $post_data['affiliate_id'], 'link' );

                else :

                    do_action( 'sejoli/checkout/check-cookie', $post_data );
                
                endif;

                do_action( 'sejoli/order/set-affiliate', $post_data );

                if ($user_data && isset($user_data->ID)) :
                    $order_data['user_id'] = $user_data->ID;
                endif;

                // user is not registered
                if(false === $user_data) :
                    if( $product->type === "physical" ) :
                        do_action('sejoli/user/register', $post_data);
                        $user_data = sejolisa_get_user($post_data['user_phone']); // user phone
                        if ($user_data && isset($user_data->ID)) :
                            $order_data['user_id'] = $user_data->ID;
                        endif;
                    else:
                        do_action('sejoli/user/register', $post_data);
                        $user_data = sejolisa_get_user($post_data['user_email']); // user email
                        if ($user_data && isset($user_data->ID)) :
                            $order_data['user_id'] = $user_data->ID;
                        endif;
                    endif;
                endif;

                $order_data['status'] = 'completed';

                // Order type checking must be first before set grand total
                $order_data['type'] = apply_filters('sejoli/order/type', 'regular', $order_data);

                // calculate grand total
                if(!isset($order_data['grand_total'])) :
                    $order_data['grand_total'] = apply_filters('sejoli/order/grand-total', 0, $order_data);
                endif;

                $respond = sejolisa_create_order($order_data);

                $respond['messages']['info']    = sejolisa_get_messages('info');
                $respond['messages']['warning'] = sejolisa_get_messages('warning');

                sejolisa_set_respond($respond, 'order');

                if(false !== $respond['valid']) :

                    do_action('sejoli/log/write', 'order created', $order_data);

                    if(!empty($order_data['coupon'])) :
                        do_action('sejoli/coupon/update-usage', $order_data['coupon']);
                    endif;

                    $order_data = $respond['order'];

                    do_action('sejoli/order/new', $order_data);
                    do_action('sejoli/order/set-status/completed', $order_data);
                    do_action('sejoli/order/update-status', [
                        'ID'     => $respond['order']['ID'],
                        'status' => 'completed'
                    ]);

                    // do_action( 'sejoli/order/create', $order_data );

                    $update_query = "update " . LFB_FORM_DATA_TBL . " set status='customer' where id='" . $leadID . "'";
                    $th_save_db = new LFB_SAVE_DB( $wpdb );
                    $update_leads = $th_save_db->lfb_update_form_data( $update_query );

                    echo lfb_customeremail_send( $form_data, $form_id, $lead_id, $product_id, $affiliate_id, $email );
                    echo lfb_send_data_wa( $form_id, $form_data, $lead_id, $product_id, $affiliate_id );

                    // echo lfb_send_data_sms( $form_id, $form_data, $lead_id, $product_id, $affiliate_id );

                    return wp_send_json(true);

                    die();

                else:

                    return wp_send_json(false);

                    die();

                endif;

            else :
    
                sejolisa_set_respond([
                    'valid' => false,
                    'messages' => [
                        'error' => sejolisa_get_messages()
                    ]
                ], 'checkout');

                return wp_send_json(false);

                die();
        
            endif;

        endif;

    }

}
add_action('wp_ajax_ProceedToCustomer', 'lfb_ProceedToCustomer');