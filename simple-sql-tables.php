<?php
namespace VinlandMedia\SimpleSQLTables;

/*
Plugin Name: Simple SQL Tables
Plugin URI: https://vinland.tech/products/wp-post-stickies/
Description: Simple SQL Tables...
Version: 1.0.0
Author: Vinland Media
Author URI: http://vinlandmedia.com/
License: GPLv2
Tags:
Text Domain: simple-sql-tables
*/

// includes
require_once('includes/QueryActDeactClass.php');
require_once('includes/QueryInitClass.php');
require_once( 'includes/QueryEnqueuer.php' );
//require_once('includes/sst-queries.php');
//require_once('includes/sst-utility-functions.php');

// public
//require_once('public/QueryProcessorClass.php');

// admin
//require_once('admin/sst-settings-options.php');
require_once('admin/MetaBox.php');

define( 'WP_DEBUG', true );

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
* Plugin Activation
*/
function sst_queries_activate($network_wide) {
	$myActivator = new QueryActDeactClass();

	$myActivator->Activator($network_wide);
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\sst_queries_activate');

/*
* Plugin Deactivation
*/
function sst_queries_deactivate() {
	$myDeactivator = new QueryActDeactClass();

	$myDeactivator->Deactivator();

}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\sst_queries_deactivate');


/**
 * Initialization
 *
 * Registers the custom post type (CPT).
 *
 * @param null
 * @return null
 *
 * @author Vinland Media, LLC.
 */
function sst_query_init() {
	$myInitializor = new QueryInitClass();

	$myInitializor->QueryInitialize();

}
add_action('init', __NAMESPACE__.'\\sst_query_init');

function sst_admin_scripts() {
	$myEnqueuer = new QueryEnqueuer();

	$myEnqueuer->EnqueueAdmin();

}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\sst_admin_scripts');

function sst_wp_scripts() {
	$myEnqueuer = new QueryEnqueuer();

	$myEnqueuer->EnqueuePublic();

}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\sst_wp_scripts');

/**
 * Core Plugin Function
 *
 * This function, based upon a shortcode and parameters, returns one of three
 * functional Scrum boards (story board, product backlog, and sprint).
 *
 * @param string $atts  Shortcode attributes (to be expounded upon later!)
 * @return string $agilepress_show  Formatted Scrum board output.
 *
 * @author Ken Kitchen ken@vinlandmedia.com
 * @author Vinland Media, LLC.
 * @package AgilePress
 */
function sst_main($atts)
{
	global $wpdb;

	$fetch_data_atts = shortcode_atts( array(
		'query' => '',
		'output' => ''
	), $atts );

	if (!isset($fetch_data_atts['query'])) {
		return 'A query parameter is required.';
	}

	if (isset($fetch_data_atts['output'])) {
		if ($fetch_data_atts['output'] == 'dt') {
			$output_format = 'dt';
		}
	} else {
		$output_format = 'plain';
	}

	$query = $fetch_data_atts['query'];

	$results = $wpdb->get_results($query, OBJECT_K);
	$do_headers = true;

	if ('dt' == $output_format) {
		$return_data = '<table id="dt-default">';
	} else {
		$return_data = '<table id="plain" class="w3-table">';
	}

	foreach ($results as $key => $row) {
		$tags = array_keys((array)$row);
		$tag_count = count($tags);
		if ($do_headers) {
			$return_data .= '<thead>';
			for ($x = 0; $x <= $tag_count; $x++) {
				$return_data .= '<th>' . ucwords(str_replace('_', ' ', $tags[$x])) . '</th>';
			}
			$return_data .= '</thead><tbody>';
			$do_headers = false;
		}
		$return_data .= '<tr>';
		for ($x = 0; $x <= $tag_count; $x++) {
			$return_data .= '<td>' . $row->{$tags[$x]} . '</td>';
		}
		$return_data .= '</tr>';
	}

	$return_data .= '</tbody></table>';

	return $return_data;
}
add_shortcode('sst-query', __NAMESPACE__.'\\sst_main');
