<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://beacons.ai/omarvasquez
 * @since      1.0.0
 *
 * @package    Medcal
 * @subpackage Medcal/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Medcal
 * @subpackage Medcal/admin
 * @author     Omar Vásquez <omarvs91@gmail.com>
 */
class Medcal_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The procedures instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Medcal_Procedures    $procedures    The procedures instance.
	 */
	private $procedures;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string             $plugin_name    The name of this plugin.
	 * @param    string             $version        The version of this plugin.
	 * @param    Medcal_Procedures  $procedures     The procedures instance.
	 */
	public function __construct($plugin_name, $version, $procedures) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->procedures = $procedures;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/medcal-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Enqueue the admin script
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/medcal-admin.js', array('jquery'), $this->version, false);
		
		// Enqueue jQuery UI Sortable for drag and drop functionality
		wp_enqueue_script('jquery-ui-sortable');
		
		 // Enqueue WordPress color picker
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		
		// Localize the script with data for AJAX calls
		wp_localize_script($this->plugin_name, 'medcal_vars', array(
			'procedure_order_nonce' => wp_create_nonce('medcal_procedure_order'),
			'order_success_message' => __('Orden de procedimientos actualizado correctamente.', 'medcal'),
			'order_error_message' => __('Error al actualizar el orden de procedimientos.', 'medcal'),
		));
	}

	/**
	 * Register hooks for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Register admin menu
		add_action('admin_menu', array($this, 'add_admin_menu'));
		
		// Register admin scripts and styles
		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		
		// Register AJAX handler for procedure sorting
		add_action('wp_ajax_medcal_update_procedure_order', array($this, 'ajax_update_procedure_order'));
	}

	/**
	 * Register the admin menu for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		// Main menu
		add_menu_page(
			 'Calculadora Médica Ocular',
			 'Calculadora Médica Ocular',
			 'manage_options',
			 'medcal',
			 array($this, 'display_procedures_page'),
			 'dashicons-calculator',
			 26
		);

		// Procedures submenu (same as main)
		add_submenu_page(
			'medcal',
			'Ajuste de Costos de Cirugías',
			'Cirugías',
			'manage_options',
			'medcal',
			array($this, 'display_procedures_page')
		);

		
		// Calculator Configuracion submenu
		add_submenu_page(
			'medcal',
			'Configuración',
			'Configuración',
			'manage_options',
			'medcal-fields',
			array($this, 'display_fields_page')
		);
	}

	/**
	 * Display the settings page for procedures.
	 *
	 * @since    1.0.0
	 */
	public function display_procedures_page() {
		// Handle form submission for adding new procedure
		if (isset($_POST['medcal_add_procedure']) && check_admin_referer('medcal_add_procedure', 'medcal_add_nonce')) {
			$this->add_procedure_settings();
		}
		
		// Handle form submission for deleting a procedure
		if (isset($_POST['medcal_delete_procedure']) && check_admin_referer('medcal_delete_procedure', 'medcal_delete_nonce')) {
			$this->delete_procedure_settings();
		}
		
		// Handle form submission for saving procedures
		if (isset($_POST['medcal_save_procedures']) && check_admin_referer('medcal_save_procedures', 'medcal_nonce')) {
			$this->save_procedures_settings();
		}

		// Handle reset to defaults
		if (isset($_POST['medcal_reset_procedures']) && check_admin_referer('medcal_reset_procedures', 'medcal_reset_nonce')) {
			$this->reset_procedures_settings();
		}
		
		// Get all procedures, including disabled ones
		$procedures = $this->procedures->get_all_procedures();
		
		 // Get general settings for default values
		$general_settings = get_option('medcal_general_settings', array(
			'default_currency' => 'S/. ',
		));
		
		 // Get available currencies
		$currencies = $this->get_available_currencies();
		
		// Load admin view
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/medcal-admin-procedures.php';
	}
	
	/**
	 * Get available currency options
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @return   array    The available currency options
	 */
	private function get_available_currencies() {
		return array(
			'S/. ' => __('S/. (Sol peruano)', 'medcal'),
			'S/.' => __('S/. (Sol peruano)', 'medcal'),
			'$ ' => __('$ (Dólar)', 'medcal'),
			'$' => __('$ (Dólar)', 'medcal')
		);
	}

	/**
	 * Add a new procedure.
	 *
	 * @since    1.0.0
	 */
	private function add_procedure_settings() {
		// Check if all required fields are set
		if (!isset($_POST['procedure_id'], $_POST['procedure_title'], $_POST['procedure_total'], $_POST['procedure_currency'])) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedure_add_error',
				__('Error al agregar el procedimiento. Faltan campos requeridos.', 'medcal'),
				'error'
			);
			return;
		}
		
		$procedure_title = sanitize_text_field($_POST['procedure_title']);
		
		// Check if procedure with same title already exists
		$existing_procedures = $this->procedures->get_all_procedures();
		foreach ($existing_procedures as $proc) {
			if (strtolower($proc['title']) === strtolower($procedure_title)) {
				add_settings_error(
					'medcal_procedures',
					'medcal_procedure_add_error',
					__('Error al agregar el procedimiento. Ya existe un procedimiento con este título.', 'medcal'),
					'error'
				);
				return;
			}
		}
		
		$procedure_data = array(
			'title'      => $procedure_title,
			'currency'   => sanitize_text_field($_POST['procedure_currency']),
			'total'      => floatval($_POST['procedure_total']),
			'pago_texto' => isset($_POST['procedure_pago_texto']) ? sanitize_text_field($_POST['procedure_pago_texto']) : 'PAGUE SOLO',
			'enabled'    => isset($_POST['procedure_enabled']) ? true : false,
		);
		
		$procedure_id = sanitize_key($_POST['procedure_id']);
		
		// Validate ID
		if (empty($procedure_id)) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedure_add_error',
				__('Error al agregar el procedimiento. El ID no es válido.', 'medcal'),
				'error'
			);
			return;
		}
		
		// Add procedure
		$success = $this->procedures->add_procedure($procedure_id, $procedure_data);
		
		if ($success) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedure_added',
				__('Procedimiento agregado correctamente.', 'medcal'),
				'success'
			);
		} else {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedure_add_error',
				__('Error al agregar el procedimiento. Es posible que el ID ya exista.', 'medcal'),
				'error'
			);
		}
	}
	
	/**
	 * Delete a procedure.
	 *
	 * @since    1.0.0
	 */
	private function delete_procedure_settings() {
		// Check if procedure ID is set
		if (!isset($_POST['procedure_id'])) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedure_delete_error',
				__('Error al eliminar el procedimiento. ID no especificado.', 'medcal'),
				'error'
			);
			return;
		}
		
		$procedure_id = sanitize_key($_POST['procedure_id']);
		
		// Delete procedure
		$success = $this->procedures->remove_procedure($procedure_id);
		
		if ($success) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedure_deleted',
				__('Procedimiento eliminado correctamente.', 'medcal'),
				'success'
			);
		} else {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedure_delete_error',
				__('Error al eliminar el procedimiento. El procedimiento no existe.', 'medcal'),
				'error'
			);
		}
	}

	/**
	 * Display the general settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_general_settings_page() {
		// Handle form submission
		if (isset($_POST['medcal_save_general_settings']) && check_admin_referer('medcal_save_general_settings', 'medcal_general_nonce')) {
			$this->save_general_settings();
		}

		// Get general settings
		$general_settings = get_option('medcal_general_settings', array(
			'default_currency' => 'S/. ',
			'min_term' => 1,
			'max_term' => 6,
			'default_term' => 6,
			'contact_number' => '51941888957',
			'button_text' => 'CONTÁCTENOS',
			'title' => 'Simulador de Precios',
		));
		
		// Load admin view
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/medcal-admin-general.php';
	}

	/**
	 * Display the configuration fields page.
	 *
	 * @since    1.0.0
	 */
	public function display_fields_page() {
		 // Check if form is submitted and process it
		if (isset($_POST['medcal_save_general_settings'])) {
			// Verify the nonce first
			if (isset($_POST['medcal_general_nonce']) && wp_verify_nonce($_POST['medcal_general_nonce'], 'medcal_general_settings')) {
				// Call the save function since nonce check passed
				$this->save_general_settings();
			} else {
				// Add an error if the nonce check failed
				add_settings_error(
					'medcal_general_settings',
					'medcal_nonce_error',
					__('Error de verificación de seguridad. Por favor, intente de nuevo.', 'medcal'),
					'error'
				);
			}
		}
		
		// Get general settings
		$general_settings = get_option('medcal_general_settings', array(
			'default_currency' => 'S/. ',
			'min_term' => 1,
			'max_term' => 6,
			'default_term' => 6,
			'contact_number' => '51941888957',
			'button_text' => 'CONTÁCTENOS',
			'title' => 'Simulador de Precios',
		));
		
		 // Get available currencies
		$currencies = $this->get_available_currencies();
		
		// Load admin view
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/medcal-admin-general.php';
	}

	/**
	 * Save procedures settings.
	 *
	 * @since    1.0.0
	 */
	private function save_procedures_settings() {
		if (!isset($_POST['procedures']) || !is_array($_POST['procedures'])) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedures_error',
				__('Error al guardar los procedimientos.', 'medcal'),
				'error'
			);
			return;
		}

		$procedures_data = $_POST['procedures'];
		$procedures = array();

		foreach ($procedures_data as $key => $data) {
			if (isset($data['title'], $data['total'], $data['currency'], $data['pago_texto'])) {
				$procedures[$key] = array(
					'title' => sanitize_text_field($data['title']),
					'currency' => sanitize_text_field($data['currency']),
					'total' => floatval($data['total']),
					'pago_texto' => sanitize_text_field($data['pago_texto']),
					'enabled' => isset($data['enabled']) ? true : false,
					// Preserve the order value if it exists
					'order' => isset($data['order']) ? intval($data['order']) : null,
				);
			}
		}

		$success = $this->procedures->save_procedures($procedures);

		if ($success) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedures_updated',
				__('Procedimientos actualizados correctamente.', 'medcal'),
				'success'
			);
		} else {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedures_error',
				__('Ocurrió un error al guardar los procedimientos.', 'medcal'),
				'error'
			);
		}
	}

	/**
	 * Reset procedures settings to defaults.
	 *
	 * @since    1.0.0
	 */
	private function reset_procedures_settings() {
		$success = $this->procedures->reset_to_defaults();

		if ($success) {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedures_reset',
				__('Procedimientos restaurados a valores por defecto.', 'medcal'),
				'success'
			);
		} else {
			add_settings_error(
				'medcal_procedures',
				'medcal_procedures_reset_error',
				__('Ocurrió un error al restaurar los procedimientos.', 'medcal'),
				'error'
			);
		}
	}

	/**
	 * Save general settings.
	 *
	 * @since    1.0.0
	 */
	private function save_general_settings() {
		// Get the values from POST
		$min_term = isset($_POST['min_term']) ? intval($_POST['min_term']) : 1;
		$max_term = isset($_POST['max_term']) ? intval($_POST['max_term']) : 6;
		$default_term = isset($_POST['default_term']) ? intval($_POST['default_term']) : 6;
		$term_step = isset($_POST['term_step']) ? intval($_POST['term_step']) : 3;
		
		// Add debug to check values
		error_log("Settings submitted - min_term: $min_term, max_term: $max_term, default_term: $default_term, term_step: $term_step");
		
		// Validate basic requirements
		$is_valid = true;
		$error_message = '';
		
		// Check if fields are valid (all should be > 0)
		if ($min_term < 1 || $max_term < 1 || $default_term < 1 || $term_step < 1) {
			$is_valid = false;
			$error_message = __('Todos los plazos deben ser mayores que cero.', 'medcal');
			error_log("Validation failed: values must be > 0");
		}
		// Check that max term is >= min term
		elseif ($max_term < $min_term) {
			$is_valid = false;
			$error_message = __('El Plazo Máximo debe ser mayor o igual al Plazo Mínimo.', 'medcal');
			error_log("Validation failed: max_term < min_term");
		}
		// Check that default term is between min and max
		elseif ($default_term < $min_term || $default_term > $max_term) {
			$is_valid = false;
			$error_message = __('El Plazo por Defecto debe estar entre el Plazo Mínimo y el Plazo Máximo.', 'medcal');
			error_log("Validation failed: default_term not between min_term and max_term");
		}
		
		if (!$is_valid) {
			add_settings_error(
				'medcal_general_settings',
				'medcal_general_settings_error',
				$error_message,
				'error'
			);
			return;
		}
		
		// Get existing settings to preserve any bank commission values
		$existing_settings = get_option('medcal_general_settings', array());
		error_log("Existing settings: " . print_r($existing_settings, true));
		
		// Start with the basic settings
		$general_settings = array(
			'default_currency' => isset($_POST['default_currency']) ? sanitize_text_field($_POST['default_currency']) : 'S/. ',
			'min_term' => $min_term,
			'max_term' => $max_term,
			'default_term' => $default_term,
			'term_step' => $term_step,
			'contact_number' => isset($_POST['contact_number']) ? sanitize_text_field($_POST['contact_number']) : '51941888957',
			'button_text' => isset($_POST['button_text']) ? sanitize_text_field($_POST['button_text']) : 'CONTÁCTENOS',
			'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'Simulador de Precios',
			'title_color' => isset($_POST['title_color']) && !empty($_POST['title_color']) ? sanitize_hex_color($_POST['title_color']) : '#000000',
			'button_color' => isset($_POST['button_color']) && !empty($_POST['button_color']) ? sanitize_hex_color($_POST['button_color']) : '#25D366',
			'tab_color' => isset($_POST['tab_color']) && !empty($_POST['tab_color']) ? sanitize_hex_color($_POST['tab_color']) : '#0d6efd',
			'inactive_tab_color' => isset($_POST['inactive_tab_color']) && !empty($_POST['inactive_tab_color']) ? sanitize_hex_color($_POST['inactive_tab_color']) : '#6c757d',
			'processing_commission' => isset($_POST['processing_commission']) ? floatval($_POST['processing_commission']) : 3.10,
			'igv' => isset($_POST['igv']) ? floatval($_POST['igv']) : 18.00,
		);
		
		// Calculate the valid terms for bank commissions
		$valid_terms = array($min_term);
		$first_step = ceil($min_term / $term_step) * $term_step;
		if ($first_step === $min_term) {
			$first_step += $term_step;
		}
		
		for ($i = $first_step; $i <= $max_term; $i += $term_step) {
			$valid_terms[] = $i;
		}
		
		// Make sure max is included
		if (end($valid_terms) !== $max_term && $max_term > $term_step) {
			$valid_terms[] = $max_term;
		}
		
		// Remove duplicates and sort
		$valid_terms = array_unique($valid_terms);
		sort($valid_terms);
		
		// Skip min_term if it's 1 (no commission for 1 cuota)
		if ($valid_terms[0] === 1) {
			array_shift($valid_terms);
		}
		
		// Now add all bank commission fields from the POST data
		foreach ($valid_terms as $term) {
			$field_name = "bank_commission_{$term}";
			if (isset($_POST[$field_name])) {
				$general_settings[$field_name] = floatval($_POST[$field_name]);
			} 
			// If the field doesn't exist in POST but exists in previous settings, preserve it
			elseif (isset($existing_settings[$field_name])) {
				$general_settings[$field_name] = $existing_settings[$field_name];
			}
		}
		
		error_log("New settings to save: " . print_r($general_settings, true));
		
		// Check if settings have actually changed before updating
		$settings_changed = false;
		
		// If count of keys is different, settings have changed
		if (count(array_keys($existing_settings)) != count(array_keys($general_settings))) {
			$settings_changed = true;
			error_log("Settings changed: Different number of keys");
		} else {
			// Compare each setting
			foreach ($general_settings as $key => $value) {
				if (!isset($existing_settings[$key]) || $existing_settings[$key] !== $value) {
					$settings_changed = true;
					error_log("Settings changed: Key $key different. Old: " . (isset($existing_settings[$key]) ? $existing_settings[$key] : 'not set') . ", New: $value");
					break;
				}
			}
		}
		
		// Only update if settings have changed
		if ($settings_changed) {
			$success = update_option('medcal_general_settings', $general_settings);
			error_log("Update option result: " . ($success ? 'true' : 'false'));
			
			if ($success) {
				add_settings_error(
					'medcal_general_settings',
					'medcal_general_settings_updated',
					__('Configuración general actualizada correctamente.', 'medcal'),
					'success'
				);
			} else {
				add_settings_error(
					'medcal_general_settings',
					'medcal_general_settings_error',
					__('Ocurrió un error al guardar la configuración general.', 'medcal'),
					'error'
				);
			}
		} else {
			// No changes, but show success message anyway
			add_settings_error(
				'medcal_general_settings',
				'medcal_general_settings_updated',
				__('No se detectaron cambios en la configuración.', 'medcal'),
				'success'
			);
		}
	}

	/**
	 * AJAX handler for updating procedure order
	 *
	 * @since    1.0.0
	 */
	public function ajax_update_procedure_order() {
		 // Add debug log to see if this function is being called
		error_log('AJAX procedure order handler called');
		
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
		
		// Update procedure order
		$order = array_map('sanitize_key', $_POST['procedure_order']);
		$success = $this->procedures->update_procedure_order($order);
		
		if ($success) {
			error_log('Procedure order updated successfully');
			wp_send_json_success(array('message' => 'Procedure order updated successfully'));
		} else {
			error_log('Failed to update procedure order');
			wp_send_json_error(array('message' => 'Failed to update procedure order'));
		}
		
		exit;
	}
}
