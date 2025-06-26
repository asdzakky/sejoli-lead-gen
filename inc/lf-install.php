<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check sejoli license
 * @since   1.0.0
 */
// function sejolisa_lead_check_own_license() {

//     global $sejolisa;

//     return boolval($sejolisa['license']['valid']);

// }

function sejolisa_lead_check_valid_transient_license() {

    $get_sejoli_license_status = get_transient( 'sejoli_lead_subscription_validate_licensed' );

    if( empty($get_sejoli_license_status) || !empty($get_sejoli_license_status) ) :

        $host   = $_SERVER['HTTP_HOST'];

        if( empty($host) ) :
            $host = str_replace(array( 'https://', 'http://', 'www.' ), '', get_option('site_url'));
        endif;

        $post_data = [
            'host' => $host
        ];
        
        $link = add_query_arg(array(
                    'string'    => $host
                ), 'https://member.sejoli.co.id/sejoli-validate-license/');
        $response = wp_remote_get($link);
        $response = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($response['detail']['status']) && $response['detail']['status'] === "active" || $response['detail']['status'] === "expired" || empty($response['detail']['status'])) :

            set_transient( 'sejoli_lead_subscription_validate_licensed', "subscribed", 30 * DAY_IN_SECONDS );
            
            return;

        else:

            set_transient( 'sejoli_lead_subscription_validate_licensed', "not_subscribed", 30 * DAY_IN_SECONDS );

        endif;
    
    endif;

}
add_filter( "admin_init", 'sejolisa_lead_check_valid_transient_license' );

function sejolisa_lead_check_own_license() {

    $get_tracking_updater = get_transient( 'sejoli_lead_subscription_validate_license' );

    if( $get_tracking_updater === "subscribed" ) :
        $status = true;
    else:
        $status = false;
    endif;
    
    return $status;

}

/**
 * Set Asstes Admin
 * Hooked via action admin_enqueue_scripts
 * @since   1.0.0
 */
function lfb_admin_assets($hook) {
    $pageSearch = array('lead-campaign_page_add-new-form','lead-campaign_page_all-form-entries','themehunk_page_wplf-plugin-menu','admin_page_pro-form-leads','lead-forms_page_all-leads','toplevel_page_lead-forms');
    if(in_array($hook, $pageSearch)){
        wp_enqueue_style('wpth_fa_css', LFB_PLUGIN_URL . 'font-awesome/css/font-awesome.css');
        wp_enqueue_style('lfb-option-css', LFB_PLUGIN_URL . 'css/option-style.css');
        wp_enqueue_style('sweet-dropdown.min', LFB_PLUGIN_URL . 'css/jquery.sweet-dropdown.min.css');
        wp_enqueue_style('wpth_b_css', LFB_PLUGIN_URL . 'css/b-style.css');
        wp_enqueue_script('lfb_modernizr_js', LFB_PLUGIN_URL . 'js/modernizr.js', '', LFB_VER, true);
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script("jquery-ui-sortable");
        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-droppable"); 
        wp_enqueue_script("jquery-ui-accordion");
        wp_enqueue_style('jquery-ui');  
        wp_enqueue_script('lfb_upload', LFB_PLUGIN_URL . 'js/upload.js', '', LFB_VER, true);
        wp_enqueue_script('sweet-dropdown.min', LFB_PLUGIN_URL . 'js/jquery.sweet-dropdown.min.js', '', LFB_VER, true);

        wp_register_style( 'sejoli-lead-dataTables',   SEJOLISA_URL . 'admin/css/dataTables.css', [], LFB_VER, 'all');
        wp_register_style( 'select2',                   'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css',  [], '4.0.7', 'all');
        wp_register_style( 'dataTables',                'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css',          [], '1.13.1', 'all');
        wp_register_style( 'daterangepicker',           'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',          [], NULL, 'all');
        wp_register_style( 'roworder-dt',           'https://cdn.datatables.net/rowreorder/1.3.1/css/rowReorder.dataTables.min.css',          [], NULL, 'all');
        wp_register_style( 'responsive-dt',           'https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css',          [], NULL, 'all');
        wp_register_style( 'fixedcolumn-dt',           'https://cdn.datatables.net/fixedcolumns/4.2.1/css/fixedColumns.dataTables.min.css',          [], NULL, 'all');
        // wp_register_style( 'semantic-ui',               'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css', [], '2.4.1', 'all' );
        wp_register_style( 'dataTables-semantic-ui',    'https://cdn.datatables.net/1.10.19/css/dataTables.semanticui.min.css',      ['dataTables', 'semantic-ui'], '1.10.19', 'all' );

        wp_enqueue_style( 'sejoli-lead-dataTables');
        wp_enqueue_style( 'select2' );
        wp_enqueue_style( 'daterangepicker');
        wp_enqueue_style( 'roworder-dt');
        wp_enqueue_style( 'responsive-dt');
        wp_enqueue_style( 'fixedcolumn-dt');
        // wp_enqueue_style( 'semantic-ui');
        
        $page_form_list = array('lead-campaign_page_all-form-entries','toplevel_page_lead-forms');
        if(in_array($hook, $page_form_list)) {
            wp_enqueue_style( 'dataTables-semantic-ui' );
        }

        wp_register_script( 'select2',          'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js',                   ['jquery'], '4.0.7', true);
        wp_register_script( 'dataTables',       'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js',                           ['jquery'], LFB_VER, '1.13.1', true);
        wp_register_script( 'roworder-dt-js',           'https://cdn.datatables.net/rowreorder/1.3.1/js/dataTables.rowReorder.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'responsive-dt-js',           'https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'fixedcolumn-dt-js',           'https://cdn.datatables.net/fixedcolumns/4.2.1/js/dataTables.fixedColumns.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'moment',           'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',                                   ['jquery'], NULL, true);
        
        wp_register_script( 'dtButton',           'https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'jsZip',           'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'pdfMake',           'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'vfsFonts',           'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'buttonHtml5',           'https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'buttonPrint',           'https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js',                                   ['jquery'], NULL, true);

        wp_register_script( 'daterangepicker',  'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',                      ['moment'], NULL, true);
        // wp_register_script( 'semantic-ui',      'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js',                 ['jquery'], '2.4.1', true );

        wp_enqueue_script( 'daterangepicker');
        wp_enqueue_script( 'select2' );
        wp_enqueue_script( 'dataTables' );
        wp_enqueue_script( 'roworder-dt-js' );
        wp_enqueue_script( 'responsive-dt-js' );
        wp_enqueue_script( 'fixedcolumn-dt-js' );
        wp_enqueue_script( 'dtButton' );
        wp_enqueue_script( 'jsZip' );
        wp_enqueue_script( 'pdfMake' );
        wp_enqueue_script( 'vfsFonts' );
        wp_enqueue_script( 'buttonHtml5' );
        wp_enqueue_script( 'buttonPrint' );

        if(wp_is_mobile()){
            $fixedcolumn = 1;
        } else {
            $fixedcolumn = 3;
        }

        wp_localize_script( "dataTables", "dataTableTranslation", array(
            "all"            => __('Semua','sejoli-lead-form'),
            "decimal"        => ",",
            "emptyTable"     => __("Tidak ada data yang bisa ditampilkan","sejoli-lead-form"),
            "info"           => __("Menampikan _START_ ke _END_ dari _TOTAL_ data","sejoli-lead-form"),
            "infoEmpty"      => __("Menampikan 0 ke 0 dari 0 data","sejoli-lead-form"),
            "infoFiltered"   => __("Menyaring dari total _MAX_ data","sejoli-lead-form"),
            "infoPostFix"    => "",
            "thousands"      => ".",
            "lengthMenu"     => __("Menampilkan _MENU_ data","sejoli-lead-form"),
            "loadingRecords" => __("Mengambil data...","sejoli-lead-form"),
            "processing"     => __("Memproses data...","sejoli-lead-form"),
            "search"         => __("Cari data :","sejoli-lead-form"),
            "zeroRecords"    => __("Tidak ditemukan data yang sesuai","sejoli-lead-form"),
            "paginate"       =>
                array(
                "first"    => __("Pertama","sejoli-lead-form"),
                "last"     => __("Terakhir","sejoli-lead-form"),
                "next"     => __("Selanjutnya","sejoli-lead-form"),
                "previous" => __("Sebelumnya","sejoli-lead-form")
            ),
            "aria"           => array(
                "sortAscending"  => __("Klik untuk mengurutkan kolom naik","sejoli-lead-form"),
                "sortDescending" => __("Klik untuk mengurutkan kolom turun","sejoli-lead-form")
            ),
            "fixedcolumn"  => $fixedcolumn
        ));
        // wp_enqueue_script( 'semantic-ui');

        wp_enqueue_script('lfb_b_js', LFB_PLUGIN_URL . 'js/b-script.js', array('jquery'), LFB_VER, true);
        wp_localize_script('lfb_b_js', 'backendajax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

}
add_action('admin_enqueue_scripts', 'lfb_admin_assets');

/**
 * Set Assets Public
 * Hooked via action wp_enqueue_scripts
 * @since   1.0.0
 */
function lfb_wp_assets() {

    global $wp;

    wp_enqueue_script('jquery-ui-datepicker');        

    wp_enqueue_script('lfb_f_js', LFB_PLUGIN_URL . 'js/f-script.js', array('jquery'), LFB_VER, true);
    wp_localize_script('lfb_f_js', 'frontendajax', 
        array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    wp_enqueue_style('font-awesome', LFB_PLUGIN_URL . 'font-awesome/css/font-awesome.css');
    
    $pagename = isset($wp->query_vars['pagename']) ? $wp->query_vars['pagename'] : '';
    if( $pagename !== "" || empty($wp->query_vars) ) :

        wp_enqueue_style('lfb_f_css', LFB_PLUGIN_URL . 'css/f-style.css');

    endif;

    if( $wp->request === 'member-area/lead-entries' || $wp->request === 'member-area/lead-affiliasi' ) :

        wp_enqueue_style('lfb_f_css', LFB_PLUGIN_URL . 'css/f-style.css');

        wp_enqueue_style('wpth_fa_css', LFB_PLUGIN_URL . 'font-awesome/css/font-awesome.css');
        wp_enqueue_style('lfb-option-css', LFB_PLUGIN_URL . 'css/option-style.css');
        wp_enqueue_style('sweet-dropdown.min', LFB_PLUGIN_URL . 'css/jquery.sweet-dropdown.min.css');
        wp_enqueue_style('wpth_b_css', LFB_PLUGIN_URL . 'css/b-style.css');
        wp_enqueue_script('lfb_modernizr_js', LFB_PLUGIN_URL . 'js/modernizr.js', '', LFB_VER, true);
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script("jquery-ui-sortable");
        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-droppable"); 
        wp_enqueue_script("jquery-ui-accordion");
        wp_enqueue_style('jquery-ui');  

        wp_register_style( 'sejoli-lead-dataTables',   SEJOLISA_URL . 'admin/css/dataTables.css', [], LFB_VER, 'all');
        wp_register_style( 'select2',                   'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css',  [], '4.0.7', 'all');
        wp_register_style( 'dataTables',                'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css',          [], '1.13.1', 'all');
        wp_register_style( 'daterangepicker',           'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',          [], NULL, 'all');
        wp_register_style( 'roworder-dt',           'https://cdn.datatables.net/rowreorder/1.3.1/css/rowReorder.dataTables.min.css',          [], NULL, 'all');
        wp_register_style( 'responsive-dt',           'https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css',          [], NULL, 'all');
        wp_register_style( 'fixedcolumn-dt',           'https://cdn.datatables.net/fixedcolumns/4.2.1/css/fixedColumns.dataTables.min.css',          [], NULL, 'all');
        // wp_register_style( 'semantic-ui',               'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css', [], '2.4.1', 'all' );
        wp_register_style( 'dataTables-semantic-ui',    'https://cdn.datatables.net/1.10.19/css/dataTables.semanticui.min.css',      ['dataTables', 'semantic-ui'], '1.10.19', 'all' );

        wp_enqueue_style( 'sejoli-lead-dataTables');
        wp_enqueue_style( 'select2' );
        wp_enqueue_style( 'daterangepicker');
        wp_enqueue_style( 'roworder-dt');
        wp_enqueue_style( 'responsive-dt');
        wp_enqueue_style( 'fixedcolumn-dt');
        // wp_enqueue_style( 'semantic-ui');
        
        // if(wp_is_mobile()){
            wp_enqueue_style( 'dataTables-semantic-ui' );
        // }

        wp_register_script( 'select2',          'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js',                   ['jquery'], '4.0.7', true);
        wp_register_script( 'dataTables',       'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js',                           ['jquery'], LFB_VER, '1.13.1', true);
        wp_register_script( 'roworder-dt-js',           'https://cdn.datatables.net/rowreorder/1.3.1/js/dataTables.rowReorder.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'responsive-dt-js',           'https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'fixedcolumn-dt-js',           'https://cdn.datatables.net/fixedcolumns/4.2.1/js/dataTables.fixedColumns.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'moment',           'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',                                   ['jquery'], NULL, true);
        wp_register_script( 'daterangepicker',  'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',                      ['moment'], NULL, true);
        // wp_register_script( 'semantic-ui',      'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js',                 ['jquery'], '2.4.1', true );

        // wp_enqueue_script( 'daterangepicker');
        wp_enqueue_script( 'daterangepicker');
        wp_enqueue_script( 'select2' );
        wp_enqueue_script( 'dataTables' );
        wp_enqueue_script( 'roworder-dt-js' );
        wp_enqueue_script( 'responsive-dt-js' );
        wp_enqueue_script( 'fixedcolumn-dt-js' );

        if(wp_is_mobile()){
            $fixedcolumn = 1;
        } else {
            $fixedcolumn = 3;
        }

        wp_localize_script( "dataTables", "dataTableTranslation", array(
            "all"            => __('Semua','sejoli'),
            "decimal"        => ",",
            "emptyTable"     => __("Tidak ada data yang bisa ditampilkan","sejoli-lead-form"),
            "info"           => __("Menampikan _START_ ke _END_ dari _TOTAL_ data","sejoli-lead-form"),
            "infoEmpty"      => __("Menampikan 0 ke 0 dari 0 data","sejoli-lead-form"),
            "infoFiltered"   => __("Menyaring dari total _MAX_ data","sejoli-lead-form"),
            "infoPostFix"    => "",
            "thousands"      => ".",
            "lengthMenu"     => __("Menampilkan _MENU_ data","sejoli-lead-form"),
            "loadingRecords" => __("Mengambil data...","sejoli-lead-form"),
            "processing"     => __("Memproses data...","sejoli-lead-form"),
            "search"         => __("Cari data :","sejoli-lead-form"),
            "zeroRecords"    => __("Tidak ditemukan data yang sesuai","sejoli-lead-form"),
            "paginate"       =>
                array(
                "first"    => __("Pertama","sejoli-lead-form"),
                "last"     => __("Terakhir","sejoli-lead-form"),
                "next"     => __("Selanjutnya","sejoli-lead-form"),
                "previous" => __("Sebelumnya","sejoli-lead-form")
            ),
            "aria"           => array(
                "sortAscending"  => __("Klik untuk mengurutkan kolom naik","sejoli-lead-form"),
                "sortDescending" => __("Klik untuk mengurutkan kolom turun","sejoli-lead-form")
            ),
            "fixedcolumn"  => $fixedcolumn
        ));
        // wp_enqueue_script( 'semantic-ui');

        wp_enqueue_script('lfb_f_m_js', LFB_PLUGIN_URL . 'js/f-m-script.js', array('jquery'), LFB_VER, true);

        wp_localize_script('lfb_f_m_js', 'frontendajax', 
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'affiliate' => array(
                'link' => array(
                    'ajaxurl' => add_query_arg([
                        'action' => 'sejoli-form-lead-affiliate-link-list'
                    ], admin_url('admin-ajax.php')),
                    'nonce' => wp_create_nonce('sejoli-list-form-lead-affiliate-link')
                ),
                'placeholder' => __('Pencarian Form Lead', 'sejoli-lead-form')
            )
        ));

        // wp_enqueue_script('lfb_upload', LFB_PLUGIN_URL . 'js/upload.js', '', LFB_VER, true);
        wp_enqueue_script('sweet-dropdown.min', LFB_PLUGIN_URL . 'js/jquery.sweet-dropdown.min.js', '', LFB_VER, true);
        // wp_enqueue_script('lfb_b_js', LFB_PLUGIN_URL . 'js/b-script.js', array('jquery'), LFB_VER, true);
        wp_localize_script('lfb_f_m_js', 'backendajax', array('ajaxurl' => admin_url('admin-ajax.php')));
    
    endif;

}
add_action('wp_enqueue_scripts', 'lfb_wp_assets', 15);

/**
 * Register admin menu pages.
 * Hooked via action admin_menu
 * @since   1.0.0
 */
function lfb_register_my_custom_menu_page() {

    $user = get_userdata( get_current_user_id() );
    // Get all the user roles as an array.
    $user_roles = $user->roles;
    add_submenu_page('lead-forms', __('Add New Forms', 'sejoli-lead-form'), __('Add New Forms', 'sejoli-lead-form'), 'manage_options', 'add-new-form', 'lfb_add_contact_forms');
    add_submenu_page( 'lead-forms', __('Entries', 'wppb'), __('Entries', 'wppb'), 'manage_options', 'all-form-entries','lfb_all_forms_lead');

    add_submenu_page(false, __('View Entries', 'sejoli-lead-form'), __('View Entries', 'sejoli-lead-form'), 'manage_options', 'all-form-leads', 'lfb_all_forms_lead');   

}
add_action('admin_menu', 'lfb_register_my_custom_menu_page');

/**
 * Set lead form page actions
 * @since   1.0.0
 */
function lfb_lead_form_page() {

    if (isset($_GET['action']) && isset($_GET['formid'])) {
        $form_action = sanitize_text_field($_GET['action']);
        $this_form_id = intval($_GET['formid']);
        if ($form_action == 'delete') {
            $page_id =1;
            if (isset($_GET['page_id'])) {
            $page_id = intval($_GET['page_id']);
            }
            $th_edit_del_form = new LFB_EDIT_DEL_FORM();
            $th_edit_del_form->lfb_delete_form_content($form_action, $this_form_id,$page_id);
        }
        if ($form_action == 'show' && isset($_GET['formid'])) {
            $fid = intval($_GET['formid']); 
            echo "<div class='lfb-show'><h1>". esc_html('Lead Form Preview Page')."</h1>";
            echo do_shortcode('[lead-form form-id="'.$fid.'" title=Contact Us]');
            echo "<div>";
        }
        if ($form_action == 'today_leads') {
            $th_show_today_leads = new LFB_Show_Leads();
            $th_show_today_leads->lfb_show_form_leads_datewise($this_form_id,"today_leads");
        }
        if ($form_action == 'total_leads') {
            $th_show_all_leads = new LFB_Show_Leads();
            $th_show_all_leads->lfb_show_form_leads_datewise($this_form_id,"total_leads");
        }
    } else {
        $th_show_forms = new LFB_SHOW_FORMS();
        $page_id =1;
        if (isset($_GET['page_id'])) {
        $page_id = intval($_GET['page_id']);
        }
        $th_show_forms->lfb_show_all_forms($page_id);
    }

}

/**
 * Extra slash remove
 * @since   1.0.0
 */
function lfb_array_stripslash($theArray) {

   foreach ( $theArray as &$v ) if ( is_array($v) ) $v = lfb_array_stripslash($v); else $v = stripslashes($v);

   return $theArray;

}

/**
 * Form builder update nad delete function
 * @since   1.0.0
 */
function lfb_add_contact_forms() {
    if (isset($_POST['update_form']) && wp_verify_nonce($_REQUEST['_wpnonce'],'_nonce_verify') ) {
        $data_form =isset($_POST['lfb_form'])?$_POST['lfb_form']:'';
        $update_form_id = intval($_POST['update_form_id']);
        $title = sanitize_text_field($_POST['post_title']);
        unset($_POST['_wpnonce']);
        unset($_POST['post_title']);
        unset($_POST['update_form']);
        unset($_POST['update_form_id']);
        global $wpdb;
        $table_name = LFB_FORM_FIELD_TBL;
        $update_leads = $wpdb->update( 
        $table_name,
        array( 
            'form_title' => $title,
            'form_data' => maybe_serialize($data_form)
        ), 
        array( 'id' => $update_form_id ));
        $rd_url = esc_url(admin_url().'admin.php?page=add-new-form&action=edit&redirect=update&formid='.$update_form_id);
        $complete_url = wp_nonce_url($rd_url);
    }

    if (isset($_GET['action']) && isset($_GET['formid'])) {
        $form_action = sanitize_text_field($_GET['action']);
        $this_form_id = intval($_GET['formid']);
        if ($form_action == 'edit') {
            $th_edit_del_form = new LFB_EDIT_DEL_FORM();
            $th_edit_del_form->lfb_edit_form_content($form_action, $this_form_id);
        }
    } else {
        $lf_add_new_form = new LFB_AddNewForm();
        $lf_add_new_form->lfb_add_new_form();
    }
}

/**
 * Show all forms lead
 * @since   1.0.0
 */
function lfb_all_forms_lead() {
    $th_show_forms = new LFB_Show_Leads();
    $th_show_forms->lfb_show_form_leads();
}