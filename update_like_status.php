<?php
include("scripts/settings.php"); // Replace with your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = intval($_POST['file_id']);

    // Check current like_status
    $query = "SELECT like_status FROM transaction_project_file WHERE sno = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->bind_result($like_status);
    $stmt->fetch();
    $stmt->close();

    // Toggle like_status
    $new_like_status = $like_status == 1 ? 0 : 1;

    // Update the database
    $update_query = "UPDATE transaction_project_file SET like_status = ? WHERE sno = ?";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bind_param("ii", $new_like_status, $file_id);
    $success = $update_stmt->execute();
    $update_stmt->close();

    echo json_encode([
        'success' => $success,
        'new_like_status' => $new_like_status,
    ]);
}
?>
