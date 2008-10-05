<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}

	function get_table_from_db($table) {
		global $connect;
		global $CONFIG;
		$num_rows_query = mysql_query("SELECT * FROM ".$CONFIG['db_prefix'].$table,$connect);
		$num_rows = mysql_num_rows($num_rows_query);
		$i = 0;
		while($i<$num_rows) {
			$i++;
			$tabledata[$i] = mysql_fetch_assoc($num_rows_query);
			}
		return $tabledata;
		}
	function get_row_from_db($table,$modifier = "", $content = "*") {
		global $connect;
		global $CONFIG;
		$row_i_query = "SELECT * FROM ".$CONFIG['db_prefix'].$table." ".$modifier;
		$row_query = mysql_query($row_i_query,$connect);
		$num_rows = mysql_num_rows($row_query);
		echo mysql_error($connect);
		$i = 1;
		while ($i <= $num_rows) {
			$rows[$i] = mysql_fetch_array($row_query);
			$i++;
			}
		$rows['num_rows'] = $num_rows;
		$rows['query'] = $row_i_query;
		return $rows;
		}
?>