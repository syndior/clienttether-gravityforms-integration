<?php
/**
 * Plugin Name:       ClientTether Gravity Forms Integration
 * Plugin URI:        https://kristall.io/
 * Description:       Sync Gravity From's submission data with ClientTether's API
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Author:            Kristall Studios
 * Author URI:        https://kristall.io/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Direct access protection
defined('ABSPATH') or die();

// plugin global variables
define( 'CTGF_API_URL', plugin_dir_url( __FILE__ ) );
define( 'CTGF_API_PATH', plugin_dir_path( __FILE__ ) );
define( 'CTGF_API_PLUGIN', plugin_basename( __FILE__ ) );
define( 'CTGF_API_ADMIN_PAGE', 'ctgfapi-settings-page' );

// composer autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// plugin activation hook
function ctgfapi_plugin_activation(){
    Inc\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'ctgfapi_plugin_activation' );

// plugin deactivation
function ctgfapi_plugin_deactivation(){
    Inc\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'ctgfapi_plugin_deactivation' );

// load plugin services
if( class_exists('Inc\\Init') ){
    Inc\Init::register_services();
}