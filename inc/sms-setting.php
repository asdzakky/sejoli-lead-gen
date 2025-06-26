<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LFB_SMSSettingForm{

    /**
     * Register Construct
     * @since   1.0.0
     */
    function __construct($this_form_id){
        global $wpdb;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_9 =  $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d LIMIT 1", $this_form_id);
        $posts = $th_save_db->lfb_get_form_content($prepare_9);
        if ($posts) {
            $form_title = $posts[0]->form_title;
            $form_status = $posts[0]->form_status;
            $captcha_status = $posts[0]->captcha_status;
            $storeType = $posts[0]->storeType;
            $storedate = $posts[0]->date;
            $sms_setting = maybe_unserialize($posts[0]->sms_setting);
            $usersms_setting = maybe_unserialize($posts[0]->usersms_setting);
            $affiliatesms_setting = maybe_unserialize($posts[0]->affiliatesms_setting);
            $form_data = maybe_unserialize($posts[0]->form_data);
        }
    }

    /**
     * SMS Setting Form
     * @since   1.0.0
     */
    function lfb_sms_setting_form($this_form_id, $sms_setting_result, $usersms_setting, $affiliatesms_setting){

        $sms_setting_message = '[lf-new-form-data]';
        $multi_sms = "";
        $sms_to = '';
        if (!empty($sms_setting_result)) {
            $sms_setting_result = maybe_unserialize($sms_setting_result);
            $sms_setting_message = $sms_setting_result['sms_setting']['message'];
            $multi_sms = (isset($sms_setting_result['sms_setting']['multiple'])) ? $sms_setting_result['sms_setting']['multiple'] : '';
            $sms_to = $sms_setting_result['sms_setting']['to'];
        }
        $asmss_nonce = wp_create_nonce( 'asmss-nonce' );

        echo '<div id="wrap-sms-setting">';

        echo "<div style='margin-top: 1em;'>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block">';
        }
        echo '<h2>'.esc_html__('SMS Setting','sejoli-lead-form').'</h2>';
        
        echo '<div><b>Shortcode</b>: <pre style="margin: 19px 0 0 0;"><i><code title="'.__('Shortcode untuk menampilkan semua entri dari form.', 'sejoli-lead-form').'">[lf-new-form-data]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama form.', 'sejoli-lead-form').'">[form-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan ID lead.', 'sejoli-lead-form').'">[lead-id]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan Nama lead.', 'sejoli-lead-form').'">[lead-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan No. Telepon lead.', 'sejoli-lead-form').'">[lead-phone]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan informasi panggilan/sapaan.', 'sejoli-lead-form').'">[lead-panggilan]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan email lead.', 'sejoli-lead-form').'">[lead-email]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli-lead-form').'">[affiliate-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli-lead-form').'">[affiliate-phone]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli-lead-form').'">[affiliate-email]</code> </i><i><code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli-lead-form').'">[product-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan harga produk.', 'sejoli-lead-form').'">[product-price]</code> </i></pre></br></div>';
        echo '</div>';
        
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: left;">';
        }
        echo "<form id='form-sms-setting' action='' method='post' style='width: 100%;'>
            <div class='inside sms_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Admin SMS Notifications', 'sejoli-lead-form') . "</h2><br>
            <table class='form-table'>
                <tbody>
                    <tr><th scope='row'><label for='sms_setting_to'>Nomor SMS Penerima" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='sms_setting[to]' required type='text' id='sms_setting_to' value='".$sms_to."' class='regular-text'>
                        <p class='description' id='from-description'>" . esc_html__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli-lead-form') . "</p></td>
                    </tr>
                    <tr>
                        <th scope='row'><label for='sms_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='sms_setting[message]' id='sms_setting_message' rows='5' cols='46' required>" . esc_html($sms_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type='hidden' name='sms_setting[form-id]' required value='" . intval($this_form_id) . "'> 
            <input type='hidden' name='asmss_nonce' value='".$asmss_nonce."'>

            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div><div id='error-message-sms-setting'></div></div></div>
            </form>";
            echo "</div>";
        echo "</div>";

        $usersms_setting_message   = esc_html('Form Submitted Successfully');
        if (!empty($usersms_setting)) {
            $usersms_setting_result = maybe_unserialize($usersms_setting);
            $usersms_setting_message = $usersms_setting_result['user_sms_setting']['message'];
        }
        $usmss_nonce = wp_create_nonce( 'usmss-nonce' );

        echo "<div>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: right;">';
        }
        echo "<form id='form-user-sms-setting' action='' method='post' style='width: 100%;'>
            <div class='inside sms_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('User SMS Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr>
                        <th scope='row'><label for='user_sms_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='user_sms_setting[message]' id='user_sms_setting_message' rows='5' cols='46' required>" . esc_html($usersms_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                </tbody>
            </table> 
            <input type='hidden' name='user_sms_setting[form-id]' required value='" . $this_form_id . "'> 
            
            <input type='hidden' name='usmss_nonce' value='".$usmss_nonce."'>

            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div>
            <div id='error-message-user-sms-setting'></div></div> </div>
            </form>";
            echo "</div>";
        echo "</div>";

        $affiliatesms_setting_message   = esc_html('Form Submitted Successfully');
        if (!empty($affiliatesms_setting)) {
            $affiliatesms_setting_result = maybe_unserialize($affiliatesms_setting);
            $affiliatesms_setting_message = $affiliatesms_setting_result['affiliate_sms_setting']['message'];
        }
        $affsmss_nonce = wp_create_nonce( 'affsmss-nonce' );

        echo "<div>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: right;">';
        }
        echo "<form id='form-affiliate-sms-setting' action='' method='post' style='width: 100%;'>
            <div class='inside sms_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Affiliate SMS Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr>
                        <th scope='row'><label for='affiliate_sms_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='affiliate_sms_setting[message]' id='affiliate_sms_setting_message' rows='5' cols='46' required>" . esc_html($affiliatesms_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                </tbody>
            </table> 
            <input type='hidden' name='affiliate_sms_setting[form-id]' required value='" . $this_form_id . "'> 
            
            <input type='hidden' name='affsmss_nonce' value='".$affsmss_nonce."'>

            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div>
            <div id='error-message-affiliate-sms-setting'></div></div> </div>
            </form>";
            echo "</div>";
        echo "</div>";

        echo "</div>";
    }

}