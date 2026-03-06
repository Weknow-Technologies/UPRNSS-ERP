<?php
include("scripts/settings.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
$msg = '';
$msg1 = '';
$tab = 1;

page_header_start();
page_header_end();
page_sidebar();

if (isset($_POST['submit'])) {

	if ($_POST['fund_transfer_by'] == 1) {
		$_POST['by_id'] = 1;
	} elseif ($_POST['fund_transfer_by'] == 2) {
		$_POST['by_id'] = $_POST['unit_id'];
	} elseif ($_POST['fund_transfer_by'] == 3) {
		$_POST['by_id'] = '';
	}


	if ($_POST['fund_transfer_to'] == 1) {
		$_POST['to_id'] = 1;
	} elseif ($_POST['fund_transfer_to'] == 2) {
		$_POST['to_id'] = $_POST['unit_id'];
	} elseif ($_POST['fund_transfer_to'] == 3) {
		$_POST['to_id'] = "";
	}

	$_POST['by_type'] = "";
	$_POST['to_type'] = "";
	if ($_POST['fund_transfer_by'] == 1) {
		$_POST['by_type'] = "HO";
	} elseif ($_POST['fund_transfer_by'] == 2) {
		$_POST['by_type'] = "Unit";
	} elseif ($_POST['fund_transfer_by'] == 3) {
		$_POST['by_type'] = "Vendor";
	}

	if ($_POST['fund_transfer_to'] == 1) {
		$_POST['to_type'] = "HO";
	} elseif ($_POST['fund_transfer_to'] == 2) {
		$_POST['to_type'] = "Unit";
	} elseif ($_POST['fund_transfer_to'] == 3) {
		$_POST['to_type'] = "Vendor";
	}

	if ($_POST['edit_sno'] == '') {

		$sql = "INSERT INTO `invoice_account_fund_transafer`(
				`department`, `sub_department_id`, `district`,unit_id, `project_name`, `fund_transfer_to`, `order_no`, `voucher_no`, `order_date`, `transafer_date`, `transafer_amount`, `gstdeduction`, `cgst_amount`, `sgst_amount`, `sentagepercentage`, `sentage`, `gsttdspercentage`, `gsttds`, `leborses`, `incometax`, `praposemoney`, `remark`, `from_account_no`, `to_bank_name`, `to_bank_ifsc`, `to_account_no`, `vendor_id`, `status`, `created_by`, `creation_time`
				)
			values (
			'{$_POST['department']}','{$_POST['sub_department_id']}','{$_POST['district']}','{$_POST['unit_id']}','{$_POST['project_name']}','{$_POST['fund_transfer_to']}','{$_POST['order_no']}', '{$_POST['voucher_no']}', '{$_POST['order_date']}', '{$_POST['transafer_date']}', '{$_POST['transafer_amount']}', '{$_POST['gstdeduction']}', '{$_POST['cgst_amount']}', '{$_POST['sgst_amount']}', '{$_POST['sentagepercentage']}', '{$_POST['sentage']}', '{$_POST['gsttdspercentage']}', '{$_POST['gsttds']}', '{$_POST['leborses']}', '{$_POST['incometax']}', '{$_POST['praposemoney']}', '{$_POST['remark']}', '{$_POST['from_account_no']}','{$_POST['to_bank_name']}','{$_POST['to_bank_ifsc']}','{$_POST['to_account_no']}','{$_POST['vendor_id']}','0','" . $_SESSION['username'] . "','" . date("Y-m-d H:i:s") . "')";
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		}
		if ($msg == '') {
			$inv_id = mysqli_insert_id($db);
			$sql = 'SELECT * FROM `uprnss_department_name` where sno="' . $_POST['department_id_1'] . '"';
			$first_to = mysqli_fetch_assoc(execute_query($sql));


			$sql = 'insert into billit_invoice_erp_payment (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, unit_id, created_by, creation_time, table_name, table_id) values ("' . $_POST['transafer_date'] . '", "' . $_POST['to_bank_name'] . '", "' . $_POST['from_account_no'] . '", "' . $_POST['transafer_amount'] . '", "' . $_POST['transafer_amount'] . '", "", "' . $_POST['voucher_no'] . '", "' . $_POST['unit_id'] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '","invoice_account_fund_transafer", "' . $inv_id . '" )';
			execute_query($sql);

			$id_stock = mysqli_insert_id($db);
			$sql = 'select * from general_settings where `desc`="GSTW"';
			$gstw = mysqli_fetch_assoc(execute_query($sql));

			$sql = 'select * from general_settings where `desc`="ADVCEN"';
			$advcen = mysqli_fetch_assoc(execute_query($sql));

			$sql = 'select * from general_settings where `desc`="GSTTDSW"';
			$gsttds = mysqli_fetch_assoc(execute_query($sql));

			$sql = 'select * from general_settings where `desc`="LABOURCESSW"';
			$labourcessw = mysqli_fetch_assoc(execute_query($sql));

			$sql = 'select * from general_settings where `desc`="ITTDSW"';
			$ittds = mysqli_fetch_assoc(execute_query($sql));




			// amount in unit bank - NET AMOUNT (should be in BY field for Debit)
			$sql = 'insert into billit_stock_erp_payment (`journal_id`,by_type, by_id, to_type, to_id, `by`,  amount, timestamp, unit_id, status) values ("' . $id_stock . '","' . $_POST['by_type'] . '","' . $_POST['by_id'] . '","' . $_POST['to_type'] . '","' . $_POST['to_id'] . '", "' . $_POST['to_bank_name'] . '", "' . $_POST['praposemoney'] . '", "' . $_POST['transafer_date'] . '", "", "")';
			execute_query($sql);

			// GST AMOUNT - DEDUCTION (should be in BY field for Debit)
			$sql = 'insert into billit_stock_erp_payment (`journal_id`,by_type, by_id, to_type, to_id, `by`,  amount, timestamp, unit_id, status) values ("' . $id_stock . '","' . $_POST['by_type'] . '","' . $_POST['by_id'] . '","' . $_POST['to_type'] . '","' . $_POST['to_id'] . '", "' . $gstw['rate'] . '",  "' . $_POST['gstdeduction'] . '", "' . $_POST['transafer_date'] . '", "", "")';
			execute_query($sql);

			// ADV centage - DEDUCTION (should be in BY field for Debit)
			$sql = 'insert into billit_stock_erp_payment (`journal_id`,by_type, by_id, to_type, to_id, `by`,  amount, timestamp, unit_id, status) values ("' . $id_stock . '","' . $_POST['by_type'] . '","' . $_POST['by_id'] . '","' . $_POST['to_type'] . '","' . $_POST['to_id'] . '", "' . $advcen['rate'] . '",  "' . $_POST['sentage'] . '", "' . $_POST['transafer_date'] . '", "", "")';
			execute_query($sql);


			// GST-TDS - DEDUCTION (should be in BY field for Debit)
			$sql = 'insert into billit_stock_erp_payment (`journal_id`,by_type, by_id, to_type, to_id, `by`,  amount, timestamp, unit_id, status) values ("' . $id_stock . '","' . $_POST['by_type'] . '","' . $_POST['by_id'] . '","' . $_POST['to_type'] . '","' . $_POST['to_id'] . '", "' . $gsttds['rate'] . '",  "' . $_POST['gsttds'] . '", "' . $_POST['transafer_date'] . '", "", "")';
			execute_query($sql);

			// LABOUR CESS - DEDUCTION (should be in BY field for Debit)
			$sql = 'insert into billit_stock_erp_payment (`journal_id`,by_type, by_id, to_type, to_id, `by`,  amount, timestamp, unit_id, status) values ("' . $id_stock . '","' . $_POST['by_type'] . '","' . $_POST['by_id'] . '","' . $_POST['to_type'] . '","' . $_POST['to_id'] . '", "' . $labourcessw['rate'] . '",  "' . $_POST['leborses'] . '", "' . $_POST['transafer_date'] . '", "", "")';
			execute_query($sql);

			// IT - DEDUCTION (should be in BY field for Debit)
			$sql = 'insert into billit_stock_erp_payment (`journal_id`,by_type, by_id, to_type, to_id, `by`,  amount, timestamp, unit_id, status) values ("' . $id_stock . '","' . $_POST['by_type'] . '","' . $_POST['by_id'] . '","' . $_POST['to_type'] . '","' . $_POST['to_id'] . '", "' . $ittds['rate'] . '",  "' . $_POST['incometax'] . '", "' . $_POST['transafer_date'] . '", "", "")';
			execute_query($sql);


			// CREDIT to HO BANK - SOURCE BANK (Should be only Transfer Amount)
			$total_credit_amount = $_POST['transafer_amount'];
			$sql = 'insert into billit_stock_erp_payment (`journal_id`,by_type, by_id, to_type, to_id, `to`,  amount, timestamp, unit_id, status) values ("' . $id_stock . '","' . $_POST['by_type'] . '","' . $_POST['by_id'] . '","' . $_POST['to_type'] . '","' . $_POST['to_id'] . '", "' . $_POST['from_account_no'] . '", "' . $total_credit_amount . '", "' . $_POST['transafer_date'] . '", "", "")';
			execute_query($sql);

			if (mysqli_error($db)) {

				$msg .= '<div class="alert alert-danger">Error # 1.369 >> ' . $sql . '</div>';

			} else {

				$msg .= '<p class="text text-success">Successfully Add</p>';
				// Generate voucher number if not provided
				$voucher_no = $_POST['voucher_no'] ?: 'FT' . date('Y') . sprintf('%04d', $inv_id);
				// Update voucher number in database if it was empty
				if (empty($_POST['voucher_no'])) {
					$update_sql = 'UPDATE invoice_account_fund_transafer SET voucher_no = "' . $voucher_no . '" WHERE sno = "' . $inv_id . '"';
					execute_query($update_sql);
				}
				echo '<script>window.location.href="fund_transfer_voucher.php?voucher=' . $inv_id . '";</script>';
				exit;
			}


		}
	} else {
		$sql = 'UPDATE invoice_account_fund_transafer SET
			unit_id="' . $_POST['unit_id'] . '",
			fund_transfer_to="' . $_POST['fund_transfer_to'] . '",
			order_no="' . $_POST['order_no'] . '",
			voucher_no="' . $_POST['voucher_no'] . '",
			order_date="' . $_POST['order_date'] . '",
			transafer_date="' . $_POST['transafer_date'] . '",
			gst_per="' . $_POST['gst_per'] . '",
			gstdeduction="' . $_POST['gstdeduction'] . '",
			cgst_amount="' . $_POST['cgst_amount'] . '",
			sgst_amount="' . $_POST['sgst_amount'] . '",
			royalty="' . $_POST['royalty'] . '",
			it_per="' . $_POST['it_per'] . '",
			incometax="' . $_POST['incometax'] . '",
			security_per="' . $_POST['security_per'] . '",
			security="' . $_POST['security'] . '",
			gsttdspercentage="' . $_POST['gsttdspercentage'] . '",
			gsttds="' . $_POST['gsttds'] . '",
			
			other_title="' . $_POST['other_title'] . '",
			other_per="' . $_POST['other_per'] . '",
			other_add_ded="' . $_POST['other_add_ded'] . '",
			other_amount="' . $_POST['other_amount'] . '",
			
			praposemoney="' . $_POST['praposemoney'] . '",
			sentagepercentage="' . $_POST['sentagepercentage'] . '",
			sentage="' . $_POST['sentage'] . '",
			leborses_per="' . $_POST['leborses_per'] . '",
			leborses="' . $_POST['leborses'] . '",
			
			total_expen="' . $_POST['total_expen'] . '",
			remark="' . $_POST['remark'] . '",
			from_account_no="' . $_POST['from_account_no'] . '",
			to_account_no="' . $_POST['to_bank_name'] . '",
			vendor_id="' . $_POST['vendor_id'] . '",
			status="1",
			request_status="7",
			to_account_no="' . $_POST['to_bank_name'] . '",
			
			created_by="' . $_SESSION['username'] . '",
			creation_time="' . date("Y-m-d H:i:s") . '"
			WHERE sno="' . $_POST['edit_sno'] . '"';

		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		}
		if ($msg == '') {
			$msg .= '<p class="alert alert-success">Data Update</p>';
			echo '<script>window.location.href="fund_transfer_report.php";</script>';
			exit;

		}
	}
} else {

	postblank:
	$_POST['unit_id'] = "";
	$_POST['department'] = "";
	$_POST['sub_department_id'] = "";
	$_POST['district'] = "";
	$_POST['project_name'] = "";
	$_POST['fund_transfer_to'] = "";
	$_POST['order_no'] = "";
	$_POST['voucher_no'] = "";
	$_POST['order_date'] = date("d-m-Y");
	$_POST['transafer_date'] = date("d-m-Y");
	$_POST['transafer_amount'] = "";
	$_POST['gstdeduction'] = "";
	$_POST['royalty'] = "";
	$_POST['security'] = "";
	$_POST['totelmgst'] = "";
	$_POST['sentagepercentage'] = "";
	$_POST['sentage'] = "";
	$_POST['gst_per'] = "";
	$_POST['gsttdspercentage'] = "";
	$_POST['gsttds'] = "";
	$_POST['cgst_amount'] = "";
	$_POST['sgst_amount'] = "";
	$_POST['leborses'] = "";
	$_POST['incometax'] = "";
	$_POST['it_per'] = "";
	$_POST['praposemoney'] = "";
	$_POST['remark'] = "";
	$_POST['from_account_no'] = "";
	$_POST['to_bank_name'] = "";
	$_POST['to_bank_ifsc'] = "";
	$_POST['to_account_no'] = "";
	$_POST['vendor_id'] = "";
	$_POST['vendor_name'] = "";
	$_POST['other_amount'] = "";
	$_POST['total_expen'] = "";
	$_POST['edit_sno'] = '';
}


if (isset($_GET['edit_sno'])) {
	$sql = 'select * from invoice_account_fund_transafer where sno="' . $_GET['edit_sno'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['department'] = $data['department'];
	$_POST['sub_department_id'] = $data['sub_department_id'];
	$_POST['district'] = $data['district'];
	$_POST['project_name'] = $data['project_name'];
	$_POST['fund_transfer_to'] = $data['fund_transfer_to'];
	$_POST['order_no'] = $data['order_no'];
	$_POST['voucher_no'] = $data['voucher_no'];
	$_POST['order_data'] = $data['order_data'];
	$_POST['transafer_data'] = $data['transafer_data'];
	$_POST['transafer_amount'] = $data['transafer_amount'];
	$_POST['gstdeduction'] = $data['gstdeduction'];
	$_POST['cgst_amount'] = $data['cgst_amount'];
	$_POST['sgst_amount'] = $data['sgst_amount'];
	$_POST['sentagepercentage'] = $data['sentagepercentage'];
	$_POST['sentage'] = $data['sentage'];
	$_POST['gsttdspercentage'] = $data['gsttdspercentage'];
	$_POST['gsttds'] = $data['gsttds'];
	$_POST['leborses'] = $data['leborses'];
	$_POST['incometax'] = $data['incometax'];
	$_POST['praposemoney'] = $data['praposemoney'];
	$_POST['remark'] = $data['remark'];
	$_POST['from_account_no'] = $data['from_account_no'];
	$_POST['to_bank_name'] = $data['to_bank_name'];
	$_POST['to_bank_ifsc'] = $data['to_bank_ifsc'];
	$_POST['to_account_no'] = $data['to_account_no'];
	$_POST['vendor_id'] = $data['vendor_id'];

	$_POST['edit_sno'] = $data['sno'];
}

if (isset($_GET['del'])) {

	$sql = 'UPDATE  invoice_account_fund_transafer SET status="5" where sno="' . $_GET['del'] . '"';
	execute_query($sql);

	$msg1 .= '<p class="alert alert-danger">Data Deleted.</p>';
}
if (isset($_GET['id'])) {
	$sql = 'select * from invoice_account_fund_transafer where sno="' . $_GET['id'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['department'] = $data['department'];
	$_POST['sub_department_id'] = $data['sub_department_id'];
	$_POST['district'] = $data['district'];
	$_POST['project_name'] = $data['project_name'];
	$_POST['fund_transfer_to'] = $data['fund_transfer_to'];
	$_POST['transafer_amount'] = $data['transafer_amount'];
	$_POST['bill_date'] = $data['bill_date'];
	$_POST['bill_no'] = $data['bill_no'];
	$_POST['edit_sno'] = $data['sno'];

	// Get vendor information from project
	$sql_vendor = 'SELECT t.project_awarded_to, v.firm_name, v.contractor_name 
				   FROM tender_allotment t 
				   LEFT JOIN vendor v ON t.project_awarded_to = v.sno 
				   WHERE t.project_id = "' . $data['project_name'] . '" AND t.status != "5" 
				   LIMIT 1';
	$vendor_data = mysqli_fetch_assoc(execute_query($sql_vendor));
	if ($vendor_data) {
		$_POST['vendor_name'] = $vendor_data['firm_name'] . ' (' . $vendor_data['contractor_name'] . ')';
		$_POST['vendor_id'] = $vendor_data['project_awarded_to'];
	} else {
		$_POST['vendor_name'] = '';
		$_POST['vendor_id'] = '';
	}
}
?>
<style>
	label {
		color: #333 !important;
		font-weight: 600 !important;
		font-size: 12px !important;
		margin-bottom: 4px;
		text-transform: uppercase;
		letter-spacing: 0.3px;
	}

	.form-control,
	.form-select {
		border: 1px solid #d1d5db !important;
		border-radius: 4px !important;
		height: 38px !important;
		background-color: #ffffff !important;
		color: #111827 !important;
		font-weight: 500 !important;
		font-size: 14px !important;
		transition: all 0.2s ease;
	}

	.form-control:focus,
	.form-select:focus {
		border-color: #ae1f20 !important;
		box-shadow: 0 0 0 3px rgba(174, 31, 32, 0.1) !important;
		background-color: #fff !important;
	}

	.form-control[readonly] {
		background-color: #f9fafb !important;
		border: 1px solid #e5e7eb !important;
		color: #4b5563 !important;
		cursor: not-allowed;
	}

	#total_expen,
	#total_withgst,
	#net_payment,
	#praposemoney {
		border-left: 4px solid #ae1f20 !important;
		font-weight: 700 !important;
		color: #ae1f20 !important;
		background-color: #fff !important;
	}

	.card-header {
		background: #ae1f20 !important;
		padding: 15px !important;
	}

	.btn-success,
	.btn-primary {
		background-color: #ae1f20 !important;
		border: none !important;
		padding: 10px 25px !important;
		border-radius: 4px !important;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}

	input[type="number"],
	.text-right-input {
		text-align: right;
	}

	.form-control[readonly],
	.form-select[readonly] {
		background-color: #f9f9f9 !important;
		color: #000 !important;
		font-weight: 600 !important;
		border-style: dashed !important;
		border-color: #ae1f20 !important;
		cursor: default;
	}

	/* Section Styles */
	.section-card {
		border: 2px solid #e5e7eb;
		border-radius: 8px;
		margin-bottom: 20px;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
	}

	.section-header {
		background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
		padding: 12px 20px;
		border-bottom: 2px solid #e5e7eb;
		font-weight: 700;
		font-size: 14px;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.section-header.additions {
		background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
		border-bottom: 2px solid #22c55e;
		color: #15803d;
	}

	.section-header.deductions {
		background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
		border-bottom: 2px solid #ef4444;
		color: #b91c1c;
	}

	.section-body {
		padding: 20px;
	}

	.calculation-summary {
		background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
		color: white;
		padding: 20px;
		border-radius: 8px;
		margin-top: 20px;
	}

	.summary-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 8px 0;
		border-bottom: 1px solid rgba(255, 255, 255, 0.1);
	}

	.summary-item:last-child {
		border-bottom: none;
		font-size: 18px;
		font-weight: 700;
		color: #fbbf24;
	}
</style>

<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="return validateForm();">
	<div class="card">
		<div class="card-header text-center" style="background-color:#ae1f20;">
			<h4 class="card-title mb-0 fw-semibold text-white text-uppercase">
				Add Applicable Taxes on Bill

			</h4>
		</div>
		<?php echo $msg; ?>

		<div class="card-body">
			<!-- Basic Information Section -->
			<div class="section-card">
				<div class="section-header">
					📋 Basic Information
				</div>
				<div class="section-body">
					<div class="row">
						<div class="col-md-3 ">
							<div class="form-group">
								<label>विभाग</label><br>
								<select class="form-control" name="department" id="department"
									tabindex="<?php echo $tab++; ?>"
									onChange="fill_district(this.value), fill_sub_department(this.value)" readonly>
									<option value="">--- Select ---</option>
									<?php
									if (!empty($_SESSION['department'])) {
										$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in (' . implode(",", $_SESSION['department']) . ') group by department_id) ';
									} elseif (!empty($_SESSION['divisions'])) {
										$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in (' . implode(",", $_SESSION['divisions']) . ') group by division_id ) ';
									}
									$run = mysqli_query($db, $query);
									while ($data = mysqli_fetch_array($run)) {
										echo '<option value="' . $data['sno'] . '" ';
										if (isset($_POST['department'])) {
											if ($_POST['department'] == $data['sno']) {
												echo ' selected="Selected"';
											}
										}
										echo '>' . trim($data['department_name_hindi']) . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>उप विभाग</label>
								<select class="form-control" name="sub_department_id" id="sub_department_id"
									tabindex="<?php echo $tab++; ?>" readonly>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>आच्छादित जनपद</label>
								<select class="form-control" name="district" id="district"
									tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value)" readonly>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>परियोजना का नाम</label><br>
								<select class="form-control" name="project_name" id="project_name"
									tabindex="<?php echo $tab++; ?>" onChange="fill_division(this.value)" readonly>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Order No</label>
								<input type="text" name="order_no" id="order_no" class="form-control" placeholder=""
									value="<?php echo $_POST['order_no']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Voucher No</label>
								<input type="text" name="voucher_no" id="voucher_no" class="form-control"
									placeholder="FT20240001" value="<?php echo $_POST['voucher_no'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Transfer Date</label>
								<input type="date" name="transafer_date" id="transafer_date" class="form-control"
									placeholder="" value="<?php echo $_POST['transafer_date']; ?>"
									tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Bill No.</label>
								<input type="text" name="bill_no" id="bill_no" class="form-control" placeholder=""
									value="<?php echo $_POST['bill_no']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Bill Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('bill_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['bill_date']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Bill Amount</label>
								<input type="text" name="transafer_amount" id="transafer_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['transafer_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" oninput="findcalculation()">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Vendor Name</label>
								<div class="input-group">
									<select class="form-control" name="vendor_name" id="vendor_name"
										tabindex="<?php echo $tab++; ?>" onchange="updateVendorId()">
										<option value="">--- Select Vendor ---</option>
										<?php
										$query = 'SELECT * FROM vendor ORDER BY firm_name ASC';
										$run = mysqli_query($db, $query);
										while ($vendor = mysqli_fetch_array($run)) {
											echo '<option value="' . $vendor['sno'] . '" ';
											if (isset($_POST['vendor_id']) && $_POST['vendor_id'] == $vendor['sno']) {
												echo ' selected="selected"';
											}
											echo '>' . trim($vendor['firm_name']) . ' (' . trim($vendor['contractor_name']) . ')</option>';
										}
										?>
									</select>
									<div class="input-group-append">
										<button type="button" class="btn btn-primary" onclick="addNewVendor()"
											title="Add New Vendor">
											<i class="fas fa-plus"></i>
										</button>
									</div>
								</div>
								<input type="hidden" name="vendor_id" id="vendor_id"
									value="<?php echo $_POST['vendor_id']; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Remark </label>
								<textarea type="text" name="remark" id="remark" class="form-control" placeholder=""
									tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['remark']; ?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Additions Section -->
			<div class="section-card">
				<div class="section-header additions">
					➕ ADDITIONS (जोड़े जाने वाले राशि)
				</div>
				<div class="section-body">
					<div class="row">
						<div class="col-md-1">
							<div class="form-group">
								<label>GST%</label>
								<select class="form-control" name="gst_per" id="gst_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="10">10%</option>
									<option value="18">18%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>GST Amount</label>
								<input type="text" name="gstdeduction" id="gstdeduction" class="form-control"
									placeholder="" value="<?php echo $_POST['gstdeduction']; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>CGST Amount</label>
								<input type="text" name="cgst_amount" id="cgst_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['cgst_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>SGST Amount</label>
								<input type="text" name="sgst_amount" id="sgst_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['sgst_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Total Amount</label>
								<input type="text" name="total_withgst" id="total_withgst" class="form-control"
									placeholder="" value="<?php echo $_POST['total_withgst'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Other Additions</label>
								<input type="text" name="other_additions" id="other_additions" class="form-control"
									placeholder="Other additions" value="<?php echo $_POST['other_additions'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>" oninput="findcalculation()">
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Deductions Section -->
			<div class="section-card">
				<div class="section-header deductions">
					➖ DEDUCTIONS (जो काटे जाने वाले राशि)
				</div>
				<div class="section-body">
					<div class="row">
						<div class="col-md-1">
							<div class="form-group">
								<label>Inc. Tax % </label>
								<select class="form-control" name="it_per" id="it_per" tabindex="<?php echo $tab++; ?>"
									onchange="findcalculation()">
									<option value=""></option>
									<option value="2">2%</option>
									<option value="10">10%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Income Tax Amount </label>
								<input type="text" name="incometax" id="incometax" class="form-control" placeholder=""
									value="<?php echo $_POST['incometax']; ?>" tabindex="<?php echo $tab++; ?>"
									readonly>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<label>GST TDS% </label>
								<select class="form-control" name="gsttdspercentage" id="gsttdspercentage"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="2">2%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>GST TDS Amount</label>
								<input type="text" name="gsttds" id="gsttds" class="form-control" placeholder=""
									value="<?php echo $_POST['gsttds']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<label>Security % </label>
								<select class="form-control" name="security_per" id="security_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="1">1%</option>
									<option value="2">2%</option>
									<option value="3">3%</option>
									<option value="4">4%</option>
									<option value="5">5%</option>
									<option value="6">6%</option>
									<option value="7">7%</option>
									<option value="8">8%</option>
									<option value="9">9%</option>
									<option value="10">10%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Security Amount </label>
								<input type="text" name="security" id="security" class="form-control" placeholder=""
									value="<?php echo $_POST['security']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Royalty Amount </label>
								<input type="text" name="royalty" id="royalty" class="form-control" placeholder=""
									value="<?php echo $_POST['royalty']; ?>" tabindex="<?php echo $tab++; ?>"
									oninput="findcalculation()">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-1">
							<div class="form-group">
								<label>labour cess%</label>
								<select class="form-control" name="leborses_per" id="leborses_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="1">1%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>labour cess</label>
								<input type="text" name="leborses" id="leborses" class="form-control" placeholder=""
									value="<?php echo $_POST['leborses']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Other Title</label>
								<select name="other_title" id="other_title" class="form-control"
									tabindex="<?php echo $tab++; ?>">
									<option></option>
									<?php
									$sql = 'select * from fund_transfar_other_title';
									$row_type = execute_query($sql);
									while ($type_details = mysqli_fetch_array($row_type)) {
										echo '<option value="' . $type_details['sno'] . '"';

										echo '>' . $type_details['title_name'] . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<label>Oth%/Amt. </label>
								<select class="form-control" name="other_per" id="other_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="amt">AMT.</option>
									<option value="2">2%</option>
								</select>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<label>Add/Ded. </label>
								<select class="form-control" name="other_add_ded" id="other_add_ded"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="Add">Add</option>
									<option value="Deducted">Deducted</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Other Amount</label>
								<input type="text" name="other_amount" id="other_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['other_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" onblur="findcalculation()">
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Calculation Summary Section -->
			<div class="calculation-summary">
				<h5 class="text-center mb-3">📊 CALCULATION SUMMARY</h5>
				<div class="row">
					<div class="col-md-6">
						<div class="summary-item">
							<span>Bill Amount:</span>
							<span>₹<span id="summary_bill_amount">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>Total Additions:</span>
							<span style="color: #22c55e;">₹<span id="summary_additions">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>Total Deductions:</span>
							<span style="color: #ef4444;">₹<span id="summary_deductions">0.00</span></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="summary-item">
							<span>Total Expenditure:</span>
							<span>₹<span id="summary_total_expen">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>NET Payment:</span>
							<span style="color: #fbbf24;">₹<span id="summary_net_payment">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>Total with GST:</span>
							<span>₹<span id="summary_total_withgst">0.00</span></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Final Results Section -->
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label>Total Expendeture</label>
						<input type="text" name="total_expen" id="total_expen" class="form-control" placeholder=""
							value="<?php echo $_POST['total_expen']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label>NET Payment</label>
						<input type="text" name="praposemoney" id="praposemoney" class="form-control" placeholder=""
							value="<?php echo $_POST['praposemoney']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
					</div>
				</div>
			</div>

			<hr>
			<div class="row">
				<div class="col-md-4 ">
					<div class="form-group">
						<label>From Account </label>
						<select class="form-control" name="from_account_no" id="from_account_no"
							tabindex="<?php echo $tab++; ?>" required>
							<option value="">--- Select ---</option>
							<?php
							$query = 'select * from billit_customer where parent ="1" order by cus_name';
							$run = mysqli_query($db, $query);
							while ($data = mysqli_fetch_array($run)) {
								echo '<option value="' . $data['sno'] . '" ';
								if (isset($_POST['from_account_no'])) {
									if ($_POST['from_account_no'] == $data['sno']) {
										echo ' selected="Selected"';
									}
								}
								echo '>' . trim($data['cus_name']) . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<input type="hidden" name="unit_id" id="unit_id" class="form-control" placeholder=""
							value="<?php echo $_POST['unit_id']; ?>" tabindex="<?php echo $tab++; ?>"
							onblur="fill_bank_details(this.value, '<?php echo $_POST['to_bank_name']; ?>')">
					</div>
				</div>
				<div class="col-md-4" id="bank_unit">
					<div class="form-group">
						<label for="">To Account</label>
						<input type="text" class="form-control" name="to_bank_name" id="bank_name_unit"
							placeholder="Enter account name"
							value="<?php echo htmlspecialchars($_POST['to_bank_name'] ?? ''); ?>"
							tabindex="<?php echo $tab++; ?>">
					</div>
				</div>
			</div></br>
			<div class="row">
				<div class="col-md-12" align="center">
					<div class="form-group">
						<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
						<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

<script>
	// Validate form before submission
	function validateForm() {
		// Run calculation one more time to ensure all values are up to date
		findcalculation();

		// Get required fields
		const transferAmount = parseFloat(document.getElementById('transafer_amount').value) || 0;
		const netPayment = document.getElementById('praposemoney').value;
		const fromAccount = document.getElementById('from_account_no').value;
		const toAccount = document.getElementById('bank_name_unit').value;

		// Validations
		if (transferAmount <= 0) {
			alert('Please enter a valid Transfer Amount');
			document.getElementById('transafer_amount').focus();
			return false;
		}

		if (!fromAccount) {
			alert('Please select From Account');
			document.getElementById('from_account_no').focus();
			return false;
		}

		if (!toAccount) {
			alert('Please enter To Account');
			document.getElementById('bank_name_unit').focus();
			return false;
		}

		if (!netPayment || netPayment === '0' || netPayment === '') {
			alert('Net Payment calculation issue. Please check the amounts.');
			return false;
		}

		return true;
	}

	//calculation of fund transfer
	function findcalculation() {

		let transafer_amount = parseFloat(document.getElementById("transafer_amount").value);
		transafer_amount = parseFloat(transafer_amount);
		if (!transafer_amount) {
			transafer_amount = 0;
		}

		let gst_per = parseFloat(document.getElementById("gst_per").value);
		gst_per = parseFloat(gst_per);
		if (!gst_per) {
			gst_per = 0;
		}

		let gstdeduction = parseFloat(document.getElementById("gstdeduction").value);
		gstdeduction = parseFloat(gstdeduction);
		if (!gstdeduction) {
			gstdeduction = 0;
		}

		let gsttdspercentage = parseFloat(document.getElementById("gsttdspercentage").value);
		gsttdspercentage = parseFloat(gsttdspercentage);
		if (!gsttdspercentage) {
			gsttdspercentage = 0;
		}

		let gsttds = parseFloat(document.getElementById("gsttds").value);
		gsttds = parseFloat(gsttds);
		if (!gsttds) {
			gsttds = 0;
		}

		let it_per = parseFloat(document.getElementById("it_per").value);
		it_per = parseFloat(it_per);
		if (!it_per) {
			it_per = 0;
		}

		let leborses = parseFloat(document.getElementById("leborses").value);
		if (!leborses) {
			leborses = 0;
		}

		let leborses_per = parseFloat(document.getElementById("leborses_per").value);
		leborses_per = parseFloat(leborses_per);
		if (!leborses_per) {
			leborses_per = 0;
		}

		let incometax = parseFloat(document.getElementById("incometax").value);
		incometax = parseFloat(incometax);
		if (!incometax) {
			incometax = 0;
		}

		let praposemoney = parseFloat(document.getElementById("praposemoney").value);
		praposemoney = parseFloat(praposemoney);
		if (!praposemoney) {
			praposemoney = 0;
		}

		let security_per = parseFloat(document.getElementById("security_per").value);
		security_per = parseFloat(security_per);
		if (!security_per) {
			security_per = 0;
		}
		let otherAmount = parseFloat(document.getElementById("other_amount").value);
		otherAmount = parseFloat(otherAmount);
		if (!otherAmount) {
			otherAmount = 0;
		}

		let royalty = parseFloat(document.getElementById("royalty").value);
		royalty = parseFloat(royalty);
		if (!royalty) {
			royalty = 0;
		}

		let other_additions = parseFloat(document.getElementById("other_additions").value);
		other_additions = parseFloat(other_additions);
		if (!other_additions) {
			other_additions = 0;
		}

		let total_withgst = parseFloat(document.getElementById("total_withgst").value);
		total_withgst = parseFloat(total_withgst);
		if (!total_withgst) {
			total_withgst = 0;
		}

		// gst calculation
		var gstdeductionres = ((transafer_amount * gst_per) / 100).toFixed();
		document.getElementById('gstdeduction').value = gstdeductionres;

		// Split GST into CGST and SGST (50% each)
		var cgst_amount = (gstdeductionres / 2).toFixed();
		var sgst_amount = (gstdeductionres / 2).toFixed();
		document.getElementById('cgst_amount').value = cgst_amount;
		document.getElementById('sgst_amount').value = sgst_amount;

		// security calculation
		var securityres = ((transafer_amount * security_per) / 100).toFixed();
		document.getElementById('security').value = securityres;

		//gst tds % 
		var gsttdsres = ((transafer_amount * gsttdspercentage) / 100).toFixed();
		document.getElementById('gsttds').value = gsttdsres;

		var leborsesres = ((transafer_amount * leborses_per) / 100).toFixed();
		document.getElementById('leborses').value = leborsesres;

		//income
		var incometaxres = ((transafer_amount * it_per) / 100).toFixed();
		document.getElementById('incometax').value = incometaxres;

		var otherPer = document.getElementById('other_per').value;
		var otherAmountInput = document.getElementById('other_amount');
		if (otherPer === 'amt') {
			// Enable manual entry
			otherAmountInput.readOnly = false;
		} else {
			// Auto-calculate percentage of transferAmount
			var percent = parseFloat(otherPer);
			var calculatedAmount = (transafer_amount * percent) / 100;
			otherAmountInput.value = calculatedAmount.toFixed();
			otherAmountInput.readOnly = true;
		}

		var abcd = parseFloat(royalty) + parseFloat(incometaxres) + parseFloat(securityres) + parseFloat(gsttdsres) + parseFloat(leborsesres);
		abcd = abcd.toFixed(2);

		var total_withgst_amt = parseFloat(transafer_amount) + parseFloat(gstdeductionres);
		total_withgst_amt = total_withgst_amt.toFixed();
		document.getElementById('total_withgst').value = total_withgst_amt;

		// NET Payment calculation
		var type = document.getElementById('other_add_ded').value;
		if (type === 'Add') {
			var prakhand_ko_preshit_amount = (transafer_amount - abcd + otherAmount).toFixed();
		} else if (type === 'Deducted') {
			var prakhand_ko_preshit_amount = (transafer_amount - abcd - otherAmount).toFixed();
		} else {
			var prakhand_ko_preshit_amount = (transafer_amount - abcd).toFixed();
		}

		document.getElementById('praposemoney').value = prakhand_ko_preshit_amount;

		// Total expenditure calculation (without centage and contingency)
		var total_expenses = (
			(parseFloat(transafer_amount) || 0) +
			(parseFloat(gstdeductionres) || 0) +
			(parseFloat(other_additions) || 0)
		);
		document.getElementById('total_expen').value = parseFloat(total_expenses).toFixed();

		// Update Summary Section
		updateSummary(transafer_amount, gstdeductionres, other_additions, abcd, otherAmount, type, total_expenses, prakhand_ko_preshit_amount, total_withgst_amt);
	}

	// Update Summary Section
	function updateSummary(billAmount, gstAmount, otherAdditions, totalDeductions, otherAmount, otherType, totalExpenses, netPayment, totalWithGst) {
		document.getElementById('summary_bill_amount').textContent = parseFloat(billAmount || 0).toFixed(2);

		// Total Additions
		var totalAdditions = parseFloat(gstAmount || 0) + parseFloat(otherAdditions || 0);
		document.getElementById('summary_additions').textContent = totalAdditions.toFixed(2);

		// Total Deductions
		var totalDed = parseFloat(totalDeductions || 0);
		if (otherType === 'Add') {
			// Other amount is added, not deducted
		} else if (otherType === 'Deducted') {
			totalDed += parseFloat(otherAmount || 0);
		}
		document.getElementById('summary_deductions').textContent = totalDed.toFixed(2);

		document.getElementById('summary_total_expen').textContent = parseFloat(totalExpenses || 0).toFixed(2);
		document.getElementById('summary_net_payment').textContent = parseFloat(netPayment || 0).toFixed(2);
		document.getElementById('summary_total_withgst').textContent = parseFloat(totalWithGst || 0).toFixed(2);
	}

</script>

<?php
page_footer_start();
?>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>


<!--  Charts Plugin -->
<script src="js/chartist.min.js"></script>

<?php
page_footer_end();
?>

<script>
	var actionUrl = 'scripts/ajax.php';

	function fill_sub_department(val, selected) {
		var data = { "term": "b", "id": "sub_dep", "val": val };

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (ajaxdata) {
				// console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.sub_department_hindi + '</option>';

				});
				$("#sub_department_id").html(txt);
			}
		});
	}

	function fill_district(val, selected) {
		var data = { "term": "b", "id": "dist", "val": val };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.district_name + '</option>';

				});
				$("#district").html(txt);
			}
		});
	}

	function fill_project(val, selected) {
		var data = { "term": "b", "id": "proj", "val": val, "dept": $("#department").val() };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.project_name_hindi + '</option>';

				});
				$("#project_name").html(txt);
			}
		});
	}


	function fill_division(val) {
		var data = { "term": "b", "id": "proj_div", "val": val, "dept": $("#department").val() };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$("#unit_id").val(data.division_id);
				fill_bank_details(data.division_id)
			}

		});
	}

	function fill_bank_details(val, selected) {
		var data = { "term": "b", "id": "unit_bank", "val": val };
		var vendorSelect = document.getElementById('vendor_name');
		var isVendorSelected = vendorSelect && vendorSelect.value !== '';

		if (!isVendorSelected) {
			$("#bank_name_unit").html('<option value="">--Select--</option>');
		}

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (ajaxdata) {
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (value.id == selected) {
						txt += ' selected="selected" ';
					}
					txt += '>' + value.cus_name + '</option>';

				});

				if (!isVendorSelected) {
					$("#bank_name_unit").html(txt);
				}

				// Auto-sync: To Account ki selected value ko From Account mein set karo
				var toVal = selected ? selected : (ajaxdata.length > 0 ? ajaxdata[0].id : '');
				if (toVal && $("#from_account_no option[value='" + toVal + "']").length) {
					$("#from_account_no").val(toVal);
				} else if (!selected && ajaxdata.length > 0 && !isVendorSelected) {
					// Agar selected nahi tha to pehla option auto-select karo
					$("#bank_name_unit").val(ajaxdata[0].id);
					if ($("#from_account_no option[value='" + ajaxdata[0].id + "']").length) {
						$("#from_account_no").val(ajaxdata[0].id);
					}
				}

				if (isVendorSelected) {
					updateVendorId();
				}
			}
		});
	}

	// To Account (bank_name_unit) change hone par From Account bhi auto-update hoga
	$(document).on('change', '#bank_name_unit', function () {
		var toVal = $(this).val();
		if (toVal && $("#from_account_no option[value='" + toVal + "']").length) {
			$("#from_account_no").val(toVal);
		}
	});

	// Update vendor ID when vendor is selected
	function updateVendorId() {
		var vendorSelect = document.getElementById('vendor_name');
		var selectedVendor = vendorSelect.value;
		document.getElementById('vendor_id').value = selectedVendor;

		if (selectedVendor !== '') {
			var vendorText = vendorSelect.options[vendorSelect.selectedIndex].text;
			document.getElementById('bank_name_unit').value = vendorText;
		} else {
			document.getElementById('bank_name_unit').value = '';
		}
	}

	// Add new vendor function
	function addNewVendor() {
		var firmName = prompt("Enter Firm Name:");
		if (!firmName) return;

		var contractorName = prompt("Enter Contractor Name:");
		if (!contractorName) return;

		// AJAX call to add new vendor
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: {
				"term": "add_vendor",
				"firm_name": firmName,
				"contractor_name": contractorName
			},
			success: function (response) {
				var result = JSON.parse(response);
				if (result.success) {
					// Add new option to vendor dropdown
					var vendorText = firmName + ' (' + contractorName + ')';
					var newOption = '<option value="' + result.vendor_id + '" selected="selected">' + vendorText + '</option>';
					$('#vendor_name').append(newOption);
					$('#vendor_id').val(result.vendor_id);

					// Update To Account field
					$('#bank_name_unit').val(vendorText);

					alert('Vendor added successfully!');
				} else {
					alert('Error adding vendor: ' + result.message);
				}
			},
			error: function () {
				alert('Error adding vendor. Please try again.');
			}
		});
	}

	$(document).ready(function () {
		$('#general_stat_table').DataTable({

		});

		// Initialize To Account with vendor name if a vendor is pre-selected
		if ($('#vendor_name').val() !== '') {
			updateVendorId();
		}

		<?php if (isset($_GET['edit_sno']) && $_POST['to_bank_name'] != ''): ?>
			// Populate bank details on page load for edit
			var unit_id = '<?php echo $_POST['unit_id']; ?>';
			var selected_bank = '<?php echo $_POST['to_bank_name']; ?>';
			if (unit_id != '') {
				fill_bank_details(unit_id, selected_bank);
			}
		<?php endif; ?>
	});


	<?php
	if (isset($_GET['edit_sno'])) {
		?>
		$(document).ready(function () {
			fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
			fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
			fill_project(<?php echo $_POST['district']; ?>, <?php echo $_POST['project_name']; ?>);


		});
		<?php
	}
	?>


	<?php
	if (isset($_GET['id'])) {
		?>
		$(document).ready(function () {
			fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
			fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
			fill_project(<?php echo $_POST['district']; ?>, <?php echo $_POST['project_name']; ?>);
			fill_division(<?php echo $_POST['project_name']; ?>, <?php echo $_POST['unit_id']; ?>);

		});
		<?php
	}
	?>
</script>