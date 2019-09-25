<?php

namespace ssoFramework\Src\assets;

use ssoFramework\Src\assets\Helper;

class Enqueue {

	static function style( $alias, $src, $deps, $ver = false, $screen ) {
		return wp_enqueue_style( $alias, Helper::assets( 'prod/css' ) . $src, $deps, $ver, $screen );
	}

	static function script( $alias, $src, $deps, $ver = false, $footer ) {
		return wp_enqueue_script( $alias, Helper::assets( 'prod/js' ) . $src, $deps, $ver, $footer );
	}

	static function css() {
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', function () {
				self::style( 'MainCss', 'main.css', [], false, false );
			} );
		}
	}

	static function js() {
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', function () {
				self::script( 'mainJs', 'theme.js', ['wp-i18n'], '0.0.1', true );
				wp_set_script_translations( 'mainJs', 'sso-grathwol', get_template_directory() . '/languages' );
			} );
		}
	}

	static function adminActions() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'adminJss', Helper::assets( 'dev/js' ) . 'admin.js', [ 'wp-color-picker' ], false, true );
	}

	static function loadActions() {
		add_action( 'admin_enqueue_scripts', [ '\ssoFramework\Src\assets\Enqueue', 'adminActions' ] );
	}

}
