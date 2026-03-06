<?php
	date_default_timezone_set("Asia/Kolkata");
	// $db = mysqli_connect("p:localhost", "root", "mysql", "cloudice_uprnss_2023_10_08");
	// $db = mysqli_connect("p:localhost", "root", "mysql", "cloudice_uprnss");
	// $db_erp = mysqli_connect("p:localhost", "root", "mysql", "cloudice_uprnss_03_01_2024");
	$db_erp = mysqli_connect("p:localhost", "cloudice", "KR1wSsahbhBFHCe@", "cloudice_uprnss");
	if(!$db_erp){
		die("Error 1 : Contact Administrator.");
	}


?>