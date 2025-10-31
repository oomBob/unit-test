<?php

/**
 * Register Theme Options Page
 * @author oom_cn 
 * @since  1.5.0
 */

add_action('admin_menu', 'oom_add_theme_options_page');

function oom_add_theme_options_page() {
    add_menu_page(
        'OOm Theme Options',     // Page title
        'Theme Options',     // Menu title
        'manage_options',    // Capability
        'theme-options',     // Menu slug
        'oom_theme_options_page_html', // Callback function to display the page content
        site_url().'/wp-content/themes/hello-elementor-child/assets/img/oom-logo.svg' // Icon for the menu item (replace with the desired icon)
    );
}

function oom_theme_options_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['submit'])) {
        update_option('oom_gtm_code', sanitize_text_field($_POST['oom_gtm_code']));
        update_option('oom_table_status', sanitize_text_field($_POST['oom_table_status']));
        update_option('oom_form_status', sanitize_text_field($_POST['oom_form_status']));
        update_option('oom_google_place_api', sanitize_text_field($_POST['oom_google_place_api']));
        update_option('oom_location', sanitize_text_field($_POST['oom_location']));
        update_option('oom_security_deposit', sanitize_text_field($_POST['oom_security_deposit']));
        update_option('oom_advanced_booking_days', sanitize_text_field($_POST['oom_advanced_booking_days']));
        update_option('oom_pickup_dropoff_charge', sanitize_text_field($_POST['oom_pickup_dropoff_charge']));
        
        // Validate and save blockout dates
        $blockout_dates = sanitize_text_field($_POST['oom_blockout_dates']);
        if (!empty($blockout_dates)) {
            // Validate date format (dd-mm-yyyy or mm-dd-yyyy)
            $dates_array = array_map('trim', explode(',', $blockout_dates));
            $valid_dates = array();
            
            foreach ($dates_array as $date) {
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                    $date_parts = explode('-', $date);
                    
                    // Try both dd-mm-yyyy and mm-dd-yyyy formats
                    $day = (int)$date_parts[0];
                    $month = (int)$date_parts[1];
                    $year = (int)$date_parts[2];
                    
                    // Check if it's a valid date in dd-mm-yyyy format
                    if (checkdate($month, $day, $year)) {
                        $valid_dates[] = $date;
                    }
                    // If not, try mm-dd-yyyy format
                    elseif (checkdate($day, $month, $year)) {
                        // Convert to dd-mm-yyyy format for consistency
                        $valid_dates[] = sprintf('%02d-%02d-%04d', $month, $day, $year);
                    }
                }
            }
            
            update_option('oom_blockout_dates', implode(', ', $valid_dates));
        } else {
            update_option('oom_blockout_dates', '');
        }
    }

    $oom_gtm_code = get_option('oom_gtm_code', '');
    $oom_table_status = get_option('oom_table_status', 'active');
    $oom_form_status = get_option('oom_form_status', 'active');
    $oom_google_place_api = get_option('oom_google_place_api', 'AIzaSyAK5x3pdAgDPv-QsfX4SXHtmULjYBW0NIE');
    $oom_location = get_option('oom_location', '205 Braddell Road Blk H Singapore 479401');
    $oom_security_deposit = get_option('oom_security_deposit', '500.00');
    $oom_advanced_booking_days = get_option('oom_advanced_booking_days', '2');
    $oom_pickup_dropoff_charge = get_option('oom_pickup_dropoff_charge', '30.00');
    $oom_blockout_dates = get_option('oom_blockout_dates', '');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field('oom_theme_options_update', 'oom_theme_options_nonce'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_gtm_code">GTM Code</label></h3>
                        <input name="oom_gtm_code" type="text" id="oom_gtm_code" value="<?php echo esc_attr($oom_gtm_code); ?>" class="regular-text" placeholder="GTM-O3OBZGFD">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_table_status">OOm Table</label></h3>
                        <select name="oom_table_status" id="oom_table_status">
                            <option value="active" <?php selected($oom_table_status, 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($oom_table_status, 'inactive'); ?>>Inactive</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_form_status">OOm Form</label></h3>
                        <select name="oom_form_status" id="oom_form_status">
                            <option value="active" <?php selected($oom_form_status, 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($oom_form_status, 'inactive'); ?>>Inactive</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_google_place_api">Google Place API</label></h3>
                        <input name="oom_google_place_api" type="text" id="oom_google_place_api" value="<?php echo esc_attr($oom_google_place_api); ?>" class="regular-text" placeholder="AIzaSyB...">
                        <p class="description">Enter your Google Places API key for location services.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_location">ComfortDelGro Location</label></h3>
                        <input name="oom_location" type="text" id="oom_location" value="<?php echo esc_attr($oom_location); ?>" class="regular-text" placeholder="205 Braddell Road Blk H Singapore 479401">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_advanced_booking_days">Advanced Booking Days</label></h3>
                        <select name="oom_advanced_booking_days" id="oom_advanced_booking_days">
                            <option value="1" <?php selected($oom_advanced_booking_days, '1'); ?>>1 Day</option>
                            <option value="2" <?php selected($oom_advanced_booking_days, '2'); ?>>2 Days</option>
                            <option value="3" <?php selected($oom_advanced_booking_days, '3'); ?>>3 Days</option>
                            <option value="4" <?php selected($oom_advanced_booking_days, '4'); ?>>4 Days</option>
                            <option value="5" <?php selected($oom_advanced_booking_days, '5'); ?>>5 Days</option>
                            <option value="6" <?php selected($oom_advanced_booking_days, '6'); ?>>6 Days</option>
                            <option value="7" <?php selected($oom_advanced_booking_days, '7'); ?>>7 Days</option>
                        </select>
                        <p class="description">Select the minimum number of days in advance that bookings must be made.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_security_deposit">Security Deposit</label></h3>
                        <input name="oom_security_deposit" type="number" id="oom_security_deposit" value="<?php echo esc_attr($oom_security_deposit); ?>" class="regular-text" step="0.01" min="0" placeholder="500.00">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_pickup_dropoff_charge">Pick up and drop-off service charges</label></h3>
                        <input name="oom_pickup_dropoff_charge" type="number" id="oom_pickup_dropoff_charge" value="<?php echo esc_attr($oom_pickup_dropoff_charge); ?>" class="regular-text" step="0.01" min="0" placeholder="30.00">
                        <p class="description">Enter the charge for pickup and drop-off services (per service).</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;">
                        <h3><label for="oom_blockout_dates">Blockout Dates</label></h3>
                        <textarea name="oom_blockout_dates" id="oom_blockout_dates" rows="4" cols="50" class="large-text" placeholder="25-08-2025, 26-08-2025, 27-08-2025"><?php echo esc_textarea($oom_blockout_dates); ?></textarea>
                        <p class="description">Entry will be dd-mm-yyyy format. Example: 25-08-2025, 26-08-2025. Separate multiple dates with commas. These dates will be blocked out in jQuery date picker fields.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Changes'); ?>
        </form>
    </div>
    <?php
}