<?php

namespace VinlandMedia\SimpleSQLTables;

/**
 * Created by PhpStorm.
 * User: kenkitchen
 * Date: 10/30/17
 * Time: 6:02 PM
 */

class QueryInitClass {

	public function QueryInitialize() {
		register_post_type('ama-databases', $this->RegisterDatabaseCPT());
		register_post_type('ama-queries', $this->RegisterQueryCPT());

		if (!$this->role_exists('ama_admin')) {
			add_role(
				'ama_admin',
				'Ask Me Anything - Administrator',
				[
					'administer_ama_queries' => true,
					'delete_ama_queries'     => true,
					'add_ama_queries'        => true,
					'update_ama_queries'     => true,
					'view_ama_queries'       => true,
				]
			);
		}

		if (!$this->role_exists('ama_superuser')) {
			add_role(
				'ama_superuser',
				'Ask Me Anything - Superuser',
				[
					'delete_ama_queries' => true,
					'add_ama_queries'    => true,
					'update_ama_queries' => true,
					'view_ama_queries'   => true,
				]
			);
		}

		if (!$this->role_exists('ama_user')) {
			add_role(
				'ama_user',
				'Ask Me Anything - User',
				[
					'add_ama_queries'    => true,
					'update_ama_queries' => true,
					'view_ama_queries'   => true,
				]
			);
		}

		if (!$this->role_exists('ama_viewer')) {
			add_role(
				'ama_viewer',
				'Ask Me Anything - Viewer',
				[
					'view_ama_queries' => true,
				]
			);
		}

		$current_user = wp_get_current_user();
		$current_user->add_role('ama_admin');

		// flush rewrite cache
		flush_rewrite_rules();
	}

	private function RegisterQueryCPT() {
		//register the query custom post type
		$labels = array(
			'name'               => __( 'AMA Queries', 'ask-me-anything' ),
			'singular_name'      => __( 'AMA Query', 'ask-me-anything' ),
			'add_new'            => __( 'Add New', 'ask-me-anything' ),
			'add_new_item'       => __( 'Add New AMA Query', 'ask-me-anything' ),
			'edit_item'          => __( 'Edit AMA Query', 'ask-me-anything' ),
			'new_item'           => __( 'New AMA Query', 'ask-me-anything' ),
			'all_items'          => __( 'All AMA Queries', 'ask-me-anything' ),
			'view_item'          => __( 'View AMA Query', 'ask-me-anything' ),
			'search_items'       => __( 'Search AMA Queries', 'ask-me-anything' ),
			'not_found'          =>  __( 'No AMA Queries found', 'ask-me-anything' ),
			'not_found_in_trash' => __( 'No AMA Queries found in Trash', 'ask-me-anything' ),
			'menu_name'          => __( 'AMA Queries', 'ask-me-anything' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			//'show_in_menu'       => 'ask-me-anything_main_menu',
			'menu_icon'			 => 'dashicons-search',
			'query_var'          => true,
			'rewrite'            => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'show_in_rest'       => true,
			'rest_base'          => 'ask-me-anything-queries',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports'           => array('title', 'comments'),
//		'taxonomies'         => array('category')
		);

		return $args;
	}

	private function RegisterDatabaseCPT() {
		//register the database custom post type
		$labels = array(
			'name'               => __( 'AMA DB Links', 'ask-me-anything' ),
			'singular_name'      => __( 'AMA DB Link', 'ask-me-anything' ),
			'add_new'            => __( 'Add New', 'ask-me-anything' ),
			'add_new_item'       => __( 'Add New AMA DB Link', 'ask-me-anything' ),
			'edit_item'          => __( 'Edit AMA DB Link', 'ask-me-anything' ),
			'new_item'           => __( 'New AMA DB Link', 'ask-me-anything' ),
			'all_items'          => __( 'All AMA DB Links', 'ask-me-anything' ),
			'view_item'          => __( 'View AMA DB Link', 'ask-me-anything' ),
			'search_items'       => __( 'Search AMA DB Links', 'ask-me-anything' ),
			'not_found'          =>  __( 'No AMA DB Links found', 'ask-me-anything' ),
			'not_found_in_trash' => __( 'No AMA DB Links found in Trash', 'ask-me-anything' ),
			'menu_name'          => __( 'AMA DB Links', 'ask-me-anything' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			//'show_in_menu'       => 'ask-me-anything_main_menu',
			'menu_icon'			 => 'dashicons-index-card',
			'query_var'          => true,
			'rewrite'            => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'show_in_rest'       => true,
			'rest_base'          => 'ask-me-anything-databases',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports'           => array('title', 'comments'),
//		'taxonomies'         => array('category')
		);

		return $args;
	}

	private function role_exists($role_name) {

		$role = get_role($role_name);

		if( ! is_null( $role ) ) {
			return true;
		}

		return false;

	}
}