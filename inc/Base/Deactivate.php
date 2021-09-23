<?php 
namespace Inc\Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class Deactivate
{
    public static function deactivate()
    {
        flush_rewrite_rules();
    }
}