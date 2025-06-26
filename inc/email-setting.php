<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LFB_EmailSettingForm{

    /**
     * Register Construct
     * @since   1.0.0
     */
    function __construct($this_form_id){

        global $wpdb;

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_9  = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d LIMIT 1", $this_form_id);
        $posts = $th_save_db->lfb_get_form_content($prepare_9);
        
        if ($posts) {
            $form_title = $posts[0]->form_title;
            $form_status = $posts[0]->form_status;
            $captcha_status = $posts[0]->captcha_status;
            $storeType = $posts[0]->storeType;
            $storedate = $posts[0]->date;
            $mail_setting = maybe_unserialize($posts[0]->mail_setting);
            $usermail_setting = maybe_unserialize($posts[0]->usermail_setting);
            $affiliatemail_setting = maybe_unserialize($posts[0]->affiliatemail_setting);
            $form_data = maybe_unserialize($posts[0]->form_data);
        }

    }

    /**
     * Email Setting Forms
     * @since   1.0.0
     */
    function lfb_email_setting_form($this_form_id, $mail_setting_result, $usermail_setting, $affiliatemail_setting){

        $mail_setting_to = get_option('admin_email');
        $mail_setting_from = get_option('admin_email');
        $mail_setting_subject = esc_html__("Form Leads", "sejoli-lead-form");
        $mail_setting_message = '[lf-new-form-data]';
        $multi_mail = "";
        $mail_setting_header = esc_html__("New Lead Received", "sejoli-lead-form");
        if (!empty($mail_setting_result)) {
            $mail_setting_result = maybe_unserialize($mail_setting_result);
            $mail_setting_to = $mail_setting_result['email_setting']['to'];
            $mail_setting_from = $mail_setting_result['email_setting']['from'];
            $mail_setting_subject = $mail_setting_result['email_setting']['subject'];
            $mail_setting_message = $mail_setting_result['email_setting']['message'];
            $multi_mail = (isset($mail_setting_result['email_setting']['multiple'])) ? $mail_setting_result['email_setting']['multiple'] : '';

            $mail_setting_header = (isset($mail_setting_result['email_setting']['header'])) ? $mail_setting_result['email_setting']['header'] : $mail_setting_header;
        }
        $aes_nonce = wp_create_nonce( 'aes-nonce' );

        echo '<div id="wrap-email-setting">';

        echo "<div style='margin-top: 1em;'>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block">';
        }
        echo '<h2>'.esc_html__('Email Setting','sejoli-lead-form').'</h2>';
        
        echo '<div><b>Shortcode</b>: <pre style="margin: 19px 0 0 0;"><i><code title="'.__('Shortcode untuk menampilkan semua entri dari form.', 'sejoli-lead-form').'">[lf-new-form-data]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama form.', 'sejoli-lead-form').'">[form-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan ID lead.', 'sejoli-lead-form').'">[lead-id]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan Nama lead.', 'sejoli-lead-form').'">[lead-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan No. Telepon lead.', 'sejoli-lead-form').'">[lead-phone]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan informasi panggilan/sapaan.', 'sejoli-lead-form').'">[lead-panggilan]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan email lead.', 'sejoli-lead-form').'">[lead-email]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli-lead-form').'">[affiliate-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli-lead-form').'">[affiliate-phone]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli-lead-form').'">[affiliate-email]</code> </i><i><code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli-lead-form').'">[product-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan harga produk.', 'sejoli-lead-form').'">[product-price]</code> </i></pre></br></div>';
        echo '</div>';

        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: left;">';
        }
        echo "<form id='form-email-setting' action='' method='post' style='width: 100%;'>
            <div class='inside email_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Admin Email Notifications', 'sejoli-lead-form') . "</h2><br>
            <table class='form-table'>
                <tbody>
                    <tr><th scope='row'><label for='email_setting_to'>To" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='email_setting[to]' required type='email' id='email_setting_to' value='" . esc_html($mail_setting_to) . "' class='regular-text'>
                    </tr>
                    <tr><th scope='row'><label for='multiemail_setting_from'>" . esc_html__('Multiple Email Recieved', 'sejoli-lead-form') . "</label></th>
                        <td>
                        <textarea name='email_setting[multiple]' id='email_setting_message' rows='2' cols='46'>" . esc_html($multi_mail) . "</textarea></label>
                        <p class='description' id='message-description'>" . esc_html__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli-lead-form'). "</p></td>
                    </tr>
                    <tr><th scope='row'><label for='email_setting_from'>From" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='email_setting[from]' required type='email' id='email_setting_from' value='" . esc_html($mail_setting_from) . "' class='regular-text'>
                    </tr>
                    <tr>
                        <th scope='row'><label for='email_setting_header'>Header" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='email_setting[header]' type='text' id='email_setting_header' value='" . esc_html($mail_setting_header) . "' class='regular-text' required>
                    </tr>
                    <tr>
                        <th scope='row'><label for='email_setting_subject'>Subject" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='email_setting[subject]' type='text' id='email_setting_subject' value='" . esc_html($mail_setting_subject) . "' class='regular-text' required>
                    </tr>
                    <tr>
                        <th scope='row'><label for='email_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='email_setting[message]' id='email_setting_message' rows='10' cols='70' required>" . esc_html($mail_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type='hidden' name='email_setting[form-id]' required value='" . intval($this_form_id) . "'> 
            <input type='hidden' name='aes_nonce' value='".$aes_nonce."'>

            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div><div id='error-message-email-setting'></div></div></div>
            </form>";
            echo "</div>";
        echo "</div>";

        $usermail_setting_from      = get_option('admin_email');
        $usermail_setting_subject   = esc_html('Received a lead');
        $usermail_setting_message   = esc_html('Form Submitted Successfully');
        $usermail_setting_option    = esc_html('OFF');
        $usermail_setting_header    = esc_html('New Lead Received');
        if (!empty($usermail_setting)) {
            $usermail_setting_result = maybe_unserialize($usermail_setting);
            $usermail_setting_from = $usermail_setting_result['user_email_setting']['from'];
            $usermail_setting_subject = $usermail_setting_result['user_email_setting']['subject'];
            $usermail_setting_message = $usermail_setting_result['user_email_setting']['message'];
            $usermail_setting_option = $usermail_setting_result['user_email_setting']['user-email-setting-option'];
            $usermail_setting_header = (isset($usermail_setting_result['user_email_setting']['header'])) ? $usermail_setting_result['user_email_setting']['header'] : $usermail_setting_header;
        }
        $ues_nonce = wp_create_nonce( 'ues-nonce' );

        echo "<div>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: right;">';
        }
        echo "<form id='form-user-email-setting' action='' method='post' style='width: 100%;'>
            <div class='inside email_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('User Email Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr><th scope='row'><label for='user_email_setting_from'>From" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='user_email_setting[from]' required type='email' id='user_email_setting_from' value='" . esc_html($usermail_setting_from) . "' class='regular-text'>
                    </tr>

                    <tr>
                        <th scope='row'><label for='user_email_setting_header'>Header" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='user_email_setting[header]' required type='text' id='user_email_setting_header' value='" . esc_html($usermail_setting_header) . "' class='regular-text'>
                    </tr>
                    <tr>
                        <th scope='row'><label for='user_email_setting_subject'>Subject" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='user_email_setting[subject]' required type='text' id='user_email_setting_subject' value='" . esc_html($usermail_setting_subject) . "' class='regular-text'>
                    </tr>
                    <tr>
                        <th scope='row'><label for='user_email_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='user_email_setting[message]' id='user_email_setting_message' rows='10' cols='70' required>" . esc_html($usermail_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                    <tr>
                    <th scope='row'><label for='user-email-setting'></th>
                    <td>
                    <p><input type='radio' name='user_email_setting[user-email-setting-option]' " . ($usermail_setting_option == 'ON' ? 'checked' : '') . " value='" . esc_html__('ON', 'sejoli-lead-form') . "'><span>" . esc_html__('Send email to user when submit form.', 'sejoli-lead-form') . " </span></p>
                    <p><input type='radio' name='user_email_setting[user-email-setting-option]' " . ($usermail_setting_option == 'OFF' ? 'checked' : '') . " value='" . esc_html__('OFF', 'sejoli-lead-form') . "'><span>" . esc_html__("Don't Send.", 'sejoli-lead-form') . " </span></p>
                    </td></tr>
                </tbody>
            </table> 
            <input type='hidden' name='user_email_setting[form-id]' required value='" . $this_form_id . "'> 
            
            <input type='hidden' name='ues_nonce' value='".$ues_nonce."'>

            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div>
            <div id='error-message-user-email-setting'></div></div> </div>
            </form>";
            echo "</div>";
        echo "</div>";

        $affiliatemail_setting_from    = get_option('admin_email');
        $affiliatemail_setting_subject = esc_html('Received a lead');
        $affiliatemail_setting_message = esc_html('Form Submitted Successfully');
        $affiliatemail_setting_option  = esc_html('OFF');
        $affiliatemail_setting_header  = esc_html('New Lead Received');
        if (!empty($affiliatemail_setting)) {
            $affiliatemail_setting_result = maybe_unserialize($affiliatemail_setting);
            $affiliatemail_setting_from = $affiliatemail_setting_result['affiliate_email_setting']['from'];
            $affiliatemail_setting_subject = $affiliatemail_setting_result['affiliate_email_setting']['subject'];
            $affiliatemail_setting_message = $affiliatemail_setting_result['affiliate_email_setting']['message'];
            $affiliatemail_setting_option = $affiliatemail_setting_result['affiliate_email_setting']['affiliate-email-setting-option'];
            $affiliatemail_setting_header = (isset($affiliatemail_setting_result['affiliate_email_setting']['header'])) ? $affiliatemail_setting_result['affiliate_email_setting']['header'] : $affiliatemail_setting_header;
        }
        $affes_nonce = wp_create_nonce( 'affes-nonce' );

        echo "<div>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width: 90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: right;">';
        }
        echo "<form id='form-affiliate-email-setting' action='' method='post' style='width: 100%;'>
            <div class='inside email_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Affiliate Email Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr><th scope='row'><label for='affiliate_email_setting_from'>From" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='affiliate_email_setting[from]' required type='email' id='affiliate_email_setting_from' value='" . esc_html($affiliatemail_setting_from) . "' class='regular-text'>
                    </tr>

                    <tr>
                        <th scope='row'><label for='affiliate_email_setting_header'>Header" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='affiliate_email_setting[header]' required type='text' id='affiliate_email_setting_header' value='" . esc_html($affiliatemail_setting_header) . "' class='regular-text'>
                    </tr>
                    <tr>
                        <th scope='row'><label for='affiliate_email_setting_subject'>Subject" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='affiliate_email_setting[subject]' required type='text' id='affiliate_email_setting_subject' value='" . esc_html($affiliatemail_setting_subject) . "' class='regular-text'>
                    </tr>
                    <tr>
                        <th scope='row'><label for='affiliate_email_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='affiliate_email_setting[message]' id='affiliate_email_setting_message' rows='10' cols='70' required>" . esc_html($affiliatemail_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                    <tr>
                    <th scope='row'><label for='affiliate-email-setting'></th>
                    <td>
                    <p><input type='radio' name='affiliate_email_setting[affiliate-email-setting-option]' " . ($affiliatemail_setting_option == 'ON' ? 'checked' : '') . " value='" . esc_html__('ON', 'sejoli-lead-form') . "'><span>" . esc_html__('Send email to user when submit form.', 'sejoli-lead-form') . " </span></p>
                    <p><input type='radio' name='affiliate_email_setting[affiliate-email-setting-option]' " . ($affiliatemail_setting_option == 'OFF' ? 'checked' : '') . " value='" . esc_html__('OFF', 'sejoli-lead-form') . "'><span>" . esc_html__("Don't Send.", 'sejoli-lead-form') . " </span></p>
                    </td></tr>
                </tbody>
            </table> 
            <input type='hidden' name='affiliate_email_setting[form-id]' required value='" . $this_form_id . "'> 
            
            <input type='hidden' name='affes_nonce' value='".$affes_nonce."'>

            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div>
            <div id='error-message-affiliate-email-setting'></div></div> </div>
            </form>";
            echo "</div>";
        echo "</div>";

        echo "</div>";
    }

    /**
     * Captcha Setting
     * @since   1.0.0
    */
    function lfb_captcha_setting_form($this_form_id, $captcha_option){
        $captcha_nonce = wp_create_nonce( 'captcha-nonce' );

        if (isset($captcha_option)) {
            $captcha_option_val = $captcha_option;
        } else {
            $captcha_option_val = esc_html('OFF');
        }

        $captcha_sitekey = get_option('captcha-setting-sitekey');
        $captcha_secret = get_option('captcha-setting-secret');
        
        echo '<div class="wrap" style="margin-top: 1em !important;">';
        if(wp_is_mobile()){
            echo '<div class="form-block" id="recaptcha" style="width: 90.5%;">';
        } else {
            echo '<div class="form-block" id="recaptcha" style="width: 80%;">';
        }
        echo'<div class="infobox">
            <h2>' . esc_html__('Setup Captcha', 'sejoli-lead-form') . '</h2><br>
            <a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">' . esc_html__('Get your Keys', 'sejoli-lead-form') . '</a></div>
            <br class="clear">
            <div class="inside">
            <p>' . esc_html__('reCAPTCHA is a free service to protect your website from spam and abuse.', 'sejoli-lead-form') . '</p>
            <form method="post" id="captcha-form" action="">
            <table>
            <tbody>
            <tr>
                <th scope="row"><label for="sitekey">' . esc_html__('Site Key', 'sejoli-lead-form') . ' </label></th>
                <td><input type="text" style="width: 100%;" required value="' . esc_html($captcha_sitekey) . '" id="sitekey" name="captcha-setting-sitekey" class="regular-text code"></td>
            </tr>
            <tr>
                <th scope="row"><label for="secret">' . esc_html__('Secret Key', 'sejoli-lead-form') . ' </label></th>
                <td><input type="text" style="width: 100%;" required value="' . esc_html($captcha_secret) . '" id="secret" name="captcha-setting-secret" class="regular-text code"></td>
            </tr>
            </tbody>
            </table>
            <input type="hidden" name="captcha-keys" required value="' . intval($this_form_id) . '">
            <input type="hidden" name="captcha_nonce" value="'.$captcha_nonce.'">

            <p class="submit" style="text-align:right"><input type="submit" style="background: #ff4545; margin: 1em 8px 0 8px;" class="button button-primary" id="captcha_save_settings" value="' . esc_html('Save', 'sejoli-lead-form') . '" name="submit"></p>
            </form><br/>
            <div id="error-message-captcha-key"></div>
            </div>
            </div>
            </div>';

        if ($captcha_sitekey) {
            echo '<div class="inside setting_section">
            <div class="form-block">
                <div class="cardd">
                <form name="" id="captcha-on-off-setting" method="post" action="">
                <h2>' . esc_html__(' Captcha On/Off Option', 'sejoli-lead-form') . '</h2>
                <p><input type="radio" name="captcha-on-off-setting" ' . ($captcha_option_val == "ON" ? 'checked' : "") . ' value="' . esc_html('ON') . '"><span>' . esc_html__('Enable', 'sejoli-lead-form') . ' </span></p>
                <p><input type="radio" name="captcha-on-off-setting" ' . ($captcha_option_val == "OFF" ? 'checked' : "") . ' value="OFF"><span>' . esc_html__('Disable', 'sejoli-lead-form') . ' </span></p>
                <p><input type="submit" class="button button-primary" id="captcha_on_off_form_id" value="Save"></p>
                <input type="hidden" name="captcha_on_off_form_id" required value="' . intval($this_form_id) . '">
                <input type="hidden" name="captcha_nonce" value="'.$captcha_nonce.'">

                </form><br/>
                <div id="error-message-captcha-option"></div>            
                </div>
                </div>
                </div>';
        }
    }

    /**
     * Leads Setting
     * @since   1.0.0
     */
    function lfb_lead_setting_form($this_form_id, $lead_store_option, $form_display_option){

        global $wpdb;
        $msg_nonce = wp_create_nonce( 'thankyou-nonce' );
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_10 =  $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d LIMIT 1", $this_form_id);
        $posts = $th_save_db->lfb_get_form_content($prepare_10);
        if (isset($posts[0]->multiData)) {
            $multidata = unserialize($posts[0]->multiData);
            $successMsg = isset($multidata['thankyou_settings']['success-msg']) ? $multidata['thankyou_settings']['success-msg'] : '';
            $redirectUrl = isset($multidata['thankyou_settings']['redirect-url']) ? $multidata['thankyou_settings']['redirect-url'] : '';
        } else {
            $successMsg = esc_html__("Thank You ...", 'sejoli-lead-form');
            $redirectUrl = '';
        }
        echo "<div style='margin-top: 1em;'>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="width:90.5%">';
        } else {
            echo '<div class="form-block" style="width:40%">';
        }
        echo '<form name="" id="lfb-form-success-msg" method="post" action="">
                <h2>' . esc_html__('Form submitting Message (Thankyou Message)', 'sejoli-lead-form') . '</h2>
                <div class="tablenav top">
                <p>
                 <textarea name="thankyou_settings[success-msg]" id="lfb_success_msg">' . esc_html($successMsg) . '</textarea> 
                 <br/>                
                 <i>' . esc_html__('This message will display to the visitor at your site. After submitting form.', 'sejoli-lead-form') . ' </i>

                </p>
                <h2>' . esc_html__('Redirect Url', 'sejoli-lead-form') . '</h2>
                <p>
                 <input name="thankyou_settings[redirect-url]" id="lfb_redirect_url" value="' . esc_url($redirectUrl) . '">
                 <p><i>' . esc_html__('Visitor will be redirected to this URL after submitting form.', 'sejoli-lead-form') . ' </i></p>
                 <i>' . esc_html__('Enter full url like : http://domainanda.com/thankyou', 'sejoli-lead-form') . ' </i>
                </p>
                </div>
                <p style="text-align:right"><input type="submit" style="background: #ff4545; margin: 0em 0px 0 8px;" class="button button-primary" id="advance_lead_msg_setting" value="' . esc_html('Save') . '"></p>
                <input type="hidden" name="thankyou_settings[form-id]" value="' . intval($this_form_id) . '">    
                <input type="hidden" name="thankyou-nonce" value="' . $msg_nonce . '">
                <div id="error-thankyou-message-setting"></div>
            </form>
            </div>
            </div>';

        if (isset($lead_store_option)) {
            $lead_store_option = $lead_store_option;
        } else {
            $lead_store_option = 3;
        }
        $nonce = wp_create_nonce( 'lrv-nonce' );
        echo '<div>';
        if(wp_is_mobile()){
            echo '<div class="form-block" style="width:90.5%">';
        } else {
            echo '<div class="form-block" style="width:40%">';
        }
        echo '<form name="" id="lead-email-setting" method="post" action="">
                <h2>' . esc_html__('Lead Receiving Method', 'sejoli-lead-form') . '</h2>
                <p><input type="radio" name="data-recieve-method" ' . ($lead_store_option == 1 ? 'checked' : "") . ' value="1"><span>' . esc_html__('Receive Leads in Email, WhatsApp and SMS', 'sejoli-lead-form') . ' </span></p>
                <p><input type="radio" name="data-recieve-method" ' . ($lead_store_option == 2 ? 'checked' : "") . ' value="2"><span>' . esc_html__('Save Leads in database (you can see all leads in the lead option)', 'sejoli-lead-form') . ' </span></p>
                <p><input type="radio" name="data-recieve-method" ' . ($lead_store_option == 3 ? 'checked' : "") . ' value="3"><span>' . esc_html__('Receive Leads in Email, WhatsApp, SMS and Save in database', 'sejoli-lead-form') . '</span><br><span id="data-rec-met-err"></span></p>
                <p style="text-align:right"><input type="submit" class="button button-primary" style="background: #ff4545; margin: 0em 0px 0 8px;" id="advance_lead_setting" value="' . esc_html('Update') . '"></p>
                <input type="hidden" name="action-lead-setting" value="' . intval($this_form_id) . '">    
                <input type="hidden" name="lrv_nonce_verify" value="' . $nonce . '">

                </form><br/><div id="error-message-lead-store"></div>          
            </div>
            </div>';

        if (isset($form_display_option)) {
            $form_display_option = $form_display_option;
        } else {
            $form_display_option = 6;
        }
        $nonce = wp_create_nonce( 'fop-nonce' );
        echo '<div>';
        if(wp_is_mobile()){
            echo '<div class="form-block" style="width:90.5%">';
        } else {
            echo '<div class="form-block" style="width:40%">';
        }
        echo '<form name="" id="form-option-setting" method="post" action="">
                <h2>' . esc_html__('Form Display Setting', 'sejoli-lead-form') . '</h2>
                <p><input type="radio" name="data-form-option-method" ' . ($form_display_option == 3 ? 'checked' : "") . ' value="3"><span>' . esc_html__('Hide Form Name & Text "Affiliasi Oleh"', 'sejoli-lead-form') . ' </span></p>
                <p><input type="radio" name="data-form-option-method" ' . ($form_display_option == 4 ? 'checked' : "") . ' value="4"><span>' . esc_html__('Show Form Name Only', 'sejoli-lead-form') . ' </span></p>
                <p><input type="radio" name="data-form-option-method" ' . ($form_display_option == 5 ? 'checked' : "") . ' value="5"><span>' . esc_html__('Show Text "Affiliasi Oleh" Only', 'sejoli-lead-form') . ' </span></p>
                <p><input type="radio" name="data-form-option-method" ' . ($form_display_option == 6 ? 'checked' : "") . ' value="6"><span>' . esc_html__('Show Form Name & Text "Affiliasi Oleh"', 'sejoli-lead-form') . '</span><br><span id="data-rec-met-err"></span></p>
                <p style="text-align:right"><input type="submit" class="button button-primary" style="background: #ff4545; margin: 0em 0px 0 8px;" id="advance_form_option_setting" value="' . esc_html('Update') . '"></p>
                <input type="hidden" name="action-form-option-setting" value="' . intval($this_form_id) . '">    
                <input type="hidden" name="fop_nonce_verify" value="' . $nonce . '">

                </form><br/><div id="error-message-form-option"></div>          
            </div>
            </div>';

    }
}
