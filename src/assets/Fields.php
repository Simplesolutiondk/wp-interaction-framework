<?php

namespace ssoFramework\Src\Assets;

use ssoFramework\Src\Assets\Helper;

class Fields {

	static function productVariantsFields() {
		$fields = [
			[
				'id'    => 'variable_title',
				'label' => 'Title',
			],
			[
				'id'    => 'variable_stock_message',
				'label' => 'Stock message',
			]
		];

		return Helper::arrayToObject( $fields );
	}

	static function productRelatedFields() {
		$fields = [
			[
				'ID'   => 'accessories_ids',
				'name' => 'Accessories',
			],
			[
				'ID'   => 'custom_related_ids',
				'name' => 'Related Products',
			],
		];

		return Helper::arrayToObject( $fields );
	}

	static function customerBillingFields() {
		// Underscore should be used to hide it from the backend meta dropdown selector.
		$fields = [
			'cvr_number' => [
				'label' => __( 'CVR Number', 'sso-grathwol' ),
				'show'  => true,
			],
		];

		return $fields;
	}
}
