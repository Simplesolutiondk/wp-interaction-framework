<?php

namespace ssoFramework\Src\assets;

use WC_Frontend_Scripts;

class Removable {
	static function woocommerce() {
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		remove_action( 'wp_enqueue_scripts', [ WC_Frontend_Scripts::class, 'load_scripts' ] );
		remove_action( 'wp_print_scripts', [ WC_Frontend_Scripts::class, 'localize_printed_scripts' ], 5 );
		remove_action( 'wp_print_footer_scripts', [ WC_Frontend_Scripts::class, 'localize_printed_scripts' ], 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	}

	static function removeActions() {
		self::woocommerce();
	}
}
