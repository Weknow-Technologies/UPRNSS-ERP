<?php

	// $db_emb = mysqli_connect("p:localhost", "root", "mysql", "embuprnss_o1");
	// $db_emb = mysqli_connect("43.205.221.231", "upcod", "Alpha@13579", "uprnss_pro_db");
	$db_emb = mysqli_connect("137.97.124.122", "upcds", "Upcds@321", "uprnss_pro_db",3306 );
	if(!$db_emb){
		die("Error 2.01 : Contact Administrator.");
	}
	// else{
		// echo 'done';
	// }
	mysqli_query($db_emb, 'SET character_set_results=utf8'); 
	mysqli_query($db_emb, 'SET names utf8'); 
	mysqli_query($db_emb, 'SET character_set_client=utf8'); 
	mysqli_query($db_emb, 'SET character_set_connection=utf8'); 
	mysqli_query($db_emb, 'SET character_set_results=utf8'); 
	mysqli_query($db_emb, 'SET collation_connection=utf8_general_ci'); 	

?>