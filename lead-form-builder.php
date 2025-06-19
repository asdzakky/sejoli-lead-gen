<?php
/**
 *
 * @link              https://ridwan-arifandi.com
 * @since             1.0.0
 * @package           Sejoli
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - Lead Campaign
 * Plugin URI:        https://sejoli.co.id
 * Description:       Integrate Sejoli Premium WordPress Membership Plugin with Lead Campaign Addon.
 * Version:           1.1.1
 * Requires PHP:      7.4.1
 * Author:            Sejoli
 * Author URI:        https://sejoli.co.id
 * Text Domain:       sejoli-lead-form
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Version constant for easy CSS refreshes

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (strpos($errstr, '_load_textdomain_just_in_time') !== false) {
        return true;
    }
    return false;
});

if (!function_exists('lfb_plugin_action_links')){

    define('LFB_VER', '1.1.1');

    define('LFB_PLUGIN_URL', plugin_dir_url(__FILE__));
    define( 'LFB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

    include_once(LFB_PLUGIN_DIR . 'inc/admin/admin-menu.php');
    include_once( LFB_PLUGIN_DIR . 'inc/lfb-constant.php' );

    /**
     * Add the settings link to the Lead Form Plugin plugin row
     *
     * @param array $links - Links for the plugin
     * @return array - Links
     */
    function lfb_plugin_action_links($links){
        $settings_page = add_query_arg(array('page' => 'lead-forms'), admin_url('/admin.php?'));
        $settings_link = '<a href="'.esc_url($settings_page).'">'.__('Settings', 'sejoli-lead-form' ).'</a>';
        array_unshift($links, $settings_link);
        
        return $links;
    }

    include_once( LFB_PLUGIN_DIR . 'inc/lf-db.php' );
    register_activation_hook(__FILE__, 'lfb_plugin_activate');

    if(!function_exists('lfb_include_file')) {

        function lfb_include_file(){
            include_once( LFB_PLUGIN_DIR . 'inc/inc.php' );
        }
        add_action('init','lfb_include_file');
        
    }

    include_once( LFB_PLUGIN_DIR . 'inc/lfb-widget.php' );


}

add_action('plugins_loaded', 'sejoli_lead_check_sejoli');
add_action('admin_init', 'sejoli_lead_check_sejoli');

function sejoli_lead_check_sejoli() {

    if(!defined('SEJOLISA_VERSION')) :

        add_action('admin_notices', 'sejolp_no_sejoli_functions');

        function sejolp_no_sejoli_functions() {
            ?><div class='notice notice-error'>
            <p><?php _e('Plugin Sejoli Lead Campaign Tidak Bisa diaktifkan, Anda belum menginstall atau mengaktifkan SEJOLI terlebih dahulu.', 'sejoli-lead-form'); ?></p>
            </div><?php
        }

        deactivate_plugins(plugin_basename(__FILE__));

        return;

    endif;

}

add_action('admin_notices', 'display_lead_license_message');
function display_lead_license_message() {

    if(false === sejolisa_lead_check_valid_license() ) :

?>
        <div class="notice notice-error">
            <h3>SEJOLI LEAD CAMPAIGN</h3>
            <p><?php _e('Plugin Sejoli Lead Campaign Tidak Bisa digunakan, Lisensi Sejoli Anda Telah Berakhir/Tidak Aktif, Aktifkan Lisensi Sejoli Anda Terlebih Dahulu.', 'sejoli-lead-form'); ?></p>
        </div>
<?php

    endif;

}

register_activation_hook(__FILE__, 'sejoli_plugin_activation_check');

function sejoli_plugin_activation_check() {

    if(!defined('SEJOLISA_VERSION')) :

        deactivate_plugins(plugin_basename(__FILE__));

        // Stop the activation process
        wp_die(
            __('Plugin Sejoli Lead Campaign Tidak Bisa diaktifkan, Anda belum menginstall atau mengaktifkan SEJOLI terlebih dahulu.', 'sejoli-lead-form'),
            __('Aktivasi Gagal', 'sejoli-lead-form'),
            array(
                'link_url' => admin_url('plugins.php'),
                'link_text' => __('Kembali ke halaman Plugin', 'sejoli-lead-form')
            )
        );

    endif;

    if(false === sejolisa_lead_check_valid_license() ) :
    
        deactivate_plugins(plugin_basename(__FILE__));

        // Stop the activation process
        wp_die(
            __('Plugin Sejoli Lead Campaign Tidak Bisa diaktifkan, Anda belum menginstall atau mengaktifkan SEJOLI terlebih dahulu.', 'sejoli-lead-form'),
            __('Aktivasi Gagal', 'sejoli-lead-form'),
            array(
                'link_url' => admin_url('plugins.php'),
                'link_text' => __('Kembali ke halaman Plugin', 'sejoli-lead-form')
            )
        );

    endif;

}