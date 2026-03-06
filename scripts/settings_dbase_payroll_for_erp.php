<?php
	date_default_timezone_set("Asia/Kolkata");
	// $db_payroll = mysqli_connect("p:localhost", "root", "mysql", "cloudice_uprnss_payroll");
	$db_payroll = mysqli_connect("localhost", "root", "mysql", "cloudice_uprnss_payroll");
	if(!$db_payroll){
		die("Error 1 : Contact Administrator.");
	}


?>