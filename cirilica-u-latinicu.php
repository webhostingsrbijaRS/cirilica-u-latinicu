<?php
/*
 * Plugin Name:       Cirilica u latinicu
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Converts Serbian Cyrillic text to Latinic.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      8.0
 * Author:            WebHostingSrbija
 * Author URI:        https://www.webhostingsrbija.rs/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/webhostingsrbijaRS/cirilica-u-latinicu
 * Text Domain:       cirilica-u-latinicu
 */


function cyrillic_to_latinic($text) {
    $cyrillic = array(
        'А', 'Б', 'В', 'Г', 'Д', 'Ђ', 'Е', 'Ж', 'З', 'И', 'Ј', 'К', 'Л', 'Љ', 'М', 'Н', 'Њ', 'О', 'П', 'Р', 'С', 'Т', 'Ћ', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Џ', 'Ш',
        'а', 'б', 'в', 'г', 'д', 'ђ', 'е', 'ж', 'з', 'и', 'ј', 'к', 'л', 'љ', 'м', 'н', 'њ', 'о', 'п', 'р', 'с', 'т', 'ћ', 'у', 'ф', 'х', 'ц', 'ч', 'џ', 'ш'
    );
    
    $latinic = array(
        'A', 'B', 'V', 'G', 'D', 'Đ', 'E', 'Ž', 'Z', 'I', 'J', 'K', 'L', 'Lj', 'M', 'N', 'Nj', 'O', 'P', 'R', 'S', 'T', 'Ć', 'U', 'F', 'H', 'C', 'Č', 'Dž', 'Š',
        'a', 'b', 'v', 'g', 'd', 'đ', 'e', 'ž', 'z', 'i', 'j', 'k', 'l', 'lj', 'm', 'n', 'nj', 'o', 'p', 'r', 's', 't', 'ć', 'u', 'f', 'h', 'c', 'č', 'dž', 'š'
    );

    return str_replace($cyrillic, $latinic, $text);
}

// General WordPress content
add_filter('the_content', 'cyrillic_to_latinic');
add_filter('the_title', 'cyrillic_to_latinic');
add_filter('the_excerpt', 'cyrillic_to_latinic');
add_filter('widget_text', 'cyrillic_to_latinic');
add_filter('widget_text_content', 'cyrillic_to_latinic');
add_filter('widget_title', 'cyrillic_to_latinic');

// Navigation menus
add_filter('nav_menu_item_title', 'cyrillic_to_latinic');
add_filter('nav_menu_description', 'cyrillic_to_latinic');
add_filter('nav_menu_attribute_title', 'cyrillic_to_latinic');

// WooCommerce content
add_filter('woocommerce_product_title', 'cyrillic_to_latinic');
add_filter('woocommerce_product_description', 'cyrillic_to_latinic');
add_filter('woocommerce_product_short_description', 'cyrillic_to_latinic');
add_filter('woocommerce_product_get_name', 'cyrillic_to_latinic');
add_filter('woocommerce_cart_shipping_method_full_label', 'cyrillic_to_latinic', 9999);
add_filter('woocommerce_cart_totals_shipping_label', 'cyrillic_to_latinic', 9999);
add_filter('woocommerce_checkout_shipping_method_label', 'cyrillic_to_latinic', 9999);
add_filter('woocommerce_shipping_rate_label', 'cyrillic_to_latinic', 9999);

function convert_cyrillic_in_cart_checkout() {
    ob_start();
}
add_action('woocommerce_before_cart', 'convert_cyrillic_in_cart_checkout');
add_action('woocommerce_before_checkout_form', 'convert_cyrillic_in_cart_checkout');

function end_convert_cyrillic_in_cart_checkout() {
    $output = ob_get_clean();
    echo cyrillic_to_latinic($output);
}
add_action('woocommerce_after_cart', 'end_convert_cyrillic_in_cart_checkout');
add_action('woocommerce_after_checkout_form', 'end_convert_cyrillic_in_cart_checkout');
add_filter('woocommerce_shipping_package_name', 'cyrillic_to_latinic', 9999);




// Comments and their meta
add_filter('comment_text', 'cyrillic_to_latinic');
add_filter('comment_excerpt', 'cyrillic_to_latinic');

// Meta data
add_filter('single_post_title', 'cyrillic_to_latinic');
add_filter('single_cat_title', 'cyrillic_to_latinic');
add_filter('single_tag_title', 'cyrillic_to_latinic');
add_filter('single_month_title', 'cyrillic_to_latinic');

// Filters for themes & plugins
add_filter('plugin_row_meta', 'cyrillic_to_latinic');
add_filter('theme_row_meta', 'cyrillic_to_latinic');

function transliterate_translated_strings($translated_text, $original_text, $domain) {
    // Apply our transliteration function
    return cyrillic_to_latinic($translated_text);
}
add_filter('gettext', 'transliterate_translated_strings', 20, 3);
add_filter('ngettext', 'transliterate_translated_strings', 20, 3);

function custom_currency_symbol( $currency_symbol, $currency ) {
    if ( $currency == 'RSD' ) {
        return 'rsd';
    }
    return $currency_symbol;
}
add_filter('woocommerce_currency_symbol', 'custom_currency_symbol', 10, 2);

// And many more filters depending on your needs...
function convert_attribute_dropdown($args) {
    // Convert the 'selected' value
    if (isset($args['selected'])) {
        $args['selected'] = cyrillic_to_latinic($args['selected']);
    }

    // Convert all options
    foreach ($args['options'] as $key => $value) {
        $args['options'][$key] = cyrillic_to_latinic($value);
    }

    return $args;
}
add_filter('woocommerce_dropdown_variation_attribute_options_args', 'convert_attribute_dropdown');

function cyrillic_to_latinic_fragments($fragments) {
    foreach ($fragments as $key => $fragment) {
        $fragments[$key] = cyrillic_to_latinic($fragment);
    }
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'cyrillic_to_latinic_fragments');


// Comments
add_filter('get_comment_author', 'cyrillic_to_latinic');
add_filter('get_comment_text', 'cyrillic_to_latinic');
add_filter('get_comment_excerpt', 'cyrillic_to_latinic');
add_filter('get_comment_date', 'cyrillic_to_latinic');

// For plugins and themes
add_filter('plugin_row_meta', 'cyrillic_to_latinic');
add_filter('theme_row_meta', 'cyrillic_to_latinic');

// For tags and categories
add_filter('list_cats', 'cyrillic_to_latinic');
add_filter('tag_rows', 'cyrillic_to_latinic');

// For admin sections
add_filter('admin_title', 'cyrillic_to_latinic');
add_filter('display_post_states', 'cyrillic_to_latinic');
add_filter('gettext', 'cyrillic_to_latinic', 20, 3);
