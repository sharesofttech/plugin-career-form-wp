<?php
/**
 * Frontend asset loading.
 *
 * @package Career_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('wp_enqueue_scripts', 'career_form_assets');

function career_form_assets()
{

    /*
     * Load Career Form assets only on pages
     * where the [career_form] shortcode exists.
     */
    if (! is_singular()) {
        return;
    }

    global $post;

    if (! $post || ! has_shortcode($post->post_content, 'career_form')) {
        return;
    }

    wp_enqueue_style(
        'career-form-css',
        CAREER_FORM_URL . 'public/css/career-form.css',
        array(),
        CAREER_FORM_VERSION
    );


    wp_enqueue_style(
        'intl-tel-input',
        'https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.2/build/css/intlTelInput.css',
        array(),
        '25.3.2'
    );

    wp_enqueue_script(
        'intl-tel-input',
        'https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.2/build/js/intlTelInput.min.js',
        array(),
        '25.3.2',
        true
    );

    wp_enqueue_script(
        'career-form-js',
        CAREER_FORM_URL . 'public/js/career-form.js',
        array('intl-tel-input'),
        CAREER_FORM_VERSION,
        true
    );
}
