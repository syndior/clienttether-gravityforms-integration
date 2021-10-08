<?php 
namespace Inc\Core;
use \Inc\Base\HelperFunctions;
use \Inc\Api\ApiHandler;
use \GFAPI;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomFields
{
    public function register()
    {

        // load CMB2 files
        $cmb2_init_file_path = CTGF_API_PATH . 'cmb2/init.php';
        if( file_exists( $cmb2_init_file_path ) ){
            require_once $cmb2_init_file_path;
        }
        
        // create admin options page
        add_action( 'cmb2_admin_init', array( $this, 'create_admin_options_page' ) );
        
        // create meta box and custom fiels for CPI
        add_action( 'cmb2_admin_init', array( $this, 'register_cpt_custom_fields' ) );


    }

    public function create_admin_options_page()
    {
        $cmb2_options_page = new_cmb2_box( array(
            'id'           => 'ctgfapi_options_page',
            'title'        => 'ClientTether Gravity Forms Integration',
            'object_types' => array( 'options-page' ),
            'option_key'   => CTGF_API_ADMIN_PAGE,
            'icon_url'     => CTGF_API_URL . 'assets/img/admin-icon.png',
            'menu_title'   => 'ClientTether',
            'position'     => 99,
            'save_button'  => 'Save',
        ) );

        $cmb2_options_page->add_field( array(
            'id'   => 'ctgfapi_access_token',
            'name' => 'ClientTether Access Token',
            'type' => 'text',
        ) );
        
        $cmb2_options_page->add_field( array(
            'id'   => 'ctgfapi_web_key',
            'name' => 'ClientTether Web Key',
            'type' => 'text',
        ) );
    }


    public function register_cpt_custom_fields()
    {
        $cpt_meta_box = new_cmb2_box( array(
            'id'            => 'ctgfapi_cpt_meta_box',
            'title'         => 'Lead Data',
            'object_types'  => array( 'ctgfapi_entry_rules' ), // Post type
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true, // Show field names on the left
        ) );


        // status field
        $cpt_meta_box->add_field( array(
            'id'        => 'ctgfapi_entry_status',
            'name'      => 'Status',
            'type'      => 'checkbox',
        ) );

        // gravity form select field
        $cpt_meta_box->add_field( array(
            'id'               => 'ctgfapi_selected_form',
            'name'             => 'Select Form',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_gravity_forms' ),
        ) );
        
        // user first name select field
        $cpt_meta_box->add_field( array(
            'id'               => 'ctgfapi_user_first_name',
            'name'             => 'User First Name',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_gform_fields' ),
            'show_on_cb'       => array( $this, 'is_gform_selected' ),
        ) );
        
        // user last name select field
        $cpt_meta_box->add_field( array(
            'id'               => 'ctgfapi_user_last_name',
            'name'             => 'User Last Name',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_gform_fields' ),
            'show_on_cb'       => array( $this, 'is_gform_selected' ),
        ) );
        
        // user email select field
        $cpt_meta_box->add_field( array(
            'id'               => 'ctgfapi_user_email',
            'name'             => 'User Email',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_gform_fields' ),
            'show_on_cb'       => array( $this, 'is_gform_selected' ),
        ) );
        
        // lead action plan select field
        $cpt_meta_box->add_field( array(
            'id'               => 'ctgfapi_lead_action_plan',
            'name'             => 'Lead Action Plan',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_lead_action_plan_list' ),
            'show_on_cb'       => array( $this, 'is_gform_selected' ),
        ) );
        
        // lead Source select field
        $cpt_meta_box->add_field( array(
            'id'               => 'ctgfapi_lead_source',
            'name'             => 'Lead Source',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_lead_source_list' ),
            'show_on_cb'       => array( $this, 'is_gform_selected' ),
        ) );
        
        // lead sales cycle select field
        $cpt_meta_box->add_field( array(
            'id'               => 'ctgfapi_lead_sales_cycle',
            'name'             => 'Lead Sales Cycle',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_lead_sales_cycle_list' ),
            'show_on_cb'       => array( $this, 'is_gform_selected' ),
        ) );

        // repeatable attributes group
        $attr_group = $cpt_meta_box->add_field( array(
            'id'          => 'ctgfapi_secondary_fields',
            'type'        => 'group',
            'description' => 'Select lead Attributes',
            'options'     => array(
                'group_title'       => 'Attribute {#}',
                'add_button'        => 'Add Attribute',
                'remove_button'     => 'Remove Attribute',
                'sortable'          => true,
            ),
            'show_on_cb'  => array( $this, 'is_gform_selected' ),
        ) );

        // secondary fields status
        $cpt_meta_box->add_group_field( $attr_group, array(
            'id'        => 'ctgfapi_secondary_field_status',
            'name'      => 'Status',
            'type'      => 'checkbox',
        ) );

        // select Attribute
        $cpt_meta_box->add_group_field( $attr_group, array(
            'id'               => 'ctgfapi_secondary_attribute',
            'name'             => 'Attribute',
            'type'             => 'select',
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_secondary_attribute_options' ),
        ) );
        
        // select value type
        $cpt_meta_box->add_group_field( $attr_group, array(
            'id'               => 'ctgfapi_secondary_attribute_value_type',
            'type'             => 'radio',
            'default'          => 'static_value',
            'options'          => array(
                'static_value'      => 'Static Value',
                'form_field_value'  => 'Form Field',
            ),
        ) );
        
        // static attribute value
        $cpt_meta_box->add_group_field( $attr_group, array(
            'id'               => 'ctgfapi_secondary_attribute_value_static',
            'name'             => 'Enter Static Value',
            'type'             => 'text',
            'show_on_cb'       => array( $this, 'select_attr_static_value' ),
        ) );
        
        // from attribute value
        $cpt_meta_box->add_group_field( $attr_group, array(
            'id'               => 'ctgfapi_secondary_attribute_value_form_field',
            'name'             => 'Select Form Field',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => array( $this, 'get_gform_fields' ),
            'show_on_cb'       => array( $this, 'select_attr_form_field_value' ),
        ) );

    }

    
    public function get_lead_action_plan_list()
    {
        $action_plans = ApiHandler::get_data_list( 'action_plan' );

        if( !empty($action_plans) ){
            return $action_plans;
        }

        return array();

    }


    public function get_lead_source_list()
    {
        $source_list = ApiHandler::get_data_list( 'source' );

        if( !empty($source_list) ){
            return $source_list;
        }

        return array();
    }


    public function get_lead_sales_cycle_list()
    {
        $sales_cycle = ApiHandler::get_data_list( 'sales_cycle' );

        if( !empty($sales_cycle) ){
            $new_lead_option = array( '-1' => 'New Lead' );
            return  array_merge( $new_lead_option,  $sales_cycle );
        }

        return array();
    }


    public function get_gravity_forms()
    {
        $gravity_forms = array();

        if( class_exists('GFAPI') ){
            $forms = GFAPI::get_forms();
            foreach( $forms as $form ){
                $gravity_forms[ $form['id'] ] = $form['title'];
            }
        }

        return $gravity_forms;
    }


    public function get_gform_fields()
    {
        global $post;
        $options = array();

        if( class_exists('GFAPI') && isset($post) && $post->post_type == 'ctgfapi_entry_rules' ){

            $selected_form_id = get_post_meta( $post->ID, 'ctgfapi_selected_form', true );

            if( isset($selected_form_id) && $selected_form_id > 0 ){

                $form = GFAPI::get_form( $selected_form_id );

                if( isset($form) && $form !== false ){
                    
                    $form_fields = $form['fields'];

                    if( is_array($form_fields) && !empty($form_fields) ){

                        foreach( $form_fields as $field ){

                            $array_key              = (string)$field->id;
                            $array_value            = (string)$field->label;
                            $options[$array_key]    = $array_value;

                        }

                    }


                }

            }

        }

        return $options;
    }


    public function is_gform_selected()
    {
        global $post;
        if( isset($post) && $post->post_type == 'ctgfapi_entry_rules' ){
            $selected_form_id = get_post_meta( $post->ID, 'ctgfapi_selected_form', true );
            if( isset($selected_form_id) && $selected_form_id !== '' && $selected_form_id > 0 ){
                return true;
            }
        }
        return false;
    }


    public function select_attr_static_value( $field )
    {
        global $post;
        if( isset($post) && $post->post_type == 'ctgfapi_entry_rules' ){
            $index              = $field->group->index;
            $group_val          = $field->group->value();
            
            if( isset($group_val[ $index ]['ctgfapi_secondary_attribute_value_type']) ){

                $attr_value_type    = $group_val[ $index ]['ctgfapi_secondary_attribute_value_type'];

                if( $attr_value_type == 'static_value' ){
                    return true;
                }

            }

        }
        return false;
    }


    public function select_attr_form_field_value( $field )
    {
        global $post;
        if( isset($post) && $post->post_type == 'ctgfapi_entry_rules' ){
            $index              = $field->group->index;
            $group_val          = $field->group->value();

            if( isset($group_val[ $index ]['ctgfapi_secondary_attribute_value_type']) ){

                $attr_value_type    = $group_val[ $index ]['ctgfapi_secondary_attribute_value_type'];

                if( $attr_value_type == 'form_field_value' ){
                    return true;
                }

            }

        }
        return false;
    }


    public function get_secondary_attribute_options()
    {
        $options = array(
            '0'             => 'Select',
            'phone'         => 'Phone Number',
            'smsok'         => 'SMS Consent',
            'address'       => 'User Address',
            'city'          => 'User City',
            'state'         => 'User State',
            'zip'           => 'User Zip Code',
            'compName'      => 'User Compnay Name',
            'age'           => 'User Age',
            'gender'        => 'User Gender',
            'whiteboard'    => 'Whiteboard note',
        );
        return $options;
    }

}