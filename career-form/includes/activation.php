<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function career_form_activate() {

    $career_page = get_page_by_path( 'career' );

    if ( ! $career_page ) {

        $page_id = wp_insert_post(
            array(
                'post_title'   => 'Career',
                'post_name'    => 'career',
                'post_content' => '[career_form]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            )
        );

        if ( ! is_wp_error( $page_id ) ) {
            update_option(
                'career_form_page_id',
                $page_id
            );
        }

    } else {

        update_option(
            'career_form_page_id',
            $career_page->ID
        );
    }

    flush_rewrite_rules();
}


function career_form_deactivate() {

    flush_rewrite_rules();
}