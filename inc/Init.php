<?php 
namespace Inc;

if ( ! defined( 'ABSPATH' ) ) exit;

final class Init
{

    public static function get_services()
    {
        return array(
            Base\SettingsLink::class,
            Base\Enqueue::class,
            Admin\AdminNotice::class,
            Api\ApiHandler::class,
            Core\CustomPostTypes::class,
            Core\CustomFields::class,
        );
    }
 
    public static function register_services()
    {
        foreach( self::get_services() as $class ){
            
            $service = self::instantiate( $class );
            if( method_exists( $service, 'register' ) ){
                $service->register();
            }

        }
    }


    private static function instantiate( $class )
    {
        return new $class();
    }

}