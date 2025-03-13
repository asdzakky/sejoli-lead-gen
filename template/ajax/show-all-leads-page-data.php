<?php ob_start();

foreach ($fieldData as $fieldkey => $fieldvalue) {
    if ($headcount < 6) {
        $tableHead  .= '<th>' . $fieldvalue . '</th>';
    }
    $fieldIdNew[] = $fieldkey;

    $headcount++;
}

if (!empty($posts)) {
    $entry_counter = 0;
    $value1 = 0;
    $table_body = '';
    $table_head = '';
    $popupTab   = '';

    if ($headcount >= 6) {
        $table_head .= '<th><input type="button" onclick="show_all_leads(' . $id . ',' . $form_id . ')" value="Show all fields"></th>';
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
            $status = '<button type="submit" class="button button-small button-status-lead">'. __('Lead', 'sejoli-lead-form') .' </button>';
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

        $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,100);
        $table_row .= "<td>".$product->post_title."</td>";
        $table_row .= $returnData['table_row'];

        if($affiliate->display_name) {
            $table_row .= "<td>".$affiliate->display_name."</td>";
        } else {
            $table_row .= "<td>-</td>";
        }

        $table_row .= $date_td;
        $table_row .= '<td></span><a class="lead-followup-wa"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></span></span><a class="lead-remove" onclick="delete_this_lead(' . $lead_id . ',\''.$nonce.'\')"><i class="fa fa-trash" aria-hidden="true" title="Hapus"></i></a></span></td>';

        $table_row .= '<td>'.$status.'</td>';

        $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

        $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
        <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
        </div>
        </div>';

        $table_body .= '<tr><td><span class="lead-count"><a href="#lf-openModal-' . $lead_id . '" title="View Detail">#' . $sn_counter . '</a></td>'. $table_row .'</tr>';
    }

    echo wp_kses($thHead . $table_body . '</table>' . $popupTab, $showLeadsObj->expanded_alowed_tags());

    $rows = count($rows);
} else {
    esc_html_e('No leads founds..!', 'sejoli-lead-form');
}

$html = ob_get_contents();
ob_end_clean();
?>  