<?php
date_default_timezone_set('Asia/Calcutta');
$time = mktime(true);
include("settings.php");

if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $stmt = $db->prepare("SELECT old_employee_code FROM dp_personal_info WHERE old_employee_code LIKE ?");
    $searchTerm = "%".$query."%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $suggestions = array();
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['old_employee_code'];
    }
    
    echo json_encode($suggestions);
}

if(empty($data)!=true){
	echo json_encode($data);
}
?>