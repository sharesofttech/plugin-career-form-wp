<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}


/*
 * Delete Career page
 */
$career_page_id = absint(
    get_option( 'career_form_page_id', 0 )
);

if ( $career_page_id ) {

    wp_delete_post(
        $career_page_id,
        true
    );
}


/*
 * Delete plugin settings/data
 */
delete_option( 'career_designations' );
delete_option( 'career_applications' );
delete_option( 'career_custom_fields' );
delete_option( 'career_field_order' );
delete_option( 'career_form_shortcode' );
delete_option( 'career_mail_settings' );
delete_option( 'career_core_fields' );
delete_option( 'career_form_page_id' );
delete_option( 'career' );