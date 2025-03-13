<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get lead form data by ID
 * @since   1.0.0
 * @param   string $link [description]
 * @param   array  $args [description]
 * @param   string $key  [description]
 * @return  array
 */
function sejoli_show_affiliate_form_lead_by_id( $form_id ) {

    global $wpdb;

    $option_form = '';
    $first_form  = 0;
    $th_save_db  = new LFB_SAVE_DB( $wpdb );
    $table_name  = LFB_FORM_FIELD_TBL;
    $prepare_16  = $wpdb->prepare("SELECT id, form_title, form_url, form_status FROM $table_name WHERE id = %s ORDER BY id DESC ", $form_id);
    $posts       = $th_save_db->lfb_get_form_content( $prepare_16 );

    if (!empty($posts)) {
        foreach ($posts as $results) {
            $first_form++;
        }
    	return $results;
    }

}

/**
 * Get lead form data in dropdown
 * @since   1.0.0
 * @param   string $link [description]
 * @param   array  $args [description]
 * @param   string $key  [description]
 * @return  json
 */
function sejoli_lead_get_lead_form_ajax_callback(){
    
    global $wpdb;

    $options     = array();
    $option_form = '';
    $first_form  = 0;
    $th_save_db  = new LFB_SAVE_DB( $wpdb );
    $table_name  = LFB_FORM_FIELD_TBL;
    $prepare_16  = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ORDER BY id DESC ",'ACTIVE');
    $posts       = $th_save_db->lfb_get_form_content( $prepare_16 );

    if ( !empty( $posts ) ) {
        
        foreach ( $posts as $results ) {
            
            $first_form++;
        	$title = ( mb_strlen( $results->form_title ) > 50 ) ? mb_substr( $results->form_title, 0, 49 ) . '...' : $results->form_title;
            $options[] = [
                'id'   => $results->id,
                'text' => sprintf( _x(' %s', 'product-options', 'sejoli-lead-form'), $title)
            ];

        }

    }

    wp_send_json([
        'results' => $options
    ]);

    exit;

}
add_action( 'wp_ajax_sejoli_lead_form', 'sejoli_lead_get_lead_form_ajax_callback' );

/**
 * Generate lead form affililiate link
 * @since   1.0.0
 * @param   string $link [description]
 * @param   array  $args [description]
 * @param   string $key  [description]
 * @return  json
 */
function sejoli_lead_get_affiliate_link_lead_form_ajax_callback(){

	$data = [];

    if(
        wp_verify_nonce($_POST['nonce'], 'sejoli-list-form-lead-affiliate-link') ||
        class_exists('WP_CLI')
    ) :
        $form_lead = sejoli_show_affiliate_form_lead_by_id($_POST['lead_form_id']);

        $args = [
            'user_id'      => get_current_user_id(),
            'form_lead_id' => $form_lead->id
        ];

        $i = 0;

        $key  = sanitize_title( $form_lead->form_title );
        $data = [
            0   => [
                'label'          => __('Halaman Form Lead', 'sejoli-lead-form'),
                'redirect_link'  => $form_lead->form_url,
                'affiliate_link' => esc_url(apply_filters('sejoli/lead-form-affiliate/link', '', $args, $key)),
                'description'    => __('Link menuju ke halaman form lead.', 'sejoli-lead-form')
            ]
        ];

    endif;

    wp_send_json( $data );

    exit;
    
}
add_action( 'wp_ajax_sejoli-form-lead-affiliate-link-list', 'sejoli_lead_get_affiliate_link_lead_form_ajax_callback' );

/**
 * Get cookie name
 * @since   1.0.0
 * @return  string
 */
function sejoli_lead_form_get_cookie_name() {

    $tokens           = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $auth_key         = AUTH_KEY;
    $secure_auth_key  = SECURE_AUTH_KEY;
    $logged_in_key    = LOGGED_IN_KEY;
    $nonce_key        = NONCE_KEY;
    $auth_salt        = AUTH_SALT;
    $secure_auth_salt = SECURE_AUTH_SALT;
    $logged_in_salt   = LOGGED_IN_SALT;
    $nonce_salt       = NONCE_SALT;

    $i = [
        0 => absint(ord(strtolower($auth_key[0])) - 48),
        1 => absint(ord(strtolower($secure_auth_key[0])) - 96),
        2 => absint(ord(strtolower($logged_in_key[0])) - 96),
        3 => absint(ord(strtolower($nonce_key[0])) - 48),
        4 => absint(ord(strtolower($auth_salt[0])) - 96),
        5 => absint(ord(strtolower($secure_auth_salt[0])) - 96),
        6 => absint(ord(strtolower($logged_in_salt[0])) - 48),
        7 => absint(ord(strtolower($nonce_salt[0])) - 96),
    ];

    $key = '';

    foreach($i as $_i) :
        $key .= $tokens[$_i];
    endforeach;

    return 'SEJOLI-'.$key;

}

/**
 * Get cookie value
 * @since   1.0.0
 * @return  array
 */
function sejoli_lead_form_get_affiliate_cookie() {

    $data        = [];
    $cookie_name = sejoli_lead_form_get_cookie_name();

    if(isset($_COOKIE[$cookie_name])) :
        $data = maybe_unserialize(stripslashes($_COOKIE[$cookie_name]));
    endif;

    return $data;

}

/**
 *  Set end point custom menu
 *  Hooked via action init, priority 999
 *  @since 1.0.0
 *  @return void
 */
function set_endpoint() {

	add_rewrite_rule( '^lead-aff/([^/]*)/([^/]*)/([^/]*)/?', 'index.php?lead-affiliate-link=1&lead-affiliate=$matches[1]&form-lead=$matches[2]&target=$matches[3]','top');
	add_rewrite_rule( '^lead-aff/([^/]*)/([^/]*)/?',		 'index.php?lead-affiliate-link=1&lead-affiliate=$matches[1]&form-lead=$matches[2]','top');

    flush_rewrite_rules();

}
add_action( 'init', 'set_endpoint', 999);
		
/**
 * Set custom query vars
 * Hooked via filter query_vars, priority 999
 * @since   1.0.0
 * @param   array $vars
 * @return  array
 */
function set_query_vars($vars = array()) {

    $vars[] = 'lead-affiliate-link';
	$vars[] = 'lead-affiliate';
	$vars[] = 'form-lead';
	$vars[] = 'target';

    return $vars;

}
add_filter( 'query_vars', 'set_query_vars', 999);

/**
 * Set affiliate data to cookie
 * Hooked via action sejoli/lead-form-affiliate/set-cookie, priority 1
 * @since 	1.0.0
 * @param 	array 	$args
 * @return 	void
 */
function set_cookie(array $args) {

	$affiliate_id        = $args['lead-affiliate'];
	$cookie_name         = sejoli_lead_form_get_cookie_name();
	$cookie_age          = absint( sejolisa_carbon_get_theme_option('sejoli_cookie_age') );
	$cookie_age          = ( 0 < $cookie_age ) ? $cookie_age : 30;
	$lifespan_cookie_day = time() + ( DAY_IN_SECONDS * $cookie_age );
	$affiliate_data      = $current_cookie = sejoli_lead_form_get_affiliate_cookie();

	$affiliate_data['general'] = $affiliate_id;

	$affiliate_data = apply_filters('sejoli/lead-form-affiliate/cookie-data', $affiliate_data, $affiliate_id);

	setcookie( $cookie_name, serialize( $affiliate_data ), $lifespan_cookie_day, COOKIEPATH, COOKIE_DOMAIN );

}
add_action( 'sejoli/lead-form-affiliate/set-cookie', 'set_cookie', 1);

/**
 * Redirect customer to selected sales page
 * Hooked via action sejoli/lead-form-affiliate/redirect, priority 999
 * @since 	1.0.0
 * @param 	array 	$args
 * @return 	void
 */
function lead_form_redirect(array $args) {

	$links         = [];
	$i             = 0;
	$form_lead     = sejoli_show_affiliate_form_lead_by_id( $args['form-lead'] );
	$redirect_link = '';

	if( empty( $redirect_link ) ) :
		$redirect_link = esc_url( $form_lead->form_url );
	endif;

	if( empty( $redirect_link ) ) :

		wp_die(
			__('Terjadi kesalahan pada link affiliasi. Kontak pemilik website ini', 'sejoli-lead-form'),
			__('Kesalahan pada pengalihan', 'sejoli-lead-form')
		);

	endif;

	wp_redirect( $redirect_link );

	exit;

}
add_action( 'sejoli/lead-form-affiliate/redirect', 'lead_form_redirect', 999);

/**
 * Check parse query and if aff found, $enable_framework will be true
 * Hooked via action parse_query, priority 999
 * @since 	1.0.0
 * @since 	1.5.2 	Add conditional check if current product is affiliate-able
 * @access 	public
 * @return 	void
 */
function check_lead_form_parse_query() {
	
    global $wp_query;

	if(is_admin()) :
		return;
	endif;
	
	$affiliate = false;
	$target    = false;
	$form_lead = false;

    if( isset( $wp_query->query_vars['lead-affiliate-link'] ) ) :

		if( isset( $wp_query->query_vars['lead-affiliate'] ) && !empty( $wp_query->query_vars['lead-affiliate'] ) ) :
			$affiliate = intval($wp_query->query_vars['lead-affiliate']);
		endif;

		if( isset( $wp_query->query_vars['form-lead'] ) && !empty( $wp_query->query_vars['form-lead'] ) ) :
			$form_lead = intval($wp_query->query_vars['form-lead']);
		endif;

		if( isset( $wp_query->query_vars['target'] ) && !empty( $wp_query->query_vars['target'] ) ) :
			$target = $wp_query->query_vars['target'];
		endif;

		$args = [
			'lead-affiliate' => $affiliate,
			'form-lead'	     => $form_lead,
			'target'	     => $target
		];

		do_action( 'sejoli/lead-form-affiliate/set-cookie', $args );
		do_action( 'sejoli/lead-form-affiliate/redirect', $args );

		exit;

    endif;

}
add_action( 'parse_query', 'check_lead_form_parse_query', 999);

/**
 * Generate lead form affililiate link
 * Hooked via filter sejoli/lead-form-affiliate/link, prioirty 1
 * @since   1.0.0
 * @param   string $link [description]
 * @param   array  $args [description]
 * @param   string $key  [description]
 * @return  string
 */
function set_form_lead_affiliate_link( $link, array $args, $key = '' ) {

	$link = home_url('/lead-aff');
	$link = $link . '/' . $args['user_id'] . '/' . $args['form_lead_id'] . '/';

	if( !empty( $key ) ) :
		$link .= $key;
	endif;

	return esc_url( $link );

}
add_filter( 'sejoli/lead-form-affiliate/link', 'set_form_lead_affiliate_link', 1, 3);