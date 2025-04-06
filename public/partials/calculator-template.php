<?php
/**
 * Template for rendering the calculator(s)
 *
 * This template can render either a single calculator or multiple calculators in a tabbed interface.
 *
 * @link       https://beacons.ai/omarvasquez
 * @since      1.0.0
 *
 * @package    Medcal
 * @subpackage Medcal/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Check if we're rendering a tabbed interface or a single calculator
$is_tabbed = isset($procedures) && is_array($procedures) && !empty($procedures);

if ($is_tabbed) {
    // Generate a unique ID for the tabbed container
    $container_id = 'medcal-tabs-' . uniqid();
    ?>
    <div class="medcal-tabbed-container">
        <div class="container mt-5">
            <div class="text-center text-uppercase my-5">
                <h4 class="display-4"><?php echo esc_html($atts['title']); ?></h4>
            </div>
            <div class="row">
                <div class="col-lg-10 col-md-12 mx-auto">
                    <ul class="nav nav-tabs" id="<?php echo esc_attr($container_id); ?>-tabs" role="tablist">
                        <?php
                        $index = 0;
                        foreach ($procedures as $proc_id => $procedure):
                            $tab_id = $container_id . '-tab-' . $proc_id;
                            $active = ($index === 0) ? 'active' : '';
                            ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo $active; ?>" id="<?php echo esc_attr($tab_id); ?>-btn"
                                    data-bs-toggle="tab" data-bs-target="#<?php echo esc_attr($tab_id); ?>" type="button"
                                    role="tab" aria-controls="<?php echo esc_attr($tab_id); ?>"
                                    aria-selected="<?php echo ($index === 0) ? 'true' : 'false'; ?>">
                                    <?php echo esc_html($procedure['title']); ?>
                                </button>
                            </li>
                            <?php
                            $index++;
                        endforeach;
                        ?>
                    </ul>
                    <div class="tab-content" id="<?php echo esc_attr($container_id); ?>-content">
                        <?php
                        $index = 0;
                        foreach ($procedures as $proc_id => $procedure):
                            $tab_id = $container_id . '-tab-' . $proc_id;
                            $show_active = ($index === 0) ? 'show active' : '';
                            $calculator_id = 'medcal-' . $proc_id . '-' . uniqid();
                            ?>
                            <div class="tab-pane fade <?php echo $show_active; ?>" id="<?php echo esc_attr($tab_id); ?>"
                                role="tabpanel" aria-labelledby="<?php echo esc_attr($tab_id); ?>-btn">

                                <div class="calculator" id="<?php echo esc_attr($calculator_id); ?>">
                                    <h1><?php echo esc_html($procedure['title']); ?></h1>
                                    <div class="payment-section">
                                        <h2><?php echo esc_html($procedure['pago_texto'] ?? 'PAGUE'); ?></h2>
                                        <p style="font-size: 2.2rem;"><?php echo esc_html($procedure['currency']); ?> <span
                                                id="<?php echo esc_attr($calculator_id); ?>-total"
                                                data-total-cost="<?php echo esc_attr($procedure['total']); ?>"></span></p>
                                        <h2 style="margin-bottom: 0;">EN</h2>
                                        <div class="term">
                                            <div class="range-container">
                                                <div class="arrow left"><i class="fas fa-angle-left"></i></div>
                                                <input class="rango" type="range"
                                                    id="<?php echo esc_attr($calculator_id); ?>-range"
                                                    name="<?php echo esc_attr($calculator_id); ?>-range"
                                                    min="<?php echo esc_attr($atts['min_term']); ?>"
                                                    max="<?php echo esc_attr($atts['max_term']); ?>"
                                                    value="<?php echo esc_attr($atts['default_term']); ?>"
                                                    data-calculator-id="<?php echo esc_attr($calculator_id); ?>">
                                                <div class="arrow right"><i class="fas fa-angle-right"></i></div>
                                            </div>
                                            <p style="font-size: 2rem;"><span
                                                    id="<?php echo esc_attr($calculator_id); ?>-term"></span></p>
                                        </div>
                                    </div>
                                    <div style="padding: 20px;">
                                        <a href="https://wa.me/<?php echo esc_attr($atts['contact_number']); ?>"
                                            target="_blank"
                                            class="quote-button"><?php echo esc_html($atts['button_text']); ?></a>
                                    </div>
                                </div>

                            </div>
                            <?php
                            $index++;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    // Single calculator display
    $calculator_id = 'medcal-' . $procedure_id . '-' . uniqid();
    ?>
    <div class="calculator" id="<?php echo esc_attr($calculator_id); ?>">
        <h1><?php echo esc_html($procedure['title']); ?></h1>
        <div class="payment-section">
            <h2><?php echo esc_html($procedure['pago_texto'] ?? 'PAGUE'); ?></h2>
            <p style="font-size: 2.2rem;"><?php echo esc_html($procedure['currency']); ?> <span
                    id="<?php echo esc_attr($calculator_id); ?>-total"
                    data-total-cost="<?php echo esc_attr($procedure['total']); ?>"></span></p>
            <h2 style="margin-bottom: 0;">EN</h2>
            <div class="term">
                <div class="range-container">
                    <div class="arrow left"><i class="fas fa-angle-left"></i></div>
                    <input class="rango" type="range" id="<?php echo esc_attr($calculator_id); ?>-range"
                        name="<?php echo esc_attr($calculator_id); ?>-range"
                        min="<?php echo esc_attr($atts['min_term']); ?>"
                        max="<?php echo esc_attr($atts['max_term']); ?>"
                        value="<?php echo esc_attr($atts['default_term']); ?>"
                        data-calculator-id="<?php echo esc_attr($calculator_id); ?>">
                    <div class="arrow right"><i class="fas fa-angle-right"></i></div>
                </div>
                <p style="font-size: 2rem;"><span id="<?php echo esc_attr($calculator_id); ?>-term"></span></p>
            </div>
        </div>
        <div style="padding: 20px;">
            <a href="https://wa.me/<?php echo esc_attr($atts['contact_number']); ?>" target="_blank"
                class="quote-button"><?php echo esc_html($atts['button_text']); ?></a>
        </div>
    </div>
    <?php
}
?>