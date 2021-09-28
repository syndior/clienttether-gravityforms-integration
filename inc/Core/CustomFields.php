<?php 
namespace Inc\Core;
use \Carbon_Fields\Carbon_Fields;
use \Carbon_Fields\Container;
use \Carbon_Fields\Field;
use \GFAPI;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomFields
{
    public function register()
    {

        // load carbon fields
        add_action( 'after_setup_theme', array( $this, 'load_carbon_fields' ) );

        // register carbon fields
        add_action( 'carbon_fields_register_fields', array( $this, 'register_carbon_fields' ) );


    }


    public function cpt_fields()
    {
        $custom_fields = array();

        return $custom_fields;
    }


    public function get_gform_fields()
    {
        $options = array();

        if( class_exists('GFAPI') ){

            $selected_form_id = carbon_get_post_meta( get_the_ID(), 'ctgfapi_selected_form' );

            if( isset($selected_form_id) && $selected_form_id > 0 ){

                $form = GFAPI::get_form( $selected_form_id );

                if( isset($form) && $form !== false ){
                    
                    $form_fields = $form['fields'];

                    if( is_array($form_fields) && !empty($form_fields) ){

                        foreach( $form_fields as $field ){

                            $array_key      = (string)$field->id;
                            $array_value    = (string)$field->label;

                            $options[$array_key] = $array_value;

                        }

                    }


                }

            }

        }

        return $options;
    }


    public function get_secondary_attribute_options()
    {
        $options = array(
            '0'         => 'Select',
            'phone'     => 'Phone Number',
            'smsok'     => 'SMS Consent',
            'address'   => 'User Address',
            'city'      => 'User City',
            'state'     => 'User State',
            'zip'       => 'User Zip Code',
            'compName'  => 'User Compnay Name',
            'age'       => 'User Age',
            'gender'    => 'User Gender',
        );
        return $options;
    }


    public function get_secondary_attribute_value_form_field_options()
    {
        $options = array();

        if( class_exists('GFAPI') ){

            $selected_form_id = carbon_get_post_meta( get_the_ID(), 'ctgfapi_selected_form' );

            if( isset($selected_form_id) && $selected_form_id > 0 ){

                $form = GFAPI::get_form( $selected_form_id );

                if( isset($form) && $form !== false ){
                    
                    $form_fields = $form['fields'];

                    if( is_array($form_fields) && !empty($form_fields) ){

                        foreach( $form_fields as $field ){
                            $options[$field->id] = $field->label;
                        }

                    }


                }

            }

        }

        return $options;
    }


    public function load_carbon_fields()
    {
        Carbon_Fields::boot();
    }


    public function register_carbon_fields()
    {
        // main plugin options page
        $options_page = Container::make( 'theme_options', CTGF_API_ADMIN_PAGE ,__( 'ClientTether Gravity Forms Integration' ) );
        $options_page->set_page_menu_title( 'ClientTether' );
        $options_page->set_icon( CTGF_API_URL . 'assets/img/admin-icon.png' );
        $options_page->set_page_menu_position( 99 );
        $options_page->add_fields( array(
            Field::make( 'text', 'ctgfapi_access_token', __( 'ClientTether Access Token' ) ),
            Field::make( 'text', 'ctgfapi_web_key', __( 'ClientTether Web Key' ) ),
        ) );


        // CPT Custom fields
        $cpt_met_box = Container::make( 'post_meta', 'ctgfapi-cpt-meta-box' ,'Setup Lead Data' );
        $cpt_met_box->where( 'post_type', '=', 'ctgfapi_entry_rules' );

        // entry stats field
        $entry_status_field = Field::make( 'checkbox', 'ctgfapi_entry_status', 'Status' );
        $entry_status_field->set_option_value( 'enabled' );
        $entry_status_field->set_default_value( 'enabled' );

        // gravity form field
        $gform_field = Field::make( 'gravity_form', 'ctgfapi_selected_form', __( 'Select Form' ) );
        $gform_field->set_required( true );
        $gform_field->set_default_value( '0' );

        // user first name field
        $user_first_name_field = Field::make( 'select', 'ctgfapi_user_first_name', __( 'User First Name' ) );
        $user_first_name_field->add_options( array( $this, 'get_gform_fields' ) );
        $user_first_name_field->set_conditional_logic( array(
            array(
                'field'     => 'ctgfapi_selected_form',
                'compare'   => '>',
                'value'     => '0',
            ),
        ) );
        
        // user last name field
        $user_last_name_field = Field::make( 'select', 'ctgfapi_user_last_name', __( 'User Last Name' ) );
        $user_last_name_field->add_options( array( $this, 'get_gform_fields' ) );
        $user_last_name_field->set_conditional_logic( array(
            array(
                'field'     => 'ctgfapi_selected_form',
                'compare'   => '>',
                'value'     => '0',
            ),
        ));
        
        // user email field
        $user_email_field = Field::make( 'select', 'ctgfapi_user_email', __( 'User Email' ) );
        $user_email_field->add_options( array( $this, 'get_gform_fields' ) );
        $user_email_field->set_conditional_logic( array(
            array(
                'field'     => 'ctgfapi_selected_form',
                'compare'   => '>',
                'value'     => '0',
            ),
        ));

        // add required fields
        $cpt_met_box->add_fields( array( 
            $entry_status_field, 
            $gform_field, 
            $user_first_name_field, 
            $user_last_name_field,
            $user_email_field,
        ) );

        // secondary fields template
        $status_field = Field::make( 'checkbox', 'ctgfapi_secondary_field_status', 'Status' );
        $status_field->set_option_value( 'enabled' );
        $status_field->set_default_value( 'enabled' );

        $attribute_field = Field::make( 'select', 'ctgfapi_secondary_attribute', 'Attribute' );
        $attribute_field->add_options( array( $this, 'get_secondary_attribute_options' ) );

        $attribute_value_type = Field::make( 'radio', 'ctgfapi_secondary_attribute_value_type', 'Value Type' );
        $attribute_value_type->add_options( array( 
            'static_value'      => 'Static Value',
            'form_field_value'  => 'Form Field',
        ) );
        $attribute_value_type->set_conditional_logic( array( 
            array(
                'field'     => 'ctgfapi_secondary_attribute',
                'compare'   => '!=',
                'value'     => '0',
            ),
        ) );

        $attribute_value_static = Field::make( 'text', 'ctgfapi_secondary_attribute_value_static', 'Enter Static Value' );
        $attribute_value_static->set_conditional_logic( array( 
            array(
                'field'     => 'ctgfapi_secondary_attribute',
                'compare'   => '!=',
                'value'     => '0',
            ),
            array(
                'field'     => 'ctgfapi_secondary_attribute_value_type',
                'compare'   => '=',
                'value'     => 'static_value',
            ),
        ) );

        $attribute_value_form_field = Field::make( 'select', 'ctgfapi_secondary_attribute_value_form_field', 'Select Form Field' );
        $attribute_value_form_field->add_options( array( $this, 'get_secondary_attribute_value_form_field_options' ) );
        $attribute_value_form_field->set_conditional_logic( array( 
            array(
                'field'     => 'ctgfapi_secondary_attribute',
                'compare'   => '!=',
                'value'     => '0',
            ),
            array(
                'field'     => 'ctgfapi_secondary_attribute_value_type',
                'compare'   => '=',
                'value'     => 'form_field_value',
            ),
        ) );


        // Secondary fields group
        $field_group = Field::make( 'complex', 'ctgfapi_secondary_fields', 'Secondary Values' );
        $field_group->set_collapsed( false );
        $field_group->add_fields( array(
            $status_field,
            $attribute_field,
            $attribute_value_type,
            $attribute_value_static,
            $attribute_value_form_field,
        ) );

        $cpt_met_box->add_fields( array( $field_group ) );

    }


    public function write_to_error_log( $label = '', $data = array() )
    {
        ob_start();
        echo $label . ":\n";
        var_dump( $data );
        $log = ob_get_clean();

        error_log( $log );

    }
    

}