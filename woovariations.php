<?php
/**
* Plugin Name: WooVariations
* Plugin URI: http://wp-lessons.com/woovariations
* Description: Add opportunity to choose product variations on category/product list page with "Add to cart" button.
* Version: 1.0
* Author: Flaeron
* Author URI: http://wp-lessons.com/
*/

/*  Copyright 2015 Flaeron  (email : d.flaeron@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Add css styles
add_action('wp_enqueue_scripts', 'add_woovariations_custom_styles');

function add_woovariations_custom_styles() {
	wp_enqueue_style ('css-style', plugins_url('css/styles.css', __FILE__));
}

// Settings
function woovariations_settings_page() {
    add_settings_section("woovariations-section", "", null, "woovariations-settings");
    add_settings_field("woovariations-checkbox", "Options:", "woovariations_checkbox_display", "woovariations-settings", "woovariations-section"); 
    register_setting("woovariations-section", "wv-active-plugin-option");
	register_setting("woovariations-section", "wv-quantity-option");
	register_setting("woovariations-section", "wv-meta-option");
}

// Add checkbox
function woovariations_checkbox_display() {
	?>
		<input type="checkbox" name="wv-active-plugin-option" value="1" <?php checked(1, get_option('wv-active-plugin-option'), true); ?> /> Active plugin<br />
        <input type="checkbox" name="wv-quantity-option" value="1" <?php checked(1, get_option('wv-quantity-option'), true); ?> /> Hide quantity<br />
		<input type="checkbox" name="wv-meta-option" value="1" <?php checked(1, get_option('wv-meta-option'), true); ?> /> Hide product meta (SKU&Category)	
	<?php
}

// Option page
add_action("admin_init", "woovariations_settings_page");

function woovariations_options_page() {
	?>
	<div class="wrap">
		<h1>WooVariations</h1>
		<p>Add opportunity to choose product variations on category/product list page with "Add to cart" button.</p>
		<p>Plugin work correct only with simple themes that don't overwrite the file <b>content-product.php</b></p>
		<form method="post" action="options.php">
            <?php
               settings_fields("woovariations-section");
               do_settings_sections("woovariations-settings");
               submit_button();
            ?>
		</form>
	</div>
	<?php
}

// Add submenu item
function woovariations_menu_item() {
  add_submenu_page("woocommerce", "WooVariations", "WooVariations", "manage_options", "woovariations", "woovariations_options_page"); 
}
 
add_action("admin_menu", "woovariations_menu_item");

// If wv-active-plugin-option checked
if( get_option('wv-active-plugin-option') ) {
	// Get path for templates used in loop
	add_filter( 'wc_get_template_part', function( $template, $slug, $name ) 
	{ 
		// Look in plugin for content-product.php
		if ( $name ) {
			$path = plugin_dir_path( __FILE__ ) . WC()->template_path() . "{$slug}-{$name}.php";    
		} 

		return file_exists( $path ) ? $path : $template;

	}, 10, 3 );	
}

// If wv-quantity-option checked
if( get_option('wv-quantity-option') ) {
	// Remove WooCommerce quantity name only on archive (shop)
	function sv_remove_archive_page_quantity( $enabled ) {
		if ( is_shop() ) {
			return true;
		}

		return $enabled;
	}
	add_filter( 'woocommerce_is_sold_individually', 'sv_remove_archive_page_quantity' );
}

// If wv-meta-option checked
if( get_option('wv-meta-option') ) {
	// Remove WooCommerce meta only on archive (shop)
	add_action('wp_footer', 'wv_custom_admin_styles');

	function wv_custom_admin_styles() {
	  echo '<style>
			.woocommerce ul.products .product_meta {
				display: none;
			}
	  </style>';
	}
}