<?php

/**
 * Form Field Manager — core + custom fields.
 *
 * @package Career_Form
 */

if (! defined('ABSPATH')) {
    exit;
}

//    FORM FIELD MANAGER — CORE + CUSTOM FIELDS

function career_get_core_fields()
{
    $defaults = array(
        'name' => array(
            'label'   => 'Name',
            'enabled' => true,
            'locked'  => true,
            'type'    => 'text',
            'options' => array(),
        ),

        'email' => array(
            'label'   => 'Email',
            'enabled' => true,
            'locked'  => true,
            'type'    => 'email',
            'options' => array(),
        ),

        'contact' => array(
            'label'   => 'Contact Number',
            'enabled' => true,
            'locked'  => true,
            'type'    => 'text',
            'options' => array(),
        ),

        'qualification' => array(
            'label'   => 'Qualification',
            'enabled' => true,
            'locked'  => false,
            'type'    => 'dropdown',
            'options' => array(
                'Diploma',
                'BE / B.Tech',
                'B.Sc',
                'BCA',
                'MCA',
                'ME / M.Tech',
                'MBA',
                'Other',
            ),
        ),

        'designation' => array(
            'label'   => 'Designation',
            'enabled' => true,
            'locked'  => false,
            'type'    => 'dropdown',
            'options' => array(
                'Frontend Developer',
                'Backend Developer',
                'Full Stack Developer',
                'Software Engineer',
                'QA Engineer',
                'UI/UX Designer',
                'Business Analyst',
                'Other',
            ),
        ),

        'experience' => array(
            'label'   => 'Experience',
            'enabled' => true,
            'locked'  => false,
            'type'    => 'dropdown',
            'options' => array(
                'Fresher',
                '0 - 1 Years',
                '1 - 3 Years',
                '3 - 5 Years',
                '5+ Years',
            ),
        ),

        'resume' => array(
            'label'   => 'Resume',
            'enabled' => true,
            'locked'  => false,
            'type'    => 'file',
            'options' => array(),
        ),
    );

    $saved = get_option('career_core_fields', array());

    foreach ($defaults as $key => $config) {

        if (isset($saved[$key]['enabled'])) {
            $defaults[$key]['enabled'] = (bool) $saved[$key]['enabled'];
        }

        if (
            isset($saved[$key]['label']) &&
            $saved[$key]['label'] !== ''
        ) {
            $defaults[$key]['label'] =
                sanitize_text_field($saved[$key]['label']);
        }

        if (isset($core_fixed_types[$key])) {

            $defaults[$key]['type'] =
                $core_fixed_types[$key];
        } elseif (
            isset($saved[$key]['type']) &&
            $saved[$key]['type'] !== ''
        ) {

            $defaults[$key]['type'] =
                sanitize_key($saved[$key]['type']);
        }

        if (
            isset($saved[$key]['options']) &&
            is_array($saved[$key]['options'])
        ) {
            $defaults[$key]['options'] =
                array_values(
                    array_filter(
                        array_map(
                            'sanitize_text_field',
                            $saved[$key]['options']
                        )
                    )
                );
        }

        if ($defaults[$key]['locked']) {
            $defaults[$key]['enabled'] = true;
        }
    }

    return $defaults;
}

function career_core_field_enabled($key)
{
    $fields = career_get_core_fields();

    return isset($fields[$key]) ? (bool) $fields[$key]['enabled'] : true;
}

//custom fields

/**
 * Predefined, grouped option lists the admin can pick from when
 * a custom field's label matches a recognizable field type
 * (Designation, Qualification, Country, State, City, ...).
 *
 * Each entry has a display 'label' and 'groups' — group heading
 * => list of options under that heading (used to render the
 * checkbox picker under the "Options (comma separated)" box).
 */
function career_get_field_preset_catalogs()
{
    $catalogs = array(

        'designation' => array(
            'label'  => 'Designation',
            'groups' => array(
                'Development' => array(
                    'Frontend Developer',
                    'Backend Developer',
                    'Full Stack Developer',
                    'Mobile App Developer',
                    'Game Developer',
                ),
                'Software' => array(
                    'Software Engineer',
                    'System Analyst',
                    'DevOps Engineer',
                    'Software Architect',
                ),
                'Testing' => array(
                    'QA Engineer',
                    'Manual Tester',
                    'Automation Tester',
                    'Test Lead',
                ),
                'Designing' => array(
                    'UI/UX Designer',
                    'Graphic Designer',
                    'Product Designer',
                ),
                'Analysis' => array(
                    'Business Analyst',
                    'Data Analyst',
                    'Research Analyst',
                ),
            ),
        ),

        'qualification' => array(
            'label'  => 'Qualification',
            'groups' => array(
                'Diploma / ITI' => array(
                    'Diploma in Engineering',
                    'Diploma in Computer Engineering',
                    'Diploma in Mechanical Engineering',
                    'Diploma in Civil Engineering',
                    'Diploma in Electrical Engineering',
                    'Diploma in Electronics',
                    'ITI - Fitter',
                    'ITI - Electrician',
                    'ITI - Welder',
                    'Polytechnic',
                ),
                'BE / B.Tech' => array(
                    'BE Computer Science',
                    'BE Mechanical',
                    'BE Civil',
                    'BE Electronics & Communication',
                    'BE Electrical & Electronics',
                    'BE Information Technology',
                    'BE Automobile',
                    'BE Aeronautical',
                    'BE Biomedical',
                    'BE Chemical',
                    'BE Instrumentation',
                    'B.Tech Computer Science',
                    'B.Tech Information Technology',
                    'B.Tech Artificial Intelligence',
                    'B.Tech Data Science',
                    'B.Tech Mechanical',
                    'B.Tech Civil',
                    'B.Tech Electrical',
                    'B.Tech Electronics',
                    'B.Tech Chemical',
                ),
                'B.Sc' => array(
                    'B.Sc Computer Science',
                    'B.Sc Mathematics',
                    'B.Sc Physics',
                    'B.Sc Chemistry',
                    'B.Sc Biotechnology',
                    'B.Sc Information Technology',
                    'B.Sc Biology',
                    'B.Sc Nursing',
                    'B.Sc Agriculture',
                    'B.Sc Psychology',
                    'B.Sc Statistics',
                    'B.Sc Zoology',
                    'B.Sc Botany',
                ),
                'Computer Applications' => array(
                    'BCA',
                    'MCA',
                ),
                'Post Graduation' => array(
                    'M.Tech',
                    'MBA',
                    'M.Sc',
                    'MA',
                    'M.Com',
                    'MSW',
                ),
            ),
        ),

        'country' => array(
            'label'  => 'Country',
            'groups' => array(
                'Countries' => array(
                    'India',
                    'United States',
                    'United Kingdom',
                    'Australia',
                    'Canada',
                    'Germany',
                    'France',
                    'Singapore',
                    'United Arab Emirates',
                    'New Zealand',
                ),
            ),
        ),

        'state' => array(
            'label'  => 'State',
            'groups' => array(
                'India' => array(
                    'Tamil Nadu',
                    'Kerala',
                    'Karnataka',
                    'Andhra Pradesh',
                    'Telangana',
                    'Maharashtra',
                    'Delhi',
                    'Gujarat',
                    'Rajasthan',
                    'West Bengal',
                    'Uttar Pradesh',
                ),
                'United States' => array(
                    'California',
                    'Texas',
                    'Florida',
                    'New York',
                    'New Jersey',
                    'Washington',
                ),
                'United Kingdom' => array(
                    'England',
                    'Scotland',
                    'Wales',
                    'Northern Ireland',
                ),
                'Australia' => array(
                    'New South Wales',
                    'Victoria',
                    'Queensland',
                    'Western Australia',
                ),
                'Canada' => array(
                    'Ontario',
                    'Quebec',
                    'British Columbia',
                    'Alberta',
                ),
            ),
        ),

        'city' => array(
            'label'  => 'City',
            'groups' => array(
                'Tamil Nadu' => array(
                    'Chennai',
                    'Coimbatore',
                    'Madurai',
                    'Tiruchirappalli',
                    'Salem',
                ),
                'Kerala' => array(
                    'Kochi',
                    'Thiruvananthapuram',
                    'Kozhikode',
                    'Thrissur',
                ),
                'Karnataka' => array(
                    'Bengaluru',
                    'Mysuru',
                    'Mangaluru',
                ),
                'Maharashtra' => array(
                    'Mumbai',
                    'Pune',
                    'Nagpur',
                ),
                'Telangana' => array(
                    'Hyderabad',
                ),
                'Delhi' => array(
                    'New Delhi',
                ),
                'West Bengal' => array(
                    'Kolkata',
                ),
            ),
        ),

    );

    /**
     * Allow other plugins/themes to add or modify the
     * preset catalogs offered in the field-options picker.
     */
    $catalogs = apply_filters(
        'career_field_preset_catalogs',
        $catalogs
    );

    /*
 * Keep every predefined catalog, group and option
 * alphabetically. This also applies to catalogs added
 * later through the filter above.
 */
    foreach ($catalogs as $catalog_key => &$catalog) {

        if (empty($catalog['groups']) || ! is_array($catalog['groups'])) {
            continue;
        }

        uksort(
            $catalog['groups'],
            'strnatcasecmp'
        );

        foreach ($catalog['groups'] as &$options) {

            if (! is_array($options)) {
                continue;
            }

            $options = array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'sanitize_text_field',
                            $options
                        )
                    )
                )
            );

            usort(
                $options,
                'strnatcasecmp'
            );
        }

        unset($options);
    }

    unset($catalog);

    return $catalogs;
}

/**
 * Try to match a custom field's label (as typed by the admin)
 * to one of the known preset catalogs above, so the options
 * picker knows which list to show. Case-insensitive, matches
 * if the catalog key appears anywhere in the label — e.g.
 * "Home State", "Native Country", "Current City" all match.
 */
function career_match_preset_catalog_key($label)
{
    $label = strtolower(trim((string) $label));

    if ($label === '') {
        return '';
    }

    $catalogs = career_get_field_preset_catalogs();

    foreach ($catalogs as $key => $catalog) {
        if (strpos($label, $key) !== false) {
            return $key;
        }
    }

    return '';
}

//custom fields

function career_get_custom_fields()
{
    return get_option('career_custom_fields', array());
}

function career_get_enabled_custom_fields()
{
    return array_values(
        array_filter(
            career_get_custom_fields(),
            function ($field) {
                return ! empty($field['enabled']);
            }
        )
    );
}


/**
 * Return the saved field order.
 *
 * The order contains core field keys (name, email, etc.)
 * and custom field IDs (field_xxx). If no order has been
 * saved yet, return the current default order.
 */
function career_get_field_order()
{
    $core_fields   = career_get_core_fields();
    $custom_fields = career_get_custom_fields();

    // Every field id that actually exists right now (core + custom).
    $all_ids = array_merge(
        array_keys($core_fields),
        wp_list_pluck($custom_fields, 'id')
    );

    $saved_order = get_option('career_field_order', array());

    if (!is_array($saved_order) || empty($saved_order)) {
        return $all_ids;
    }

    $saved_order = array_values(
        array_filter(
            array_map('sanitize_text_field', $saved_order)
        )
    );

    // Drop ids from the saved order that no longer exist
    // (e.g. a custom field that was deleted).
    $saved_order = array_values(
        array_intersect($saved_order, $all_ids)
    );

    // Append any current field ids that are missing from the
    // saved order (e.g. a custom field added after the order
    // was last saved), so newly created fields keep appearing
    // on the form/admin table instead of silently disappearing.
    $missing = array_diff($all_ids, $saved_order);

    if (!empty($missing)) {
        $saved_order = array_merge($saved_order, array_values($missing));
    }

    return $saved_order;
}
