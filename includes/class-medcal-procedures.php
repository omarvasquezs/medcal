<?php
/**
 * The procedures functionality of the plugin.
 *
 * @link       https://beacons.ai/omarvasquez
 * @since      1.0.0
 *
 * @package    Medcal
 * @subpackage Medcal/includes
 */

/**
 * Class for managing procedures and their settings.
 *
 * This class defines all code necessary to manage the calculator procedures.
 *
 * @since      1.0.0
 * @package    Medcal
 * @subpackage Medcal/includes
 * @author     Omar Vasquez <mail@omarvasquez.me>
 */
class Medcal_Procedures {

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
     * The procedures data.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $procedures    The procedures data.
     */
    private $procedures;

    /**
     * The general settings data.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $general_settings    The general settings data.
     */
    private $general_settings;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Load procedures and settings
        $this->load_procedures();
        $this->load_general_settings();
    }

    /**
     * Load the saved procedures or set defaults if none exist
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_procedures() {
        $saved_procedures = get_option('medcal_procedures', false);
        
        if ($saved_procedures) {
            $this->procedures = $saved_procedures;
        } else {
            $this->procedures = $this->get_default_procedures();
            update_option('medcal_procedures', $this->procedures);
        }
    }

    /**
     * Load the general settings or set defaults if none exist
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_general_settings() {
        $saved_settings = get_option('medcal_general_settings', false);
        
        if ($saved_settings) {
            $this->general_settings = $saved_settings;
        } else {
            $this->general_settings = $this->get_default_general_settings();
            update_option('medcal_general_settings', $this->general_settings);
        }
    }

    /**
     * Get the default procedures data
     *
     * @since    1.0.0
     * @access   private
     * @return   array    The default procedures data.
     */
    private function get_default_procedures() {
        return array(
            'catarata' => array(
                'enabled'     => true,
                'title'       => 'Cirugía de Catarata',
                'currency'    => 'S/',
                'total'       => 2900,
                'pago_texto'  => 'PAGUE SOLO'
            ),
            'lasik_prk' => array(
                'enabled'     => true,
                'title'       => 'Cirugía Refractiva (Lasik/PRK)',
                'currency'    => 'S/',
                'total'       => 2900,
                'pago_texto'  => 'PAGUE SOLO'
            ),
            'pterigion' => array(
                'enabled'     => true,
                'title'       => 'Cirugía de Pterigion',
                'currency'    => 'S/',
                'total'       => 1450,
                'pago_texto'  => 'PAGUE SOLO'
            ),
            'estrabismo' => array(
                'enabled'     => true,
                'title'       => 'Cirugía de Estrabismo',
                'currency'    => 'S/',
                'total'       => 2900,
                'pago_texto'  => 'PAGUE SOLO'
            ),
            'transplante_cornea' => array(
                'enabled'     => true,
                'title'       => 'Trasplante de Córnea',
                'currency'    => 'S/',
                'total'       => 3500,
                'pago_texto'  => 'PAGUE SOLO'
            ),
        );
    }

    /**
     * Get the default general settings
     *
     * @since    1.0.0
     * @access   private
     * @return   array    The default general settings.
     */
    private function get_default_general_settings() {
        return array(
            'default_currency' => 'S/',
            'min_term'         => 3,
            'max_term'         => 24,
            'default_term'     => 6,
            'title'            => 'Calculadora de Financiamiento',
            'contact_number'   => '51941888957',
            'button_text'      => 'Comunicarme por WhatsApp'
        );
    }

    /**
     * Get all procedures
     *
     * @since    1.0.0
     * @access   public
     * @return   array    All procedures data.
     */
    public function get_all_procedures() {
        return $this->procedures;
    }

    /**
     * Get enabled procedures only
     *
     * @since    1.0.0
     * @access   public
     * @return   array    Enabled procedures data only.
     */
    public function get_enabled_procedures() {
        return array_filter($this->procedures, function($procedure) {
            return isset($procedure['enabled']) && $procedure['enabled'];
        });
    }

    /**
     * Get a specific procedure by key
     *
     * @since    1.0.0
     * @access   public
     * @param    string    $key    The procedure key.
     * @return   array|false       The procedure data or false if not found.
     */
    public function get_procedure($key) {
        if (isset($this->procedures[$key])) {
            return $this->procedures[$key];
        }
        return false;
    }

    /**
     * Get general settings
     *
     * @since    1.0.0
     * @access   public
     * @return   array    The general settings.
     */
    public function get_general_settings() {
        return $this->general_settings;
    }

    /**
     * Save procedures to database
     *
     * @since    1.0.0
     * @access   public
     * @param    array    $procedures    The procedures data to save.
     * @return   boolean                 Whether the update was successful.
     */
    public function save_procedures($procedures) {
        // Merge with current to preserve any missing fields
        $updated_procedures = array();
        
        foreach ($this->procedures as $key => $current_procedure) {
            if (isset($procedures[$key])) {
                $updated_procedures[$key] = array_merge($current_procedure, $procedures[$key]);
                
                // Ensure enabled is properly set as boolean based on presence of the value
                // This is the fix: explicitly set to false when not in the incoming data
                $updated_procedures[$key]['enabled'] = isset($procedures[$key]['enabled']) && $procedures[$key]['enabled'] ? true : false;
            } else {
                $updated_procedures[$key] = $current_procedure;
            }
        }
        
        $this->procedures = $updated_procedures;
        
        // Add error logging to track update_option failures
        $result = update_option('medcal_procedures', $this->procedures);
        
        if (!$result) {
            // Try forcing the update by adding autoload parameter
            $result = update_option('medcal_procedures', $this->procedures, true);
            
            if (!$result) {
                // Log the error for debugging
                error_log('Medcal: Failed to save procedures. Data size: ' . strlen(maybe_serialize($this->procedures)) . ' bytes');
                
                // As a last resort, try deleting the option first then adding it
                delete_option('medcal_procedures');
                $result = add_option('medcal_procedures', $this->procedures, '', true);
            }
        }
        
        return $result;
    }

    /**
     * Save general settings to database
     *
     * @since    1.0.0
     * @access   public
     * @param    array    $settings    The settings to save.
     * @return   boolean               Whether the update was successful.
     */
    public function save_general_settings($settings) {
        $this->general_settings = array_merge($this->general_settings, $settings);
        return update_option('medcal_general_settings', $this->general_settings);
    }

    /**
     * Reset procedures to default values
     *
     * @since    1.0.0
     * @access   public
     * @return   boolean    Whether the reset was successful.
     */
    public function reset_procedures() {
        $this->procedures = $this->get_default_procedures();
        return update_option('medcal_procedures', $this->procedures);
    }

    /**
     * Reset general settings to default values
     *
     * @since    1.0.0
     * @access   public
     * @return   boolean    Whether the reset was successful.
     */
    public function reset_general_settings() {
        $this->general_settings = $this->get_default_general_settings();
        return update_option('medcal_general_settings', $this->general_settings);
    }
    
    /**
     * Reset all settings to default values
     *
     * @since    1.0.0
     * @access   public
     * @return   boolean    Whether both resets were successful.
     */
    public function reset_to_defaults() {
        $procedures_reset = $this->reset_procedures();
        $settings_reset = $this->reset_general_settings();
        
        return $procedures_reset && $settings_reset;
    }

    /**
     * Add a new procedure
     *
     * @since    1.0.0
     * @access   public
     * @param    string    $id         Unique ID for the procedure
     * @param    array     $data       The procedure data
     * @return   boolean               Whether the procedure was added successfully
     */
    public function add_procedure($id, $data) {
        // Validate required fields
        if (!isset($data['title']) || !isset($data['total']) || !isset($data['currency'])) {
            return false;
        }
        
        // Make sure ID is sanitized and doesn't exist already
        $id = sanitize_key($id);
        if (empty($id) || isset($this->procedures[$id])) {
            return false;
        }
        
        // Set defaults for optional fields
        $data['enabled'] = isset($data['enabled']) ? (bool) $data['enabled'] : true;
        $data['pago_texto'] = isset($data['pago_texto']) ? $data['pago_texto'] : 'PAGUE SOLO';
        
        // Add to procedures
        $this->procedures[$id] = array(
            'title'       => sanitize_text_field($data['title']),
            'currency'    => sanitize_text_field($data['currency']),
            'total'       => floatval($data['total']),
            'pago_texto'  => sanitize_text_field($data['pago_texto']),
            'enabled'     => (bool) $data['enabled'],
        );
        
        // Save to database
        return update_option('medcal_procedures', $this->procedures);
    }
    
    /**
     * Remove a procedure
     *
     * @since    1.0.0
     * @access   public
     * @param    string    $id         The procedure ID to remove
     * @return   boolean               Whether the procedure was removed successfully
     */
    public function remove_procedure($id) {
        // Check if procedure exists
        if (!isset($this->procedures[$id])) {
            return false;
        }
        
        // Remove the procedure
        unset($this->procedures[$id]);
        
        // Save to database
        return update_option('medcal_procedures', $this->procedures);
    }
}