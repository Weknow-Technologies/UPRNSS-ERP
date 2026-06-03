<?php
date_default_timezone_set('Asia/Calcutta');
$time = mktime(true);
include("settings.php");
include("billit_settings.php");

$q = isset($_GET["term"]) ? htmlspecialchars(urldecode(strtoupper($_GET["term"])), ENT_QUOTES) : '';

if (isset($_REQUEST['id'])) {
	$id = $_REQUEST['id'];
} else {
	$id = '';
}

// Handle create_group before $q check (it does not need $q)
if ($id == 'create_group') {
	if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'sadmin') {
		echo "Error: Unauthorized Access";
		exit;
	}
	$group_name = isset($_GET['group_name']) ? trim(mysqli_real_escape_string($db, $_GET['group_name'])) : '';
	$group_parent_id = isset($_GET['group_parent_id']) ? intval($_GET['group_parent_id']) : 0;
	$edit_group_sno = isset($_GET['edit_group_sno']) ? intval($_GET['edit_group_sno']) : 0;

	$unit_id = '';
	if (isset($_SESSION['usertype']) && $_SESSION['usertype'] != 'sadmin' && isset($_SESSION['divisions']) && is_array($_SESSION['divisions']) && count($_SESSION['divisions']) > 0) {
		$unit_id = $_SESSION['divisions'][0];
	}
	$created_by = isset($_SESSION['username']) ? $_SESSION['username'] : '';

	if ($group_name != '' && $group_parent_id > 0) {
		if ($edit_group_sno > 0) {
			$sql_chk = 'SELECT unit_id FROM billit_pl_heads WHERE sno=' . $edit_group_sno;
			$chk_res = execute_query($sql_chk);
			if ($row = mysqli_fetch_assoc($chk_res)) {
				if ($_SESSION['usertype'] != 'sadmin' && ($row['unit_id'] != $unit_id || empty($row['unit_id']))) {
					// unauthorized to edit
				} else {
					$sql_upd = 'UPDATE billit_pl_heads SET description="' . $group_name . '", parent=' . $group_parent_id . ' WHERE sno=' . $edit_group_sno;
					execute_query($sql_upd);
				}
			}
		} else {
			$sql_chk = 'SELECT sno FROM billit_pl_heads WHERE description="' . $group_name . '"';
			$chk_res = execute_query($sql_chk);
			if (mysqli_num_rows($chk_res) == 0) {
				// Use 'parent' column (existing column in billit_pl_heads)
				$visibility = ($unit_id != '' ? 'private' : 'public');
				$sql_ins = 'INSERT INTO billit_pl_heads (description, parent, unit_id, visibility, created_by) VALUES ("' . $group_name . '", ' . $group_parent_id . ', "' . $unit_id . '", "' . $visibility . '", "' . $created_by . '")';
				execute_query($sql_ins);
			}
		}
	}
	// Return updated <option> list for the parent dropdown
	echo get_group_hierarchy_options('', '', true);
	exit;
}

if ($id == 'get_group_details') {
	$sno = isset($_GET['sno']) ? intval($_GET['sno']) : 0;
	$sql = 'SELECT * FROM billit_pl_heads WHERE sno=' . $sno;
	$res = execute_query($sql);
	if ($row = mysqli_fetch_assoc($res)) {
		$unit_id = '';
		if (isset($_SESSION['usertype']) && $_SESSION['usertype'] != 'sadmin' && isset($_SESSION['divisions']) && is_array($_SESSION['divisions']) && count($_SESSION['divisions']) > 0) {
			$unit_id = $_SESSION['divisions'][0];
		}
		if ($_SESSION['usertype'] != 'sadmin') {
			if ($row['unit_id'] != $unit_id || empty($row['unit_id'])) {
				echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this group. It can only be edited by the division that created it.']);
				exit;
			}
		}
		echo json_encode(['success' => true, 'data' => $row]);
	} else {
		echo json_encode(['success' => false, 'message' => 'Group not found']);
	}
	exit;
}

if ($id == 'delete_group') {
	if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'sadmin') {
		echo json_encode(['success' => false, 'message' => 'Error: Unauthorized Access']);
		exit;
	}
	$sno = isset($_GET['sno']) ? intval($_GET['sno']) : 0;
	$sql = 'SELECT * FROM billit_pl_heads WHERE sno=' . $sno;
	$res = execute_query($sql);
	if ($row = mysqli_fetch_assoc($res)) {
		$unit_id = '';
		if (isset($_SESSION['usertype']) && $_SESSION['usertype'] != 'sadmin' && isset($_SESSION['divisions']) && is_array($_SESSION['divisions']) && count($_SESSION['divisions']) > 0) {
			$unit_id = $_SESSION['divisions'][0];
		}
		if ($_SESSION['usertype'] != 'sadmin') {
			if ($row['unit_id'] != $unit_id || empty($row['unit_id'])) {
				echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this group.']);
				exit;
			}
		}
		execute_query('DELETE FROM billit_pl_heads WHERE sno=' . $sno);
		echo json_encode(['success' => true]);
	} else {
		echo json_encode(['success' => false, 'message' => 'Group not found']);
	}
	exit;
}

if (!$q && ($id == 'cust_name' || $id == ''))
	return;

$result = array();

if ($id == 'cust_name') {
	if (!$q) {
		echo json_encode([]);
		exit;
	} // term required for autocomplete
	$sql = 'select * from billit_customer where (cus_name like "%' . $q . '%" or sno like "' . $q . '%" or mobile like "%' . $q . '%" or tin like "%' . $q . '%" or account_no like "%' . $q . '%")';
	if (isset($_GET['cust']) && $_GET['cust'] == '1') {
		$sql .= ' and category="ledger"';
	}
	if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'sadmin') {
		// Sadmin can see all units
	} else {
		// Non-sadmin: restrict to their division(s)
		if (isset($_GET['division_id']) && $_GET['division_id'] != '') {
			$div_esc = mysqli_real_escape_string($db, $_GET['division_id']);
			$sql .= ' and (unit_id="' . $div_esc . '" or visibility="public")';
		} elseif (isset($_SESSION['divisions']) && is_array($_SESSION['divisions']) && count($_SESSION['divisions']) > 0) {
			$divs = implode('","', $_SESSION['divisions']);
			$sql .= ' and (unit_id IN ("' . $divs . '") or visibility="public")';
		}
	}
	$sql .= ' limit 20';
	//echo $sql;
	$res = execute_query($sql);
	while ($row = mysqli_fetch_array($res)) {
		$row['balance'] = get_cust_balace('1970-01-01', date("Y-m-d"), $row['sno']);
		$sql = 'select * from billit_invoice_sale where supplier_id="' . $row['sno'] . '" order by sno desc limit 1';
		$last_invoice = execute_query($sql);
		if (mysqli_num_rows($last_invoice) != 0) {
			$last_invoice = mysqli_fetch_assoc($last_invoice);
			$last_invoice_sno = $last_invoice['sno'];
			$last_invoice = 'Rs.' . $last_invoice['total_amount1'] . '. Dt: ' . date("d-m-Y", strtotime($last_invoice['dateofdispatch'])) . ' Inv#: ' . $last_invoice['invoice_no'];
		} else {
			$last_invoice = '';
			$last_invoice_sno = '';
		}
		$parent_name = $row['parent_ledger'] ? get_ledger($row['parent_ledger']) : get_parent($row['parent']);
		array_push($result, array("id" => $row['sno'], "label" => $row['cus_name'] . ' [' . $parent_name . ']', "cust_name" => $row['cus_name'], "address" => $row['address'], "address2" => $row['add_2'], "city" => $row['city'], "mobile" => $row['mobile'], "tin" => $row['tin'], "aadhar" => $row['adhar_no'], "state" => $row['state'], "state_name" => get_state($row['state']), "opening" => $row['opening_balance'], "category" => $row['parent'], "balance" => $row['balance'], "zipcode" => $row['zipcode'], "last_invoice" => $last_invoice, "last_invoice_sno" => $last_invoice_sno));
	}
} elseif ($id == 'create_type') {
	if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'sadmin') {
		echo "Error: Unauthorized Access";
		return 0;
	}
	if ($_GET['type_name'] != '') {
		$sql = 'select * from billit_cust_type where type="' . $_GET['type_name'] . '"';
		$result = execute_query($sql);
		if (mysqli_num_rows($result) == 0) {
			if ($_GET['edit_sno_type'] != '') {
				$sql = 'update billit_cust_type set type="' . $_GET['type_name'] . '", edited_by="' . $_SESSION['username'] . '", edition_time="' . date("Y-m-d H:i:s") . '" where sno=' . $_GET['edit_sno_type'];
			} else {
				$sql = 'insert into billit_cust_type(type, created_by, creation_time) values("' . $_GET['type_name'] . '" , "' . $_SESSION['username'] . '","' . date("Y-m-d H:i:s") . '")';
			}
			execute_query($sql);
		}
	}
	$sql = 'select * from billit_cust_type';
	$row_type = execute_query($sql);
	echo '<option value=""></option>';
	while ($type_details = mysqli_fetch_array($row_type)) {
		echo '<option value="' . $type_details['sno'] . '">' . $type_details['type'] . '</option>';
	}
	return 0;
} elseif ($id == 'create_ledger') {
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	$cus_name = isset($_GET['cus_name']) ? trim(htmlspecialchars(urldecode($_GET['cus_name']))) : '';
	$parent = isset($_GET['parent']) ? intval($_GET['parent']) : 0;
	$state = isset($_GET['state']) ? trim($_GET['state']) : '';
	$mobile = isset($_GET['mobile']) ? trim($_GET['mobile']) : '';
	$tin = isset($_GET['tin']) ? trim($_GET['tin']) : '';
	$address = isset($_GET['address']) ? trim($_GET['address']) : '';
	$address2 = isset($_GET['add_2']) ? trim($_GET['add_2']) : '';

	if ($cus_name == '') {
		echo json_encode(['success' => false, 'message' => 'Ledger name is required.']);
		exit;
	}

	$unit_id = isset($_GET['unit_id']) ? $_GET['unit_id'] : '';
	if ($unit_id == '' && isset($_SESSION['divisions']) && is_array($_SESSION['divisions'])) {
		$unit_id = $_SESSION['divisions'][0];
	}

	// add_customer() from billit_settings.php — returns sno if already exists or new insert id
	$sno = add_customer([
		'cus_name' => strtoupper($cus_name),
		'address' => $address,
		'add_2' => $address2,
		'state' => $state,
		'mobile' => $mobile,
		'tin' => $tin,
		'parent' => $parent,
		'visibility' => ($unit_id != '' ? 'private' : 'public'),
		'unit_id' => $unit_id
	]);

	if ($sno) {
		echo json_encode([
			'success' => true,
			'id' => $sno,
			'name' => strtoupper($cus_name),
			'message' => 'Ledger created successfully.'
		]);
	} else {
		echo json_encode(['success' => false, 'message' => 'Failed to create ledger. Please try again.']);
	}
	exit;
} elseif ($id == 'delete_type') {
	if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'sadmin') {
		echo "Error: Unauthorized Access";
		return 0;
	}
	if ($_GET['type'] != '') {
		$sql = 'delete from billit_cust_type where sno="' . $_GET['type'] . '"';
		$result = execute_query($sql);
	}
	$sql = 'select * from billit_cust_type';
	$row_type = execute_query($sql);
	echo '<option value=""></option>';
	while ($type_details = mysqli_fetch_array($row_type)) {
		echo '<option value="' . $type_details['sno'] . '">' . $type_details['type'] . '</option>';
	}
	return 0;
} elseif ($id == 'create_category') {
	if ($_GET['category_name'] != '') {
		$sql = 'select * from billit_cust_category where category="' . $_GET['category_name'] . '"';
		$result = execute_query($sql);
		if (mysqli_num_rows($result) == 0) {
			if ($_GET['edit_sno_category'] != '') {
				$sql = 'update billit_cust_category set category="' . $_GET['category_name'] . '", edited_by="' . $_SESSION['username'] . '", edition_time="' . date("Y-m-d H:i:s") . '" where sno=' . $_GET['edit_sno_category'];
			} else {
				$sql = 'insert into billit_cust_category(category, created_by, creation_time) values("' . $_GET['category_name'] . '" , "' . $_SESSION['username'] . '","' . date("Y-m-d H:i:s") . '")';
			}
			execute_query($sql);
		}
	}
	$sql = 'select * from billit_cust_category';
	$row_category = execute_query($sql);
	echo '<option value=""></option>';
	while ($category_details = mysqli_fetch_array($row_category)) {
		echo '<option value="' . $category_details['sno'] . '">' . $category_details['category'] . '</option>';
	}
	return 0;
} elseif ($id == 'delete_category') {
	if ($_GET['category'] != '') {
		$sql = 'delete from billit_cust_category where sno="' . $_GET['category'] . '"';
		$result = execute_query($sql);
	}
	$sql = 'select * from billit_cust_category';
	$row_category = execute_query($sql);
	echo '<option value=""></option>';
	while ($category_details = mysqli_fetch_array($row_category)) {
		echo '<option value="' . $category_details['sno'] . '">' . $category_details['category'] . '</option>';
	}
	return 0;
} elseif ($id == 'create_parent') {
	if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'sadmin') {
		echo "Error: Unauthorized Access";
		return 0;
	}
	if ($_GET['parent_name'] != '') {
		$fund_type = isset($_GET['fund_type']) ? $_GET['fund_type'] : '';
		$sql = 'select * from billit_pl_heads where description="' . $_GET['parent_name'] . '"';
		$result = execute_query($sql);
		if (mysqli_num_rows($result) == 0) {
			if ($_GET['edit_sno_parent'] != '') {
				$sql = 'update billit_pl_heads set description="' . $_GET['parent_name'] . '", fund_type="' . $fund_type . '", edited_by="' . $_SESSION['username'] . '", edition_time="' . date("Y-m-d H:i:s") . '" where sno=' . $_GET['edit_sno_parent'];
			} else {
				$sql = 'insert into billit_pl_heads(description, fund_type, created_by, creation_time) values("' . $_GET['parent_name'] . '", "' . $fund_type . '", "' . $_SESSION['username'] . '","' . date("Y-m-d H:i:s") . '")';
			}
			execute_query($sql);
		}
	}
	$sql = 'select * from billit_pl_heads';
	$row_parent = execute_query($sql);
	echo '<option value=""></option>';
	while ($parent_details = mysqli_fetch_array($row_parent)) {
		echo '<option value="' . $parent_details['sno'] . '">' . $parent_details['description'] . '</option>';
	}
	return 0;
} elseif ($id == 'delete_parent') {
	if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'sadmin') {
		echo "Error: Unauthorized Access";
		return 0;
	}
	if ($_GET['parent'] != '') {
		$sql = 'delete from billit_pl_heads where sno="' . $_GET['parent'] . '"';
		$result = execute_query($sql);
	}
	$sql = 'select * from billit_pl_heads';
	$row_parent = execute_query($sql);
	echo '<option value=""></option>';
	while ($parent_details = mysqli_fetch_array($row_parent)) {
		echo '<option value="' . $parent_details['sno'] . '">' . $parent_details['description'] . '</option>';
	}
	return 0;
} elseif ($id == 'get_groups_by_super_parent') {
	$super_parent_id = isset($_GET['super_parent_id']) ? intval($_GET['super_parent_id']) : 0;
	$selected_unit = isset($_GET['unit_id']) ? trim($_GET['unit_id']) : '';
	$is_unit = (isset($_SESSION['usertype']) && $_SESSION['usertype'] !== 'sadmin')
		|| ($selected_unit !== '' && $selected_unit !== 'all' && $selected_unit !== '53');

	if ($is_unit) {
		$head_vis_cond = "(visibility IS NULL OR visibility = '' OR visibility = 'public')";
	} else {
		$head_vis_cond = "1=1";
	}

	$sql = "SELECT sno, description FROM billit_pl_heads WHERE parent = $super_parent_id AND $head_vis_cond ORDER BY sort_no+0, sno ASC";
	$res = execute_query($sql);
	$options = '<option value="">-- Select Group --</option>';
	
	// Add the Super Parent itself so ledgers can be placed directly under it
	$sp_sql = "SELECT sno, description FROM billit_pl_heads WHERE sno = $super_parent_id AND $head_vis_cond";
	$sp_res = execute_query($sp_sql);
	if ($sp_row = mysqli_fetch_assoc($sp_res)) {
		$options .= '<option value="' . $sp_row['sno'] . '">' . htmlspecialchars($sp_row['description']) . ' (Direct)</option>';
	}

	while ($row = mysqli_fetch_assoc($res)) {
		$options .= '<option value="' . $row['sno'] . '">' . htmlspecialchars($row['description']) . '</option>';
	}
	echo $options;
	exit;
} elseif ($id == 'get_super_parent_of_group') {
	$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
	$sql = "SELECT parent FROM billit_pl_heads WHERE sno = $group_id";
	$res = execute_query($sql);
	if ($row = mysqli_fetch_assoc($res)) {
		echo $row['parent'];
	} else {
		echo 0;
	}
	exit;
}

// Handle POST request for adding parent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_parent') {
	$name = mysqli_real_escape_string($db, $_POST['name']);
	$mobile = mysqli_real_escape_string($db, $_POST['mobile']);
	$address = mysqli_real_escape_string($db, $_POST['address']);
	$table_type = isset($_POST['table_type']) ? $_POST['table_type'] : 'billit_customer';

	if ($table_type == 'billit_pl_heads') {
		// Add to billit_pl_heads table
		$sql = "INSERT INTO billit_pl_heads (description, created_by, creation_time) VALUES ('$name', '" . $_SESSION['username'] . "', '" . date('Y-m-d H:i:s') . "')";
		execute_query($sql);
		$insert_id = mysqli_insert_id($db);

		if ($insert_id) {
			echo json_encode(['success' => true, 'id' => $insert_id, 'name' => $name]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Failed to add parent head']);
		}
	} else {
		// Add to billit_customer table (original logic)
		$sql = "SELECT sno FROM billit_customer WHERE cus_name = '$name'";
		$result = execute_query($sql);

		if (mysqli_num_rows($result) > 0) {
			echo json_encode(['success' => false, 'message' => 'Parent with this name already exists']);
			exit;
		}

		$sql = "INSERT INTO billit_customer (cus_name, mobile, address, created_by, creation_time) VALUES ('$name', '$mobile', '$address', '" . $_SESSION['username'] . "', '" . date('Y-m-d H:i:s') . "')";
		execute_query($sql);

		$insert_id = mysqli_insert_id($db);

		if ($insert_id) {
			echo json_encode(['success' => true, 'id' => $insert_id, 'name' => $name]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Failed to add parent']);
		}
	}
	exit;
}


if (empty($result) != true) {
	echo json_encode($result);
}
?>