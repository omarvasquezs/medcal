<?php
/**
 * Plugin utility functions
 *
 * @link       https://beacons.ai/omarvasquez
 * @since      1.0.0
 *
 * @package    Medcal
 * @subpackage Medcal/includes
 */

/**
 * Adjust brightness of a hex color
 * 
 * @since    1.0.0
 * @param    string    $hex        Hex color code
 * @param    int       $percent    Percentage to adjust (negative for darker, positive for lighter)
 * @return   string                Adjusted hex color
 */
function medcal_adjust_brightness($hex, $percent) {
    // Convert hex to rgb
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $rgb = array(
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    );

    // Adjust brightness
    for ($i = 0; $i < 3; $i++) {
        // Darken
        if ($percent < 0) {
            $rgb[$i] = round($rgb[$i] * (100 + $percent) / 100);
        } 
        // Lighten
        else {
            $rgb[$i] = round($rgb[$i] * (100 - $percent) / 100 + (255 - $rgb[$i]) * $percent / 100);
        }
        // Keep within bounds
        $rgb[$i] = max(0, min(255, $rgb[$i]));
    }

    // Convert back to hex
    return '#' . sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}