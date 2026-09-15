<?php

/**
 * admin-post.php action handlers:
 * submit application, delete application, bulk delete,
 * toggle core field, save/delete custom field.
 *
 * @package Career_Form
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action(
    'admin_post_submit_career_application',
    'career_submit_application'
);

add_action(
    'admin_post_nopriv_submit_career_application',
    'career_submit_application'
);

function career_submit_application()
{
    $core_fields = career_get_core_fields();

    $name = sanitize_text_field(
        $_POST['name'] ?? ''
    );

    $email = sanitize_email(
        $_POST['email'] ?? ''
    );

    /*
 * =========================================================
 * 7 DAYS EMAIL RESTRICTION
 * =========================================================
 */

    $applications = get_option(
        'career_applications',
        array()
    );

    $now = current_time('timestamp');

    foreach ($applications as $application) {

        $old_email = strtolower(
            trim($application['email'] ?? '')
        );

        $new_email = strtolower(
            trim($email)
        );

        if (
            $old_email === $new_email &&
            ! empty($application['date'])
        ) {

            $last_applied = strtotime(
                $application['date']
            );

            if (
                $last_applied &&
                ($now - $last_applied) < (7 * DAY_IN_SECONDS)
            ) {

                $remaining_days = ceil(
                    (
                        (7 * DAY_IN_SECONDS)
                        -
                        ($now - $last_applied)
                    ) / DAY_IN_SECONDS
                );

                wp_die(
                    'You have already applied using this email address. '
                        . 'Please try again after '
                        . $remaining_days
                        . ' day(s).'
                );
            }
        }
    }

    $qualification = sanitize_text_field(
        $_POST['qualification'] ?? ''
    );

    $contact = sanitize_text_field(
        $_POST['contact'] ?? ''
    );

    $designation = sanitize_text_field(
        $_POST['designation'] ?? ''
    );

    $experience = sanitize_text_field(
        $_POST['experience'] ?? ''
    );

    /*
     * Only validate/require a core field
     * if the admin has kept it enabled.
     */

    if (
        ($core_fields['name']['enabled'] && empty($name)) ||
        ($core_fields['email']['enabled'] && empty($email)) ||
        ($core_fields['qualification']['enabled'] && empty($qualification)) ||
        ($core_fields['contact']['enabled'] && empty($contact)) ||
        ($core_fields['designation']['enabled'] && empty($designation)) ||
        ($core_fields['experience']['enabled'] && empty($experience)) 
        // ($core_fields['resume']['enabled'] && empty($_FILES['resume']['name']))
    ) {
        wp_die('All fields are required.');
    }

    $resume_url = '';
    if (
        $core_fields['resume']['enabled'] &&
        !empty($_FILES['resume']['name'])
    ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // wp_handle_upload() rejects the request outright if the
        // upload had any error (e.g. exceeds upload_max_filesize).
        if (
            isset($_FILES['resume']['error']) &&
            $_FILES['resume']['error'] !== UPLOAD_ERR_OK
        ) {
            wp_die('There was a problem uploading your resume. Please try a smaller file.');
        }

        $upload = wp_handle_upload(
            $_FILES['resume'],
            array(
                'test_form' => false,

                'mimes' => array(
                    'pdf'  => 'application/pdf',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ),
            )
        );

        if (
            empty($upload['error']) &&
            ! empty($upload['url'])
        ) {
            $resume_url = esc_url_raw(
                $upload['url']
            );
        } else {
            // wp_handle_upload() failed (bad mime, disk error, etc).
            wp_die(
                'Resume upload failed: ' .
                    esc_html($upload['error'] ?? 'unknown error')
            );
        }
    }

    // Process custom (admin-added) fields.

    $custom_values  = array();
    $custom_fields  = career_get_enabled_custom_fields();

    foreach ($custom_fields as $field) {

        $post_key = 'custom_' . $field['name'];

        $raw_value = $_POST[$post_key] ?? '';

        if ($field['type'] === 'textarea') {

            $value = sanitize_textarea_field($raw_value);
        } elseif ($field['type'] === 'email') {

            $value = sanitize_email($raw_value);
        } elseif ($field['type'] === 'checkbox') {

            $value = ! empty($raw_value) ? 'Yes' : 'No';
        } else {

            $value = sanitize_text_field($raw_value);
        }

        if (
            ! empty($field['required']) &&
            $field['type'] !== 'checkbox' &&
            $value === ''
        ) {
            wp_die('All fields are required.');
        }

        $custom_values[$field['name']] = array(
            'label' => $field['label'],
            'value' => $value,
        );
    }

    // $applications = get_option(
    //     'career_applications',
    //     array()
    // );

    $new_application = array(

        'name' => $name,

        'email' => $email,

        'qualification' => $qualification,

        'contact' => $contact,

        'designation' => $designation,

        'experience' => $experience,

        'resume' => $resume_url,

        'custom_fields' => $custom_values,

        'date' => current_time('mysql'),

    );

    $applications[] = $new_application;

    update_option(
        'career_applications',
        $applications,
        false
    );

    // Send the auto-response mail to the applicant's email
    // (from the address configured under Career > Mail Settings).
    $mail_result = career_send_application_response_mail($new_application);

    // Send the separate admin/HR copy, in its own format, if enabled.
    $admin_mail_result = career_send_admin_copy_mail($new_application);

    /*
     * TEMPORARY DEBUG BLOCK — REMOVE AFTER YOU FIND THE ISSUE.
     * Submit the form with ?career_debug=1 added to the form
     * page's URL (e.g. https://yoursite.com/career/?career_debug=1)
     * and this will print everything on screen instead of
     * redirecting, so you can see exactly what happened.
     */
    if (isset($_GET['career_debug']) || isset($_POST['career_debug'])) {
        echo '<pre>';
        echo "SAVED APPLICATION:\n";
        print_r($new_application);

        echo "\nMAIL SETTINGS (Career > Mail Settings):\n";
        print_r(career_get_mail_settings());

        echo "\nwp_mail() RETURNED (applicant mail): ";
        var_dump($mail_result);

        echo "\nwp_mail() RETURNED (admin copy mail): ";
        var_dump($admin_mail_result);

        echo "\nNOTE: wp_mail() returning true only means WordPress\n";
        echo "handed the mail to PHP's mail() function successfully —\n";
        echo "it does NOT mean the mail actually reached the inbox.\n";
        echo "If this says true but you still got nothing, check your\n";
        echo "hosting mail logs / spam folder, or check wp-content/debug.log\n";
        echo "for a 'CAREER MAIL FAILED:' line (enable WP_DEBUG_LOG first).\n";
        echo '</pre>';
        exit;
    }

    $redirect = wp_get_referer();

    if (! $redirect) {
        $redirect = home_url('/career/');
    }

    $redirect = add_query_arg(
        'career_submitted',
        'success',
        $redirect
    );

    wp_safe_redirect($redirect);
    exit;
}

add_action(
    'admin_post_delete_career_application',
    'career_delete_application'
);

function career_delete_application()
{
    if (
        ! isset($_POST['application_index']) ||
        ! isset($_POST['career_delete_nonce'])
    ) {
        wp_die('Invalid request.');
    }

    $index = absint($_POST['application_index']);

    if (
        ! wp_verify_nonce(
            $_POST['career_delete_nonce'],
            'delete_career_application_' . $index
        )
    ) {
        wp_die('Security check failed.');
    }

    if (! current_user_can('manage_options')) {
        wp_die('You do not have permission to delete this application.');
    }

    $applications = get_option(
        'career_applications',
        array()
    );

    if (isset($applications[$index])) {

        unset($applications[$index]);

        // Re-index array
        $applications = array_values($applications);

        update_option(
            'career_applications',
            $applications,
            false
        );
    }

    wp_safe_redirect(
        admin_url('admin.php?page=career-applications')
    );

    exit;
}

/* =========================================================
   BULK DELETE CAREER APPLICATIONS
   ========================================================= */

add_action(
    'admin_post_delete_all_career_applications',
    'career_delete_all_applications'
);

function career_delete_all_applications()
{

    if (! current_user_can('manage_options')) {
        wp_die(
            'You do not have permission to delete applications.'
        );
    }

    if (
        ! isset($_POST['career_bulk_delete_nonce']) ||
        ! wp_verify_nonce(
            $_POST['career_bulk_delete_nonce'],
            'career_bulk_delete_applications'
        )
    ) {
        wp_die('Security check failed.');
    }

    $selected_indexes = $_POST['application_indices'] ?? array();


    if (
        empty($selected_indexes) ||
        ! is_array($selected_indexes)
    ) {
        wp_safe_redirect(
            admin_url('admin.php?page=career-applications')
        );

        exit;
    }

    $applications = get_option(
        'career_applications',
        array()
    );

    foreach ($selected_indexes as $index) {

        $index = absint($index);

        if (isset($applications[$index])) {

            unset($applications[$index]);
        }
    }

    $applications = array_values($applications);

    update_option(
        'career_applications',
        $applications,
        false
    );

    wp_safe_redirect(
        admin_url('admin.php?page=career-applications')
    );

    exit;
}



/* =========================================================
   FIELD MANAGER — ADMIN ACTIONS
   ========================================================= */

add_action(
    'admin_post_toggle_career_core_field',
    'career_toggle_core_field'
);

function career_toggle_core_field()
{
    if (! current_user_can('manage_options')) {
        wp_die('You do not have permission to do this.');
    }

    $key = sanitize_key($_POST['field_key'] ?? '');

    if (
        empty($key) ||
        ! isset($_POST['career_toggle_field_nonce']) ||
        ! wp_verify_nonce(
            $_POST['career_toggle_field_nonce'],
            'career_toggle_core_field_' . $key
        )
    ) {
        wp_die('Security check failed.');
    }

    $fields = career_get_core_fields();

    if (isset($fields[$key]) && ! $fields[$key]['locked']) {

        $saved = get_option('career_core_fields', array());

        $current_state = isset($saved[$key]['enabled'])
            ? $saved[$key]['enabled']
            : true;

        $saved[$key]['enabled'] = ! $current_state;

        update_option('career_core_fields', $saved);
    }

    wp_safe_redirect(
        admin_url('admin.php?page=career-settings')
    );

    exit;
}

add_action(
    'admin_post_update_career_core_field',
    'career_update_core_field'
);


add_action(
    'admin_post_save_career_custom_field',
    'career_save_custom_field'
);

function career_save_custom_field()
{
    if (! current_user_can('manage_options')) {
        wp_die('You do not have permission to do this.');
    }

    if (
        ! isset($_POST['career_custom_field_nonce']) ||
        ! wp_verify_nonce(
            $_POST['career_custom_field_nonce'],
            'career_save_custom_field'
        )
    ) {
        wp_die('Security check failed.');
    }

    $field_id = sanitize_text_field($_POST['field_id'] ?? '');
    $label    = sanitize_text_field($_POST['field_label'] ?? '');
    $enabled  = isset($_POST['field_enabled']);

    if (empty($label)) {
        wp_die('Field label is required.');
    }

    /*
     * Editing a CORE (built-in) field — field_id looks like
     * "core:qualification". Only label / enabled can change;
     * type, options, required are fixed for core fields.
     */
    if (strpos($field_id, 'core:') === 0) {

        $key = sanitize_key(substr($field_id, 5));

        $core_fields = career_get_core_fields();

        if (
            isset($core_fields[$key]) &&
            ! $core_fields[$key]['locked']
        ) {

            $saved = get_option(
                'career_core_fields',
                array()
            );

            $type = sanitize_key(
                $_POST['field_type'] ?? 'text'
            );

            $allowed_types = array(
                'text',
                'textarea',
                'number',
                'email',
                'date',
                'dropdown',
                'radio',
            );

            if (! in_array($type, $allowed_types, true)) {
                $type = 'text';
            }

            $options = array();

            if (
                $type === 'dropdown' ||
                $type === 'radio'
            ) {

                $raw_options = sanitize_textarea_field(
                    $_POST['field_options'] ?? ''
                );

                $options = array_values(
                    array_filter(
                        array_map(
                            'trim',
                            explode(',', $raw_options)
                        )
                    )
                );

                $options = array_values(
                    array_unique($options)
                );
            }

            $saved[$key]['label']   = $label;
            $saved[$key]['enabled'] = $enabled;
            $saved[$key]['type']    = $type;
            $saved[$key]['options'] = $options;

            update_option(
                'career_core_fields',
                $saved
            );
        }

        wp_safe_redirect(
            admin_url('admin.php?page=career-settings')
        );

        exit;
    }

    // ---- everything below is the ORIGINAL custom-field logic, unchanged ----

    $type     = sanitize_key($_POST['field_type'] ?? 'text');
    $required = isset($_POST['field_required']);

    $allowed_types = array(
        'text',
        'textarea',
        'number',
        'email',
        'date',
        'dropdown',
        'radio',
        'checkbox',
    );

    if (! in_array($type, $allowed_types, true)) {
        $type = 'text';
    }

    $options = array();

    if (in_array($type, array('dropdown', 'radio'), true)) {

        $raw_options = sanitize_textarea_field(
            $_POST['field_options'] ?? ''
        );

        $options = array_values(
            array_filter(
                array_map('trim', explode(',', $raw_options))
            )
        );

        if (empty($options)) {
            wp_die('Please add at least one option for this field type.');
        }
    }

    $custom_fields = get_option('career_custom_fields', array());

    if (empty($field_id)) {

        $field_id = 'field_' . uniqid();

        $slug = sanitize_title($label);
        $slug = $slug ? str_replace('-', '_', $slug) : 'field';

        $existing_slugs = wp_list_pluck($custom_fields, 'name');
        $base_slug      = $slug;
        $suffix         = 1;

        while (in_array($slug, $existing_slugs, true)) {
            $slug = $base_slug . '_' . $suffix;
            $suffix++;
        }

        $custom_fields[] = array(
            'id'       => $field_id,
            'label'    => $label,
            'name'     => $slug,
            'type'     => $type,
            'options'  => $options,
            'required' => $required,
            'enabled'  => $enabled,
        );
    } else {

        foreach ($custom_fields as &$field) {

            if ($field['id'] === $field_id) {

                $field['label']    = $label;
                $field['type']     = $type;
                $field['options']  = $options;
                $field['required'] = $required;
                $field['enabled']  = $enabled;

                break;
            }
        }

        unset($field);
    }

    update_option('career_custom_fields', $custom_fields);

    wp_safe_redirect(
        admin_url('admin.php?page=career-settings')
    );

    exit;
}

add_action(
    'admin_post_delete_career_custom_field',
    'career_delete_custom_field'
);

function career_delete_custom_field()
{
    if (! current_user_can('manage_options')) {
        wp_die('You do not have permission to do this.');
    }

    $field_id = sanitize_text_field($_POST['field_id'] ?? '');

    if (
        empty($field_id) ||
        ! isset($_POST['career_delete_field_nonce']) ||
        ! wp_verify_nonce(
            $_POST['career_delete_field_nonce'],
            'career_delete_custom_field_' . $field_id
        )
    ) {
        wp_die('Security check failed.');
    }

    $custom_fields = get_option('career_custom_fields', array());

    $custom_fields = array_values(
        array_filter(
            $custom_fields,
            function ($field) use ($field_id) {
                return $field['id'] !== $field_id;
            }
        )
    );

    update_option('career_custom_fields', $custom_fields);

    wp_safe_redirect(
        admin_url('admin.php?page=career-settings')
    );

    exit;
}
