<?php

/**
 * Admin "Mail Settings" — the auto-response email sent to an
 * applicant after they submit the career form.
 *
 * Storage: single option `career_mail_settings`
 *
 * @package Career_Form
 */

if (!defined('ABSPATH')) {
    exit;
}


function career_get_mail_settings()
{
    $defaults = array(

        /* Applicant mail */
        'enabled'    => false,

        'from_email' => get_option('admin_email'),
        'from_name'  => get_bloginfo('name'),

        'subject'    => 'Thank you for your application, {name}!',
        'title'      => 'Application Received',

        'content'    => '<p>Hi {name},</p>'
            . '<p>Thank you for applying for the position of <strong>{designation}</strong>. '
            . 'We have received your application and our team will review it shortly.</p>'
            . '<p>Regards,<br>' . esc_html(get_bloginfo('name')) . '</p>',


        /* Admin / HR copy */
        'admin_copy_enabled' => false,

        'admin_recipient'    => get_option('admin_email'),

        'admin_subject'      => 'New Career Application: {name} — {designation}',

        'admin_title'        => 'New Application Received',

        'admin_content'      => '<p>A new application has been submitted.</p>'
            . '<table cellpadding="4" cellspacing="0" style="border-collapse:collapse;">'
            . '<tr><td><strong>Name</strong></td><td>{name}</td></tr>'
            . '<tr><td><strong>Email</strong></td><td>{email}</td></tr>'
            . '<tr><td><strong>Contact</strong></td><td>{contact}</td></tr>'
            . '<tr><td><strong>Qualification</strong></td><td>{qualification}</td></tr>'
            . '<tr><td><strong>Designation</strong></td><td>{designation}</td></tr>'
            . '<tr><td><strong>Experience</strong></td><td>{experience}</td></tr>'
            . '<tr><td><strong>Resume</strong></td><td>{resume}</td></tr>'
            . '<tr><td><strong>Date</strong></td><td>{date}</td></tr>'
            . '</table>',
    );


    $saved = get_option(
        'career_mail_settings',
        array()
    );


    if (!is_array($saved)) {
        $saved = array();
    }


    return wp_parse_args(
        $saved,
        $defaults
    );
}

add_action(
    'admin_post_save_career_mail_settings',
    'career_save_mail_settings'
);


function career_save_mail_settings()
{
    if (!current_user_can('manage_options')) {
        wp_die(
            'You do not have permission to do this.'
        );
    }


    if (
        !isset($_POST['career_mail_settings_nonce']) ||
        !wp_verify_nonce(
            $_POST['career_mail_settings_nonce'],
            'career_save_mail_settings'
        )
    ) {
        wp_die(
            'Security check failed.'
        );
    }


    $from_email = sanitize_email(
        $_POST['from_email'] ?? ''
    );


    if (empty($from_email)) {
        $from_email = get_option('admin_email');
    }

    $raw_recipients = (string) (
        $_POST['admin_recipient'] ?? ''
    );


    $recipient_list = array_map(
        'trim',
        explode(
            ',',
            $raw_recipients
        )
    );


    $recipient_list = array_filter(
        $recipient_list,
        'is_email'
    );


    $recipient_list = array_map(
        'sanitize_email',
        $recipient_list
    );


    $admin_recipient = !empty($recipient_list)
        ? implode(
            ', ',
            $recipient_list
        )
        : get_option('admin_email');


    $settings = array(

        'enabled' => isset(
            $_POST['mail_enabled']
        ),

        'from_email' => $from_email,

        'from_name' => sanitize_text_field(
            $_POST['from_name'] ?? ''
        ),

        'subject' => sanitize_text_field(
            $_POST['mail_subject'] ?? ''
        ),

        'title' => sanitize_text_field(
            $_POST['mail_title'] ?? ''
        ),

        'content' => wp_kses_post(
            wp_unslash(
                $_POST['mail_content'] ?? ''
            )
        ),

        'admin_copy_enabled' => isset(
            $_POST['admin_copy_enabled']
        ),

        'admin_recipient' => $admin_recipient,

        'admin_subject' => sanitize_text_field(
            $_POST['admin_subject'] ?? ''
        ),

        'admin_title' => sanitize_text_field(
            $_POST['admin_title'] ?? ''
        ),

        'admin_content' => wp_kses_post(
            wp_unslash(
                $_POST['admin_content'] ?? ''
            )
        ),
    );

    update_option(
        'career_mail_settings',
        $settings
    );

    wp_safe_redirect(
        admin_url(
            'admin.php?page=career-mail-settings&updated=1'
        )
    );

    exit;
}


add_action(
    'wp_mail_failed',
    'career_log_mail_failure'
);


function career_log_mail_failure($wp_error)
{
    $message = 'CAREER MAIL FAILED: '
        . print_r(
            $wp_error->get_error_messages(),
            true
        );


    $log_file = WP_CONTENT_DIR
        . '/career-mail-error.log';


    file_put_contents(
        $log_file,
        date('Y-m-d H:i:s')
        . "\n"
        . $message
        . "\n\n",
        FILE_APPEND
    );
}

function career_apply_mail_placeholders(
    $text,
    $application
) {

    $resume = !empty(
        $application['resume']
    )
        ? '<a href="'
            . esc_url(
                $application['resume']
            )
            . '">'
            . esc_html(
                $application['resume']
            )
            . '</a>'
        : 'No resume attached';


    $placeholders = array(

        '{name}' =>
            $application['name'] ?? '',

        '{email}' =>
            $application['email'] ?? '',

        '{qualification}' =>
            $application['qualification'] ?? '',

        '{contact}' =>
            $application['contact'] ?? '',

        '{designation}' =>
            $application['designation'] ?? '',

        '{experience}' =>
            $application['experience'] ?? '',

        '{date}' =>
            $application['date'] ?? '',

        '{resume}' =>
            $resume,
    );


    return strtr(
        $text,
        $placeholders
    );
}

function career_build_mail_body(
    $title,
    $content
) {

    $body = '<div style="
        font-family:Arial,Helvetica,sans-serif;
        font-size:14px;
        line-height:1.6;
        color:#333;
    ">';

    if ($title !== '') {

        $body .= '<h2 style="
            margin:0 0 15px;
        ">'
            . esc_html($title)
            . '</h2>';
    }

    $body .= wpautop(
        $content
    );

    $body .= '</div>';


    return $body;
}


function career_send_application_response_mail(
    $application
) {

    $settings = career_get_mail_settings();


    if (empty($settings['enabled'])) {
        return false;
    }

    $to = $application['email'] ?? '';


    if (
        empty($to) ||
        !is_email($to)
    ) {
        return false;
    }

    $subject = career_apply_mail_placeholders(
        $settings['subject'],
        $application
    );


    $title = career_apply_mail_placeholders(
        $settings['title'],
        $application
    );


    $content = career_apply_mail_placeholders(
        $settings['content'],
        $application
    );


    $body = career_build_mail_body(
        $title,
        $content
    );


    $from_email = !empty(
        $settings['from_email']
    )
        ? $settings['from_email']
        : get_option('admin_email');

    $from_name = !empty(
        $settings['from_name']
    )
        ? $settings['from_name']
        : get_bloginfo('name');


    $headers = array(

        'Content-Type: text/html; charset=UTF-8',

        sprintf(
            'From: %s <%s>',
            $from_name,
            $from_email
        ),
    );


    return wp_mail(
        $to,
        $subject,
        $body,
        $headers
    );
}


function career_send_admin_copy_mail(
    $application
) {

    $settings = career_get_mail_settings();


    if (
        empty(
            $settings['admin_copy_enabled']
        )
    ) {
        return false;
    }

    $to_raw = !empty(
        $settings['admin_recipient']
    )
        ? $settings['admin_recipient']
        : get_option('admin_email');

    $to = array_filter(
        array_map(
            'trim',
            explode(
                ',',
                $to_raw
            )
        ),
        'is_email'
    );


    if (empty($to)) {
        return false;
    }

    $subject = career_apply_mail_placeholders(
        $settings['admin_subject'],
        $application
    );


    $title = career_apply_mail_placeholders(
        $settings['admin_title'],
        $application
    );


    $body = '<div style="
        font-family:Arial,Helvetica,sans-serif;
        font-size:14px;
        line-height:1.5;
        color:#333;
        max-width:700px;
    ">';


    if (!empty($title)) {

        $body .= '<h2 style="
            margin:0 0 20px;
            font-size:22px;
            color:#222;
        ">'
            . esc_html($title)
            . '</h2>';
    }


    $body .= '<p style="
        margin:0 0 15px;
    ">
        A new career application has been submitted.
    </p>';



    $body .= '<table
        cellpadding="0"
        cellspacing="0"
        width="100%"
        style="
            border-collapse:collapse;
            width:100%;
            max-width:700px;
            border:1px solid #ddd;
        "
    >';


    $add_row = function (
        $label,
        $value
    ) use (&$body) {

 
        if (is_array($value)) {

            $value = implode(
                ', ',
                $value
            );
        }


        $body .= '<tr>';


        $body .= '<td style="
            width:35%;
            padding:10px 12px;
            border:1px solid #ddd;
            background:#f7f7f7;
            font-weight:600;
            vertical-align:top;
        ">'
            . esc_html($label)
            . '</td>';


        $body .= '<td style="
            width:65%;
            padding:10px 12px;
            border:1px solid #ddd;
            vertical-align:top;
        ">'
            . esc_html($value)
            . '</td>';

        $body .= '</tr>';
    };



    $add_row(
        'Name',
        $application['name'] ?? ''
    );


    $add_row(
        'Email',
        $application['email'] ?? ''
    );


    $add_row(
        'Qualification',
        $application['qualification'] ?? ''
    );


    $add_row(
        'Contact',
        $application['contact'] ?? ''
    );


    $add_row(
        'Designation',
        $application['designation'] ?? ''
    );


    $add_row(
        'Experience',
        $application['experience'] ?? ''
    );


    $add_row(
        'Date',
        $application['date'] ?? ''
    );



    if (
        !empty(
            $application['custom_fields']
        ) &&
        is_array(
            $application['custom_fields']
        )
    ) {

        foreach (
            $application['custom_fields']
            as $custom_field
        ) {

            $custom_label =
                $custom_field['label'] ?? '';


            $custom_value =
                $custom_field['value'] ?? '';


            if ($custom_label === '') {
                continue;
            }


            if (is_array($custom_value)) {

                $custom_value = implode(
                    ', ',
                    $custom_value
                );
            }


            $add_row(
                $custom_label,
                $custom_value
            );
        }
    }


    $body .= '</table>';



    if (!empty($application['resume'])) {

        $body .= '<p style="
            margin:20px 0 0;
            padding:12px;
            background:#f7f7f7;
            border:1px solid #ddd;
        ">
            <strong>Resume:</strong>
            Attached to this email.
        </p>';

    } else {

        $body .= '<p style="
            margin:20px 0 0;
            padding:12px;
            background:#fff8e5;
            border:1px solid #ead9a6;
        ">
            <strong>Resume:</strong>
            No resume was attached.
        </p>';
    }


    $body .= '</div>';


    $from_email = !empty(
        $settings['from_email']
    )
        ? $settings['from_email']
        : get_option('admin_email');


    $from_name = !empty(
        $settings['from_name']
    )
        ? $settings['from_name']
        : get_bloginfo('name');


    $headers = array(

        'Content-Type: text/html; charset=UTF-8',

        sprintf(
            'From: %s <%s>',
            $from_name,
            $from_email
        ),
    );


    $attachments = array();


    if (!empty($application['resume'])) {

        $upload_dir = wp_upload_dir();


        $resume_url =
            $application['resume'];


        $uploads_url =
            trailingslashit(
                $upload_dir['baseurl']
            );


        if (
            strpos(
                $resume_url,
                $uploads_url
            ) === 0
        ) {

            $relative_path = ltrim(
                str_replace(
                    $uploads_url,
                    '',
                    $resume_url
                ),
                '/'
            );


            $resume_path =
                trailingslashit(
                    $upload_dir['basedir']
                )
                . $relative_path;

            if (
                file_exists($resume_path) &&
                is_readable($resume_path)
            ) {

                $attachments[] =
                    $resume_path;
            }
        }
    }


    return wp_mail(
        $to,
        $subject,
        $body,
        $headers,
        $attachments
    );
}