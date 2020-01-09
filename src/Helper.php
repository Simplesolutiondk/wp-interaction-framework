<?php

namespace SimpleSolution\WpInteractionFramework;

class Helper {
	static function base_path() {
		return get_site_url() . get_template_directory_uri();
	}

	static function assets( $dir ) {
		return get_template_directory_uri() . '/assets/' . $dir . '/';
	}

	static function shared( $folder, $file ) {
		return get_site_url() . '/wp-content/shared/' . $folder . '/' . $file;
	}

	static function widget( $name, $class ) {

		$id     = str_replace( ' ', '-', strtolower( $name ) );
		$widget = register_sidebar( [
			'name'          => __( $name, 'sso-default' ),
			'id'            => $id,
			'class'         => $class,
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget__title">',
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

	static function register_taxonomy( $args ) {

		$args = helper::arrayToObject( $args );

		$label = strtolower( $args->label );

		register_taxonomy(
			$args->post_type . '_' . $label,
			$args->post_type,
			[
				'label'        => __( $args->label, 'sso-default' ),
				'rewrite'      => [ 'slug' => $args->post_type . '_' . $label ],
				'hierarchical' => true,
				'show_in_rest' => true
			]
		);
	}

	static function arrayToObject( $array ) {
		return json_decode( json_encode( $array ), false );
	}

	static function getCurrentLang() {
		$lang = pll_current_language();

		return ! empty( $lang ) ? $lang : 'all';
	}

	static function getTermMeta() {
		$postType = get_post_type();

		if ( $postType === 'case' ) {
			return get_the_term_list( get_the_ID(), 'case_project_theme', '', ', ' );
		} else {
			return get_the_term_list( get_the_ID(), 'category', '', ', ' );
		}
	}

}

