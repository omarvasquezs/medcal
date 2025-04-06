<?php

/**
 * The shortcode functionality of the plugin.
 *
 * @link       https://beacons.ai/omarvasquez
 * @since      1.0.0
 *
 * @package    Medcal
 * @subpackage Medcal/public
 */

/**
 * The shortcode functionality of the plugin.
 *
 * Handles registering and rendering the calculator shortcodes.
 *
 * @package    Medcal
 * @subpackage Medcal/public
 * @author     Omar Vásquez <omar@vasquez.dev>
 */
class Medcal_Shortcodes {

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
     * @param    Medcal_Procedures    $procedures    The procedures instance.
     */
    public function __construct( $procedures ) {
        $this->procedures = $procedures;
        
        // Register shortcodes
        add_shortcode( 'medcal_calculator', array( $this, 'render_calculator' ) );
        add_shortcode( 'medcal_tabbed_calculators', array( $this, 'render_tabbed_calculators' ) );
        add_shortcode( 'calculadora_precios', array( $this, 'render_tabbed_calculators' ) );
    }
    
    /**
     * Get the general settings with default values
     *
     * @since    1.0.0
     * @return   array    The general settings
     */
    private function get_general_settings() {
        return get_option('medcal_general_settings', array(
            'default_currency' => 'S/. ',
            'min_term' => 1,
            'max_term' => 6,
            'default_term' => 6,
            'contact_number' => '51941888957',
            'button_text' => 'CONTÁCTENOS',
            'title' => 'Simulador de Precios',
        ));
    }

    /**
     * Render a single calculator shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string   The rendered calculator HTML.
     */
    public function render_calculator( $atts ) {
        // Get general settings
        $general_settings = $this->get_general_settings();
        
        // Merge shortcode attributes with general settings
        $atts = shortcode_atts( array(
            'procedure' => 'catarata',
            'contact_number' => $general_settings['contact_number'],
            'min_term' => $general_settings['min_term'],
            'max_term' => $general_settings['max_term'],
            'default_term' => $general_settings['default_term'],
            'button_text' => $general_settings['button_text'],
        ), $atts, 'medcal_calculator' );
        
        // Get the procedure data
        $procedure = $this->procedures->get_procedure( $atts['procedure'] );
        if ( ! $procedure ) {
            return '<p>Procedimiento no encontrado.</p>';
        }
        
        // Check if procedure is enabled
        if (isset($procedure['enabled']) && !$procedure['enabled']) {
            return ''; // Return empty string for disabled procedures
        }
        
        $procedure_id = sanitize_title( $atts['procedure'] );
        $single_calculator = true;
        
        // Start output buffering
        ob_start();
        
        // Include the template
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/calculator-template.php';
        
        // Return the buffered output
        return ob_get_clean();
    }
    
    /**
     * Render all calculators in a tabbed interface.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string   The rendered tabbed calculators HTML.
     */
    public function render_tabbed_calculators( $atts ) {
        // Get general settings
        $general_settings = $this->get_general_settings();
        
        // Merge shortcode attributes with general settings
        $atts = shortcode_atts( array(
            'contact_number' => $general_settings['contact_number'],
            'min_term' => $general_settings['min_term'],
            'max_term' => $general_settings['max_term'],
            'default_term' => $general_settings['default_term'],
            'button_text' => $general_settings['button_text'],
            'title' => $general_settings['title'],
        ), $atts, 'medcal_tabbed_calculators' );
        
        // Get only enabled procedures
        $procedures = $this->procedures->get_enabled_procedures();
        
        // If no procedures, return empty
        if (empty($procedures)) {
            return '<p>No hay procedimientos habilitados.</p>';
        }
        
        $single_calculator = false;
        
        // Start output buffering
        ob_start();
        
        // Include the unified template
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/calculator-template.php';
        
        // Return the buffered output
        return ob_get_clean();
    }
    
    /**
     * Render the HTML for a single calculator.
     * 
     * @since    1.0.0
     * @param    string    $calculator_id    The ID for the calculator.
     * @param    array     $procedure        The procedure data.
     * @param    array     $atts             The shortcode attributes.
     */
    public function render_calculator_html( $calculator_id, $procedure, $atts ) {
        ?>
        <div class="calculator" id="<?php echo esc_attr( $calculator_id ); ?>">
            <h1><?php echo esc_html( $procedure['title'] ); ?></h1>
            <div class="payment-section">
                <h2><?php echo esc_html( $procedure['pago_texto'] ?? 'PAGUE' ); ?></h2>
                <p style="font-size: 2.2rem;"><?php echo esc_html( $procedure['currency'] ); ?><span 
                    id="<?php echo esc_attr( $calculator_id ); ?>-total" 
                    data-total-cost="<?php echo esc_attr( $procedure['total'] ); ?>"></span></p>
                <h2 style="margin-bottom: 0;">EN</h2>
                <div class="term">
                    <div class="range-container">
                        <div class="arrow left"><i class="fas fa-angle-left"></i></div>
                        <input class="rango" type="range" 
                               id="<?php echo esc_attr( $calculator_id ); ?>-range" 
                               name="<?php echo esc_attr( $calculator_id ); ?>-range" 
                               min="<?php echo esc_attr( $atts['min_term'] ); ?>" 
                               max="<?php echo esc_attr( $atts['max_term'] ); ?>" 
                               value="<?php echo esc_attr( $atts['default_term'] ); ?>" 
                               data-calculator-id="<?php echo esc_attr( $calculator_id ); ?>">
                        <div class="arrow right"><i class="fas fa-angle-right"></i></div>
                    </div>
                    <p style="font-size: 2rem;"><span id="<?php echo esc_attr( $calculator_id ); ?>-term"></span></p>
                </div>
            </div>
            <div style="padding: 20px;">
                <a href="https://wa.me/<?php echo esc_attr( $atts['contact_number'] ); ?>" target="_blank" class="quote-button"><?php echo esc_html( $atts['button_text'] ); ?></a>
            </div>
        </div>
        <?php
    }
}