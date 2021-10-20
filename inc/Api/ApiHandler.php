<?php 
namespace Inc\Api;
use \Inc\Admin\AdminNotice;
use \Inc\Base\HelperFunctions;
use \GFAPI;

if ( ! defined( 'ABSPATH' ) ) exit;

class ApiHandler
{

    public function register()
    {
        // dynamically hook with gform
        add_action( 'gform_after_submission', array( $this, 'gform_submission_trigger' ), 20, 2 );

        // setup api constant
        $option_page_meta   = get_option( CTGF_API_ADMIN_PAGE );
        define( 'CLIENTTETHER_API_URL', 'https://api.clienttether.com/v2/api/' );
        define( 'CLIENTTETHER_API_ACCESS_TOKEN', $option_page_meta['ctgfapi_access_token'] );
        define( 'CLIENTTETHER_API_WEB_KEY', $option_page_meta['ctgfapi_web_key'] );
    }


    public function gform_submission_trigger( $entry, $form )
    {
        
        $args = array(
            'post_type'     => 'ctgfapi_entry_rules',
            'post_status'   => 'publish',
            'numberposts'   => -1,
            'orderby'       => 'date',
		    'order'         => 'DESC',
        );
        $submission_entries = get_posts( $args );

        if( is_array($submission_entries) && !empty($submission_entries) ){

            foreach( $submission_entries as $submission_entry_rule ){

                $post_id        = $submission_entry_rule->ID;
                $entry_status   = get_post_meta( $post_id, 'ctgfapi_entry_status', true );

                if( $entry_status == 'on' ){

                    $selected_form_id = get_post_meta( $post_id, 'ctgfapi_selected_form', true );

                    if( $form['id'] == $selected_form_id ){

                        $first_name_field_id    = get_post_meta( $post_id, 'ctgfapi_user_first_name', true );
                        $last_name_field_id     = get_post_meta( $post_id, 'ctgfapi_user_last_name', true );
                        $email_field_id         = get_post_meta( $post_id, 'ctgfapi_user_email', true );
                        $action_plan_id         = get_post_meta( $post_id, 'ctgfapi_lead_action_plan', true );
                        $sales_cycle_id         = get_post_meta( $post_id, 'ctgfapi_lead_sales_cycle', true );
                        $lead_source_id         = get_post_meta( $post_id, 'ctgfapi_lead_source', true );
                        
                        $action_plan_id         = isset( $action_plan_id ) ? $action_plan_id : '0';
                        $sales_cycle_id         = isset( $sales_cycle_id ) ? $sales_cycle_id : '0';
                        $lead_source_id         = isset( $lead_source_id ) ? $lead_source_id : '0';

                        // essential required data
                        $submission_data = array(
                            'firstName'         => rgar( $entry, $first_name_field_id ),
                            'lastName'          => rgar( $entry, $last_name_field_id ),
                            'email'             => rgar( $entry, $email_field_id ),
                            'action_plan_id'    => $action_plan_id,
                            'sales_cycle_id'    => $sales_cycle_id,
                            'lead_source_id'    => $lead_source_id,
                        );

                        // optional secondary data
                        $secondary_fields = get_post_meta( $post_id, 'ctgfapi_secondary_fields', true );
                        if( is_array($secondary_fields) && !empty($secondary_fields) ){

                            foreach( $secondary_fields as $secondary_field_row ){

                                $field_status           = isset( $secondary_field_row['ctgfapi_secondary_field_status'] ) ? $secondary_field_row['ctgfapi_secondary_field_status'] : null;
                                $field_type             = isset( $secondary_field_row['ctgfapi_secondary_attribute_value_type'] ) ? $secondary_field_row['ctgfapi_secondary_attribute_value_type'] : null;
                                $field_attr_key         = isset( $secondary_field_row['ctgfapi_secondary_attribute'] ) ? $secondary_field_row['ctgfapi_secondary_attribute'] : null;
                                $field_static_value     = isset( $secondary_field_row['ctgfapi_secondary_attribute_value_static'] ) ? $secondary_field_row['ctgfapi_secondary_attribute_value_static'] : null;
                                $field_form_field_id    = isset( $secondary_field_row['ctgfapi_secondary_attribute_value_form_field'] ) ? $secondary_field_row['ctgfapi_secondary_attribute_value_form_field'] : null;

                                if( isset( $field_status ) && $field_status == 'on' ){
                        
                                    if( isset($field_type) && isset($field_static_value) && $field_type == 'static_value' ){

                                        $submission_data[ $field_attr_key ] = esc_html( $field_static_value );

                                    }elseif ( isset($field_type) && isset($field_form_field_id) && $field_type == 'form_field_value' ) {
                                        
                                        // handle multiple data points for whiteboard note value
                                        if( $field_attr_key == 'whiteboard' ){


                                            // string variable for data
                                            $current_form_field_label = '';
                                            $current_form_field_value = '';

                                            // get current gform field object
                                            $current_form_id    = $form['id'];
                                            $current_field_id   = $field_form_field_id;
                                            $current_field_obj  = GFAPI::get_field(  $current_form_id, $current_field_id );

                                            if( $current_field_obj !== false && isset( $current_field_obj->label ) ){
                                                $current_form_field_label = $current_field_obj->label;
                                            }

                                            // get form field value
                                            $current_form_field_value = rgar( $entry, $field_form_field_id );
                                            if( !(isset( $current_form_field_value ) && gettype( $current_form_field_value ) == 'string') ){
                                                $current_form_field_value = 'N/A';
                                            }

                                            // handle fields with array as return value
                                            if( in_array( $current_field_obj->type, array( 'multiselect', 'checkbox' ) ) ){
                                                $current_form_field_value = $current_field_obj->get_value_export( $entry, $field_form_field_id, true );
                                            }

                                            // update value
                                            $whiteboard_data_item = '<p>(' . $current_form_field_label .' : '. $current_form_field_value . ')</p>';
                                            $submission_data[ $field_attr_key ] .= $whiteboard_data_item;


                                        }else{

                                            $submission_data[ $field_attr_key ] = strval( esc_html( rgar( $entry, $field_form_field_id ) ) );

                                        }

                                    }
                                    
                                }

                            }

                        }

                        $client_exists = $this->client_exists( $submission_data );

                        if( $client_exists == false ){
                            // create new client
                            $this->create_client( $submission_data );
                        }else{

                            // update existing client
                            if( isset( $client_exists['data'] ) ){

                                $client_id = $client_exists['data'][0]['client_id'];
                                if( isset($client_id) && $client_id > 0 ){

                                    $submission_data['client_id'] = $client_id;
                                    $this->update_client( $submission_data );

                                }else{
                                    HelperFunctions::error_log( 'error client id not set', $client_id );
                                }

                            }else{
                                HelperFunctions::error_log( 'error client data not set', $client_exists );
                            }

                        }

                    }

                }

            }

        }

    }


    public function create_client( array $data = array() )
    {
        // API header parameters
        $api_access_token   = CLIENTTETHER_API_ACCESS_TOKEN;
        $api_web_key        = CLIENTTETHER_API_WEB_KEY;

        if( ( isset($api_access_token) && strlen($api_access_token) > 0 ) && ( isset($api_web_key) && strlen($api_web_key) > 0 ) ){

            // submittion data
            $defaults = array(
                'firstName'         => '',
                'lastName'          => '',
                'phone'             => null,
                'email'             => null,
                'smsok'             => null,
                'address'           => null,
                'city'              => null,
                'state'             => null,
                'zip'               => null,
                'compName'          => null,
                'age'               => null,
                'gender'            => null,
                'action_plan_id'    => null,
                'sales_cycle_id'    => null,
                'lead_source_id'    => null,
            );

            // fallbacks/filter
            $data = wp_parse_args( $data, $defaults );

            // first check if first name , last name and email is provided
            if( isset($data['firstName']) && isset($data['lastName']) && isset($data['email']) ){

                // make API url
                $url = CLIENTTETHER_API_URL . 'create_client';
                $post_fields = array_filter( $data );
                
                // phone number formating
                if( isset( $post_fields['phone'] ) ){
                    $post_fields['phone'] = preg_replace( '/[^0-9]/', '', $post_fields['phone'] );
                }
                
                $api_endpoint = add_query_arg( $post_fields, $url );

                // API args
                $api_args = array();
                $api_args['method']  = 'POST';
                $api_args['headers'] = array();
                $api_args['headers']['X-Access-Token']  = $api_access_token;
                $api_args['headers']['X-Web-Key']       = $api_web_key;

                $response = wp_remote_request( $api_endpoint , $api_args );

                if( !is_wp_error($response) && ($response['response']['code'] == 200 || $response['response']['code'] == 201) ){
                    
                    // api response
                    $response_body = json_decode( $response['body'], true );

                    // if error with client creation
                    if( !($response_body['ResultCode'] == 'CT_200') ){
                        HelperFunctions::error_log( 'error creating new client', $response_body );
                    }

                }else{
                    HelperFunctions::error_log( 'response_error', $response );
                }

            }else{
                HelperFunctions::error_log( 'first_name, last_name and email fields are required', $data );
            }

        }else{
            HelperFunctions::error_log( 'API keys not set', array( $api_access_token, $api_web_key ) );
        }
    }
    
    
    public function update_client( array $data = array() )
    {
        // API header parameters
        $api_access_token   = CLIENTTETHER_API_ACCESS_TOKEN;
        $api_web_key        = CLIENTTETHER_API_WEB_KEY;

        if( ( isset($api_access_token) && strlen($api_access_token) > 0 ) && ( isset($api_web_key) && strlen($api_web_key) > 0 ) ){

            // submittion data
            $defaults = array(
                'client_id'         => null,
                'firstName'         => '',
                'lastName'          => '',
                'phone'             => null,
                'email'             => null,
                'smsok'             => null,
                'address'           => null,
                'city'              => null,
                'state'             => null,
                'zip'               => null,
                'compName'          => null,
                'age'               => null,
                'gender'            => null,
                'action_plan_id'    => null,
                'sales_cycle_id'    => null,
                'lead_source_id'    => null,
            );

            // fallbacks/filter
            $data = wp_parse_args( $data, $defaults );

            // first check if first name , last name and email is provided
            if( isset($data['client_id']) && isset($data['firstName']) && isset($data['lastName']) && isset($data['email']) ){

                // make API url
                $url = CLIENTTETHER_API_URL . 'update_client_by_id';
                $post_fields = array_filter( $data );
                
                // phone number formating
                if( isset( $post_fields['phone'] ) ){
                    $post_fields['phone'] = preg_replace( '/[^0-9]/', '', $post_fields['phone'] );
                }
                
                $api_endpoint = add_query_arg( $post_fields, $url );

                // API args
                $api_args = array();
                $api_args['method']  = 'POST';
                $api_args['headers'] = array();
                $api_args['headers']['X-Access-Token']  = $api_access_token;
                $api_args['headers']['X-Web-Key']       = $api_web_key;

                $response = wp_remote_request( $api_endpoint , $api_args );

                if( !is_wp_error($response) && ($response['response']['code'] == 200 || $response['response']['code'] == 201) ){
                    
                    // api response
                    $response_body = json_decode( $response['body'], true );

                    // if error with client creation
                    if( !($response_body['ResultCode'] == 'CT_200') ){
                        HelperFunctions::error_log( 'error updating client', $response_body );
                    }

                }else{
                    HelperFunctions::error_log( 'response_error', $response );
                }

            }else{
                HelperFunctions::error_log( 'client_id, first_name, last_name and email fields are required', $data );
            }

        }else{
            HelperFunctions::error_log( 'API keys not set', array( $api_access_token, $api_web_key ) );
        }
    }


    public function client_exists( array $data = array() )
    {
        // API header parameters
        $api_access_token   = CLIENTTETHER_API_ACCESS_TOKEN;
        $api_web_key        = CLIENTTETHER_API_WEB_KEY;

        if( ( isset($api_access_token) && strlen($api_access_token) > 0 ) && ( isset($api_web_key) && strlen($api_web_key) > 0 ) ){

            // submittion data
            $defaults = array(
                'firstName' => '',
                'lastName'  => '',
                'phone'     => null,
                'email'     => null,
                'smsok'     => null,
                'address'   => null,
                'city'      => null,
                'state'     => null,
                'zip'       => null,
                'compName'  => null,
                'age'       => null,
                'gender'    => null,
            );

            // fallbacks/filter
            $data = wp_parse_args( $data, $defaults );

            // need email to check for client
            if( isset($data['email']) ){

                // make API url
                $url = CLIENTTETHER_API_URL . 'read_client_exist';
                $api_endpoint = add_query_arg( array( 'email' => $data['email'] ), $url );

                // API args
                $api_args = array();
                $api_args['headers'] = array();
                $api_args['headers']['X-Access-Token']  = $api_access_token;
                $api_args['headers']['X-Web-Key']       = $api_web_key;

                $response = wp_remote_request( $api_endpoint , $api_args );

                if( !is_wp_error($response) && ($response['response']['code'] == 200 || $response['response']['code'] == 201) ){
                    
                    // api response
                    $response_body = json_decode( $response['body'], true );

                    if( !($response_body['ResultCode'] == 'CT_200') && !($response_body['TotalRecord'] >= 1) && $response_body['ResultCode'] == 'CT_515' ){
                        return false;
                    }else{
                        return $response_body;
                    }

                }else{
                    HelperFunctions::error_log( 'response_error', $response );
                }

            }else{
                HelperFunctions::error_log( 'no email provided for api call', $data );
            }

        }else{
            HelperFunctions::error_log( 'API keys not set', array( $api_access_token, $api_web_key ) );
        }
    }


    public static function get_data_list( string $lists_type = '', int $limit = 100 )
    {
        $api_access_token   = CLIENTTETHER_API_ACCESS_TOKEN;
        $api_web_key        = CLIENTTETHER_API_WEB_KEY;

        if( ( isset($api_access_token) && strlen($api_access_token) > 0 ) && ( isset($api_web_key) && strlen($api_web_key) > 0 ) ){

            if( isset($lists_type) && in_array( $lists_type, array( 'action_plan', 'source', 'sales_cycle' ) ) ){

                // make API url
                if( $lists_type == 'action_plan' ){
                    
                    $url = CLIENTTETHER_API_URL . 'read_action_plan_list';

                }elseif ($lists_type == 'source') {
                    
                    $url = CLIENTTETHER_API_URL . 'read_lead_source_list';

                }elseif ($lists_type == 'sales_cycle') {
                    
                    $url = CLIENTTETHER_API_URL . 'read_sales_cycle_list';

                }

                if( isset($limit) && $limit < 100 ){
                    $query_limit = $limit;
                }else{
                    $query_limit = 100;
                }

                $api_endpoint = add_query_arg( array( 'limit' => $query_limit ), $url );

                // API args
                $api_args = array();
                $api_args['headers'] = array();
                $api_args['headers']['X-Access-Token']  = $api_access_token;
                $api_args['headers']['X-Web-Key']       = $api_web_key;

                $response = wp_remote_request( $api_endpoint , $api_args );

                if( !is_wp_error($response) && ($response['response']['code'] == 200 || $response['response']['code'] == 201) ){
                    
                    // api response
                    $response_body = json_decode( $response['body'], true );

                    if( $response_body['ResultCode'] == 'CT_200' && $response_body['Message'] == 'Success' && isset($response_body['data']) ){
                        
                        $data_list = array();

                        if( $lists_type == 'source' ){
                            
                            foreach( $response_body['data'] as $list_item ){
                                $data_list[ $list_item['lead_source_id'] ] = $list_item['lead_source_name'];
                            }

                        }elseif( $lists_type == 'sales_cycle' ){

                            foreach( $response_body['data'] as $list_item ){
                                $data_list[ $list_item['sales_cycle_id'] ] = $list_item['sales_cycle_name'];
                            }

                        }elseif( $lists_type == 'action_plan' ){

                            foreach( $response_body['data'] as $list_item_parent ){

                                foreach( $list_item_parent as $list_item ){
                                    $data_list[ $list_item['action_plan_id'] ] = $list_item['action_plan_name'];
                                }

                            }

                        }

                        return $data_list;

                    }else{
                        HelperFunctions::error_log( 'api response error', $response_body );
                    }

                }else{
                    HelperFunctions::error_log( 'response_error', $response );
                }

            }else{
                HelperFunctions::error_log( 'lists_type not provided or invalid', $lists_type );
            }

        }else{
            HelperFunctions::error_log( 'API keys not set', array( $api_access_token, $api_web_key ) );
        }
    }

}