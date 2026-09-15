<?php
/**
 * Plugin Name: Career Form
 * Description: Customer career application form plugin
 * Version: 1.0.1
 * Author: Sharesoft Technology
 * Text Domain: career-form
 *
 * @package Career_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CAREER_FORM_VERSION', '1.0.1' );
define( 'CAREER_FORM_PATH', plugin_dir_path( __FILE__ ) );
define( 'CAREER_FORM_URL', plugin_dir_url( __FILE__ ) );


  // CORE INCLUDES
   

require_once CAREER_FORM_PATH . 'includes/activation.php';
require_once CAREER_FORM_PATH . 'includes/fields.php';
require_once CAREER_FORM_PATH . 'includes/shortcode.php';
require_once CAREER_FORM_PATH . 'includes/mail-settings.php';
require_once CAREER_FORM_PATH . 'includes/handlers.php';

require_once CAREER_FORM_PATH . 'public/public.php';

if ( is_admin() ) {
    require_once CAREER_FORM_PATH . 'admin/admin.php';
}


  // ACTIVATION

register_activation_hook( __FILE__, 'career_form_activate' );
register_deactivation_hook(__FILE__,'career_form_deactivate');
