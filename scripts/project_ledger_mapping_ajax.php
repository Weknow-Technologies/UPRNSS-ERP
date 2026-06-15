<?php
include("settings.php");

if(isset($_POST['action'])) {
    
    // Fetch Projects and Ledgers for the selected Unit
    if($_POST['action'] == 'get_mapping_data') {
        $unit_id = mysqli_real_escape_string($db, $_POST['unit_id']);
        
        $response = [
            'projects' => [],
            'ledgers' => []
        ];
        
        // Fetch projects for this unit
        // We fetch all projects. We'll indicate if it's already mapped by checking if any ledger has its erp_code.
        $sql_proj = "SELECT sno, project_name, erp_code FROM uprnss_project_temp WHERE division_id = '$unit_id' AND erp_code IS NOT NULL AND erp_code != ''";
        $res_proj = execute_query($sql_proj);
        while($row = mysqli_fetch_assoc($res_proj)) {
            $response['projects'][] = $row;
        }
        
        // Fetch ledgers for this unit
        $sql_ledger = "SELECT sno, cus_name, erp_code FROM billit_customer WHERE unit_id = '$unit_id' AND category = 'ledger'";
        $res_ledger = execute_query($sql_ledger);
        while($row = mysqli_fetch_assoc($res_ledger)) {
            $response['ledgers'][] = $row;
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Save a single mapping
    if($_POST['action'] == 'save_mapping') {
        $ledger_sno = mysqli_real_escape_string($db, $_POST['ledger_sno']);
        $erp_code = mysqli_real_escape_string($db, $_POST['erp_code']);
        
        // Reset this erp_code from any other ledger in this unit to ensure 1-to-1 mapping
        // Or globally reset it
        $sql_reset = "UPDATE billit_customer SET erp_code = NULL WHERE erp_code = '$erp_code'";
        execute_query($sql_reset);
        
        if($ledger_sno != '') {
            $sql_update = "UPDATE billit_customer SET erp_code = '$erp_code' WHERE sno = '$ledger_sno'";
            if(execute_query($sql_update)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
            }
        } else {
            // Unmapping
            echo json_encode(['status' => 'success']);
        }
        exit;
    }
}
?>
