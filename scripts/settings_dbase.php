<?php
date_default_timezone_set("Asia/Kolkata");
//	$db = mysqli_connect("p:localhost", "root", "mysql", "cloudice_uprnss");
$db = mysqli_connect("p:localhost", "root", "mysql", "uprnss_erp");
if (!$db) {
	die("Error 1 : Contact Administrator.");
}
mysqli_query($db, 'SET character_set_results=utf8');
mysqli_query($db, 'SET names utf8');
mysqli_query($db, 'SET character_set_client=utf8');
mysqli_query($db, 'SET character_set_connection=utf8');
mysqli_query($db, 'SET character_set_results=utf8');
mysqli_query($db, 'SET collation_connection=utf8_general_ci');

function execute_query($query)
{
	global $db;
	$result = mysqli_query($db, $query);
	return $result;
}

function insert_id($db = '')
{
	global $db;
	return mysqli_insert_id($db);
}

function select_data($table, $fields, $where = '', $join = '', $join_on = '', $union = '', $union_cols = '')
{

}

function update_data($table, $fields, $values, $where)
{

}

function delete_data($table, $fields, $values, $where)
{

}

?>