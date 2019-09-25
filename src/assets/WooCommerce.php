<?php

namespace ssoFramework\Src\assets;

class WooCommerce {

	static function replaceAddToCartLoop( $args ) {
		global $product;

		echo apply_filters( 'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
			sprintf( '<button href="%s" data-quantity="%s" class="%s" %s>%s</button>',
				esc_url( $product->add_to_cart_url() ),
				esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
				esc_attr( isset( $args['class'] ) ? $args['class'] : 'button ' ),
				isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
				esc_html( $product->add_to_cart_text() )
			),
			$product, $args );
	}

	static function accessoriesProductsMount() {
		global $product;

		$products = get_post_meta( $product->get_id(), '_accessories_ids', true );

		if ( empty( $products ) ) {

			return;
		}

		$accessoriesProducts = [];

		foreach ( $products as $p ) {
			$accessoriesProducts[] = wc_get_product( $p );
		}

		set_transient( 'accessoriesProducts', $accessoriesProducts, 12 * HOUR_IN_SECONDS );
		$products = get_transient( 'accessoriesProducts' );

		if ( $products ) {
			wc_get_template( 'single-product/accessories.php', [
				'accessories_product'  => $product,
				'accessories_products' => $products,
				'quantites_required'   => false,
			] );
		}
	}

		static function variantsProductsMount() {
		global $product;

		if ( $product->is_type( 'variable' ) ) {
            if ( $product ) {
                wc_get_template( 'single-product/variants.php', [
                    'product'  => $product,
                    'variants_products' => ($product->get_available_variations()) ? $product->get_available_variations() : '',
                    'quantites_required'   => false,
                ] );
            }
		}
	}

	static function productImages() {
	    get_template_part('parts/content', 'product-content');
	}

	/**
	 * Let us add productsMount for WooCommerce product overview - to hook in the ReactJS front-end
	 */
	static function productsMount() {
		ob_start(); ?>

        <div class="app-filter"></div>

		<?php
		$content = ob_get_clean();
		echo $content;
	}

	/**
	 * Let us add cartMount for WooCommerce cart - to hook in the ReactJS front-end
	 */
	static function cartMount() {
		ob_start(); ?>

        <div class="app-cart"></div>

		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function miniCartMount() {
		ob_start();
		if (is_user_logged_in()) :
		?>

        <div class="app-mini-cart"></div>

		<?php
		endif;
		$content = ob_get_clean();
		echo $content;
	}

	static function modalMount() {
		ob_start(); ?>

        <div id="modal"></div>

		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function relatedProductsMount() {
		global $product;

		$productRelated = get_post_meta( $product->get_id(), '_custom_related_ids', true );

		if ( ! empty( $productRelated ) ) {
			$defaults = [
				'posts_per_page' => 8,
				'columns'        => 4,
				'orderby'        => 'rand',
				'order'          => 'desc',
			];

			$args            = wp_parse_args( $defaults );
			$relatedProducts = [];

			foreach ( $productRelated as $p ) {
				$relatedProducts[] = wc_get_product( $p );
			}

			set_transient( 'related_products', $relatedProducts, 12 * HOUR_IN_SECONDS );
			$args['related_products'] = get_transient( 'related_products' );

			// Set global loop values. - See documentation for more info about "wc_set_loop".
			wc_set_loop_prop( 'name', 'custom_related' );
			wc_set_loop_prop( 'columns', apply_filters( 'woocommerce_related_products_columns', $args['columns'] ) );

			wc_get_template( 'single-product/related.php', $args );
		} else {

			return woocommerce_output_related_products();

		}
	}

	static function registerRelatedProductFields() {
		global $post;

		$fields = Fields::productRelatedFields();
		Helper::createRelatedProductsField( $post, $fields );
	}

	static function saveRelatedProductFields( $post_id ) {

		$fields = Fields::productRelatedFields();

		foreach ( $fields as $field ) {
			update_post_meta( $post_id, '_' . $field->ID, $_POST[ $field->ID ] );
		}
	}

	static function unsetProductTabs( $tabs ) {

		unset( $tabs['description'] );
		unset( $tabs['additional_information'] );
		unset( $tabs['reviews'] );

		return $tabs;

	}

	static function beforeRowWrapper() {
		ob_start();
		?>
        <div class="grid-container">
        <div class="grid-row">
		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function afterRowWrapper() {
		ob_start();
		?>
        </div>
        </div>
		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function beforeGridWrapper() {
		ob_start();
		?>
        <div class="product-content-right xs-12-cl lg-8-cl">
		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function afterGridWrapper() {
		ob_start();
		?>
        </div>
		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function pllWoocommerceCurrency( $currency ) {
		global $post;
		$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : false;
		if ( ! $lang && is_a( $post, 'WP_Post' ) && function_exists( 'pll_get_post_language' ) ) {
			$lang = pll_get_post_language( $post->ID );
		}

		return $lang ? get_option( "pll_mc_{$lang}_currency", $currency ) : $currency;
	}

	static function productMetaFields() {
		$prefix = 'product';

		$cmb_360_images = new_cmb2_box( [
			'id'           => $prefix . '_360_image_group',
			'title'        => esc_html__( 'Product 360 Images', 'sso-grathwol' ),
			'object_types' => [ 'product' ],
		] );

		$cmb_360_images->add_field( [
			'name' => __( 'Add images for 360 Gallery', 'sso-grathwol' ),
			'id'   => $prefix . '_360_images_files',
			'type' => 'file_list',
		] );

		$product_meta = new_cmb2_box( [
			'id'           => $prefix . '_meta',
			'title'        => esc_html__( 'Product Meta', 'sso-grathwol' ),
			'object_types' => [ 'product' ],
		] );

		$product_meta->add_field( [
			'name' => __( 'Grathwol Title', 'sso-grathwol' ),
			'id'   => $prefix . 'grathwol',
			'type' => 'text',
		] );

		$product_meta->add_field( [
			'name'      => __( 'Video embed link, Ex. Youtube', 'sso-grathwol' ),
			'id'        => $prefix . '_video_link',
			'type'      => 'text_url',
			'protocols' => [ 'http', 'https' ],

		] );

		$post_id = 0;
		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = $product_meta->object_id( absint( $_REQUEST['post'] ) );
		} elseif ( isset( $_REQUEST['post_ID'] ) ) {
			$post_id = $product_meta->object_id( absint( $_REQUEST['post_ID'] ) );
		}

		$file_attachments = new_cmb2_box( [
			'id'           => $prefix . '_file_attachment',
			'title'        => esc_html__( 'File Attachment', 'sso-grathwol' ),
			'object_types' => [ 'product' ],
		] );

		$file_attachment_group_id = $file_attachments->add_field( [
			'id'      => $prefix . '_file_attachment_group',
			'type'    => 'group',
			'options' => [
				'group_title'   => __( 'File {#}', 'sso-grathwol' ),
				'add_button'    => __( 'Add Another File', 'sso-grathwol' ),
				'remove_button' => __( 'Remove File', 'sso-grathwol' ),
				'sortable'      => true,
			],
		] );

		$file_attachments->add_group_field( $file_attachment_group_id, [
			'name'         => __( 'Add File Attachment', 'sso-grathwol' ),
			'id'           => $prefix . '_file_attachments',
			'type'         => 'file',
			// Optional:
			'options'      => [
				'url' => false,
			],
			'text'         => [
				'add_upload_file_text' => 'Add File'
			],
			'query_args'   => [
				'type' => 'application/pdf',
				'type' => [
					'image/gif',
					'image/jpeg',
					'image/png',
				],
			],
			'preview_size' => 'large',
		] );
	}

	static function registerProductVariantsFields( $loop, $variation_data, $variation ) {
		$fields = Fields::productVariantsFields();
		foreach ( $fields as $field ) {
			woocommerce_wp_text_input( [
					'id'    => $field->id . '[' . $loop . ']',
					'class' => 'short',
					'label' => __( $field->label, 'sso-grathwol' ),
					'value' => get_post_meta( $variation->ID, $field->id, true )
				]
			);
		}
	}

	static function addCustomBillingFields( $fields ) {

		$fieldsArgs = Fields::customerBillingFields();

		return array_merge( $fields, $fieldsArgs );
	}

	static function saveVariantsFields( $post_id, $i ) {
		foreach ( Fields::productVariantsFields() as $field ) {
			$meta_field = $_POST[ $field->id ][ $i ];

			if ( isset( $meta_field ) ) {
				update_post_meta( $post_id, $field->id, esc_attr( $meta_field ) );
			}
		}
	}

	static function addWrapperToLoopBefore() {
		ob_start(); ?>
        <div class="caption">
		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function addWrapperToLoopAfter() {
		ob_start(); ?>
        </div>
		<?php
		$content = ob_get_clean();
		echo $content;
	}

    static function product_summary_react()  {

        // Quantity
        $bag          = get_post_meta( get_the_ID(), '_grathwol_bag', true );
        $bag_price      = get_post_meta( get_the_ID(), '_grathwol_bag_price', true );
        $box          = get_post_meta( get_the_ID(), '_grathwol_box', true );
        $box_price      = get_post_meta( get_the_ID(), '_grathwol_box_price', true );
        $pallet       = get_post_meta( get_the_ID(), '_grathwol_pallet', true );
        $pallet_price   = get_post_meta( get_the_ID(), '_grathwol_pallet_price', true );
        $packing      = get_post_meta( get_the_ID(), '_grathwol_packing', true );
        $packing_price  = get_post_meta( get_the_ID(), '_grathwol_packing_price', true );
        $pcs          = get_post_meta( get_the_ID(), '_grathwol_pcs', true );
        $pcs_price      = get_post_meta( get_the_ID(), '_grathwol_pcs_price', true );

        $prices = [ $pcs_price, $box_price, $pallet_price, $packing_price, $bag_price ];

        $prices = array_unique( array_filter( $prices ) );

        $dynamic_pricing_data = get_post_meta( get_the_ID() );

        if (get_theme_mod( 'tooltip_textbox')) {
            $tooltip = get_theme_mod( 'tooltip_textbox');

        } else {
            $tooltip = __('This is a merged product', 'sso-grathwol');
        }

        if ( isset( $dynamic_pricing_data['_grathwol_dynamic_pricing'][0] ) ) {
            $dynamic_pricing_list = maybe_unserialize( $dynamic_pricing_data['_grathwol_dynamic_pricing'][0] );
        }

        $user_specific_price_group = get_post_meta( get_the_ID())['_grathwol_user_specific_pricing_group'];
        $user_specific_price_group_unserialized = maybe_unserialize($user_specific_price_group[0]);

        $user_price = false;
        $matched = false;

        // Determine the key
        if (isset($user_specific_price_group_unserialized)) {
            $matched = array_search(get_current_user_id(), array_column($user_specific_price_group_unserialized, 'customer_id'));
        }

        // If matched
        if ($matched !== false) {
            $user_price = $user_specific_price_group_unserialized[$matched]['customer_discount_price'];
        }

        // Environmental percentage
        $environmental_percentage = get_post_meta( get_the_ID(), 'environmental_price', true);

        // Files
        $files = get_post_meta( get_the_ID(), 'product_file_attachment_group', true );

        $productDataObject = [
            'prices'                => $prices,
            'dynamic_pricing'     => isset( $dynamic_pricing_list ) ? $dynamic_pricing_list : 0,
            'user_specific_price' => $user_price !== false ? (float) $user_price : 0,
            'environmental_percentage' => (float) $environmental_percentage,
            'pcs'                 => ( $pcs && $pcs_price ) ? [
                'price'      => $pcs_price,
                'quantity' => $pcs,
                'locale'   => 'stk'
            ] : false,
            'bag'                 => ( $bag ) ? [
                'price'      => $bag_price,
                'quantity' => $bag,
                'locale'   => 'pose(r)'
            ] : false,
            'box'                 => ( $box ) ? [
                'price'      => $box_price,
                'quantity' => $box,
                'locale'   => 'kasse(r)'
            ] : false,
            'pallet'              => ( $pallet ) ? [
                'price'      => $pallet_price,
                'quantity' => $pallet,
                'locale'   => 'palle(r)'
            ] : false,
            'packing'             => ( $packing ) ? [
                'price'      => $packing_price,
                'quantity' => $packing,
                'locale'   => 'pakke(r)'
            ] : false,
            'locale'              => [], // Optional array of WP language strings
            'files' => isset( $files ) ? $files : array(),
            'tooltip'      => $tooltip
        ]

        ?>

        <script>
        var productDataObject = '<?php echo json_encode( $productDataObject ); ?>';
        </script>
            <div id="product-summary-react-mount" class="product__resume col-12 col-md-4">
                <!-- Contents get injected by React (/dev/js/product-details.js) -->
            </div>
            </div>
        <?php
    }

    static function product_details_react()
    {
        ?>
        <div class="product-wrapper-react-mounts">
            <div id="product-details-react-mount" class="product__resume col-12 col-md-8">
                <!-- Contents get injected by React (/dev/js/product-details.js) -->
            </div>
        <?php
    }

	static function get_current_post_taxonomies() {

		$taxonomy_names = get_object_taxonomies( 'product' );

		return $taxonomy_names;
	}

	static function colorField( $taxonomy ) { ?>
        <div class="form-field term-color-wrap">
            <label for="tag-color"><?php echo __( 'Color', 'sso-grathwol' ); ?></label>
            <input name="tag-color" class="tag-color" id="tag-color" type="text" value="" size="40"
                   aria-required="true"/>
        </div>
		<?php
	}

	static function addColorFieldsToTaxonomies() {
		foreach ( WooCommerce::get_current_post_taxonomies() as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', [ '\ssoFramework\Src\assets\WooCommerce', 'colorField' ] );
		}
	}

	static function save_form_fields( $term_id ) {
		$meta_name = 'tag-color';

		if ( isset( $_POST[ $meta_name ] ) ) {
			$meta_value = $_POST[ $meta_name ];

			$term_metas = get_option( "taxonomy_{$term_id}_metas" );

			if ( ! is_array( $term_metas ) ) {
				$term_metas = Array();
			}
			// Save the meta value
			$term_metas[ $meta_name ] = $meta_value;


			update_option( "taxonomy_{$term_id}_metas", $term_metas );
		}
	}

	static function overridePriceHtml( $price_html, $product ) {

		// Restrict to frontend and on sale only
		if ( is_admin() || ! $product->is_on_sale() ) {
			return $price_html;
		}

		// Variable product type
		if ( $product->is_type( 'variable' ) ) {
			$percentages = array();

			// Get all variation prices
			$prices = $product->get_variation_prices();

			// Iterate over prices
			foreach ( $prices['price'] as $key => $price ) {

				// Only on sale variations
				if ( $prices['regular_price'][ $key ] !== $price ) {

					// Calculate and set percentage for each variation on sale
					$percentages[] = round( 100 - ( $prices['sale_price'][ $key ] / $prices['regular_price'][ $key ] * 100 ), 1 );
				}
			}

			//  If sale prices are variable
			if ( min( $percentages ) !== max( $percentages ) ) {
				$percentage = min( $percentages ) . '-' . max( $percentages ) . '%';
			} else {
				$percentage = max( $percentages ) . '%';
			}
			// Regular products
		} else {
			$regular_price = $product->get_regular_price();
			$sale_price    = $product->get_sale_price();
			$percentage    = round( 100 - ( $sale_price / $regular_price * 100 ), 1 ) . '%';
		}

		return '<div class="product-sale-badge"> - ' . $percentage . '</div>' . $price_html;
	}

	/**
	 * Change the breadcrumb separator
	 */
	static function wcc_change_breadcrumb_delimiter( $defaults ) {
		// Change the breadcrumb delimeter from '/' to '>'
		$defaults['delimiter'] = '<span> &gt; </span>';

		return $defaults;
	}

	static function AddShippingTollSetting( $settings ) {

		$updated_settings = array();

		foreach ( $settings as $section ) {

			// Add to the bottom of the Shipping Options section
			if ( isset( $section['id'] ) && 'shipping_options' == $section['id'] &&
			     isset( $section['type'] ) && 'sectionend' == $section['type'] ) {

				$updated_settings[] = array(
					'name'     => __( 'Toll countries', 'sso-grathwol' ),
					'desc_tip' => __( 'Adds a Toll inclusion for the selected countries.', 'sso-grathwol' ),
					'id'       => 'woocommerce_toll_countries',
					'type'     => 'multi_select_countries',
					'default'  => '',
				);
				$updated_settings[] = array(
					'name'     => __( 'Toll amount', 'sso-grathwol' ),
					'desc_tip' => __( 'Adds a Toll price in addition to the shipping price.', 'sso-grathwol' ),
					'id'       => 'woocommerce_toll_pricing',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'default'  => '220',
				);
			}

			$updated_settings[] = $section;
		}

		return $updated_settings;
	}

	static function woocommerceSingleOpen() {
        global $product;

		if ($product) :  ?>
		<script>
			var product_id = '<?php echo $product->get_ID(); ?>';
		</script>
			<?php
        endif;
	}

	static function get_users() {
        $users[] = '';

        $results = get_users();

        foreach ($results as $u) {
            $users = [
                $u->ID => $u->display_name,
            ];
        }


        return $users;
	}

    static function user_specific_pricing_backend()
    {
        $cmb = new_cmb2_box([
            'id'           => 'user_specific_pricing_options_metabox',
            'title'        => __('User Specific Prices', 'sso-grathwol'),
            'object_types' => ['product',], // Post type
            'context'      => 'normal',
            'priority'     => 'high',
            'show_names'   => true, // Show field names on the left
        ]);

        $user_specific_pricing_group_id = $cmb->add_field([
            'id'         => Helper::prefix() . 'user_specific_pricing_group',
            'type'       => 'group',
            'repeatable' => true,
            'options'    => [
                'group_title'   => 'User Specific Price {#}',
                'add_button'    => 'Add Another User Specific Price',
                'remove_button' => 'Remove User Specific Price',
                'closed'        => true,
                'sortable'      => false,
            ],
        ]);

        $cmb->add_group_field($user_specific_pricing_group_id, [
            'name'             => 'Customer Name',
            'desc'             => 'Select an option',
            'id'               => 'customer_id',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => 'custom',
            'options_cb'       => ['\ssoFramework\Src\assets\WooCommerce', 'get_users']
        ]);


        $cmb->add_group_field($user_specific_pricing_group_id, [
            'name'             => __('Discount Type', 'sso-grathwol'),
            'desc'             => __('Select an option', 'sso-grathwol'),
            'id'               => 'customer_discount_type',
            'type'             => 'select',
            'show_option_none' => true,
            'default'          => '1',
            'options'          => [
                0 => '%',
                1 => 'flat'
            ]
        ]);

        $cmb->add_group_field($user_specific_pricing_group_id, [
            'name'    => __('Price (Price or %)', 'sso-grathwol'),
            'default' => '0',
            'id'      => 'customer_discount_price',
            'type'    => 'text_small'
        ]);

        $cmb->add_group_field($user_specific_pricing_group_id, [
            'name' => __('Related Packing price', 'sso-grathwol'),
            'id'   => 'customer_discount_related_packing_price',
            'type' => 'text'
        ]);

        $cmb->add_group_field($user_specific_pricing_group_id, [
            'name' => __('Packing Type ', 'sso-grathwol'),
            'id'   => 'customer_discount_packing_type',
            'type' => 'text'
        ]);
    }

    static function product_packing()
    {
        $cmb = new_cmb2_box([
            'id'           => 'quantity_options_metabox',
            'title'        => __('Quantity', 'sso-grathwol'),
            'object_types' => ['product',],
            'context'      => 'normal',
            'priority'     => 'high',
            'show_names'   => true,
        ]);
        $cmb->add_field([
            'name' => __('Box', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'box',
            'type' => 'text',
        ]);
        $cmb->add_field([
            'name' => __('Box price/stk', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'box_price',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('PCS', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'pcs',
            'type' => 'text',
        ]);
        $cmb->add_field([
            'name' => __('PCS price/stk', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'pcs_price',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Pallet', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'pallet',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Pallet price/stk', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'pallet_price',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Packing', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'packing',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Packing price/stk', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'packing_price',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Bag', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'bag',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Bag price/stk', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'bag_price',
            'type' => 'text',
        ]);
    }

    static function user_defined_price_account( $items ) {

        $items['user-defined-prices'] = __('User defied Prices', 'sso-grathwol');

        return $items;
    }

    static function rewrite_endpoints()
    {
        add_rewrite_endpoint('user-defined-prices', EP_ROOT | EP_PAGES);
    }

    static function user_defined_price_account_content()
    {

        global $woocommerce;

        $args = [
            'post_type' => 'product',
        ];

        $products     = wc_get_products($args);
        $product_meta = [];

        foreach ($products as $p) {

            $meta = $p->get_meta('_grathwol_user_specific_pricing_group');
            $product_meta[] = [
                'meta' => $meta,
                'id'   => $p->get_id(),
            ];
        }

        $product_meta = json_decode(json_encode($product_meta), false);
        $current_user = get_current_user_id();

        ?>
            <div class="Product">
                <table>
                    <tr>
                        <th>Product Name</th>
                        <th>Special Price</th>
                    </tr>
                    <?php

                    foreach ($product_meta as $p) {

                        $product = wc_get_product($p->id);
                        foreach ($p->meta as $meta) {
                            if (isset($meta->customer_id) && $meta->customer_id == $current_user) {
                                $packing_type   = isset($meta->customer_discount_packing_type) ? $meta->customer_discount_packing_type : '';
                                $customer_price = isset($meta->customer_discount_price) ? $meta->customer_discount_price : '';
                                $packing_price    = isset($meta->customer_discount_related_packing_price) ? $meta->customer_discount_related_packing_price : '';

                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo get_permalink($product->get_id()); ?>"><?php echo $product->get_name(); ?></a>
                                    </td>
                                    <td data-price="<?php echo $packing_price; ?>"><?php echo wc_price($customer_price) . ' ' . $packing_type; ?></td>
                                </tr>
                            <?php
                        }
                    }
                }
                ?>

                </table>
            </div>
        <?php

    }

    static function user_defined_price_account_query_vars($vars)
    {
        $vars[] = 'user-defined-prices';

        return $vars;
    }

    static function relatedProductsColumns( $args ) {
	    $args['columns'] = 3;

        return $args;
    }

    static function upsellsProductsColumns( $args ) {
     $args['columns'] = 3;

     return $args;
    }

    static function addYoastBreacrumbToWC() {
        get_template_part( 'parts/content', 'breadcrumbs' );
    }

	static function createEnvironmentPriceField() {
      $field = array(
        'id' => 'environmental_price',
        'label' => __( 'Environmental Price (%)', 'sso-grathwol' ),
        'data_type' => 'percentage'
      );

      woocommerce_wp_text_input( $field );
    }

    static function saveEnvironmentPriceField( $post_id ) {

      $environmental_field_value = isset( $_POST['environmental_price'] ) ? $_POST['environmental_price'] : '';

      $product = wc_get_product( $post_id );
      $product->update_meta_data( 'environmental_price', $environmental_field_value );
      $product->save();
    }

    static function woocommerce_account_menu_items($items) {
        unset($items['dashboard']);
        // unset($items['orders']);
        unset($items['downloads']);
        // unset($items['edit-address']);
        // unset($items['edit-account']);
        // unset($items['customer-logout']);
        return $items;
    }

    static function redirect_from_dashboard_to_orders($wp) {
	   if ( $wp->request === 'min-konto' && is_user_logged_in() ) {
            wp_redirect( home_url( '/min-konto/orders/' ) );
            exit;
	   }
    }

    static function validate_extra_register_fields( $username, $email, $validation_errors ) {
      if ( isset( $_POST['billing_company'] ) && empty( $_POST['billing_company'] ) ) {
         $validation_errors->add( 'billing_company_error', __( '<strong>Error</strong>: Company name is required!', 'woocommerce' ) );
      }
      if ( !isset( $_POST['accept_contact'] ) || empty( $_POST['accept_contact'] ) ) {
         $validation_errors->add( 'accept_contact_error', __( '<strong>Error</strong>: You should accept to contact!', 'woocommerce' ) );
      }
      return $validation_errors;
    }

    static function save_extra_register_fields( $customer_id ) {
        if ( isset( $_POST['billing_company'] ) ) {
             update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
        }
    }

	static function user_specific_free_shipping_backend() {
        $cmb = new_cmb2_box([
            'id'           => 'user_specific_free_shipping_options_metabox',
            'title'        => __('User Specific Free Shipping', 'sso-grathwol'),
            'object_types' => ['product',],
            'context'      => 'normal',
            'priority'     => 'high',
            'show_names'   => true,
        ]);

        $cmb->add_field([
            'name' => __('Free shipping', 'sso-grathwol'),
            'id'   => Helper::prefix() . 'user_specific_free_shipping',
            'type' => 'multicheck',
            'options_cb' => ['\ssoFramework\Src\assets\WooCommerce', 'get_users']
        ]);
    }

    static function modify_contact_field( $contactmethods ) {
        unset($contactmethods['facebook']);
        unset($contactmethods['instagram']);
        unset($contactmethods['tumblr']);
        unset($contactmethods['youtube']);
        unset($contactmethods['linkedin']);
        unset($contactmethods['myspace']);
        unset($contactmethods['pinterest']);
        unset($contactmethods['soundcloud']);
        unset($contactmethods['twitter']);
        unset($contactmethods['wikipedia']);

        $contactmethods['cvr'] = __( 'CVR' );
        $contactmethods['ean'] = __( 'EAN' );
        $contactmethods['phone'] = __( 'Phone nr.' );

        return $contactmethods;
    }

    function save_contact_field( $user_ID ){
        if ( isset( $_POST['account_phone'] ) ) {
             update_user_meta( $user_ID, 'phone', wc_clean( $_POST['account_phone'] ) );
        }
        if ( isset( $_POST['account_ean'] ) ) {
             update_user_meta( $user_ID, 'ean', wc_clean( $_POST['account_ean'] ) );
        }
        if ( isset( $_POST['account_cvr'] ) ) {
             update_user_meta( $user_ID, 'cvr', wc_clean( $_POST['account_cvr'] ) );
        }
        wp_redirect( home_url( '/min-konto/edit-account/' ) );
        exit;
    }

	static function loadFilters() {
	    add_filter( 'woocommerce_output_related_products_args', [ '\ssoFramework\Src\assets\WooCommerce', 'relatedProductsColumns' ] , 20 );
	    add_filter( 'woocommerce_upsell_display_args', [ '\ssoFramework\Src\assets\WooCommerce', 'upsellsProductsColumns' ], 20 );
		add_filter( 'woocommerce_product_tabs', [ '\ssoFramework\Src\assets\WooCommerce', 'unsetProductTabs' ] );
		add_filter( 'woocommerce_admin_billing_fields', [ '\ssoFramework\Src\assets\WooCommerce', 'addCustomBillingFields' ] );
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
		add_filter( 'woocommerce_currency', [ '\ssoFramework\Src\assets\WooCommerce', 'pllWoocommerceCurrency' ], 999 );
		add_filter( 'woocommerce_product_additional_information_heading', '__return_null' );
		add_filter( 'woocommerce_breadcrumb_defaults', [
			'\ssoFramework\Src\assets\WooCommerce',
			'wcc_change_breadcrumb_delimiter'
		], 999 );
		add_filter( 'woocommerce_get_price_html', [ '\ssoFramework\Src\assets\WooCommerce', 'overridePriceHtml' ], 10, 2 );
		add_filter( 'woocommerce_shipping_settings', [
			'\ssoFramework\Src\assets\WooCommerce',
			'AddShippingTollSetting'
		], 10, 1 );
		add_filter( 'woocommerce_account_menu_items', [ '\ssoFramework\Src\assets\WooCommerce', 'woocommerce_account_menu_items' ], 10 );
		add_filter('user_contactmethods', [ '\ssoFramework\Src\assets\WooCommerce', 'modify_contact_field' ], 10, 1);
	}


	static function loadActions() {
		add_action( 'created_pa_farve',  [ '\ssoFramework\Src\assets\WooCommerce', 'save_form_fields' ], 10, 2 );
		add_action( 'admin_head', [ '\ssoFramework\Src\assets\WooCommerce', 'addColorFieldsToTaxonomies' ] );
		add_action( 'woocommerce_after_shop_loop_item_title', [
			'\ssoFramework\Src\assets\WooCommerce',
			'replaceAddToCartLoop'
		], 15 );
		add_action( 'woocommerce_shop_loop_item_title', [
			'\ssoFramework\Src\assets\WooCommerce',
			'addWrapperToLoopBefore'
		], 1 );
		add_action( 'woocommerce_after_shop_loop_item_title', [
			'\ssoFramework\Src\assets\WooCommerce',
			'addWrapperToLoopAfter'
		], 11 );
		add_action( 'woocommerce_before_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'productImages'
		], 20 );
		add_action( 'woocommerce_product_options_related', [
			'\ssoFramework\Src\assets\WooCommerce',
			'registerRelatedProductFields'
		] );
		add_action( 'woocommerce_process_product_meta', [
			'\ssoFramework\Src\assets\WooCommerce',
			'saveRelatedProductFields'
		] );
		add_action( 'woocommerce_after_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'relatedProductsMount'
		], 20 );
		add_action( 'woocommerce_after_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'accessoriesProductsMount'
		], 30 );
		add_action( 'woocommerce_after_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'variantsProductsMount'
		], 30 );
		add_action( 'woocommerce_before_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'beforeRowWrapper'
		], 1 );
		add_action( 'woocommerce_after_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'afterRowWrapper'
		], 30 );
		add_action( 'woocommerce_before_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'beforeGridWrapper'
		], 40 );
		add_action( 'woocommerce_after_single_product_summary', [
			'\ssoFramework\Src\assets\WooCommerce',
			'afterGridWrapper'
		], 12 );
		add_action( 'init', function () {
			register_taxonomy( 'product_tag', 'product', [
				'public'            => false,
				'show_ui'           => false,
				'show_admin_column' => false,
				'show_in_nav_menus' => false,
				'show_tagcloud'     => false,
			] );
		}, 100 );
		add_action( 'admin_init', function () {
			add_filter( 'manage_product_posts_columns', function ( $columns ) {
				unset( $columns['product_tag'] );

				return $columns;
			}, 100 );
		} );
		add_action( 'cmb2_admin_init', [ '\ssoFramework\Src\assets\WooCommerce', 'productMetaFields' ] );
		add_action( 'woocommerce_save_product_variation', [
			'\ssoFramework\Src\assets\WooCommerce',
			'saveVariantsFields'
		], 10, 2 );
		add_action( 'woocommerce_variation_options', [
			'\ssoFramework\Src\assets\WooCommerce',
			'registerProductVariantsFields'
		], 10, 3 );
		add_action( 'after_footer_mount', [ '\ssoFramework\Src\assets\WooCommerce', 'modalMount' ], 1 );
		add_action( 'after_service_menu', [ '\ssoFramework\Src\assets\WooCommerce', 'miniCartMount' ], 1 );
		add_action( 'products_mount', [ '\ssoFramework\Src\assets\WooCommerce', 'productsMount' ], 1 );
		add_action( 'mount_after_content', [ '\ssoFramework\Src\assets\WooCommerce', 'cartMount' ], 1 );
		add_action('woocommerce_single_product_summary', ['\ssoFramework\Src\assets\WooCommerce', 'product_details_react'], 15);
		add_action('woocommerce_single_product_summary', ['\ssoFramework\Src\assets\WooCommerce', 'product_summary_react'], 20);
		add_action('wp_head', ['\ssoFramework\Src\assets\WooCommerce', 'woocommerceSingleOpen']);
		add_action('cmb2_admin_init', ['\ssoFramework\Src\assets\WooCommerce', 'user_specific_pricing_backend']);
		add_action('cmb2_admin_init', ['\ssoFramework\Src\assets\WooCommerce', 'user_specific_free_shipping_backend']);
        add_action('cmb2_admin_init', ['\ssoFramework\Src\assets\WooCommerce', 'product_packing']);
        add_action('init', ['\ssoFramework\Src\assets\WooCommerce', 'rewrite_endpoints']);
        add_action('woocommerce_account_user-defined-prices_endpoint', [
            '\ssoFramework\Src\assets\WooCommerce',
            'user_defined_price_account_content'
        ]);
        add_action( 'woocommerce_before_main_content', ['\ssoFramework\Src\assets\WooCommerce', 'addYoastBreacrumbToWC'], 20 );
		add_action( 'woocommerce_product_options_pricing', [ '\ssoFramework\Src\assets\WooCommerce', 'createEnvironmentPriceField' ] );
        add_action( 'woocommerce_process_product_meta', [ '\ssoFramework\Src\assets\WooCommerce', 'saveEnvironmentPriceField' ] );
        add_action( 'parse_request', [ '\ssoFramework\Src\assets\WooCommerce', 'redirect_from_dashboard_to_orders' ], 10, 1  );
        add_action( 'woocommerce_register_post', [ '\ssoFramework\Src\assets\WooCommerce', 'validate_extra_register_fields' ], 10, 3 );
        add_action( 'woocommerce_created_customer', [ '\ssoFramework\Src\assets\WooCommerce', 'save_extra_register_fields' ] );
        add_action( 'woocommerce_save_account_details', [ '\ssoFramework\Src\assets\WooCommerce', 'save_contact_field' ] );
	}
}
