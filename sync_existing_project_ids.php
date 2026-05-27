<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("scripts/settings.php");         // expects $db  (main DB with uprnss_*)
include("scripts/setting_dbase_emb.php"); // expects $db_emb (EMB DB with projects etc.)

echo "<h2>Syncing Existing Project IDs...</h2>";

// 1. Ensure the column 'emb_project_id' exists in uprnss_project_temp
$res_col = mysqli_query($db, "SHOW COLUMNS FROM `uprnss_project_temp` LIKE 'emb_project_id'");
if (!$res_col || mysqli_num_rows($res_col) === 0) {
    echo "<p>Adding column 'emb_project_id' to uprnss_project_temp table...</p>";
    $alter_res = mysqli_query($db, "ALTER TABLE `uprnss_project_temp` ADD COLUMN `emb_project_id` INT NULL DEFAULT NULL AFTER `erp_code`");
    if ($alter_res) {
        echo "<p style='color:green;'>Column added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Failed to add column: " . mysqli_error($db) . "</p>";
    }
}

// 2. Fetch all projects from EMB that have erp_code set
$sql = "SELECT id, erp_code, project_name FROM projects WHERE erp_code IS NOT NULL AND erp_code != ''";
$query = mysqli_query($db_emb, $sql);

if (!$query) {
    die("<p style='color:red;'>Error fetching from EMB projects: " . mysqli_error($db_emb) . "</p>");
}

$total_found = mysqli_num_rows($query);
echo "<p>Found <strong>$total_found</strong> projects in EMB with ERP Code.</p>";

$synced_count = 0;
$skipped_count = 0;

echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>";
echo "<tr><th>EMB Project ID</th><th>ERP Code</th><th>Project Name</th><th>Status</th></tr>";

while ($row = mysqli_fetch_assoc($query)) {
    $emb_id = (int)$row['id'];
    $erp_code = trim($row['erp_code']);
    $proj_name = htmlspecialchars($row['project_name']);

    $safe_erp = mysqli_real_escape_string($db, $erp_code);
    
    // Check if there is a matching row in uprnss_project_temp
    $check_sql = "SELECT sno, emb_project_id FROM uprnss_project_temp WHERE TRIM(erp_code) = TRIM('$safe_erp') LIMIT 1";
    $check_res = mysqli_query($db, $check_sql);
    
    if ($check_res && mysqli_num_rows($check_res) > 0) {
        $temp_row = mysqli_fetch_assoc($check_res);
        $temp_sno = (int)$temp_row['sno'];
        
        // Update the emb_project_id
        $update_sql = "UPDATE uprnss_project_temp SET emb_project_id = '$emb_id' WHERE sno = '$temp_sno'";
        if (mysqli_query($db, $update_sql)) {
            echo "<tr>
                    <td>$emb_id</td>
                    <td>" . htmlspecialchars($erp_code) . "</td>
                    <td>$proj_name</td>
                    <td style='color:green;'>Updated (temp.sno = $temp_sno)</td>
                  </tr>";
            $synced_count++;
        } else {
            echo "<tr>
                    <td>$emb_id</td>
                    <td>" . htmlspecialchars($erp_code) . "</td>
                    <td>$proj_name</td>
                    <td style='color:red;'>Update Failed: " . htmlspecialchars(mysqli_error($db)) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr>
                <td>$emb_id</td>
                <td>" . htmlspecialchars($erp_code) . "</td>
                <td>$proj_name</td>
                <td style='color:orange;'>Not found in temp table</td>
              </tr>";
        $skipped_count++;
    }
}

echo "</table>";
echo "<h3>Sync Completed!</h3>";
echo "<p>Total Synced: <strong style='color:green;'>$synced_count</strong></p>";
echo "<p>Total Skipped (Not found in ERP temp table): <strong style='color:orange;'>$skipped_count</strong></p>";
?>
