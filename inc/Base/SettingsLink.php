<?php 
namespace Inc\Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class SettingsLink
{
    public function register()
    {
        add_filter( 'plugin_action_links_' . CTGF_API_PLUGIN , array( $this, 'settings_link' ) );
    }
    
    public function settings_link( $links )
    {
        $setting_link = '<a href="admin.php?page='. CTGF_API_ADMIN_PAGE .'">Settings</a>';
        return  array_merge( array( $setting_link ), $links ); 
    }
}