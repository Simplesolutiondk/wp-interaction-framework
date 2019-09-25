<?php

namespace ssoFramework\Src\Api;


class MenuEndpoints {

	static function getMenuItems($request) {


        $params     = $request->get_params();
        $location   = $params['location'];
        $locations = get_nav_menu_locations();

		$menu_name = wp_get_nav_menu_object( $locations[$location] );
		$menu_items = wp_get_nav_menu_items($menu_name->name);

		set_transient( 'wp_nav_menu_items', $menu_items, 60 * 60 * 12 );

		return get_transient( 'wp_nav_menu_items' );
	}

	static function loadActions() {
		add_action( 'rest_api_init', function () {
			register_rest_route( 'wp/v2', '/menu/(?P<location>[a-zA-Z0-9_-]+)', array(
				'methods'  => 'GET',
				'callback' => [ '\ssoFramework\Src\Api\MenuEndpoints', 'getMenuItems' ],
			) );
		} );

	}

}
