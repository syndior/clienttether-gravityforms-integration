<?php 
namespace Inc\Api;
use \Inc\Admin\AdminNotice;

if ( ! defined( 'ABSPATH' ) ) exit;

class ApiHandler
{
    public function register()
    {
        if( in_array( 'gravityforms/gravityforms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){

        }else{

            $notice = array(
                'message'           => 'Please make sure Gravity forms plugin is installed and activated',
                'type'              => 'notice-error',
                'is_dismissible'    => false,
            );

            AdminNotice::add_admin_notice( $notice );

        }
    }
}