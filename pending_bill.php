<?php
include("scripts/settings.php");
include("scripts/alerts.php");

if (isset($_POST['fetch_erp_action']) && $_POST['fetch_erp_action'] == 'fetch_project_by_erp_tender') {
	$code = mysqli_real_escape_string($db, $_POST['code']);
	$data = array('status' => 'error', 'message' => 'Not found');

	// 1. Search by ERP Code in uprnss_project_temp
	$sql = "SELECT department_id, sub_department_id, district_id, sno FROM uprnss_project_temp WHERE erp_code = '$code' LIMIT 1";
	$result = execute_query($sql);

	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$data = array(
			'status' => 'success',
			'department_id' => $row['department_id'],
			'sub_department_id' => $row['sub_department_id'],
			'district_id' => $row['district_id'],
			'project_id' => $row['sno']
		);
	} else {
		// 2. Search by Tender Number in tender_allotment
		$sql_tender = "SELECT project_id FROM tender_allotment WHERE tender_no = '$code' AND status != '5' LIMIT 1";
		$result_tender = execute_query($sql_tender);

		if (mysqli_num_rows($result_tender) > 0) {
			$tender_row = mysqli_fetch_assoc($result_tender);
			$project_id = $tender_row['project_id'];

			// Fetch project details
			$sql_proj = "SELECT department_id, sub_department_id, district_id, sno FROM uprnss_project_temp WHERE sno = '$project_id' LIMIT 1";
			$result_proj = execute_query($sql_proj);

			if (mysqli_num_rows($result_proj) > 0) {
				$row = mysqli_fetch_assoc($result_proj);
				$data = array(
					'status' => 'success',
					'department_id' => $row['department_id'],
					'sub_department_id' => $row['sub_department_id'],
					'district_id' => $row['district_id'],
					'project_id' => $row['sno']
				);
			}
		}
	}
	echo json_encode($data);
	exit;
}

$msg = '';
$tab = 1;


if (isset($_POST['submit'])) {
	$department = mysqli_real_escape_string($db, $_POST['department']);
	$sub_department_id = mysqli_real_escape_string($db, $_POST['sub_department_id']);
	$district = mysqli_real_escape_string($db, $_POST['district']);
	$project_name = mysqli_real_escape_string($db, $_POST['project_name']);

	if (empty($department) || empty($district) || empty($project_name)) {
		$msg = 'Please select विभाग, जनपद, and परियोजना का नाम.';
	} else {
		// Build list of row suffixes to process: base ('') and dynamic ('_1', '_2', ...)
		$suffixes = [];
		// Base row
		if (!empty($_POST['bill_no']) && !empty($_POST['transafer_amount'])) {
			$suffixes[] = '';
		}
		// Dynamic rows discovered by bill_no_#
		foreach ($_POST as $key => $val) {
			if (preg_match('/^bill_no_(\d+)$/', $key, $m)) {
				$suf = '_' . $m[1];
				$bn = trim($_POST['bill_no' . $suf] ?? '');
				$amt = trim($_POST['transafer_amount' . $suf] ?? '');
				if ($bn !== '' && $amt !== '') {
					$suffixes[] = $suf;
				}
			}
		}

		if (empty($suffixes)) {
			$msg = 'Please enter at least one Bill No. and Amount.';
		} else {
			// Prepare upload directory once
			$upload_dir = 'uploads/bill/' . $project_name . '/';
			if (!file_exists($upload_dir)) {
				mkdir($upload_dir, 0777, true);
			}

			$success_count = 0;
			$errors = [];

			foreach ($suffixes as $suf) {
				$bill_no = mysqli_real_escape_string($db, $_POST['bill_no' . $suf]);
				$bill_date = mysqli_real_escape_string($db, $_POST['bill_date' . $suf] ?? $_POST['bill_date']);
				$transafer_amount = mysqli_real_escape_string($db, $_POST['transafer_amount' . $suf]);
				$unit_remark = mysqli_real_escape_string($db, $_POST['unit_remark' . $suf] ?? '');

				$photo1 = '';
				$photo2 = '';

				// Handle Photo1 for this row
				$photo1_key = 'photo1' . $suf;
				if (isset($_FILES[$photo1_key]) && isset($_FILES[$photo1_key]['error']) && $_FILES[$photo1_key]['error'] == 0) {
					$photo1_info = pathinfo($_FILES[$photo1_key]['name']);
					$photo1_ext = strtolower($photo1_info['extension'] ?? '');
					if ($_FILES[$photo1_key]['size'] <= 2 * 1024 * 1024) {
						$mime_type = mime_content_type($_FILES[$photo1_key]['tmp_name']);
						$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
						if (in_array($mime_type, $allowed_types)) {
							$photo1 = $upload_dir . 'photo1_' . time() . '_' . uniqid() . '.' . $photo1_ext;
							move_uploaded_file($_FILES[$photo1_key]['tmp_name'], $photo1);
						} else {
							$errors[] = 'Invalid file type for Photo / PDF 1 (Bill ' . htmlspecialchars($bill_no) . ').';
						}
					} else {
						$errors[] = 'Photo 1 must be less than 2MB (Bill ' . htmlspecialchars($bill_no) . ').';
					}
				}

				// Handle Photo2 for this row
				$photo2_key = 'photo2' . $suf;
				if (isset($_FILES[$photo2_key]) && isset($_FILES[$photo2_key]['error']) && $_FILES[$photo2_key]['error'] == 0) {
					$photo2_info = pathinfo($_FILES[$photo2_key]['name']);
					$photo2_ext = strtolower($photo2_info['extension'] ?? '');
					if (in_array($photo2_ext, ['jpg', 'jpeg', 'png', 'pdf']) && $_FILES[$photo2_key]['size'] <= 2 * 1024 * 1024) {
						$photo2 = $upload_dir . 'photo2_' . time() . '_' . uniqid() . '.' . $photo2_ext;
						move_uploaded_file($_FILES[$photo2_key]['tmp_name'], $photo2);
					} else {
						$errors[] = 'Photo / PDF 2 must be JPG/PNG/PDF and less than 2MB (Bill ' . htmlspecialchars($bill_no) . ').';
					}
				}

				// Insert row
				$insert_sql = "INSERT INTO invoice_account_fund_transafer 
                (department, sub_department_id, district, project_name, bill_no, bill_date, transafer_amount, unit_remark, photo1, photo2, status, request_status) 
                VALUES 
                ('$department', '$sub_department_id', '$district', '$project_name', '$bill_no', '$bill_date', '$transafer_amount', '$unit_remark', '$photo1', '$photo2', '0', '0')";

				if (mysqli_query($db, $insert_sql)) {
					$success_count++;
				} else {
					$errors[] = 'DB Error for Bill ' . htmlspecialchars($bill_no) . ': ' . mysqli_error($db);
				}
			}

			if ($success_count > 0) {
				$msg = $success_count . ' bill(s) inserted successfully.';
			}
			if (!empty($errors)) {
				$msg .= implode('<br>', $errors);
			}
		}
	}
} else {
	$_POST['bill_date'] = date("Y-m-d");
	$_POST['bill_no'] = '';
	$_POST['unit_remark'] = '';
	$_POST['transafer_amount'] = '';
}



if (!isset($_POST['search'])) {
	$_POST['edit_sno'] = '';
	$_POST['department'] = '';
	$_POST['district'] = '';
	$_POST['division_name'] = '';
	$_POST['project_name'] = '';
	$_POST['project_name_hindi'] = '';

	$_POST['project_name_hindi_unicode'] = '';
	$_POST['project_type'] = '';
	$_POST['work_start_date'] = '';
	$_POST['date_from'] = '';
	$_POST['date_to'] = '';
	$_POST['date_type'] = '';
	$_POST['sanction_cost'] = '';
	$_POST['type'] = '';
	$_POST['type1'] = '';
	$_POST['tot_exp_project'] = '';
	$_POST['closing_date'] = '';
	$_POST['closing_remark'] = '';
}

if (isset($_GET['new'])) {
	$page = 2;
} else {
	$page = 1;
}
if (isset($_GET['view'])) {
	$page = 3;
}
if (isset($_GET['cid'])) {
	$sql = 'select * from uprnss_project_temp where sno="' . $_GET['cid'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));

	$_POST['cid_sno'] = $data['sno'];
	$_POST['head_project_name'] = $data['new_project_id'];
	$_POST['department'] = $data['department_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['division_name'] = $data['division_id'];
	$_POST['project_name'] = $data['project_name_hindi'];
	$_POST['project_type'] = $data['project_type'];
	$_POST['project_name_hindi'] = '';
	$_POST['project_name_hindi_unicode'] = $data['project_name_hindi'];
}

if (isset($_GET['delid'])) {

	$sql4 = 'DELETE FROM invoice_account_fund_transafer WHERE sno="' . $_GET['delid'] . '"';
	if (execute_query($sql4)) {

		$msg .= 'Bill Deleted.>';
	} else {
		$msg .= 'Failed to delete Bill.';
	}
}



page_header_start();

?>
<script src="js/krutidev.js"></script>
<script src="js/unicode_keyboard.js"></script>
<style>
	#project_name_hindi {
		font-family: 'Kruti Dev 010';
		font-size: 20px;
	}

	textarea {
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
	}

	.no-border {
		border: 0 !important;
		box-shadow: none !important;
	}

	.card-like {
		background: #fff;
		border: 1px solid #e0e0e0;
		border-radius: 6px;
		padding: 15px;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
		margin-bottom: 15px;
	}

	.bill-inner {
		border: 2px solid #000;
		border-radius: 6px;
		padding: 15px;
		margin: 0;
	}

	.bill-inner label {
		font-size: 12px;
		margin-bottom: 4px;
	}

	.bill-inner .form-group {
		margin-bottom: 14px;
	}

	.bill-inner .compact [class^='col-'] {
		padding-left: 10px;
		padding-right: 10px;
	}
</style>

<?php
page_header_end();
page_sidebar();

?>

<?php
//running project page

switch ($page) {
	case "1":
		?>

		<div id="container" class="no-print">
			<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
				action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
				<div class="card card-body">
					<div class="d-flex justify-content-between align-items-right mb-3">
						<a href="pending_bill.php?new=new" class="btn btn-success shadow-sm">
							<i class="fa fa-plus-circle"></i> Add New Bill
						</a>
						<a href="pending_bill.php?view=view" style="color: #ffffff"><button type="button" name="student_ledger"
								class="btn btn-danger">Click Here for Paid bill List</button></a>
					</div>
					<?php
					if ($msg != '') {
						echo '<h5>' . alert($msg) . '</h5>';
					}
					?>
					<div class="row d-flex my-auto">

						<table width="100%" class="table table-striped table-hover rounded">
							<tr>
								<th>विभाग </th>
								<th width="18%">
									<select class="form-control" name="department" id="department"
										tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_department_name";
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
								</th>
								<th>प्रखण्ड </th>
								<th width="18%"><select class="form-control" name="division_name" id="division_name"
										tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = 'select * from uprnss_division order by division_name ASC';
										$run = mysqli_query($db, $query);
										while ($data = mysqli_fetch_array($run)) {
											echo '<option value="' . $data['s_no'] . '" ';
											if (isset($_POST['division_name'])) {
												if ($_POST['division_name'] == $data['s_no']) {
													echo ' selected="Selected"';
												}
											}
											echo '>' . $data['division_name'] . '</option>';
										}
										?>
									</select></th>
								<th>आच्छादित जनपद</th>
								<th width="18%">
									<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_district";
										$run = mysqli_query($db, $query);
										while ($data = mysqli_fetch_array($run)) {
											echo '<option value="' . $data['sno'] . '" ';
											if (isset($_POST['district'])) {
												if ($_POST['district'] == $data['sno']) {
													echo ' selected="Selected"';
												}
											}
											echo '>' . $data['district_name_hindi'] . '</option>';
										}
										?>
									</select>
								</th>
								<th>परियोजना का स्तर</th>
								<th width="18%">
									<select name="project_type" id="project_type" class="form-control"
										onchange="onchangetype()">
										<option value="">--- Select ---</option>
										<option value="1" <?php echo ($_POST['project_type'] == 1 ? 'selected' : ''); ?>>शासन स्तर
										</option>
										<option value="2" <?php echo ($_POST['project_type'] == 2 ? 'selected' : ''); ?>>जिला स्तर
										</option>
									</select>
								</th>
							</tr>
						</table>
						<div class="col-md-12 text-center">
							<button type="submit" name="search" class="btn btn-primary">Search</button>
							<input type="hidden" id="id" name="id" value="1">
							<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
						</div>
					</div>
				</div>
			</form>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header bg-danger text-white mt-2">
						<h4 class="card-title text-center text-white ">Pending For Payment bill list</h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-hover table-bordered" id="">
							<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th>S.No.</th>
									<th>Project Type</th>
									<th>Department Name</th>
									<th>District Name</th>
									<th>Project Name</th>
									<th>Bill Number </th>
									<th>Bill Date</th>
									<th>Bill Amount</th>
									<th>Action</th>
									<th>Delete</th>

								</tr>
								<tr>
									<?php
									for ($i = 1; $i <= 10; $i++) {
										echo '<th>' . $i . '</th>';
									}
									?>
								</tr>
							</thead>

							<tbody>
								<?php
								if (isset($_POST['search'])) {
									// if($_SESSION['usertype']=='11' || $_SESSION['usertype']=='3' || $_SESSION['usertype']=='5' || $_SESSION['usertype']=='4'){
									/////////////////////// for unit All  user type  /////////////////////////
									$sql = 'SELECT 
										invoice.sno,
										invoice.bill_no,
										invoice.bill_date,
										invoice.transafer_amount,
										invoice.gstdeduction,
										invoice.totelmgst,
										invoice.gsttds,
										invoice.leborses,
										invoice.incometax,
										invoice.praposemoney,
										invoice.unit_remark,
										invoice.status,

										project.project_name_hindi,
										project.project_type,
										project.admin_go_no,

										dept.department_name_hindi,
										subdept.sub_department_hindi,
										dist.district_name_hindi

									FROM invoice_account_fund_transafer AS invoice

									LEFT JOIN uprnss_project_temp AS project ON project.sno = invoice.project_name
									LEFT JOIN uprnss_department_name AS dept ON dept.sno = invoice.department
									LEFT JOIN uprnss_sub_department AS subdept ON subdept.sno = invoice.sub_department_id
									LEFT JOIN uprnss_district AS dist ON dist.sno = invoice.district


									WHERE invoice.genrate_note_sheet = 0 
									AND project.division_id in (' . implode(",", $_SESSION['divisions']) . ')
									';
									if (isset($_POST['search'])) {
										if ($_POST['department'] != '') {
											$sql .= ' AND invoice.department = "' . $_POST['department'] . '"';
										}
										if ($_POST['district'] != '') {
											$sql .= ' AND invoice.district = "' . $_POST['district'] . '"';
										}
										if ($_POST['project_type'] != '') {
											$sql .= ' AND project.project_type = "' . $_POST['project_type'] . '"';
										}
										if ($_POST['division_name'] != '') {
											$sql .= ' AND project.division_id = "' . $_POST['division_name'] . '"';
										}
									}
									$sql .= ' ORDER BY invoice.bill_date DESC';
									$result = execute_query($sql);
									$i = 1;
									while ($row = mysqli_fetch_assoc($result)) {
										echo '<tr>';
										echo '<td>' . $i++ . '</td>';
										echo '<td>';
										if ($row['project_type'] == '1')
											echo 'शासन स्तर';
										elseif ($row['project_type'] == '2')
											echo 'जिला स्तर';
										echo '</td>';
										echo '<td>' . $row['department_name_hindi'] . '<br><small>' . $row['sub_department_hindi'] . '</small></td>';
										echo '<td>' . $row['district_name_hindi'] . '</td>';

										echo '<td>' . $row['project_name_hindi'] . '<br><small>' . $row['admin_go_no'] . '</small></td>';
										echo '<td>' . $row['bill_no'] . '</td>';
										echo '<td>' . date('d-m-Y', strtotime($row['bill_date'])) . '</td>';
										echo '<td>₹' . number_format($row['transafer_amount'], 2) . '</td>';
										echo '</td>
										<td class="no-print text-center">';

										echo '<a target="_blank" href="fund_transfer.php?id=' . $row['sno'] . '" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Proceed For Payment" ></span></a><br/><br/>';

										echo '</td>';
										echo '</td>
										<td class="no-print text-center">';

										echo '<a target="_blank" href="pending_bill.php?delid=' . $row['sno'] . '" onClick="return confirm(\'Are you sure you?\');"><span class="fas fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete" ></span></a><br/><br/>';

										echo '</td>';
										echo '</tr>';
									}
									// }
						
								}
								?>
							</tbody>
						</table>

					</div>
				</div>
			</div>
		</div>

		<?php
		//////////////////////////////////// New Bill Form///////////////////////////////////////////
		break;
	case "2":
		?>

		<div id="container" class="no-print">
			<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
				action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title"></h4>
					</div>
					<?php
					if ($msg != '') {
						echo '<h5>' . alert($msg) . '</h5>';
					}
					?>
					<!---<div class="text-right">
						<a href="billit_payment_report.php?view=view"><button type="button" class="btn btn-warning">View Vouchers (Payment Report)</button></a>
					</div>---->
					<div class="card-body">
						<h5>Add Bill Without Any Deduction</h5>
						<div class="row"
							style="background:#f9f9f9; padding:10px; border-radius:5px; margin-bottom:15px; border:1px solid #ddd;">
							<div class="col-md-4">
								<div class="form-group">
									<label>Enter ERP Code / Tender No.</label>
									<input type="text" id="erp_tender_code" class="form-control"
										placeholder="ERP Code or Tender No">
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>&nbsp;</label><br>
									<button type="button" class="btn btn-info btn-fill" onclick="fetchProjectDetails()">Fetch
										Detail</button>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3 ">
								<div class="form-group">
									<label>विभाग</label><br>
									<select class="form-control" name="department" id="department"
										tabindex="<?php echo $tab++; ?>"
										onChange="fill_sub_department(this.value); fill_district(this.value);">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_department_name";
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
										value="<?php echo $_POST['sub_department_id']; ?>" tabindex="<?php echo $tab++; ?>">
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>आच्छादित जनपद</label>
									<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>"
										onChange="fill_project(this.value)">
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>परियोजना का नाम</label><br>
									<select class="form-control" name="project_name" id="project_name"
										tabindex="<?php echo $tab++; ?>" onChange="fill_division(this.value)">
									</select>
								</div>
							</div>
							<div class="card-like">
								<button type="button" id="addBillRow" class="btn btn-primary"
									style="padding:8px 16px; font-size:14px; margin-left:90%;">
									<i class="fa fa-plus"></i> Add
								</button>
								<div class="row bill-inner">
									<div class="col-md-12 text-right" style="margin-bottom:8px;">
										<!-- <button type="button" id="addBillRow" class="btn btn-primary" style="padding:8px 16px; font-size:14px;">
								<i class="fa fa-plus"></i> Add
							</button> -->
									</div>
									<div class="row compact" style="width:100%; margin:0;">
										<div class="col-md-3">
											<div class="form-group">
												<label>Bill No.</label>
												<input type="text" name="bill_no" id="bill_no" class="form-control"
													placeholder="" value="<?php echo $_POST['bill_no']; ?>"
													tabindex="<?php echo $tab++; ?>">
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>Bill Date</label>
												<input type="date" name="bill_date" id="bill_date" class="form-control"
													value="<?php echo htmlspecialchars($_POST['bill_date']); ?>">
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>Bill Amount(Without Any Deduction)</label>
												<input type="text" name="transafer_amount" id="transafer_amount"
													class="form-control" placeholder=""
													value="<?php echo $_POST['transafer_amount']; ?>"
													tabindex="<?php echo $tab++; ?>" oninput="findcalculation()">
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>Remark</label>
												<textarea type="text" name="unit_remark" id="unit_remark" class="form-control"
													placeholder=""
													tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['unit_remark']; ?> </textarea>
											</div>
										</div>
									</div>
									<div class="row compact" style="width:100%; margin:0;">
										<div class="col-md-6">
											<div class="form-group">
												<label>Upload Photo / PDF 1 *</label>
												<input type="file" name="photo1" id="photo1" class="form-control"
													accept="image/*,application/pdf" onchange="validateAndPreview(this, 'preview1')" required>
												<img id="preview1" src="#" alt="Preview Image 1"
													style="display:none; width:100px; margin-top:10px; border:1px solid #ccc;" />
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label>Upload Photo / PDF 2</label>
												<input type="file" name="photo2" id="photo2" class="form-control"
													accept="image/*,application/pdf" onchange="validateAndPreview(this, 'preview2')">
												<img id="preview2" src="#" alt="Preview Image 2"
													style="display:none; width:100px; margin-top:10px; border:1px solid #ccc;" />
											</div>
										</div>
									</div>
								</div>
							</div>
							<div id="additional_bill_blocks"></div>
						</div>
						<div class="row">
							<div class="col-md-12" align="center">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Genrate
										Bill</button>
									<input type="hidden" id="edit_sno" name="edit_sno"
										value="<?php echo $_POST['edit_sno']; ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<script>
			function validateAndPreview(input, previewId) {
				var file = input.files[0];
				var preview = document.getElementById(previewId);

				if (file) {
					// Validate file size (max 2MB)
					if (file.size > 2 * 1024 * 1024) {
						alert("File size should not exceed 2MB.");
						input.value = ""; // Clear the file input
						preview.style.display = "none";
						return;
					}

					// Validate file type (only image and pdf)
					var validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
					if (!validTypes.includes(file.type)) {
						alert("Only JPG, PNG images and PDF files are allowed.");
						input.value = "";
						preview.style.display = "none";
						return;
					}

					// Preview the image
					if (file.type === 'application/pdf') {
						preview.src = "#";
						preview.style.display = "none";
					} else {
						var reader = new FileReader();
						reader.onload = function (e) {
							preview.src = e.target.result;
							preview.style.display = "block";
						}
						reader.readAsDataURL(file);
					}
				} else {
					preview.src = "#";
					preview.style.display = "none";
				}
			}
		</script>


		<?php
		// close project list page
		break;
	case "3":
		?>
		<div id="container" class="no-print">
			<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
				action="pending_bill.php?view=view" onSubmit="">
				<div class="card card-body">
					<div class="row d-flex my-auto">
						<div class="col-md-12 text-right"><a href="pending_bill.php" style="color: #ffffff"><button
									type="button" name="student_ledger" class="btn btn-danger">Pending For Payment bill
									list</button></a></div>
						<table width="100%" class="table table-striped table-hover rounded">
							<tr>
								<th>विभाग </th>
								<th width="18%">
									<select class="form-control" name="department" id="department"
										tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_department_name";
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
								</th>
								<th>प्रखण्ड </th>
								<th width="18%"><select class="form-control" name="division_name" id="division_name"
										tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = 'select * from uprnss_division order by division_name ASC';
										$run = mysqli_query($db, $query);
										while ($data = mysqli_fetch_array($run)) {
											echo '<option value="' . $data['s_no'] . '" ';
											if (isset($_POST['division_name'])) {
												if ($_POST['division_name'] == $data['s_no']) {
													echo ' selected="Selected"';
												}
											}
											echo '>' . $data['division_name'] . '</option>';
										}
										?>
									</select></th>
								<th>आच्छादित जनपद</th>
								<th width="18%">
									<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_district";
										$run = mysqli_query($db, $query);
										while ($data = mysqli_fetch_array($run)) {
											echo '<option value="' . $data['sno'] . '" ';
											if (isset($_POST['district'])) {
												if ($_POST['district'] == $data['sno']) {
													echo ' selected="Selected"';
												}
											}
											echo '>' . $data['district_name_hindi'] . '</option>';
										}
										?>
									</select>
								</th>
							</tr>
						</table>
						<div class="col-md-12 text-center">
							<button type="submit" name="search" class="btn btn-primary">Search</button>
						</div>
					</div>
				</div>
			</form>
		</div>

		<form name="test" action="bill_note_sheet.php" method="POST" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header bg-danger text-white mt-2">
							<h5 class="card-title text-center text-white ">Note Sheet Genrate Bill</h5></br>
						</div>
						<div class="card-body">
							<div class="col-md-12 text-center">
								<button formtarget="_blank" type="submit" name="note_sheet"
									class="btn btn-primary text-right">Print Note Sheet</button>
							</div>
							<table class="table table-striped table-hover table-bordered" id="">
								<thead style="position:sticky;top:0; z-index:2;">
									<tr>
										<th></th>
										<th>S.No.</th>
										<th>Project Type</th>
										<th>Department Name</th>
										<th>District Name</th>
										<th>Project Name</th>
										<th>Bill Number </th>
										<th>Bill Date</th>
										<th>Bill Amount</th>

									</tr>
									<tr>
										<?php
										for ($i = 1; $i <= 9; $i++) {
											echo '<th>' . $i . '</th>';
										}
										?>
									</tr>
								</thead>

								<tbody>
									<?php
									if (isset($_POST['search'])) {
										// if($_SESSION['usertype']=='11' || $_SESSION['usertype']=='1' || $_SESSION['usertype']=='5' || $_SESSION['usertype']=='4'){
										/////////////////////// for unit All  user type  /////////////////////////
										$sql = 'SELECT 
										invoice.sno,
										invoice.bill_no,
										invoice.bill_date,
										invoice.transafer_amount,
										invoice.gstdeduction,
										invoice.totelmgst,
										invoice.gsttds,
										invoice.leborses,
										invoice.incometax,
										invoice.praposemoney,
										invoice.unit_remark,
										invoice.status,

										project.sno as project_id,
										project.project_name_hindi,
										project.project_type,
										project.admin_go_no,

										dept.department_name_hindi,
										subdept.sub_department_hindi,
										dist.district_name_hindi

									FROM invoice_account_fund_transafer AS invoice

									LEFT JOIN uprnss_project_temp AS project ON project.sno = invoice.project_name
									LEFT JOIN uprnss_department_name AS dept ON dept.sno = invoice.department
									LEFT JOIN uprnss_sub_department AS subdept ON subdept.sno = invoice.sub_department_id
									LEFT JOIN uprnss_district AS dist ON dist.sno = invoice.district


									WHERE invoice.status = 1
									AND project.division_id in (' . implode(",", $_SESSION['divisions']) . ')
									';
										if (isset($_POST['search'])) {
											if ($_POST['department'] != '') {
												$sql .= ' AND invoice.department = "' . $_POST['department'] . '"';
											}
											if ($_POST['district'] != '') {
												$sql .= ' AND invoice.district = "' . $_POST['district'] . '"';
											}
											if ($_POST['division_name'] != '') {
												$sql .= ' AND project.division_id = "' . $_POST['division_name'] . '"';
											}
										}
										$sql .= ' ORDER BY project_id,  invoice.bill_date DESC';
										$result = execute_query($sql);
										$i = 1;
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<tr><td><input type="checkbox" name="print_sno_' . $row['sno'] . '" id="date_of_acceptance_revised_cost" class="form-control" value="' . $row['sno'] . '" tabindex=""></td>';
											echo '<td>' . $i++ . '</td>';
											echo '<td>';
											if ($row['project_type'] == '1')
												echo 'शासन स्तर';
											elseif ($row['project_type'] == '2')
												echo 'जिला स्तर';
											echo '</td>';
											echo '<td>' . $row['department_name_hindi'] . '<br><small>' . $row['sub_department_hindi'] . '</small></td>';
											echo '<td>' . $row['district_name_hindi'] . '</td>';

											echo '<td>' . $row['project_name_hindi'] . '<br><small>' . $row['admin_go_no'] . '</small></td>';
											echo '<td>' . $row['bill_no'] . '</td>';
											echo '<td>' . date('d-m-Y', strtotime($row['bill_date'])) . '</td>';
											echo '<td>₹' . number_format($row['transafer_amount'], 2) . '</td>';
											echo '</td>';
											echo '</tr>';
										}
										// }
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<?php

			break;

}

?>




	<?php
	page_footer_start();
	?>

	<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
	<script>

		$('select[multiple]').multiselect({
			search: true
		});

		$(document).ready(function () {
			/*$('#general_stat_table').DataTable({
				paging: false,
				fixedHeader: true,
				colReorder: true
				});
			});	*/


			var t = $('#general_stat_table').DataTable({
				paging: false
			});


		});

	</script>


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
					console.log(ajaxdata);
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
					makeSearchable('sub_department_id');
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
					makeSearchable('district');
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
					makeSearchable('project_name');
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
			$("#bank_name_unit").html('<option value="">--Select--</option>');
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
					$("#bank_name_unit").html(txt);
					makeSearchable('bank_name_unit');
				}
			});
		}

		function makeSearchable(selectId) {
			if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
				console.error('Select2 or jQuery not loaded');
				return;
			}
			var $select = $('#' + selectId);
			if (!$select.length) return;

			if ($select.data('select2')) $select.select2('destroy');
			$select.select2({
				theme: 'bootstrap4',
				width: '100%',
				placeholder: '--Select--',
				allowClear: true
			});
		}

		$(function () {
			['department', 'division_name', 'district', 'project_type', 'sub_department_id', 'project_name'].forEach(makeSearchable);
		});

		$(document).ready(function () {
			$('#general_stat_table').DataTable({

			});
			// Cascading: after विभाग, reset उप विभाग, आच्छादित जनपद, परियोजना का नाम
			$('#department').on('change', function () {
				$('#sub_department_id').html('<option value="">--Select--</option>');
				$('#district').html('<option value="">--Select--</option>');
				$('#project_name').html('<option value="">--Select--</option>');
			});
			// Cascading: after उप विभाग, reload आच्छादित जनपद
			$('#sub_department_id').on('change', function () {
				var deptId = $('#department').val();
				$('#district').html('<option value="">--Select--</option>');
				$('#project_name').html('<option value="">--Select--</option>');
				if (deptId) {
					fill_district(deptId);
				}
			});

			// Cascading: after आच्छादित जनपद, load परियोजना का नाम
			$('#district').on('change', function () {
				var distId = $(this).val();
				$('#project_name').html('<option value="">--Select--</option>');
				if (distId) {
					fill_project(distId);
				}
			});
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
	</script>

	<script>
			// Dynamic add rows for the horizontal bill block
			(function () {
				var counter = 1; // start from 1; base row is un-indexed for backward compatibility
				var addBtn = document.getElementById('addBillRow');
				if (!addBtn) { return; }

				addBtn.addEventListener('click', function () {
					var container = document.getElementById('additional_bill_blocks');
					if (!container) { return; }

					var block = document.createElement('div');
					block.className = 'card-like';
					var row = document.createElement('div');
					row.className = 'row bill-inner';
					row.style.cssText = 'margin-top:0;';

					var html = '' +
						'<div class="row compact" style="width:100%; margin:0;">' +
						'<div class="col-md-3">' +
						'<div class="form-group">' +
						'<label>Bill No.</label>' +
						'<input type="text" name="bill_no_' + counter + '" id="bill_no_' + counter + '" class="form-control" placeholder="">' +
						'</div>' +
						'</div>' +
						'<div class="col-md-3">' +
						'<div class="form-group">' +
						'<label>Bill Date</label>' +
						'<input type="date" name="bill_date_' + counter + '" id="bill_date_' + counter + '" class="form-control">' +
						'</div>' +
						'</div>' +
						'<div class="col-md-3">' +
						'<div class="form-group">' +
						'<label>Bill Amount(Without Any Deduction)</label>' +
						'<input type="text" name="transafer_amount_' + counter + '" id="transafer_amount_' + counter + '" class="form-control" placeholder="">' +
						'</div>' +
						'</div>' +
						'<div class="col-md-3">' +
						'<div class="form-group">' +
						'<label>Remark</label>' +
						'<textarea name="unit_remark_' + counter + '" id="unit_remark_' + counter + '" class="form-control" placeholder=""></textarea>' +
						'</div>' +
						'</div>' +
						'<div class="row compact" style="width:100%; margin:0;">' +
						'<div class="col-md-6">' +
						'<div class="form-group">' +
						'<label>Upload Photo / PDF 1 *</label>' +
						'<input type="file" name="photo1_' + counter + '" id="photo1_' + counter + '" class="form-control" accept="image/*,application/pdf" onchange="validateAndPreview(this, \'preview1_' + counter + '\')">' +
						'<img id="preview1_' + counter + '" src="#" alt="Preview Image 1" style="display:none; width:100px; margin-top:10px; border:1px solid #ccc;"/>' +
						'</div>' +
						'</div>' +
						'<div class="col-md-6">' +
						'<div class="form-group">' +
						'<label>Upload Photo / PDF 2</label>' +
						'<input type="file" name="photo2_' + counter + '" id="photo2_' + counter + '" class="form-control" accept="image/*,application/pdf" onchange="validateAndPreview(this, \'preview2_' + counter + '\')">' +
						'<img id="preview2_' + counter + '" src="#" alt="Preview Image 2" style="display:none; width:100px; margin-top:10px; border:1px solid #ccc;"/>' +
						'</div>' +
						'</div>' +
						'<div class="col-md-12 text-right" style="margin-top:10px;">' +
						'<button type="button" class="btn btn-danger btn-sm removeRow" style="padding:4px 10px;">Remove</button>' +
						'</div>';

					row.innerHTML = html;
					block.appendChild(row);
					container.appendChild(block);

					block.querySelector('.removeRow').addEventListener('click', function () {
						container.removeChild(block);
					});

					counter++;
				});
			})();
	</script>

	<script>
		// Universal preview handler for base and dynamic rows
		(function () {
			$(document).on('change', 'input[type=file][id^=photo1], input[type=file][id^=photo2]', function () {
				var input = this;
				var file = input.files && input.files[0];
				if (!file) { return; }
				// Derive preview id by replacing photo1_X -> preview1_X, photo2_X -> preview2_X
				var previewId = input.id.replace('photo', 'preview');
				var preview = document.getElementById(previewId);
				if (!preview) { return; }
				if (file.size > 2 * 1024 * 1024) {
					alert('File size should not exceed 2MB.');
					input.value = '';
					preview.style.display = 'none';
					return;
				}
				var validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
				if (validTypes.indexOf(file.type) === -1) {
					alert('Only JPG, PNG images and PDF files are allowed.');
					input.value = '';
					preview.style.display = 'none';
					return;
				}
				
				if (file.type === 'application/pdf') {
					preview.src = '#';
					preview.style.display = 'none';
				} else {
					var reader = new FileReader();
					reader.onload = function (e) {
						preview.src = e.target.result;
						preview.style.display = 'block';
					};
					reader.readAsDataURL(file);
				}
			});
		})();
		function fetchProjectDetails() {
			var code = document.getElementById('erp_tender_code').value.trim();
			if (code === "") {
				alert("Please enter ERP Code or Tender Number");
				return;
			}

			$.ajax({
				type: "POST",
				url: "pending_bill.php",
				data: { fetch_erp_action: "fetch_project_by_erp_tender", code: code },
				dataType: "json",
				success: function (response) {
					if (response.status === 'success') {
						// 1. Set Department
						$('#department').val(response.department_id).trigger('change');

						// 2. Fetch and set Sub Department & District (dependent on Department)
						// We need to pass the selected values so they are selected after options load
						fill_sub_department(response.department_id, response.sub_department_id);
						fill_district(response.department_id, response.district_id);

						// 3. Fetch and set Project (dependent on District)
						// Note: fill_project uses $("#department").val() and the 'val' arg is District ID
						// Since AJAX for fill_district might take time, and fill_project only needs district ID and dept ID (which is already set), 
						// we can call it immediately.

						// However, fill_project updates #project_name options.
						// We pass response.project_id as selected.
						setTimeout(function () {
							fill_project(response.district_id, response.project_id);
						}, 500);

						// 4. Fill division (dependent on Project) -- actually fill_division is called onchange of project
						// But we should call it to set hidden unit_id or similar if needed.
						// The form doesn't seem to have a 'division' dropdown in this 'Case 2' block, but it has 'fill_division' available.
						// Let's check if fill_division updates any UI visible here. 
						// In Case 2, I don't see division dropdown, but fill_division updates #unit_id and calls fill_bank_details.

						// We can trigger it once project is likely set.
						setTimeout(function () {
							fill_division(response.project_id);
						}, 1000);

					} else {
						alert("Details not found for the given code.");
					}
				},
				error: function () {
					alert("Error fetching details.");
				}
			});
		}
	</script>