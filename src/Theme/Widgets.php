<?php

namespace SimpleSolution\WpInteractionFramework\Theme;
//TODO: Add ability to apply_filter so we can manipulate with columns via. the theme.
class Widgets {

	static function initWidgetsFooter() {
		$widgets = [
			'Footer Widget 1' => [
				'class' => 'xs-12-cl lg-3-cl footer__widget'
			],
			'Footer Widget 2' => [
				'class' => 'xs-12-cl lg-3-cl footer__widget'
			],
			'Footer Widget 3' => [
				'class' => 'xs-12-cl lg-3-cl footer__widget'
			],
			'Footer Widget 4' => [
				'class' => 'xs-12-cl lg-3-cl footer__widget'
			],
		];

		return $widgets;
	}

	static function initWidgetsTopFooter() {

		$widgets = [
			'Footer Top Widget 1' => [
				'class' => 'xs-12-cl lg-6-cl footer__widget'
			],
			'Footer Top Widget 2' => [
				'class' => 'xs-12-cl lg-3-cl footer__widget'
			],
			'Footer Top Widget 3' => [
				'class' => 'xs-12-cl lg-2-cl footer__widget'
			],
			'Footer Top Widget 4' => [
				'class' => 'xs-12-cl lg-1-cl footer__widget'
			],
		];

		return $widgets;
	}


	static function initSidebars() {
		$widgets = [
			'Page Sidebar'        => [
				'class' => ''
			],
			'Page Sidebar Bottom' => [
				'class' => ''
			],
			'Archive Sidebar'        => [
				'class' => ''
			],
			'Archive Sidebar Bottom' => [
				'class' => ''
			]
		];

		return $widgets;
	}
}
