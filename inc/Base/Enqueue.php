<?php
namespace Inc\Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class Enqueue
{
    public function register()
    {
        if( is_admin() ){
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_files' ) );
        }

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_files' ) );
    }
	
    
    public function enqueue_admin_files()
    {
        wp_enqueue_style( 'ctgfapi-admin-styles', CTGF_API_URL . 'assets/css/ctgfapi-admin.css', array(), '1.0.0' );
        wp_enqueue_script( 'ctgfapi-admin-script', CTGF_API_URL . 'assets/js/ctgfapi-admin.js', array(), '1.0.0', true );

    }

    public function enqueue_frontend_files()
    {
        wp_enqueue_style( 'ctgfapi-frontend-styles', CTGF_API_URL . 'assets/css/ctgfapi-frontend.css', array(), '1.0.0' );
        wp_enqueue_script( 'ctgfapi-frontend-script', CTGF_API_URL . 'assets/js/ctgfapi-frontend.js', array(), '1.0.0', true );
    }
}