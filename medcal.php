<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://beacons.ai/omarvasquez
 * @since             1.0.0
 * @package           Medcal
 *
 * @wordpress-plugin
 * Plugin Name:       Médica Ocular - Calculadora de Precios
 * Plugin URI:        https://medicaocular.pe
 * Description:       A WordPress custom plugin that enables visitors to easily calculate the costs of various eye surgeries. This interactive pricing calculator was designed specifically for ophthalmology clinics and eye care centers, allowing patients to get transparent cost estimates before scheduling procedures.
 * Version:           1.0.0
 * Author:            Omar Vásquez
 * Author URI:        https://beacons.ai/omarvasquez/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       medcal
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MEDCAL_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-medcal-activator.php
 */
function activate_medcal() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-medcal-activator.php';
	Medcal_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-medcal-deactivator.php
 */
function deactivate_medcal() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-medcal-deactivator.php';
	Medcal_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_medcal' );
register_deactivation_hook( __FILE__, 'deactivate_medcal' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-medcal.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_medcal() {

	$plugin = new Medcal();
	$plugin->run();

}
run_medcal();

// Direct AJAX handler for procedure ordering (simpler approach)
add_action('wp_ajax_medcal_update_procedure_order', function() {
    // Add debug log
    error_log('Direct AJAX handler for procedure ordering called');
    
    // Check security nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'medcal_procedure_order')) {
        error_log('Nonce verification failed');
        wp_send_json_error(array('message' => 'Nonce verification failed'));
        exit;
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        error_log('Insufficient permissions');
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        exit;
    }
    
    // Check if procedure_order data exists
    if (!isset($_POST['procedure_order']) || !is_array($_POST['procedure_order'])) {
        error_log('Invalid procedure order data');
        wp_send_json_error(array('message' => 'Invalid procedure order data'));
        exit;
    }
    
    // Log the received order data
    error_log('Received procedure order: ' . print_r($_POST['procedure_order'], true));
    
    // Load procedures class
    require_once plugin_dir_path(__FILE__) . 'includes/class-medcal-procedures.php';
    $procedures = new Medcal_Procedures('medcal', MEDCAL_VERSION);
    
    // Update procedure order
    $order = array_map('sanitize_key', $_POST['procedure_order']);
    $success = $procedures->update_procedure_order($order);
    
    if ($success) {
        error_log('Procedure order updated successfully');
        wp_send_json_success(array('message' => 'Procedure order updated successfully'));
    } else {
        error_log('Failed to update procedure order');
        wp_send_json_error(array('message' => 'Failed to update procedure order'));
    }
    
    exit;
});
