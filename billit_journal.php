<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");
$msg = '';
$response = 0;
$finalmsg = '';
$tab = 1;
date_default_timezone_set('Asia/Calcutta');
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if (!isset($_SESSION['username'])) {
	$_SESSION['username'] = 'system';
}


if (isset($_POST['sale_date'])) {
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
	$sql = 'INSERT INTO billit_invoice_journal 
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
					$sql = 'INSERT INTO billit_stock_journal 
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
					$sql = 'INSERT INTO billit_stock_journal 
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
			$msg .= 'Data Saved';
			// Redirect to same page with success message
			header("Location: billit_journal.php?success=1");
			exit();
		}
	}

	$response = 1;
} else {
	$sql = 'select * from billit_stock_journal order by sno desc limit 1';
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
	$sql = 'SELECT * FROM billit_invoice_journal WHERE sno="' . $_GET['id'] . '"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));

	// Also get vendor name and status from stock journal if possible
	$sql_extra = 'SELECT vendor, status FROM billit_stock_journal WHERE journal_id="' . $_GET['id'] . '" LIMIT 1';
	$res_extra = execute_query($sql_extra);
	if ($row_extra = mysqli_fetch_assoc($res_extra)) {
		$old_data['vendor_name'] = $row_extra['vendor'];
		$old_data['status'] = $row_extra['status'];
	}
}
if (isset($_GET['del'])) {
	$sql = 'delete from billit_invoice_journal where sno="' . $_GET['del'] . '"';
	execute_query($sql);
	$sql = 'delete from billit_stock_journal where journal_id="' . $_GET['del'] . '"';
	execute_query($sql);
	$msg .= 'Journal Entry Successfully Deleted';
}
if (isset($_GET['success']) && $_GET['success'] == 1) {
	$msg = 'Journal Entry Successfully Inserted!';
}
if (isset($_GET['view'])) {
	$response = 2;
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
	function formatBalance(balance) {
		balance = parseFloat(balance);
		if (isNaN(balance)) return "";
		if (balance > 0) {
			return balance.toFixed(2) + " Dr";
		} else if (balance < 0) {
			return Math.abs(balance).toFixed(2) + " Cr";
		} else {
			return "0.00";
		}
	}
	function recalcRowBalance(id) {
		var origBal = parseFloat($('#orig_balance_' + id).val()) || 0;
		var debit = parseFloat($('#debit_' + id).val()) || 0;
		var credit = parseFloat($('#credit_' + id).val()) || 0;

		var newBal = origBal + debit - credit;
		if ($('#account_' + id + '_sno').val() != "") {
			$('#balance_' + id).html("Cur Bal: " + formatBalance(newBal));
		}
	}
</script>


<?php
page_header_end();
page_sidebar();
?>
<form id="purchase_form" name="purchase_form" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>"
	onSubmit="return check_prev_date($('[name=&#34;sale_date&#34;]').val());">
	<input type="hidden" name="parent_id" id="parent_hidden" value="32">

	<div class="row">
		<div class="col-md-12">

			<?php
			switch ($response) {
				case 1: {
					?>
					<?php if ($msg != ''): ?>
							<div class="row mb-2">
								<div class="col-12"><?php echo alert($msg); ?></div>
							</div>
					<?php endif; ?>
						<div class="card mb-3">
							<div class="card-body py-2">
								<div class="row align-items-center">
									<div class="col-md-1 font-weight-bold text-nowrap">Unit Name</div>
									<div class="col-md-3">
										<?php
										$options = '';
										if (is_array($_SESSION['divisions'])) {
											foreach ($_SESSION['divisions'] as $k => $v) {
												$options .= '<option value="' . $v . '">' . get_division($v) . '</option>';
											}
										}
										?>
										<select name="unit_id" id="unit_id" class="form-control form-control-sm">
										<?php echo $options; ?>
										</select>
									</div>
									<div class="col-md-1 font-weight-bold">Date</div>
									<div class="col-md-2">
										<script type="text/javascript" language="javascript">
											document.writeln(DateInput('sale_date', 'purchase_form', false, 'YYYY-MM-DD', '<?php if (isset($_GET['id'])) {
												echo $old_data['timestamp'];
											} else {
												echo $_POST['sale_date'];
											} ?>', <?php echo $tab++;
											 $tab += 3; ?>));
										</script>
									</div>
									<div class="col-md-1 font-weight-bold text-nowrap">Voucher No.</div>
									<div class="col-md-2">
										<input id="challan_no" name="challan_no" class="form-control form-control-sm" maxlength="18"
											tabindex="<?php echo $tab++; ?>" type="text" value="<?php if (isset($old_data['voucher_no']))
												   echo $old_data['voucher_no']; ?>">
									</div>
									<div class="col-md-1 text-center">
										<label class="mb-0 font-weight-bold">Final</label><br>
										<input type="checkbox" name="treat_as_final" <?php if (isset($old_data['status']) && $old_data['status'] == 1) {
											echo "checked='checked'";
										} elseif (!isset($_GET['id'])) {
											echo 'checked="checked"';
										} ?>>
									</div>
									<div class="col-md-1 text-right">
										<a href="billit_journal.php?view=view">
											<button type="button" class="btn btn-warning btn-sm">View Vouchers</button>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="card pb-5 px-2">
							<div class="row">
								<div class="col-12">
									<div class="alert alert-primary mb-3">Particulars</div>
								</div>
							</div>

							<div class="legend" id="legend_container">
								<div class="row align-items-end mb-2" id="row_ledger_1">
									<div class="col-1">
										<label>By/To</label>
										<select name="voucher_type_1" id="voucher_type_1" class="form-control"
											onFocus="set_current(1)" onChange="update_voucher(1);">
											<option value="by">By</option>
											<option value="to">To</option>
										</select>
									</div>
									<div class="col-4">
										<div class="d-flex justify-content-between align-items-center">
											<label class="mb-0">Ledger</label>
											<div id="balance_1" style="font-size:11px;color:blue;font-weight:bold;"></div>
										</div>
										<input type="text" name="account_1" id="account_1" class="form-control"
											onFocus="set_current(1)">
										<input type="hidden" name="account_1_sno" id="account_1_sno">
										<input type="hidden" name="ledger_type_1" id="ledger_type_1">
										<input type="hidden" name="orig_balance_1" id="orig_balance_1" value="0">
									</div>
									<div class="col-3">
										<label>Debit</label>
										<input type="text" name="debit_1" id="debit_1" class="form-control" placeholder="Amount"
											onFocus="set_current(1)">
									</div>
									<div class="col-3">
										<label>Credit</label>
										<input type="text" name="credit_1" id="credit_1" class="form-control" placeholder="Amount"
											disabled onFocus="set_current(1)">
									</div>
									<div class="col-1">
										<button type="button" class="btn btn-danger btn-sm" onclick="remove_row(1)"
											id="remove_btn_1" style="display:none;">✕</button>
									</div>
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-12">
									<label for="common_description"><strong>Description / Narration</strong></label>
									<textarea type="text" name="description_1" id="common_description"
										class="form-control form-control-sm" style="width: 40%;"
										placeholder="Enter narration for this voucher"></textarea>
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-12">
									<button type="button" class="btn btn-success" onclick="add_new_row()">Add Row</button>
									<button type="button" class="btn btn-info" onclick="window.open('billit_ledgers.php')">New
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
						<a href="billit_journal.php"><button type="button" class="btn btn-warning">Create Vouchers (Journal
								Entry)</button></a>
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
									<th>Voucher No.</th>
									<th>Particulars</th>
									<th>Amount</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ($_SESSION['usertype'] != 'sadmin') {
									$sql = 'SELECT * FROM billit_invoice_journal WHERE created_by="' . $_SESSION['username'] . '"';
								} else {
									$sql = 'SELECT * FROM billit_invoice_journal';
								}

								$result = execute_query($sql);
								$i = 1;
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<tr>
                            <td>' . $i++ . '</td>
                            <td>' . $row['timestamp'] . '</td>
                            <td>' . get_division($row['unit_id']) . '</td>
                            <td>' . $row['voucher_no'] . '</td>
                            <td>' . get_ledger($row['first_to']) . '</td>
                            <td>' . $row['tot_debit'] . '</td>
                            <td class="no-print actions-col" style="white-space:nowrap">
                                <div class="dropdown">
                                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                  </button>
                                  <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="billit_journal.php?id=' . $row['sno'] . '">✏️ Edit</a>
                                    <a class="dropdown-item" target="_blank" href="billit_journal_details.php?id=' . $row['sno'] . '">👁️ View Details</a>
                                    <a class="dropdown-item" target="_blank" href="billit_journal_print.php?id=' . $row['sno'] . '">🧾 Voucher</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="billit_journal.php?del=' . $row['sno'] . '" onclick="return confirm(\'Are you sure you want to delete this voucher?\');">🗑️ Delete</a>
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
<?php page_footer_start(); ?>
<script src="js/core/bootstrap.min.js"></script>

<script>
	function edit_client() {
		var current_row = $("#current").val();
		var id = $("#account_" + current_row + "_sno").val();
		if (id == '') {
			alert('Please select a ledger first.');
			return;
		}
		window.open("billit_ledgers.php?id=" + id);
	}
	$(function () {
		var options = {
			source: function (request, response) {
				$.ajax({
					url: "scripts/billit_ajax.php?id=cust_name&cust=1",
					dataType: "json",
					data: {
						term: request.term,
						division_id: $('#unit_id').val()
					},
					cache: false,
					success: function (data) {
						response(data);
					}
				});
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

				if (ui.balance !== undefined) {
					$('#orig_balance_' + id).val(ui.balance);
					recalcRowBalance(id);
				} else {
					$('#orig_balance_' + id).val(0);
					$('#balance_' + id).html("");
				}
				return false;
			}

		};

		$(document.body).on("input", "[id^='account_']", function () {
			var id = $(this).attr('id').replace('account_', '');
			if ($(this).val() == "") {
				$('#account_' + id + '_sno').val("");
				$('#orig_balance_' + id).val(0);
				$('#balance_' + id).html("");
			}
		});

		$(document).on('input', '[id^="debit_"], [id^="credit_"]', function () {
			var id = $(this).attr('id').split('_')[1];
			recalcRowBalance(id);
		});

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

		// Initialize totals on page load
		calc_total();
	});

	function add_new_row() {
		var tot_damt = parseFloat($("#total_damt_hidden").val()) || 0;
		var tot_camt = parseFloat($("#total_camt_hidden").val()) || 0;
		if (tot_damt > 0 && tot_camt > 0 && Math.abs(tot_damt - tot_camt) < 0.001) {
			$('#saveForm').focus();
			return false;
		}

		var max_id = parseInt($("#id").val());
		var new_id = max_id + 1;

		var txt = '<div class="row align-items-end mb-2" id="row_ledger_' + new_id + '">' +
			'<div class="col-1">' +
			'<select name="voucher_type_' + new_id + '" id="voucher_type_' + new_id + '" class="form-control" onFocus="set_current(' + new_id + ')" onChange="update_voucher(' + new_id + ')">' +
			'<option value="by">By</option><option value="to">To</option></select></div>' +
			'<div class="col-4">' +
			'<div class="d-flex justify-content-between align-items-center">' +
			'<span style="font-size:14px;">Ledger</span>' +
			'<div id="balance_' + new_id + '" style="font-size:11px;color:blue;font-weight:bold;"></div>' +
			'</div>' +
			'<input type="text" name="account_' + new_id + '" id="account_' + new_id + '" class="form-control" placeholder="Ledger" onFocus="set_current(' + new_id + ')">' +
			'<input type="hidden" name="account_' + new_id + '_sno" id="account_' + new_id + '_sno">' +
			'<input type="hidden" name="ledger_type_' + new_id + '" id="ledger_type_' + new_id + '">' +
			'<input type="hidden" name="orig_balance_' + new_id + '" id="orig_balance_' + new_id + '" value="0"></div>' +
			'<div class="col-3"><input type="text" name="debit_' + new_id + '" id="debit_' + new_id + '" class="form-control" placeholder="Debit" onFocus="set_current(' + new_id + ')"></div>' +
			'<div class="col-3"><input type="text" name="credit_' + new_id + '" id="credit_' + new_id + '" class="form-control" placeholder="Credit" disabled onFocus="set_current(' + new_id + ')"></div>' +
			'<div class="col-1"><button type="button" class="btn btn-danger btn-sm" onclick="remove_row(' + new_id + ')" id="remove_btn_' + new_id + '">✕</button></div>' +
			'</div>';

		$("#legend_container").append(txt);
		$("#id").val(new_id);
		$("#remove_btn_1").show();
	}

	function remove_row(id) {
		$("#row_ledger_" + id).remove();
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

	$(document).on('keydown', 'input, select', function (e) {
		if (e.keyCode === 13) { // Enter key
			// IMPORTANT: Do not prevent default if the element is the submit button itself
			if ($(this).attr('id') === 'saveForm' || $(this).attr('type') === 'submit') {
				return true;
			}
			e.preventDefault();
			var $this = $(this);
			var id = $this.attr('id') || '';
			if (id.indexOf('credit_') !== -1) {
				var current_id = parseInt(id.split('_')[1]);
				var tot_damt = parseFloat($("#total_damt_hidden").val()) || 0;
				var tot_camt = parseFloat($("#total_camt_hidden").val()) || 0;
				if (tot_damt > 0 && Math.abs(tot_damt - tot_camt) < 0.001) {
					$('#common_description').focus();
					return;
				}
				add_new_row();
				var next_id = parseInt($("#id").val());
				$('#voucher_type_' + next_id).val('to');
				update_voucher(next_id);
				setTimeout(function () { $('#account_' + next_id).focus(); }, 50);
				return;
			}
			if (id.indexOf('debit_') !== -1) {
				var current_id = parseInt(id.split('_')[1]);
				if (!$('#credit_' + current_id).prop('disabled')) {
					$('#credit_' + current_id).focus();
				} else {
					var tot_damt = parseFloat($("#total_damt_hidden").val()) || 0;
					var tot_camt = parseFloat($("#total_camt_hidden").val()) || 0;
					if (tot_damt > 0 && Math.abs(tot_damt - tot_camt) < 0.001) {
						$('#common_description').focus();
						return;
					}
					add_new_row();
					var next_id = parseInt($("#id").val());
					$('#voucher_type_' + next_id).val('to');
					update_voucher(next_id);
					setTimeout(function () { $('#account_' + next_id).focus(); }, 50);
				}
				return;
			}

			if (id.indexOf('account_') !== -1 && id.indexOf('_sno') === -1) {
				var current_id = id.replace('account_', '');
				var vch_type = $('#voucher_type_' + current_id).val();
				if (vch_type === 'by') {
					$('#debit_' + current_id).focus();
				} else {
					$('#credit_' + current_id).focus();
				}
				return;
			}
			var inputs = $(this).closest('form').find(':input:visible:not([disabled])');
			var idx = inputs.index(this);

			if (idx !== -1 && idx < inputs.length - 1) {
				inputs[idx + 1].focus();
			}
		}
	});

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
</script>
<?php
if (isset($_GET['id'])) {
	$sql = 'select * from billit_stock_journal where journal_id="' . $_GET['id'] . '"';
	$result = execute_query($sql);
	$rows = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$description = $row['remarks'];
		$rows[] = $row;
	}

	echo "<script>
			$(document).ready(function() {
				$('#unit_id').val('" . $old_data['unit_id'] . "');
		";

	echo "$('#header_description').val('" . $description . "');";

	$i = 1;
	foreach ($rows as $r) {
		if ($i > 1) {
			echo "add_new_row();\n";
			echo "$('#description_" . $i . "').val('" . $r['remarks'] . "');\n";
		}

		if ($r['by'] != "") {
			$bal = get_cust_balace('1970-01-01', date("Y-m-d"), $r['by']);
			echo "$('#voucher_type_" . $i . "').val('by');\n";
			echo "$('#account_" . $i . "_sno').val('" . $r['by'] . "');\n";
			echo "$('#account_" . $i . "').val('" . get_ledger($r['by']) . "');\n";
			echo "$('#orig_balance_" . $i . "').val('" . $bal . "');\n";
			echo "$('#balance_" . $i . "').html('Cur Bal: ' + formatBalance(" . $bal . "));\n";
			echo "$('#debit_" . $i . "').val('" . $r['amount'] . "');\n";
			echo "update_voucher(" . $i . ");\n";
			echo "recalcRowBalance(" . $i . ");\n";
		} else {
			$bal = get_cust_balace('1970-01-01', date("Y-m-d"), $r['to']);
			echo "$('#voucher_type_" . $i . "').val('to');\n";
			echo "$('#account_" . $i . "_sno').val('" . $r['to'] . "');\n";
			echo "$('#account_" . $i . "').val('" . get_ledger($r['to']) . "');\n";
			echo "$('#orig_balance_" . $i . "').val('" . $bal . "');\n";
			echo "$('#balance_" . $i . "').html('Cur Bal: ' + formatBalance(" . $bal . "));\n";
			echo "$('#credit_" . $i . "').val('" . $r['amount'] . "');\n";
			echo "update_voucher(" . $i . ");\n";
			echo "recalcRowBalance(" . $i . ");\n";
		}
		$i++;
	}
	echo "
				calc_total();
			});
		</script>";
}
?>
<?php
page_footer_end();
?>