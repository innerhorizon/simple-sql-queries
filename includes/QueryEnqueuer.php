<?php

namespace VinlandMedia\SimpleSQLTables;

/**
 * Created by PhpStorm.
 * User: kenkitchen
 * Date: 10/28/17
 * Time: 12:00 PM
 */

class QueryEnqueuer {

	public function EnqueuePublic() {
		wp_register_style('w3_lite_css', plugins_url('simple-sql-tables') . '/public/css/w3-lite.css', null, null);
		wp_enqueue_style('w3_lite_css');

		wp_register_style('fontawesome-js', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', null);
		wp_enqueue_style('fontawesome-js');

		wp_register_style('sst_dt_css', plugins_url('simple-sql-tables') . '/public/datatables/datatables.min.css');
		wp_enqueue_style('sst_dt_css');

		wp_register_script('sst_dt_js', plugins_url('simple-sql-tables') . '/public/datatables/datatables.min.js', array('jquery'));
		wp_enqueue_script('sst_dt_js');

		wp_register_script('sst_js', plugins_url('simple-sql-tables') . '/public/js/sst-public.js', array('jquery'));
		wp_enqueue_script('sst_js');

	}

	public function EnqueueAdmin() {
//		wp_register_style('sst-admin-css', plugins_url('simple-sql-tables') . '/admin/css/sst-admin.css');
//		wp_enqueue_style('sst-admin-css');

//		wp_register_script('sst-query-admin-js', plugins_url('simple-sql-tables-queries') . '/admin/js/sst-query-admin.js', array('jquery'));
//		wp_localize_script('sst-query-admin-js', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
//		wp_enqueue_script('sst-query-admin-js');

//		wp_enqueue_script('jquery-ui-sortable');

		wp_register_style('fontawesome-js', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', null);
		wp_enqueue_style('fontawesome-js');
	}
}