<?php

namespace SimpleSolution\WpInteractionFramework\Theme;

use \SimpleSolution\WpInteractionFramework\Helper;

class Init extends \SimpleSolution\WpInteractionFramework\Init {

	static function setup() {
		add_action( 'after_setup_theme', function () {
			add_theme_support( 'title-tag' );

			add_theme_support( 'custom-logo', [
				'flex-width'  => true,
				'flex-height' => true,
				'header-text' => false
			] );

			add_theme_support( 'align-wide' );


			add_theme_support( 'html5', [
				'search-from' => true
			] );

			add_theme_support( 'post-formats', array( 'video' ) );


			add_theme_support( 'post-thumbnails' );


			$widgetArgs = array_merge( Widgets::initWidgetsFooter(), Widgets::initWidgetsTopFooter(), Widgets::initSidebars() );
			if ( ! empty ( $widgetArgs ) ) // Loop over all names of array and add as widget to footer in backend
			{
				foreach ( $widgetArgs as $widgetKey => $widgetValue ) {
					Helper::widget( $widgetKey, $widgetValue['class'] );
				}
			}
			// Register nav menu locations
			register_nav_menu( 'main-menu', 'Main Menu' );
			register_nav_menu( 'main-menu-right', 'Main Menu Right' );
			register_nav_menu( 'top-menu', 'Top Menu' );

			load_theme_textdomain( 'sso-default', get_template_directory() . '/languages' );
		} );
	}

}
