<?php
date_default_timezone_set('Asia/Calcutta');
$time = mktime(true);
include("settings.php");
include("billit_settings.php");

$q = htmlspecialchars(urldecode(strtoupper($_REQUEST["term"])), ENT_QUOTES);
if (!$q)
	return;

if (isset($_REQUEST['id'])) {
	$id = $_REQUEST['id'];
} else {
	$id = '';
}
$data = array();

if ($id == 'get_ledger') {
	$sno = $_POST['sno'];
	echo get_ledger($sno);
	exit;
}

if ($id == 'villages') {
	$sql = 'select * from location_village where parent=' . $q;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "location_name" => $row['location_name']);
	}
} elseif ($id == 'villages_selected') {
	$sql = 'select location_village.sno as sno, location_village.location_name as location_name from plv_users_villages left join location_village on location_village.sno = village_id where user_id=' . $_GET['plv_id'];
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "location_name" => $row['location_name']);
	}
} elseif ($id == 'dist') {
	$sql = 'SELECT uprnss_district.sno as sno, uprnss_district.district_name_english as district_name FROM `uprnss_project_temp` left join uprnss_district on uprnss_district .sno = district_id where uprnss_project_temp.division_id in (' . implode(",", $_SESSION['divisions']) . ') and department_id="' . $_POST['val'] . '" group by district_id';
	//echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "district_name" => $row['district_name']);
	}
} elseif ($id == 'proj') {
	$sql = 'SELECT uprnss_project_temp.sno as sno, uprnss_project_temp.project_name_hindi as project_name_hindi FROM `uprnss_project_temp` left join uprnss_district on uprnss_district .sno = district_id where (status="0" or status is null) and uprnss_project_temp.division_id in (' . implode(",", $_SESSION['divisions']) . ') and department_id="' . $_POST['dept'] . '" and district_id="' . $_POST['val'] . '"';
	//echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "project_name_hindi" => $row['project_name_hindi']);
	}
} elseif ($id == 'proj_detail') {
	$sql = 'select * from uprnss_project_temp where sno="' . $_POST['val'] . '"';
	$project = mysqli_fetch_assoc(execute_query($sql));
	foreach ($project as $k => $v) {
		$data[$k] = $v;
	}

} elseif ($id == 'sub_dep') {
	$sql = 'SELECT * FROM uprnss_sub_department where department_id="' . $_POST['val'] . '" ';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "sub_department_hindi" => $row['sub_department_hindi']);
	}
} elseif ($id == 'scheme') {
	$sql = 'SELECT * FROM uprnss_project_scheme where department_id="' . $_POST['val'] . '" ';
	if (isset($_POST['subval'])) {
		$sql .= ' and sub_department_id="' . $_POST['subval'] . '"';
	}
	//echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "scheme_name_hindi" => $row['scheme_name_hindi']);
	}
} elseif ($id == 'sub_scheme') {
	$sql = 'SELECT * FROM uprnss_project_sub_scheme where scheme_id="' . $_POST['val'] . '" ';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "sub_scheme_hindi" => $row['sub_scheme_hindi']);
	}
} elseif ($id == 'designation') {
	$sql = 'SELECT * FROM dp_designation where grade_id="' . $_POST['val'] . '" order by abs(sort_no)';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "designation" => $row['designation']);
	}
} elseif ($id == 'employee') {
	$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="' . $_POST['val'] . '" order by full_name ASC';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "full_name" => $row['full_name'], "emp_code" => $row['old_employee_code']);
	}
} elseif ($id == 'payband') {
	//$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="'.$_POST['val'].'" order by full_name ASC';
	$sql = 'select * from master_pay_band where pay_scale ="' . $_POST['val'] . '"';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "band_name" => $row['band_name']);
	}
} elseif ($id == 'paylevel') {
	//$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="'.$_POST['val'].'" order by full_name ASC';
	$sql = 'select * from master_pay_level where pay_band_id ="' . $_POST['val'] . '"';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "level_name" => $row['level_name']);
	}
} elseif ($id == 'basicpay') {
	//$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="'.$_POST['val'].'" order by full_name ASC';
	$sql = 'select * from master_pay_level_grade where level_name ="' . $_POST['val'] . '"';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "basic_pay" => $row['basic_pay']);
	}
} elseif ($id == 'emp_detail') {
	// $sql = 'select * from uprnss_project_temp where sno="'.$_POST['val'].'"';
	$sql = 'SELECT * FROM `employee_appointment` where employee_id="' . $_POST['val'] . '" ORDER BY `joinning_date` DESC LIMIT 1';
	$result = execute_query($sql);
	$numRows = mysqli_num_rows($result);
	if ($numRows != 0) {
		$emp_details = mysqli_fetch_assoc(execute_query($sql));
		foreach ($emp_details as $k => $v) {
			$data[$k] = $v;
		}
	}

} elseif ($id == 'unit_bank') {

	$sql = 'select * from billit_customer where unit_id="' . $_POST['val'] . '" and (parent ="1" or (account_no is not null and account_no != ""))';
	// echo $sql;
	$result = execute_query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = array("id" => $row['sno'], "cus_name" => $row['cus_name']);
	}

} elseif ($id == 'vendor_bank') {

	if ($_POST['val'] == "4") {
		////////////////////////////////// contractor//////////////////
		$sql = 'SELECT 
		t.sno AS tender_id,
		t.tender_title,
		t.tender_no,
		t.project_id,
		t.division_id,
		t.district_id,
		t.tender_status,
		t.publish_date,
		t.end_date,
		t.project_awarded_to,
		v.sno AS vendor_id,
		v.firm_name,
		v.contractor_name
	FROM tender_allotment AS t
	LEFT JOIN vendor AS v 
		ON t.project_awarded_to = v.sno
	 where  project_id="' . $_POST['project_id'] . '"';
		// echo $sql;
		$result = execute_query($sql);
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = array("id" => $row['vendor_id'], "cus_name" => $row['firm_name']);
		}
	} elseif ($_POST['val'] == "5") {
		//////////////////////////////////  Architect//////////////////
		$sql = 'SELECT 
				p.sno AS project_id,
				a.sno AS architect_id,
				a.full_name_english
			FROM uprnss_project_temp AS p
			LEFT JOIN uprnss_architect AS a 
				ON p.architect_id = a.sno
			WHERE p.sno ="' . $_POST['project_id'] . '"';
		// echo $sql;
		$result = execute_query($sql);
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = array("id" => $row['architect_id'], "cus_name" => $row['full_name_english']);

		}
	} elseif ($_POST['val'] == "6") {
		////////////////////////////////// Structural Architect//////////////////
		$sql = 'SELECT 
				p.sno AS project_id,
				a.sno AS architect_id,
				a.full_name_english
			FROM uprnss_project_temp AS p
			LEFT JOIN uprnss_architect AS a 
				ON p.structural_architect_id = a.sno
			WHERE p.sno ="' . $_POST['project_id'] . '"';
		// echo $sql;
		$result = execute_query($sql);
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = array("id" => $row['architect_id'], "cus_name" => $row['full_name_english']);

		}
	}
} elseif ($id == 'proj_div') {
	$sql = 'select * from uprnss_project_temp where sno="' . $_POST['val'] . '"';
	$project = mysqli_fetch_assoc(execute_query($sql));
	foreach ($project as $k => $v) {
		$data[$k] = $v;
	}

} elseif ($id == 'location') {
	if ($_POST['val'] == 'office') {
		$sql = 'select * from uprnss_division where s_no in ("' . implode(",", $_SESSION['divisions']) . '")';
		$result = execute_query($sql);
		$row = mysqli_fetch_assoc($result);
		$txt = '<div class="col-md-2">
				<label>Latitude</label>
				<input type="text" id="lat" disabled="disabled" value="' . $row['latitude'] . '" class="form-control">
				<label>Longitude</label>
				<input type="text" id="long" disabled="disabled" value="' . $row['longitude'] . '" class="form-control">
				<button type="button" class="btn btn-info" onClick="getLocation();">लोकेशन रिफ्रेश करें</button>
			</div>
			<div class="col-md-10" id="map_container">
				<iframe id="googlemap" src="https://maps.google.com/maps?q=' . $row['latitude'] . ',' . $row['longitude'] . '&hl=en&z=13&amp;output=embed" width="100%" height="100%" style="border:1px solid; border-radius:10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
			</div>';
		$data[] = array("latitude" => $row['latitude'], "longitude" => $row['longitude']);
	}
} elseif ($id === 'proj_info') {
	$prj = intval($_POST['val'] ?? 0);
	$exclude = intval($_POST['exclude_invoice'] ?? 0);

	$out = [
		'sanction_cost_lakh' => 0,
		'advance_amount' => 0,
		'received_to_date' => 0,
		'installments_count' => 0
	];

	if ($prj) {
		// Sanction in Lakh
		$q1 = mysqli_query($db, 'SELECT sanction_cost, financial_go_amount FROM uprnss_project_temp WHERE sno=' . $prj . ' LIMIT 1');
		if ($q1 && mysqli_num_rows($q1)) {
			$r1 = mysqli_fetch_assoc($q1);
			$out['sanction_cost_lakh'] = floatval($r1['sanction_cost']);
			$out['advance_amount'] = floatval($r1['financial_go_amount']);
		}

		// Received (₹) + Installments (distinct), excluding current invoice if editing
		$sql2 = 'SELECT COALESCE(SUM(tfr.p_receive_amount),0) AS rcvd,
                            COUNT(DISTINCT ifr.installment) AS inst_cnt
                     FROM transaction_fund_receive tfr
                     JOIN invoice_fund_receive ifr ON ifr.sno = tfr.invoice_id
                     WHERE tfr.project_id=' . $prj;
		if ($exclude > 0) {
			$sql2 .= ' AND ifr.sno<>' . $exclude;
		}
		$q2 = mysqli_query($db, $sql2);
		if ($q2 && mysqli_num_rows($q2)) {
			$r2 = mysqli_fetch_assoc($q2);
			$out['received_to_date'] = floatval($r2['rcvd']);      // Rupees
			$out['installments_count'] = intval($r2['inst_cnt']);
		}
	}
	echo json_encode($out);
	exit;
} elseif ($_POST['id'] === 'proj_balance') {
	$dept = intval($_POST['dept'] ?? 0);
	$proj = intval($_POST['project'] ?? 0);
	$received = 0.0;  // total received for this project
	$transferred = 0.0; // total transferred for this project

	// TODO: replace table/columns with your actual "fund receive" source:
	// Example 1 (if you use invoice_account_fund_receive):
	// $q1 = "SELECT SUM(receive_amount) as s FROM invoice_account_fund_receive WHERE project_name='$proj' AND status!='5'";
	// Example 2 (if receipts are in another table name, adjust here)
	$q1 = "SELECT SUM(p_receive_amount) as s FROM transaction_fund_receive WHERE project_id='$proj' AND status!='5'";
	$r1 = execute_query($q1);
	if ($r1 && mysqli_num_rows($r1)) {
		$received = floatval(mysqli_fetch_assoc($r1)['s']);
	}

	// transferred so far (sum of transafer_amount)
	$q2 = "SELECT SUM(transafer_amount) as s FROM transaction_fund_transfer WHERE project_name='$proj' AND status!='5'";
	$r2 = execute_query($q2);
	if ($r2 && mysqli_num_rows($r2)) {
		$transferred = floatval(mysqli_fetch_assoc($r2)['s']);
	}

	echo json_encode(['received' => $received, 'transferred' => $transferred]);
	exit;
} elseif ($id === 'voucher_info') {
	$voucher = mysqli_real_escape_string($db, $_POST['val'] ?? '');
	$prj = intval($_POST['project_id'] ?? 0);
	$out = ['transafer_date' => '', 'transafer_amount' => 0];

	if ($voucher != '' && $prj > 0) {
		$sql = "SELECT transafer_date, transafer_amount FROM invoice_account_fund_transafer WHERE order_no='$voucher' AND project_name='$prj' LIMIT 1";
		$res = execute_query($sql);
		if ($res && mysqli_num_rows($res)) {
			$row = mysqli_fetch_assoc($res);
			$out['transafer_date'] = $row['transafer_date'];
			$out['transafer_amount'] = floatval($row['transafer_amount']);
		}
	}
	echo json_encode($out);
	exit;
} elseif ($id == 'order_no') {
	$go_no = trim(mysqli_real_escape_string($db, $_POST['val'] ?? ''));
	$data = ['status' => 'error', 'order_date' => '', 'voucher_no' => ''];

	if ($go_no != '') {
		$sql = "SELECT go_date FROM invoice_new_project WHERE go_number = '$go_no' LIMIT 1";
		$res = execute_query($sql);
		if ($res && mysqli_num_rows($res)) {
			$row = mysqli_fetch_assoc($res);
			$data['status'] = 'success';
			$data['order_date'] = $row['go_date'];
			$data['voucher_no'] = $go_no;
		}
	}
} elseif ($id === 'go_info') {
	$go_no = trim(mysqli_real_escape_string($db, $_POST['val'] ?? ''));
	$out = ['go_date' => '', 'projects' => []];

	if ($go_no != '') {
		// Use LIKE to handle minor spacing differences or partial matches if needed
		$sql = "SELECT go_date, sno FROM invoice_new_project WHERE go_number LIKE '%$go_no%' ORDER BY sno DESC LIMIT 1";
		$res = execute_query($sql);
		if ($res && mysqli_num_rows($res)) {
			$row = mysqli_fetch_assoc($res);
			$out['go_date'] = $row['go_date'];
			$inv_id = $row['sno'];

			$sql_p = "SELECT p.*, d.district_name_english, dept.department_name_hindi, sdept.sub_department_hindi 
					  FROM uprnss_project_temp p
					  LEFT JOIN uprnss_district d ON d.sno = p.district_id
					  LEFT JOIN uprnss_department_name dept ON dept.sno = p.department_id
					  LEFT JOIN uprnss_sub_department sdept ON sdept.sno = p.sub_department_id
					  WHERE p.new_project_id='$inv_id' AND (p.status!='5')";
			$res_p = execute_query($sql_p);
			while ($rp = mysqli_fetch_assoc($res_p)) {
				$out['projects'][] = $rp;
			}
		}
	}
	echo json_encode($out);
	exit;
}

// Fetch vendors for billit journal
if ($id == 'get_vendors') {
	header('Content-Type: application/json');

	$sql = "SELECT sno, firm_name 
			FROM vendor
			WHERE firm_name IS NOT NULL
			  AND firm_name != ''
			ORDER BY firm_name ASC";

	$result = mysqli_query($db, $sql);
	$vendors = array();

	if ($result && mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$vendors[] = array(
				'sno' => $row['sno'],
				'firm_name' => htmlspecialchars(trim($row['firm_name']))
			);
		}
	}

	echo json_encode($vendors);
	exit;
}

// Add new vendor functionality
if ($id == 'add_vendor') {
	$firm_name = mysqli_real_escape_string($db, $_POST['firm_name']);
	$contractor_name = mysqli_real_escape_string($db, $_POST['contractor_name']);

	$sql = "INSERT INTO vendor (firm_name, contractor_name, created_by, creation_time) 
			VALUES ('$firm_name', '$contractor_name', '" . $_SESSION['username'] . "', '" . date('Y-m-d H:i:s') . "')";

	if (execute_query($sql)) {
		$vendor_id = mysqli_insert_id($db);
		$data = array(
			'success' => true,
			'vendor_id' => $vendor_id,
			'message' => 'Vendor added successfully'
		);
	} else {
		$data = array(
			'success' => false,
			'message' => 'Error adding vendor: ' . mysqli_error($db)
		);
	}
}

// Fetch voucher amounts from fund receive tables
if ($id == 'fund_receive_voucher_amounts') {
	$department_id = $_POST['department_id'] ?? '';
	$district_id = $_POST['district_id'] ?? '';
	$project_id = $_POST['project_id'] ?? '';

	$data = array(
		'tds_deducted' => 0,
		'gsttds_deducted' => 0,
		'labour_cess' => 0,
		'cgst_amount' => 0,
		'sgst_amount' => 0
	);

	if (!empty($department_id) && !empty($district_id) && !empty($project_id)) {
		// Get the latest voucher amounts for specific department/district/project combination
		$sql = "SELECT 
					tfr.tds_alloc,
					tfr.gsttds_alloc,
					tfr.labour_cess_alloc,
					tfr.cgst_amount,
					tfr.sgst_amount
				FROM transaction_fund_receive tfr
				INNER JOIN invoice_fund_receive ifr ON tfr.invoice_id = ifr.sno
				WHERE tfr.department_id = '" . mysqli_real_escape_string($db, $department_id) . "'
				AND tfr.district_id = '" . mysqli_real_escape_string($db, $district_id) . "'
				AND tfr.project_id = '" . mysqli_real_escape_string($db, $project_id) . "'
				AND ifr.status != '5'
				AND tfr.status != '5'
				ORDER BY ifr.creation_time DESC, tfr.sno DESC
				LIMIT 1";

		// Debug: Log the SQL query
		error_log("Debug SQL: " . $sql);

		$result = execute_query($sql);
		if ($result && $row = mysqli_fetch_assoc($result)) {
			// Debug: Log the raw row data
			error_log("Debug DB Row: " . print_r($row, true));

			$data = array(
				'tds_deducted' => number_format(floatval($row['tds_alloc']), 2, '.', ''),
				'gsttds_deducted' => number_format(floatval($row['gsttds_alloc']), 2, '.', ''),
				'labour_cess' => number_format(floatval($row['labour_cess_alloc']), 2, '.', ''),
				'cgst_amount' => number_format(floatval($row['cgst_amount']), 2, '.', ''),
				'sgst_amount' => number_format(floatval($row['sgst_amount']), 2, '.', '')
			);

			// Debug: Log the final data
			error_log("Debug Final Data: " . print_r($data, true));
		} else {
			error_log("Debug: No data found for dept=$department_id, dist=$district_id, proj=$project_id");
		}
	} else {
		error_log("Debug: Missing parameters - dept=$department_id, dist=$district_id, proj=$project_id");
	}
}

// Fetch account numbers based on Department, District, Project selection
if ($id == 'fund_transfer_accounts') {
	$department_id = $_POST['department_id'] ?? '';
	$district_id = $_POST['district_id'] ?? '';
	$unit_id = $_POST['unit_id'] ?? '';

	$data = array();

	if (!empty($department_id) || !empty($district_id) || !empty($unit_id)) {
		// Use the provided query to fetch account numbers
		$sql = "SELECT bc.account_no, bc.sno, bc.cus_name
				FROM billit_customer bc
				JOIN uprnss_division ud ON bc.unit_id = ud.s_no
				WHERE
					(bc.department_id = '" . mysqli_real_escape_string($db, $department_id) . "' OR '" . mysqli_real_escape_string($db, $department_id) . "' = '')
				AND (ud.district_under_division LIKE CONCAT('%', '" . mysqli_real_escape_string($db, $district_id) . "', '%') OR '" . mysqli_real_escape_string($db, $district_id) . "' = '')
				AND (bc.unit_id = '" . mysqli_real_escape_string($db, $unit_id) . "' OR '" . mysqli_real_escape_string($db, $unit_id) . "' = '')
				AND bc.account_no IS NOT NULL
				ORDER BY bc.account_no";

		$result = execute_query($sql);
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = array(
				"sno" => $row['sno'],
				"account_no" => $row['account_no'],
				"cus_name" => $row['cus_name']
			);
		}
	}
}


// Fetch HO bank used when fund was received for a given project
if ($id == 'ho_bank_for_project') {
	$project_id = intval($_POST['project_id'] ?? 0);
	$result_data = ['sno' => '', 'cus_name' => ''];

	if ($project_id > 0) {
		// Get the bank_name from the most recent fund receive entry for this project
		$sql = "SELECT ifr.bank_name 
				FROM transaction_fund_receive tfr
				INNER JOIN invoice_fund_receive ifr ON ifr.sno = tfr.invoice_id
				WHERE tfr.project_id = '$project_id'
				  AND ifr.status != '5'
				  AND tfr.status != '5'
				  AND ifr.bank_name != ''
				ORDER BY ifr.creation_time DESC, tfr.sno DESC
				LIMIT 1";
		$res = execute_query($sql);
		if ($res && $row = mysqli_fetch_assoc($res)) {
			$bank_name = $row['bank_name'];
			// Now find the matching sno in billit_customer (HO banks: unit_id=53)
			$sql2 = "SELECT sno, cus_name FROM billit_customer 
					 WHERE parent='1' AND unit_id='53' 
					 AND (sno = '" . mysqli_real_escape_string($db, $bank_name) . "' 
					      OR cus_name LIKE '%" . mysqli_real_escape_string($db, $bank_name) . "%')
					 LIMIT 1";
			$res2 = execute_query($sql2);
			if ($res2 && $row2 = mysqli_fetch_assoc($res2)) {
				$result_data = ['sno' => $row2['sno'], 'cus_name' => $row2['cus_name']];
			}
		}
	}
	echo json_encode($result_data);
	exit;
}

if (empty($data) != true) {
	echo json_encode($data);
}
?>