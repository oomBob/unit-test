<?php 

/**
 * Recursively find widget settings by widget ID.
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function find_elementor_widget_settings( $elements, $form_id ) {
    foreach ( $elements as $element ) {
        if ( isset( $element['id'] ) && $element['id'] === $form_id ) {
            return isset( $element['settings'] ) ? $element['settings'] : [];
        }

        if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
            $settings = find_elementor_widget_settings( $element['elements'], $form_id );

            if ( $settings ) {
                return $settings;
            }
        }
    }

    return [];
}

/**
 * Replace Email Label
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function replace_email_placeholders($template, $form_data, $widget_settings) {

    error_log( print_r( $widget_settings, true ) );
    
    // Loop through form data and replace placeholders
    foreach ($form_data as $key => $value) {
        $placeholder = '[' . $key . ']';
        if (is_string($value)) {
            $template = str_replace($placeholder, sanitize_text_field($value), $template);
        } elseif (is_array($value)) {
            $array_string = implode(', ', array_map('sanitize_text_field', $value));
            $template = str_replace($placeholder, $array_string, $template);
        }
    }

    // Also handle email settings (from widget settings) placeholders
    foreach ($widget_settings as $key => $value) {
        if (is_string($value)) {
            $template = str_replace($placeholder, sanitize_text_field($value), $template);
        } elseif (is_array($value)) {
            $array_string = implode(', ', array_map('sanitize_text_field', $value));
            $template = str_replace($placeholder, $array_string, $template);
        }
    }

    return $template;
}


/**
 * Meta Data
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function get_real_meta_data($page_id) {
    $data = [];

    $data['date']       = date('Y-m-d'); // Current Date
    $data['time']       = date('H:i:s'); // Current Time
    $data['page_url']   = get_permalink($page_id); // Current Page URL
    $data['user_agent'] = esc_html( $_SERVER['HTTP_USER_AGENT'] ); // User Agent
    $data['remote_ip']  = esc_html( $_SERVER['REMOTE_ADDR'] ); // Remote IP

    return $data;
}


/**
 * Dynamic Form Submission Handler
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function dynamic_form_submit_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'oom_form_submit_action' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid nonce. Please refresh the page and try again.' ) );
    }
    // Parse form data
    parse_str( $_POST['form_data'], $form_data );

     // Spam check
     if ( ! empty( $form_data['oom_form'] ) ) {
        wp_send_json_error(['message' => 'Spam detected.'] );
    }

    $post_id = isset( $form_data['post_id'] ) ? intval( $form_data['post_id'] ) : 0;
    $page_id = isset( $form_data['page_id'] ) ? intval( $form_data['page_id'] ) : 0;
    $form_id = isset( $form_data['form_id'] ) ? sanitize_text_field( $form_data['form_id'] ) : '';

    if ( ! $post_id || ! $form_id ) {
        wp_send_json_error( array( 'message' => 'Invalid post ID or form ID.' ) );
    }

    // Get the Elementor page settings
    $document = \Elementor\Plugin::instance()->documents->get( $post_id );
    
    if ( $document ) {
        $elementor_data = $document->get_elements_data();
        $widget_settings = find_elementor_widget_settings( $elementor_data, $form_id );
		
// 		error_log( print_r( $document, true ) );
		
// 		error_log( print_r( $widget_settings, true ) );
        
        if ( $widget_settings ) {
            $errors = [];
            foreach ( $widget_settings['form_fields'] as $field ) {
                $custom_id = $field['custom_id'];
                $required = isset($field['required']) && $field['required'] === 'true';
                $field_value = $form_data['field_' . $custom_id];

                if ( $required && empty( $field_value ) ) {
                    $errors[] = $field['field_label'] . ' is required.';
                    continue;
                }

                if ( ! empty( $field_value ) ) {
                    if( isset($field['field_type']) ){
                        switch ( $field['field_type'] ) {
                            case 'email':
                                if ( ! filter_var( $field_value, FILTER_VALIDATE_EMAIL ) ) {
                                    $errors[] = 'Invalid email format for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            case 'url':
                                if ( ! filter_var( $field_value, FILTER_VALIDATE_URL ) ) {
                                    $errors[] = 'Invalid URL format for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            case 'tel':
                                if ( ! preg_match( '/^[0-9+\-\(\)\s]*$/', $field_value ) ) {
                                    $errors[] = 'Invalid phone number for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            case 'number':
                                if ( ! is_numeric( $field_value ) ) {
                                    $errors[] = 'Invalid number for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            case 'date':
                                if ( ! strtotime( $field_value ) ) {
                                    $errors[] = 'Invalid date format for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            case 'time':
                                if ( ! preg_match( '/^(2[0-3]|[01]?[0-9]):[0-5]?[0-9]$/', $field_value ) ) {
                                    $errors[] = 'Invalid time format for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            case 'checkbox':
                            case 'acceptance':
                                if ( $required && empty( $field_value ) ) {
                                    $errors[] = $field['field_label'] . ' must be checked.';
                                }
                                break;
    
                            case 'select':
                                if ( $required && empty( $field_value ) ) {
                                    $errors[] = 'Please select an option for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            case 'radio':
                                if ( $required && empty( $field_value ) ) {
                                    $errors[] = 'Please select an option for ' . $field['field_label'] . '.';
                                }
                                break;
    
                            default:
                                break;
                        }
                    }
                }
            }

            if ( ! empty( $errors ) ) {
                wp_send_json_error( array( 'message' => implode( ' ', $errors ) ) );
            }



            // Save submission to the database 
            global $wpdb;
            $table_name = $wpdb->prefix . 'oom_form_submissions';
            
            $submission_data = maybe_serialize($form_data);
            $encryption_key = get_option('oom_form_encryption_key', 'oomDefaultKeys');
            $encrypted_submission_data = oom_encrypt_form_data($submission_data);
            if(isset($widget_settings['meta_data'])){
                $selected_meta_fields = $widget_settings['meta_data'];
            }else{
                $selected_meta_fields = array();
            }
            $all_meta_data = get_real_meta_data($page_id);

            // Initialize an empty metadata array
            $metadata = array();

            // Filter metadata based on selected fields
            foreach ($selected_meta_fields as $field) {
                if (isset($all_meta_data[$field])) {
                    $metadata[$field] = $all_meta_data[$field];  
                }
            }

            // Convert metadata array to JSON
            $metadata_json = json_encode( $metadata );

            $wpdb->insert(
                $table_name,
                array(
                    'form_id' => $form_id,
                    'ec_key' => $encryption_key,
                    'form_name' => sanitize_text_field($widget_settings['form_name']),
                    'submission_data' => $encrypted_submission_data,
                    'submission_page_id' => $post_id,
                    'submitted_at' => current_time('mysql'),
                    'metadata' => $metadata_json,
                )
            ); 
			
			// Send Email 2
			if (!empty($widget_settings['email2_to'])) {
				$mailto = $widget_settings['email2_to'];
				$to2 = replace_email_placeholders($mailto, $form_data, $widget_settings);
				$subject_template2 = $widget_settings['email2_subject'];
				$from2 = $widget_settings['email2_from'];
				$reply_to2 = $widget_settings['email2_reply_to'];
				$email_message2 = $widget_settings['email2_message'];

				// Process subject
				$subject2 = replace_email_placeholders($subject_template2, $form_data, $widget_settings);

				// Process message
				if ($email_message2 == '[all-fields]') {
					$message_template2 = "You have a new form submission:\n\n";
					foreach ($form_data as $key => $value) {
						if (strpos($key, 'field_') === 0) {
							$field_name = str_replace('field_', '', $key);
							$field_name = preg_replace('/^[a-zA-Z]+_/', '', $field_name);
							$message_template2 .= ucwords(preg_replace('_', ' ', $field_name)) . ": [" . $key . "]<br>";
						}
					}
				} else {
					$message_template2 = $email_message2;
				}

				// Append metadata to the email message
				$message_template2 .= "\n\nMetadata:\n";
				foreach ($metadata2 as $meta_key => $meta_value) {
					$message_template2 .= ucwords(preg_replace('_', ' ', $meta_key)) . ": " . $meta_value . "<br>";
				}

				// Replace placeholders in the message
				$message2 = replace_email_placeholders($message_template2, $form_data, $widget_settings);

				// Construct email headers
				$headers2 = [];
				if (!empty($from2)) {
					$headers2[] = 'From: ' . sanitize_email($from2);
				}
				if (!empty($reply_to2)) {
					$headers2[] = 'Reply-To: ' . sanitize_email($reply_to2);
				}
				$headers2[] = 'Content-Type: text/html; charset=UTF-8';

				// Send the email
				wp_mail($to2, $subject2, $message2, $headers2);
			}



            // Send Email
            $to = $widget_settings['email_to'];
            $subject_template = $widget_settings['email_subject'];
            $from = $widget_settings['email_from'];
            $reply_to = $widget_settings['email_reply_to'];
            $email_message = $widget_settings['email_message'];
            $subject = replace_email_placeholders($subject_template, $form_data, $widget_settings);
            if ($email_message == '[all-fields]'){
                $message_template = "You have a new form submission:\n\n";
                foreach ($form_data as $key => $value) {
                    if (strpos($key, 'field_') === 0) {
                        $field_name = str_replace( 'field_', '', $key );
						$field_name = preg_replace('/^[a-zA-Z]+_/', '', $field_name);
                        $message_template .= ucwords(str_replace('_', ' ', $field_name)) . ": [" . $key . "]<br>";
                    }
                }
            }else{
                $message_template = $email_message;
            }

            // Append metadata to the email message
            $message_template .= "\n\nMetadata:\n";
            
            foreach ($metadata as $meta_key => $meta_value) {
                $message_template .= ucwords(str_replace('_', ' ', $meta_key)) . ": " . $meta_value . "<br>";
            }
            
            $message = replace_email_placeholders($message_template, $form_data, $widget_settings);
            
            // error_log( print_r( $message, true ) );

            // Construct email headers
            $headers = [];
            $headers[] = 'From: ' . $from;
            $headers[] = 'Reply-To: ' . $reply_to;
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
            
            // Send the email
            if ( wp_mail( $to, $subject, $message, $headers ) ) {
                // Check if 'redirect_link' is an array with a 'url' key
                if ( !empty( $widget_settings['redirect_link']['url'] ) ) {
                    // If 'redirect_link' has a 'url' key, use it
                    $redirect_url = esc_url( $widget_settings['redirect_link']['url'] );
                } elseif ( !empty( $widget_settings['__dynamic__']['redirect_link'] ) ) {
                    // If '__dynamic__' contains a dynamic link tag, handle it
                    $dynamic_link = $widget_settings['__dynamic__']['redirect_link'];
                    
                    // Extract post_id from the dynamic tag
                    if ( preg_match( '/post_id%22%3A%22(\d+)%22/', $dynamic_link, $matches ) ) {
                        $post_id = $matches[1];
                        $redirect_url = get_permalink( $post_id );
                    } else {
                        $redirect_url = ''; // Handle case if post_id is not found
                    }
                } else {
                    // Default case if no valid URL or dynamic link is found
                    $redirect_url = '';
                }

                // error_log($redirect_url);

                wp_send_json_success( array( 
                    'message' => 'Form submitted successfully!',
                    'redirect_url' => $redirect_url
                ));
            } else {
                wp_send_json_error( array( 'message' => 'Failed to send email. Please try again later.' ) );
            }

        } else {
            wp_send_json_error( 'Widget settings not found' );
        }
    }

    wp_send_json_error( 'Unable to load Elementor document.' );
    wp_die();
}
add_action('wp_ajax_dynamic_form_submit', 'dynamic_form_submit_handler');
add_action('wp_ajax_nopriv_dynamic_form_submit', 'dynamic_form_submit_handler');

/**
 * AJAX handler for postal code validation
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function validate_postal_code_handler() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'oom_form_submit_action')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    $postal_code = sanitize_text_field($_POST['postal_code']);
    
    // Validate postal code format
    if (empty($postal_code)) {
        wp_send_json_success(array(
            'valid' => false,
            'message' => 'Postal code is required.'
        ));
        return;
    }
    
    // Check if exactly 6 digits
    if (!preg_match('/^\d{6}$/', $postal_code)) {
        wp_send_json_success(array(
            'valid' => false,
            'message' => 'Postal code must be exactly 6 digits.'
        ));
        return;
    }
    
    // If we get here, postal code is valid
    wp_send_json_success(array(
        'valid' => true,
        'message' => 'Valid postal code.'
    ));
}
add_action('wp_ajax_validate_postal_code', 'validate_postal_code_handler');
add_action('wp_ajax_nopriv_validate_postal_code', 'validate_postal_code_handler');

function oom_encrypt_form_data($data) {
    $encryption_key = get_option('oom_form_encryption_key', 'oomDefaultKeys');
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}