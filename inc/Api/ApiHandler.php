<?php 
namespace Inc\Api;
use \Inc\Admin\AdminNotice;

if ( ! defined( 'ABSPATH' ) ) exit;

class ApiHandler
{
    public $api_url = 'https://api.clienttether.com/v2/api/';

    public function register()
    {
        // dynamically hook with gform
        add_action( 'gform_after_submission', array( $this, 'gform_submission_trigger' ), 10, 2 );
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

                $post_id = $submission_entry_rule->ID;

                $entry_status = carbon_get_post_meta( $post_id, 'ctgfapi_entry_status' );
                if( $entry_status == 'enabled' ){

                    $selected_form_id       = carbon_get_post_meta( $post_id, 'ctgfapi_selected_form' );
                    $first_name_field_id    = carbon_get_post_meta( $post_id, 'ctgfapi_user_first_name' );
                    $last_name_field_id     = carbon_get_post_meta( $post_id, 'ctgfapi_user_last_name' );
                    $email_field_id         = carbon_get_post_meta( $post_id, 'ctgfapi_user_email' );

                    $this->write_to_error_log( 'post_meta', get_post_meta( $post_id ) );
                    $this->write_to_error_log( 'selected_form_id', $selected_form_id );
                    $this->write_to_error_log( 'first_name_field_id', $first_name_field_id );
                    $this->write_to_error_log( 'last_name_field_id', $last_name_field_id );
                    $this->write_to_error_log( 'email_field_id', $email_field_id );

                    if( $form['id'] == $selected_form_id ){

                        // essential required data
                        $submission_data = array(
                            'firstName'     => rgar( $entry, $first_name_field_id ),
                            'lastName'      => rgar( $entry, $last_name_field_id ),
                            'email'         => rgar( $entry, $email_field_id ),
                        );

                        // optional secondary data
                        $secondary_fields = carbon_get_post_meta( $post_id, 'ctgfapi_secondary_fields' );
                        if( is_array($secondary_fields) && !empty($secondary_fields) ){

                            foreach( $secondary_fields as $secondary_field_row ){

                                if( $secondary_field_row['ctgfapi_secondary_field_status'] == 'enabled' ){
                        
                                    if( $secondary_field_row['ctgfapi_secondary_attribute_value_type'] == 'static_value' ){

                                        $submission_data[ $secondary_field_row['ctgfapi_secondary_attribute'] ] = $secondary_field_row['ctgfapi_secondary_attribute_value_static'];

                                    }elseif ( $secondary_field_row['ctgfapi_secondary_attribute_value_type'] == 'form_field_value' ) {
                                        
                                        $submission_data[ $secondary_field_row['ctgfapi_secondary_attribute'] ] = rgar( $entry, $secondary_field_row['ctgfapi_secondary_attribute_value_form_field'] ) ;

                                    }
                                    
                                }

                            }

                        }

                        $this->submit_to_api( $submission_data );

                    }

                }

            }

        }

    }


    public function submit_to_api( array $data = array() )
    {
        // API header parameters
        $api_access_token   = carbon_get_theme_option( 'ctgfapi_access_token' );
        $api_web_key        = carbon_get_theme_option( 'ctgfapi_web_key' );

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

            $this->write_to_error_log( 'data before call', $data );

            // first check if the client already exists or not
            if( isset($data['email']) ){

                // make API url
                $url = $this->api_url . 'read_client_exist';
                $api_endpoint = add_query_arg( array( 'email' => $data['email'] ), $url );

                // API args
                $api_args = array();
                $api_args['headers'] = array();
                $api_args['headers']['X-Access-Token']  = $api_access_token;
                $api_args['headers']['X-Web-Key']       = $api_web_key;

                $response = wp_remote_request( $api_endpoint , $api_args );

                if( !is_wp_error($response) && ($response['response']['code'] == 200 || $response['response']['code'] == 201) ){
                    
                    // $this->write_to_error_log( 'response_success', $response );
                    $response_body = json_decode( $response['body'] );

                }else{
                    
                    $this->write_to_error_log( 'response_error', $response );

                }

            }else{
                // create new entry
            }

        }else{
            $this->write_to_error_log( 'API keys not set', array( $api_access_token, $api_web_key ) );
        }

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