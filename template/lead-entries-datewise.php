<?php ob_start();

$tableHead  = '';
foreach ($fieldData as $fieldkey => $fieldvalue) {
    // Html Field removed
    $pos = strpos($fieldkey, 'htmlfield_');
    if ($pos !== false) {
        continue;
    }

    if($headcount < 6){
        $tableHead  .='<th>' . $fieldvalue . '</th>';
    }
    $fieldIdNew[] = $fieldkey;
    $headcount++;
}

if (!empty($posts)) {
    $entry_counter = 0;
    $value1 = 0;
    $table_head = '';
    $table_body = '';
    $popupTab   = '';
   
    if($headcount >= 6){
        $table_head .='<th> . . . </th><th> <input type="button" onclick="show_all_leads(' . $id . ',' . $form_id . ')" value="'.esc_html__('Show all Columns','sejoli-lead-form').'"></th>';
    }
    
    foreach ($posts as $results) {
        $table_row = '';
        $sn_counter++;
        $row_size_limit = 0;
        $form_data = $results->form_data;
        $lead_id = $results->id;
        $product_id = $results->product;
        $product    = sejolisa_get_product($product_id);
        $affiliate_id = $results->affiliate;
        $affiliate    = sejolisa_get_user($affiliate_id);
        $form_data = maybe_unserialize($form_data);
        $lead_date = date("j M Y", strtotime($results->date));
        $get_status = $results->status;
        if ($get_status === "lead") {
            $status = '<button type="submit" class="button button-small button-status-lead" data-lead-id="'.$lead_id.'">'. __('Lead', 'sejoli-lead-form') .' </button>';
        } else {
            $status = '<a href="#" class="button button-small button-status-customer">'. __('Customer', 'sejoli-lead-form') .' </a>';
        }
        unset($form_data['hidden_field']);
        unset($form_data['action']);
        unset($form_data['g-recaptcha-response']);
        $entry_counter++;
        $complete_data = '';
        $popup_data_val= '';
        $date_td = '<td><b>'.$lead_date.'</b></td>';

        $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,5);

        $table_row .= "<td>".$product->post_title."</td>";
        $table_row .= $returnData['table_row'];
        if($affiliate_id > 0) {
            $table_row .= "<td>".$affiliate->display_name."</td>";
        } else {
            $table_row .= "<td>-</td>";
        }
        $table_row .= "<td>".sejolisa_price_format($product->price)."</td>";
        $table_row .= $date_td;
        $form = $th_save_db->lfb_get_form_data($results->form_id);
        $form_data_result = maybe_unserialize($form[0]->form_data);

        global $wpdb;
        $table_form = LFB_FORM_FIELD_TBL;
        // $prepare_9 = $wpdb->prepare("SELECT * FROM $table_form WHERE id = %d LIMIT 1", $results->form_id);
        // $posts = $th_save_db->lfb_get_form_content($prepare_9);
        // if ($posts) {
        //     $followup_text = maybe_unserialize($posts[0]->followup_setting);;
        // }

        // $text_follow = '';
        // foreach ($form_data_result as $results) {
        //     $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
        //     if ( $type === 'phonenumber' ) {
        //         $field_id = $results['field_id'];
        //         $phone_number = isset($form_data['phonenumber_'.$field_id]) ? $this->phone_number_format($form_data['phonenumber_'.$field_id]) : '';
        //         if ( wp_is_mobile() ) :
        //             $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://wa.me/'.$phone_number  . '?text='. $followup_text .'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
        //         else :
        //             $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://api.whatsapp.com/send?phone='.$phone_number.'&text='.$followup_text.'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
        //         endif;
        //         $text_follow = "Follow Up";
        //     }
        // }

        $table_row .= '<td>'.$status.'</td>';

        $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

        $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
            <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
            </div>
            </div>';

        $table_body .= '<tr><td><span class="lead-count"><a href="#lf-openModal-' . $lead_id . '" title="View Detail">#' . $sn_counter . '</a></td>'. $table_row .'</tr>';
    }

    $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat fixed" id="show-leads-table" >
        <thead><tr><th>ID</th><th>Product</th>'.$tableHead.'<th>Affiliate</th><th>Value</th><th>Date</th>'.$table_head.'<th>Status</th></tr></thead>';

    echo wp_kses($thHead. $table_body.'</table>'.$popupTab,$this->expanded_alowed_tags());
} else {
    ?> <div class="wrap" id="form-leads-show"><?php
    esc_html_e("No leads founds..!","sejoli-lead-form");
    ?> </div> <?php
}

$html = ob_get_contents();
ob_end_clean();
?>  