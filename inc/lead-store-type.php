<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

Class LFB_LeadStoreType{

    /**
     * Php Mailer
     * @since   1.0.0
     */
    function lfb_phpmailer_active( $fid ) {

        $lfb_leadform = NEW LFB_SAVE_DB();
       
        $smtp = $lfb_leadform->lfb_get_ext_data($fid,2);
        $this->active = isset($smtp[0]->active)?$smtp[0]->active:'';

        if($this->active):

            $this->smtpmail = isset($smtp[0]->ext_api)?unserialize($smtp[0]->ext_api):'';

            $this->smtp_name = isset($this->smtpmail['smtp_name'])?$this->smtpmail['smtp_name']:'';
            $this->smtp_server = isset($this->smtpmail['smtp_server'])?$this->smtpmail['smtp_server']:'';
            $this->smtp_port = isset($this->smtpmail['smtp_port'])?$this->smtpmail['smtp_port']:25;
            $this->smtp_enc_type = isset($this->smtpmail['smtp_enc_type'])?$this->smtpmail['smtp_enc_type']:'';
            $this->smtp_username = isset($this->smtpmail['smtp_username'])?$this->smtpmail['smtp_username']:'';
            $this->smtp_pass = isset($this->smtpmail['smtp_pass'])?$this->smtpmail['smtp_pass']:'';

            add_action( 'phpmailer_init',array($this,'lfb_phpmailer_send') );

       endif;

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
     * PHP Mailer Send
     * @since   1.0.0
     */
    function lfb_phpmailer_send($phpmailer){

        // remove_action( 'phpmailer_init', array($this,__function__ ));
        $phpmailer->isSMTP();    
        $phpmailer->Host = $this->smtp_server;
        $phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
        $phpmailer->Port = $this->smtp_port;
        $phpmailer->Username = $this->smtp_username;
        $phpmailer->Password = $this->smtp_pass;

        // Additional settings…
        $phpmailer->SMTPSecure = $this->smtp_enc_type; // Choose SSL or TLS, if necessary for your server
        //$phpmailer->From = "you@yourdomail.com";
        //$phpmailer->FromName = "Your Name";  
        
    }

    /**
     * Save Data Form
     * @since   1.0.0
     */
    function lfb_save_data($form_id, $form_product, $form_affiliate, $form_data){

        global $wpdb;
        
        $server_request = $_SERVER['HTTP_USER_AGENT'];
        $ip_address = $this->lfb_get_user_ip_addres();
        $data_table_name = LFB_FORM_DATA_TBL;

        $update_leads = $wpdb->query( $wpdb->prepare( 
        "INSERT INTO $data_table_name ( form_id, product, affiliate, form_data, status, ip_address, server_request, date ) 
        VALUES ( %d, %d, %d, %s, %s, %s, %s, %s )",
        $form_id, $form_product, $form_affiliate, $form_data, 'lead', $ip_address, $server_request, date_i18n('Y/m/d H:i:s') ) );
        
        $lead_id = $wpdb->insert_id;

        if ($update_leads) {
            return $lead_id;
        }

    }

    /**
     * Filter Mail Data
     * @since   1.0.0
     */
    function lfb_mail_filter($form_id,$form_data){

        global $wpdb;

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data = maybe_unserialize($form_data);
        $i = 0;
        $table = '<table rules="all" style="width: 80%; border: 1px solid #FBFBFB;"  cellpadding="10"><tbody>';
        
        foreach ($form_field as $key => $value){
            $trnth = ($i%2)?'background:#FBFBFB;':'';

            if(isset($form_data[$key]) && is_array($form_data[$key])){
                if(strstr($key, 'upload_')){
                    $upload_filename = isset($form_data[$key]['filename'])?$form_data[$key]['filename']:$form_data[$key]['error'];
                    // $upload = isset($form_data[$key]['url'])?'<a target="_blank" href="'.esc_url($form_data[$key]["url"]).'">'.$upload_filename.'</a>':$upload_filename;
                    // $table .='<tr style="'.$trnth.'" ><td style="padding:8px;" ><strong>'.$value.': </strong></td><td style="padding:8px;" >'.$upload.'</td></tr>';
                } else {
                    $fieldVal = implode(", ",$form_data[$key]);
                    $table .='<tr style="'.$trnth.'" ><td style="padding:8px;" ><strong>'.$value.': </strong></td><td style="padding:8px;" >'.$fieldVal.'</td></tr>';
                }
            } else{
                $table .= (isset($form_data[$key]))?'<tr style="'.$trnth.'" ><td style="padding:8px;" ><strong> '.$value.': </td></strong><td style="padding:8px;" >'.$form_data[$key].'</td></tr>':'<tr style="'.$trnth.'" ><td style="padding:8px;" ><strong>'.$value.'</strong></td><td style="padding:8px;" > - </td></tr>';
            }
            $i++;
        }

        return $table ."</tbody></table>";

    }

    /**
     * Filter Data from Form Entries
     * @since   1.0.0
     */
    function lfb_data_filter($form_id,$form_data){

        global $wpdb;

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data = maybe_unserialize($form_data);
        $i = 0;
        $table = '';
   
        foreach ($form_field as $key => $value){

            if(isset($form_data[$key]) && is_array($form_data[$key])){

                if(strstr($key, 'upload_')){
                    $upload_filename = isset($form_data[$key]['filename']) ? $form_data[$key]['filename'] : $form_data[$key]['error'];
                    $upload = isset($form_data[$key]['url'])?'<a target="_blank" href="'.esc_url($form_data[$key]["url"]).'">'.$upload_filename.'</a>':$upload_filename;
                    $table .= $value .': '. $upload."\r \n";
                } else {
                    $fieldVal = implode(", ",$form_data[$key]);
                    $table .= $value .': '. $fieldVal."\r \n";
                }

            } else{

                if(isset($form_data[$key]) && is_array($form_data[$key])){
                    $table .= $value .': '. $form_data[$key]."\r \n";
                } else {
                    $table .= null;
                }

            }
            

            $i++;
        }

        return $table;

    }

    /**
     * Filter Data from Form Entries
     * @since   1.0.0
     */
    function lfb_wa_sms_data_filter($form_id,$form_data){

        global $wpdb;

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data = maybe_unserialize($form_data);
        $i = 0;
        $table = '';
   
        foreach ($form_field as $key => $value){

            if(isset($form_data[$key]) && is_array($form_data[$key])){

                if(strstr($key, 'upload_')){
                    $upload_filename = isset($form_data[$key]['filename']) ? $form_data[$key]['filename'] : $form_data[$key]['error'];
                    $upload = isset($form_data[$key]['url'])? esc_url($form_data[$key]["url"]):$upload_filename;
                    $table .= $value .': '. $upload."\r \n";
                } else {
                    $fieldVal = implode(", ",$form_data[$key]);
                    $table .= $value .': '. $fieldVal."\r \n";
                }

            } else{

                $table .= $value .': '. $form_data[$key]."\r \n";

            }
            
            $i++;
        }

        return $table;

    }

    /**
     * Filter WhatsApp Data
     * @since   1.0.0
     */
    function lfb_wa_filter($form_id,$form_data){

        global $wpdb;

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $form = $th_save_db->lfb_get_form_data($form_id);
        $form_datas = maybe_unserialize($form[0]->form_data);

        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data = maybe_unserialize($form_data);

        foreach ($form_datas as $results) {
            $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
            $field_id = $results['field_id'];
            if ( $type === 'phonenumber' ) {
                $wa_number = $form_data['phonenumber_'.$field_id];
            }
        }

        return (array) $this->phone_number_format($wa_number);

    }

    /**
     * Send Data into Admin Email
     * @since   1.0.0
     */
    function lfb_send_data_email($form_id,$form_data,$lead_id,$form_title,$form_product,$form_affiliate,$mail_setting,$user_email,$affiliate_email,$affiliate_setting){
       
        $form_entry_data = $this->lfb_mail_filter($form_id,$form_data);

        $user_email['leads'] = $form_entry_data;
        $reply_to = $user_email['emailid'];
        $to = get_option('admin_email');
        $subject = esc_html__('New Lead Recieved','sejoli-lead-form');
        $new_message = esc_html__('Recieved New Leads','sejoli-lead-form');

        $form_entry_data .=	"<br/>";
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        $message = '';

        if(!empty($mail_setting)){
            $sitelink = preg_replace('#^https?://#', '', site_url());
            $to = $mail_setting['email_setting']['to'];

            $shortcodes_a = '[lf-new-form-data]';
            $shortcodes_b = $form_entry_data; 
            $shortcode_form_name = '[form-name]';
            $shortcode_lead_id = '[lead-id]';
            $shortcode_lead_name = '[lead-name]';
            $shortcode_lead_email = '[lead-email]';
            $shortcode_lead_phone = '[lead-phone]';
            $shortcode_lead_panggilan = '[lead-panggilan]';
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

            $form_field = $th_save_db->lfb_admin_email_send($form_id);
            $form_data  = maybe_unserialize($form_data);
            $lead_email = '';
            $lead_name  = '';
            $lead_phone = '';
            $getFileUploadSource = array();

            foreach ($form_datas as $results) {

                $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
                $field_id = $results['field_id'];

                if ($type === 'email') { 
                    $lead_email = $form_data['email_'.$field_id];
                } elseif($type === 'name') { 
                    $lead_name = $form_data['name_'.$field_id];
                } elseif ( $type === 'phonenumber' ) {
                    $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                    // Menambahkan +62 di depan
                    $lead_phone = '+62' . $phone_number;
                } elseif ($type === 'upload') { 
                    $lead_uploaded_file = $form_data['upload_'.$field_id];
                    $getFileUploadSource[] = $lead_uploaded_file['url'];
                } elseif ( $type === 'radiopanggilan' ) {
                    $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
                }

            }

            $header = (isset($mail_setting['email_setting']['header']))?$mail_setting['email_setting']['header']:$sitelink;
            $header = str_replace($shortcode_form_name, $form_title, $header);
            $header = str_replace($shortcode_lead_id, $lead_id, $header);
            $header = str_replace($shortcode_lead_name, $lead_name, $header);
            $header = str_replace($shortcode_lead_email, $lead_email, $header);
            $header = str_replace($shortcode_lead_phone, $lead_phone, $header);
            $header = str_replace($shortcode_lead_panggilan, $lead_panggilan, $header);
            $header = str_replace($shortcode_affiliate_name, $affiliate_name, $header);
            $header = str_replace($shortcode_affiliate_email, $affiliate_email, $header);
            $header = str_replace($shortcode_affiliate_phone, $affiliate_phone, $header);
            $header = str_replace($shortcode_product_name, $product_name, $header);
            $header = str_replace($shortcode_product_price, $product_price, $header);

            $subject = esc_html($mail_setting['email_setting']['subject']);
            $subject = str_replace($shortcode_form_name, $form_title, $subject);
            $subject = str_replace($shortcode_lead_id, $lead_id, $subject);
            $subject = str_replace($shortcode_lead_name, $lead_name, $subject);
            $subject = str_replace($shortcode_lead_email, $lead_email, $subject);
            $subject = str_replace($shortcode_lead_phone, $lead_phone, $subject);
            $subject = str_replace($shortcode_lead_panggilan, $lead_panggilan, $subject);
            $subject = str_replace($shortcode_affiliate_name, $affiliate_name, $subject);
            $subject = str_replace($shortcode_affiliate_email, $affiliate_email, $subject);
            $subject = str_replace($shortcode_affiliate_phone, $affiliate_phone, $subject);
            $subject = str_replace($shortcode_product_name, $product_name, $subject);
            $subject = str_replace($shortcode_product_price, $product_price, $subject);

            $message = $mail_setting['email_setting']['message'];

            // $message = '';
            $message = ($message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $message);
            $message = str_replace($shortcode_form_name, $form_title, $message);
            $message = str_replace($shortcode_lead_id, $lead_id, $message);
            $message = str_replace($shortcode_lead_name, $lead_name, $message);
            $message = str_replace($shortcode_lead_email, $lead_email, $message);
            $message = str_replace($shortcode_lead_phone, $lead_phone, $message);
            $message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $message);
            $message = str_replace($shortcode_affiliate_name, $affiliate_name, $message);
            $message = str_replace($shortcode_affiliate_email, $affiliate_email, $message);
            $message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $message);
            $message = str_replace($shortcode_product_name, $product_name, $message);
            $message = str_replace($shortcode_product_price, $product_price, $message);

            $headers[] = "From:".$header." <".$mail_setting['email_setting']['from'].">";
            $headers[] = "Reply-To:".$header." <".$reply_to.">";

            $multiple = isset($mail_setting['email_setting']['multiple'])?$mail_setting['email_setting']['multiple']:'';
            if($multiple!=''){
                $explode = explode( ',',$multiple );
                if(is_array($explode)){
                    foreach ( $explode as $bcc_email ) {
                        $fname = explode( '@',$bcc_email );
                        $bcc_head = isset($fname[0])?$fname[0]:'user';
                            $headers[]= "Bcc:".$bcc_head." <".trim( $bcc_email ).">";
                    } // foreach
                } // //is array
            } //explode
        }
           
        // Admin Email Send
        if($message) {
            $email = new LeadFormEmail();

            $email->send(
                array($to),
                $message,
                $subject,
                $getFileUploadSource
            );
        }

        //user email send
        if(!empty($user_email['user_email_settings'])){
            $usermail_option = $user_email['user_email_settings']['user_email_setting']['user-email-setting-option'];
            $emailid = $user_email['emailid'];

            if(($usermail_option =="ON") && ($emailid !='invalid_email') && is_email($emailid)){
                 $this->lfb_useremail_send($form_id,$form_data,$form_title,$user_email, $shortcodes_b,$lead_id,$form_product,$form_affiliate);
            }
        }

        if(!empty($affiliate_setting['affiliate_email_settings'])){
            $affiliatemail_option = $affiliate_setting['affiliate_email_settings']['affiliate_email_setting']['affiliate-email-setting-option'];

            if(($affiliatemail_option =="ON") && is_email($affiliate_email)){
                $this->lfb_affiliateemail_send($form_id,$form_data,$form_title,$affiliate_setting, $affiliate_email, $shortcodes_b,$lead_id,$form_product,$form_affiliate);
            }
        }

    }

    /**
     * Send Data into User Email
     * @since   1.0.0
     */
    function lfb_useremail_send($form_id, $form_data, $form_title, $user_email, $shortcodes_b,$lead_id,$form_product,$form_affiliate){

        $usermail_setting = $user_email['user_email_settings'];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $to = $user_email['emailid'];

        $shortcodes_a = '[lf-new-form-data]';
        $shortcodes_b = $shortcodes_b; 
        $shortcode_form_name = '[form-name]';
        $shortcode_lead_id = '[lead-id]';
        $shortcode_lead_name = '[lead-name]';
        $shortcode_lead_email = '[lead-email]';
        $shortcode_lead_phone = '[lead-phone]';
        $shortcode_lead_panggilan = '[lead-panggilan]';
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

        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data  = maybe_unserialize($form_data);
        $lead_email = '';
        $lead_name  = '';
        $lead_phone = '';
        $getFileUploadSource = array();

        foreach ($form_datas as $results) {

            $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
            $field_id = $results['field_id'];

            if ($type === 'email') { 
                $lead_email = $form_data['email_'.$field_id];
            } elseif($type === 'name') { 
                $lead_name = $form_data['name_'.$field_id];
            } elseif ( $type === 'phonenumber' ) {
                $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                // Menambahkan +62 di depan
                $lead_phone = '+62' . $phone_number;
            } elseif ($type === 'upload') { 
                $lead_uploaded_file = $form_data['upload_'.$field_id];
                $getFileUploadSource[] = $lead_uploaded_file['url'];
            } elseif ( $type === 'radiopanggilan' ) {
                $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
            }

        }

        $header =  (isset($usermail_setting['user_email_setting']['header']))?$usermail_setting['user_email_setting']['header']:'Submit Form';
        $header = str_replace($shortcode_form_name, $form_title, $header);
        $header = str_replace($shortcode_lead_id, $lead_id, $header);
        $header = str_replace($shortcode_lead_name, $lead_name, $header);
        $header = str_replace($shortcode_lead_email, $lead_email, $header);
        $header = str_replace($shortcode_lead_phone, $lead_phone, $header);
        $header = str_replace($shortcode_lead_panggilan, $lead_panggilan, $header);
        $header = str_replace($shortcode_affiliate_name, $affiliate_name, $header);
        $header = str_replace($shortcode_affiliate_email, $affiliate_email, $header);
        $header = str_replace($shortcode_affiliate_phone, $affiliate_phone, $header);
        $header = str_replace($shortcode_product_name, $product_name, $header);
        $header = str_replace($shortcode_product_price, $product_price, $header);

        $subject =  $usermail_setting['user_email_setting']['subject'];
        $subject = str_replace($shortcode_form_name, $form_title, $subject);
        $subject = str_replace($shortcode_lead_id, $lead_id, $subject);
        $subject = str_replace($shortcode_lead_name, $lead_name, $subject);
        $subject = str_replace($shortcode_lead_email, $lead_email, $subject);
        $subject = str_replace($shortcode_lead_phone, $lead_phone, $subject);
        $subject = str_replace($shortcode_lead_panggilan, $lead_panggilan, $subject);
        $subject = str_replace($shortcode_affiliate_name, $affiliate_name, $subject);
        $subject = str_replace($shortcode_affiliate_email, $affiliate_email, $subject);
        $subject = str_replace($shortcode_affiliate_phone, $affiliate_phone, $subject);
        $subject = str_replace($shortcode_product_name, $product_name, $subject);
        $subject = str_replace($shortcode_product_price, $product_price, $subject);

        $message  =  $usermail_setting['user_email_setting']['message'];

        $message = ($message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $message);
        $message = str_replace($shortcode_form_name, $form_title, $message);
        $message = str_replace($shortcode_lead_id, $lead_id, $message);
        $message = str_replace($shortcode_lead_name, $lead_name, $message);
        $message = str_replace($shortcode_lead_email, $lead_email, $message);
        $message = str_replace($shortcode_lead_phone, $lead_phone, $message);
        $message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $message);
        $message = str_replace($shortcode_affiliate_name, $affiliate_name, $message);
        $message = str_replace($shortcode_affiliate_email, $affiliate_email, $message);
        $message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $message);
        $message = str_replace($shortcode_product_name, $product_name, $message);
        $message = str_replace($shortcode_product_price, $product_price, $message);

        $headers[] = "From:".$header." <".$usermail_setting['user_email_setting']['from'].">";
        $headers[] = "Reply-To:".$header." <".$usermail_setting['user_email_setting']['from'].">";  
        
        if($message) {
            $email = new LeadFormEmail();

            $email->send(
                array($to),
                $message,
                $subject,
                $getFileUploadSource
            );
        }

    }

    /**
     * Send Data into Affiliate Email
     * @since   1.0.0
     */
    function lfb_affiliateemail_send($form_id, $form_data, $form_title, $affiliate_setting, $affiliate_email, $shortcodes_b,$lead_id,$form_product,$form_affiliate){
        
        $affiliate_form = $affiliate_setting['affiliate_email_settings'];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $to = $affiliate_email;

        $shortcodes_a = '[lf-new-form-data]';
        $shortcodes_b = $shortcodes_b; 
        $shortcode_form_name = '[form-name]';
        $shortcode_lead_id = '[lead-id]';
        $shortcode_lead_name = '[lead-name]';
        $shortcode_lead_email = '[lead-email]';
        $shortcode_lead_phone = '[lead-phone]';
        $shortcode_lead_panggilan = '[lead-panggilan]';
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

        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data  = maybe_unserialize($form_data);
        $lead_email = '';
        $lead_name  = '';
        $lead_phone = '';
        $getFileUploadSource = array();

        foreach ($form_datas as $results) {

            $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
            $field_id = $results['field_id'];

            if ($type === 'email') { 
                $lead_email = $form_data['email_'.$field_id];
            } elseif($type === 'name') { 
                $lead_name = $form_data['name_'.$field_id];
            } elseif ( $type === 'phonenumber' ) {
                $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                // Menambahkan +62 di depan
                $lead_phone = '+62' . $phone_number;
            } elseif ($type === 'upload') { 
                $lead_uploaded_file = $form_data['upload_'.$field_id];
                $getFileUploadSource[] = $lead_uploaded_file['url'];
            } elseif ( $type === 'radiopanggilan' ) {
                $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
            }

        }

        $header =  (isset($affiliate_form['affiliate_email_setting']['header']))?$affiliate_form['affiliate_email_setting']['header']:'Submit Form';
        $header = str_replace($shortcode_form_name, $form_title, $header);
        $header = str_replace($shortcode_lead_id, $lead_id, $header);
        $header = str_replace($shortcode_lead_name, $lead_name, $header);
        $header = str_replace($shortcode_lead_email, $lead_email, $header);
        $header = str_replace($shortcode_lead_phone, $lead_phone, $header);
        $header = str_replace($shortcode_lead_panggilan, $lead_panggilan, $header);
        $header = str_replace($shortcode_affiliate_name, $affiliate_name, $header);
        $header = str_replace($shortcode_affiliate_email, $affiliate_email, $header);
        $header = str_replace($shortcode_affiliate_phone, $affiliate_phone, $header);
        $header = str_replace($shortcode_product_name, $product_name, $header);
        $header = str_replace($shortcode_product_price, $product_price, $header);

        $subject =  $affiliate_form['affiliate_email_setting']['subject'];
        $subject = str_replace($shortcode_form_name, $form_title, $subject);
        $subject = str_replace($shortcode_lead_id, $lead_id, $subject);
        $subject = str_replace($shortcode_lead_name, $lead_name, $subject);
        $subject = str_replace($shortcode_lead_email, $lead_email, $subject);
        $subject = str_replace($shortcode_lead_phone, $lead_phone, $subject);
        $subject = str_replace($shortcode_lead_panggilan, $lead_panggilan, $subject);
        $subject = str_replace($shortcode_affiliate_name, $affiliate_name, $subject);
        $subject = str_replace($shortcode_affiliate_email, $affiliate_email, $subject);
        $subject = str_replace($shortcode_affiliate_phone, $affiliate_phone, $subject);
        $subject = str_replace($shortcode_product_name, $product_name, $subject);
        $subject = str_replace($shortcode_product_price, $product_price, $subject);

        $message  =  $affiliate_form['affiliate_email_setting']['message'];

        $message = ($message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $message);
        $message = str_replace($shortcode_form_name, $form_title, $message);
        $message = str_replace($shortcode_lead_id, $lead_id, $message);
        $message = str_replace($shortcode_lead_name, $lead_name, $message);
        $message = str_replace($shortcode_lead_email, $lead_email, $message);
        $message = str_replace($shortcode_lead_phone, $lead_phone, $message);
        $message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $message);
        $message = str_replace($shortcode_affiliate_name, $affiliate_name, $message);
        $message = str_replace($shortcode_affiliate_email, $affiliate_email, $message);
        $message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $message);
        $message = str_replace($shortcode_product_name, $product_name, $message);
        $message = str_replace($shortcode_product_price, $product_price, $message);

        $headers[] = "From:".$header." <".$affiliate_form['affiliate_email_setting']['from'].">";
        $headers[] = "Reply-To:".$header." <".$affiliate_form['affiliate_email_setting']['from'].">";  
            
        if($message) {
            $email = new LeadFormEmail();

            $email->send(
                array($to),
                $message,
                $subject,
                $getFileUploadSource
            );
        }

    }

    /**
     * Send Data into WhatsApp
     * @since   1.0.0
     */
    function lfb_send_data_wa($form_id,$form_data,$lead_id,$form_title,$form_product,$form_affiliate,$wa_setting,$user_wa,$affiliate_wa,$affiliate_setting,$user_emailid){
        
        $form_entry_data = $this->lfb_wa_sms_data_filter($form_id,$form_data);

        if(!empty($wa_setting['whatsapp_setting'])){
            $sitelink = preg_replace('#^https?://#', '', site_url());
            $to = $wa_setting['whatsapp_setting']['to'];
            $message  = $wa_setting['whatsapp_setting']['message'];

            $shortcodes_a = '[lf-new-form-data]';
            $shortcodes_b = $form_entry_data; 
            $shortcode_form_name = '[form-name]';
            $shortcode_lead_id = '[lead-id]';
            $shortcode_lead_name = '[lead-name]';
            $shortcode_lead_email = '[lead-email]';
            $shortcode_lead_phone = '[lead-phone]';
            $shortcode_lead_panggilan = '[lead-panggilan]';
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
                    $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                    // Menambahkan +62 di depan
                    $lead_phone = '+62' . $phone_number;
                } elseif ( $type === 'radiopanggilan' ) {
                    $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
                }

            }

            $message = ($message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $message);
            $message = str_replace($shortcode_form_name, $form_title, $message);
            $message = str_replace($shortcode_lead_id, $lead_id, $message);
            $message = str_replace($shortcode_lead_name, $lead_name, $message);
            $message = str_replace($shortcode_lead_email, $lead_email, $message);
            $message = str_replace($shortcode_lead_phone, $lead_phone, $message);
            $message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $message);
            $message = str_replace($shortcode_affiliate_name, $affiliate_name, $message);
            $message = str_replace($shortcode_affiliate_email, $affiliate_email, $message);
            $message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $message);
            $message = str_replace($shortcode_product_name, $product_name, $message);
            $message = str_replace($shortcode_product_price, $product_price, $message);

            $multiple = isset($wa_setting['whatsapp_setting']['to'])?$wa_setting['whatsapp_setting']['to']:'';
            if($multiple != ''){
                $explode = explode( ',',$multiple );
                if(is_array($explode)){
                    foreach ( $explode as $wa ) {
                        $wa_number = explode( ',',$multiple );
                    } // foreach
                } // //is array
            } //explode

            if($message) {
                $whatsapp = new LeadFormWhatsApp();
                $whatsapp->send($wa_number, strip_tags($message), $title = '');
            }

        }

        if(!empty($user_wa['user_wa_settings'])){

            $get_user_wa_number = $this->lfb_wa_filter($form_id,$form_data);
            $th_save_db = new LFB_SAVE_DB();
            $user_message = $user_wa['user_wa_settings']['user_wa_setting']['message'];

            $shortcodes_a = '[lf-new-form-data]';
            $shortcodes_b = $form_entry_data; 
            $shortcode_form_name = '[form-name]';
            $shortcode_lead_id = '[lead-id]';
            $shortcode_lead_name = '[lead-name]';
            $shortcode_lead_email = '[lead-email]';
            $shortcode_lead_phone = '[lead-phone]';
            $shortcode_lead_panggilan = '[lead-panggilan]';
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
                    $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                    // Menambahkan +62 di depan
                    $lead_phone = '+62' . $phone_number;
                } elseif ( $type === 'radiopanggilan' ) {
                    $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
                }

            }

            $user_message = ($user_message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $user_message);
            $user_message = str_replace($shortcode_form_name, $form_title, $user_message);
            $user_message = str_replace($shortcode_lead_id, $lead_id, $user_message);
            $user_message = str_replace($shortcode_lead_name, $lead_name, $user_message);
            $user_message = str_replace($shortcode_lead_email, $lead_email, $user_message);
            $user_message = str_replace($shortcode_lead_phone, $lead_phone, $user_message);
            $user_message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $user_message);
            $user_message = str_replace($shortcode_affiliate_name, $affiliate_name, $user_message);
            $user_message = str_replace($shortcode_affiliate_email, $affiliate_email, $user_message);
            $user_message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $user_message);
            $user_message = str_replace($shortcode_product_name, $product_name, $user_message);
            $user_message = str_replace($shortcode_product_price, $product_price, $user_message);

            if($user_message) {
                $whatsapp = new LeadFormWhatsApp();
                $whatsapp->send($get_user_wa_number, strip_tags($user_message), $title = '');
            }

        }

        //affiliate wa send
        if(!empty($affiliate_setting['affiliate_wa_settings'])){

            $get_affiliate_wa_number = (array) $this->phone_number_format($affiliate_wa);
            $th_save_db = new LFB_SAVE_DB();
            $affiliate_message = $affiliate_setting['affiliate_wa_settings']['affiliate_wa_setting']['message'];
            $shortcodes_a = '[lf-new-form-data]';
            $shortcodes_b = $form_entry_data; 
            $shortcode_form_name = '[form-name]';
            $shortcode_lead_id = '[lead-id]';
            $shortcode_lead_name = '[lead-name]';
            $shortcode_lead_email = '[lead-email]';
            $shortcode_lead_phone = '[lead-phone]';
            $shortcode_lead_panggilan = '[lead-panggilan]';
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
                    $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                    // Menambahkan +62 di depan
                    $lead_phone = '+62' . $phone_number;
                } elseif ( $type === 'radiopanggilan' ) {
                    $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
                }

            }

            $affiliate_message = ($affiliate_message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $affiliate_message);
            $affiliate_message = str_replace($shortcode_form_name, $form_title, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_id, $lead_id, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_name, $lead_name, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_email, $lead_email, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_phone, $lead_phone, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $affiliate_message);
            $affiliate_message = str_replace($shortcode_affiliate_name, $affiliate_name, $affiliate_message);
            $affiliate_message = str_replace($shortcode_affiliate_email, $affiliate_email, $affiliate_message);
            $affiliate_message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $affiliate_message);
            $affiliate_message = str_replace($shortcode_product_name, $product_name, $affiliate_message);
            $affiliate_message = str_replace($shortcode_product_price, $product_price, $affiliate_message);

            if($affiliate_message) {
                $whatsapp = new LeadFormWhatsApp();
                $whatsapp->send($get_affiliate_wa_number, strip_tags($affiliate_message), $title = '');
            }

        }

    }

    /**
     * Send Data into SMS
     * @since   1.0.0
     */
    function lfb_send_data_sms($form_id,$form_data,$lead_id,$form_title,$form_product,$form_affiliate,$sms_setting,$user_sms,$affiliate_sms,$affiliate_setting,$user_emailid){
        
        $form_entry_data = $this->lfb_data_filter($form_id,$form_data);

        if(!empty($sms_setting['sms_setting'])){
            $sitelink = preg_replace('#^https?://#', '', site_url());
            $to = $sms_setting['sms_setting']['to'];
            $message  = $sms_setting['sms_setting']['message'];

            $shortcodes_a = '[lf-new-form-data]';
            $shortcodes_b = $form_entry_data; 
            $shortcode_form_name = '[form-name]';
            $shortcode_lead_id = '[lead-id]';
            $shortcode_lead_name = '[lead-name]';
            $shortcode_lead_email = '[lead-email]';
            $shortcode_lead_phone = '[lead-phone]';
            $shortcode_lead_panggilan = '[lead-panggilan]';
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
                    $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                    // Menambahkan +62 di depan
                    $lead_phone = '+62' . $phone_number;
                } elseif ( $type === 'radiopanggilan' ) {
                    $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
                }

            }

            $message = ($message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $message);
            $message = str_replace($shortcode_form_name, $form_title, $message);
            $message = str_replace($shortcode_lead_id, $lead_id, $message);
            $message = str_replace($shortcode_lead_name, $lead_name, $message);
            $message = str_replace($shortcode_lead_email, $lead_email, $message);
            $message = str_replace($shortcode_lead_phone, $lead_phone, $message);
            $message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $message);
            $message = str_replace($shortcode_affiliate_name, $affiliate_name, $message);
            $message = str_replace($shortcode_affiliate_email, $affiliate_email, $message);
            $message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $message);
            $message = str_replace($shortcode_product_name, $product_name, $message);
            $message = str_replace($shortcode_product_price, $product_price, $message);

            $multiple = isset($sms_setting['sms_setting']['to'])?$sms_setting['sms_setting']['to']:'';
            if($multiple != ''){
                $explode = explode( ',',$multiple );
                if(is_array($explode)){
                    foreach ( $explode as $sms ) {
                        $sms_number = explode( ',',$multiple );
                    } // foreach
                } // //is array
            } //explode
            
            if($message) {
                $sms = new LeadFormSMS();
                $sms->send($sms_number, strip_tags($message), $title = '');
            }
        }
        
        //user sms send
        if(!empty($user_sms['user_sms_settings'])){
            $get_user_sms_number = $this->lfb_wa_filter($form_id,$form_data);
            $th_save_db = new LFB_SAVE_DB();
            $user_message = $user_sms['user_sms_settings']['user_sms_setting']['message'];
            
            $shortcodes_a = '[lf-new-form-data]';
            $shortcodes_b = $form_entry_data; 
            $shortcode_form_name = '[form-name]';
            $shortcode_lead_id = '[lead-id]';
            $shortcode_lead_name = '[lead-name]';
            $shortcode_lead_email = '[lead-email]';
            $shortcode_lead_phone = '[lead-phone]';
            $shortcode_lead_panggilan = '[lead-panggilan]';
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
                    $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                    // Menambahkan +62 di depan
                    $lead_phone = '+62' . $phone_number;
                } elseif ( $type === 'radiopanggilan' ) {
                    $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
                }

            }

            $user_message = ($user_message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $user_message);
            $user_message = str_replace($shortcode_form_name, $form_title, $user_message);
            $user_message = str_replace($shortcode_lead_id, $lead_id, $user_message);
            $user_message = str_replace($shortcode_lead_name, $lead_name, $user_message);
            $user_message = str_replace($shortcode_lead_email, $lead_email, $user_message);
            $user_message = str_replace($shortcode_lead_phone, $lead_phone, $user_message);
            $user_message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $user_message);
            $user_message = str_replace($shortcode_affiliate_name, $affiliate_name, $user_message);
            $user_message = str_replace($shortcode_affiliate_email, $affiliate_email, $user_message);
            $user_message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $user_message);
            $user_message = str_replace($shortcode_product_name, $product_name, $user_message);
            $user_message = str_replace($shortcode_product_price, $product_price, $user_message);

            if($user_message) {
                $sms = new LeadFormSMS();
                $sms->send($get_user_sms_number, strip_tags($user_message), $title = '');
            }
        }

        //affiliate sms send
        if(!empty($affiliate_setting['affiliate_sms_settings'])){
            $get_affiliate_sms_number = (array) $this->phone_number_format($affiliate_sms);
            $th_save_db = new LFB_SAVE_DB();
            $affiliate_message = $affiliate_setting['affiliate_sms_settings']['affiliate_sms_setting']['message'];
            
            $shortcodes_a = '[lf-new-form-data]';
            $shortcodes_b = $form_entry_data; 
            $shortcode_form_name = '[form-name]';
            $shortcode_lead_id = '[lead-id]';
            $shortcode_lead_name = '[lead-name]';
            $shortcode_lead_email = '[lead-email]';
            $shortcode_lead_phone = '[lead-phone]';
            $shortcode_lead_panggilan = '[lead-panggilan]';
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
                    $phone_number = ltrim($form_data['phonenumber_'.$field_id], '0');
                    // Menambahkan +62 di depan
                    $lead_phone = '+62' . $phone_number;
                } elseif ( $type === 'radiopanggilan' ) {
                    $lead_panggilan = $form_data['radiopanggilan_'.$field_id];
                }

            }

            $affiliate_message = ($affiliate_message=='')?esc_html('New Leads'):str_replace($shortcodes_a, $shortcodes_b, $affiliate_message);
            $affiliate_message = str_replace($shortcode_form_name, $form_title, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_id, $lead_id, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_name, $lead_name, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_email, $lead_email, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_phone, $lead_phone, $affiliate_message);
            $affiliate_message = str_replace($shortcode_lead_panggilan, $lead_panggilan, $affiliate_message);
            $affiliate_message = str_replace($shortcode_affiliate_name, $affiliate_name, $affiliate_message);
            $affiliate_message = str_replace($shortcode_affiliate_email, $affiliate_email, $affiliate_message);
            $affiliate_message = str_replace($shortcode_affiliate_phone, $affiliate_phone, $affiliate_message);
            $affiliate_message = str_replace($shortcode_product_name, $product_name, $affiliate_message);
            $affiliate_message = str_replace($shortcode_product_price, $product_price, $affiliate_message);

            if($affiliate_message) {
                $sms = new LeadFormSMS();
                $sms->send($get_affiliate_sms_number, strip_tags($affiliate_message), $title = '');
            }
        }

    }

    /**
     * 1 = Recieve Leads in Email
     * 2 = Save Leads in database
     * 3 = Recieve Leads in Email and Save in database
     * @since   1.0.0
     */
    function lfb_mail_type($form_id,$form_title,$form_product,$form_affiliate,$form_data,$lfbdb,$user_emailid){

        $return             = '';
        $posts              = $lfbdb->lfb_mail_store_type($form_id);
        $storeType          = $posts[0]->storeType;
        $mail_setting       = $posts[0]->mail_setting;
        $admin_mail_setting = maybe_unserialize($mail_setting);
        $usermail_setting   = $posts[0]->usermail_setting;
        $usermail           = maybe_unserialize($usermail_setting);
        $user_email         = array('user_email_settings'=>$usermail,'emailid'=>$user_emailid);
        $affiliate_form     = $posts[0]->affiliatemail_setting;
        $affiliatemail      = maybe_unserialize($affiliate_form);
        $affiliate_setting  = array('affiliate_email_settings'=>$affiliatemail);

        if($form_affiliate > 0) {
            $affiliate       = sejolisa_get_user($form_affiliate);
            $affiliate_email = $affiliate->user_email;
        } else {
            $affiliate_email = '';
        }

        $wa_setting           = $posts[0]->wa_setting;
        $admin_wa_setting     = maybe_unserialize($wa_setting);
        $userwa_setting       = $posts[0]->userwa_setting;
        $userwa               = maybe_unserialize($userwa_setting);
        $user_wa              = array('user_wa_settings'=>$userwa,'emailid'=>$user_emailid);
        $affiliate_wa_form    = $posts[0]->affiliatewa_setting;
        $affiliatwa           = maybe_unserialize($affiliate_wa_form);
        $affiliate_wa_setting = array('affiliate_wa_settings'=>$affiliatwa);

        if($form_affiliate > 0) {
            $affiliate    = sejolisa_get_user($form_affiliate);
            $affiliate_wa = $affiliate->meta->phone;
        } else {
            $affiliate_wa = '';
        }

        $sms_setting        = $posts[0]->sms_setting;
        $admin_sms_setting  = maybe_unserialize($sms_setting);
        $usersms_setting    = $posts[0]->usersms_setting;
        $usersms            = maybe_unserialize($usersms_setting);
        $user_sms           = array('user_sms_settings'=>$usersms,'emailid'=>$user_emailid);
        $affiliate_sms_form = $posts[0]->affiliatesms_setting;
        $affiliatsms        = maybe_unserialize($affiliate_sms_form);
        $affiliate_sms_setting  = array('affiliate_sms_settings'=>$affiliatsms);

        if($form_affiliate > 0) {
            $affiliate     = sejolisa_get_user($form_affiliate);
            $affiliate_sms = $affiliate->meta->phone;
        } else {
            $affiliate_sms = '';
        }

        if ($storeType == 1) {
            $this->lfb_send_data_email($form_id, $form_data, $lead_id, $form_title, $form_product, $form_affiliate, $admin_mail_setting,$user_email,$affiliate_email, $affiliate_setting);
            $return = 1;
            $this->lfb_send_data_wa($form_id, $form_data, $lead_id, $form_title, $form_product, $form_affiliate, $admin_wa_setting,$user_wa,$affiliate_wa, $affiliate_wa_setting,$user_emailid);
            $this->lfb_send_data_sms($form_id, $form_data, $lead_id, $form_title, $form_product, $form_affiliate, $admin_sms_setting,$user_sms,$affiliate_sms, $affiliate_sms_setting,$user_emailid);
        }
        if ($storeType == 2) {
            $return =  $this->lfb_save_data($form_id, $form_product, $form_affiliate, $form_data);
        }
        if ($storeType == 3) {
            $return = $this->lfb_save_data($form_id, $form_product, $form_affiliate, $form_data);
            $lead_id = $return;
            $this->lfb_send_data_email($form_id, $form_data, $lead_id, $form_title, $form_product, $form_affiliate, $admin_mail_setting,$user_email,$affiliate_email, $affiliate_setting);
            $this->lfb_send_data_wa($form_id, $form_data, $lead_id, $form_title, $form_product, $form_affiliate, $admin_wa_setting,$user_wa,$affiliate_wa, $affiliate_wa_setting,$user_emailid);
            $this->lfb_send_data_sms($form_id, $form_data, $lead_id, $form_title, $form_product, $form_affiliate, $admin_sms_setting,$user_sms,$affiliate_sms, $affiliate_sms_setting,$user_emailid);
        }

        echo $return;

    }

    /**
     * Get User IP Address
     * Hooked via action admin_menu
     * @since   1.0.0
     */
    function lfb_get_user_ip_addres(){

        $ipaddress = '';
        
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = esc_html__('UNKNOWN','sejoli-lead-form');

        return $ipaddress;

    }
    
}