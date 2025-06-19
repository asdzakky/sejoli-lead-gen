<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LFB_CustomerSettingForm{

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
            $customer_setting_result = maybe_unserialize($posts[0]->customer_setting);
            $customer_wa_setting_result = maybe_unserialize($posts[0]->customer_wa_setting);
            $customer_sms_setting_result = maybe_unserialize($posts[0]->customer_sms_setting);
            $form_data = maybe_unserialize($posts[0]->form_data);
        }
    }

    /**
     * Whatsapp Setting Forms
     * @since   1.0.0
     */
    function lfb_customer_setting_form($this_form_id, $customer_setting_result, $customer_wa_setting_result, $customer_sms_setting_result){

        $customer_email_setting_from      = get_option('admin_email');
        $customer_email_setting_subject   = esc_html('Proses Perubahan ke Customer');
        $customer_email_setting_message   = esc_html('Form Submitted Successfully');
        $customer_email_setting_header    = esc_html('New Lead Received');
        if (!empty($customer_setting_result)) {
            $customer_setting_result = maybe_unserialize($customer_setting_result);
            $customer_email_setting_from = $customer_setting_result['customer_email_setting']['from'];
            $customer_email_setting_subject = $customer_setting_result['customer_email_setting']['subject'];
            $customer_email_setting_message = $customer_setting_result['customer_email_setting']['message'];
            $customer_email_setting_header = (isset($customer_setting_result['customer_email_setting']['header'])) ? $customer_setting_result['customer_email_setting']['header'] : $customer_email_setting_header;
        }
        $ces_nonce = wp_create_nonce( 'ces-nonce' );

        echo '<div id="wrap-customer-setting">';

        echo "<div style='margin-top: 1em;'>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block">';
        }
        echo '<h2>'.esc_html__('Customer Setting','sejoli-lead-form').'</h2>';
        
        echo '<div><b>Shortcode</b>: <pre style="margin: 19px 0 0 0;"><i><code title="'.__('Shortcode untuk menampilkan semua entri dari form.', 'sejoli-lead-form').'">[lf-new-form-data]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama form.', 'sejoli-lead-form').'">[form-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan ID lead.', 'sejoli-lead-form').'">[lead-id]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan Nama lead.', 'sejoli-lead-form').'">[lead-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan No. Telepon lead.', 'sejoli-lead-form').'">[lead-phone]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan email lead.', 'sejoli-lead-form').'">[lead-email]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli-lead-form').'">[affiliate-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli-lead-form').'">[affiliate-phone]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli-lead-form').'">[affiliate-email]</code> </i><i><code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli-lead-form').'">[product-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan harga produk.', 'sejoli-lead-form').'">[product-price]</code> </i></pre></br></div>';
        echo '</div>';

        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: left;">';
        }
        echo "<form id='form-customer-email-setting' action='' method='post' style='width: 100%;'>
            <div class='inside customer_email_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Customer Email Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr><th scope='row'><label for='customer_email_setting_from'>From" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='customer_email_setting[from]' required type='email' id='customer_email_setting_from' value='" . esc_html($customer_email_setting_from) . "' class='regular-text'>
                    </tr>

                    <tr>
                        <th scope='row'><label for='customer_email_setting_header'>Header" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='customer_email_setting[header]' required type='text' id='customer_email_setting_header' value='" . esc_html($customer_email_setting_header) . "' class='regular-text'>
                    </tr>
                    <tr>
                        <th scope='row'><label for='customer_email_setting_subject'>Subject" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='customer_email_setting[subject]' required type='text' id='customer_email_setting_subject' value='" . esc_html($customer_email_setting_subject) . "' class='regular-text'>
                    </tr>
                    <tr>
                        <th scope='row'><label for='customer_email_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='customer_email_setting[message]' id='customer_email_setting_message' rows='5' cols='46' required>" . esc_html($customer_email_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                </tbody>
            </table> 
            <input type='hidden' name='customer_email_setting[form-id]' required value='" . $this_form_id . "'> 
            
            <input type='hidden' name='ces_nonce' value='".$ces_nonce."'>
            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div>
            <div id='error-message-customer-email-setting'></div></div> </div>
            </form>";
            echo '</div>';
        echo "</div>";

        $customer_wa_setting_message   = esc_html('Form Submitted Successfully');
        if (!empty($customer_wa_setting_result)) {
            $customer_wa_setting = maybe_unserialize($customer_wa_setting_result);
            $customer_wa_setting_message = $customer_wa_setting['customer_wa_setting']['message'];
        }
        $cws_nonce = wp_create_nonce( 'cws-nonce' );

        echo "<div>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        } else {
            echo '<div class="form-block" style="display: inline-block; width: 47%;float: right;">';
        }
        echo "<form id='form-customer-wa-setting' action='' method='post' style='width: 100%;'>
            <div class='inside customer_wa_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Customer WhatsApp Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr>
                        <th scope='row'><label for='customer_wa_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='customer_wa_setting[message]' id='customer_wa_setting_message' rows='10' cols='70' required>" . esc_html($customer_wa_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                </tbody>
            </table> 
            <input type='hidden' name='customer_wa_setting[form-id]' required value='" . $this_form_id . "'> 
            
            <input type='hidden' name='cws_nonce' value='".$cws_nonce."'>

            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
            </p>
            </div>
            <div id='error-message-customer-wa-setting'></div></div> </div>
            </form>";
            echo "</div>";
        echo "</div>";

        // $customer_sms_setting_message   = esc_html('Form Submitted Successfully');
        // if (!empty($customer_sms_setting_result)) {
        //     $customer_sms_setting = maybe_unserialize($customer_sms_setting_result);
        //     $customer_sms_setting_message = $customer_sms_setting['customer_sms_setting']['message'];
        // }
        // $css_nonce = wp_create_nonce( 'css-nonce' );

        // echo "<div>";
        // if(wp_is_mobile()){
        //     echo '<div class="form-block" style="display: inline-block; width:90.5%">';
        // } else {
        //     echo '<div class="form-block" style="display: inline-block; width: 47%;float: right;">';
        // }
        // echo "<form id='form-customer-sms-setting' action='' method='post' style='width: 100%;'>
        //     <div class='inside customer_sms_setting_section'>
        //     <div class='cards'>
        //     <div class='infobox'>
        //     <h2>" . esc_html__('Customer SMS Notifications', 'sejoli-lead-form') . " </h2><br>
        //     <table class='form-table'>
        //         <tbody>
        //             <tr>
        //                 <th scope='row'><label for='customer_sms_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
        //                 <td>
        //                     <textarea name='customer_sms_setting[message]' id='customer_sms_setting_message' rows='10' cols='70' required>" . esc_html($customer_sms_setting_message) . "</textarea></label>
        //                 </td>
        //             </tr>
        //         </tbody>
        //     </table> 
        //     <input type='hidden' name='customer_sms_setting[form-id]' required value='" . $this_form_id . "'> 
            
        //     <input type='hidden' name='css_nonce' value='".$css_nonce."'>

        //     <p style='text-align:right'>
        //     <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 10px 0 8px;' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'>
        //     </p>
        //     </div>
        //     <div id='error-message-customer-sms-setting'></div></div> </div>
        //     </form>";
        //     echo "</div>";
        // echo "</div>";

        echo "</div>";
    }

}