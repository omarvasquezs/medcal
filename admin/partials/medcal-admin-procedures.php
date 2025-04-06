<?php
/**
 * Admin view for procedures settings.
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
settings_errors('medcal_procedures');
?>

<div class="wrap medcal-admin-container">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="medcal-admin-help">
        <h3><?php _e('Cómo usar la calculadora', 'medcal'); ?></h3>
        <p><?php _e('Utilice estos shortcodes para mostrar las calculadoras en sus páginas:', 'medcal'); ?></p>
        <p><strong><?php _e('Para todas las calculadoras en formato de pestañas:', 'medcal'); ?></strong></p>
        <code>[calculadora_precios]</code>
    </div>

    <div class="medcal-admin-section">
        <h2><?php _e('Agregar Nuevo Procedimiento', 'medcal'); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('medcal_add_procedure', 'medcal_add_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="procedure_id"><?php _e('ID del Procedimiento', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="procedure_id" name="procedure_id" class="regular-text" required>
                        <p class="description"><?php _e('ID único para el procedimiento (solo letras minúsculas, números y guiones).', 'medcal'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="procedure_title"><?php _e('Título', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="procedure_title" name="procedure_title" class="regular-text" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="procedure_currency"><?php _e('Símbolo de Moneda', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="procedure_currency" name="procedure_currency" 
                               value="<?php echo esc_attr($general_settings['default_currency']); ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="procedure_total"><?php _e('Precio Total', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="procedure_total" name="procedure_total" min="0" step="0.01" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="procedure_pago_texto"><?php _e('Texto de Pago', 'medcal'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="procedure_pago_texto" name="procedure_pago_texto" 
                               value="PAGUE SOLO">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <?php _e('Estado', 'medcal'); ?>
                    </th>
                    <td>
                        <label class="medcal-toggle-switch">
                            <input type="checkbox" name="procedure_enabled" value="1" checked>
                            <span class="medcal-toggle-slider"></span>
                        </label>
                        <span class="medcal-status-text medcal-status-enabled">
                            <?php _e('Activo', 'medcal'); ?>
                        </span>
                    </td>
                </tr>
            </table>
            
            <div class="medcal-submit-row">
                <button type="submit" name="medcal_add_procedure" class="button button-primary">
                    <?php _e('Agregar Procedimiento', 'medcal'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div class="medcal-admin-section">
        <h2><?php _e('Procedimientos Existentes', 'medcal'); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('medcal_save_procedures', 'medcal_nonce'); ?>
            
            <?php foreach ($procedures as $key => $procedure) : ?>
                <div class="medcal-procedure-card">
                    <h3>
                        <?php echo esc_html($procedure['title']); ?>
                        <label class="medcal-toggle-switch">
                            <input type="checkbox" name="procedures[<?php echo esc_attr($key); ?>][enabled]" value="1" <?php checked(isset($procedure['enabled']) && $procedure['enabled']); ?>>
                            <span class="medcal-toggle-slider"></span>
                        </label>
                        <span class="medcal-status-text <?php echo isset($procedure['enabled']) && $procedure['enabled'] ? 'medcal-status-enabled' : 'medcal-status-disabled'; ?>">
                            <?php echo isset($procedure['enabled']) && $procedure['enabled'] ? __('Activo', 'medcal') : __('Inactivo', 'medcal'); ?>
                        </span>
                        
                        <!-- Delete procedure button (not a nested form) -->
                        <button type="button" class="button button-secondary button-small medcal-delete-button" 
                                data-procedure-id="<?php echo esc_attr($key); ?>" 
                                data-procedure-title="<?php echo esc_attr($procedure['title']); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </h3>
                    
                    <div class="medcal-form-row">
                        <div class="medcal-form-field">
                            <label for="<?php echo esc_attr($key); ?>-title"><?php _e('Título', 'medcal'); ?></label>
                            <input type="text" id="<?php echo esc_attr($key); ?>-title" 
                                   name="procedures[<?php echo esc_attr($key); ?>][title]" 
                                   value="<?php echo esc_attr($procedure['title']); ?>" required>
                        </div>
                        
                        <div class="medcal-form-field">
                            <label for="<?php echo esc_attr($key); ?>-pago_texto"><?php _e('Texto de Pago', 'medcal'); ?></label>
                            <input type="text" id="<?php echo esc_attr($key); ?>-pago_texto" 
                                   name="procedures[<?php echo esc_attr($key); ?>][pago_texto]" 
                                   value="<?php echo esc_attr($procedure['pago_texto'] ?? 'PAGUE SOLO'); ?>">
                        </div>
                    </div>
                    
                    <div class="medcal-form-row">
                        <div class="medcal-form-field">
                            <label for="<?php echo esc_attr($key); ?>-currency"><?php _e('Símbolo de Moneda', 'medcal'); ?></label>
                            <input type="text" id="<?php echo esc_attr($key); ?>-currency" 
                                   name="procedures[<?php echo esc_attr($key); ?>][currency]" 
                                   value="<?php echo esc_attr($procedure['currency']); ?>">
                        </div>
                        
                        <div class="medcal-form-field">
                            <label for="<?php echo esc_attr($key); ?>-total"><?php _e('Precio Total', 'medcal'); ?></label>
                            <input type="number" id="<?php echo esc_attr($key); ?>-total" 
                                   name="procedures[<?php echo esc_attr($key); ?>][total]" 
                                   value="<?php echo esc_attr($procedure['total']); ?>" 
                                   min="0" step="0.01" required>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="medcal-submit-row">
                <button type="submit" name="medcal_save_procedures" class="button button-primary">
                    <?php _e('Guardar Cambios', 'medcal'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div class="medcal-admin-reset">
        <form method="post" action="">
            <?php wp_nonce_field('medcal_reset_procedures', 'medcal_reset_nonce'); ?>
            <button type="submit" name="medcal_reset_procedures" class="button button-secondary" 
                    onclick="return confirm('<?php _e('¿Está seguro de que desea restaurar todos los procedimientos a los valores predeterminados? Esta acción no se puede deshacer.', 'medcal'); ?>');">
                <?php _e('Restaurar Valores Predeterminados', 'medcal'); ?>
            </button>
        </form>
    </div>
</div>

<style>
    .medcal-admin-container {
        max-width: 900px;
    }
    .medcal-admin-section {
        background: #fff;
        border: 1px solid #ddd;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    .medcal-procedure-card {
        background: #f9f9f9;
        border: 1px solid #eee;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
    }
    .medcal-procedure-card h3 {
        display: flex;
        align-items: center;
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .medcal-toggle-switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 20px;
        margin: 0 10px;
    }
    .medcal-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .medcal-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 20px;
    }
    .medcal-toggle-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    .medcal-toggle-switch input:checked + .medcal-toggle-slider {
        background-color: #2196F3;
    }
    .medcal-toggle-switch input:checked + .medcal-toggle-slider:before {
        transform: translateX(20px);
    }
    .medcal-status-text {
        margin-right: auto;
    }
    .medcal-status-enabled {
        color: #2196F3;
    }
    .medcal-status-disabled {
        color: #999;
    }
    .medcal-form-row {
        display: flex;
        margin-bottom: 10px;
        gap: 15px;
    }
    .medcal-form-field {
        flex: 1;
    }
    .medcal-form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .medcal-form-field input {
        width: 100%;
    }
    .medcal-submit-row {
        margin-top: 20px;
    }
    .medcal-admin-reset {
        margin-top: 30px;
    }
    .medcal-delete-form {
        display: inline-block;
        margin-left: 10px;
    }
    .medcal-delete-form button {
        color: #cc0000;
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle status text when checkbox changes
    $('.medcal-toggle-switch input').on('change', function() {
        var statusText = $(this).closest('h3').find('.medcal-status-text');
        if ($(this).is(':checked')) {
            statusText.text('<?php _e('Activo', 'medcal'); ?>');
            statusText.removeClass('medcal-status-disabled').addClass('medcal-status-enabled');
        } else {
            statusText.text('<?php _e('Inactivo', 'medcal'); ?>');
            statusText.removeClass('medcal-status-enabled').addClass('medcal-status-disabled');
        }
    });
    
    // Auto-generate ID from title
    $('#procedure_title').on('blur', function() {
        var title = $(this).val();
        if (title && $('#procedure_id').val() === '') {
            var id = title.toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '_');
            $('#procedure_id').val(id);
        }
    });

    // Handle delete button click
    $('.medcal-delete-button').on('click', function() {
        var procedureId = $(this).data('procedure-id');
        var procedureTitle = $(this).data('procedure-title');
        if (confirm('<?php _e('¿Está seguro de que desea eliminar este procedimiento?', 'medcal'); ?> ' + procedureTitle + '?')) {
            $('<form method="post" action="">')
                .append($('<input type="hidden" name="procedure_id">').val(procedureId))
                .append($('<input type="hidden" name="medcal_delete_procedure">').val('1'))
                .append($('<input type="hidden" name="medcal_delete_nonce">').val('<?php echo wp_create_nonce('medcal_delete_procedure'); ?>'))
                .appendTo('body')
                .submit();
        }
    });
});
</script>