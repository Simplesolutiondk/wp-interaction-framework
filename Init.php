<?php

namespace ssoFramework;

require_once __DIR__ . '/src/cmb2/init.php';
require_once __DIR__ . '/src/cmb2/cmb2-post-search-field/cmb2_post_search_field.php';

use ssoFramework\src\Assets\Helper;
use ssoFramework\src\Assets\Widgets;


class Init {

	static function load() {
	}

	static function theme_setup() {
		add_action( 'after_setup_theme', function () {
			add_theme_support( 'title-tag' );

			add_theme_support( 'custom-logo', [
				'flex-width'  => true,
				'flex-height' => true,
				'header-text' => false
			] );

			add_theme_support( 'html5', [
				'search-from' => true
			] );

			add_theme_support( 'post-formats', array( 'video' ) );


			add_theme_support( 'post-thumbnails' );


			$widgetArgs = array_merge( Widgets::initWidgetsFooter(), Widgets::initWidgetsTopFooter(), Widgets::initWidgetsBlog(), Widgets::initWidgetsArtist(), Widgets::initWidgetsTopFooterDescription() );
			if ( ! empty ( $widgetArgs ) ) // Loop over all names of array and add as widget to footer in backend
			{
				foreach ( $widgetArgs as $widgetKey => $widgetValue ) {
					Helper::widget( $widgetKey, $widgetValue['class'] );
				}
			}
			// Register nav menu locations
			register_nav_menu( 'main-menu', 'Main Menu' );
			register_nav_menu( 'top-menu', 'Top Menu' );

			load_theme_textdomain( 'sso-showbizz', get_template_directory() . '/languages' );
		} );
	}
}
