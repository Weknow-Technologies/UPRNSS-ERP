<?php
$employee_id = $_SESSION['usersno'];
// function createApprovalRequest($module_name, $row_id) {
//     global $db;
//     $stmt = $db->prepare("INSERT INTO approval_requests (module_name, row_id) VALUES (?, ?)");
//     $stmt->bind_param("si", $module_name, $row_id);
//     $stmt->execute();
//     return $db->insert_id;
// }

function createApprovalRequest($module_name, $row_id) {
    global $db;

    if ($module_name === 'project_note_sheet') {
        $current_level = 2;
        $stmt = $db->prepare("INSERT INTO approval_requests (module_name, row_id, current_level) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $module_name, $row_id, $current_level);
    } else {
        $stmt = $db->prepare("INSERT INTO approval_requests (module_name, row_id) VALUES (?, ?)");
        $stmt->bind_param("si", $module_name, $row_id);
    }

    $stmt->execute();
    return $db->insert_id;
}


function getPendingRequestsForUser($employee_id, $moduleName) {
    global $db;

    // Step 1: Get logged-in employee's designation and division
    $empRes = $db->query("SELECT type FROM users WHERE sno = $employee_id");
    $emp = $empRes->fetch_assoc();
    $designation_id = $emp['type'];

    // Step 2: Fetch only those requests where employee's division matches project's division
	   $division_ids = array_map('intval', $_SESSION['divisions']); // Always sanitize!
		$division_ids_str = implode(",", $division_ids);

		$sql = "SELECT *
				FROM approval_requests ar
				JOIN approvals_config ac 
				  ON ar.module_name = ac.module_name AND ar.current_level = ac.level
				JOIN {$moduleName} pr 
				  ON pr.id = ar.row_id
				JOIN uprnss_project_temp p 
				  ON pr.project_id = p.sno
				WHERE ac.designation_id = {$designation_id}
				  AND ar.status = 'pending'
				  AND p.division_id IN ($division_ids_str)";

    return $db->query($sql);
}


function performApprovalAction($moduleName, $request_id, $employee_id, $action, $remarks = '') {
    global $db;
    $req = $db->query("SELECT * FROM approval_requests WHERE id = $request_id")->fetch_assoc();
    $module = $req['module_name'];
    $level = $req['current_level'];

    $stmt = $db->prepare("INSERT INTO approval_logs (request_id, level, approved_by, action, remarks) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $request_id, $level, $employee_id, $action, $remarks);
    $stmt->execute();

    if ($action == 'approved'){
        $next = $db->query("SELECT * FROM approvals_config WHERE module_name = '$module' AND level = " . ($level + 1));
        if ($next->num_rows > 0) {
            $db->query("UPDATE approval_requests SET current_level = current_level + 1 WHERE id = $request_id");
        } else {
            $db->query("UPDATE approval_requests SET status = 'approved' WHERE id = $request_id");
        }
    } else {
        $db->query("UPDATE approval_requests SET status = 'rejected' WHERE id = $request_id");
    }
}



function getApprovalTrailRow($moduleName, $rowId) {
    global $db;

    // Fetch approval request
    $reqRes = $db->query("SELECT * FROM approval_requests WHERE module_name = '$moduleName' AND row_id = $rowId");
    if ($reqRes->num_rows == 0) {
        return "<td colspan='3'>कोई अनुरोध नहीं मिला</td>";
    }

    $req = $reqRes->fetch_assoc();
    $request_id = $req['row_id'];
    $current_level = $req['current_level'];
    $status = ucfirst($req['status']);

    // Get current level approver (if needed)
    $levelInfo = $db->query("SELECT d.user_type FROM approvals_config ac 
                             JOIN user_type d ON ac.designation_id = d.sno
                             WHERE ac.module_name = '$moduleName' AND ac.level = $current_level")->fetch_assoc();
    $currentStage = "Level $current_level";
    if ($levelInfo) {
        $currentStage .= " ({$levelInfo['user_type']})";
    }

    // Prepare TDs
    $html = "<td>$currentStage</td>
			 <td>$status</td>
			 <td class='no-print'>";

	if ($moduleName == "project_note_sheet") {
		$html .= "<a href='note_sheet_trail.php?request_id=$request_id' target='_blank'>View Trail</a>";
	} elseif ($moduleName == "project_bill_adviser") {
		$html .= "<a href='paymnet_adviser_trail.php?request_id=$request_id' target='_blank'>View Trail</a>";
	}
	elseif ($moduleName == "invoice_deduction_release") {
		$html .= "<a href='print_release_advice.php?request_id=$request_id' target='_blank'>View Trail</a>";
	} elseif ($moduleName == "inspection_reports") {
		$html .= "<a href='inspection_report.php' target='_blank'>View Reports</a>";
	}

	$html .= "</td>";

	return $html;
}
function getDesignation($employee_id) {
    global $db;
    $res = $db->query("SELECT type FROM users WHERE sno = $employee_id");
    return ($res && $r = $res->fetch_assoc()) ? $r['type'] : null;
}


function processApprovalAction($request_id, $employee_id, $action,$moduleName, $remarks = '') {
    global $db;

    // 1. Get approval request
    $req = $db->query("SELECT * FROM approval_requests WHERE id = $request_id AND module_name='$moduleName'")->fetch_assoc();
    if (!$req) return false;

    $module_name = $moduleName;
    $module_row_id = $req['row_id']; // Link to project_note_sheet row
    $current_level = (int)$req['current_level'];

    // 2. Log approval action
    $stmt = $db->prepare("INSERT INTO approval_logs (request_id, level, approved_by, action, remarks) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $request_id, $current_level, $employee_id, $action, $remarks);
    $stmt->execute();

    // 3. Action handler
    if ($action === 'forward') {
        $next = $db->query("SELECT * FROM approvals_config WHERE module_name = '$module_name' AND level = " . ($current_level + 1));

        if ($next->num_rows > 0) {
            // Move to next stage
            $db->query("UPDATE approval_requests SET current_level = current_level + 1, status = 'pending' WHERE id = $request_id");
			
        } else {
			$sqlupdate = "UPDATE $module_name SET `status` = 1 WHERE id = $module_row_id";
			$db->query($sqlupdate);
			
            // Final approval stage
            $db->query("UPDATE approval_requests SET status = 'approved' WHERE id = $request_id");
			$current_time = date('Y-m-d H:i:s');
            //  Copy project_note_sheet data into bill_adviser
            if ($module_name === 'project_note_sheet') {
				
				$sqlgenrateadvice = "UPDATE $module_name SET `note_sheet_status` = 1 WHERE id = $module_row_id";
				$db->query($sqlgenrateadvice);
				
                $original = $db->query("SELECT * FROM project_note_sheet WHERE id = $module_row_id")->fetch_assoc();
				
                if ($original) {
					$vendor = mysqli_fetch_assoc(execute_query('SELECT tender_allotment.sno,vendor.sno as vendor_id, firm_name FROM tender_allotment LEFT JOIN vendor ON tender_allotment.project_awarded_to = vendor.sno WHERE project_id = "'.$original['project_id'].'" AND tender_allotment.status != 5'));
					
					$related_snos = $original['related_bill_snos']; // e.g., '101,102,103'
					$query = "SELECT * FROM `invoice_account_fund_transafer` WHERE sno IN ($related_snos) LIMIT 1";
					$to_acc = mysqli_fetch_assoc(execute_query($query));
					
                   $stmtCopy = $db->prepare("
					INSERT INTO project_bill_adviser (
						unit_account, vendor_id, notesheet_id, project_id, related_bill_snos, bill_count,
						note_sheet_no, total_transfer, total_net_pay, total_expense, total_rcv_amt,
						total_privious_expense, grand_total_expense, created_at
					) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				");

				$query = "
					INSERT INTO project_bill_adviser (
						unit_account, vendor_id, notesheet_id, project_id, related_bill_snos, bill_count,
						note_sheet_no, total_transfer, total_net_pay, total_expense, total_rcv_amt,
						total_privious_expense, grand_total_expense, created_at
					) VALUES (
						'{$to_acc['to_account_no']}', '{$vendor['vendor_id']}', '{$original['id']}',
						'{$original['project_id']}', '{$original['related_bill_snos']}', '{$original['bill_count']}',
						'{$original['note_sheet_no']}', '{$original['total_transfer']}', '{$original['total_net_pay']}',
						'{$original['total_expense']}', '{$original['total_rcv_amt']}', '{$original['total_privious_expense']}',
						'{$original['grand_total_expense']}', '$current_time'
					)
				";

				$db->query($query);
				$insert_id = $db->insert_id;
					$module_name_bill="project_bill_adviser";
					createApprovalRequest($module_name_bill, $insert_id);
					
                }
            }
        }

    } elseif ($action === 'revert') {
        // Handle revert
        if ($current_level > 1) {
            $db->query("UPDATE approval_requests SET current_level = current_level - 1, status = 'reverted' WHERE id = $request_id");
        } else {
            $db->query("UPDATE approval_requests SET status = 'reverted' WHERE id = $request_id");
        }
    }
}

?>