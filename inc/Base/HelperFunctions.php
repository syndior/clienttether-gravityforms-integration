<?php
namespace Inc\Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class HelperFunctions
{
    public static function error_log( string $label = '', $data = array() )
    {
        if( !(strlen($label) > 0) ){
            $label = date( 'Y-m-d H:i:s', time() );
        }

        ob_start();
        echo $label . ":\n";
        var_dump( $data );
        $log = ob_get_clean();

        error_log( $log );
    }
    
    public static function debug_dump( $data = array() )
    {
        add_action( 'wp_footer', function(){
            echo '<pre>';
            var_dump( $data );
            echo '</pre>';
        } );

    }
}