<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Set local js variables
 * Hooked via action wp_enqueue_scripts, priority 1111
 * @since   1.0.0
 */
function set_localize_js_var() {

    if(!current_user_can('manage_sejoli_jv_data')) :
		return;
	endif;

    wp_localize_script( 'sejoli-member-area', 'sejoli_jv', array(
        'order' => array(
            'link' => add_query_arg(array(
                'action'    => 'sejoli-jv-order-table',
            ), admin_url('admin-ajax.php')),

			'nonce'	=> wp_create_nonce('sejoli-jv-set-for-table')
        ),
		'earning' => array(
            'link' => add_query_arg(array(
                'action'    => 'sejoli-jv-user-earning-table',
            ), admin_url('admin-ajax.php')),

			'nonce'	=> wp_create_nonce('sejoli-render-jv-single-table')
        ),
		'export_prepare' => array(
			'link'	=> add_query_arg(array(
					'action' => 'sejoli-jv-order-export-prepare'
			), admin_url('admin-ajax.php')),

			'nonce' => wp_create_nonce('sejoli-jv-order-export-prepare')
		),
		'export_earning_prepare' => array(
			'link'	=> add_query_arg(array(
					'action' => 'sejoli-jv-earning-export-prepare'
			), admin_url('admin-ajax.php')),

			'nonce' => wp_create_nonce('sejoli-jv-earning-export-prepare')
		),
    ));

}
add_action( 'wp_enqueue_scripts', 'set_localize_js_var', 1111);

/**
 * Register member area menu
 * Hooked via filter sejoli/member-area/menu, priority 11
 * @since   1.0.0
 * @param   array  $menu
 * @return  array
 */
function register_menu( array $menu ) {

    $menu_position = 1;

    $member_menu = array(
        'link'    => 'javascript::void(0)',
        'label'   => __('Lead Campaign', 'sejoli'),
        'icon'    => 'users icon',
        'class'   => 'item',
        'submenu' => array(
            'lead-affiliasi' => array(
                'link'    => site_url('member-area/lead-affiliasi'),
                'label'   => __('Link Affiliasi', 'sejoli'),
                'icon'    => '',
                'class'   => 'item',
                'submenu' => array()
            ),
            'lead-entries' => array(
                'link'    => site_url('member-area/lead-entries'),
                'label'   => __('Manajemen Data ', 'sejoli'),
                'icon'    => '',
                'class'   => 'item',
                'submenu' => array()
            )
        )
    );

    $menu = array_slice($menu, 0, $menu_position, true) +
            array( 'lead-form-page' => $member_menu )+
            array_slice($menu, $menu_position, count($menu) - 1, true);

    return $menu;

}
add_filter( 'sejoli/member-area/menu', 'register_menu', 11);

/**
 * Add point menu to menu backend area
 * Hooked via filter sejoli/member-area/backend/menu, priority 1111
 * @since   1.0.0
 * @param   array   $menu
 * @return  array
 */
function add_menu_in_backend(array $menu) {

    $menu_position = 1;

    $point_menu = array(
        'title'  => __('Lead Campaign', 'sejoli'),
        'object' => 'sejoli-lead-gen',
        'url'    => site_url('member-area/lead-entries')
    );

    // Add point menu in selected position
    $menu = array_slice($menu, 0, $menu_position, true) +
            array('lead-form-page' => $point_menu) +
            array_slice($menu, $menu_position, count($menu) - 1, true);

    return $menu;

}
add_filter( 'sejoli/member-area/backend/menu', 'add_menu_in_backend', 1111);

/**
 * Display link list for point member link
 * Hooked via filter sejoli/member-area/menu-link, priority 1
 * @since   1.0.0
 * @param   string  $output
 * @param   object  $object
 * @param   array   $args
 * @param   array   $setup
 * @return  string
 */
function display_link_list_in_menu($output, $object, $args, $setup) {

    $member_menu = array(
        'link'    => 'javascript::void(0)',
        'label'   => __('Lead Campaign', 'sejoli'),
        'icon'    => 'users icon',
        'class'   => 'item',
        'submenu' => array(
            'lead-affiliasi' => array(
                'link'    => site_url('member-area/lead-affiliasi'),
                'label'   => __('Link Affiliasi', 'sejoli'),
                'icon'    => '',
                'class'   => 'item',
                'submenu' => array()
            ),
            'lead-entries' => array(
                'link'    => site_url('member-area/lead-entries'),
                'label'   => __('Manajemen Data ', 'sejoli'),
                'icon'    => '',
                'class'   => 'item',
                'submenu' => array()
            )
        )
    );

    if('sejoli-lead-gen' === $object->object) :

        extract($args);

        ob_start();
        ?>
        <div class="master-menu">
            <a href="javascript:void(0)" class='item'>
                <i class='users icon'></i>
                <?php echo $object->post_title; ?>
            </a>
            <ul class="menu">
            <?php foreach( $member_menu['submenu'] as $submenu ) : ?>
                <li>
                    <a href="<?php echo $submenu['link']; ?>" class="<?php echo $submenu['class']; ?>">
                        <?php if( !empty( $submenu['icon'] ) ) : ?>
                            <i class="<?php echo $submenu['icon']; ?>"></i>
                        <?php endif; ?>
                        <?php echo $submenu['label']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php

        $item_output = ob_get_contents();
        ob_end_clean();

        return $item_output;

    endif;

    return $output;

}
add_filter( 'sejoli/member-area/menu-link', 'display_link_list_in_menu', 11, 4);

/**
 * Set template file for point menu template
 * Hooked via sejoli/template-file, priority 111
 * @since   1.0.0
 * @param   string  $file
 * @param   string  $view_request
 */
function set_template_file(string $file, string $view_request) {

    if( in_array( $view_request, array('lead-affiliasi', 'lead-entries') ) ) :

        $current_user_group  = sejolisa_get_user_group();
        $no_access_affiliate = boolval(sejolisa_carbon_get_theme_option('sejoli_no_access_affiliate'));

        // Need to be factored later
        if(
            !sejolisa_check_user_can_access_affiliate_page()
        ) :
            $template_file = 'no-affiliate';
    
            $directory = apply_filters('sejoli/template-directory',SEJOLISA_DIR . 'template/');
            $file      = $directory.$template_file.'.php';
            $file      = apply_filters('sejoli/template-file', $file, $template_file);

            if(file_exists($file)) :
                return $file;
            else:
                return str_replace($template_file, '404', $file);
            endif;

        else:

            if( 'lead-affiliasi' === $view_request ) :

                return LFB_PLUGIN_DIR . 'template/lead-affiliasi.php';

            elseif( 'lead-entries' === $view_request ) :

                return LFB_PLUGIN_DIR . 'template/lead-entries.php';

            endif;

        endif;

    endif;

    return $file;

}
add_filter( 'sejoli/template-file', 'set_template_file', 111, 2);