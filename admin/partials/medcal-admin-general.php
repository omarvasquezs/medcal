<?php
/**
 * Admin view for general settings.
 *
 * @link       https://beacons.ai/omarvasquez
 * @since      1.0.0
 *
 * @package    Medcal
 * @subpackage Medcal/admin/partials
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Display any admin notices
settings_errors('medcal_general_settings');
?>

<div class="wrap medcal-admin-container">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="medcal-admin-section">
        <h2><?php _e('Configuración General', 'medcal'); ?></h2>
        
        <div class="medcal-admin-help">
            <p><?php _e('La configuración general afecta a todas las calculadoras de procedimientos:', 'medcal'); ?></p>
            <ul>
                <li><?php _e('<strong>Símbolo de Moneda por Defecto:</strong> Se utilizará en nuevos procedimientos.', 'medcal'); ?></li>
                <li><?php _e('<strong>Plazos:</strong> Controla los términos mínimos y máximos para los pagos a plazos.', 'medcal'); ?></li>
                <li><?php _e('<strong>Número de WhatsApp:</strong> Número que se contactará cuando el usuario haga clic en el botón.', 'medcal'); ?></li>
                <li><?php _e('<strong>Texto del Botón:</strong> Texto que aparece en el botón de WhatsApp.', 'medcal'); ?></li>
            </ul>
        </div>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=' . $_GET['page'])); ?>">
            <?php wp_nonce_field('medcal_general_settings', 'medcal_general_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="default_currency"><?php _e('Símbolo de Moneda por Defecto', 'medcal'); ?></label>
                    </th>
                    <td>
                        <select id="default_currency" name="default_currency">
                            <option value="S/. " <?php selected(strpos($general_settings['default_currency'], 'S/') !== false, true); ?>>
                                <?php echo esc_html(__('S/. (Sol peruano)', 'medcal')); ?>
                            </option>
                            <option value="$ " <?php selected(strpos($general_settings['default_currency'], '$') !== false, true); ?>>
                                <?php echo esc_html(__('$ (Dólar)', 'medcal')); ?>
                            </option>
                        </select>
                        <p class="description"><?php _e('Símbolo de moneda a usar por defecto en todos los procedimientos.', 'medcal'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="min_term"><?php _e('Plazo Mínimo (meses)', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="min_term" name="min_term" 
                               value="<?php echo esc_attr($general_settings['min_term']); ?>" 
                               class="small-text" min="1" max="48">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="max_term"><?php _e('Plazo Máximo (meses)', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="max_term" name="max_term" 
                               value="<?php echo esc_attr($general_settings['max_term']); ?>" 
                               class="small-text" min="1" max="48">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_term"><?php _e('Plazo por Defecto (meses)', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="default_term" name="default_term" 
                               value="<?php echo esc_attr($general_settings['default_term']); ?>" 
                               class="small-text" min="1" max="48">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="title"><?php _e('Título de la Calculadora', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="title" name="title" 
                               value="<?php echo esc_attr($general_settings['title']); ?>" class="regular-text">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="contact_number"><?php _e('Número de WhatsApp', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="contact_number" name="contact_number" 
                               value="<?php echo esc_attr($general_settings['contact_number']); ?>" class="regular-text">
                        <p class="description"><?php _e('Número con código de país (sin espacios ni símbolos). Ejemplo: 51941888957', 'medcal'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="button_text"><?php _e('Texto del Botón', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="button_text" name="button_text" 
                               value="<?php echo esc_attr($general_settings['button_text']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="medcal_save_general_settings" class="button button-primary">
                    <?php _e('Guardar Cambios', 'medcal'); ?>
                </button>
            </p>
        </form>
    </div>
</div>