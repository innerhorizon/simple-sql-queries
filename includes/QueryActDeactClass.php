<?php

namespace VinlandMedia\SimpleSQLTables;

/**
 * Created by PhpStorm.
 * User: kenkitchen
 * Date: 10/28/17
 * Time: 11:40 AM
 */
class QueryActDeactClass {

	public function Activator($network_wide) {
		global $wp_version;

		if (version_compare($wp_version, '4.1', '<')) {
			wp_die('Requires version 4.8 or higher.');
		}

		flush_rewrite_rules();

	}

	public function Deactivator() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// delete_option('wpps_admin_only');

		// unregister_post_type('wpps-post-stickies');

		// flush rewrite cache
		flush_rewrite_rules();

	}
}