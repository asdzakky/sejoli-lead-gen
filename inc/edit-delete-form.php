<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once('lf-db.php');

Class LFB_EDIT_DEL_FORM {

    /**
     * Allowed Tags
     * @since   1.0.0
     */
    function _alowed_tags() {
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
            'disabled'  => array(),
            'onclick' => array(),
            'placeholder'  => array(),
            'checked'  => array(),
        );
        $allowed['p'] = array(
            'class'    => array(),
            'onclick'  => array(),
            'id'   => array(),
        );

        $allowed['select'] = array(
            'class'    => array(),
            'onclick'  => array(),
            'id'   => array(),
            'name'  => array(),

        );

        $allowed['option'] = array(
            'value'    => array(),
            'selected'   => array(),
        );

        return $allowed;
    }

    /**
     * Get Product Lead
     * @since   1.0.0
     */
    function sejoli_lead_get_product($product_id) {

        $html = '';

        // if( $product_id > 0 ) {
            $html .= "<div id='titlewrap'>";
            $html .= "<div class='label-form'><label>".esc_html__('Product','sejoli-lead-form')."</label></div>";
            $html .= '<div class="field-form"><select id="sejoli_lead_select2_products" name="product">';
            $html .= '<option value="">Select Product</option>';
            $title = get_the_title( $product_id );
            $html .= '<option value="' . $product_id . '" selected="selected">' . $title . '</option>';
            $html .= '<select></div></div><!-- #titlewrap -->';
        // }

        return $html;

    }

    /**
     * Edit Form Content
     * @since   1.0.0
     */
    function lfb_edit_form_content($form_action, $this_form_id) {

        global $wpdb;

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_8 = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d LIMIT 1", $this_form_id  );
        $posts = $th_save_db->lfb_get_form_content($prepare_8);
        if ($posts){
            $form_title = esc_html($posts[0]->form_title);
            $product = isset($_POST['product']) ? esc_html($_POST['product']) : $posts[0]->product;
            $form_url = isset($_POST['form_page_url']) ? esc_html($_POST['form_page_url']) : $posts[0]->form_url;
            $form_data_result = maybe_unserialize($posts[0]->form_data);
            $mail_setting_result = $posts[0]->mail_setting;
            $usermail_setting_result = $posts[0]->usermail_setting;
            $affiliatemail_setting_result = $posts[0]->affiliatemail_setting;
            $autoresponder_setting_result = $posts[0]->autoresponder_setting;
            $followup_setting_result = $posts[0]->followup_setting;
            $wa_setting_result = $posts[0]->wa_setting;
            $userwa_setting_result = $posts[0]->userwa_setting;
            $affiliatewa_setting_result = $posts[0]->affiliatewa_setting;
            $sms_setting_result = $posts[0]->sms_setting;
            $usersms_setting_result = $posts[0]->usersms_setting;
            $affiliatesms_setting_result = $posts[0]->affiliatesms_setting;
            $customer_setting_result = $posts[0]->customer_setting;
            $customer_wa_setting_result = $posts[0]->customer_wa_setting;
            $customer_sms_setting_result = $posts[0]->customer_sms_setting;
            $captcha_option = $posts[0]->captcha_status;
            $lead_store_option = esc_html($posts[0]->storeType);
            $form_display_option = esc_html($posts[0]->formDisplayOption);

            $all_form_fields = $this->lfb_create_form_fields_for_edit($form_title, $form_data_result);
        }
        $wpdb->query("UPDATE ".LFB_FORM_FIELD_TBL." set product='" . $product . "', form_url='" . $form_url . "' where id='" . $this_form_id . "'" );
        $form_message ='';
        if(isset($_GET['redirect'])){
            $redirect_value= esc_html($_GET['redirect']);
            if($redirect_value=='create'){
                $form_message='<div id="message" class="updated notice is-dismissible"><p>Form<strong>Saved</strong>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.esc_html__("Dismiss this notice.","sejoli-lead-form").'</span></button></div>';
            }
            
            if($redirect_value=='update'){
                $form_message='<div id="message" class="updated notice is-dismissible"><p>Form <strong>Updated</strong>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.esc_html__("Dismiss this notice.","sejoli-lead-form").'</span></button></div>';
            }
        }

        $nonce = wp_create_nonce( '_nonce_verify' );
        $update_url = "admin.php?page=add-new-form&action=edit&redirect=update&formid=".$this_form_id.'&_wpnonce='.$nonce;
        $email_active = $wa_active = $autoresponder_active = $followup_active = $customer_active = $sms_active = $captcha_active = $form_active = $_active = '';
        
        if(isset($_GET['email-setting'])){
            $email_active = 'nav-tab-active';
        }elseif(isset($_GET['captcha-setting'])){
            $captcha_active = 'nav-tab-active';
        }elseif(isset($_GET['form-setting'])){
            $form_active = 'nav-tab-active';
        }else{
            $_active = 'nav-tab-active';
        }

        echo '<div class="wrap">';
        include_once( plugin_dir_path(__FILE__) . 'header.php' );
        echo wp_kses($form_message,$this->_alowed_tags());
        echo '<div class="nav-tab-wrapper">
            <a class="nav-tab edit-lead-form '.esc_attr($_active).'" href="#">'.esc_html__("Edit Form","sejoli-lead-form").'</a>
            <a class="nav-tab lead-form-email-setting  '.esc_attr($email_active).'" href="#">'.esc_html__("Email Setting","sejoli-lead-form").'</a>
            <a class="nav-tab lead-form-wa-setting  '.esc_attr($wa_active).'" href="#">'.esc_html__("WhatsApp Setting","sejoli-lead-form").'</a>
            <a class="nav-tab lead-form-autoresponder-setting  '.esc_attr($customer_active).'" href="#">'.esc_html__("Autoresponder Setting","sejoli-lead-form").'</a>
            <a class="nav-tab lead-form-customer-setting  '.esc_attr($customer_active).'" href="#">'.esc_html__("Customer Setting","sejoli-lead-form").'</a>
            <a class="nav-tab lead-form-captcha-setting  '.esc_attr($captcha_active).'" href="#">'.esc_html__("Captcha Setting","sejoli-lead-form").'</a>
            <a class="nav-tab lead-form-setting  '.esc_attr($form_active).'" href="#">'.esc_html__("Setting","sejoli-lead-form").'</a>
            </div>
            <div id="sections">
            <span class="back-arrow"><a href="'.admin_url('admin.php?page=lead-forms').'" ><img width ="18" src="'.LFB_FORM_BACK_SVG.'" ></a></span>
            <section><div class="wrap">
            <form method="post" action="'.esc_url($update_url).'" id="new_lead_form">
            <div id="poststuff">
                <div id="post-body">
                    <div id="post-body-content" class="form-block">
                        <h2>'.esc_html__('Edit From','sejoli-lead-form').'</h2>
                        <div id="titlediv">
                            <div id="titlewrap">
                                <div class="label-form"><label>'.esc_html__("Form Name","sejoli-lead-form").'</label></div>
                                <div class="field-form"><input type="text" class="new_form_heading" name="post_title" placeholder="Enter form name here" value="' . $form_title . '" size="30" id="title" spellcheck="true" autocomplete="off"></div></div><!-- #titlewrap -->
                            </br>
                            '.$this->sejoli_lead_get_product($product).'
                            </br>
                            <div id="titlewrap">
                            <div class="label-form"><label>'.esc_html__("Url","sejoli-lead-form").'</label></div>
                            <div class="field-form"><input type="text" class="new_form_heading" name="form_page_url" placeholder="'.esc_html__('Enter url here','sejoli-lead-form').'" value="' . $form_url . '" size="30" id="title" spellcheck="true" autocomplete="off"></div></div><!-- #titlewrap -->
                            </br>
                            <div class="inside">
                            </div>
                        </div><!-- #titlediv -->
                    </div><!-- #post-body-content -->
                </div>
            </div>';
        $this->lfb_basic_form();
        echo wp_kses($all_form_fields,$this->_alowed_tags());
        echo '<div id="append_new_field"></div>
            </table>
            </div>
            <div class="form-block">
            <p class="submit" style="text-align:right"><input type="submit" class="update_form button-primary" style="background: #ff4545; margin: 0 0 0 0;" name="update_form" id="update_form" value="Update Form"><input type="hidden" class="update_form_id button-primary" name="update_form_id" id="update_form_id" value="'.intval($this_form_id).'"></p>
                <input type="hidden" name = "_wpnonce" value="'.$nonce.'" />
            </div>
            </td>
            </form> 
            </div>
            </section>
            <section>';
            if (is_admin()) {
                $lf_email_setting_form = new LFB_EmailSettingForm($this_form_id);
                $lf_email_setting_form->lfb_email_setting_form($this_form_id,$mail_setting_result,$usermail_setting_result,$affiliatemail_setting_result);
            }
        echo '</section>
            <section>';
            if (is_admin()) {
                $lf_wa_setting_form = new LFB_WhatsAppSettingForm($this_form_id);
                $lf_wa_setting_form->lfb_whatsapp_setting_form($this_form_id,$wa_setting_result,$userwa_setting_result,$affiliatewa_setting_result);
            }
        // echo '</section>
        //     <section>';
        //     if (is_admin()) {
        //         $lf_sms_setting_form = new LFB_SMSSettingForm($this_form_id);
        //         $lf_sms_setting_form->lfb_sms_setting_form($this_form_id,$sms_setting_result,$usersms_setting_result,$affiliatesms_setting_result);
        //     }
        echo '</section>
            <section>';
            if (is_admin()) {
                $lf_autoresponder_setting_form = new LFB_AutoresponderSettingForm($this_form_id);
                $lf_autoresponder_setting_form->lfb_autoresponder_setting_form($this_form_id, $autoresponder_setting_result);
            }
        // echo '</section>
        //     <section>';
        //     if (is_admin()) {
        //         $lf_followup_setting_form = new LFB_FollowUpSettingForm($this_form_id);
        //         $lf_followup_setting_form->lfb_followup_setting_form($this_form_id, $followup_setting_result);
        //     }
        echo '</section>
            <section>';
            if (is_admin()) {
                $lf_followup_setting_form = new LFB_CustomerSettingForm($this_form_id);
                $lf_followup_setting_form->lfb_customer_setting_form($this_form_id,$customer_setting_result, $customer_wa_setting_result, $customer_sms_setting_result);
            }
        echo '</section>
            <section>';
            if (is_admin()) {
                $lf_email_setting_form = new LFB_EmailSettingForm($this_form_id);
                $lf_email_setting_form->lfb_captcha_setting_form($this_form_id, $captcha_option);
            }
        echo '</section><section>';
            if (is_admin()) {
                $lf_email_setting_form = new LFB_EmailSettingForm($this_form_id);
                $lf_email_setting_form->lfb_lead_setting_form($this_form_id, $lead_store_option, $form_display_option);
            }
        echo '</section></div>
            </div>';

    }
    
    /**
     * Delete Form Content
     * @since   1.0.0
     */
    function lfb_delete_form_content($form_action, $this_form_id, $page_id) {

        global $wpdb;

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;

        $update_leads = $wpdb->update( 
        $table_name,
        array( 
            'form_status' => esc_html('Disable')
        ), 
        array( 'id' =>$this_form_id));

        if($update_leads){    
            $th_show_forms = new LFB_SHOW_FORMS();
            $th_show_forms->lfb_show_all_forms($page_id);
        }

    }

    /**
     * Register Basic Form
     * @since   1.0.0
     */
    function lfb_basic_form() {

        echo "<div class='inside spth_setting_section'  id='wpth_add_form'>
            <div class='form-block'>
            <h2>".esc_html__('Form Fields','sejoli-lead-form')."</h2>
            <table class='widefat' id='sortable'>          
            <thead>
            <tr>
            <th>".esc_html__('Field name','sejoli-lead-form')."</th>
            <th>".esc_html__('Field Type','sejoli-lead-form')."</th>
            <th>".esc_html__('Default Value','sejoli-lead-form')."</th>
            <th>".esc_html__('Required','sejoli-lead-form')."</th>
            <th>".esc_html__('Action','sejoli-lead-form')."</th>
            </tr></thead>";

    }

    /**
     * Initial Form Field
     * @since   1.0.0
     */
    function lfbFormField($key){

        $fields =  array('name'=>'Name','email'=>'Email','message'=>'Message','dob'=>'DOB(Date of Birth)','date'=>'Date','text'=>'Text (Single Line Text)','textarea'=>'Textarea (Multiple Line Text)','htmlfield'=>'Content Area (Read only Text)','url'=>'Link (Website Url)','phonenumber'=>'Phone Number','number'=>'Number (Only Numeric 0-9)','upload'=>'File Upload','radio'=>'Radio (Choose Single Option)','option'=>'Option (Choose Single Option)','checkbox'=>'Checkbox (Choose Multiple Option)','terms'=>'Checkbox (Terms & condition)');
        $return = isset($fields[$key])?$fields[$key]:'';
        
        return $return;

    }

    /**
     * Form Field Name
     * @since   1.0.0
     */
    function lfbFieldName($fieldv,$fieldID){

        $fieldName = isset($fieldv['field_name'])?$fieldv['field_name']:'';
        $return = '<td><input type="text" name="lfb_form[form_field_' . $fieldID . '][field_name]" id="field_name_' . $fieldID . '" value="' . $fieldName . '"></td>';
        
        return $return;

    }

    /**
     * Form Field Default
     * @since   1.0.0
     */
    function lfbFieldTypeDefault($fieldtype,$name,$fieldID){

        $return = "<td><select class='form_field_select' name='lfb_form[form_field_" . $fieldID . "][field_type][type]' id='field_type_" . $fieldID . "'>
            <option value='select' ".( $fieldtype === 'select' ? 'selected="selected"' : '' ).">".esc_html__('Select Field Type','sejoli-lead-form')."</option>
            <option value='name' ".( $fieldtype === 'name' ? 'selected="selected"' : '' ).">".esc_html__('Name','sejoli-lead-form')."</option>         
            <option value='email' ".( $fieldtype === 'email' ? 'selected="selected"' : '' ).">".esc_html__('Email','sejoli-lead-form')."</option>
            <option value='message' ".( $fieldtype === 'message' ? 'selected="selected"' : '' ).">".esc_html__('Message','sejoli-lead-form')."</option>
            <option value='dob' ".( $fieldtype === 'dob' ? 'selected="selected"' : '' ).">".esc_html__('Date of Birth','sejoli-lead-form')."</option>
            <option value='date' ".( $fieldtype === 'date' ? 'selected="selected"' : '' ).">".esc_html__('Date','sejoli-lead-form')." </option>        
            <option value='text' ".( $fieldtype === 'text' ? 'selected="selected"' : '' ).">".esc_html__('Text (Single Line Text)','sejoli-lead-form')."</option>
            <option value='textarea' ".( $fieldtype === 'textarea' ? 'selected="selected"' : '' ).">".esc_html__('Textarea (Multiple Line Text)','sejoli-lead-form')." </option>
            <option value='htmlfield' ".( $fieldtype === 'htmlfield' ? 'selected="selected"' : '' ).">".esc_html__('Content Area (Read only Text)','sejoli-lead-form')."</option>
            <option value='url' ".( $fieldtype === 'url' ? 'selected="selected"' : '' ).">".esc_html__('Url (Website url)','sejoli-lead-form')."</option>
            <option value='phonenumber' ".( $fieldtype === 'phonenumber' ? 'selected="selected"' : '' ).">".esc_html__('Phone Number','sejoli-lead-form')." </option>
            <option value='number' ".( $fieldtype === 'number' ? 'selected="selected"' : '' ).">".esc_html__('Number (Only Numeric 0-9 )','sejoli-lead-form')." </option>
            <option value='upload' ".( $fieldtype === 'upload' ? 'selected="selected"' : '' ).">".esc_html__('Upload File/Image','sejoli-lead-form')." </option>
            <option value='radio' ".( $fieldtype === 'radio' ? 'selected="selected"' : '' ).">".esc_html__('Radio (Choose Single Option)','sejoli-lead-form')."</option>    
            <option value='option' ".( $fieldtype === 'option' ? 'selected="selected"' : '' ).">".esc_html__('Option (Choose Single Option)','sejoli-lead-form')."</option>  
            <option value='checkbox' ".( $fieldtype === 'checkbox' ? 'selected="selected"' : '' ).">".esc_html__('Checkbox (Choose Multiple Option)','sejoli-lead-form')."</option>
            <option value='terms' ".( $fieldtype === 'terms' ? 'selected="selected"' : '' ).">".esc_html__('Checkbox (Terms & condition)','sejoli-lead-form')." </option>
            </select></td>";
        
        return $return;

    }

    /**
     * Field Default Value
     * @since   1.0.0
     */
    function lfbFieldDefaultValue($fieldv,$fieldID,$fieldtype=''){

        $defaultValue = isset($fieldv['default_value'])?$fieldv['default_value']:'';
        $hide = ($fieldtype=='terms')?'style=display:none;':'';
        $return = '<td><input '.$hide.' type="text" class="default_value" name="lfb_form[form_field_' . $fieldID . '][default_value]" id="default_value_' . $fieldID . '" value="'.$defaultValue.'">';

        return $return;

    }

    function lfbHtmlFieldValue($fieldv,$fieldID){
        $defaultValue = isset($fieldv['default_value'])?$fieldv['default_value']:'';
        $return = '<td colspan="3" ><div class="default_htmlfield_' . $fieldID . '" id="default_htmlfield"><textarea class="default_value default_htmlfield" name="lfb_form[form_field_' . $fieldID . '][default_value]" id="default_value_'. $fieldID . '">'.$defaultValue.'</textarea></div>';
        
        return $return;  
    }

    /**
     * Field Placeholder
     * @since   1.0.0
     */
    function lfbFieldPlaceholder($fieldv,$fieldID,$fieldtype){

        $fieldPlaceholder = isset($fieldv['default_phonenumber'])?$fieldv['default_phonenumber']:'';
        $isRequired = ($fieldPlaceholder == 1 ? 'checked' : "" );
        $hide = ($fieldtype=='terms')?'style=display:none;':'';

        $return = '<td><input '.$hide.' type="checkbox" class="default_phonenumber" name="lfb_form[form_field_' . $fieldID . '][default_phonenumber]" id="default_phonenumber_' . $fieldID . '" value="1" '.$isRequired.'></td>';
        
        return $return;

    }

    /**
     * Field Required
     * @since   1.0.0
     */
    function lfbFieldIsRequired($fieldv,$fieldID){

        $fieldRequired = isset($fieldv['is_required'])?$fieldv['is_required']:'';
        $isRequired = ($fieldRequired == 1 ? 'checked' : "" );
        $return = '<td><input type="checkbox" name="lfb_form[form_field_' . $fieldID . '][is_required]" id="is_required_'.$fieldID.'" value="1" '.$isRequired.'></td>';
        
        return $return;

    }

    /**
     * Remove Field Button
     * Hooked via action admin_menu
     * @since   1.0.0
     */
    function lfbRemoveField($fieldID){

        $return = '<td id="wpth_add_form_table_' . $fieldID . '">
            <input type="button" class="button lf_remove" name="remove_field" id="remove_field_' . $fieldID . '" onclick="remove_form_fields(' . $fieldID . ')" value="Remove">
            <input type="hidden" value="' . $fieldID . '" name="lfb_form[form_field_' . $fieldID . '][field_id]">
            </td>';
        
        return $return;

    }

    /**
     * Add Field
     * @since   1.0.0
     */
    function lfbAddField($fieldv,$fieldID,$lastFieldID){

        $return = '<td></td><td><input type="hidden" name="lfb_form[form_field_'.$fieldID.'][field_name]" id="field_name_'.$fieldID.'" value="submit"><select class="form_field_select" name="lfb_form[form_field_'.$fieldID.'][field_type][type]" id="field_type_'.$fieldID.'">
            <option value="submit" selected="selected">'.esc_html("Submit Button").'</option>
            </select></td>';

        $return .=$this->lfbFieldDefaultValue($fieldv,$fieldID);

        $fieldButton = '<span><input type="button" class="button lf_addnew" name="add_new" id="add_new_'.$lastFieldID.'" onclick="add_new_form_fields('.$lastFieldID.')" value="Add New"></span>';
        $return .='<td><input type="hidden" value="' . $fieldID . '" name="lfb_form[form_field_' . $fieldID . '][field_id]"></td>';
        $return .= '<td class="add-field" id="wpth_add_form_table_' . $fieldID . '">'.$fieldButton.'</td>';

        return $return;

    }

    /**
     * Field Type Text
     * @since   1.0.0
     */
    function lfbTypeText($fieldv,$fieldtype,$fieldID){

        $checkboxField = $isChecked = $return ='';
        $value = $this->lfbFormField($fieldtype);

        $return .= $this->lfbFieldName($fieldv,$fieldID);
        $return .= $this->lfbFieldTypeDefault($fieldtype,$value,$fieldID);
        $return .= $this->lfbFieldDefaultValue($fieldv,$fieldID,$fieldtype);
        $return .= $this->lfbFieldIsRequired($fieldv,$fieldID);
        $return .= $this->lfbRemoveField($fieldID);
        
        return $return;

    }

    /**
     * Field Textarea
     * @since   1.0.0
     */
    function lfbTypeTextarea($fieldv,$fieldtype,$fieldID){

        $return ='';
        
        $return .= $this->lfbFieldName($fieldv,$fieldID);
        $return .= $this->lfbFieldTypeDefault('message','Message',$fieldID);
        $return .= $this->lfbFieldDefaultValue($fieldv,$fieldID,$fieldtype);
        $return .= $this->lfbFieldIsRequired($fieldv,$fieldID);
        $return .= $this->lfbRemoveField($fieldID);

        return $return;

    }

    /**
     * Html Field
     * @since   1.0.0
     */
    function lfbhtmlfield($fieldv,$fieldtype,$fieldID){

        $return ='';
        
        $return .= $this->lfbFieldName($fieldv,$fieldID);
        $return .= $this->lfbFieldTypeDefault('htmlfield',esc_html__('Content Area (Read only Text)','sejoli-lead-form'),$fieldID);
        $return .= $this->lfbHtmlFieldValue($fieldv,$fieldID);
        $return .= $this->lfbRemoveField($fieldID);
        
        return $return;

    }

    /**
     * Select Field Option
     * @since   1.0.0
     */
    function lfbSelectOption($fieldv,$fieldtype,$fieldID){

        $optionField = $isChecked = $return ='';
        $lastFieldID = 0;
        unset($fieldtype['type']);
        foreach ($fieldtype as $key => $value) {
            $checkboxId = str_replace("field_", "", $key);
            $checked = isset($fieldv['default_value']['field']) && $fieldv['default_value']['field']==$checkboxId?'checked':'';

            $fieldMinus = '<p class="button lf_minus" id="delete_option_' . $checkboxId . '" onclick="delete_option_fields(' . $fieldID . ',' . $checkboxId . ')"><i class="fa fa-minus" aria-hidden="true"></i></p>';
            if($lastFieldID < $checkboxId){
                $lastFieldID = $checkboxId;
                $fieldPlus = '<p class="button lf_plus" id="add_new_option_' . $lastFieldID . '" onclick="add_new_option_fields(' . $fieldID . ',' . $lastFieldID . ')"><i class="fa fa-plus" aria-hidden="true"></i></p>';
            }

            $childOption = '<input type="text" class="input_option_val" name="lfb_form[form_field_' . $fieldID . '][field_type][field_' . $checkboxId . ']" id="option_field_' . $checkboxId . '" placeholder="First Choice" value="'.$value.'">';

            // default checked
            $isChecked .='<p id="default_option_value_' . $checkboxId . '">'.$value.' <input type="radio" class="checked" name="lfb_form[form_field_' . $fieldID . '][default_value][field]" id="default_option_value_' . $checkboxId . '" value="' . $checkboxId . '" '.$checked.'></p>';

            $optionField .= $childOption.$fieldMinus;
        }

        $optionField .= $fieldPlus;
        $return .=$this->lfbFieldName($fieldv,$fieldID);

        $return .= '<td>
            <select class="form_field_select" name="lfb_form[form_field_' . $fieldID . '][field_type][type]" id="field_type_' . $fieldID . '">
            <option value="option" selected="selected" >'.esc_html__("Option (Choose Single Option)","sejoli-lead-form").'</option>
            </select>
            <div class="add_radio_checkbox_' . $fieldID . '" id="add_radio_checkbox">
            <div class="" id="add_option">' . $optionField . '</div>
            </div>
            </td>
            <td><input  style="border:none;" type="text" class="default_value" name="lfb_form[form_field_' . $fieldID . '][default_value]" id="default_value_' . $fieldID . '" value="Choose Default Value" disabled="disabled">
            <div class="add_default_radio_checkbox_' . $fieldID . '" id="add_default_radio_checkbox">
            <div class="" id="default_add_option">' . $isChecked . '</div>
            </div>
            </td>';
           
        $return .= $this->lfbFieldIsRequired($fieldv,$fieldID);

        $return .= $this->lfbRemoveField($fieldID);

        return $return;

    }

    /**
     * Radio Button
     * @since   1.0.0
     */
    function lfbRadio($fieldv,$fieldtype,$fieldID){

        $optionField = $isChecked = $return ='';
        $lastFieldID = 0;
        unset($fieldtype['type']);
        
        foreach ($fieldtype as $key => $value) {
            $checkboxId = str_replace("field_", "", $key);
            $checked = isset($fieldv['default_value']['field']) && $fieldv['default_value']['field']==$checkboxId?'checked':'';

            $fieldMinus = '<p class="button lf_minus" id="delete_radio_' . $checkboxId . '" onclick="delete_radio_fields(' . $fieldID . ',' . $checkboxId . ')"><i class="fa fa-minus" aria-hidden="true"></i></p>';

            if($lastFieldID < $checkboxId){
                $lastFieldID = $checkboxId;
                $fieldPlus = '<p class="button lf_plus" id="add_new_radio_' . $lastFieldID . '" onclick="add_new_radio_fields(' . $fieldID . ',' . $lastFieldID . ')"><i class="fa fa-plus" aria-hidden="true"></i></p>';
            }
        
            $childOption = '<input type="text" class="input_radio_val" name="lfb_form[form_field_' . $fieldID . '][field_type][field_' . $checkboxId . ']" id="radio_field_' . $checkboxId . '" placeholder="'.esc_html__("First Choice","sejoli-lead-form").'" value="'.$value.'">';

            // default checked
            $isChecked .='<p id="default_radio_value_' . $checkboxId . '">'.$value.' <input type="radio" class="checked" name="lfb_form[form_field_' . $fieldID . '][default_value][field]" id="default_radio_value_' . $checkboxId . '" value="' . $checkboxId . '" '.$checked.'></p>';

            $optionField .= $childOption.$fieldMinus;
        }
        
        $optionField .= $fieldPlus;

        $return .=$this->lfbFieldName($fieldv,$fieldID);

        $return .= '<td>
            <select class="form_field_select" name="lfb_form[form_field_' . $fieldID . '][field_type][type]" id="field_type_' . $fieldID . '" >
            <option value="radio" selected="selected" >'.esc_html__("Radio (Choose Single Option)","sejoli-lead-form").'</option>
            </select>
            <div class="add_radio_checkbox_' . $fieldID . '" id="add_radio_checkbox">
            <div class="" id="add_radio">' . $optionField . '</div>
            </div>
            </td>
            <td><input type="text" class="default_value" name="lfb_form[form_field_' . $fieldID . '][default_value]" id="default_value_' . $fieldID . '" value="'.esc_html__("Choose Default Value","sejoli-lead-form").'" disabled="disabled">
            <div class="add_default_radio_checkbox_' . $fieldID . '" id="add_default_radio_checkbox">
            <div class="" id="default_add_radio">' . $isChecked . '</div>
            </div>
            </td>';

        $return .= '<td>-</td>';
           
        $return .= $this->lfbFieldIsRequired($fieldv,$fieldID);

        $return .= $this->lfbRemoveField($fieldID);

        return $return;

    }

    /**
     * Checkbox Option
     * @since   1.0.0
     */
    function lfbCheckbox($fieldv,$fieldtype,$fieldID){

        $checkboxField = $isChecked = $return ='';
        $lastFieldID = 0;
        unset($fieldtype['type']);
        
        foreach ($fieldtype as $key => $value) {
            $checkboxId = str_replace("field_", "", $key);
            $checked = isset($fieldv['default_value'][$key])?'checked':'';

            $fieldMinus = '<p class="button lf_minus" id="delete_checkbox_' . $checkboxId . '" onclick="delete_checkbox_fields(' . $fieldID . ',' . $checkboxId . ')"><i class="fa fa-minus" aria-hidden="true"></i></p>';

            if($lastFieldID < $checkboxId){
                $lastFieldID = $checkboxId;
                $fieldPlus = '<p class="button lf_plus" id="add_new_checkbox_' . $lastFieldID . '" onclick="add_new_checkbox_fields(' . $fieldID . ',' . $lastFieldID . ')"><i class="fa fa-plus" aria-hidden="true"></i></p>';
            }

            $childCheckbox = '<input type="text" class="input_checkbox_val" name="lfb_form[form_field_' . $fieldID . '][field_type][field_' . $checkboxId . ']" id="checkbox_field_' . $checkboxId . '" placeholder="First Choice" value="'.$value.'">';

            // default checked
            $isChecked .='<p id="default_checkbox_value_' . $checkboxId . '">'.$value.' <input type="checkbox" class="checked" name="lfb_form[form_field_' . $fieldID . '][default_value][field_' . $checkboxId . ']" id="default_checkbox_val_' . $checkboxId . '" value="1" '.$checked.' ></p>';

            $checkboxField .= $childCheckbox.$fieldMinus;
        }

        $checkboxField .= $fieldPlus;
        $return .=$this->lfbFieldName($fieldv,$fieldID);

        $return .= '<td>
            <select class="form_field_select" name="lfb_form[form_field_' . $fieldID . '][field_type][type]" id="field_type_' . $fieldID . '" >
            <option value="checkbox" selected="selected" >'.esc_html__("Checkbox (Choose Multiple Option)","sejoli-lead-form").'</option>
            </select>
            <div class="add_radio_checkbox_' . $fieldID . '" id="add_radio_checkbox">
            <div class="" id="add_checkbox">' . $checkboxField . '</div>
            </div>
            </td>
            <td><input type="text" class="default_value" name="lfb_form[form_field_' . $fieldID . '][default_value]" id="default_value_' . $fieldID . '" value="'.esc_html__("Choose Default Value","sejoli-lead-form").'" disabled="disabled">
            <div class="add_default_radio_checkbox_' . $fieldID . '" id="add_default_radio_checkbox">
            <div class="" id="default_add_checkbox">' . $isChecked . '</div>
            </div>
            </td>';

        $return .= '<td>-</td>';
           
        $return .= $this->lfbFieldIsRequired($fieldv,$fieldID);

        $return .= $this->lfbRemoveField($fieldID);

        return $return;

    }

    /**
     * Set Field Type
     * @since   1.0.0
     */
    function lfbFieldType($fieldv,$fieldID){

        $text = array('name','email','url','phonenumber','number','text','date','dob','upload','terms');
        $textarea = array('message','textarea');
        $upload = array('upload','file');
        $fieldtype = $fieldv['field_type'];
        $fType = $fieldv['field_type']['type'];

        if($fType=='checkbox'){
            return $this->lfbCheckbox($fieldv,$fieldtype,$fieldID);
        } elseif($fType=='option') {
            return $this->lfbSelectOption($fieldv,$fieldtype,$fieldID);
        }  elseif($fType=='radio') {
            return $this->lfbRadio($fieldv,$fieldtype,$fieldID);
        } elseif($fType=='htmlfield') {
            return $this->lfbhtmlfield($fieldv,$fieldtype,$fieldID);
        } elseif(in_array($fType, $text)){
            return $this->lfbTypeText($fieldv,$fType,$fieldID);
        } elseif(in_array($fType, $upload)){
            return $this->lfbTypeText($fieldv,$fType,$fieldID);
        } elseif(in_array($fType, $textarea)){
            return $this->lfbTypeTextarea($fieldv,$fType,$fieldID);
        }

    }

    /**     
     * For each for each form fields 
     * @since   1.0.0
     */
    function lfb_create_form_fields_for_edit($form_title, $form_data_result) {

        $all_form_fields = "";
        $total_form_fields = count($form_data_result);
        $field_counter = 0;
        $fieldRow = $addButton = '';
        $lastFieldID = 0;
         
        foreach ($form_data_result as $fieldv) {
            $fieldID = $fieldv['field_id'];

            if($lastFieldID < $fieldID){
                $lastFieldID = $fieldID;
            }

            if($fieldID==0){
                $addButton = $this->lfbAddField($fieldv,$fieldID,$lastFieldID);
            }else{
                $tr = $this->lfbFieldType($fieldv,$fieldID);
                $fieldRow .= '<tr id="form_field_row_' . $fieldID . '">'.$tr.'</tr>';
            }
        }

        return '<tbody class="append_new">'.$fieldRow.'</tbody>'.$addButton;
        
    }
}