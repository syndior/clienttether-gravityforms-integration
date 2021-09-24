<?php 
namespace Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminPages
{
    public function register()
    {
        /* Add Admin Page */
        add_action( 'admin_menu', array( $this, 'add_admin_page' ) );

        /* Setting sections for admin page */
        add_action( 'admin_init', array( $this, 'add_admin_settings_section' ) );
            
        /* settings feilds setup */
        add_action( 'admin_init', array( $this, 'add_admin_settings_field' ) );
    }


    public function add_admin_page()
    {
        if( function_exists( 'add_menu_page' ) ){
            
            $page_title         = 'ClientTether Gravity Forms Integration';
            $menu_title         = 'ClientTether';
            $capability         = 'manage_options';
            $menu_slug          = CTGF_API_ADMIN_PAGE;
            $function           = array( $this, 'load_admin_page_template' );
            $icon_url           = CTGF_API_URL . 'assets/img/admin-icon.png';
            $position           = 99;

            add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        }
        
        if( function_exists( 'add_submenu_page' ) ){
            
            $parent_slug        = CTGF_API_ADMIN_PAGE;
            $page_title         = 'Lead Submission';
            $menu_title         = 'Lead Submission';
            $capability         = 'manage_options';
            $menu_slug          = 'edit.php?post_type=ctgfapi_entry_rules';
            $function           = '';
            $position           = 99;

            add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function, $position );
        }
    }


    public function load_admin_page_template()
    {
        require_once ( CTGF_API_PATH . 'templates/admin/admin-management-page.php' );
    }

    
    public function add_admin_settings_section()
    {
        $id             = 'ctgfapi_setting_section';
        $title          = 'Woo Google Sheets Order Sync Options';
        $callback       = array( $this, 'ctgfapi_setting_section_callback' );
        $page           = CTGF_API_ADMIN_PAGE;
        add_settings_section( $id, $title, $callback, $page );   
    }


    public function ctgfapi_setting_section_callback()
    {
        # code...
    }


    public function add_admin_settings_field()
    {
        $settings_fields = AdminFields::get_settings_fields();

        foreach($settings_fields as $field){

            $id                 = $field['id'];
            $title              = $field['title'];
            $callback           = array( $this, 'setting_fields_render_callback' );
            $page               = CTGF_API_ADMIN_PAGE;
            $section            = 'ctgfapi_setting_section';
            $args               = $field;
            add_settings_field( $id, $title, $callback, $page, $section, $args );
            register_setting( CTGF_API_ADMIN_PAGE, $field['id'] );
        }
    }


    public function setting_fields_render_callback( $arguments )
    {
        AdminFields::render_field( $arguments );
    }

}