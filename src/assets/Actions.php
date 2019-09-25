<?php

namespace ssoFramework\Src\assets;

use ssoFramework\Src\assets\Fields;
use ssoFramework\Src\assets\Helper;
use ssoFramework\Src\assets\Tabs;

class Actions {
	static function addMenuSubpage() {
		Helper::register_options_page( 'product', 'Properties Options' );
	}

	static function searchMount() {
		ob_start(); ?>

        <div class="app-search"></div>

		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function addBreadcrumbs() {

		$showBreadcrumbs = get_post_meta( get_the_ID(), 'page_show_breadcrumbs', 1 );

		if ( $showBreadcrumbs || is_singular( 'post' ) ) {
			get_template_part( 'parts/content', 'breadcrumbs' );
		}

	}

	static function favoriteListMount() {

		if (is_user_logged_in() && !is_page( 'kurv' ) && !is_page( 'tak-for-din-ordre' )) :
		 echo '<div class="app-favoritlist-mount"></div>';
		endif;
	}

	static function pageFields() {
		$prefix = 'page';

		$cmb = new_cmb2_box( [
			'id'           => $prefix . 'alternative_fields',
			'title'        => __( 'Alternative Options', 'sso-grathwol' ),
			'object_types' => [ 'page' ],
			'show_names'   => true,
		] );

		$cmb->add_field( [
			'name' => __( 'Breadcrumbs', 'sso-grathwol' ),
			'desc' => __( 'Shows breadcrumbs on page if enabled.', 'sso-grathwol' ),
			'id'   => $prefix . '_show_breadcrumbs',
			'type' => 'checkbox',
		] );
	}

	static function addThemeClass( $classes, $class, $post_id ) {
		global $product, $woocommerce_loop;

		if ( is_product() && ! $woocommerce_loop['name'] == 'related' ) {
			$classes[] = 'copy';
		} elseif ( $woocommerce_loop['name'] == 'related' || $woocommerce_loop['name'] == 'up-sells' || $woocommerce_loop['name'] == 'custom_related' ) {
			if ( $woocommerce_loop['columns'] == 4 ) {
				$classes[] = 'copy xs-12-cl sm-12-cl lg-3-cl';
			} elseif ( $woocommerce_loop['columns'] == 3 ) {
				$classes[] = 'copy xs-12-cl sm-12-cl lg-4-cl';
			} elseif ( $woocommerce_loop['columns'] == 2 ) {
				$classes[] = 'copy xs-12-cl sm-12-cl lg-6-cl';
			}
		}

		return $classes;
	}

	static function mainNavigationMount() {
		ob_start(); ?>

        <div id="menu-react-mount"></div>

		<?php
		$content = ob_get_clean();
		echo $content;
	}

	static function contactInformation() {
		ob_start(); ?>

        <div class="service-menu-contact">
            <ul>
                <li class="contact-item"><a href="tel:+45 4344 1314"><span>Telefon: </span>+45 4344 1314</a></li>
                <li class="contact-item-seperator"><p>|</p></li>
                <li class="contact-item"><a href="mailto:info@grathwol.dk"><span>Email: </span>info@grathwol.dk</a></li>
            </ul>
        </div>

		<?php
		$content = ob_get_clean();
		echo $content;
	}

	/**
	 * Register options page for WooCommerce Taxonomies
	 */
	static function propertyOptionsPage() {

		$cmb_options = new_cmb2_box( [
			'id'           => 'myprefix_option_metabox',
			'title'        => esc_html__( 'Properties Options', 'sso-grathwol' ),
			'object_types' => [ 'options-page' ],
			'option_key'   => 'properties_options',
			'capability'   => 'manage_options',
		] );

		$group_field_id = $cmb_options->add_field( [
			'id'      => 'property_repeat_group',
			'type'    => 'group',
			'options' => [
				'group_title'   => __( 'Property {#}', 'sso-grathwol' ),
				'add_button'    => __( 'Add Another Property', 'sso-grathwol' ),
				'remove_button' => __( 'Remove Property', 'sso-grathwol' ),
				'sortable'      => true,
			],
		] );

		$cmb_options->add_group_field( $group_field_id, [
			'name'        => __( 'Label', 'sso-grathwol' ),
			'description' => __( 'Label/Name for the property', 'sso-grathwol' ),
			'id'          => 'label',
			'type'        => 'text',
		] );

		$cmb_options->add_group_field( $group_field_id, [
			'name'        => __( 'Post Type', 'sso-grathwol' ),
			'description' => __( 'Post Type is inherited from the current Post Type', 'sso-grathwol' ),
			'id'          => 'post_type',
			'type'        => 'text',
			'default'     => isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : '',
		] );
	}

	static function propertiesGetOption( $key = '', $default = false ) {
		if ( function_exists( 'cmb2_get_option' ) ) {
			return cmb2_get_option( 'properties_options', $key, $default );
		}

		$opts = get_option( 'properties_options', $default );

		$val = $default;

		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}

	static function pllAddForm( $lang = null ) {
		?>
        <div class="form-field">
            <label for="pll_mc_wc_currency"><?php esc_html_e( 'WooCommerce Currency', 'polylang' ); ?></label><?php
			printf(
				'<input name="pll_mc_wc_currency" id="pll_mc_wc_currency" type="text" value="%s" />',
				! empty( $lang->slug ) ? esc_attr( get_option( "pll_mc_{$lang->slug}_currency", '' ) ) : ''
			); ?>
            <p><?php esc_html_e( 'Set the WooCommerce currency for the language', 'polylang' ); ?></p>
        </div>
		<?php
	}

	static function pllSaveForm() {
		$action = isset( $_POST['pll_action'] ) ? $_POST['pll_action'] : '';

		if ( 'update' === $action && ! empty( $_POST['slug'] ) ) {
			$lang     = $_POST['slug'];
			$currency = isset( $_POST['pll_mc_wc_currency'] ) ? $_POST['pll_mc_wc_currency'] : '';

			$lang_slugs = [];
			foreach ( PLL()->model->get_languages_list() as $single_lang ) {
				$lang_slugs[] = $single_lang->slug;
			}

			//Language valid
			if ( in_array( $lang, $lang_slugs ) ) {
				update_option( "pll_mc_{$lang}_currency", $currency );
			}
		}
	}

	static function pllRemoveMetadataSync( $to_copy, $sync, $from, $to, $lang ) {
		$remove_fields = [
			'_max_price_variation_id',
			'_max_regular_price_variation_id',
			'_max_sale_price_variation_id',
			'_max_variation_price',
			'_max_variation_regular_price',
			'_max_variation_sale_price',
			'_min_price_variation_id',
			'_min_regular_price_variation_id',
			'_min_sale_price_variation_id',
			'_min_variation_price',
			'_min_variation_regular_price',
			'_min_variation_sale_price',
			'_regular_price',
			'_sale_price',
			'_sale_price_dates_from',
			'_sale_price_dates_to',
			'_tax_class',
			'_tax_status',
			'_price',
			'_tax_class',
			'_tax_status',
			'_visibility',
		];

		foreach ( $remove_fields as $key_num => $key_to_remove ) {
			if ( ( $key = array_search( $key_to_remove, $to_copy ) ) !== false ) {
				unset( $to_copy[ $key ] );
			}
		}

		return $to_copy;
	}

	static function ctaButtonHeader() {

		ob_start(); ?>
        <div class="cta-button-header">
            <a href="/kontakt/" class="btn btn--primary">Kontakt</a>
        </div>

		<?php
		$content = ob_get_clean();
		echo $content;

	}

	static function registerTaxonomies() {

		$args = Actions::propertiesGetOption( 'property_repeat_group' );

		set_transient( 'args', $args, 60 * 60 * 12 );

		$args = get_transient( 'args' );
		foreach ( $args as $arg ) {
			Helper::register_taxonomy( $arg );
		}
	}

	static function loadFilters() {
		add_filter( 'pllwc_copy_post_metas', [ '\ssoFramework\Src\assets\Actions', 'pllRemoveMetadataSync' ], 10, 5 );
		add_filter( 'post_class', [
			'\ssoFramework\Src\assets\Actions',
			'addThemeClass'
		], 10, 3 );
		add_filter( 'pre_option_rg_gforms_disable_css', '__return_true' );
        add_filter( 'woocommerce_variable_sale_price_html', [ '\ssoFramework\Src\assets\Actions', 'remove_prices_if_related_and_upsell'  ], 10, 2 );
        add_filter( 'woocommerce_variable_price_html', [ '\ssoFramework\Src\assets\Actions', 'remove_prices_if_related_and_upsell' ], 10, 2 );
        add_filter( 'woocommerce_get_price_html', [ '\ssoFramework\Src\assets\Actions', 'remove_prices_if_related_and_upsell' ], 10, 2 );


	}

    static function remove_prices_if_related_and_upsell( $price, $product ) {
	    global $woocommerce_loop;
	    if ($woocommerce_loop['name'] == 'up-sells') {
            return $price = '';
        } else {
	        return $price;
        }
    }
	/**
	 * Enqueue WordPress theme styles within Gutenberg.
	 */

	static function add_gutenberg_editor_style() {
		wp_enqueue_style( 'sso-grathwol', get_theme_file_uri( 'framework/assets/dev/scss/gutenberg-editor.css' ), false, '1.0', 'all' );
	}

	static function add_login_myaccount_link( $items, $args ) {
        if ($args->theme_location == 'custom-links-menu') {

            if (is_user_logged_in()) {
                $title = 'Min Konto';
                $content_popup = '<ul>
                                    <li><a href="/min-konto">Min Konto</a></li>
                                    <li><a href="' . wp_logout_url() . '">Log ud</a></li>
                                  </ul>';
            }
            elseif (!is_user_logged_in()) {
                $title = 'Min konto';
                $content_popup = '<form autocomplete="off" method="post" action="' . site_url( '/wp-login.php' ) . '">
                                    <input type="text" name="log" placeholder="Brugernavn"/>
                                    <input type="password" name="pwd" placeholder="Kodeord"/>
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="rememberme" name="rememberme" value="forever"/>
                                        <label for="rememberme">Husk mig</label>
                                    </div>
                                    <button type="submit" class="btn btn--primary">Log in</button>
								   </form>
								   <a href="' .get_permalink(wc_get_page_id('myaccount')). '?action=register">Opret bruger</a>';
            }
			$items .= '<li class="account_widget">
						<a href="/min-konto" class="text">' . $title . '</a>
						<div class="account_widget-popup">' . $content_popup . '</div>
                       </li>';
        }
        return $items;
	}
	/**
	 * HOOKS WC's price html
	 * Checks if a user has logged in and determine therefore,
	 * if the price should be shown.
	 */
	static function check_logged_in_user_price($price) {
		if ( is_user_logged_in() ) {
			return '<span class="woocommerce-Price-amount amount">'.$price.'</span>';
		} else {
			return '';
		}
	}

	/**
	 * HOOKS product card
	 * Checks if a user has logged in and determine therefore,
	 * if the price should be shown.
	 */
	static function check_logged_in_user_buttons() {
		if ( is_user_logged_in() ) {
			return __( 'Add to cart', 'woocommerce' );
		} else {
			return __( 'Read more', 'woocommerce' );
		}
	}

	static function add_shipping_account_menu( $items ) {

        // Remove the logout menu
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );

        $items['shipping'] = __( 'Gratis fragt', 'grathwol' );

        // Insert logout menu again
		$items['customer-logout'] = $logout;

        return $items;

    }

    static function add_shipping_account_menu_endpoint() {
        add_rewrite_endpoint( 'shipping', EP_PAGES );
    }

    static function add_shipping_account_menu_content() {
        echo '<h3>' . __( 'Liste over produkter du har gratis fragt på.', 'grathwol' ) . '</h3>';

        $current_user = wp_get_current_user();

        // Return if not logged in
        if (0 == $current_user->ID) return;

        // List of products
        $products = get_posts( array(
            'numberposts' => -1,
            'post_type'   => 'product',
        ) );

        $products_with_contact = array();

        foreach ( $products as $product ) {
            $data = wc_get_product( $product->ID );

            $user_specific_shipping = maybe_unserialize(get_post_meta( $product->ID, '_grathwol_user_specific_free_shipping', true));

            if (is_array($user_specific_shipping)) {

                // Determine the key
                if (in_array($current_user->ID, $user_specific_shipping)) {
                    echo '<div><a href="'. get_permalink($product->ID) . '">'. $product->post_title . '</a></div>';
                }
            }
        }
    }

	// Creates the menu item for often bought items.
	static function wc_create_link_my_account( $items ) {

		// Remove the logout menu
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );

		$items['koebte-produkter'] = __( 'Ofte Købte Produkter', 'grathwol' );
		$items['favoritter'] = __( 'Favoritter', 'grathwol' );

		// Insert logout menu again
		$items['customer-logout'] = $logout;

		return $items;

	}

	// Register endpoint for often bought items & Favorit List.
	static function wc_my_account_add_endpoint() {
		add_rewrite_endpoint( 'koebte-produkter', EP_PAGES );
		add_rewrite_endpoint( 'favoritter', EP_PAGES );
	}

	// Adds content to the page for often bought items on my account page.
	static function wc_my_account_endpoint_content() {
		// GET CURRENT USER
		$current_user = wp_get_current_user();
		if ( 0 == $current_user->ID ) return;

		// GET USER ORDERS (COMPLETED + PROCESSING)
		$customer_orders = get_posts( array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $current_user->ID,
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_is_paid_statuses() ),
		) );
		if (count($customer_orders) <= 0) {
			?>
			<p><?php _e('Du har endnu ikke købt noget.', 'sso-grathwol'); ?></p>
			<?php
		} else {
		// LOOP THROUGH ORDERS AND GET PRODUCT IDS
		if ( ! $customer_orders ) return;
		$product_ids = array();
		$items_count = [];
		foreach ( $customer_orders as $customer_order ) {
			$order = wc_get_order( $customer_order->ID );
			$items = $order->get_items();
			foreach ( $items as $item ) {
				$product_id = $item->get_product_id();
				$product_ids[] = $product_id;
				$items_count[] = [$item->get_product_id() => $item->get_quantity()];
			}
		}
		$totals = [];
		array_walk($items_count, function($item_count) use (&$totals) {
			return @$totals[key($item_count)] += current($item_count);
		});
		$product_ids = array_unique( $product_ids );
		$product_ids_str = implode( ",", $product_ids );



		?>

		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<th></th>
					<th>Produkt</th>
					<th>Antal købte (i alt)</th>
					<th>Handling</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($product_ids as $key) :
					$product = wc_get_product($key);
					$occurences = array_count_values($product_ids);
				?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-completed order">
						<td class="products-table-data">
							<?php echo $product->get_image('thumbnail'); ?>
						</td>
						<td class="products-table-data">
							<p><?php echo $product->get_title(); ?></p>
						</td>
						<td class="products-table-data">
							<p><?php echo $totals[$product->get_id()] ?></p>
						</td>
						<td class="products-table-data">
							<a href="<?php echo $product->get_permalink(); ?>">Vis produkt</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php };
	}



	/**
	 * Adds content for favorit list.
	 */
	static function favorit_list_content() {

		global $wpdb;
		$table_name = "wp_wc_favorit_list";
		$user_id = get_current_user_id();
		$data = $wpdb->get_results('SELECT wp_wc_product_id FROM ' . $table_name . ' WHERE wp_user_id LIKE ' . $user_id);
		$_products = [];
		$index = 0;
		while($index <= count($data) - 1 ) {
			$item = wc_get_product($data[$index]->wp_wc_product_id);
			$image_id = wp_get_attachment_image_src( get_post_thumbnail_id( $item->get_ID() ), 'thumbnail' );
			$temp_product['product_id'] = $item->get_ID();
			$temp_product['product_name'] = $item->get_name();
			$temp_product['product_image'] = $image_id[0];
			$temp_product['product_link'] = get_permalink($item->get_ID());
			array_push( $_products , $temp_product );

			$index++;
		}
		if (count($data) <= 0) {
			?>
			<p><?php _e('Du har endnu ikke valgt nogle produkter som favorit.', 'sso-grathwol'); ?></p>
			<?php
		} else {
		?>

		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<th></th>
					<th>Produkt</th>
					<th>Handling</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$index = 0;
				while($index <= count($data) - 1 ) :
					$item = wc_get_product($data[$index]->wp_wc_product_id);
					$image_id = wp_get_attachment_image_src( get_post_thumbnail_id( $item->get_ID() ), 'thumbnail' );
					$index++;
				?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-completed order">
						<td class="products-table-data">
							<img height="auto" width="75px" src="<?php echo $image_id[0]; ?>" alt="<?php echo $item->get_name(); ?>" />
						</td>
						<td class="products-table-data">
							<p><?php echo $item->get_name(); ?></p>
						</td>
						<td class="products-table-data">
							<a href="<?php echo get_permalink($item->get_ID()); ?>">Vis produkt</a>
						</td>
					</tr>
				<?php
				endwhile;
			}?>
			</tbody>
		</table>

		<?php

	}

	/**
	 * Creates REST API for posting saved Product to favourites.
	 */
	static function saveFavorite() {
		register_rest_route( 'wc/v3', '/save_favorite', array(
			'methods'  => 'POST',
			'callback' => __CLASS__ . '::saveFavoriteProduct',
		) );
	}

	/**
	 * Saves the favourit product to users list.
	 */
	static function saveFavoriteProduct($request) {
		global $wpdb;

		$user_id = $request->get_params()[user_id];
		$product_id = $request->get_params()[product_id];
		$table_name = "wp_wc_favorit_list";

		$data = $wpdb->get_results('SELECT wp_wc_product_id FROM ' . $table_name . ' WHERE wp_user_id LIKE ' . $user_id);

		if(!in_array($product_id, array_column($data, 'wp_wc_product_id'))) :
			$wpdb->insert($table_name, array('wp_user_id' => $user_id, 'wp_wc_product_id' => $product_id) );
		endif;
	}

	static function getFavorites() {
		register_rest_route( 'wp/v2', '/get_user_favorit_list/(?P<user_id>\d+)', array(
			'methods'  => 'GET',
			'callback' => __CLASS__ . '::getFavoritList',
		) );
	}

	/**
	 * Retrieves the users favorit list.
	 */
	static function getFavoritList($request) {
		global $wpdb;
		$table_name = "wp_wc_favorit_list";
		$user_id = $request->get_params()['user_id'];
		$data = $wpdb->get_results('SELECT wp_wc_product_id FROM ' . $table_name . ' WHERE wp_user_id LIKE ' . $user_id);

		$_products = [];
		$index = 0;
		while($index <= count($data) - 1 ) {
			$item = wc_get_product($data[$index]->wp_wc_product_id);
			if(!$item){
				$index++;
				continue;
			}
			$image_id = wp_get_attachment_image_src( get_post_thumbnail_id( $item->get_ID() ), 'thumbnail' );
			$temp_product['product_id'] = $item->get_ID();
			$temp_product['product_name'] = $item->get_name();
			$temp_product['product_image'] = $image_id[0];
			$temp_product['product_link'] = get_permalink($item->get_ID());
			array_push( $_products , $temp_product );

			$index++;
		}
		return $_products;
	}

	/**
	 * Registers endpoint for removing item
	 */
	static function removeFavoritRestRoute() {
		register_rest_route( 'wc/v3', '/remove_favorite', array(
			'methods'  => 'POST',
			'callback' => __CLASS__ . '::removeFavorit',
		) );
	}

	/**
	 * Removes an item from the favorit list.
	 */
	static function removeFavorit($request) {
		global $wpdb;

		$user_id = $request->get_params()[user_id];
		$product_id = $request->get_params()[product_id];

		$table_name = "wp_wc_favorit_list";
		$wpdb->delete( $table_name, array( 'wp_wc_product_id' => $product_id, 'wp_user_id' => $user_id) );
	}

	/**
	 * Gets called everytime a user is getting deleted.
	 * HOOK: delete_user
	 * @$user_id (INT)
	 */
	static function removeFavoritList( $user_id ) {
		global $wpdb;
		$table_name = "wp_wc_favorit_list";

		$wpdb->delete( $table_name, array( 'wp_user_id' => $user_id) );
	}

	/**
	 * Change position of logout button.
	 */
	static function adjust_account_menu_items( $items ) {

        // Remove the logout menu
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );

        $items['contract'] = __( 'Contract', 'grathwol' );

        // Insert logout menu again
		$items['customer-logout'] = $logout;

		return $items;

    }

    static function add_my_account_endpoint() {
        add_rewrite_endpoint( 'contract', EP_PAGES );
    }

    static function add_contract_endpoint_content() {
        echo '<h3>' . __( 'List of contract products', 'grathwol' ) . '</h3>';

        $current_user = wp_get_current_user();

        // Return if not logged in
        if (0 == $current_user->ID) return;

        // List of products
        $products = get_posts( array(
            'numberposts' => -1,
            'post_type'   => 'product',
        ) );

        $products_with_contact = array();

        foreach ( $products as $product ) {
            $data = wc_get_product( $product->ID );

            $user_specific_price_group = maybe_unserialize(get_post_meta( $product->ID, '_grathwol_user_specific_pricing_group', true));

            // Determine the key
            if ($user_specific_price_group) {
                $matched = array_search(2, array_column($user_specific_price_group, 'customer_id'));
            }

            // If matched
            if ($matched !== false) {
                $user_price = $user_specific_price_group[$matched];
                echo '<div><a href="'. get_permalink($product->ID) . '">'. $product->post_title . '</a></div>';
            }

        }
	}

	/**
	 * Adds custom category for GB Plugins
	 */
	static function sso_custom_block_categories( $categories, $post ) {

		// if ( $post->post_type !== 'post' ) {
		//     return $categories;
		// }
		$merged = array_merge(
			$categories,
			array(
				array(
					'slug'  => 'simplesolution',
					'title' => 'Simple Solution',
					'icon'  => null,
				),
			)
		);

		return $merged;
	}




    public function customizer_init( $wp_customize ) {
        $wp_customize->add_section(
            'product_settings',
            array(
                'title' => __('Product settings', 'sso-grathwol'),
                'priority' => 35,
            )
        );

        $wp_customize->add_setting(
            'tooltip_textbox',
            array(
                'default' => __('This is a merged product', 'sso-grathwol'),
            )
        );

        $wp_customize->add_control(
            'tooltip_textbox',
            array(
                'label' => 'Text on tooltip',
                'section' => 'product_settings',
                'type' => 'text',
            )
        );
    }

	static function registerOrderSuccessPermalink() {
        add_rewrite_endpoint( 'tak-for-din-ordre', EP_PERMALINK );
	}

	static function registerOrderSuccessRedirect() {
		global $wp_query;
			// @TODO: strengthen using a MD5 hash calculation
            if (!isset($_GET['orderid']) || !isset($_GET['hash'])) {
                echo 'Not allowed!';
                die;
            }
			$order = wc_get_order((int)$_GET['orderid']);
			if (!$order) :
				echo '<script>window.location.replace(window.location.origin + "/404")</script>';
				exit();
			endif;
				if($order->get_status() == "pending") :
				$order->update_status("processing");
				endif;
				wc_get_template( 'checkout/thankyou.php', array( 'order' => $order ) );
	}

	static function maintanenceMode() {
		echo '<div class="maintanence-mount"></div>';
	}

	/**
	 * Loads all actions and filters.
	 */
	static function loadActions() {
		add_action( "rest_api_init", function () {
			$post_types = get_post_types();
			foreach ( $post_types as $post_type ) {
				register_rest_field( $post_type, "terms", [
					"get_callback" => function ( $post ) {
						$taxonomies           = get_post_taxonomies( $post['id'] );
						$terms_and_taxonomies = [];
						foreach ( $taxonomies as $taxonomy_name ) {
							$terms_and_taxonomies[ $taxonomy_name ] = wp_get_post_terms( $post['id'], $taxonomy_name );
						}

						set_transient( 'terms_and_taxonomies', $terms_and_taxonomies, 60 * 60 * 12 );

						return get_transient( 'terms_and_taxonomies' );
					}
				] );
			}
		} );
        add_filter( 'excerpt_length', function(){
            return 40;
		} );
		add_action( 'after_body', [ '\ssoFramework\Src\assets\Actions', 'maintanenceMode' ], 1 ); // Maintanence hook.
		add_filter( 'block_categories', [ '\ssoFramework\Src\assets\Actions', 'sso_custom_block_categories' ], 1, 2 );
		add_filter( 'wp_nav_menu_items', [ '\ssoFramework\Src\assets\Actions', 'add_login_myaccount_link' ], 10, 2 ); // Registers my-account menu item (Service menu)
		add_filter( 'woocommerce_product_add_to_cart_text', [ '\ssoFramework\Src\assets\Actions', 'check_logged_in_user_buttons' ], 10, 2);
		add_filter( 'woocommerce_get_price_html', [ '\ssoFramework\Src\assets\Actions', 'check_logged_in_user_price' ], 10, 4 );
		add_action( 'woocommerce_account_menu_items', [ '\ssoFramework\Src\assets\Actions', 'wc_create_link_my_account' ], 40 );
		add_action( 'init', [ '\ssoFramework\Src\assets\Actions', 'wc_my_account_add_endpoint' ], 2 );
		add_action( 'woocommerce_account_koebte-produkter_endpoint', [ '\ssoFramework\Src\assets\Actions', 'wc_my_account_endpoint_content' ] );
		add_action( 'woocommerce_account_favoritter_endpoint', [ '\ssoFramework\Src\assets\Actions', 'favorit_list_content' ] );
		add_action( 'after_header_navigation_mount', [ '\ssoFramework\Src\assets\Actions', 'ctaButtonHeader' ], 1 );
        add_action( 'after_header_navigation_mount', [ '\ssoFramework\Src\assets\Actions', 'searchMount' ], 2 );
		add_action( 'header_navigation_mount', [ '\ssoFramework\Src\assets\Actions', 'mainNavigationMount' ], 1 );
		add_action( 'admin_menu', [ '\ssoFramework\Src\assets\Actions', 'addMenuSubpage' ] );
		add_action( 'cmb2_admin_init', [ '\ssoFramework\Src\assets\Actions', 'propertyOptionsPage' ] );
		// Outcommented add_action below, as it was generating error -->  Warning: Invalid argument supplied for foreach() in /Users/simplesol/Documents/www/grathwol/wp-content/themes/sso-grathwol/framework/Src/assets/Actions.php on line 200
		// add_action( 'init', [ '\ssoFramework\Src\assets\Actions', 'registerTaxonomies' ] );
		add_action( 'mount_after_content', [ '\ssoFramework\Src\assets\Actions', 'favoriteListMount' ] );
		add_action( 'before_service_menu', [ '\ssoFramework\Src\assets\Actions', 'contactInformation' ], 1 );
		add_action( 'rest_api_init', [ '\ssoFramework\Src\api\Overrides', 'create_api_posts_meta_field' ] );
		add_action( 'pll_language_edit_form_fields', [ '\ssoFramework\Src\assets\Actions', 'pllAddForm' ] );
		add_action( 'pll_language_add_form_fields', [ '\ssoFramework\Src\assets\Actions', 'pllAddForm' ] );
		add_action( 'admin_init', [ '\ssoFramework\Src\assets\Actions', 'pllSaveForm' ], 999 );
		add_action( 'enqueue_block_editor_assets', [ '\ssoFramework\Src\assets\Actions', 'add_gutenberg_editor_style' ] );
		add_action( 'after_header', [ '\ssoFramework\Src\assets\Actions', 'addBreadcrumbs' ], 1 );
		add_action( 'cmb2_admin_init', [ '\ssoFramework\Src\assets\Actions', 'pageFields' ] );
		add_action( 'rest_api_init', [ '\ssoFramework\Src\assets\Actions', 'saveFavorite' ] );
		add_action( 'rest_api_init', [ '\ssoFramework\Src\assets\Actions', 'getFavorites' ] );
		add_action( 'rest_api_init', [ '\ssoFramework\Src\assets\Actions', 'removeFavoritRestRoute' ] );
		add_filter( 'woocommerce_account_menu_items', [ '\ssoFramework\Src\assets\Actions', 'add_shipping_account_menu' ]);
        add_action( 'init', [ '\ssoFramework\Src\assets\Actions', 'add_shipping_account_menu_endpoint' ] );
        add_action( 'woocommerce_account_shipping_endpoint', [ '\ssoFramework\Src\assets\Actions', 'add_shipping_account_menu_content'] );
		// add_filter( 'woocommerce_account_menu_items', [ '\ssoFramework\Src\assets\Actions', 'adjust_account_menu_items' ]);
        add_action( 'init', [ '\ssoFramework\Src\assets\Actions', 'add_my_account_endpoint' ] );
		add_action( 'woocommerce_account_contract_endpoint', [ '\ssoFramework\Src\assets\Actions', 'add_contract_endpoint_content'] );
		add_action( 'delete_user', [ '\ssoFramework\Src\assets\Actions', 'removeFavoritList'] );
		add_action( 'init', [ '\ssoFramework\Src\assets\Actions', 'registerOrderSuccessPermalink' ] );
        add_action( 'cart_thankyou_before', [ '\ssoFramework\Src\assets\Actions', 'registerOrderSuccessRedirect' ] );

        add_action( 'customize_register', [ '\ssoFramework\Src\assets\Actions', 'customizer_init' ] );

    }
}
