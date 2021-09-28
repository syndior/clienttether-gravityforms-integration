<?php 
namespace Inc\Core;
use \Inc\Admin\AdminNotice;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomPostTypes
{
    public function register()
    {
        // register custom post type
        add_action( 'init', array( $this, 'register_custom_post_types' ) );
    }

    public function register_custom_post_types()
    {
        if( function_exists('register_post_type') ){

            $custom_post_types = $this->get_custom_post_types();

            if( !empty($custom_post_types) ){

                foreach( $custom_post_types as $cpt ){

                    register_post_type( $cpt['post_type_key'], $cpt['args'] );

                }

            }

        }
    }


    public function get_custom_post_types()
    {
        $custom_post_types = array(
            array(
                'post_type_key' => 'ctgfapi_entry_rules',
                'args' => array(
                    'labels' => array(
                        'name'                  => __( 'Lead Submissions' ),
                        'singular_name'         => __( 'Lead Submission' ),
                        'add_new'               => __( 'Add New' ),
                        'add_new_item'          => __( 'Add New Lead Submission' ),
                        'edit'                  => __( 'Edit' ),
                        'edit_item'             => __( 'Edit Lead Submission' ),
                        'new_item'              => __( 'New Lead Submission' ),
                        'view'                  => __( 'View Lead Submission' ),
                        'view_item'             => __( 'View Lead Submission' ),
                        'search_items'          => __( 'Search Lead Submissions' ),
                        'not_found'             => __( 'No Lead Submissions found' ),
                        'not_found_in_trash'    => __( 'No Lead Submissions found in Trash' ),
                        'parent'                => __( 'Parent Lead Submission' ),
                    ),
                    'public'                => false,
                    'publicly_queryable'    => false,
                    'show_ui'               => true,
                    'hierarchical'          => false,
					'show_in_nav_menus'     => false,
					'rewrite'               => false,
					'query_var'             => false,
                    'supports'              => array( 'title' ),
                    'has_archive'           => false,
                    'menu_position'         => 99,
                    'menu_icon'             => CTGF_API_URL . 'assets/img/admin-icon.png',
                ),
            ),
        );

        return $custom_post_types;
    }

}