<?php

/**
 * Admin menu, settings page, and admin-side asset loading.
 *
 * @package Career_Form
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'career_form_admin_menu');
function career_form_admin_menu()
{

    add_menu_page(
        'Career',
        'Career',
        'manage_options',
        'career-settings',
        'career_settings_page',
        'dashicons-businessperson',
        25
    );

    add_submenu_page(
        'career-settings',
        'Applications',
        'Applications',
        'manage_options',
        'career-applications',
        'career_applications_page'
    );

    add_submenu_page(
        'career-settings',
        'Mail Settings',
        'Mail Settings',
        'manage_options',
        'career-mail-settings',
        'career_mail_settings_page'
    );
}

/**
 * "Mail Settings" submenu page — configures the auto-response
 * mail sent to an applicant's email address when they submit
 * the career form. The "From" address here is always static;
 * the "To" address is always whatever the applicant entered.
 */
function career_mail_settings_page()
{
    $settings = career_get_mail_settings();
?>
    <div class="wrap">

        <h1>Mail Settings</h1>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Mail settings saved.</p>
            </div>
        <?php endif; ?>

        <!-- <div class="career_info_box">
            <p>
                Configure the automatic reply sent to an applicant's own
                email address after they submit the
                <strong>[career_form]</strong>, and the separate copy sent
                to your own recipient. Each has its own tab and its own
                format below.
            </p>
            <p class="career_info_note">
                You can use these placeholders inside the Subject, Title, and
                Content fields of either tab — each is replaced with the
                applicant's actual details before sending:
                <code>{name}</code>, <code>{email}</code>,
                <code>{qualification}</code>, <code>{contact}</code>,
                <code>{designation}</code>, <code>{experience}</code>,
                <code>{date}</code>, <code>{resume}</code>
                (renders as a clickable link that opens the resume file
                straight in the browser, or "No resume attached").
            </p>
        </div> -->

        <h2 class="nav-tab-wrapper" id="career_mail_tabs">
            <a href="#career_tab_applicant" class="nav-tab nav-tab-active" data-tab="career_tab_applicant">Applicant Email</a>
            <a href="#career_tab_admin" class="nav-tab" data-tab="career_tab_admin">Admin Copy Email</a>
        </h2>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var tabs = document.querySelectorAll('#career_mail_tabs .nav-tab');
                var panes = document.querySelectorAll('.career_tab_pane');

                function activate(id) {
                    tabs.forEach(function(t) {
                        t.classList.toggle('nav-tab-active', t.dataset.tab === id);
                    });
                    panes.forEach(function(p) {
                        p.style.display = (p.id === id) ? '' : 'none';
                    });

                    if (
                        id === 'career_tab_admin' &&
                        typeof tinymce !== 'undefined' &&
                        tinymce.get('admin_content') === null
                    ) {
                        tinymce.execCommand('mceAddEditor', false, 'admin_content');
                    }
                }

                tabs.forEach(function(t) {
                    t.addEventListener('click', function(e) {
                        e.preventDefault();
                        activate(t.dataset.tab);
                    });
                });

                activate('career_tab_applicant');
            });
        </script>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

            <input type="hidden" name="action" value="save_career_mail_settings">
            <?php wp_nonce_field('career_save_mail_settings', 'career_mail_settings_nonce'); ?>

            <div id="career_tab_applicant" class="career_tab_pane">

                <table class="form-table" role="presentation">

                    <tr>
                        <th scope="row">
                            <label for="mail_enabled">Send Response Mail</label>
                        </th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    id="mail_enabled"
                                    name="mail_enabled"
                                    value="1"
                                    <?php checked(! empty($settings['enabled'])); ?>>
                                Automatically email the applicant after they submit the form
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="from_email">From Email</label>
                        </th>
                        <td>
                            <input
                                type="email"
                                id="from_email"
                                name="from_email"
                                class="regular-text"
                                value="<?php echo esc_attr($settings['from_email']); ?>"
                                required>
                            <p class="description">
                                All response mails are sent from this one address.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="from_name">From Name</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="from_name"
                                name="from_name"
                                class="regular-text"
                                value="<?php echo esc_attr($settings['from_name']); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mail_subject">Subject</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="mail_subject"
                                name="mail_subject"
                                class="regular-text"
                                value="<?php echo esc_attr($settings['subject']); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mail_title">Title</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="mail_title"
                                name="mail_title"
                                class="regular-text"
                                value="<?php echo esc_attr($settings['title']); ?>">
                            <p class="description">
                                Shown as the heading inside the email body.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mail_content">Content</label>
                        </th>
                        <td>
                            <?php
                            wp_editor(
                                $settings['content'],
                                'mail_content',
                                array(
                                    'textarea_name' => 'mail_content',
                                    'media_buttons' => false,
                                    'textarea_rows' => 12,
                                    'teeny'         => false,
                                )
                            );
                            ?>
                        </td>
                    </tr>

                </table>

            </div><!-- /#career_tab_applicant -->

            <div id="career_tab_admin" class="career_tab_pane" style="display:none;">

                <p class="description">
                    A separate copy sent to your own recipient (HR inbox, etc.)
                    in its own format — completely independent of the applicant
                    email in the other tab. You decide the recipient and the
                    format here.
                </p>

                <table class="form-table" role="presentation">

                    <tr>
                        <th scope="row">
                            <label for="admin_copy_enabled">Send Admin Copy</label>
                        </th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    id="admin_copy_enabled"
                                    name="admin_copy_enabled"
                                    value="1"
                                    <?php checked(! empty($settings['admin_copy_enabled'])); ?>>
                                Email the recipient below whenever a new application is submitted
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="admin_recipient">Recipient Email</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="admin_recipient"
                                name="admin_recipient"
                                class="regular-text"
                                placeholder="one@example.com, two@example.com"
                                value="<?php echo esc_attr($settings['admin_recipient']); ?>">
                            <p class="description">
                                One or more email addresses, separated by commas. Defaults to the
                                site's admin email if left blank.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="admin_subject">Subject</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="admin_subject"
                                name="admin_subject"
                                class="regular-text"
                                value="<?php echo esc_attr($settings['admin_subject']); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="admin_title">Title</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="admin_title"
                                name="admin_title"
                                class="regular-text"
                                value="<?php echo esc_attr($settings['admin_title']); ?>">
                            <p class="description">
                                Shown as the heading inside this email's body.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="admin_content">Content</label>
                        </th>
                        <td>
                            <?php
                            wp_editor(
                                $settings['admin_content'],
                                'admin_content',
                                array(
                                    'textarea_name' => 'admin_content',
                                    'media_buttons' => false,
                                    'textarea_rows' => 12,
                                    'teeny'         => false,
                                )
                            );
                            ?>
                            <p class="description">
                                Use <code>{resume}</code> where you want the
                                resume link to appear — it renders as a normal
                                link, so clicking it opens the file directly in
                                the browser tab (view) rather than forcing a
                                download, as long as your server serves PDFs/DOCs
                                inline.
                            </p>
                        </td>
                    </tr>

                </table>

            </div><!-- /#career_tab_admin -->


            <?php submit_button('Save Mail Settings'); ?>

        </form>

    </div>
<?php
}

function career_settings_page()
{

?>
    <div class="wrap">

        <div class="career_info_box">
            <h3>Career Form Plugin Information</h3>

            <p>
                When you activate the plugin, a <strong>Career</strong> page
                will be created automatically with the
                <code>[career_form]</code> shortcode added to it.
            </p>

            <p>
                If you want to change the page name, simply go to
                <strong>Pages → Career</strong>, edit the page title,
                and update it.
            </p>

            <p class="career_info_note">
                <strong>Note:</strong> Please make sure the
                <code>[career_form]</code> shortcode remains inside the page content.
            </p>
        </div>

        <h1>Career Settings</h1>

    </div>

    <?php
    $core_fields_admin   = career_get_core_fields();
    $custom_fields_admin = career_get_custom_fields();

    // Lookup map so custom fields can be rendered in field-order sequence.
    $custom_fields_by_id_admin = array();

    foreach ($custom_fields_admin as $custom_field_admin) {
        if (!empty($custom_field_admin['id'])) {
            $custom_fields_by_id_admin[$custom_field_admin['id']] = $custom_field_admin;
        }
    }

    // Render the table in the exact order saved from drag & drop,
    // so the row order survives a page refresh instead of
    // resetting back to "core fields first, then custom fields".
    $field_order_admin = career_get_field_order();
    ?>

    <div class="career_fields_section">

        <div class="career_fields_header">
            <h2>Manage Form Fields</h2>

            <?php
            $custom_field_count = count(
                $custom_fields_admin
            );

            $custom_limit_reached =
                $custom_field_count >= 6;
            ?>

            <button
                type="button"
                id="career_add_field_btn"
                class="button button-primary"
                <?php echo $custom_limit_reached ? 'disabled' : ''; ?>
                title="<?php echo $custom_limit_reached
                            ? 'Maximum of 5 custom fields allowed.'
                            : 'Add a new custom field.'; ?>">

                + Add New Field

            </button>
        </div>

        <div class="career_fields_table_wrapper">

            <table class="widefat striped career_fields_table">

                <thead>
                    <tr>
                        <th style="width:30px;"></th>
                        <th>Label</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th>Status</th>
                        <th style="width:220px;">Action</th>
                    </tr>
                </thead>

                <tbody id="career_fields_table_body">

                    <?php foreach ($field_order_admin as $field_key) : ?>

                        <?php if (isset($core_fields_admin[$field_key])) : ?>

                            <?php $field = $core_fields_admin[$field_key]; ?>

                            <tr draggable="true" data-field-id="<?php echo esc_attr($field_key); ?>">
                                <td>
                                    <span class="career_drag_handle dashicons dashicons-move" title="Drag to reorder"></span>
                                </td>
                                <td><?php echo esc_html($field['label']); ?></td>
                                <td>Built-in</td>
                                <td>Yes</td>
                                <td>
                                    <?php if ($field['enabled']) : ?>
                                        <span class="career_field_badge career_field_enabled">Enabled</span>
                                    <?php else : ?>
                                        <span class="career_field_badge career_field_disabled">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($field['locked']) : ?>

                                        <span class="career_field_badge career_field_locked">Locked</span>

                                    <?php else : ?>

                                        <button
                                            type="button"
                                            class="career_icon_btn career_edit_field_btn"
                                            data-id="core:<?php echo esc_attr($field_key); ?>"
                                            data-label="<?php echo esc_attr($field['label']); ?>"
                                            data-type="<?php echo esc_attr($field['type'] ?? 'text'); ?>"
                                            data-options="<?php echo esc_attr(
                                                                implode(', ', $field['options'] ?? array())
                                                            ); ?>"
                                            data-required="0"
                                            data-enabled="<?php echo $field['enabled'] ? '1' : '0'; ?>"
                                            title="Edit">

                                            <span class="dashicons dashicons-edit"></span>

                                        </button>

                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_career_core_field">
                                            <input type="hidden" name="field_key" value="<?php echo esc_attr($field_key); ?>">
                                            <?php wp_nonce_field('career_toggle_core_field_' . $field_key, 'career_toggle_field_nonce'); ?>

                                            <button
                                                type="submit"
                                                class="career_icon_btn <?php echo $field['enabled'] ? 'career_icon_btn_danger' : ''; ?>"
                                                title="<?php echo $field['enabled'] ? 'Disable' : 'Enable'; ?>">
                                                <span class="dashicons <?php echo $field['enabled'] ? 'dashicons-hidden' : 'dashicons-visibility'; ?>"></span>
                                            </button>
                                        </form>

                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php elseif (isset($custom_fields_by_id_admin[$field_key])) : ?>

                            <?php $field = $custom_fields_by_id_admin[$field_key]; ?>

                            <tr draggable="true" data-field-id="<?php echo esc_attr($field['id']); ?>">
                                <td>
                                    <span class="career_drag_handle dashicons dashicons-move" title="Drag to reorder"></span>
                                </td>
                                <td><?php echo esc_html($field['label']); ?></td>
                                <td><?php echo esc_html(ucfirst($field['type'])); ?></td>
                                <td><?php echo ! empty($field['required']) ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <?php if (! empty($field['enabled'])) : ?>
                                        <span class="career_field_badge career_field_enabled">Enabled</span>
                                    <?php else : ?>
                                        <span class="career_field_badge career_field_disabled">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="career_icon_btn career_edit_field_btn"
                                        data-id="<?php echo esc_attr($field['id']); ?>"
                                        data-label="<?php echo esc_attr($field['label']); ?>"
                                        data-type="<?php echo esc_attr($field['type']); ?>"
                                        data-options="<?php echo esc_attr(implode(', ', $field['options'])); ?>"
                                        data-required="<?php echo ! empty($field['required']) ? '1' : '0'; ?>"
                                        data-enabled="<?php echo ! empty($field['enabled']) ? '1' : '0'; ?>"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;" onsubmit="return confirm('Delete this field permanently?');">
                                        <input type="hidden" name="action" value="delete_career_custom_field">
                                        <input type="hidden" name="field_id" value="<?php echo esc_attr($field['id']); ?>">
                                        <?php wp_nonce_field('career_delete_custom_field_' . $field['id'], 'career_delete_field_nonce'); ?>

                                        <button type="submit" class="career_icon_btn career_icon_btn_danger" title="Delete">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        <?php endif; ?>

                    <?php endforeach; ?>

                    <?php if (empty($custom_fields_admin)) : ?>

                        <tr>
                            <td colspan="6" class="career_no_custom_fields">
                                No custom fields added yet. Click “+ Add New Field” to create one.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>

        </div>

    </div>

    <!-- Add / Edit Field Modal -->
    <div id="career_field_modal_overlay" class="career_field_modal_overlay">

        <div class="career_field_modal">

            <div class="career_field_modal_header">
                <h2 id="career_field_modal_title">Add New Field</h2>
                <button type="button" id="career_field_modal_close" class="career_field_modal_close">&times;</button>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                <input type="hidden" name="action" value="save_career_custom_field">
                <input type="hidden" name="field_id" id="career_field_id" value="">
                <?php wp_nonce_field('career_save_custom_field', 'career_custom_field_nonce'); ?>

                <div class="career_field_modal_body">

                    <div class="career_field_form_group">
                        <label for="career_field_label">Field Label</label>
                        <input type="text" id="career_field_label" name="field_label" placeholder="e.g. Address, Age, Expected Salary" required>
                    </div>

                    <div class="career_field_form_group">
                        <label for="career_field_type">Field Type</label>
                        <select id="career_field_type" name="field_type">
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="email">Email</option>
                            <option value="date">Date</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="radio">Radio Buttons</option>
                            <option value="checkbox">Checkbox (Yes/No)</option>
                        </select>
                    </div>

                    <div class="career_field_form_group" id="career_field_options_group" style="display:none;">
                        <label for="career_field_options">Options (comma separated)</label>
                        <textarea id="career_field_options" name="field_options" rows="3" placeholder="e.g. Option A, Option B, Option C"></textarea>
                    </div>

                    <div class="career_field_form_group career_preset_options_group" id="career_preset_options_group" style="display:none;">
                        <label>Pick from predefined list</label>
                        <div class="career_preset_options_body" id="career_preset_options_body"></div>
                    </div>

                    <div class="career_field_form_group career_field_checkbox_row">
                        <label>
                            <input type="checkbox" id="career_field_required" name="field_required" value="1">
                            Required field
                        </label>

                        <label>
                            <input type="checkbox" id="career_field_enabled" name="field_enabled" value="1" checked>
                            Show on form (enabled)
                        </label>
                    </div>

                </div>

                <div class="career_field_modal_footer">
                    <button type="button" id="career_field_modal_cancel" class="button button-secondary">Cancel</button>
                    <button type="submit" class="button button-primary">Save Field</button>
                </div>

            </form>

        </div>

    </div>
<?php
}

/**
 * "Applications" submenu page — lists submitted career
 * applications with select-all / delete-all / delete actions.
 */
function career_applications_page()
{

    $applications = get_option(
        'career_applications',
        array()
    );

?>
    <div class="wrap">

        <div class="career_applications_section">

            <div class="career_applications_header">

                <h2>Applications</h2>

                <?php if (! empty($applications)) : ?>

                    <div class="career_bulk_actions">

                        <button
                            type="button"
                            id="career_select_all"
                            class="button button-primary">
                            Select All
                        </button>

                        <button
                            type="button"
                            id="career_delete_all"
                            class="button button-secondary"
                            style="display:none;">
                            Delete All
                        </button>

                    </div>

                <?php endif; ?>

            </div>


            <?php if (! empty($applications)) : ?>

                <div class="career_applications_table_wrapper">

                    <?php $custom_fields_table = career_get_custom_fields(); ?>

                    <table class="widefat striped">

                        <thead>
                            <tr>
                                <th style="width:40px;">Select</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Qualification</th>
                                <th>Contact</th>
                                <th>Designation</th>
                                <th>Experience</th>
                                <th>Resume</th>
                                <?php foreach ($custom_fields_table as $custom_col) : ?>
                                    <th><?php echo esc_html($custom_col['label']); ?></th>
                                <?php endforeach; ?>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php foreach ($applications as $index => $application): ?>
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            class="career_application_checkbox"
                                            value="<?php echo esc_attr($index); ?>">
                                    </td>
                                    <td>
                                        <?php
                                        echo esc_html(
                                            $application['name'] ?? ''
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo esc_html(
                                            $application['email'] ?? ''
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo esc_html(
                                            $application['qualification'] ?? ''
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo esc_html(
                                            $application['contact'] ?? ''
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo esc_html(
                                            $application['designation'] ?? ''
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo esc_html(
                                            $application['experience'] ?? ''
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <?php
                                        $resume = $application['resume'] ?? '';
                                        ?>

                                        <?php if ($resume) : ?>

                                            <a
                                                href="<?php echo esc_url($resume); ?>"
                                                target="_blank"
                                                class="button">
                                                View Resume
                                            </a>

                                        <?php else : ?>
                                            No Resume
                                        <?php endif; ?>
                                    </td>

                                    <?php foreach ($custom_fields_table as $custom_col) : ?>
                                        <td>
                                            <?php
                                            echo esc_html(
                                                $application['custom_fields'][$custom_col['name']]['value'] ?? ''
                                            );
                                            ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td>
                                        <?php
                                        echo esc_html(
                                            $application['date'] ?? ''
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                                            <input type="hidden" name="action" value="delete_career_application">

                                            <input type="hidden" name="application_index" value="<?php echo esc_attr($index); ?>">

                                            <?php wp_nonce_field(
                                                'delete_career_application_' . $index,
                                                'career_delete_nonce'
                                            ); ?>

                                            <button
                                                type="submit"
                                                onclick="return confirm('Are you sure you want to delete this application?');"
                                                class="button button-secondary">
                                                Delete
                                            </button>

                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else : ?>

                <div class="career_no_applications">
                    <span class="dashicons dashicons-id"></span>
                    <h3>No Applications Yet</h3>
                    <p>
                        Submitted career applications will appear here.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}

add_action('admin_enqueue_scripts', 'career_form_admin_assets');

function career_form_admin_assets($hook)
{
    $career_form_admin_pages = array(
        'toplevel_page_career-settings',
        'career_page_career-applications',
        'career_page_career-mail-settings',
    );

    if (! in_array($hook, $career_form_admin_pages, true)) {
        return;
    }

    wp_enqueue_style(
        'career-form-admin-css',
        CAREER_FORM_URL . 'admin/css/career-form-admin.css',
        array(),
        CAREER_FORM_VERSION
    );

    wp_enqueue_script(
        'career-form-admin-js',
        CAREER_FORM_URL . 'admin/js/career-form-admin.js',
        array(),
        CAREER_FORM_VERSION,
        true
    );

    wp_localize_script(
        'career-form-admin-js',
        'careerFormAdmin',
        array(
            'ajaxUrl'          => esc_url(admin_url('admin-post.php')),
            'bulkDeleteNonce'  => wp_create_nonce('career_bulk_delete_applications'),
            'fieldOrderNonce'  => wp_create_nonce('career_save_field_order'),
            'presetCatalogs'   => career_get_field_preset_catalogs(),
        )
    );
}

/**
 * Save the drag-and-drop field order (core + custom fields combined).
 *
 * Reads 'field_order[]' — an array of field keys/ids in the order
 * the admin dragged them into — and stores it so it can be used
 * later to render the form / table in that order.
 */
add_action('admin_post_save_career_field_order', 'career_save_field_order');
function career_save_field_order()
{

    if (
        ! isset($_POST['career_field_order_nonce']) ||
        ! wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['career_field_order_nonce'])),
            'career_save_field_order'
        )
    ) {
        wp_send_json_error('Security check failed.', 403);
    }

    if (! current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized.', 403);
    }

    $order = isset($_POST['field_order'])
        ? array_map('sanitize_text_field', (array) wp_unslash($_POST['field_order']))
        : array();

    update_option('career_field_order', $order);

    wp_send_json_success(array('order' => $order));
}
