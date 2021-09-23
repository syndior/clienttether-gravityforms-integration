<?php 
namespace Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

final class AdminFields
{
    private static $settings_fields = array(
        array(
            'id'            => 'ctgfapi_access_token',
            'title'         => 'ClientTether Access Token',
            'type'          => 'text',
            'placeholder'   => false,
        ),
        array(
            'id'            => 'ctgfapi_web_key',
            'title'         => 'ClientTether Web Key',
            'type'          => 'text',
            'placeholder'   => false,
        ),
    );

    public static function get_settings_fields()
    {
        $fields = array();

        foreach( self::$settings_fields as $field ){
            if( isset( $field['parent'] ) && isset( $field['parent_value'] ) ){
                if( get_option( $field['parent'] ) !== $field['parent_value'] ){
                    continue;
                }
            }
            array_push( $fields, $field );
        }

        return $fields;
    }

    public static function render_field( $arguments = array() )
    {
        $value = get_option( $arguments['id'] );
        if( ! $value ) {
            $value = isset( $arguments['default'] ) ? $arguments['default'] : null;
        }
        
        switch( $arguments['type'] ){
            case 'text':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['id'], $arguments['type'], $arguments['placeholder'], $value );
            break;
            
            case 'number':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['id'], $arguments['type'], $arguments['placeholder'], $value );
            break;

            case 'toggle':
                $checked_attr = $value == 'checked' ? 'checked' : '';
                echo '<label class="wp_admin_fields_switch">';
                echo '<input name="'.$arguments['id'].'" value="'.$value.'" type="checkbox" '.$checked_attr.'>';
                echo '<span class="wp_admin_fields_slider"></span>';
                echo '</label>';
            break;
            
            case 'checkbox':
                foreach($arguments['options'] as $option){
                    $checked_attr = ( in_array( $option['value'], (array)$value ) ) ? 'checked' : '';
                    echo '<p>';
                    echo '<input name="'.$arguments['id'].'[]" value="'.$option['value'].'" type="checkbox" '.$checked_attr.'>';
                    echo '<span>'.$option['label'].'</span>';
                    echo '</p>';
                }
            break;
            
            case 'button':
                $btn_href       = '';
                $btn_css_class  = '';
                $btn_txt        = '';
                $btn_target     = '';

                echo '<a href="'.$btn_href.'" class="'.$btn_css_class.'" target="'.$btn_target.'">'.$btn_txt.'</a>';
            break;

            case 'select':
                echo '<select name="'.$arguments['id'].'">';
                echo '<option>Select</option>';

                foreach( $arguments['options'] as $option_value => $option_label ){
                    $selected = ($option_value == $value) ? 'selected' : '';
                    echo '<option value="'.$option_value.'" '.$selected.'>'.$option_label.'</option>';
                }

                echo '</select>';
            break;
        }

        if( isset($arguments['description']) ){
            echo '<p class="description">'.$arguments['description'].'</p>';
        }
    }
}