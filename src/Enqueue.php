<?php

namespace SimpleSolution\WpInteractionFramework;

class Enqueue {

	static function style( $alias, $src, $deps, $ver = false, $screen ) {
		return wp_enqueue_style( $alias, get_template_directory_uri() . $src, $deps, $ver, $screen );
	}

	static function script( $alias, $src, $deps, $ver = false, $footer ) {
		return wp_enqueue_script( $alias, get_template_directory_uri() . $src, $deps, $ver, $footer );
	}

	static function adminActions() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'adminJss', Helper::assets( 'dev/js' ) . 'admin.js', [ 'wp-color-picker' ], false, true );
	}

	static function loadActions() {
		add_action( 'admin_enqueue_scripts', [ $this, 'adminActions' ] );
	}

}
