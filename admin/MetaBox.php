<?php

namespace VinlandMedia\SimpleSQLTables;

/**
 * Created by PhpStorm.
 * User: kenkitchen
 * Date: 10/30/17
 * Time: 5:57 PM
 */
function query_cpt_metabox($post) {
	global $wpdb;

	$selection_table = get_post_meta($post->ID, '_ama_selection_table', true);
	$selection_columns = get_post_meta($post->ID, '_ama_selection_columns', true);

	if ($selection_columns != "") {
		$selected_column_array = explode(',', $selection_columns);
	} else {
		$selected_column_array = null;
	}

	wp_nonce_field('query_cpt_metabox_nonce', 'meta_box_nonce');

	$existing_tables = $wpdb->get_results(
		'select t.table_name ' .
		'from information_schema.tables t ' .
		'where t.table_name like "' . $wpdb->prefix . '%" ' .
		'and t.table_name not in ("' . $wpdb->prefix . 'commentmeta", "' .
		$wpdb->prefix . 'comments", "' . $wpdb->prefix . 'links", "' .
		$wpdb->prefix . 'options", "' . $wpdb->prefix . 'postmeta", "' .
		$wpdb->prefix . 'posts", "' . $wpdb->prefix . 'term_relationships", "' .
		$wpdb->prefix . 'term_taxonomy", "' . $wpdb->prefix . 'termmeta", "' .
		$wpdb->prefix . 'terms", "' . $wpdb->prefix . 'usermeta", "' .
		$wpdb->prefix . 'users") '
	);

	foreach ($existing_tables as $existing_table) {
		//$table_columns['table_name'] = $existing_table->table_name;
		$columns = $wpdb->get_results(
			'select t.table_name, c.column_name, c.data_type ' .
			'from information_schema.columns c, information_schema.tables t ' .
			'where t.table_name = c.table_name ' .
			'and t.table_name = "' . $existing_table->table_name . '"');

		foreach ($columns as $column) {
			$table_columns[$existing_table->table_name][] = $column->column_name;
		}
	}

	?>
	<br>
	<div id="selection-table-div">
		<table id="main-table" width="100%">
			<thead>
			<th width="40%">Instructions</th>
			<th width="30%">Table</th>
			<th width="30%">Columns</th>
			</thead>
			<tbody>
			<tr>
				<td>Select a table from the dropdown and then select the column(s) you want returned (use shift/control to select multiple items).</td>
				<td>
					<select id="selection-table" name="selection-table" onchange="tableSelect('selection-table', 999)">
						<?php
						echo '<option value="">(please select a table)</option>';
						foreach ($existing_tables as $existing_table) {
							if ($existing_table->table_name == $selection_table) {
								echo '<option value="' . $existing_table->table_name . '" selected>' . $existing_table->table_name . '</option>';
							} else {
								echo '<option value="' . $existing_table->table_name . '">' . $existing_table->table_name . '</option>';
							}
						}
						?>
					</select>
				</td>
				<br>
				<br>
				<td>
					<?php
					foreach ($existing_tables as $existing_table) {
						if ($selection_table == $existing_table->table_name) {
							echo '<select id="' . $existing_table->table_name . '" class="cm-show" multiple="multiple" name="' . $existing_table->table_name . '[]">';
						} else {
							echo '<select id="' . $existing_table->table_name . '" class="cm-hidden" multiple="multiple" name="' . $existing_table->table_name . '[]">';
						}
						foreach ($table_columns[$existing_table->table_name] as $table_column) {
							if ((null != $selected_column_array) && (in_array($table_column, $selected_column_array))) {
								echo '<option value="' . $table_column . '" selected>' . $table_column . '</option>';
							} else {
								echo '<option value="' . $table_column . '">' . $table_column . '</option>';
							}
						}
						echo '</select>';
					}
					?>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
	<br>

	<br>
	<div id="joining-table-div" class="repeat">
		<table class="wrapper" width="100%">
			<thead>
			<tr>
				<td colspan="4"><span class="add"><i class="fa fa-plus" aria-hidden="true"></i> Join On</span></td>
			</tr>
			</thead>
			<tbody class="container">
			<tr class="template row">
				<td>
					<select id="joining-table[{{row-count-placeholder}}]" name="joining-table[{{row-count-placeholder}}]" onchange="tableSelect('joining-table', {{row-count-placeholder}})">
						<?php
						echo '<option value="">(select table to join)</option>';
						foreach ($existing_tables as $existing_table) {
							if ($existing_table->table_name == $selection_table) {
								echo '<option value="' . $existing_table->table_name . '[{{row-count-placeholder}}]" selected>' . $existing_table->table_name . '</option>';
							} else {
								echo '<option value="' . $existing_table->table_name . '[{{row-count-placeholder}}]">' . $existing_table->table_name . '</option>';
							}
						}
						?>
					</select>
				</td>
				<td>
					<?php
					foreach ($existing_tables as $existing_table) {
						if ($selection_table == $existing_table->table_name) {
							echo '<select id="' . $existing_table->table_name . '[{{row-count-placeholder}}]" class="cm-show" name="' . $existing_table->table_name . '[{{row-count-placeholder}}]">';
						} else {
							echo '<select id="' . $existing_table->table_name . '[{{row-count-placeholder}}]" class="cm-hidden" name="' . $existing_table->table_name . '[{{row-count-placeholder}}]">';
						}
						foreach ($table_columns[$existing_table->table_name] as $table_column) {
							if ((null != $selected_column_array) && (in_array($table_column, $selected_column_array))) {
								echo '<option value="' . $table_column . '[{{row-count-placeholder}}]" selected>' . $table_column . '</option>';
							} else {
								echo '<option value="' . $table_column . '[{{row-count-placeholder}}]">' . $table_column . '</option>';
							}
						}
						echo '</select>';
					}
					?>
				</td>
                <td> = </td>
                <td>
                    (join columns)
                </td>
				<td><span class="remove"><i class="fa fa-minus" aria-hidden="true"></i></span></td>
			</tr>
			</tbody>
		</table>
	</div><br />

	<br>
	<div class="repeat">
		<table class="wrapper" width="100%">
			<thead>
			<tr>
				<td width="10%" colspan="4"><span class="add"><i class="fa fa-plus" aria-hidden="true"></i> Add Where Clause Line</span></td>
			</tr>
			</thead>
			<tbody class="container">
			<tr class="template row">
				<td width="10%">
					<label for="selection-and-or">Where/And/Or: </label>
					<select name="selection-and-or[{{row-count-placeholder}}]">
						<option value="and">where/and</option>
						<option value="or">where/or</option>&nbsp;
					</select>
				</td>
				<td width="20%">
					<label for="left-value">Left Value: </label>
					<input name="left-value[{{row-count-placeholder}}]"><br>
				</td>
				<td width="10%">
					<label for="selection-operator">Operator: </label>
					<select name="selection-operator[{{row-count-placeholder}}]">
						<option value="=">is equal to</option>
						<option value="!=">is not equal to</option>
						<option value="&lt;">is less than</option>
						<option value="&gt;">is greater than</option>
						<option value="&lt;=">is less than or equal to</option>
						<option value="&gt;=">is greater than or equal to</option>
						<option value="!&lt;">is not less than</option>
						<option value="!&gt;">is not greater than</option>
						<option value="LIKE%">is like field%</option>
						<option value="%LIKE">is like %field</option>
						<option value="%LIKE%">is like %field%</option>
					</select>
				</td>
				<td width="20%">
					<label for="right-value">Right Value: </label>
					<input name="right-value[{{row-count-placeholder}}]"><br>
				</td>

				<td width="10%"><span class="remove"><i class="fa fa-minus" aria-hidden="true"></i></span></td>
			</tr>
			</tbody>
		</table>
	</div><br />
	<?php

}

function query_cpt_metabox_save($post_id) {
	global $wpdb;

	// Bail if we're doing an auto save
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	// if our nonce isn't there, or we can't verify it, bail
	if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'query_cpt_metabox_nonce')) return;

	// if our current user can't edit this post, bail
	if (!current_user_can('edit_post')) return;

	// now we can actually save the data
	$allowed = array(
		'a' => array( // on allow a tags
			'href' => array() // and those anchors can only have href attribute
		)
	);

	// Make sure your data is set before trying to save it
	if (isset($_POST['selection-table'])) {
		$selection_table = wp_kses($_POST['selection-table'], $allowed);
		update_post_meta($post_id, '_ama_selection_table', $selection_table);
	}

	if (isset($_POST[$selection_table])) {
		$selection_columns = $_POST[$selection_table];
		$selection_columns_as_text = "";
		foreach ($selection_columns as $selection_column) {
			$selection_columns_as_text .= $selection_column . ",";
		}
		$selection_columns_as_text = rtrim($selection_columns_as_text, ",");
		update_post_meta($post_id, '_ama_selection_columns', $selection_columns_as_text);
	}



	/*
		$existing_tables = $wpdb->get_results(
			'select t.table_name ' .
			'from information_schema.tables t ' .
			'where t.table_name like "' . $wpdb->prefix . '%" ' .
			'and t.table_name not in ("' . $wpdb->prefix . 'commentmeta", "' .
			$wpdb->prefix . 'comments", "' . $wpdb->prefix . 'links", "' .
			$wpdb->prefix . 'options", "' . $wpdb->prefix . 'postmeta", "' .
			$wpdb->prefix . 'posts", "' . $wpdb->prefix . 'term_relationships", "' .
			$wpdb->prefix . 'term_taxonomy", "' . $wpdb->prefix . 'termmeta", "' .
			$wpdb->prefix . 'terms", "' . $wpdb->prefix . 'usermeta", "' .
			$wpdb->prefix . 'users") '
		);

		foreach ($existing_tables as $existing_table) {
			//$table_columns['table_name'] = $existing_table->table_name;
			$columns = $wpdb->get_results(
				'select t.table_name, c.column_name, c.data_type ' .
				'from information_schema.columns c, information_schema.tables t ' .
				'where t.table_name = c.table_name ' .
				'and t.table_name = "' . $existing_table->table_name . '"');

			foreach ($columns as $column) {
				$table_columns[$existing_table->table_name][] = $column->column_name;
			}
		}
	*/

	$joining_tables = array();
	$x = 0;

	if (isset($_POST['joining-table']) && !empty($_POST['joining-table'])) {
		for ($i=0; $i < 99; $i++) {
			$joining_table = wp_kses($_POST['joining-table'], $allowed);
			$joining_table_sans_index = substr($joining_table[$i], 0, strpos($joining_table[$i],'['));
			if (isset($_POST[$joining_table_sans_index]) && !empty($_POST[$joining_table_sans_index])) {
				$joining_column = wp_kses($_POST[$joining_table_sans_index], $allowed);
				array_push($joining_tables, [
					'join_table'    => $joining_table_sans_index,
					'join_column'   => $joining_column[$i],

				]);
				$x++;
			} else {
				break;
			}
		}

	}

	if ($x > 0) {
		foreach ($joining_tables as $joining_table) {
			$joining_table_as_text =
				$joining_table['join_table'] . ',' .
				$joining_table['join_column['];

			$joining_table_as_text = rtrim($joining_table_as_text, ",");
			update_post_meta( $post_id, '_ama_join_row', $joining_table_as_text );
		}
	}



	$where_rows = array();
	$y = 0;

	for ($i=0; $i < 99; $i++) {
		if (check_where_row(
			$_POST["selection-and-or"][$i],
			$_POST["left-value"][$i],
			$_POST["selection-operator"][$i],
			$_POST["right-value"][$i])) {
			array_push($where_rows, [
				'selection_and_or'   => $_POST["selection-and-or"][$i],
				'left_value'         => $_POST["left-value"][$i],
				'selection_operator' => $_POST["selection-operator"][$i],
				'right_value'        => $_POST["right-value"][$i],
			]);
			$y++;
		} else {
			break;
		}
	}

	if ($y > 0) {
		foreach ($where_rows as $where_row) {
			$where_row_as_text =
				$where_row['selection_and_or'] . ',' .
				$where_row['left_value'] . ',' .
				$where_row['selection_operator'] . ',' .
				$where_row['right_value'];

			update_post_meta($post_id, '_ama_where_row', $where_row_as_text);
		}

	}

}
add_action('save_post', __NAMESPACE__.'\\query_cpt_metabox_save');

function query_cpt_meta_box_add() {
	add_meta_box( 'query-cpt-metabox-key', 'AMA Query Builder', __NAMESPACE__.'\\query_cpt_metabox', 'ama-queries', 'normal', 'high' );
}
add_action('add_meta_boxes', __NAMESPACE__.'\\query_cpt_meta_box_add');

function database_cpt_metabox($post) {
	wp_nonce_field('database_cpt_metabox_nonce', 'meta_box_nonce');

	$database_host = get_post_meta($post->ID, '_ama_database_host', true);
	$database_name = get_post_meta($post->ID, '_ama_database_name', true);
	$database_username = get_post_meta($post->ID, '_ama_database_username', true);
	$database_password = get_post_meta($post->ID, '_ama_database_password', true);
	$database_port = get_post_meta($post->ID, '_ama_database_port', true);

?>
<br>
<div id="database-link-div">
    <table>
        <tr>
            <td>DB Host</td>
            <td><input type="text" name="db-host" value="<?= $database_host ?>"></td>
        </tr>
        <tr>
            <td>DB Name</td>
            <td><input type="text" name="db-name" value="<?= $database_name ?>"></td>
        </tr>
        <tr>
            <td>DB Username</td>
            <td><input type="text" name="db-username" value="<?= $database_username ?>"></td>
        </tr>
        <tr>
            <td>DB Password</td>
            <td><input type="password" name="db-password" value="<?= $database_password ?>"></td>
        </tr>
        <tr>
            <td>DB Port</td>
            <td><input type="text" name="db-port" value="<?= $database_port ?>"></td>
        </tr>
    </table>
</div>
<?php
}

function database_cpt_metabox_save($post_id) {
	global $wpdb;

	// Bail if we're doing an auto save
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	// if our nonce isn't there, or we can't verify it, bail
	if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'database_cpt_metabox_nonce')) return;

	// if our current user can't edit this post, bail
	if (!current_user_can('edit_post')) return;

	// now we can actually save the data
	$allowed = array(
		'a' => array( // on allow a tags
			'href' => array() // and those anchors can only have href attribute
		)
	);

	if (isset($_POST['db-host'])) {
		$database_host = wp_kses($_POST['db-host'], $allowed);
		update_post_meta($post_id, '_ama_database_host', $database_host);
	}
	if (isset($_POST['db-name'])) {
		$database_name = wp_kses($_POST['db-name'], $allowed);
		update_post_meta($post_id, '_ama_database_name', $database_name);
	}
	if (isset($_POST['db-username'])) {
		$database_username = wp_kses($_POST['db-username'], $allowed);
		update_post_meta($post_id, '_ama_database_username', $database_username);
	}
	if (isset($_POST['db-password'])) {
		$database_password = wp_kses($_POST['db-password'], $allowed);
		update_post_meta($post_id, '_ama_database_password', $database_password);
	}
	if (isset($_POST['db-port'])) {
		$database_port = wp_kses($_POST['db-port'], $allowed);
		update_post_meta($post_id, '_ama_database_port', $database_port);
	}
}
add_action('save_post', __NAMESPACE__.'\\database_cpt_metabox_save');

function database_cpt_meta_box_add() {
	add_meta_box( 'database-cpt-metabox-key', 'AMA Database Link', __NAMESPACE__.'\\database_cpt_metabox', 'ama-databases', 'normal', 'high' );
}
add_action('add_meta_boxes', __NAMESPACE__.'\\database_cpt_meta_box_add');

function check_where_row($selection_and_or = null, $left_value = null, $selection_operator = null, $right_value = null) {
	if ((!isset($selection_and_or) || empty($selection_and_or))
	    || (!isset($left_value) || empty($left_value))
	    || (!isset($selection_operator) || empty($selection_operator))
	    || (!isset($right_value) || empty($right_value))) {
		return false;
	}

	return true;

}
