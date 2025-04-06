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
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/medcal-admin.js', array('jquery'), $this->version, false);
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
		
		// Load admin view
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/medcal-admin-procedures.php';
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
		
		$procedure_data = array(
			'title'      => sanitize_text_field($_POST['procedure_title']),
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
		$general_settings = array(
			'default_currency' => isset($_POST['default_currency']) ? sanitize_text_field($_POST['default_currency']) : 'S/. ',
			'min_term' => isset($_POST['min_term']) ? intval($_POST['min_term']) : 1,
			'max_term' => isset($_POST['max_term']) ? intval($_POST['max_term']) : 6,
			'default_term' => isset($_POST['default_term']) ? intval($_POST['default_term']) : 6,
			'contact_number' => isset($_POST['contact_number']) ? sanitize_text_field($_POST['contact_number']) : '51941888957',
			'button_text' => isset($_POST['button_text']) ? sanitize_text_field($_POST['button_text']) : 'CONTÁCTENOS',
			'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'Simulador de Precios',
		);

		$success = update_option('medcal_general_settings', $general_settings);

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
	}
}
