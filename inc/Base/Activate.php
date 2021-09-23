<?php 
namespace Inc\Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class Activate
{
    public static function activate()
    {
        flush_rewrite_rules();
    }
}