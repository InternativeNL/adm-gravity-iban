<?php
/*
Plugin Name: Admium IBAN for Gravity Forms
Plugin URI: www.admium.nl
Description: Add IBAN mask for Gravity Forms field based on http://code.google.com/p/php-iban
Author: Admium
Version: 0.1
Author URI: www.admium.nl
GitHub Plugin URI: AdmiumNL/adm-gravity-iban
*/

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ( class_exists( 'GFForms' ) ) {

    add_filter("gform_input_masks", "add_iban_mask");
    function add_iban_mask($masks){
    
        $masks["IBAN"] = "iban";
        return $masks;
    
    }
    
    add_filter("gform_input_mask_script", "set_mask_script", 10, 4);
    function set_mask_script($script, $form_id, $field_id, $mask){
        
        // Mask: 2 letters / 2 numbers / max 32 numbers or letters
        if ($mask == "iban") {
            $script = "jQuery('#input_{$form_id}_{$field_id}').mask('aa99 ?**** **** **** **** **** **** **** ****');";
        }
        return $script;
        
    }

	/**
	 * Validates Gravity Forms fields with an IBAN mask.
	 *
	 * @since 	0.1
	 * @param 	array 	$validation_result	Contains the validation result and the current Form Object.
	 * @return 	array						The new validation result.
	 */
	function gform_iban_validation($validation_result){

	    foreach ( $validation_result['form']['fields'] as &$field ) {

	        $fieldValue = rgpost( "input_{$field['id']}" );

	        if ( 'iban' == $field['inputMaskValue'] ) {
				if ( 0 == strlen( $fieldValue ) ) { // If empty continue in foreach loop
	                continue;
	            } else { // If not empty do the IBAN check
					require_once( dirname( __FILE__ ) . '/php-iban.php' );
	                if ( ! verify_iban( $fieldValue ) ) {
	                    $validation_result['is_valid'] = false;
	                    $field['failed_validation'] = true;
	                    $field['validation_message'] = __( 'Please enter a valid value.' , 'gravityforms' );
	                }
	            }
	        }
	    }
	    return $validation_result;

	}
	add_filter( 'gform_validation', 'gform_iban_validation' );
}
