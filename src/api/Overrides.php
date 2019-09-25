<?php

namespace ssoFramework\Src\api;

class Overrides {
	static function create_api_posts_meta_field() {
		register_rest_field( 'product', '360_images', array(
				'get_callback' => function ( $object ) {

					$post_id = $object['id'];

					global $wpdb;

					$querystr = "SELECT * FROM {$wpdb->postmeta} WHERE post_id = {$post_id} AND meta_key = 'product_360_images_files'";

					$result = $wpdb->get_results( $querystr, OBJECT );

					if ( $result ) {
						$data = $result[0]->meta_value;

						$data     = maybe_unserialize( $data );
						$newArray = [];


						foreach ( $data as $data ) {
							$newArray[] = [
								'src' => $data,
							];
						}

						return $newArray;
					} else {
						return [];
					}

				},
				'schema'       => null,
			)
		);
	}
}
