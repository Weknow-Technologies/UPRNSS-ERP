<?php
include("d:/htdocs/uprnsserp_08_06_2026/scripts/settings.php");
$res = mysqli_query($db, "SHOW TABLES");
while($row = mysqli_fetch_array($res)) {
    echo $row[0] . "\n";
}
?>
