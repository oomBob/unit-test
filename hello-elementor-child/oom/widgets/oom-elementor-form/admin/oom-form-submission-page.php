<?php

/**
 * Form Submission Page
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function oom_form_admin_menu() {
    add_menu_page(
        'Elementor Form Submissions', // Page Title
        'Elementor Form', // Menu Title
        'manage_options', // Capability
        'oom_form_submissions', // Menu Slug
        'oom_form_submissions_page', // Function to display the page
         site_url().'/wp-content/themes/hello-elementor-child/oom/widgets/oom-elementor-form/assets/img/oom-logo.svg', // Icon
        26 // Position
    );
}
add_action('admin_menu', 'oom_form_admin_menu');
function oom_form_submissions_page() {
    global $wpdb;

    // Handle bulk delete
    if ( isset( $_POST['bulk_delete'] ) && isset( $_POST['submission_ids'] ) ) {
        $submission_ids = array_map( 'intval', $_POST['submission_ids'] );
        foreach ( $submission_ids as $id ) {
            $wpdb->delete( $wpdb->prefix . 'oom_form_submissions', array( 'id' => $id ), array( '%d' ) );
        }
        echo '<div class="notice notice-success is-dismissible"><p>Selected submissions have been deleted.</p></div>';
    }

    // Fetch submissions based on filters or search
    $search_query = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
    $form_filter = isset( $_GET['form_filter'] ) ? sanitize_text_field( $_GET['form_filter'] ) : '';
    
    // Fetch Submissions with Pagination 
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20; // Number of bookings per page
    $offset = ($paged - 1) * $per_page;
    
    // Initialize the base query for counting submissions
    $count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}oom_form_submissions WHERE 1=1";
    
    // Add conditions based on filters and search query
    if ($search_query) {
        $count_query .= " AND submission_data LIKE '%" . esc_sql($search_query) . "%'";
    }
    if ($form_filter) {
        $count_query .= " AND form_name = '" . esc_sql($form_filter) . "'";
    }
    
    // Get the total number of submissions based on filters
    $total_submissions = $wpdb->get_var($count_query);
    
    // Calculate total pages
    $total_pages = ceil($total_submissions / $per_page);
    
    
    // Build the query to fetch submissions with pagination
    $query = "SELECT * FROM {$wpdb->prefix}oom_form_submissions WHERE 1=1";
    
    if ($search_query) {
        $query .= " AND submission_data LIKE '%" . esc_sql($search_query) . "%'";
    }
    if ($form_filter) {
        $query .= " AND form_name = '" . esc_sql($form_filter) . "'";
    }
    
    // Add pagination to the query
    $query .= " ORDER BY submitted_at DESC LIMIT $offset, $per_page";
    
    // Fetch submissions
    $submissions = $wpdb->get_results($query);


    // Display form filter and search
    echo '<div class="wrap"><h1>OOm Form Submissions</h1>';
    echo '<form method="get" action="" style="display:flex; gap:5px;">';
    echo '<input type="hidden" name="page" value="oom_form_submissions" />';
    echo '<input type="search" name="s" value="' . esc_attr( $search_query ) . '" placeholder="Search Submissions..." />';
    
    // Form filter dropdown
    $forms = $wpdb->get_col( "SELECT DISTINCT form_name FROM {$wpdb->prefix}oom_form_submissions" );
    echo '<select name="form_filter">';
    echo '<option value="">All Forms</option>';
    foreach ( $forms as $form ) {
        echo '<option value="' . esc_attr( $form ) . '" ' . selected( $form_filter, $form, false ) . '>' . esc_html( $form ) . '</option>';
    }
    echo '</select>';
    
    echo '<input type="submit" class="button" value="Filter" />';
    echo '</form>';
    
    // Bulk delete form
    echo '<form method="post" action="">';    
    
        echo '<table class="widefat fixed striped" style="margin: 10px 0;">';
        echo '<thead><tr>';
        echo '<th style="width: 40px;"><input type="checkbox" id="select_all" /></th>';
        echo '<th style="width: 40px;">ID</th>';
        echo '<th>Form Name</th>';
        echo '<th>First Field / Email</th>';
        echo '<th>Submission Page</th>';
        echo '<th>Submission Date</th>';
        echo '<th>Action</th>';
        echo '</tr></thead><tbody>';
        if ( $submissions ) {
            foreach ( $submissions as $submission ) {
                
                $encrypted_data = $submission->submission_data;
                $ec_key = $submission->ec_key;
                $decrypted_submission_data = oom_decrypt_form_data($encrypted_data, $ec_key);
                $submission_data = maybe_unserialize($decrypted_submission_data);
                if (isset($submission_data['page_id'])){
                    $page_id = $submission_data['page_id'];
                }else{
                    $page_id = $submission->submission_page_id;
                }

                $first_field = reset( $submission_data ); // Get the first field
                $email_field = isset( $submission_data['field_email'] ) ? $submission_data['field_email'] : $first_field; // Prefer email if present

                echo '<tr>';
                echo '<th><input type="checkbox" name="submission_ids[]" value="' . esc_attr( $submission->id ) . '" /></th>';
                echo '<td>' . esc_html( $submission->id ) . '</td>';
                echo '<td>' . esc_html( $submission->form_name ) . ' (' . esc_html( $submission->form_id ) . ')</td>';
                echo '<td>' . esc_html( $email_field ) . '</td>';
                echo '<td><a href="' . get_permalink( $page_id ) . '">' . get_the_title( $page_id ) . '</a></td>';
                echo '<td>' . esc_html( date( 'F j, Y g:i a', strtotime( $submission->submitted_at ) ) ) . '</td>';
                echo '<td><a href="' . admin_url( 'admin.php?page=oom_form_submission_view&id=' . $submission->id ) . '">View Details</a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">No submissions found.</td></tr>';
        }

        echo '</tbody></table>';

        echo '<div>';
        echo '<select name="bulk_action">';
        echo '<option value="delete">Delete</option>';
        echo '</select>';
        echo '<input type="submit" name="bulk_delete" class="button button-danger" value="Apply" style="margin: 0 5px;" />';
        echo '<a href="' . admin_url( 'admin.php?page=oom_form_submissions&action=export_to_csv' ) . '" class="button">Export All to CSV</a>';
        echo '</div>';
    

    echo '</form>';
    // Display Pagination
    if ($total_pages > 1) {
        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links([
            'base'    => add_query_arg('paged', '%#%'),
            'format'  => '',
            'current' => $paged,
            'total'   => $total_pages,
            'prev_text' => __('&laquo; Previous', 'oom-form'),
            'next_text' => __('Next &raquo;', 'oom-form'),
        ]);
        echo '</div></div>';
    }
    echo '</div>';
    ?>
    <script type="text/javascript">
        document.getElementById('select_all').onclick = function() {
            var checkboxes = document.getElementsByName('submission_ids[]');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        }
    </script>

    <?php
}


/**
 * Form Submission Details Page
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function oom_form_view_details_page() {
    add_submenu_page(
        null, // No parent menu, hidden page
        'OOm Form Submission Details', // Page Title
        'OOm Form Submission Details', // Menu Title
        'manage_options', // Capability
        'oom_form_submission_view', // Menu Slug
        'oom_form_submission_view_page' // Callback function
    );
}
add_action('admin_menu', 'oom_form_view_details_page');
function oom_form_submission_view_page() {
    if ( ! isset( $_GET['id'] ) ) {
        wp_die( 'Invalid submission ID.' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'oom_form_submissions';
    $submission_id = intval( $_GET['id'] );
    $submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $submission_id ) );

    if ( ! $submission ) {
        wp_die( 'Submission not found.' );
    }

    $encrypted_data = $submission->submission_data;
    $ec_key = $submission->ec_key;
    $decrypted_submission_data = oom_decrypt_form_data($encrypted_data, $ec_key);
    $submission_data = maybe_unserialize($decrypted_submission_data);

    // Start displaying content 
    echo '<div class="wrap">';
        echo '<div>';
            // Submission header and name
            echo '<h1>Submission #' . esc_html( $submission_id ) . '</h1>';
            echo '<table class="form-table widefat fixed striped">';
            foreach ( $submission_data as $key => $value ) {
                if ($key != 'form_id' && $key != 'post_id' && $key != 'page_id' && $key != 'oom_form' && $key != 'phone_full' && $key != 'country_code') {
                    echo '<tr>';
                    $field_name = str_replace( 'field_', '', $key );
                    echo '<td>' . esc_html( ucwords( str_replace( '_', ' ', $field_name ) ) ) . '</td>';
                    if (is_array($value)) {
                        $value = implode(', ', array_map('esc_html', $value));
                    } else {
                        $value = esc_html($value);
                    }
                    echo '<td>' . esc_html( $value ) . '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        echo '</div>';
        // Additional Info section
        echo '<div>';
            echo '<h3>Additional Info</h3>';
            echo '<table class="widefat fixed striped">';
            echo '<thead><tr><th>Form:</th><th>Page:</th><th>Create Date:</th></tr></thead><tbody>';
            echo '<tr><td><a href="#">' . esc_html( $submission->form_name ) . '</a></td><td><a href="' . get_permalink( $submission->submission_page_id ) . '">' . get_the_title( $submission->submission_page_id ) . '</a></td><td>' . esc_html( date('F j, Y g:i a', strtotime($submission->submitted_at)) ) . '</td></tr>';
            echo '</tbody></table>';
        echo '</div>';

        // Additional Info section
        echo '<div>';
            echo '<h3>Metadata</h3>';
            echo '<table class="widefat fixed striped">';
            echo '<thead><tr><th>Key</th><th>Value</th></tr></thead>';
            echo '<tbody>';
            $metadata = json_decode($submission->metadata, true);
            foreach ($metadata as $key => $value) {
                echo '<tr>';
                echo '<td>' . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . '</td>';
                echo '<td>' . htmlspecialchars($value) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        echo '</div>';

        echo '<div style="margin-top: 20px;">';
            $delete_url = wp_nonce_url(
                admin_url( 'admin.php?page=oom_form_submissions&action=delete&id=' . $submission_id ),
                'delete_submission_' . $submission_id
            );
            echo '<a href="' . esc_url( $delete_url ) . '" style="margin-right: 10px;" class="button button-danger" onclick="return confirm(\'Are you sure you want to move this submission to trash?\');">Move to Trash</a>';
            echo '<a href="' . admin_url( 'admin.php?page=oom_form_submissions' ) . '" class="button">Back to Submissions</a>';
        echo '</div>';
    echo '</div>';
}



/**
 * Form Submission Settings Page
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function oom_form_settings_page() {
    add_submenu_page(
        'oom_form_submissions', // parent menu
        'OOm Form Settings', // Page Title
        'OOm Form Settings', // Menu Title
        'manage_options', // Capability
        'oom_form_settings_view', // Menu Slug
        'oom_form_settings_view_page' // Callback function
    );
}
add_action('admin_menu', 'oom_form_settings_page');
function oom_form_settings_view_page() {
    // Check if the user has permission
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save the option if the form is submitted
    if (isset($_POST['oom_form_save_settings']) && check_admin_referer('oom_form_save_settings_nonce')) {
        update_option('oom_form_encryption_key', sanitize_text_field($_POST['oom_form_encryption_key']));
        echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'oom-form') . '</p></div>';
    }

    // Get the current value of the options
    $oom_form_encryption_key = get_option('oom_form_encryption_key', '');

    ?>
    <div class="wrap">
        <h2><?php esc_html_e('OOm Form Settings', 'oom-form'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('oom_form_save_settings_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <td style="padding: 0;">
                        <h3><label for="oom_form_encryption_key"><?php esc_html_e('Encryption Key', 'oom-form'); ?></label></h3>
                        <input name="oom_form_encryption_key" type="text" id="oom_form_encryption_key" value="<?php echo esc_attr($oom_form_encryption_key); ?>" required class="regular-text" placeholder="<?php esc_attr_e('Enter encryption key', 'oom-form'); ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button(esc_html__('Save Settings', 'oom-form'), 'primary', 'oom_form_save_settings'); ?>
        </form>
    </div>
    <?php
}


/**
 * Handle Delete Submission
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function oom_handle_delete_submission() {
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) ) {
        global $wpdb;

        // Get the submission ID
        $submission_id = intval( $_GET['id'] );

        // Verify the nonce for security
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_submission_' . $submission_id ) ) {
            wp_die( 'Security check failed.' );
        }

        // Define the table name
        $table_name = $wpdb->prefix . 'oom_form_submissions';

        // Delete the submission from the database
        $wpdb->delete(
            $table_name,
            array( 'id' => $submission_id ),
            array( '%d' )
        );

        // Redirect back to the submissions list page with a success message
        wp_redirect( admin_url( 'admin.php?page=oom_form_submissions&deleted=1' ) );
        exit;
    }
}
add_action( 'admin_init', 'oom_handle_delete_submission' );


/**
 * Export to csv
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function oom_export_submissions_to_csv() {
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'export_to_csv' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'oom_form_submissions';
        $submissions = $wpdb->get_results( "SELECT * FROM $table_name" );

        if ( $submissions ) {
            // Set the CSV headers for download
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename=submissions.csv' );

            // Open the output stream for CSV
            $output = fopen( 'php://output', 'w' );

            // Write the header row (all keys except form_id, post_id, oom_form)
            $header_written = false;

            foreach ( $submissions as $submission ) {
                $ec_key = $submission->ec_key;
                $encrypted_data = $submission->submission_data;
                $submission_data = oom_decrypt_form_data($encrypted_data, $ec_key);
                
                // Check if the decrypted data is serialized and unserialize it
                if ( is_string( $submission_data ) && is_serialized( $submission_data ) ) {
                    $submission_data = unserialize( $submission_data );
                }

                // Filter out unwanted fields
                $filtered_data = array_filter($submission_data, function($key) {
                    return $key !== 'form_id' && $key !== 'post_id' && $key !== 'oom_form' && $key !== 'page_id' && $key !== 'country_code';
                }, ARRAY_FILTER_USE_KEY);

                // Ensure all values are strings (convert arrays to strings)
                $formatted_data = array_map(function($value) {
                    if (is_array($value)) {
                        // Convert arrays to a comma-separated string
                        return implode(', ', $value);
                    }
                    return $value;
                }, $filtered_data);

                // Write the CSV header based on keys (if not done already)
                if ( !$header_written ) {
                    fputcsv( $output, array_keys( $formatted_data ) );
                    $header_written = true;
                }

                // Write the row data for each submission
                fputcsv( $output, $formatted_data );
            }

            // Close the output stream
            fclose( $output );
            exit;
        }
    }
}

add_action( 'admin_init', 'oom_export_submissions_to_csv' );

/**
 * Decrypt Form Data
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function oom_decrypt_form_data($a,$b){if(!is_string($a)||!is_string($b)){return false;} $c=$b;$d=base64_decode($a);if($d===false){return false;} $e=explode('::',$d,2);if(count($e)!==2){return false;}list($f,$g)=$e;$h=openssl_decrypt($f,'aes-256-cbc',$c,0,$g);if($h===false){return false;}return $h;}
