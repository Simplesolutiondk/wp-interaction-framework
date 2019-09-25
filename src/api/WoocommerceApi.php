<?php

namespace ssoFramework\Src\api;

use ssoFramework\Src\assets\Fields;
use ssoFramework\Src\assets\Helper;

class WoocommerceApi {

	static function addVariantsFieldsToRest() {
		foreach ( Fields::productVariantsFields() as $field ) {
			register_rest_field( 'product', $field->id, array(
				"get_callback" => function ( $object ) use ( $field ) {
					set_transient( 'register_variants_fields_' . $field->id, get_post_meta( $object['id'], $field->id, true ), 60 * 60 * 12 );

					return get_transient( 'register_variants_fields_' . $field->id );
				}
			) );
		}
	}

	static function addRelatedProductFieldsToRest() {
		foreach ( Fields::productRelatedFields() as $field ) {
			register_rest_field( 'product', $field->ID, array(
				"get_callback" => function ( $object ) use ( $field ) {
					set_transient( 'register_related_fields_' . $field->ID, get_post_meta( $object['id'], '_' . $field->ID, true ), 60 * 60 * 12 );

					return get_transient( 'register_related_fields_' . $field->ID );
				}
			) );
		}
	}

	static function registerProductImagesCustom() {
		register_Rest_field( 'product', 'product_images_custom', array(
			'get_callback' => function ( $object ) {
				return self::populateProductImages( $object );
			},
			'schema'       => null,
		) );
	}

	static function populateProductImages( $object ) {

		$product        = new \WC_product( $object['id'] );
		$attachment_ids = $product->get_gallery_image_ids();
		$images         = array();

        if ( has_post_thumbnail( $product->id ) ) {
            $attachment_ids[0] = get_post_thumbnail_id( $product->id );
            $product_featured_image = wp_get_attachment_image_src($attachment_ids[0], 'full' ); 
        }

		foreach ( $attachment_ids as $pictures ) {
			$images[] = [
				'thumbnail'     => wp_get_attachment_image_src( $pictures, 'thumbnail' )[0],
				'medium'        => wp_get_attachment_image_src( $pictures, 'medium' )[0],
                'full'          => wp_get_attachment_image_src( $pictures, 'full' )[0],
                'product_featured_image'          => $product_featured_image[0],
			];
		}

		return $images;

	}

	static function shippingCalculation() {
		register_rest_route( 'wc/v3', '/calc_shipping', array(
			'methods'  => 'POST',
			'callback' => __CLASS__ . '::calculateShippingCosts',
		) );
	}
    
    static function calculateShippingCosts($request) {

        global $wpdb;
		global $woocommerce;

        // For the ORDER get matching rates
        $shipping_class = 0;
        $price          = 0;
        $weight         = 0;
        $count          = 0;
        $count_in_class = 0;
        
        $data = $request->get_params();
        
        $products = $data['products'];
        $country = $data['country'];
        $zipcode = $data['zipcode'];
        $language = (string) $_COOKIE['pll_language'];
        
        if (!is_array($products)) {
            return array('params' => null);
        }

        $matching_rates = array();

        foreach ($products as $product) {
            $_product = wc_get_product($product['product_id']);

            if ( $_product->needs_shipping() ) {
                $rates = self::has_rates($_product->get_shipping_class_id());
                
                if (is_array($rates) && count($rates) > 0) {
                    $weight += (float) $_product->get_weight() * (float) $product['quantity'];
                    $matching_rates = array_merge($matching_rates, $rates);
                }
            }
        }

        
        // Weight based
        if ($weight > 0) {

            // Create comparable ranges
            $filtered = array_filter(array_map(function($match) use ($weight) { 
                return in_array($weight, range((int)$match->rate_min, (int)$match->rate_max)); 
            }, $matching_rates));
            
            // Extract keys
            $filtered_keys = array_keys($filtered);

            // Out of range
            if (!$filtered_keys && count($matching_rates) > 0) {
                
                usort($matching_rates, function ($a, $b) {
                    return $b->rate_cost - $a->rate_cost;
                });
                
                $rate = (object) array();
                
                // Highest costs
                $rate->rate_cost = $matching_rates[0]->rate_cost;

            } else {
                $rate = $matching_rates[$filtered_keys[0]];
            }
            
            $type = 'flat';
            $droppoint = 0;
            $duty = false;
            
            $eu_countries = array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' );

            
            // DK Based
            if ($country == 'DK') {

                if ($weight <= 5) {
                    $rate->rate_cost = 95;
                    $rate->rate_condition = 'Light goods DK GLS';
                    $droppoint = 1;
                    $type = 'gls';
                } elseif ($weight > 5 && $weight < 20) {
                    $rate->rate_cost = 199;
                    $rate->rate_condition = 'Light goods DK';
                } else {
                    $rate->rate_cost = 285;
                    $rate->rate_condition = 'Heavy goods DK';
                }
            // EU Countries
            } elseif (in_array($country, $eu_countries)) {

                $rate->rate_cost = 100;
                $rate->rate_condition = 'Heavy goods EU';
                
            // Norway and GB pay extra toll
            } elseif (in_array($country, array('UK', 'NO'))) { 
                $rate->rate_cost = 100 + 220;
                $rate->rate_condition = 'Heavy goods plus Toll EU';
                $duty = 220;
                
            // Everything else
            } else {
                $rate->rate_cost = 0;
                $rate->rate_condition = 'Contact Grathwol for shipping options';
                $type = 'custom';

            }

            $shipping_rate[] = array(
                'id' => 'weight',
                'label'   => __( ucFirst($rate->rate_condition), 'woocommerce-weight-rate-shipping' ),
                'cost'    => $rate->rate_cost,
                'type'    => $type,
                'droppoint' => $droppoint,
                'duty' => $duty
            );
            
        } else {
            
             $duty = false;
             $shipping_rate[] = array(
                'id' => 'regular',
                'label'   => __( ($country == 'DK') ? 'DK Shipping' : 'Flatrate Shipping', 'woocommerce-weight-rate-shipping' ),
                'cost'    => 100,
                'type' => 'flat',
                'droppoint' => 0,
                'duty' => $duty
            );
        }
   
        return array('params' => $shipping_rate);
    }
    
    static function has_rates($shipping_class_id) {
        
        global $wpdb;
        
        $rates_table = $wpdb->prefix . 'woocommerce_shipping_table_rates';
        
        $shipping_class_id_in = " WHERE rate_class IN ( '', '" . absint( $shipping_class_id ) . "' )";

        $has_rates = $wpdb->get_results("
            SELECT *
            FROM {$rates_table}
            {$shipping_class_id_in}
		");

        return $has_rates;
    }

	static function getBillingFieldsRest( $response, $object, $request ) {

		if ( empty( $response->data ) ) {
			return $response;
		}

		$orderId                                 = $object->get_id();
		$cvrNumber                               = get_post_meta( $orderId, '_billing_cvr_number', true );
		$response->data['billing']['cvr_number'] = $cvrNumber;

		return $response;
	}

	static function loadFilters() {
		add_filter( "woocommerce_rest_prepare_shop_order_object", [
			'\ssoFramework\Src\api\WoocommerceApi',
			'getBillingFieldsRest'
		], 10, 3 );
	}

	static function loadActions() {
		add_action( 'rest_api_init', [ '\ssoFramework\Src\api\WoocommerceApi', 'addVariantsFieldsToRest' ] );
		add_action( 'rest_api_init', [ '\ssoFramework\Src\api\WoocommerceApi', 'registerProductImagesCustom' ] );
		add_action( 'rest_api_init', [ '\ssoFramework\Src\api\WoocommerceApi', 'addRelatedProductFieldsToRest' ] );
		add_action( 'rest_api_init', [ '\ssoFramework\Src\api\WoocommerceApi', 'shippingCalculation' ] );
	}
}