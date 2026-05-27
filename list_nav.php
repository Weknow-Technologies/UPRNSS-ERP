<?php
include("scripts/settings.php");
$sql = "SELECT * FROM navigation ORDER BY link_description";
$result = execute_query($sql);
while($row = mysqli_fetch_assoc($result)){
    echo "ID: " . $row['sno'] . " | Name: " . $row['link_description'] . " | Link: " . $row['hyper_link'] . "\n";
}
?>
