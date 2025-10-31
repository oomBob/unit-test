<?php
/**
 * Database for form submission
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */
function oom_form_create_database_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'oom_form_submissions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
		ec_key varchar(255) NOT NULL,
        form_id varchar(255) NOT NULL,
        form_name varchar(255) NOT NULL,
        submission_data longtext NOT NULL,
        metadata longtext NULL,
        submission_page_id mediumint(9) NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('wp_loaded', 'oom_form_create_database_table');
