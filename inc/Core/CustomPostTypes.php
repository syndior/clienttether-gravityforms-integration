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
                    'show_in_menu'          => CTGF_API_ADMIN_PAGE,
                    'hierarchical'          => false,
					'show_in_nav_menus'     => false,
					'rewrite'               => false,
					'query_var'             => false,
                    'supports'              => false,
                    'has_archive'           => false,
                    'register_meta_box_cb'  => array( $this, 'register_entry_meta_box' ),
                ),
            ),
        );

        return $custom_post_types;
    }


    public function register_entry_meta_box( $post )
    {
        if( function_exists( 'add_meta_box' ) ){

            $id             = 'ctgfapi_gform_entry_rules';
            $title          = 'Setup Lead Data';
            $callback       = array( $this, 'render_entry_meta_box_content' );
            $screen         = 'ctgfapi_entry_rules';
            $context        = 'normal';
            $priority       = 'default';
            $callback_args  = array( 'post' => $post );

            add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );

        }
    }


    public function render_entry_meta_box_content( $current_post )
    {
        // $current_post = $args['post'];

        $output = '';

        if( isset($current_post) && isset($current_post->ID) ){


            $form_fields = $this->get_entry_form_fields();
            if( !empty($form_fields) ){

                foreach( $form_fields as $field_key => $field_options ){

                    $output .= $this->render_form_field( $current_post->ID, $field_key, $field_options );

                }

            }

        }

        echo $output;

    }


    public function get_entry_form_fields()
    {
        $form_fields = array(
            'ctgfapi_gform' => array( 
                'label'             => 'Select From',
                'accepts_static'    => false,  
                'default_enabled'   => true,  
                'required'          => true, 
                'mapable'           => false,
                'render_callback'   => array( $this, 'render_gform_field' ) 
            ),

            'ctgfapi_first_name' => array( 
                'label'             => 'User\'s First Name',
                'accepts_static'    => false,  
                'default_enabled'   => true,  
                'required'          => true, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_last_name' => array( 
                'label'             => 'User\'s Last Name',
                'accepts_static'    => false,  
                'default_enabled'   => true,  
                'required'          => true, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_phone' => array( 
                'label'             => 'User\'s Phone number',
                'accepts_static'    => true,
                'default_enabled'   => false,
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_email'         => array( 
                'label'             => 'User\'s Email',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_smsok' => array( 
                'label'             => 'SMS Consent',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_address' => array( 
                'label'             => 'User\'s Address',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_city' => array( 
                'label'             => 'User\'s City',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_state' => array( 
                'label'             => 'User\'s State',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_zip' => array( 
                'label'             => 'User\'s Zip Code',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_company_name' => array( 
                'label'             => 'User\'s Company Name', 
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_tags' => array( 
                'label'             => 'ClientTether\'s Tags',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => false,
                'render_callback'   => null 
            ),

            'ctgfapi_age' => array( 
                'label'             => 'User\'s Age',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_gender'        => array( 
                'label'             => 'User\'s Gender',
                'accepts_static'    => true,   
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => true,
                'render_callback'   => null 
            ),

            'ctgfapi_lead_source' => array( 
                'label'             => 'ClientTether\'s Lead Source',
                'accepts_static'    => false,  
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => false,
                'render_callback'   => array( $this, 'render_lead_source_field' ) 
            ),

            'ctgfapi_action_plan' => array( 
                'label'             => 'Add User to Action Plan',
                'accepts_static'    => false,  
                'default_enabled'   => false, 
                'required'          => false, 
                'mapable'           => false,
                'render_callback'   => array( $this, 'render_action_plan_field' ) 
            ),

        );

        return $form_fields;
    }


    public function render_form_field( int $post_id = 0, string $field_key = '', array $form_field = array() )
    {
        $html = '';

        if( isset($post_id) && isset($field_key) && strlen($field_key) > 0 && isset($form_field) && !empty($form_field) && is_array($form_field) ){

            if( isset($form_field['render_callback']) && isset($form_field['render_callback'][0]) && isset($form_field['render_callback'][1]) ){
                
                $object = $form_field['render_callback'][0];
                $mothod = $form_field['render_callback'][1];

                if( is_object( $object ) && method_exists( $object, $mothod ) ){
                    $html = $object->{$mothod}( $form_field );
                }


            }else{

                // get field saved value
                $field_value = get_post_meta( $post_id, $field_key, true );
                $field_value = isset( $field_value ) && $field_value !== '' ? $field_value : '';

                // prepare CSS classes for field
                $css_classes    = array( 'ctgfapi_admin_field' );
                $css_classes[]  = 'ctgfapi_' . $form_field['type'];
                $css_classes[]  = $field_key;

                $field_css_classes = implode( ' ', $css_classes );

                // set label element for form fields
                $html .= '<p>';
                $html .= '<label for="'. $field_key .'">'. $form_field['label'] .'</label>';
                $html .= '<input class="'. $field_css_classes .'" id="'. $field_key .'" type="text" name="'. $field_key .'" value="'. $field_value .'">';
                $html .= '</p>';

            }

        }

        return $html;
    }

}