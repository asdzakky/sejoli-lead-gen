<?php ob_start();

echo '<div class="form-block">';
    echo '<div class="wrap"><div class="inside"><div class="card"><table class="form-table"><tbody><tr><th scope="row">
        <label for="select_form_lead">'.esc_html__('Select From','sejoli-lead-form').'</label></th>
        <td><select name="select_form_lead" id="select_form_lead">' . wp_kses($option_form,$this->expanded_alowed_tags()) . '</select>
        <td><input rem_nonce = "'.$rem_nonce.'" type="button" value="'.esc_html__('Show Entries','sejoli-lead-form').'" onclick="remember_this_form_id();" id="remember_this_form_id"></td>
        </tr><tr><td><div id="remember_this_message" ></div></td></tr></tbody></table></div></div></div><div class="wrap" id="form-leads-shows-box">';
    if(wp_is_mobile()){
        echo '<div class="wrap" style="display: inline-block;width: 100%; margin-bottom: 2em !important;">';
            if (!empty($query_forms) && isset($query_forms[0]->form_title)) {
                echo '<h3 style="margin-bottom: 3em; margin-top: 1em;float: left;">' . esc_html($query_forms[0]->form_title) . '</h3>';
            } else {
                echo '<h3 style="margin-bottom: 3em; margin-top: 1em;float: left;">Form Tidak Ditemukan</h3>';
            }
            echo '<div class="export-button"></div><table class="form-table" style="margin-bottom: 1.5em; float: none;width: `00%;clear: inherit; margin: 0;"><tbody><tr><th scope="row" style="width: auto !important; padding: 20px 0; text-align: left;">
            <label for="filter_lead_entries">'.esc_html__('Filter Data','sejoli-lead-form').'</label></th><td><input type="text" name="filter-lead-entries" id="filter_lead_entries"/><input type="hidden" name="form_id_filter" value="'.$first_form_id.'"/></td></tr></tbody></table>';
        echo '</div>';
    } else {
        echo '<div class="wrap" style="display: inline-block;width: 100%; margin-bottom: 1.5em !important;">';
            if (!empty($query_forms) && isset($query_forms[0]->form_title)) {
                echo '<h3 style="margin-bottom: 3em; margin-top: 1em;float: left;">' . esc_html($query_forms[0]->form_title) . '</h3>';
            } else {
                echo '<h3 style="margin-bottom: 3em; margin-top: 1em;float: left;">Form Tidak Ditemukan</h3>';
            }
            echo '<div class="export-button"></div><table class="form-table" style="float: right;width: 23%;clear: inherit; margin: 0;"><tbody><tr><th scope="row" style="width: auto !important; padding: 20px 0; text-align: right;">
            <label for="filter_lead_entries">'.esc_html__('Filter Data','sejoli-lead-form').'</label></th><td><input type="text" name="filter-lead-entries" id="filter_lead_entries"/><input type="hidden" name="form_id_filter" value="'.$first_form_id.'"/></td></tr></tbody></table>';
        echo '</div>';
    }
    $this->lfb_show_leads_first_form($first_form_id);
    echo '<div class="loading" style="display: none;">'.esc_html__('Please Wait...', 'sejoli-lead-form').'</div>';
    echo '</div>';
echo '</div>';

$html = ob_get_contents();
ob_end_clean();
?>  