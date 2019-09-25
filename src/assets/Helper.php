<?php

namespace ssoFramework\Src\Assets;

class Helper {
	static function base_path() {
		return get_site_url() . get_template_directory_uri();
	}

	static function assets( $dir ) {
		return get_template_directory_uri() . '/framework/assets/' . $dir . '/';
	}

	static function shared( $folder, $file ) {
		return get_site_url() . '/wp-content/shared/' . $folder . '/' . $file;
	}

	static function main_menu() {
		return wp_nav_menu( array(
			'menu'      => 2,
			'walker'    => '',
			'container' => false,
		) );
	}

	static function get_menu( $args ) {
		return wp_nav_menu( array(
			'theme_location' => ! empty( $args['menu_location'] ) ? $args['menu_location'] : 'main-menu',
			'walker'         => ! empty( $args['walker'] ) ? $args['walker'] : '',
			'container'      => ! empty( $args['container'] ) ? $args['container'] : 'false',
		) );
	}

	static function widget( $name ) {

		$id = str_replace( ' ', '-', strtolower( $name ) );

		$widget = register_sidebar( [
			'name'          => __( $name, 'sso-grathwol' ),
			'id'            => $id,
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h4 class="widgettitle">',
			'after_title'   => '</h4>'
		] );


		return $widget;
	}

	static function getCurrentTermId() {
		$currentTermId = isset ( get_queried_object()->term_id ) ? get_queried_object()->term_id : '0';

		return $currentTermId;
	}

	static function getCurrentSlug() {
		global $wp;

		$current_slug = add_query_arg( array(), $wp->request );

		return $current_slug;
	}

	static function addProductTab( $args, $tabs ) {

		$tabs[ $args['name'] ] = [
			'title'    => __( $args['title'], 'sso-grathwol' ),
			'priority' => $args['priority'],
			'callback' => $args['callback']
		];

		return $tabs;
	}

	static function register_taxonomy( $args ) {

		$args = helper::arrayToObject( $args );

		$label = strtolower( $args->label );

		register_taxonomy(
			$args->post_type . '_' . $label,
			$args->post_type,
			[
				'label'        => __( $args->label, 'sso-grathwol' ),
				'rewrite'      => [ 'slug' => $args->post_type . '_' . $label ],
				'hierarchical' => true,
				'show_in_rest' => true
			]
		);
	}

	static function register_options_page( $post_type, $name ) {
		$name_replace = preg_replace( '/\s+/', '_', strtolower( $name ) );

		add_submenu_page(
			'edit.php?post_type=' . $post_type,
			__( $name, 'sso-grathwol' ),
			__( $name, 'sso-grathwol' ),
			'manage_options',
			$name_replace,
			[ '\ssoFramework\Src\Assets\Helper', $name_replace . '_callback' ]
		);
	}

	static function properties_options_callback() {
		?>
		<?php
	}

	static function arrayToObject( $array ) {
		return json_decode( json_encode( $array ), false );
	}

	static function getCurrentLang() {
		$lang = pll_current_language();

		return ! empty( $lang ) ? $lang : 'all';
	}

	/**
	 * @param $post
	 * @param $args
	 */
	static function createRelatedProductsField( $post, $args ) {

		if ( empty( $args ) ) {
			return;
		}

		foreach ( $args as $arg ) { ?>
            <p class="form-field">
                <label for="<?php echo $arg->ID; ?>"><?php esc_html_e( $arg->name, 'sso-grathwol' ); ?></label>
                <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="<?php echo $arg->ID; ?>"
                        name="<?php echo $arg->ID; ?>[]"
                        data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
                        data-action="woocommerce_json_search_products_and_variations"
                        data-exclude="<?php echo intval( $post->ID ); ?>">
					<?php

					$product_ids = get_post_meta( $post->ID, '_' . $arg->ID, true );

					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );

						if ( is_object( $product ) ) {
							echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
						}
					}
					?>
                </select> <?php echo wc_help_tip( __( isset( $arg->description ) ? $arg->description : 'Description for ' . $arg->name . ' is not set', 'sso-grathwol' ) ); ?>
            </p>
			<?php
		}
	}

	static function prefix() {
		
		return '_grathwol_';
	}

}

