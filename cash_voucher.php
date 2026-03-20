<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");
$msg = '';
$response = 0;
$finalmsg = '';
$tab = 1;
date_default_timezone_set('Asia/Calcutta');
//print_r($_POST);
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if (!isset($_SESSION['username'])) {
	$_SESSION['username'] = 'system'; // or redirect to login page
}


if (isset($_POST['sale_date']) && !$_POST['edit_sno']) {
	foreach ($_POST as $k => $v) {
		$_POST[$k] = strtoupper($v);
	}
	$time = $_POST['sale_date'];
	if ($msg == '') {
		if (isset($_POST['treat_as_final'])) {
			$status = 1;
		} else {
			$status = 0;
		}
		$first_by = '';
		$first_to = '';
		$count = 0;


		for ($i = 1; $i <= $_POST['id']; $i++) {
			if ($_POST['account_' . $i . '_sno'] != '') {
				$count++;
			}
			//$msg .= '<h1>'.$_POST['voucher_type_'.$i].'</h1>';
			if ($_POST['voucher_type_' . $i] == 'BY' && $first_by == '') {
				$first_by = $_POST['account_' . $i . '_sno'];
			}

			if ($_POST['voucher_type_' . $i] == 'TO' && $first_to == '') {
				$first_to = $_POST['account_' . $i . '_sno'];
			}
		}
	}
	$sql = 'INSERT INTO billit_invoice_cash_voucher 
        (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, unit_id, created_by, creation_time)
        VALUES (
            "' . $_POST['sale_date'] . '",
            "' . $first_by . '",
            "' . $first_to . '",
            "' . $_POST['total_damt_hidden'] . '",
            "' . $_POST['total_camt_hidden'] . '",
            "' . $count . '",
            "' . $_POST['challan_no'] . '",
            "' . $_POST['unit_id'] . '",
            "' . $_SESSION['username'] . '",
            "' . date("Y-m-d H:i:s") . '"
        )';

	execute_query($sql);
	$id = mysqli_insert_id($db);

	// Get vendor information
	$vendor_name = '';
	if (isset($_POST['vendor_1']) && $_POST['vendor_1'] != '') {
		$vendor_sql = "SELECT firm_name FROM vendor WHERE sno = '" . $_POST['vendor_1'] . "'";
		$vendor_result = mysqli_query($db, $vendor_sql);
		if ($vendor_row = mysqli_fetch_assoc($vendor_result)) {
			$vendor_name = htmlspecialchars($vendor_row['firm_name']);
		}
	}

	if (mysqli_error($db)) {
		$msg .= 'Error # 1.369 >> ' . $sql;
	} else {
		for ($i = 1; $i <= $_POST['id']; $i++) {

			if (isset($_POST['account_' . $i . '_sno']) && $_POST['account_' . $i . '_sno'] != '') {

				$description = isset($_POST['description_' . $i]) ? $_POST['description_' . $i] : '';
				$remark = isset($_POST['remark_1']) ? $_POST['remark_1'] : '';

				if (isset($_POST['voucher_type_' . $i]) && $_POST['voucher_type_' . $i] == 'BY') {
					$sql = 'INSERT INTO billit_cash_voucher_journal 
                (`journal_id`, `by`, `to`, `amount`, `timestamp`, `remarks`, `vendor`, `challan_no`, `unit_id`, `status`) 
                VALUES (
                    "' . $id . '", 
                    "' . $_POST['account_' . $i . '_sno'] . '", 
                    "", 
                    "' . (isset($_POST['debit_' . $i]) ? $_POST['debit_' . $i] : 0) . '", 
                    "' . $_POST['sale_date'] . '", 
                    "' . $description . '", 
                    "' . $vendor_name . '", 
                    "' . $_POST['challan_no'] . '", 
                    "' . $_POST['unit_id'] . '", 
                    "' . $status . '"
                )';
				} else {
					$sql = 'INSERT INTO billit_cash_voucher_journal 
                (`journal_id`, `by`, `to`, `amount`, `timestamp`, `remarks`, `vendor`, `challan_no`, `unit_id`, `status`) 
                VALUES (
                    "' . $id . '", 
                    "", 
                    "' . $_POST['account_' . $i . '_sno'] . '", 
                    "' . (isset($_POST['credit_' . $i]) ? $_POST['credit_' . $i] : 0) . '", 
                    "' . $_POST['sale_date'] . '", 
                    "' . $description . '", 
                    "' . $vendor_name . '", 
                    "' . $_POST['challan_no'] . '", 
                    "' . $_POST['unit_id'] . '", 
                    "' . $status . '"
                )';
				}

				// Execute query
				execute_query($sql);

				// Check for MySQL errors
				if (mysqli_error($db)) {
					$msg .= 'Error # 1.025 at Line : ' . $i . ' >> ' . mysqli_error($db) . ' >> ' . $sql;
				}
			}
		}

		if ($msg == '') {
			$msg .= 'Cash Voucher Data Saved.';
			// Redirect to same page to show success message
			echo "<script>window.location.href='cash_voucher.php?success=1';</script>";
			exit();
		}
	}

	$response = 1;
} else {
	$sql = 'select * from billit_invoice_cash_voucher order by sno desc limit 1';
	$date = execute_query($sql);
	if (mysqli_num_rows($date) == 0) {
		$_POST['sale_date'] = date("Y-m-d");
	} else {
		$date = mysqli_fetch_array($date);
		$date = $date['timestamp'];
		$_POST['sale_date'] = $date;
	}
	$response = 1;
}

if (isset($_GET['id'])) {
	$sql = 'SELECT * FROM `billit_invoice_cash_voucher` AS `bicv` LEFT JOIN `billit_cash_voucher_journal` AS `bcvj` ON `bicv`.`sno` = `bcvj`.`journal_id` WHERE `bicv`.`sno` ="' . $_GET['id'] . '"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));
	$sqlJournal = 'select * from billit_cash_voucher_journal where journal_id="' . $_GET['id'] . '"';
	$result = execute_query($sqlJournal);
	$old_Journal = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$old_Journal[] = $row;
	}
}

if (isset($_POST['sale_date']) && isset($_POST['edit_sno']) && $_POST['edit_sno'] != '') {
	$id = $_POST['edit_sno'];
	if (isset($_POST['treat_as_final'])) {
		$status = 1;
	} else {
		$status = 0;
	}
	$first_by = '';
	$first_to = '';
	$count = 0;
	for ($i = 1; $i <= $_POST['id']; $i++) {
		$type = strtolower($_POST['voucher_type_' . $i] ?? '');
		if (isset($_POST['account_' . $i . '_sno']) && $_POST['account_' . $i . '_sno'] != '') {
			$count++;
		}
		if ($type == 'by' && $first_by == '') {
			$first_by = $_POST['account_' . $i . '_sno'];
		}
		if ($type == 'to' && $first_to == '') {
			$first_to = $_POST['account_' . $i . '_sno'];
		}
	}

	$sql = 'UPDATE billit_invoice_cash_voucher SET
		timestamp = "' . $_POST['sale_date'] . '",
		first_by = "' . $first_by . '",
		first_to = "' . $first_to . '",
		tot_debit = "' . $_POST['total_damt_hidden'] . '",
		tot_credit = "' . $_POST['total_camt_hidden'] . '",
		row_count = "' . $count . '",
		voucher_no = "' . $_POST['challan_no'] . '",
		unit_id = "' . $_POST['unit_id'] . '"
		WHERE sno="' . $id . '"';
	execute_query($sql);
	$del = "DELETE FROM billit_cash_voucher_journal WHERE journal_id='" . $id . "'";
	execute_query($del);
	$vendor_name = '';
	if (isset($_POST['vendor_1']) && $_POST['vendor_1'] != '') {
		$vendor_sql = "SELECT firm_name FROM vendor WHERE sno='" . $_POST['vendor_1'] . "'";
		$vendor_result = mysqli_query($db, $vendor_sql);
		if ($vendor_row = mysqli_fetch_assoc($vendor_result)) {
			$vendor_name = htmlspecialchars($vendor_row['firm_name']);
		}
	}
	for ($i = 1; $i <= $_POST['id']; $i++) {

		if (isset($_POST['account_' . $i . '_sno']) && $_POST['account_' . $i . '_sno'] != '') {

			$description = $_POST['description_' . $i] ?? '';

			$debit = $_POST['debit_' . $i] ?? 0;
			$credit = $_POST['credit_' . $i] ?? 0;

			if ($_POST['voucher_type_' . $i] == 'by') {

				$amount = $debit;

				$sql = 'INSERT INTO billit_cash_voucher_journal
            (journal_id, `by`, `to`, amount, timestamp, remarks, vendor, challan_no, unit_id, status)
            VALUES(
            "' . $id . '",
            "' . $_POST['account_' . $i . '_sno'] . '",
            "",
            "' . $amount . '",
            "' . $_POST['sale_date'] . '",
            "' . $description . '",
            "' . $vendor_name . '",
            "' . $_POST['challan_no'] . '",
            "' . $_POST['unit_id'] . '",
            "' . $status . '"
            )';
			} else {
				$amount = $credit;
				$sql = 'INSERT INTO billit_cash_voucher_journal
            (journal_id, `by`, `to`, amount, timestamp, remarks, vendor, challan_no, unit_id, status)
            VALUES(
            "' . $id . '",
            "",
            "' . $_POST['account_' . $i . '_sno'] . '",
            "' . $amount . '",
            "' . $_POST['sale_date'] . '",
            "' . $description . '",
            "' . $vendor_name . '",
            "' . $_POST['challan_no'] . '",
            "' . $_POST['unit_id'] . '",
            "' . $status . '"
            )';
			}
			execute_query($sql);
		}
	}
	if ($msg == '') {
		$msg = 'Cash Voucher Updated Successfully';
	}
}

if (isset($_GET['del'])) {
	$id = (int) $_GET['del'];
	execute_query("DELETE FROM billit_cash_voucher_journal WHERE journal_id = '$id'");
	execute_query("DELETE FROM billit_invoice_cash_voucher WHERE sno = '$id'");
	$msg .= 'Deleted';
}
if (isset($_GET['view'])) {
	$response = 2;
}
if (isset($_GET['success']) && $_GET['success'] == 1) {
	$msg = 'Cash Voucher Data Saved Successfully!';
}

page_header_start();

?>
<script type="text/javascript" language="javascript">
	function trim(stringToTrim) {
		return stringToTrim.replace(/^\s+|\s+$/g, "");
	}

	function getCurrent(id) {
		document.getElementById('current').value = id;
	}
	function printinvoice() {
		window.open("printing.php");
	}
</script>


<?php
page_header_end();
page_sidebar();
if ($msg != '') {
	echo '<h5>' . alert($msg) . '</h5>';
}
?>
<form id="purchase_form" name="purchase_form" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>"
	onSubmit="return check_prev_date($('[name=&#34;sale_date&#34;]').val());">
	<input type="hidden" name="parent_id" id="parent_hidden" value="6">

	<div class="row">
		<div class="col-md-12">

			<?php
			switch ($response) {
				case 1: {
					?>
						<div class="row">
							<div class="col-12">

								<table class=" table table-striped table-hover table-bordered">
									<tr>
										<td colspan="4">
											<ul>

											</ul>
										</td>
									</tr>
									<tr>
										<td>Unit Name : </td>
										<td>
											<?php
											$options = '';
											if (is_array($_SESSION['divisions'])) {
												foreach ($_SESSION['divisions'] as $k => $v) {
													$selected = '';
													if (!empty($old_data['unit_id']) && $old_data['unit_id'] == $v) {
														$selected = 'selected';
													}
													$options .= '<option value="' . $v . '" ' . $selected . '>' . get_division($v) . '</option>';
												}
											}
											?>
											<select name="unit_id" id="unit_id" class="form-control">
											<?php echo $options; ?>

											</select>

										</td>

										<td>Treat as Final : <input type="checkbox" name="treat_as_final" <?php if (isset($_GET['id'])) {
											if (isset($old_data['status']) && $old_data['status'] == 1) {
												echo "checked='checked'";
											}
										} else {
											echo 'checked="checked"';
										} ?>></td>
										<td class="text-right">
											<a href="cash_voucher.php?view=view"><button type="button" class="btn btn-warning">View
													Cash Vouchers</button></a>
										</td>
									</tr>
									<tr>
										<td>Date</td>
										<td>
											<script type="text/javascript" language="javascript">
												document.writeln(DateInput('sale_date', 'purchase_form', false, 'YYYY-MM-DD', '<?php if (isset($_GET['id']) && isset($old_data['timestamp'])) {
													echo $old_data['timestamp'];
												} else {
													echo $_POST['sale_date'];
												} ?>', <?php echo $tab++;
												 $tab += 3; ?>));
											</script>
										</td>
										<td>Voucher No.</td>
										<td><input id="challan_no" name="challan_no" class="field text" size="12" maxlength="18"
												tabindex="<?php echo $tab++; ?>" type="text" value="<?php if (isset($_GET['id']) && isset($old_data['voucher_no'])) {
													   echo $old_data['voucher_no'];
												   } ?>"></td>
									</tr>
								</table>
							</div>
						</div>
						<div class="card pb-5 px-2">
							<div class="row">
								<div class="col-12">
									<div class="alert alert-primary">Cash Voucher Particulars</div>
								</div>
							</div>



							<div class="legend" id="legend_container">
								<div class="row" id="row_1">
									<!-- Vendor Dropdown Commented Out
									<div class="col-md-3 mb-2" style="display:none;">
										<?#php
										// Fetch all vendors for datalist
										$vendor_list_html = '';
										$vendor_map = [];
										$vendor_sql = "SELECT sno, firm_name FROM vendor WHERE firm_name IS NOT NULL AND firm_name != '' ORDER BY firm_name ASC";
										$vendor_result = mysqli_query($db, $vendor_sql);
										while ($v_row = mysqli_fetch_assoc($vendor_result)) {
											$name = htmlspecialchars(trim($v_row['firm_name']));
											$id = $v_row['sno'];
											$vendor_list_html .= '<option value="' . $name . '">';
											$vendor_map[$name] = $id;
										}
										#?>
										<datalist id="vendor_list">
										<?#php echo $vendor_list_html; #?>
										</datalist>
										<script>
											var vendorMap = <?#php echo json_encode($vendor_map); #?>;
											function updateVendorId(id) {
												var name = $('#vendor_name_' + id).val();
												if (vendorMap[name]) {
													$('#vendor_' + id).val(vendorMap[name]);
												} else {
													$('#vendor_' + id).val('');
												}
											}
										</script>
										<label for="vendor_name_1" class="form-label">Vendor</label>
										<input type="text" name="vendor_name_1" id="vendor_name_1" list="vendor_list"
											class="form-control" onFocus="set_current(1)" onInput="updateVendorId(1)"
											placeholder="Select or Search Vendor..." value="<?#php if (isset($_GET['id']) && isset($old_data['vendor'])) {
												echo $old_data['vendor'];
											} #?>">
										<input type="hidden" name="vendor_1" id="vendor_1" value="<?#php if (isset($_GET['id']) && isset($old_data['vendor_1'])) {
											echo $old_data['vendor_1'];
										} #?>">
									</div>
									-->

									<!-- Remark -->
									<!-- <div class="col-md-3 mb-2">
								<label for="remark_1" class="form-label">Remark</label>
								<input type="text" name="remark_1" id="remark_1" class="form-control"
									placeholder="Enter Remark" onFocus="set_current(1)">
							</div> -->

									<!-- On Account Of -->
									<div class="col-md-3 mb-2">
										<label for="header_description" class="form-label">On account of</label>
										<input type="text" name="description" id="header_description" class="form-control"
											placeholder="Enter Description" onBlur="insert_row(1);" onFocus="set_current(1)" value="<?php if (isset($_GET['id']) && isset($old_data['remarks'])) {
												echo $old_data['remarks'];
											} ?>">
									</div>
									<div class="col-md-3 mb-2">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-danger btn-sm" onclick="remove_row(1)"
											id="remove_btn_1" style="display:none;">Remove</button>
									</div>
								</div>
								<div class="row" id="row_ledger_1">
									<div class="col-1">
										<label for="">By</label>
										<select name="voucher_type_1" id="voucher_type_1" class="form-control"
											onFocus="set_current(1)" onChange="update_voucher(1);">
											<option value="by">By</option>
											<option value="to">To</option>
										</select>
									</div>
									<div class="col-4">
										<label for="">Ledger</label>
										<input type="text" name="account_1" id="account_1" class="form-control"
											onFocus="set_current(1)">
										<span id="balance_1" class="text-primary font-weight-bold" style="font-size: 12px; display: block; margin-top: 2px;"></span>
										<input type="hidden" name="account_1_sno" id="account_1_sno" class="form-control">
										<input type="hidden" name="ledger_type_1" id="ledger_type_1" class="form-control">
									</div>
									<div class="col-2">
										<label for="">Debit Amount</label>
										<input type="text" name="debit_1" id="debit_1" class="form-control" placeholder="Amount"
											onFocus="set_current(1)">
									</div>
									<div class="col-2">
										<label for="">Credit Amount</label>
										<input type="text" name="credit_1" id="credit_1" class="form-control" placeholder="Amount"
											disabled onFocus="set_current(1)">
									</div>
									<div class="col-3">
										<label for="">Description</label>
										<input type="text" name="description_1" id="description_1" class="form-control"
											placeholder="Description" onFocus="set_current(1)">
									</div>
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-12">
									<button type="button" class="btn btn-success" onclick="add_new_row()">Add Row</button>
									<button type="button" class="btn btn-info" data-toggle="modal" data-target="#createModal">New
										Ledger <i class="far fa-plus-square"></i></button>
									<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal"
										onclick="edit_client();">Edit Ledger <i class="far fa-edit"></i></button>
								</div>
							</div>
							<div class="row">
								<div class="col-12 ">
									<div class="row justify-content-end no-gutters my-3">
										<div class="col-1 px-0">
											<div class="alert alert-primary text-right mb-0">Total : </div>
										</div>
										<div class="col-2 px-0">
											<div class="alert alert-warning text-dark mb-0">&nbsp;<span id="total_damt"></span>
											</div>
										</div>
										<div class="col-2 px-0">
											<div class="alert alert-success mb-0"
												style="margin-left: -15px; margin-right: 10px; padding-left: 10px; padding-right: 10px;">
												&nbsp;<span id="total_camt"></span></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 text-left">
									<input id="saveForm" name="saveForm" class="btn btn-primary" type="submit" value="Submit"
										tabindex="<?php echo $tab; ?>">
								</div>
							</div>
							<input type="hidden" name="edit_sno" value="<?php if (isset($_GET['id'])) {
								echo $_GET['id'];
							} ?>" />
							<input type="hidden" name="id" id="id" value="1">
							<input type="hidden" name="current" id="current" value="1">
							<input type="hidden" name="total_camt_hidden" id="total_camt_hidden" value="">
							<input type="hidden" name="total_damt_hidden" id="total_damt_hidden" value="">
						</div>

						</table>
			</form>

			<?php
			break;
				}
				case 2: {
					?>
			<div class="card">
				<div class="row p-2">
					<div class="col-12 text-right">
						<a href="cash_voucher.php"><button type="button" class="btn btn-warning">Create Cash Voucher</button></a>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="row">
					<div class="col-12">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th>S.No.</th>
									<th>Date</th>
									<th>Unit Name</th>
									<th>Parent</th>
									<th>Voucher No.</th>
									<th>Particulars</th>
									<th>Amount</th>
									<th class="no-print">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ($_SESSION['usertype'] != 'sadmin') {
									$sql = 'SELECT * FROM billit_invoice_cash_voucher WHERE created_by="' . $_SESSION['username'] . '"';
								} else {
									$sql = 'SELECT * FROM billit_invoice_cash_voucher';
								}

								$result = execute_query($sql);
								$i = 1;
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<tr>
                            <td>' . $i++ . '</td>
                            <td>' . $row['timestamp'] . '</td>
                            <td>' . get_division($row['unit_id']) . '</td>
                            <td>' . get_ledger($row['first_by']) . '</td>
                            <td>' . $row['voucher_no'] . '</td>
                            <td>' . get_ledger($row['first_to']) . '</td>
                            <td>' . $row['tot_debit'] . '</td>
                            <td class="no-print actions-col" style="white-space:nowrap">
                                <div class="dropdown">
                                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                  </button>
                                  <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="' . $_SERVER['PHP_SELF'] . '?id=' . $row['sno'] . '">✏️ Edit</a>
                                    <a class="dropdown-item" target="_blank" href="cash_voucher_details.php?id=' . $row['sno'] . '">👁️ View Details</a>
                                    <a class="dropdown-item" target="_blank" href="cash_voucher_print.php?id=' . $row['sno'] . '">🧾 Voucher</a>
                                  <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="' . $_SERVER['PHP_SELF'] . '?del=' . $row['sno'] . '" onclick="return confirm(\'Delete this entry?\')">🗑️ Delete</a>
                                  </div>
                                </div>
                            </td>
                        </tr>';
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>


			<?php
			break;
				}
			}
			?>

</div>

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Create New Ledger</h5>
				<button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>


			<div class="modal-body">
				<form name="create_ledger" id="create_ledger" action="scripts/billit_ajax.php?id=create_ledger"
					method="get">
					<p>Enter Details</p>
					<div class="col-md-6">
						<td>
							<label>Parent :</label>
							<select id="parent" name="parent" tabindex="1" class="form-control">
								<option value="1">BANK ACCOUNTS</option>
								<option value="2">BANK OCC A/C</option>
								<option value="3">BANK OD A/C</option>
								<option value="4">BRANCH/DIVISIONS</option>
								<option value="5">CAPITAL ACCOUNT</option>
								<option value="6">CASH IN HAND</option>
								<option value="7">CURRENT ASSETS</option>
								<option value="8">CURRENT LIABILITIES</option>
								<option value="9">DEPOSITS (ASSET)</option>
								<option value="10">DIRECT EXPENSES</option>
								<option value="11">DIRECT INCOMES</option>
								<option value="12">DUTIES & TAXES</option>
								<option value="15">FIXED ASSETS</option>
								<option value="18">INDIRECT EXPENSES</option>
								<option value="19">INDIRECT INCOMES</option>
								<option value="20">INVESTMENTS</option>
								<option value="21">LOAN & ADVANCES (ASSET)</option>
								<option value="22">LOANS (LIABILITY)</option>
								<option value="23">MISC. EXPENSES (ASSET)</option>
								<option value="24">PROVISIONS</option>
								<option value="25">PURCHASE ACCOUNTS</option>
								<option value="26">RESERVES & SURPLUS</option>
								<option value="27">RETAINED EARNINGS</option>
								<option value="28">SALES ACCOUNTS</option>
								<option value="29">SECURED LOANS</option>
								<option value="30">STOCK IN HAND</option>
								<option value="31">SUNDRY CREDITORS</option>
								<option value="6" selected="selected">CASH IN HAND</option>
								<option value="33">SUSPENSE A/C</option>
								<option value="34">UNSECURED LOANS</option>
							</select>
						</td>
					</div>
					<div class="row">
						<div class="col-md-6">
							<label>Ledger Name</label>
							<input id="supplier" name="cus_name" tabindex="21" value="" type="text"
								class="form-control">
						</div>
						<div class="col-md-6">
							<label>State</label>
							<select id="state" name="state" tabindex="22" class="form-control">
								<?php
								$sql = 'select * from general_settings where `desc`="state"';
								$default_state = mysqli_fetch_assoc(execute_query($sql));

								$sql = 'select * from billit_state_name';
								$res_state = execute_query($sql);
								while ($row_state = mysqli_fetch_array($res_state)) {
									echo '<option value="' . $row_state['state_code'] . '" ';
									if (isset($_GET['id'])) {
										if (strtoupper(trim($row_state['state_code'])) == strtoupper(trim($ledger['state']))) {
											echo ' selected="selected" ';
										}
									} else {
										if (strtoupper(trim($row_state['state_code'])) == $default_state['rate']) {
											echo ' selected="selected"';
										}
									}
									echo '>' . $row_state['indian_states'] . '</option>';
								}
								?>
							</select></td>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<label>Mobile</label>
							<input id="mobile" name="mobile" tabindex="23" value="" type="text" class="form-control">
						</div>
						<div class="col-md-6">
							<label>GSTIN</label>
							<input id="tin" name="tin" tabindex="24" value="" type="text" class="form-control">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<label>Address</label>
							<input id="address" name="address" tabindex="25" value="" type="text" class="form-control">
						</div>
						<div class="col-md-6">
							<label>Address 2</label>
							<input id="add_2" name="add_2" tabindex="26" value="" type="text" class="form-control">
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<div class="col-md-12 text-center" id="ajax_loader" style="display:none;"><img
						src="images/loading_transparent.gif"></div>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="save" onclick="create_new();">Save changes</button>
			</div>
		</div>
	</div>
</div>
<?php page_footer_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
	function create_new() {
		var parent = $('#parent').val();
		var cus_name = $.trim($('#supplier').val()); // ledger name input in modal
		var state = $('#state').val();
		var mobile = $('#mobile').val();
		var tin = $('#tin').val();
		var address = $('#address').val();
		var address2 = $('#add_2').val();

		if (cus_name === '') {
			alert('Ledger Name is required!');
			return;
		}

		var finaldata = 'id=create_ledger' +
			'&parent=' + encodeURIComponent(parent) +
			'&term=t' +
			'&cus_name=' + encodeURIComponent(cus_name) +
			'&state=' + encodeURIComponent(state) +
			'&mobile=' + encodeURIComponent(mobile) +
			'&tin=' + encodeURIComponent(tin) +
			'&address=' + encodeURIComponent(address) +
			'&add_2=' + encodeURIComponent(address2);

		var form = $('#create_ledger');
		document.getElementById('ajax_loader').style.display = 'block';

		$.ajax({
			type: "GET",
			url: form.attr('action'),
			data: finaldata,
			dataType: 'json',
			cache: false,
			success: function (resp) {
				document.getElementById('ajax_loader').style.display = 'none';
				if (resp.success) {
					alert('✅ Ledger "' + resp.name + '" created successfully!');
					window.location.href = 'cash_voucher.php';
				} else {
					alert('❌ Error: ' + resp.message);
				}
			},
			error: function () {
				document.getElementById('ajax_loader').style.display = 'none';
				alert('❌ Server error. Please try again.');
			}
		});
	}
	function edit_client() {
		var id = $("#supplier1_sno").val();
		if (id == '') {
			alert('Please select a customer.');
			return;
		}
		else {
			window.open("ledgers.php?id=" + id);
		}
	}
	$(function () {
		var options = {
			source: function (request, response) {
				$.getJSON("scripts/billit_ajax.php?id=cust_name&cust=1", request, response);
			},
			minLength: 1,
			select: function (event, ui) {
				log(ui.item ?
					"Selected: " + ui.item.value + " aka " + ui.item.description :
					"Nothing selected, input was " + this.value);
			},
			select: function (event, ui) {
				var id = document.getElementById('current').value;
				ui = ui.item;
				$("[name='account_" + id + "']").val(ui.label);
				$('#account_' + id + '_sno').val(ui.id);
				$('#ledger_type_' + id).val(ui.type);
				
				var balance = parseFloat(ui.balance) || 0;
				var formattedBalance = Math.abs(balance).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
				var balanceText = formattedBalance + (balance < 0 ? ' Cr' : (balance > 0 ? ' Dr' : ''));
				$('#balance_' + id).text(balanceText);
				
				return false;
			}

		};

		$(document.body).on("keydown.autocomplete", "[id^='account_']", function () {
			var current = $(this).attr('id').replace('account_', '');
			$(this).autocomplete(options);
		});
	});

	$(document).ready(function () {
		// Add event listeners for automatic total calculation
		$(document).on('input', '[id^="debit_"], [id^="credit_"]', function () {
			calc_total();
		});

		// Auto-toggle By/To and move focus on Enter key
		$(document).on('keydown', 'input, select', function (e) {
			if (e.keyCode === 13) { // Enter key
				// IMPORTANT: Do not prevent default if the element is the submit button itself
				if ($(this).attr('id') === 'saveForm' || $(this).attr('type') === 'submit') {
					return true;
				}
				e.preventDefault();
				var $this = $(this);
				var id = $this.attr('id') || '';

				// Special handling for Description to add row
				if (id.indexOf('description_') !== -1 && id !== 'header_description') {
					var current_id = parseInt(id.split('_')[1]);
					var next_id = current_id + 1;

					// Only jump to submit if we are on Description and the totals match
					var tot_damt = parseFloat($("#total_damt_hidden").val()) || 0;
					var tot_camt = parseFloat($("#total_camt_hidden").val()) || 0;
					if (tot_damt > 0 && Math.abs(tot_damt - tot_camt) < 0.001) {
						setTimeout(function() { $('#saveForm').focus(); }, 10);
						return;
					}

					// Add row if it doesn't exist
					if (!$('#row_' + next_id).length) {
						add_new_row();
					}

					// Toggle Voucher Type
					var current_type = $('#voucher_type_' + current_id).val();
					var next_type = (current_type === 'by') ? 'to' : 'by';

					$('#voucher_type_' + next_id).val(next_type);
					update_voucher(next_id);

					// Move focus to next row ledger field
					$('#account_' + next_id).focus();
					return;
				}

				// Special handling for Debit/Credit to move to Description
				if (id.indexOf('debit_') !== -1 || id.indexOf('credit_') !== -1) {
					var current_id = parseInt(id.split('_')[1]);
					$('#description_' + current_id).focus();
					return;
				}

				// Generic "Enter as Tab" behavior
				var inputs = $(this).closest('form').find(':input:visible:not([disabled])');
				var idx = inputs.index(this);

				if (idx !== -1 && idx < inputs.length - 1) {
					inputs[idx + 1].focus();
				}
			}
		});

		// Initialize totals on page load
		calc_total();
	});

	function add_new_row() {
		var tot_damt = parseFloat($("#total_damt_hidden").val()) || 0;
		var tot_camt = parseFloat($("#total_camt_hidden").val()) || 0;
		if (tot_damt > 0 && tot_camt > 0 && tot_damt === tot_camt) {
			$('#saveForm').focus();
			return false;
		}

		var max_id = parseInt($("#id").val());
		var new_id = max_id + 1;

		var txt = '<div class="row" id="row_' + new_id + '"><div class="col-1"><label>&nbsp;</label><select name="voucher_type_' + new_id + '" id="voucher_type_' + new_id + '" class="form-control" onFocus="set_current(' + new_id + ')" onChange="update_voucher(' + new_id + ')"><option value="by">By</option><option value="to">To</option></select></div><div class="col-3"><label for="">Ledger</label><input type="text" name="account_' + new_id + '" id="account_' + new_id + '" class="form-control" onFocus="set_current(' + new_id + ')"><span id="balance_' + new_id + '" class="text-primary font-weight-bold" style="font-size: 12px; display: block; margin-top: 2px;"></span><input type="hidden" name="account_' + new_id + '_sno" id="account_' + new_id + '_sno" class="form-control"><input type="hidden" name="ledger_type_' + new_id + '" id="ledger_type_' + new_id + '" class="form-control"></div><div class="col-2"><label for="">Debit</label><input type="text" name="debit_' + new_id + '" id="debit_' + new_id + '" class="form-control" placeholder="Amount" onFocus="set_current(' + new_id + ')"></div><div class="col-2"><label for="">Credit</label><input type="text" name="credit_' + new_id + '" id="credit_' + new_id + '" class="form-control" placeholder="Amount" disabled onFocus="set_current(' + new_id + ')"></div><div class="col-3"><label for="">Description</label><input type="text" name="description_' + new_id + '" id="description_' + new_id + '" class="form-control" placeholder="Description" onFocus="set_current(' + new_id + ')"></div><div class="col-1"><label>&nbsp;</label><button type="button" class="btn btn-danger btn-sm" onclick="remove_row(' + new_id + ')" id="remove_btn_' + new_id + '">Remove</button></div></div>';

		$("#legend_container").append(txt);
		$("#id").val(new_id);

		// Show remove button for first row if more than 1 row exists
		if (new_id > 1) {
			$("#remove_btn_1").show();
		}
	}

	function remove_row(id) {
		$("#row_" + id).remove();

		// Hide remove button for first row if only 1 row exists
		var remaining_rows = $("#legend_container .row").length;
		if (remaining_rows <= 1) {
			$("#remove_btn_1").hide();
		}

		calc_total();
	}

	function calc_total() {
		var tot_camt = 0;
		var tot_damt = 0;

		var max_id = $("#id").val();
		for (i = 1; i <= max_id; i++) {

			//console.log(max_id+"A: "+i+">>"+$("#amount"+i).val()+" >> "+document.getElementById("amount"+i).value);
			var damt = parseFloat($("#debit_" + i).val());
			if (!damt) {
				damt = 0;
			}
			var camt = parseFloat($("#credit_" + i).val());
			if (!camt) {
				camt = 0;
			}
			tot_damt += damt;
			tot_camt += camt;
		}
		$("#total_damt").html(tot_damt.toFixed(2));
		$("#total_camt").html(tot_camt.toFixed(2));
		$("#total_damt_hidden").val(tot_damt.toFixed(2));
		$("#total_camt_hidden").val(tot_camt.toFixed(2));

	}

	function update_voucher(id) {
		var vch_type = $("#voucher_type_" + id).val();
		var debit_val = $("#debit_" + id).val();
		var credit_val = $("#credit_" + id).val();

		if (vch_type == 'by') {
			if (debit_val == '' || debit_val == 0) { // Only shift if debit is empty
				if (credit_val != '' && credit_val != 0) {
					$("#debit_" + id).val(credit_val);
					$("#credit_" + id).val('');
				}
			}
			$("#credit_" + id).prop("disabled", true);
			$("#debit_" + id).prop("disabled", false);
		}
		else if (vch_type == 'to') {
			if (credit_val == '' || credit_val == 0) { // Only shift if credit is empty
				if (debit_val != '' && debit_val != 0) {
					$("#credit_" + id).val(debit_val);
					$("#debit_" + id).val('');
				}
			}
			$("#credit_" + id).prop("disabled", false);
			$("#debit_" + id).prop("disabled", true);
		}
		calc_total();
	}

	function set_current(id) {
		$("#current").val(id);
	}


	function check_prev_date(form_date) {
		var cur_date = "<?php echo date("Y-m-d"); ?>";
		var warn = 0;
		$(".noblank").each(function (index, element) {
			if ($(element).val() == "") {
				$(element).css("backgroundColor", "yellow");
				warn = 1;
			}
			else {
				$(element).css("backgroundColor", "white");
			}
		});
		if ($("#total_damt_hidden").val() != $("#total_camt_hidden").val()) {
			alert("Credit and Debit amount is different. Kindly Check.");
			return false;
		}
		if (warn != 0) {
			alert("Please enter all complusory blocks");
			return false;
		}

		if (cur_date > form_date) {
			var response = confirm("Entry date is old than today. Do you want to proceed. ?");
		}
		else {
			var response = confirm("Are you sure?");
		}
		return response;
	}

	function update_parent(parent_id) {
		// Get the dropdown element
		var selectObj = document.querySelector('select[name="parent"]');
		var parent_name = selectObj.options[selectObj.selectedIndex].text;

		// Optional: Show selected parent somewhere (debug)
		console.log("Selected Parent ID:", parent_id);
		console.log("Selected Parent Name:", parent_name);

		// Store parent_id in a hidden input for form submission
		var hiddenInput = document.getElementById('parent_hidden');
		if (!hiddenInput) {
			hiddenInput = document.createElement('input');
			hiddenInput.type = 'hidden';
			hiddenInput.id = 'parent_hidden';
			hiddenInput.name = 'parent_id';
			document.forms[0].appendChild(hiddenInput); // first form me add
		}
		hiddenInput.value = parent_id;
	}



</script>
<!-- FIRST load jQuery only ONCE -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script src="js/jquery.3.2.1.min.js"></script>

<!-- Then load jQuery UI -->
<!-- <script src="http://localhost/ERP-UPRNSS/jquery_ui/jquery-ui.js"></script> -->
<script src="js/jquery-ui.js"></script>
<!-- Bootstrap -->
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
<!--<script src="js/core/bootstrap.min.js"></script>-->
<!-- <script src="http://localhost/ERP-UPRNSS/bootstrap/js/bootstrap-switch.js"></script> -->
<script src="js/plugins/bootstrap-switch.js"></script>

<!-- Then your custom scripts / handlers -->
<script>
	$(document).on('click', '[data-toggle="modal"]', function () {
		$($(this).data('target')).modal('show');
	});
</script>
<script>
	$("#create_ledger").on("submit", function (e) {
		e.preventDefault();
		console.log("Form submit executed!");
		create_new();
	});
	var journalData = <?php echo json_encode($old_Journal ?? []); ?>;
	document.addEventListener("DOMContentLoaded", function () {
		if (!journalData || journalData.length === 0) return;
		let totalDebit = 0;
		let totalCredit = 0;
		journalData.forEach(function (row, index) {
			let i = index + 1;
			if (i > 1) {
				add_new_row();
			}
			set_current(i);
			if (document.getElementsByName("description_" + i)[0]) {
				document.getElementsByName("description_" + i)[0].value = row.remarks;
			}
			if (row.by && row.by !== "") {
				$("#voucher_type_" + i).val("by");
				update_voucher(i);
				$("#debit_" + i).val(row.amount);
				getLedgerName(row.by, function (name) {
					$("#account_" + i).val(name);
					$("#account_" + i + "_sno").val(row.by);
				});
				totalDebit += parseFloat(row.amount || 0);
			}
			if (row.to && row.to !== "") {
				$("#voucher_type_" + i).val("to");
				update_voucher(i);
				$("#credit_" + i).val(row.amount);
				getLedgerName(row.to, function (name) {
					$("#account_" + i).val(name);
					$("#account_" + i + "_sno").val(row.to);
				});
				totalCredit += parseFloat(row.amount || 0);
			}

		});
		$("#total_damt").text(totalDebit);
		$("#total_camt").text(totalCredit);
		$("#total_damt_hidden").val(totalDebit);
		$("#total_camt_hidden").val(totalCredit);
	});
	function getLedgerName(sno, callback) {
		$.ajax({
			url: "scripts/ajax.php",
			type: "POST",
			data: {
				"term": "b",
				"id": "get_ledger",
				"sno": sno
			},
			success: function (response) {
				callback(response);
			}
		});
	}
</script>

<?php
page_footer_end();
?>
?>