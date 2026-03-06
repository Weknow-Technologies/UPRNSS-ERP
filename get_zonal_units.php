<?php
include("scripts/settings.php");
include("scripts/setting_dbase_emb.php");

if (isset($_POST['zone_ids']) && is_array($_POST['zone_ids'])) {
    $zone_ids = array_map('intval', $_POST['zone_ids']);
    $zone_ids_str = implode(',', $zone_ids);

    $query = "SELECT * FROM zonal_units WHERE zone_master_id IN ($zone_ids_str) ORDER BY name ASC";
    $result = mysqli_query($db_emb, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
        }
    } else {
        echo '<option value="">No zonal units found</option>';
    }
} else {
    echo '<option value="">Invalid zone</option>';
}
?>