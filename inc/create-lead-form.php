<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once('lf-db.php');
require_once('edit-delete-form.php');

/**
 * Create Form Sanitize
 * @since   1.0.0
 */
function lfb_create_form_sanitize($form_data){
    $fieldArr =array('name','email','message','dob','date','url','text','textarea','phonenumber','number','submit');
    $field_rco =array('option','checkbox','radiopanggilan', 'radio');
    foreach($form_data as $key=>$value){
        if(in_array($value['field_type']['type'], $fieldArr)){
            $form_data[$key]['field_name'] = sanitize_text_field($value['field_name']);
            $form_data[$key]['default_value'] = sanitize_text_field($value['default_value']);
            $form_data[$key]['field_id'] = intval($value['field_id']);

            if(isset($value['is_required'])){
                $form_data[$key]['is_required'] = intval($value['is_required']);

            }

        }elseif($value['field_type']['type']=='htmlfield'){
          $form_data[$key]['field_name'] = wp_kses_post($value['field_name']);
          $form_data[$key]['default_value'] = wp_kses_post($value['default_value']);
          $form_data[$key]['field_id'] = intval($value['field_id']);

          if(isset($value['is_required'])){
            $form_data[$key]['is_required'] = intval($value['is_required']);

        }
        }elseif(in_array($value['field_type']['type'], $field_rco)){
            foreach ($value['field_type'] as $fkey => $fvalue) {
                $form_data[$key]['field_type'][$fkey] = sanitize_text_field($fvalue);
             }
             $form_data[$key]['field_name'] = sanitize_text_field($value['field_name']);
             $form_data[$key]['field_id'] = intval($value['field_id']);


             if(isset($value['is_required'])){
                $form_data[$key]['is_required'] = intval($value['is_required']);
            }

            // default value check radio , checkbox, option
            if(isset($form_data[$key]['default_value']['field']) && ($value['field_type']['type'] =='radio' || $value['field_type']['type'] =='option')){
                $form_data[$key]['default_value']['field'] = intval($value['default_value']['field']);
            } elseif(isset($form_data[$key]['default_value']['field']) && ($value['field_type']['type'] =='checkbox') ){
                foreach ($form_data[$key]['default_value'] as $ckey => $cvalue) {
                    $form_data[$key]['default_value'][$ckey] = intval($cvalue);
                }

            }

        }
    }

    return $form_data;
}

if (sanitize_text_field(isset($_POST['save_form'])) && wp_verify_nonce($_REQUEST['_wpnonce'],'_nonce_verify')) {
    $form_data=isset($_POST['lfb_form'])?$_POST['lfb_form']:'';

    $title = sanitize_text_field($_POST['post_title']);
    $product = absint($_POST['product']);
    $form_url = esc_url($_POST['form_page_url']);
    unset($_POST['post_title']);
    unset($_POST['product']);
    unset($_POST['form_page_url']);
    unset($_POST['save_form']);
    unset($_POST['_wpnonce']);
    $form_data= maybe_serialize(lfb_create_form_sanitize($form_data));
    global $wpdb;
    $table_name = LFB_FORM_FIELD_TBL;

    $wpdb->query( $wpdb->prepare( 
    "INSERT INTO $table_name ( form_title, product, form_data, form_url, date ) VALUES ( %s, %s, %s, %s, %s )",
    $title, $product, $form_data, $form_url, date('Y/m/d g:i:s') ) );

    $rd_url = admin_url().'admin.php?page=add-new-form&action=edit&redirect=create&formid='.$wpdb->insert_id.'&_wpnonce='.$_REQUEST['_wpnonce'];
    wp_redirect($rd_url);
}

Class LFB_AddNewForm {

    /**
     * Add New Form
     * @since   1.0.0
     */
    function lfb_add_new_form(){

        echo '<div class="wrap">';
        include_once( plugin_dir_path(__FILE__) . 'header.php' );

        echo '<h2 class="nav-tab-wrapper">
            <a class="nav-tab nav-tab-active lead-form-create-form" href="#">'.esc_html__('Create Form','sejoli-lead-form').'</a>
            <a class="nav-tab lead-form-email-setting" href="#">'.esc_html__('Email Setting','sejoli-lead-form').'</a>
            <a class="nav-tab lead-form-wa-setting" href="#">'.esc_html__('WhatsApp Setting','sejoli-lead-form').'</a>
            <a class="nav-tab lead-form-customer-setting" href="#">'.esc_html__('Customer Setting','sejoli-lead-form').'</a>
            <a class="nav-tab lead-form-captcha-setting" href="#">'.esc_html__('Captcha Setting','sejoli-lead-form').'</a>
            <a class="nav-tab lead-form-setting" href="#">'.esc_html__('Setting','sejoli-lead-form').'</a>
            </h2>
            <div id="sections">
            <section>';
        if (is_admin()) {
            $this->lfb_add_form_setting();
        }
        echo '</section>
        <section>';
        
        if (is_admin()) {
            echo '<div class="wrap">
            <div class="form-block" style="margin-top: 1.5em;">
                <div class="infobox">
                <h1>'.esc_html__('Email Setting','sejoli-lead-form').'</h1></div>
                <br class="clear"><div class="inside setting_section">
                <div class="card" style="margin: 0;padding: 0;">
                <form name="" id="new-lead-email-setting" method="post" action="">
                <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
                </form>            
                </div>
                </div><br class="clear"></div></div>';
        }
        echo '</section>
        <section>';

        if (is_admin()) {
            echo '<div class="wrap">
            <div class="form-block" style="margin-top: 1.5em;">
                <div class="infobox">
                <h1>'.esc_html__('WhatsApp Setting','sejoli-lead-form').'</h1></div>
                <br class="clear"><div class="inside setting_section">
                <div class="card" style="margin: 0;padding: 0;">
                <form name="" id="new-lead-email-setting" method="post" action="">
                <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
                </form>            
                </div>
                </div><br class="clear"></div></div>';
        }
        echo '</section>
        <section>';

        // if (is_admin()) {
        //     echo '<div class="wrap">
        //     <div class="form-block" style="margin-top: 1.5em;">
        //         <div class="infobox">
        //         <h1>'.esc_html__('SMS Setting','sejoli-lead-form').'</h1></div>
        //         <br class="clear"><div class="inside setting_section">
        //         <div class="card" style="margin: 0;padding: 0;">
        //         <form name="" id="new-lead-email-setting" method="post" action="">
        //         <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
        //         </form>            
        //         </div>
        //         </div><br class="clear"></div></div>';
        // }
        // echo '</section>
        // <section>';

        if (is_admin()) {
            echo '<div class="wrap">
            <div class="form-block" style="margin-top: 1.5em;">
                <div class="infobox">
                <h1>'.esc_html__('Autoresponder Setting','sejoli-lead-form').'</h1></div>
                <br class="clear"><div class="inside setting_section">
                <div class="card" style="margin: 0;padding: 0;">
                <form name="" id="new-lead-email-setting" method="post" action="">
                <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
                </form>            
                </div>
                </div><br class="clear"></div></div>';
        }
        echo '</section>
        <section>';

        // if (is_admin()) {
        //     echo '<div class="wrap">
        //     <div class="form-block" style="margin-top: 1.5em;">
        //         <div class="infobox">
        //         <h1>'.esc_html__('Follow Up Setting','sejoli-lead-form').'</h1></div>
        //         <br class="clear"><div class="inside setting_section">
        //         <div class="card" style="margin: 0;padding: 0;">
        //         <form name="" id="new-lead-email-setting" method="post" action="">
        //         <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
        //         </form>            
        //         </div>
        //         </div><br class="clear"></div></div>';
        // }
        // echo '</section>
        // <section>';

        if (is_admin()) {
            echo '<div class="wrap">
            <div class="form-block" style="margin-top: 1.5em;">
                <div class="infobox">
                <h1>'.esc_html__('Customer Setting','sejoli-lead-form').'</h1></div>
                <br class="clear"><div class="inside setting_section">
                <div class="card" style="margin: 0;padding: 0;">
                <form name="" id="new-lead-email-setting" method="post" action="">
                <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
                </form>            
                </div>
                </div><br class="clear"></div></div>';
        }
        echo '</section>
        <section>';

        if (is_admin()) {
            echo '<div class="wrap">
            <div class="form-block" style="margin-top: 1.5em;">
                <div class="infobox">
                <h1>'.esc_html__('Captcha Setting','sejoli-lead-form').'</h1></div>
                <br class="clear"><div class="inside setting_section">
                <div class="card" style="margin: 0;padding: 0;">
                <form name="" id="new-captcha-setting" method="post" action="">
                <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
                </form>            
                </div>
                </div><br class="clear"></div></div>';
        }
        echo '</section><section>';
        
        if (is_admin()) {
            echo '<div class="wrap">
            <div class="form-block" style="margin-top: 1.5em;">
                <div class="infobox">
                <h1>'.esc_html__('Lead Receiving Method','sejoli-lead-form').'</h1></div>
                <br class="clear"><div class="inside setting_section">
                <div class="card" style="margin: 0;padding: 0;">
                <form name="" id="new-lead-form-setting" method="post" action="">
                <p class="sec_head">'.esc_html__('Please create and save your Lead Form to do these settings.','sejoli-lead-form').'</p>  
                </form>            
                </div>
                </div><br class="clear"></div></div>';
        }
        echo '</section></div>
            </div>';

    }

    /**
     * Get Product on Leads
     * @since   1.0.0
     */
    function sejoli_lead_get_product() {
        $html = '';
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'sejoli-product'
        );

        if( $products = get_posts( $args ) ) {
            $html .= "<div id='titlewrap'>";
            $html .= "<div class='label-form'><label>".esc_html__('Product','sejoli-lead-form')."</label></div>";
            $html .= '<div class="field-form"><select id="sejoli_lead_select2_products" name="product">';
            $html .= '<option value="">Select a Product</option>';
            foreach( $products as $product ) {
                $html .= '<option value="' . $product->ID . '">' . $product->post_title . '</option>';
            }
            $html .= '<select></div></div><!-- #titlewrap -->';
        }

        return $html;
    }

    /**
     * Add Form Settings
     * @since   1.0.0
     */
    function lfb_add_form_setting() {

        $nonce = wp_create_nonce( '_nonce_verify' );

        $create_url ="admin.php?page=add-new-form&action=edit&redirect=create&_wpnonce=".$nonce;

        echo "<div>";
        echo "<form method='post' action='".esc_url($create_url)."' id='new_lead_form'>
            <div id='poststuff' style='padding-top: 20px;'>
            <div id='post-body'>
            <div id='post-body-content' class='form-block'>
            <h2>".esc_html__('Add New From','sejoli-lead-form')."</h2>
            <div id='titlediv'>
            <div id='titlewrap'>
            <div class='label-form'><label>".esc_html__('Form Name','sejoli-lead-form')."</label></div>
            <div class='field-form'><input type='text' class='new_form_heading' style='width: 100%;' name='post_title' placeholder='".esc_html__('Enter form name here','sejoli-lead-form')."' value='' size='30' id='title' spellcheck='true' autocomplete='off'></div></div><!-- #titlewrap -->
            </br>
            ".$this->sejoli_lead_get_product()."
            </br>
            <div id='titlewrap'>
            <div class='label-form'><label>".esc_html__('Url','sejoli-lead-form')."</label></div>
            <div class='field-form'><input type='text' class='new_form_heading' name='form_page_url' placeholder='".esc_html__('Enter url here','sejoli-lead-form')."' value='' size='30' id='title' spellcheck='true' autocomplete='off'></div></div><!-- #titlewrap -->
            </br>
            <input type='hidden' name = '_wpnonce' value='".$nonce."' />
            <div class='inside'>
            </div>
            </div><!-- #titlediv -->
            </div><!-- #post-body-content -->
            </div>
            </div>";

        $this->lfb_basic_form();
        $this->lfb_form_first_fields();
        echo "<div id='append_new_field'></div>";
        $this->lfb_form_last_fields();
        echo "</table>
            </div>
            </div>
            <div class='form-block'>
            <p class='submit' style='text-align:right'><input type='submit' class='save_form button-primary' style='background: #ff4545; margin: 0 0 0 0;'  name='save_form' id='save_form' value='".esc_html__('Save Form','sejoli-lead-form')."'></p></td>
            </form><div id='message-box-error' class='message-box-error' ></div>
            </div>
            </div>";

    }

    /**
     * Create Basic Form
     * @since   1.0.0
     */
    function lfb_basic_form() {

        echo "<div class='inside spth_setting_section'  id='wpth_add_form'>
            <div class='form-block'>
            <h2>".esc_html__('Form Fields','sejoli-lead-form')."</h2>
            <table class='widefat' id='sortable'>
            <thead>
            <tr>
            <th>".esc_html__('Field name','sejoli-lead-form')." </th>
            <th>".esc_html__('Field Type','sejoli-lead-form')." </th>
            <th>".esc_html__('Default Value','sejoli-lead-form')." </th>
            <th>".esc_html__('Required','sejoli-lead-form')." </th>
            <th>".esc_html__('Action','sejoli-lead-form')." </th>
            </tr></thead>";

    }

    /**
     * Create Form Field
     * @since   1.0.0
     */
    function lfb_form_first_fields() {

        echo "<tbody class='append_new' ><tr id='form_field_row_1'>
            <td><input type='text' name='lfb_form[form_field_1][field_name]' id='field_name_1' value=''></td>
            <td>
            <select class='form_field_select' name='lfb_form[form_field_1][field_type][type]' id='field_type_1'>
            <option value='select'>".esc_html__('Select Field Type','sejoli-lead-form')."</option>
            <option value='name'>".esc_html__('Name','sejoli-lead-form')."</option>		    
            <option value='email'>".esc_html__('Email','sejoli-lead-form')."</option>
            <option value='message'>".esc_html__('Message','sejoli-lead-form')."</option>
            <option value='dob'>".esc_html__('Date of Birth','sejoli-lead-form')."</option>
            <option value='date'>".esc_html__('Date','sejoli-lead-form')." </option>	    
            <option value='text'>".esc_html__('Text (Single Line Text)','sejoli-lead-form')."</option>
            <option value='textarea'>".esc_html__('Textarea (Multiple Line Text)','sejoli-lead-form')." </option>
            <option value='htmlfield'>".esc_html__('Content Area (Read only Text)','sejoli-lead-form')."</option>
            <option value='url'>".esc_html__('Url (Website url)','sejoli-lead-form')."</option>
            <option value='phonenumber'>".esc_html__('Phone Number','sejoli-lead-form')." </option>
            <option value='number'>".esc_html__('Number (Only Numeric 0-9 )','sejoli-lead-form')." </option>
            <option value='upload'>".esc_html__('Upload File/Image','sejoli-lead-form')." </option>
            <option value='radiopanggilan'>".esc_html__('Radio (Panggilan)','sejoli-lead-form')."</option>    
            <option value='radio'>".esc_html__('Radio (Choose Single Option)','sejoli-lead-form')."</option>    
            <option value='option'>".esc_html__('Option (Choose Single Option)','sejoli-lead-form')."</option>  
            <option value='checkbox'>".esc_html__('Checkbox (Choose Multiple Option)','sejoli-lead-form')."</option>
            <option value='terms'>".esc_html__('Checkbox (Terms & condition)','sejoli-lead-form')." </option>
            </select>
            <div class='add_radio_checkbox_1' id='add_radio_checkbox'>
            <div class='' id='add_radio'></div>
            <div class='' id='add_checkbox'></div>
            <div class='' id='add_option'></div>
            </div>
            </td>
            <td><input type='text' class='default_value' name='lfb_form[form_field_1][default_value]' id='default_value_1' value=''>
            <div class='default_htmlfield_1' id='default_htmlfield'></div>
            <div class='add_default_radio_checkbox_1' id='add_default_radio_checkbox'>
            <div class='' id='default_add_radio'></div>
            <div class='' id='default_add_checkbox'></div>
            <div class='' id='default_add_option'></div>
            </div>
            <div class='default_terms_1' id='default_terms'></div>
            </td>
            <td><input type='checkbox' name='lfb_form[form_field_1][is_required]' id='is_required_1' value='1'>
            </td>
            <td id='wpth_add_form_table_1'>
            <input type='hidden' value='1' name='lfb_form[form_field_1][field_id]'>
            </td>
            </tr></tbody>";

    }

    /**
     * Create Form Last Field
     * @since   1.0.0
     */
    function lfb_form_last_fields(){
        echo "<tr id='form_field_row_0'><td></td>
            <td>
            <input type='hidden' name='lfb_form[form_field_0][field_name]' id='field_name_0' value='submit'>
            <select class='form_field_select' name='lfb_form[form_field_0][field_type][type]' id='field_type_0'>        
            <option value='submit'>".esc_html__('Submit Button','sejoli-lead-form')." </option>
            </select>
            </td>            
            <td><input type='text' class='default_value' name='lfb_form[form_field_0][default_value]' id='default_value_0' value='".esc_html__('SUBMIT','sejoli-lead-form')."'>
            </td>
            <td><input type='hidden' name='lfb_form[form_field_0][is_required]' checked id='is_required_0' value='1'>
            <input type='hidden' value='0' name='lfb_form[form_field_0][field_id]'>
            </td>
            </td>
            <td class='add-field'><span><input type='button' class='button lf_addnew' name='add_new' id='add_new_1' onclick='add_new_form_fields(1)' value='".esc_html__('Add New')."'></span>
            </td>
            </tr>";
    }

}

/**
 * Register admin Scripts.
 * Hooked via action admin_enqueue_scripts
 * @since   1.0.0
 */
add_action( 'admin_enqueue_scripts', 'rudr_select2_enqueue' );
function rudr_select2_enqueue(){
    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );
}

/**
 * Get Product via Ajax
 * Hooked via action wp_ajax_sejoli_lead_product
 * @since   1.0.0
 */
add_action( 'wp_ajax_sejoli_lead_product', 'sejoli_lead_get_products_ajax_callback' );
function sejoli_lead_get_products_ajax_callback() {

    // we will pass post IDs and titles to this array
    $return = array();

    // you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
    $search_results = new WP_Query( array( 
        'post_type' => 'sejoli-product',
        's'=> $_GET['q'], // the search query
        'post_status' => 'publish', // if you don't want drafts to be returned
        'ignore_sticky_posts' => 1,
        'posts_per_page' => -1 // how much to show at once
    ) );
    if( $search_results->have_posts() ) :
        while( $search_results->have_posts() ) : $search_results->the_post();   
            // shorten the title a little
            $title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
            $return[] = array( $search_results->post->ID, $title ); // array( Post ID, Post Title )
        endwhile;
    endif;
    
    echo json_encode( $return );

    die;

}