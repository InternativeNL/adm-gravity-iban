<?php
/*
Plugin Name: Gravity Forms IBAN
Plugin URI: https://wordpress.org/plugins/gravity-forms-iban/
Description: Adds an IBAN mask and IBAN validation to Gravity Forms.
Author: Admium and Jeroen Schmit, Slim & Dapper
Version: 1.0
Author URI: www.admium.nl
GitHub Plugin URI: AdmiumNL/adm-gravity-iban
*/

if ( class_exists( 'GFForms' ) ) {

	/**
	 * Adds the IBAN mask to the built-in input masks that are displayed in the Text Field input mask setting.
	 *
	 * @since 	1.0
	 * @param 	array	$masks	Current list of masks to be filtered
	 * @return 	array			The list of masks, including the IBAN mask.
	 */
	function gform_iban_add_mask($masks){
		$masks['IBAN'] = 'iban';
		return $masks;
	}
	add_filter( 'gform_input_masks', 'gform_iban_add_mask' );

	/**
	 * Sets the IBAN mask script for a field.
	 *
	 * @since 	1.0
	 * @param 	string 	$script		The script (including <script> tag) to be filtered.
	 * @param 	int 	$form_id	ID of current form.
	 * @param 	int		$field_id	ID of current field.
	 * @param 	string	$mask		Currently configured mask.
	 * @return 	string              The updated script.
	 */
	function gform_iban_set_mask_script($script, $form_id, $field_id, $mask){

		// Mask: 2 letters / 2 numbers / max 32 numbers or letters
		if ( 'iban' == $mask ) {
			$script = "jQuery('#input_{$form_id}_{$field_id}').mask('aa99 ?**** **** **** **** **** **** **** ****');";
		}
		return $script;
	}
	add_filter( 'gform_input_mask_script', 'gform_iban_set_mask_script', 10, 4 );

	/**
	 * Validates Gravity Forms fields with an IBAN mask.
	 *
	 * @since 	1.0
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
					require_once( dirname( __FILE__ ) . '/lib/php-iban.php' );
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
