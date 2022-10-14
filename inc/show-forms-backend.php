<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

Class LFB_SHOW_FORMS {
    function lfb_show_form_nonce(){
        $nonce = wp_create_nonce( '_nonce_verify' );
        
        return $nonce;
    }

    function lfb_show_all_forms($id) {
        $lfb_admin_url = admin_url();
        echo '<div class="wrap show-all-form">';
        include_once( plugin_dir_path(__FILE__) . 'header.php' );
        echo '<div>
            <table class="wp-list-table widefat fixed striped posts ">
        	<thead>
        	<tr>
        		<th scope="col" id="title" class="manage-column column-title column-primary sortable asc">'.esc_html__('Form Name','lead-form-builder').'</th>
        		<th scope="col" id="product" class="manage-column column-product">'.esc_html__('Product','lead-form-builder').'</th>
                <th scope="col" id="shortcode" class="manage-column column-shortcode">'.esc_html__('Shortcode','lead-form-builder').'</th>
        		<th scope="col" id="today_count" class="manage-column column-form-count sortable desc">'.esc_html__("Today's Lead",'lead-form-builder').' </th>
        		<th scope="col" id="total_count" class="manage-column column-form-count sortable desc">'.esc_html__('Total Lead','lead-form-builder').' </th>
                <th scope="col" id="date" class="manage-column column-form-date sortable desc">'.esc_html__('Date','lead-form-builder').' </th>
        		</tr>
        	</thead>
        	<tbody id="the-list" data-wp-lists="list:post">';

        global $wpdb;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
		$start = 0;
        $limit = 10;
        $id = $id; 
        $start = ($id - 1) * $limit;
        $form_count = $start;
        $prepare_12 = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ORDER BY id DESC LIMIT $start , $limit", 'ACTIVE' );
		$posts = $th_save_db->lfb_get_form_content($prepare_12);
        if ($posts){
            foreach ($posts as $results) {
            	$form_count++;
                $form_title = $results->form_title;
                $product_id = $results->product;
                $product    = sejolisa_get_product($product_id);
                $form_date = $results->date;
                $form_id = $results->id;
                $captcha_status = $results->captcha_status;
                $data_table = LFB_FORM_DATA_TBL;
                $today_date= date('Y/m/d');
                $newDate = date("Y/m/d H:i:s", strtotime($today_date));
                $th_save_db = new LFB_SAVE_DB($wpdb);
                $prepare_13 = $wpdb->prepare("SELECT id FROM $data_table WHERE date > %s and form_id = %d ", $newDate, $form_id );
                $count_result = $th_save_db->lfb_get_form_content($prepare_13);
                $lead_count = count($count_result);

                $prepare_14 = $wpdb->prepare("SELECT id FROM $data_table WHERE form_id = %d ", $form_id );
                $total_lead_result = $th_save_db->lfb_get_form_content($prepare_14);
                $total_lead_result = count($total_lead_result);
                $edit_url_nonce =$lfb_admin_url . 'admin.php?page=add-new-form&action=edit&formid=' . $form_id.'&_wpnonce='.$this->lfb_show_form_nonce();

                $advance_adons =$lfb_admin_url . 'admin.php?page=lfb-form-extension&fname=' . $form_title.'&fid=' . $form_id.'&_wpnonce='.$this->lfb_show_form_nonce();
                $form_color = $lfb_admin_url . 'admin.php?page=wplf-plugin-menu&action=show&formid=' . $form_id;

                echo '<tr><td class="title column-title has-row-actions column-primary" data-colname="Title"><strong><a class="row-title" href="'.esc_url($edit_url_nonce).'" title="Edit “' . esc_html($form_title) . '”">' . esc_html($form_title) . '</a></strong>
            		<div class="row-actions"><span class="edit"><a href="' . esc_url($edit_url_nonce). '">Edit</a></span>|<span class="edit"><a href="' . esc_url($lfb_admin_url) . 'admin.php?page=wplf-plugin-menu&action=delete&page_id='.$id.'&formid=' . $form_id . '">Delete</a></span>|<span class="edit"><a href="'.esc_url($form_color).'" target="_blank" >View Form</a></span>
            		</div>
            		<button type="button" class="toggle-row"><span class="screen-reader-text">'.esc_html__('Show more details','lead-form-builder').' </span></button>
            		<button type="button" class="toggle-row"><span class="screen-reader-text">'.esc_html__('Show more details','lead-form-builder').' </span></button>
            		</td>
                    <td class="product column-product" data-colname="Shortcode"><span class="product">
                    <strong>'.$product->post_title.'</strong>
                    </td>
            		<td class="shortcode column-shortcode" data-colname="Shortcode"><span class="shortcode">
            		<input type="text" onfocus="this.select();" readonly="readonly" value="[lead-form form-id=' . intval($form_id) . ' title=' . esc_html($form_title) . ']" class="large-text code"></span>
            		</td>

            		<td class="form-date column-form-date" data-colname="Form-date">
            		<abbr><a href="' . esc_url($lfb_admin_url)  . 'admin.php?page=wplf-plugin-menu&action=today_leads&formid=' . $form_id . '" target="_blank"><div class="lfb-counter">' . intval($lead_count) . '</div></a></abbr>
            		</td>
            		<td class="form-date column-form-date" data-colname="Form-date">
            		<abbr><a href="' . esc_url($lfb_admin_url)  . 'admin.php?page=wplf-plugin-menu&action=total_leads&formid=' . intval($form_id) . '" target="_blank"><div class="lfb-counter">' . intval($total_lead_result) . '</div></a></abbr>
            		</td>

                    <td class="form-date column-form-date" data-colname="Form-date">
                    <span title="' . $form_date . '">' .  date("d M, Y", strtotime($form_date)). '</span>
                    </td>
            		</tr>';
            }
        }

        echo '</tbody></table><div class="tablenav bottom"><br class="clear">';
        $prepare_15 = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ", 'ACTIVE' );
        $rows = $th_save_db->lfb_get_form_content($prepare_15);
        $rows = count($rows);
        $total = ceil($rows / $limit);
        if ($id > 1) {
            echo "<a href='". esc_url($lfb_admin_url . "admin.php?page=wplf-plugin-menu&page_id=" . intval($id - 1) ). "' class='button'><i class='fa fa-chevron-right'></i></a>";
        }
        if ($id != $total) {
            echo "<a href='". esc_url($lfb_admin_url . "admin.php?page=wplf-plugin-menu&page_id=" . intval($id + 1) ). "' class='button'><i class='fa fa-chevron-left'></i></a>";
        }
        echo "<ul class='page'>";
        for ($i = 1; $i <= $total; $i++) {
            if ($i == $id) {
                echo "<li class='lf-current'><a href='#'>" . intval($i) . "</a></li>";
            } else {
                echo "<li><a href='". esc_url($lfb_admin_url . "admin.php?page=wplf-plugin-menu&page_id=" .intval($i) ). "'>" . intval($i) . "</a></li>";
            }
        }
        echo '</ul>';
        echo '</div> </div></div>';
    }
}