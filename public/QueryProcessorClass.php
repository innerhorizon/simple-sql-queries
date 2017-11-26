<?php
/**
 * Created by PhpStorm.
 * User: kenkitchen
 * Date: 10/28/17
 * Time: 8:55 AM
 */

namespace vinlandmedia\CRUDMeistR;


class QueryProcessor {
	var $my_table;
	var $my_tables;
	var $my_fields = array();
	var $my_qualified_fields = array();
	var $my_where_clause;

	var $my_joining_table;
	var $my_joining_fields = array();
	var $my_qualified_joining_fields = array();

	var $column_count;
	var $final_field_set = array();

	/**
	 * Created by PhpStorm.
	 * User: kenkitchen
	 * Date: 10/26/17
	 * Time: 5:42 PM
	 */
	public function sst_query_preprocessor($query) {
		$query_post = get_post($query);
		$this_query = sanitize_text_field($query_post->post_content);

		$this_query_array = explode(";", $this_query);
		$this_query_assoc = array();

		foreach ($this_query_array as $this_query_array_element) {
			$array_temp = explode(":", $this_query_array_element);
			$this_query_assoc[trim($array_temp[0])] = trim($array_temp[1]);
		}

		// valid query parameters
		//
		// table - required - the base table upon which to execute the query
		// fields - required - the fields from the base table to display
		//
		// joinon - optional - an optional table upon which to join
		// joinby - optional (required if "joinon" is used) - field/field
		// joinfields - optional (required if "joinon" is used) - fields from join table to display
		//
		// where - optional - field/value operator field/value, etc.

		$column_count = 0;

		$this->my_table = null;
		$this->my_tables = null;
		$this->my_qualified_fields = null;

		$this->my_joining_table = null;
		$this->my_joining_fields = null;
		$this->result_join_columns = null;

		// verify input of required parameters
		if (!isset($this_query_assoc['table']) || empty($this_query_assoc['table'])) {
			return 'You have an error in your query; a table name ("table=mytable") is required.';
		} elseif (!isset($this_query_assoc['fields']) || empty($this_query_assoc['fields'])) {
			return 'You have an error in your query; at least one field ("field=(field1, field2, etc)") is required.';
		}

		// if either join param was used, make sure both were filled in
		if (((!isset($this_query_assoc['joinon']) || empty($this_query_assoc['joinon'])) && (isset($this_query_assoc['joinby']) && !empty($this_query_assoc['joinby']))) ||
		    ((!isset($this_query_assoc['joinby']) || empty($this_query_assoc['joinby'])) && (isset($this_query_assoc['joinon']) && !empty($this_query_assoc['joinon'])))) {
			return 'You have an error in your query; "joinon" and "joinby" must both be used if either is specified.';
		}


		// if prefix wasn't used, apply it; otherwise, send as entered
		$this->my_table = $this->add_table_prefix_if_needed($this_query_assoc['table']);

		if (isset($this_query_assoc['joinon']) && !empty($this_query_assoc['joinon'])) {
			$this->my_joining_table = $this->add_table_prefix_if_needed($this_query_assoc['joinon']);
			if (isset($this_query_assoc['joinfields']) || !empty($this_query_assoc['joinfields'])) {
				$this->my_joining_fields = explode(",", str_replace(" ", "", $this_query_assoc['joinfields']));
			} else {
				$this->my_joining_fields = null;
			}
		} else {
			$this->my_joining_table = null;
			$this->my_tables = $this->my_table;
		}

		// query the info schema for the table columns
		$result_columns = get_table_columns($this->my_table);
		// if we don't get results, the table name must've been bogus
		if (!$result_columns) {
			return 'You have an error in your query; the specified table name ("table=' . $this->my_table . '") was not found in your database.';
		}

		if ($this->my_joining_table) {
			// query the info schema for the table columns
			$result_join_columns = get_table_columns($this->my_joining_table);
			// if we don't get results, the table name must've been bogus
			if (!$result_join_columns) {
				return 'You have an error in your query; the specified table name ("joinon=' . $this->my_joining_table . '") was not found in your database.';
			}

			$join_keys = explode("/", $this_query_assoc['joinby']);

			if (!find_in_multi_array($join_keys[0], (array)$result_columns)) {
				return 'You have an error in your query; the specified table name ("joinby=' . $join_keys[0] . '") was not found in your database.';
			} elseif (!find_in_multi_array($join_keys[1], (array)$result_join_columns)) {
				return 'You have an error in your query; the specified table name ("joinby=' . $join_keys[1] . '") was not found in your database.';
			}

			$this->my_tables = $this->my_table . ", " . $this->my_joining_table;
		}


		// create array of field input
		$this->my_fields = explode(",", str_replace(" ", "", $this_query_assoc['fields']));

		// verify that the fields entered exist in the table
		foreach ($this->my_fields as $my_field) {
			$this->column_count++;
			$column_found = false;
			foreach ($result_columns as $result_column) {
				if ($result_column->column_name == $my_field) {
					$column_found = true;
					break;
				}
			}
			if (!$column_found) {
				return 'You have an error in your query; a specified column name ("fields=' . $my_field . '") was not found in your database.';
			} else {
				$this->my_qualified_fields[$this->column_count-1] = $this->my_table . '.' . $my_field . ' as ' . $this->my_table . '_' . $my_field;
			}
		}

		$join_column_count = 0;

		if ($this->my_joining_fields) {
			// verify that the fields entered exist in the table
			foreach ($this->my_joining_fields as $my_joining_field) {
				$this->column_count++;
				$join_column_count++;
				$column_found = false;
				foreach ($result_join_columns as $result_column) {
					if ($result_column->column_name == $my_joining_field) {
						$column_found = true;
					}
				}
				if (!$column_found) {
					return 'You have an error in your query; a specified column name ("joinfields=' . $my_joining_field . '") was not found in your database.';
				} else {
					$this->my_qualified_joining_fields[$join_column_count] = $this->my_joining_table . '.' . $my_joining_field . ' as ' . $this->my_joining_table . '_' . $my_joining_field;
				}
			}
		}

		// if there is a where clause, evaluate it
		if (!isset($this_query_assoc['where']) || empty($this_query_assoc['where'])) {
			if ($this->my_joining_table) {
				$this->my_where_clause = ' WHERE ' . $this->my_table . '.' . $join_keys[0] . ' = ' . $this->my_joining_table . '.' . $join_keys[1];
			} else {
				$this->my_where_clause = "";
			}
		} else {
			// prepare where clause
			$this->my_where_clause = ' WHERE ' . $this_query_assoc['where'];

			if ($this->my_joining_table) {
				$this->my_where_clause .= ' AND ' . $this->my_table . '.' . $join_keys[0] . ' = ' . $this->my_joining_table . '.' . $join_keys[1];
			}
		}

		$derived_query = "SELECT " . $this->my_qualified_fields[0] . " FROM " . $this->my_tables . $this->my_where_clause;

		foreach ($this->my_qualified_fields as $my_qualified_field) {
			$split_qualifiers = explode(' as ', $my_qualified_field);
			array_push($this->final_field_set, $split_qualifiers[1]);
		}
		foreach ($this->my_qualified_joining_fields as $my_qualified_joining_field) {
			$split_qualifiers = explode(' as ', $my_qualified_joining_field);
			array_push($this->final_field_set, $split_qualifiers[1]);
		}

		return $derived_query;

	}

	public function sst_query_processor($query) {
		global $wpdb;

		$query_output = "";

		// count rows
		$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM " . $this->my_tables . $this->my_where_clause);

		// if nothing found, return a message
		if (0 == $rowcount) {
			return 'Your query did not return any results';
		}

		// one row, one column -- return just the data
		if (1 == $rowcount && 1 == $this->column_count) {
			$query_results = $wpdb->get_var("SELECT " . $this->my_qualified_fields[0] . " FROM " . $this->my_tables . $this->my_where_clause);
			$query_output = $query_results;
		}

		// one row, multiple columns
		if (1 == $rowcount && $this->column_count > 1) {
			$ticker = 0;

			$mysql_query = "SELECT ";

			foreach ($this->my_qualified_fields as $my_field) {
				$mysql_query .= $my_field . ", ";
			}

			if ($this->my_qualified_joining_fields) {
				foreach ($this->my_qualified_joining_fields as $my_field) {
					$mysql_query .= $my_field . ", ";
				}
			}

			$mysql_query = rtrim(rtrim($mysql_query), ",");

			$mysql_query .= " FROM " . $this->my_tables . $this->my_where_clause;

			$query_results = $wpdb->get_results($mysql_query);

			// display the output
			$query_output = "";
			foreach ($this->final_field_set as $my_field) {
				$ticker++;

				if ($ticker == $rowcount) {
					//$query_output = rtrim(rtrim($query_output), ",");
					$query_output .= ' and ' . $query_results[0]->$my_field;
				} else {
					$query_output .= $query_results[0]->$my_field . ", ";
				}
			}

		}

		// multiple rows, one column
		if ($rowcount > 1 && 1 == $this->column_count) {
			$ticker = 0;

			$mysql_query = "SELECT " . $this->my_qualified_fields[0] . " FROM " . $this->my_tables . $this->my_where_clause;

			$query_results = $wpdb->get_results($mysql_query);

			// display the output
			$query_output = "";
			foreach ($query_results as $query_result) {
				$ticker++;

				if ($ticker == $rowcount) {
					//$query_output = rtrim(rtrim($query_output), ",");
					$query_output .= ' and ' . $query_result->{$this->final_field_set[0]};
				} else {
					$query_output .= $query_result->{$this->final_field_set[0]} . ", ";
				}
			}

			$query_output = rtrim(rtrim($query_output), ",");

		}

		// multiple rows, multiple columns
		if ($rowcount > 1 && $this->column_count > 1) {
			$mysql_query = "SELECT ";

			foreach ($this->my_qualified_fields as $my_field) {
				$mysql_query .= $my_field . ", ";
			}

			if ($this->my_qualified_joining_fields) {
				foreach ($this->my_qualified_joining_fields as $my_field) {
					$mysql_query .= $my_field . ", ";
				}
			}

			$mysql_query = rtrim(rtrim($mysql_query), ",");

			$mysql_query .= " FROM " . $this->my_tables . $this->my_where_clause;

			$query_results = $wpdb->get_results($mysql_query);

			// display the output
			$query_output = '<table class="w3-table">';
			foreach ($query_results as $query_result) {
				$query_output .= '<tr>';
				foreach ($this->final_field_set as $my_field) {
					$query_output .= '<td>';
					$query_output .= $query_result->$my_field . " ";
					$query_output .= '</td>';
				}
				//			foreach ($my_joining_fields as $my_joining_field) {
				//				$query_output .= '<td>';
				//				$query_output .= $query_result->$my_joining_field . " ";
				//				$query_output .= '</td>';
				//			}
				$query_output .= '</tr>';
			}
			$query_output .= '</table>';

		}

		return $query_output;
	}


	public function tig4wp_function_preprocessor($function) {
		$derived_function = "";

		return $derived_function;
	}

	public function tig4wp_function_processor($function) {
		$function_results = "";

		return $function_results;
	}


	public function sst_template_preprocessor($template) {
		$derived_template = "";

		return $derived_template;
	}

	public function sst_template_processor($template) {
		$template_results = "";

		return $template_results;
	}

	private function add_table_prefix_if_needed($table_name) {
		global $wpdb;

		if (substr($table_name, 0, strlen($wpdb->prefix)) != $wpdb->prefix) {
			$my_table = $wpdb->prefix . $table_name;
		} else {
			$my_table = $table_name;
		}

		return $my_table;
	}

}