<?php
/*
Plugin Name:       Cirilica u latinicu (WHS)
Plugin URI:        https://www.webhostingsrbija.rs/
Description:       Pouzdana konverzija srpske ćirilice u latinicu (frontend) bez menjanja baze. WooCommerce-friendly.
Version:           1.1.0
Requires at least: 5.2
Requires PHP:      8.0
Author:            WebHostingSrbija
Author URI:        https://www.webhostingsrbija.rs/
License:           GPL v2 or later
Text Domain:       cirilica-u-latinicu
*/

if (!defined('ABSPATH')) { exit; }

/**
 * Brza provera: radi konverziju samo ako string sadrži ćirilicu.
 */
function whs_c2l_has_cyrillic($text): bool {
    return is_string($text) && $text !== '' && preg_match('/\p{Cyrillic}/u', $text);
}

/**
 * Srpska ćirilica -> latinica (sa LJ/NJ/DŽ i Đ/Ć/Č/Ž/Š).
 * Ne dira bazu, radi na izlazu.
 */
function whs_c2l($text) {
    if (!is_string($text) || $text === '' || !whs_c2l_has_cyrillic($text)) {
        return $text;
    }

    static $map = null;
    if ($map === null) {
        $map = [
            'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Ђ'=>'Đ','Е'=>'E','Ж'=>'Ž','З'=>'Z','И'=>'I','Ј'=>'J','К'=>'K','Л'=>'L','Љ'=>'Lj','М'=>'M','Н'=>'N','Њ'=>'Nj','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','Ћ'=>'Ć','У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'C','Ч'=>'Č','Џ'=>'Dž','Ш'=>'Š',
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','ђ'=>'đ','е'=>'e','ж'=>'ž','з'=>'z','и'=>'i','ј'=>'j','к'=>'k','л'=>'l','љ'=>'lj','м'=>'m','н'=>'n','њ'=>'nj','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','ћ'=>'ć','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'č','џ'=>'dž','ш'=>'š',
        ];
    }

    // Ako je reč u ALL CAPS, hoćemo LJ/NJ/DŽ umesto Lj/Nj/Dž.
    // Primer: "ЉУБАВ" -> "LJUBAV"
    $text = preg_replace_callback(
        '/(Љ|Њ|Џ)(?=[A-ZА-ЯЉЊЏŠĐČĆŽ])|(\b[А-ЯЉЊЏ]+(?:\b))/u',
        function ($m) {
            // Prvi deo: pojedinačni digraf pre velikog slova
            if (!empty($m[1])) {
                return $m[1] === 'Љ' ? 'LJ' : ($m[1] === 'Њ' ? 'NJ' : 'DŽ');
            }
            // Drugi deo: cela reč ALL CAPS - samo vrati (dalje će strtr uraditi ostalo),
            // ali prvo pretvori digrafe u uppercase varijante.
            if (!empty($m[2])) {
                return str_replace(['Љ','Њ','Џ'], ['LJ','NJ','DŽ'], $m[2]);
            }
            return $m[0];
        },
        $text
    );

    return strtr($text, $map);
}

/**
 * Gettext filteri: hvataju prevedene stringove (WooCommerce result count, labels, breadcrumb "Почетна", itd.)
 */
function whs_c2l_gettext($translated, $original, $domain) {
    return whs_c2l($translated);
}
add_filter('gettext', 'whs_c2l_gettext', 9999, 3);
add_filter('gettext_with_context', 'whs_c2l_gettext', 9999, 3);
add_filter('ngettext', 'whs_c2l_gettext', 9999, 3);
add_filter('ngettext_with_context', 'whs_c2l_gettext', 9999, 3);

/**
 * Dodatni WP sadržaj (minimalno, bez ludih admin filtera).
 */
add_filter('the_title', 'whs_c2l', 9999);
add_filter('the_content', 'whs_c2l', 9999);
add_filter('the_excerpt', 'whs_c2l', 9999);
add_filter('widget_title', 'whs_c2l', 9999);
add_filter('widget_text', 'whs_c2l', 9999);
add_filter('widget_text_content', 'whs_c2l', 9999);
add_filter('nav_menu_item_title', 'whs_c2l', 9999);
add_filter('nav_menu_description', 'whs_c2l', 9999);

/**
 * Output buffering fallback: hvata sve što je promaklo (tema/plugini koji echo-ju direktno).
 * Radi samo na frontendu i samo za HTML.
 */
$GLOBALS['whs_c2l_ob_started'] = false;

function whs_c2l_should_buffer(): bool {
    if (is_admin()) return false;
    if (defined('DOING_AJAX') && DOING_AJAX) return false;
    if (defined('REST_REQUEST') && REST_REQUEST) return false;
    if (defined('WP_CLI') && WP_CLI) return false;
    if (defined('DOING_CRON') && DOING_CRON) return false;
    if (function_exists('wp_is_json_request') && wp_is_json_request()) return false;
    if (is_feed() || is_trackback()) return false;
    return true;
}

function whs_c2l_buffer_callback($html) {
    // Ne diraj ako nema ćirilice (brzo).
    if (!whs_c2l_has_cyrillic($html)) return $html;
    return whs_c2l($html);
}

function whs_c2l_buffer_start() {
    if (!whs_c2l_should_buffer()) return;

    // Ne startuj buffer ako je već startovan u nekom konfliktu.
    if (!empty($GLOBALS['whs_c2l_ob_started'])) return;

    $GLOBALS['whs_c2l_ob_started'] = true;
    ob_start('whs_c2l_buffer_callback');
}
add_action('template_redirect', 'whs_c2l_buffer_start', 0);

function whs_c2l_buffer_end() {
    if (empty($GLOBALS['whs_c2l_ob_started'])) return;
    // Zatvori samo naš buffer (ne ubijaj tuđe buffere).
    if (ob_get_level() > 0) {
        @ob_end_flush();
    }
}
add_action('shutdown', 'whs_c2l_buffer_end', 0);
