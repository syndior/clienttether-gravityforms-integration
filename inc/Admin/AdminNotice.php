<?php 
namespace Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

final class AdminNotice
{
    private static $admin_notices = array();

    public function register()
    {
        // wp admin notice hook
        add_action( 'admin_notices', array( $this, 'add_plugin_admin_notices' ) );
    }

    public function add_plugin_admin_notices()
    {
        if( count( self::$admin_notices ) > 0 ){

            foreach( self::$admin_notices as $notice ){
                $classes    = array( 'notice' );
                $message    = $notice['message'];

                if( isset( $notice['type'] ) && strlen( $notice['type'] ) > 0 ){
                    $classes[] = $notice['type'];
                }else{
                    $classes[] = 'notice-info';
                }
                
                if( isset( $notice['is_dismissible'] ) && $notice['is_dismissible'] == true ){
                    $classes[] = 'is-dismissible';
                }

                $html = '';
                $html .= '<div class="'.( implode( ' ', $classes ) ).'">';
                $html .= '<p>'.esc_html( $message ).'</p>';
                $html .= '</div>';

                echo $html;
            }

        }
    }

    public static function add_admin_notice( array $notice = array() ){
        self::$admin_notices = array_merge( array( $notice ), self::$admin_notices );
    }
}