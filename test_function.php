<?php
include("scripts/settings.php");


update_project_officers($db);

// update_cloumn_invoice_civii_to_project_temp();

function update_project_officers($db) {
    // mapping: officer type -> columns in uprnss_project_temp
    $map = [
        'SE' => ['name_col'=>'superintendent_engineer_name','from_col'=>'superintendent_engineer_from','to_col'=>'superintendent_engineer_to'],
        'EE' => ['name_col'=>'executive_engineer_name','from_col'=>'executive_engineer_from','to_col'=>'executive_engineer_to'],
        'AE' => ['name_col'=>'assistant_engineer_name','from_col'=>'assistant_engineer_from','to_col'=>'assistant_engineer_to'],
        'JE' => ['name_col'=>'junior_engineer_name','from_col'=>'junior_engineer_from','to_col'=>'junior_engineer_to']
    ];

    $esc = function($v) use ($db) { return mysqli_real_escape_string($db, trim((string)($v ?? ''))); };
    $valid_date = function($d) {
        if ($d === null || $d === '') return null;
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : null;
    };

    $countUpdated = 0;
    $countInserted = 0;
    $countSkipped = 0;
    $errors = [];

    $sql = "SELECT `sno`, `erp_code`, `new_project_id`, `new_project_trans_id`,
                   `junior_engineer_name`, `assistant_engineer_name`, `executive_engineer_name`,
                   `executive_engineer_from`, `executive_engineer_to`, `superintendent_engineer_name`,
                   `superintendent_engineer_from`, `superintendent_engineer_to`
            FROM `uprnss_project_temp` WHERE status!=5";
    $res = mysqli_query($db, $sql);
    if (!$res) {
        echo "Error fetching uprnss_project_temp: " . mysqli_error($db);
        return;
    }

    mysqli_begin_transaction($db);

    while ($row = mysqli_fetch_assoc($res)) {
        $project_sno = (int)$row['sno'];
        $erp_code = $esc($row['erp_code'] ?? '');

        foreach ($map as $otype => $cols) {
            $raw_name = isset($row[$cols['name_col']]) ? trim($row[$cols['name_col']]) : '';
            $raw_from = isset($row[$cols['from_col']]) ? trim($row[$cols['from_col']]) : '';
            $raw_to   = isset($row[$cols['to_col']]) ? trim($row[$cols['to_col']]) : '';

            // अगर नाम खाली है तो skip
            if ($raw_name === '') { $countSkipped++; continue; }

            $from_date = $valid_date($raw_from);
            $to_date   = $valid_date($raw_to);

            $name_sql = "'" . $esc($raw_name) . "'";
            $from_sql = ($from_date !== null) ? ("'".$esc($from_date)."'") : "NULL";
            $to_sql   = ($to_date   !== null) ? ("'".$esc($to_date)."'") : "NULL";

            // check if officer record exists for this project + type
            $chk_sql = "SELECT id FROM project_officers WHERE project_sno = '".intval($project_sno)."' AND officer_type = '". $esc($otype) ."' LIMIT 1";
            $chk_res = mysqli_query($db, $chk_sql);
            if ($chk_res === false) {
                $errors[] = "Check query failed: " . mysqli_error($db);
                continue;
            }

            if (mysqli_num_rows($chk_res) > 0) {
                $rowchk = mysqli_fetch_assoc($chk_res);
                $id = (int)$rowchk['id'];

                // Update: store officer name directly (no dp_personal_info lookup)
                $upd = "UPDATE project_officers SET
                          dp_personal_info_id = {$name_sql},
                          erp_code = '{$erp_code}',
                          from_date = {$from_sql},
                          to_date   = {$to_sql},
                          remarks = 'Auto-updated from uprnss_project_temp',
                          updated_at = NOW()
                        WHERE id = {$id}";
                if (!mysqli_query($db, $upd)) {
                    $errors[] = "Update failed (project_sno: {$project_sno}, type: {$otype}): " . mysqli_error($db);
                } else {
                    $countUpdated++;
                }
            } else {
                // Insert: store officer name directly
                $ins = "INSERT INTO project_officers
                        (project_sno, erp_code, officer_type, dp_personal_info_id, from_date, to_date, remarks, created_by, created_at)
                        VALUES (
                          '".intval($project_sno)."',
                          '{$erp_code}',
                          '". $esc($otype) ."',
                          {$name_sql},
                          {$from_sql},
                          {$to_sql},
                          'Auto-inserted from uprnss_project_temp',
                          '0',
                          NOW()
                        )";
                if (!mysqli_query($db, $ins)) {
                    $errors[] = "Insert failed (project_sno: {$project_sno}, type: {$otype}): " . mysqli_error($db);
                } else {
                    $countInserted++;
                }
            }
        } // foreach map
    } // while rows

    if (count($errors) === 0) {
        mysqli_commit($db);
        echo "<p style='color:green'>Sync completed. Inserted: {$countInserted}, Updated: {$countUpdated}, Skipped(empty): {$countSkipped}</p>";
    } else {
        mysqli_rollback($db);
        echo "<p style='color:red'>Errors occurred, transaction rolled back. See list below:</p><ul style='color:red'>";
        foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>";
        echo "</ul>";
    }
}



function update_cloumn_invoice_civii_to_project_temp(){
    global $db; // access the $db connection from global scope

    $allowedColumnsTable1 = [
        'sanction_date', 'sanction_cost', 'revised_date', 'revised_cost', 
        'land_receive_date', 'work_start_date', 'work_completion_date', 
        'technical_sanction_date', 'technical_sanction_no', 'tender_status', 
        'go_type', 'admin_go_no', 'admin_go_date', 'financial_go_no', 
        'financial_go_date', 'financial_go_amount', 'last_fy_tot_exp', 
        'current_fy_last_month_exp', 'current_month_exp', 'current_fy_tot_exp', 
        'tot_exp_project', 'rcv_tot_amount', 'balance_amt_cost', 'balance_amt_project', 
        'last_update', 'project_status', 'payable_gst_on_project', 
        'architect_id', 'structural_architect_id', 'soil_testing_status', 
        'estimation_formation_status', 'project_status_1', 'controversial_date', 
        'lack_money_date', 'contract_date', 'inventory_date', 'progress_date', 
        'complete_date', 'in_hand_date', 'project_status_2', 'remark', 
        'junior_engineer_name', 'assistant_engineer_name', 'executive_engineer_name', 
        'executive_engineer_from', 'executive_engineer_to', 'superintendent_engineer_name', 
        'superintendent_engineer_from', 'superintendent_engineer_to', 
        'revised_estimate_amount', 'revised_estimate_remark', 'revised_estimate_date', 
        'revised_dispatch_status', 'revised_remittance_amount', 'revised_remittance_date'
    ];
	
	
	// $allowedColumnsTable1 = [ 
        // 'land_receive_date', 'work_start_date', 'work_completion_date', 
        // 'technical_sanction_date', 'technical_sanction_no', 'tender_status', 
		// 'last_fy_tot_exp', 
        // 'current_fy_last_month_exp', 'current_month_exp', 'current_fy_tot_exp', 
        // 'tot_exp_project', 'rcv_tot_amount', 'balance_amt_cost', 'balance_amt_project', 
        // 'last_update', 'project_status', 'payable_gst_on_project', 
        // 'architect_id', 'structural_architect_id', 'soil_testing_status', 
        // 'estimation_formation_status', 'project_status_1', 'controversial_date', 
        // 'lack_money_date', 'contract_date', 'inventory_date', 'progress_date', 
        // 'complete_date', 'in_hand_date', 'project_status_2', 'remark', 
        // 'junior_engineer_name', 'assistant_engineer_name', 'executive_engineer_name', 
        // 'executive_engineer_from', 'executive_engineer_to', 'superintendent_engineer_name', 
        // 'superintendent_engineer_from', 'superintendent_engineer_to', 
        // 'revised_estimate_amount', 'revised_estimate_remark', 'revised_estimate_date', 
        // 'revised_dispatch_status', 'revised_remittance_amount', 'revised_remittance_date'
    // ];
	

    $projects = mysqli_query($db, "SELECT * FROM uprnss_project_temp WHERE status!='5'");

    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr>
            <th>Sr. No.</th>
            <th>SNO (Table1)</th>
            <th>Project Invoice SNO</th>";

    foreach($allowedColumnsTable1 as $col) {
        echo "<th>$col Table1</th><th>$col Table2</th>";
    }
    echo "<th>Update Status</th>";
    echo "</tr>";

    $counter = 1;

    while($project = mysqli_fetch_assoc($projects)) {
        $invoice_sno = $project['invoice_civil_sno'];
        $row2 = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM invoice_civil WHERE sno='$invoice_sno'"));

        if($row2) {
            echo "<tr>";
            echo "<td>".$counter++."</td>";
            echo "<td>".$project['sno']."</td>";
            echo "<td>$invoice_sno</td>";

            $updates = [];
            $anyUpdate = false;

            foreach($allowedColumnsTable1 as $col) {
                $val1 = isset($project[$col]) ? $project[$col] : '';
                $val2 = isset($row2[$col]) ? $row2[$col] : '';

                $cellStyle1 = $cellStyle2 = "";
                if($val2 !== '' && $val2 != $val1) {
                    $cellStyle1 = 'style="background-color:#f8d7da;"';
                    $cellStyle2 = 'style="background-color:#f8d7da;"';
                    $updates[] = "$col='".mysqli_real_escape_string($db, $val2)."'";
                    $anyUpdate = true;
                }

                echo "<td $cellStyle1>$val1</td>";
                echo "<td $cellStyle2>$val2</td>";
            }

            if(!empty($updates)) {
                $update_sql = "UPDATE uprnss_project_temp SET ".implode(",", $updates)." WHERE sno='".$project['sno']."'";
                // Uncomment this to execute update
                mysqli_query($db, $update_sql);
            }

            echo "<td>".($anyUpdate ? 'Yes' : 'No')."</td>";
            echo "</tr>";

        } else {
            echo "<tr><td colspan='".(count($allowedColumnsTable1)*2 + 3)."'>No matching invoice_civil row found for $invoice_sno</td></tr>";
        }
    }
    echo "</table>";
}



?>
