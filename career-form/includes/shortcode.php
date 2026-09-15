<?php

/**
 * Frontend [career_form] shortcode.
 *
 * @package Career_Form
 */

if (! defined('ABSPATH')) {
    exit;
}

add_shortcode('career_form', 'career_form_shortcode');

function career_form_shortcode()
{
    $core_fields = career_get_core_fields();
    $custom_fields = career_get_custom_fields();

    /*
     * Build lookup maps so the saved drag/drop order can
     * control the actual frontend form order.
     */
    $custom_by_id = array();

    foreach ($custom_fields as $field) {
        if (!empty($field['id'])) {
            $custom_by_id[$field['id']] = $field;
        }
    }

    $field_order = career_get_field_order();

    ob_start();
?>

    <?php if (isset($_GET['career_submitted']) && $_GET['career_submitted'] === 'success') : ?>

        <div id="career_success_message" class="career_success_message">
            Your application submitted successfully!...Our Team will contact you later.
        </div>

    <?php endif; ?>

    <div class="career_form_container">
        <h1> JOB APPLICATIONS </h1>

        <form id="career_form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" novalidate>

            <input type="hidden" name="action" value="submit_career_application">

            <?php if (isset($_GET['career_debug'])) : ?>
                <input type="hidden" name="career_debug" value="1">
            <?php endif; ?>

            <?php
            /*
             * Render every enabled field in the exact order saved
             * from Manage Form Fields.
             */
            foreach ($field_order as $field_id) :

                /* ---------------- CORE FIELD ---------------- */
                if (isset($core_fields[$field_id])) :

                    $field = $core_fields[$field_id];

                    if (empty($field['enabled'])) {
                        continue;
                    }

                    $label = $field['label'];
            ?>

                    <?php if ($field_id === 'name') : ?>
                        <div class="career_form_group">
                            <label for="career_name">
                                <?php echo esc_html($label); ?> <span class="career_required">*</span> :
                            </label><br>
                            <input type="text" id="career_name" name="name" placeholder="Enter <?php echo esc_attr($label); ?>" required>
                        </div>

                    <?php elseif ($field_id === 'email') : ?>
                        <div class="career_form_group">
                            <label for="career_email">
                                <?php echo esc_html($label); ?> <span class="career_required">*</span> :
                            </label><br>
                            <input type="email" id="career_email" name="email" placeholder="Enter <?php echo esc_attr($label); ?>" required>
                        </div>

                    <?php elseif ($field_id === 'qualification') : ?>
                        <div class="career_form_group">
                            <label for="career_qualification">
                                <?php echo esc_html($label); ?> <span class="career_required">*</span> :
                            </label><br>
                            <select
                                name="qualification"
                                id="career_qualification"
                                required>

                                <option value="">
                                    Select <?php echo esc_html($label); ?>
                                </option>

                                <?php
                                $qualification_options =
                                    ! empty($field['options']) &&
                                    is_array($field['options'])
                                    ? $field['options']
                                    : array();

                                usort(
                                    $qualification_options,
                                    'strnatcasecmp'
                                );

                                foreach ($qualification_options as $option) :
                                ?>

                                    <option value="<?php echo esc_attr($option); ?>">
                                        <?php echo esc_html($option); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                    <?php elseif ($field_id === 'contact') : ?>
                        <div class="career_form_group">
                            <label for="career_contact">
                                <?php echo esc_html($label); ?> <span class="career_required">*</span> :
                            </label><br>
                            <input type="tel" id="career_contact" name="contact" autocomplete="tel" inputmode="numeric" placeholder="Enter <?php echo esc_attr($label); ?>" required>
                        </div>

                    <?php elseif ($field_id === 'designation') : ?>
                        <div class="career_form_group">
                            <label for="career_designation">
                                <?php echo esc_html($label); ?> <span class="career_required">*</span> :
                            </label><br>
                            <select
                                name="designation"
                                id="career_designation"
                                required>

                                <option value="">
                                    Select <?php echo esc_html($label); ?>
                                </option>

                                <?php
                                $designation_options =
                                    ! empty($field['options']) &&
                                    is_array($field['options'])
                                    ? $field['options']
                                    : array();

                                usort(
                                    $designation_options,
                                    'strnatcasecmp'
                                );

                                foreach ($designation_options as $option) :
                                ?>

                                    <option value="<?php echo esc_attr($option); ?>">
                                        <?php echo esc_html($option); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                    <?php elseif ($field_id === 'experience') : ?>
                        <div class="career_form_group">
                            <label for="career_experience">
                                <?php echo esc_html($label); ?> <span class="career_required">*</span> :
                            </label><br>
                            <select name="experience" id="career_experience" required>
                                <option value="">
                                    Select <?php echo esc_html($label); ?>
                                </option>
                                <option value="Fresher">Fresher</option>
                                <option value="0 - 1 Years">0 - 1 Years</option>
                                <option value="1 - 3 Years">1 - 3 Years</option>
                                <option value="3 - 5 Years">3 - 5 Years</option>
                                <option value="5+ Years">5+ Years</option>
                            </select>
                        </div>

                    <?php elseif ($field_id === 'resume') : ?>
                        <div class="career_form_group career_resume_group">
                            <label for="career_resume">
                                <?php echo esc_html($label); ?> <span>(Optional)</span>
                                <span>
                                    (Allow only pdf, doc, docx format for file upload)
                                </span>
                            </label><br>

                            <div class="career_upload_box" id="career_upload_box">
                                <div class="career_upload_content">
                                    <strong>Drag &amp; Drop Files Here</strong><br>
                                    <span class="career_upload_or">or</span><br>
                                    <label for="career_resume" class="career_browse_btn">
                                        Browse Files
                                    </label>
                                    <small id="career_file_count">0 of 1</small>
                                </div>

                                <input type="file" id="career_resume" name="resume" accept=".pdf,.doc,.docx" hidden>
                            </div>

                            <div class="career_selected_file" id="career_selected_file"></div>
                        </div>
                    <?php endif; ?>

                <?php
                /* ---------------- CUSTOM FIELD ---------------- */
                elseif (isset($custom_by_id[$field_id])) :

                    $field = $custom_by_id[$field_id];

                    if (empty($field['enabled'])) {
                        continue;
                    }

                    $name       = sanitize_key($field['name']);
                    $label      = $field['label'];
                    $type       = $field['type'];
                    $options    = !empty($field['options']) && is_array($field['options'])
                        ? $field['options']
                        : array();
                    $required   = !empty($field['required']);
                    $input_name = 'custom_' . $name;
                    $input_id   = 'career_custom_' . $name;
                ?>

                    <div class="career_form_group">

                        <label for="<?php echo esc_attr($input_id); ?>">
                            <?php echo esc_html($label); ?>
                            <?php if ($required) : ?>
                                <span class="career_required">*</span>
                            <?php endif; ?>
                            :
                        </label><br>

                        <?php if ($type === 'textarea') : ?>

                            <textarea
                                id="<?php echo esc_attr($input_id); ?>"
                                name="<?php echo esc_attr($input_name); ?>"
                                rows="4"
                                placeholder="Enter <?php echo esc_attr($label); ?>"
                                <?php echo $required ? 'required' : ''; ?>></textarea>

                        <?php elseif ($type === 'dropdown') : ?>

                            <select
                                id="<?php echo esc_attr($input_id); ?>"
                                name="<?php echo esc_attr($input_name); ?>"
                                <?php echo $required ? 'required' : ''; ?>>

                                <option value="">
                                    Select <?php echo esc_html($label); ?>
                                </option>

                                <?php foreach ($options as $option) : ?>
                                    <option value="<?php echo esc_attr($option); ?>">
                                        <?php echo esc_html($option); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($type === 'radio') : ?>

                            <div class="career_custom_radio_group">
                                <?php foreach ($options as $option_index => $option) : ?>
                                    <label class="career_custom_radio_option">
                                        <input
                                            type="radio"
                                            name="<?php echo esc_attr($input_name); ?>"
                                            value="<?php echo esc_attr($option); ?>"
                                            <?php echo ($option_index === 0 && $required) ? 'required' : ''; ?>>
                                        <?php echo esc_html($option); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($type === 'checkbox') : ?>

                            <label class="career_custom_checkbox_option">
                                <input
                                    type="checkbox"
                                    id="<?php echo esc_attr($input_id); ?>"
                                    name="<?php echo esc_attr($input_name); ?>"
                                    value="Yes">
                                Yes
                            </label>

                        <?php elseif ($type === 'number') : ?>

                            <input
                                type="number"
                                id="<?php echo esc_attr($input_id); ?>"
                                name="<?php echo esc_attr($input_name); ?>"
                                placeholder="Enter <?php echo esc_attr($label); ?>"
                                <?php echo $required ? 'required' : ''; ?>>

                        <?php elseif ($type === 'email') : ?>

                            <input
                                type="email"
                                id="<?php echo esc_attr($input_id); ?>"
                                name="<?php echo esc_attr($input_name); ?>"
                                placeholder="Enter <?php echo esc_attr($label); ?>"
                                <?php echo $required ? 'required' : ''; ?>>

                        <?php elseif ($type === 'date') : ?>

                            <input
                                type="date"
                                id="<?php echo esc_attr($input_id); ?>"
                                name="<?php echo esc_attr($input_name); ?>"
                                <?php echo $required ? 'required' : ''; ?>>

                        <?php else : ?>

                            <input
                                type="text"
                                id="<?php echo esc_attr($input_id); ?>"
                                name="<?php echo esc_attr($input_name); ?>"
                                placeholder="Enter <?php echo esc_attr($label); ?>"
                                <?php echo $required ? 'required' : ''; ?>>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            <?php endforeach; ?>

            <div class="career_form_action">
                <button type="submit">
                    Apply Now
                </button>
            </div>

        </form>
    </div>

<?php
    return ob_get_clean();
}
