<?php
include ("scripts/settings.php");

$msg = '';
$tab = 1;

if (isset($_POST['search'])) {
	$_SESSION['post_custom_report'] = $_POST;
}
/* ====== AJAX: return files list for an invoice ====== */
if(isset($_GET['ajax']) && $_GET['ajax']==='files' && isset($_GET['invoice_id'])){
    $invoice_id = (int)$_GET['invoice_id'];
    // $q = 'SELECT sno, file_type, upload_file, remark 
          // FROM transaction_project_file 
          // WHERE invoice_id='.$invoice_id.' 
          // ORDER BY sno ASC';
		  
			$q = "
			SELECT 
				t.sno,
				t.file_type,
				t.upload_file,
				t.remark,
				i.sno AS invoice_id,
				i.project_id,
				i.upload_date
			FROM transaction_project_file t
			JOIN invoice_project_file i 
				ON i.sno = t.invoice_id
			WHERE i.project_id = $invoice_id
			ORDER BY i.sno, t.sno ASC
			";

    $rs = execute_query($q);
    if(mysqli_error($db)){
        echo '<div class="alert alert-danger">Error: '.htmlspecialchars(mysqli_error($db)).'</div>';
        exit;
    }
    $rows = [];
    while($r = mysqli_fetch_assoc($rs)){ $rows[] = $r; }
    $count = count($rows);

    // echo '<div class="mb-2"><strong>Total files: '.$count.'</strong></div>';

    if($count===0){
        echo '<div class="alert alert-danger">No files uploaded for this Project.</div>';
        exit;
    }

    echo '<div class="row g-3">';
    foreach($rows as $r){
        $path = $r['upload_file'];
        $type = strtolower($r['file_type']);
        $remark = htmlspecialchars($r['remark']);
        echo '<div class="col-md-4"><div class="card h-100">';
        echo '<div class="card-body">';
        if($type==='image'){
            echo '<img src="'.htmlspecialchars($path).'" data-full="'.htmlspecialchars($path).'" class="img-fluid rounded mb-2 file-thumb" alt="image">';
        } elseif($type==='video'){
            echo '<video src="'.htmlspecialchars($path).'" class="w-100 rounded mb-2" controls></video>';
        } elseif($type==='pdf'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open PDF</a></p>';
        } elseif($type==='doc'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open Document</a></p>';
        } elseif($type==='excel'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open Excel</a></p>';
        } elseif($type==='ppt'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open PPT</a></p>';
        } else {
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open File</a></p>';
        }
        // Only remark (no id, no type)
        if($remark!==''){
            echo '<div class="mt-1"><small class="text-muted">'.$remark.'</small></div>';
        }
        echo '</div></div></div>';
    }
    echo '</div>';
    exit;
}
if (isset($_POST['submit'])) {
	if ($_POST['project_name'] == '') {
		$msg .= '<div class="alert alert-danger">Enter Project Name</div>';
	}
	if ($_POST['project_name_hindi_unicode'] == '') {
		$msg .= '<div class="alert alert-danger">Enter Project Name in Hindi</div>';
	}
	if ($msg == '') {
		if ($_POST['edit_sno'] == '') {
			$sql = 'insert into uprnss_project_temp (district_id, department_id, division_id, unit_id, project_name, project_name_hindi, master_freeze, created_by, creation_time) values ("' . $_POST['district'] . '", "' . $_POST['department'] . '", "' . $_POST['division_name'] . '", "' . $_SESSION['usersno'] . '", "' . $_POST['project_name'] . '", "' . $_POST['project_name_hindi_unicode'] . '","0", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
		} else {
			$sql = 'update uprnss_project_temp set
			district_id="' . $_POST['district'] . '", 
			department_id="' . $_POST['department'] . '", 
			division_id="' . $_POST['division_name'] . '", 
			project_type="' . $_POST['project_type'] . '", 
			unit_id="' . $_SESSION['usersno'] . '", 
			project_name="' . $_POST['project_name'] . '", 
			project_name_hindi="' . $_POST['project_name_hindi_unicode'] . '", 
			edited_by="' . $_SESSION['username'] . '", 
			edition_time="' . date("Y-m-d H:i:s") . '"		
			where sno="' . $_POST['edit_sno'] . '"';
		}
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {
			$msg .= '<p class="text text-success">Data Saved</p>';
			$_POST['edit_sno'] = '';
			$_POST['department'] = '';
			$_POST['district'] = '';
			$_POST['division_name'] = '';
			$_POST['project_name'] = '';
			$_POST['project_name_hindi'] = '';
			$_POST['project_name_hindi_unicode'] = '';
			$_POST['project_type'] = '';
			$_POST['type'] = '';
			$_POST['type1'] = '';
			$_POST['tot_exp_project'] = '';
		}
	}
} else {
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
		$_POST['type2'] = '';
		$_POST['type1'] = '';
		$_POST['tot_exp_project'] = '';
	}
}

if (isset($_GET['eid'])) {
	$sql = 'select * from uprnss_project_temp where sno="' . $_GET['eid'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['department'] = $data['department_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['division_name'] = $data['division_id'];
	$_POST['project_name'] = $data['project_name'];
	$_POST['project_name_hindi'] = '';
	$_POST['project_name_hindi_unicode'] = $data['project_name_hindi'];
}
if (isset($_GET['delid'])) {
	$sql = 'update uprnss_project_temp set status="5" where sno="' . $_GET['delid'] . '"';
	execute_query($sql);
	$msg .= '<div class="alert alert-warning">Data Delete</div>';
}

if (isset($_GET['freeze_sno'])) {
	$sql = 'update uprnss_project_temp set master_freeze="1" where sno="' . $_GET['freeze_sno'] . '"';
	execute_query($sql);
	$msg .= '<div class="alert alert-warning">Data Freezed</div>';
}

if (isset($_GET['del'])) {
	$sql = 'delete from uprnss_project_temp where sno="' . $_GET['del'] . '"';
	execute_query($sql);
	$msg .= '<p class="text text-danger">Data Deleted.</p>';
}

if (isset($_GET['act'])) {
	$sql = 'select * from uprnss_project_temp where sno="' . $_GET['act'] . '"';

	$product = mysqli_fetch_assoc(execute_query($sql));

	if ($product['status'] == '' or $product['status'] == '0') {
		$sql = 'update uprnss_project_temp set status=1 where sno=' . $_GET['act'];
		execute_query($sql);
	} else {
		$sql = 'update uprnss_project_temp set status=0 where sno=' . $_GET['act'];
		execute_query($sql);
	}
	if (mysqli_error($db)) {
		$msg .= '<div class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</div>';
	} else {
		$msg .= '<div class="alert alert-success">Data Saved</div>';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
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


	#project_name_hindi {
		font-family: 'Kruti Dev 010';
		font-size: 20px;
	}

	textarea {
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
	}

	table,
	th,
	td {
		border: 0.1px solid black !important;
		border-collapse: collapse !important;
	}
</style>
<style>
    textarea{ font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; }
    .modal-lg { max-width: 95% !important; }

    /* Make close (X) always visible on colored header */
    .modal-header .btn-close {
        filter: invert(1); /* force white icon */
        opacity: .9;
    }
    .modal-header .btn-close:hover { opacity: 1; }

    /* Thumbs and preview */
    .file-thumb { cursor: zoom-in; }
    .img-preview-full {
        max-width: 100%;
        max-height: 80vh;
        display: block;
        margin: 0 auto;
    }

    /* Icon-only buttons */
    .icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,.15);
        background: #fff;
    }
    .icon-btn:hover { background: #f6f6f6; }
    .icon-label {
        font-size: 12px;
        margin-left: 6px;
        color: #6c757d;
        vertical-align: middle;
    }
    .icon-stack { display:inline-flex; align-items:center; gap:6px; }
</style>
<?php
page_header_end();
page_sidebar();

?>
<style>
	/* Your CSS for printing */
	@media print {

		/* Resetting margins and padding to zero for consistent layout */
		table {
			margin: 0;
			padding: 0;
			border-collapse: collapse;
			table-layout: fixed;
			width: 297mm;
			/* Ensure table takes full width of page */
		}

		/* Set font size and family for better readability */
		table,
		th {
			font-size: 16px;
			/* Adjust font size as needed */
			font-family: Arial, sans-serif;
			/* Use a common sans-serif font */
		}

		/* Style table header */
		th {
			background-color: #f2f2f2;
			/* Light gray background for headers */
			border: 1px solid #dddddd;
			/* Gray border for headers */
			text-align: left;
			/* Align text to left within headers */
			padding: 8px;
			/* Add padding for readability */
			width: 10px;
			word-wrap: break-word;
		}

		/* Style table cells */
		td {
			border: 1px solid #dddddd;
			/* Gray border for cells */
			text-align: left;
			/* Align text to left within cells */
			padding: 8px;
			/* Add padding for readability */
			font-size: 12px;
			width: 10px;
			word-wrap: break-word;
		}

		/* Alternate row colors for better readability */
		tr:nth-child(even) {
			background-color: #f9f9f9;
			/* Light gray background for even rows */
		}

		/* Hide any elements not relevant for printing */
		.no-print {
			display: none;
		}
	}

	@page {
		size: Legal landscape;
	}
</style>

<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	<div id="container" class="no-print">
		<div class="card card-body">
			<div class="row d-flex my-auto">
				<table width="100%" class="table table-striped table-hover rounded">
					<tr>
						<th width="16%">Date Type</th>
						<th width="18%"><select name="date_type" id="date_type" class="form-control">
								<option value="">--- Select ---</option>
								<option value="sanction_date" <?php echo ($_POST['date_type'] == 'sanction_date' ? ' selected="selected"' : ''); ?>>Sanction Date</option>
								<option value="land_receive_date" <?php echo ($_POST['date_type'] == 'land_receive_date' ? ' selected="selected"' : ''); ?>>Land Receive Date</option>
								<option value="work_start_date" <?php echo ($_POST['date_type'] == 'work_start_date' ? ' selected="selected"' : ''); ?>>Work Start Date</option>
								<option value="work_completion_date" <?php echo ($_POST['date_type'] == 'work_completion_date' ? ' selected="selected"' : ''); ?>>Work
									Completion Date</option>
								<option value="technical_sanction_date" <?php echo ($_POST['date_type'] == 'technical_sanction_date' ? ' selected="selected"' : ''); ?>>
									Technical Sanction Date</option>
								<option value="admin_go_date" <?php echo ($_POST['date_type'] == 'admin_go_date' ? ' selected="selected"' : ''); ?>>Administrative GO Date</option>
								<option value="financial_go_date" <?php echo ($_POST['date_type'] == 'financial_go_date' ? ' selected="selected"' : ''); ?>>Financial GO Date</option>

							</select>
						</th>
						<th width="15%">Form</th>
						<th width="18%"><input type="date" name="date_from" id="date_from"
								value="<?php echo $_POST['date_from']; ?>" class="form-control"></th>
						<th width="15%">To</th>
						<th width="18%"><input type="date" name="date_to" id="date_to"
								value="<?php echo $_POST['date_to']; ?>" class="form-control"></th>
					</tr>
					<tr>
						<th>विभाग </th>
						<th width="18%">
							<select class="form-control" name="department" id="department"
								tabindex="<?php echo $tab++; ?>"
								onChange="fill_sub_department(this.value), fill_scheme(this.value)">
								<option value="">--- Select ---</option>
								<?php
								if ($_SESSION['usertype'] == 'sadmin' || $_SESSION['usertype'] == '1') {
									$query = 'select * from uprnss_department_name';
								} else {
									$query = 'select * from uprnss_department_name where sno in (' . implode(",", $_SESSION['department']) . ') ';
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
					<tr>
						<th>Project Type</th>
						<th width="18%">
							<select name="project_type" id="project_type" class="form-control"
								onchange="onchangetype()">
								<option value="">--- Select ---</option>
								<option value="1" <?php echo ($_POST['project_type'] == 1 ? 'selected' : ''); ?>>Ho Level
								</option>
								<option value="2" <?php echo ($_POST['project_type'] == 2 ? 'selected' : ''); ?>>Division
									Level</option>
							</select>
						</th>
						<th>Project Status</th>
						<th>
							<select class="form-control" name="project_status_1" id="project_status_1"
								tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = "select * from master_projoect_status_1";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['project_status_1'])) {
										if ($_POST['project_status_1'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . trim($data['status_1']) . '</option>';
								}
								?>
							</select>
						</th>
						<th width="10%">Running/Close</th>
						<th width="18%">
							<select name="project_running_status" id="project_running_status" class="form-control">
								<option value="0" <?php echo ($_POST['project_running_status'] == 0 ? 'selected' : ''); ?>>Running Project</option>
								<option value="1" <?php echo ($_POST['project_running_status'] == 1 ? 'selected' : ''); ?>>Close Project </option>
							</select>
						</th>
						<!--<th>टेंडर की स्थिति</th>
						<th>
							<select class="form-control" name="tender_status" id="tender_status"
								tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = "select * from master_tender_status";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['tender_status'])) {
										if ($_POST['tender_status'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . trim($data['status']) . '</option>';
								}
								?>
							</select>
						</th>-->
					</tr>
				</table>
				<table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">

					<tr>

						<th width="18%">मूल परियोजना लागत ( लाख में )</th>
						<th width="6%">
							<select name="type" id="type" class="form-control" onchange="onchangetype()">

								<option value="=" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>=</option>
								<option value=">" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>></option>
								<option value="<" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>
									< </option>
								<option value="<=" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>
									<= </option>
								<option value=">=" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>>=</option>
							</select>
						</th>
						<th width="12%" style="margin:0px; padding:0px;">
							<input type="text" name="sanction_cost" id="sanction_cost" class="form-control"
								placeholder="" value="<?php echo $_POST['sanction_cost']; ?>"
								tabindex="<?php echo $tab++; ?>">
						</th>
						<th width="6%">
							<select name="type2" id="type" class="form-control" onchange="onchangetype()">

								<option value="=" <?php echo ($_POST['type2'] == 1 ? 'selected' : ''); ?>>=</option>
								<option value=">" <?php echo ($_POST['type2'] == 1 ? 'selected' : ''); ?>>></option>
								<option value="<" <?php echo ($_POST['type2'] == 1 ? 'selected' : ''); ?>>
									< </option>
								<option value="<=" <?php echo ($_POST['type2'] == 1 ? 'selected' : ''); ?>>
									<= </option>
								<option value=">=" <?php echo ($_POST['type2'] == 1 ? 'selected' : ''); ?>>>=</option>
							</select>
						</th>
						<th width="12%" style="margin:0px; padding:0px;">
							<input type="text" name="sanction_cost1" id="sanction_cost" class="form-control"
								placeholder="" value="<?php echo $_POST['sanction_cost']; ?>"
								tabindex="<?php echo $tab++; ?>">
						</th>
						
						<th width="18%">परियोजना में अब तक का कुल व्यय ( लाख में )</th>
						<th width="6%">
							<select name="type1" id="type1" class="form-control" onchange="onchangetype()">

								<option value="=" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>=</option>
								<option value=">" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>></option>
								<option value="<" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>
									< </option>
								<option value="<=" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>
									<= </option>
								<option value=">=" <?php echo ($_POST['type'] == 1 ? 'selected' : ''); ?>>>=</option>S
							</select>
						</th>
						<th width="12%" style="margin:0px; padding:0px;">
							<input type="text" name="tot_exp_project" id="tot_exp_project" class="form-control"
								placeholder="" value="<?php echo $_POST['tot_exp_project']; ?>"
								tabindex="<?php echo $tab++; ?>">

						</th>
					</tr>
					
				</table>
				<?php $select_array = array(
					"chk_project_type" => array("परियोजना का प्रकार", "1"),
					"chk_division_name" => array("यूनिट का नाम ", "1"),
					"chk_district_name_hindi" => array("जनपद", "1"),
					"chk_department_name_hindi" => array("विभाग", "1"),
					"chk_project_name_hindi" => array("परियोजना का नाम (हिन्दी )", "1"),
					"chk_sacntion_cost" => array("मूल परियोजना लागत ( लाख में ) ", "1"),
					"chk_revised_date" => array("पुनरीक्षित लागत की स्वीकृति का दिनांक", "1"),
					"chk_revised_cost" => array("पुनरीक्षित लागत ( लाख में )", "1"),
					"chk_land_receive_date" => array("भूमि प्राप्त की तिथि", "1"),
					"chk_work_start_date" => array("कार्य प्रारंभ तिथि", "1"),
					"chk_work_completion_date" => array("कार्य पूर्ण तिथि", "1"),
					"chk_admin_go_no" => array("प्रशासनिक शासनादेश संख्या", "1"),
					"chk_admin_go_date" => array("प्रशासनिक शासनादेश तिथि", "1"),
					"chk_financial_go_no" => array("वित्तीय शासनादेश संख्या तिथ धनराशि ( लाख में )", "1"),
					"chk_technical_sacntion_date" => array("तकनीकी स्वीकृति  दिनांक/तकनीकी स्वीकृति क्रमांक", "1"),
					"chk_tender_status" => array("टेंडर की स्थिति", "1"),
					"chk_payable_gst" => array("योजना पर देय जी . एस. टी .", "1"),
					"chk_architect_name" => array("आर्किटेक्ट", "1"),
					"chk_structural_architect" => array("स्ट्राक्चरल आर्किटेक्ट", "1"),
					"chk_soli_testing" => array("मृदा परीक्षण / Survey", "1"),
					"chk_estimation_status" => array("आगणन गठन की स्थिति", "1"),
					"chk_je_name" => array("परियोजना  पर तैनात  अवर  अभियंता का नाम", "1"),
					"chk_ae_name" => array("परियोजना  पर तैनात  सहायक  अभियंता का नाम", "1"),
					"chk_excn_name" => array("परियोजना  के  अधिशासी  अभियंता का नाम कब से/कब तक", "1"),
					"chk_se_name" => array("परियोजना  के  अधीक्षण  अभियंता का नाम कब से/कब तक", "1"),
					"chk_revised_amount" => array("पुनरीक्षित आगण की लागत ( लाख में )", "1"),
					"chk_revision_reason" => array("पुनरीक्षित आगणन का  कारण", "1"),
					"chk_revision_date" => array("पुनरीक्षित आगण की तिथि", "1"),
					"chk_revision_sent_status" => array("पुनरीक्षित प्रेषण की स्थिति /धनराशि ( लाख में )", "1"),
					"chk_revision_sent_date" => array("पुनरीक्षित प्रेषण की तिथि", "1"),

					"chk_total_received_amount_date" => array("कुल प्राप्त धनराशि व दिनांक", "2"),
					"chk_total_received_amount" => array("कुल प्राप्त धनराशि", "1"),

					"chk_expense_current_fy" => array("गत वित्तीय वर्ष में कुल व्यय", "1"),
					"chk_expense_current_fy_month" => array("वर्तमान वित्तीय वर्ष में गत माह तक व्यय ( लाख में )", "1"),
					"chk_expense_current_month" => array("वर्तमान माह में व्यय ( लाख में )", "1"),
					"chk_expense_current_fy_total" => array("वर्तमान वित्तीय वर्ष में कुल व्यय ( लाख में )", "1"),
					"chk_expense_project" => array("परियोजना में अब तक का कुल व्यय ( लाख में )", "1"),
					"chk_balance_amount" => array("शेष धनराशि परियोजना लागत के सापेक्ष ( लाख में )", "1"),
					"chk_balance_amount_refer" => array("परियोजना पर शेष धनराशि अवमुक्त के सापेक्ष ( लाख में )", "1"),

					"chk_percent_avmukt" => array("वित्तीय प्रगति %(अवमुक्त के सापेक्ष)", "1"),
					"chk_percent_physical_progress" => array("भौतिक प्रगति", "2"),
					"chk_percent_physical_progress_per" => array("भौतिक प्रगति प्रतिशत में", "1"),

					"chk_project_status_1" => array("परियोजना की स्थिति ", "1"),
					"chk_remarks" => array("अभियुक्ति", "1"),
					"chk_third_evaluation" => array("तृतीय पक्ष मूल्यांकन", "1"),
					"chk_project_image" => array("परियोजना फ़ोटोज़", "1"),
					"chk_last_update" => array("Last Update", "1")
				);
				?>



				<table class="table table-border table-striped">
					<?php
					$i = 1;
					echo '<tr>';
					echo '<td><span><span>For All chek/Unchek<input  class="big" type="checkbox" name="all_check" onchange="checkAll(this)"  style="width: 20px; height: 20px;"></td>';
					//print_r($_POST);
					foreach ($select_array as $k => $v) {
						if ($i % 10 == 0) {
							echo '</tr><tr>';
						}
						echo '<td class="small" colspan="' . $v[1] . '"><input type="checkbox" name="' . $k . '"';
						if ($_POST['date_type'] != '') {
							if (isset($_POST[$k])) {
								echo ' checked="checked" ';
							}
						} else {
							if (isset($_POST[$k])) {
								echo ' checked="checked" ';
							}
						}
						echo '>' . $v[0] . '</td>';
						$i++;
					}
					echo '</tr>';
					?>
				</table>

				<script>
					function checkAll(source) {
						var checkboxes = document.querySelectorAll('input[type="checkbox"]');
						checkboxes.forEach(function (checkbox) {
							if (checkbox != source && checkbox.name != 'all_check') {
								checkbox.checked = source.checked;
							}
						});
					}
				</script>

				<button type="submit" name="search"
					class="btn btn-primary">Search</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="report_custom_export.php" style="color: #ffffff"><button type="button" name="student_ledger"
						class="btn btn-danger">Download In Excel</button></a>
				<input type="hidden" id="id" name="id" value="1">
				<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">

			</div>
		</div>
	</div>

</form>


<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h4 class="card-title text-center"></h4></br>
			</div>
			<div class="card-body">
				<table class="table table-striped table-hover table-bordered" id="printonly">
					<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							<th>क्र० स०</th>
							<?php
							foreach ($select_array as $k => $v) {
								if (isset($_POST[$k])) {
									echo '<th colspan="' . $v[1] . '">' . $v[0] . '</th>';
								}
							}
							?>


							<th class="no-print text-center">view</th>
						</tr>
						<tr>
							<th>1</th>
							<?php
							$i = 2;
							foreach ($select_array as $k => $v) {
								if (isset($_POST[$k])) {
									echo '<th>' . $i++ . '</th>';
								}
							}
							?>
						</tr>

					</thead>

					<tbody>

						<?php
						$i = 1;
						//if($_SESSION['usertype']=='sadmin'){
						$sql = 'select uprnss_project_temp.sno as sno, division_name, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, land_receive_date, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date,
							financial_go_amount,
							technical_sanction_date,
							technical_sanction_no,
							tender_status,
							project_type,
							
							payable_gst_on_project,
							architect_id,
							structural_architect_id,
							soil_testing_status,
							estimation_formation_status,
							project_status_1,
							project_status_2,
							remark,
							junior_engineer_name,
							assistant_engineer_name,
							executive_engineer_name,
							executive_engineer_from,
							executive_engineer_to,
							superintendent_engineer_name,
							superintendent_engineer_from,
							superintendent_engineer_to,
							revised_estimate_amount,
							revised_estimate_remark,
							revised_estimate_date,
							revised_dispatch_status,
							revised_remittance_amount,
							revised_remittance_date, 
							last_fy_tot_exp,
							current_fy_last_month_exp,
							current_month_exp,
							current_fy_tot_exp,
							tot_exp_project,
							balance_amt_cost,
							balance_amt_project,
							last_update,
							reporting_status,
							status, master_freeze 
							from uprnss_project_temp 
							left join uprnss_district on uprnss_district.sno = district_id
							left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
							left join uprnss_department_name on uprnss_department_name.sno = department_id
							where 1=1 and (status!="5" or status="0" or status is null or status="1") ';
						//print_r($_POST);
						if (isset($_POST['search'])) {
						    
						    if ($_POST['project_running_status'] != '') {
									$sql .= ' and reporting_status="' . $_POST['project_running_status'] . '"';
								}
							if ($_POST['department'] != '') {
								$sql .= ' and department_id="' . $_POST['department'] . '"';
							}
							if ($_POST['district'] != '') {
								$sql .= ' and district_id="' . $_POST['district'] . '"';
							}
							if ($_POST['division_name'] != '') {
								$sql .= ' and uprnss_project_temp.division_id="' . $_POST['division_name'] . '"';
							}
							if ($_POST['project_status_1'] != '') {
								$sql .= ' and project_status_1="' . $_POST['project_status_1'] . '"';
							}
							if ($_POST['project_type'] != '') {
								$sql .= ' and project_type="' . $_POST['project_type'] . '"';
							}
							if ($_POST['sanction_cost1'] == '') {
								if ($_POST['sanction_cost'] != '') {
									$sanction_cost = $_POST['sanction_cost'];
									$sql .= ' and abs(sanction_cost)' . $_POST['type'] . '"' . $sanction_cost . '"';
								}
							}
							if ($_POST['sanction_cost1'] != '') {
								$sanction_cost = abs($_POST['sanction_cost']);
								$sanction_cost1 = abs($_POST['sanction_cost1']);
								$sql .= ' and abs(sanction_cost)' . $_POST['type'] . '"' . $sanction_cost . '"';
								$sql .= ' and abs(sanction_cost)' . $_POST['type2'] . '"' . $sanction_cost1 . '"';
							}
							if ($_POST['tot_exp_project'] != '') {
								$sql .= ' and abs(tot_exp_project)' . $_POST['type1'] . '"' . $_POST['tot_exp_project'] . '"';
							}
							if ($_POST['tender_status'] != '') {
								$sql .= ' and tender_status="' . $_POST['tender_status'] . '"';
							}
							if ($_POST['date_type'] != '') {
								$sql .= ' and ' . $_POST['date_type'] . '>="' . $_POST['date_from'] . '" and ' . $_POST['date_type'] . '<"' . date('Y-m-d', strtotime($_POST['date_to'] . '+1 day')) . '"';
							}
							// $sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
							//if($_POST['work_start_date_from']!=''){
							// $sql .= ' and work_start_date>="'.$_POST['work_start_date_from'].'" and `work_start_date`<"'.$_POST['work_start_date_to'].'"';
							//}
						} else {
							$sql .= ' and "a"="b"';
						}
						$sql .= ' ORDER BY uprnss_project_temp.division_id, uprnss_project_temp.department_id';
						// echo $sql;
						$result = execute_query($sql);
						while ($row = mysqli_fetch_assoc($result)) {


							$sql = 'select * from master_projoect_status_2 where sno="' . $row['project_status_2'] . '"';
							// echo $sql;
							// $status2 = mysqli_fetch_assoc(execute_query($sql));
							$status2 = execute_query($sql);
							if (mysqli_num_rows($status2) != 0) {
								$status2 = mysqli_fetch_assoc($status2);
							} else {
								unset($status2);
								$status2['status_2'] = '';
							}

							$sql = 'select * from uprnss_architect where sno="' . $row['architect_id'] . '"';
							// echo $sql;
							// $architect = mysqli_fetch_assoc(execute_query($sql));
							$architect = execute_query($sql);
							if (mysqli_num_rows($architect) != 0) {
								$architect = mysqli_fetch_assoc($architect);
							} else {
								unset($architect);
								$architect['full_name_english'] = '';
							}

							$sql = 'select * from uprnss_architect where sno="' . $row['structural_architect_id'] . '"';
							// echo $sql;
							// $starch = mysqli_fetch_assoc(execute_query($sql));
							$starch = execute_query($sql);
							if (mysqli_num_rows($starch) != 0) {
								$starch = mysqli_fetch_assoc($starch);
							} else {
								unset($starch);
								$starch['full_name_english'] = '';
							}

							$sql = 'select * from dp_personal_info where sno="' . $row['junior_engineer_name'] . '"';
							// echo $sql;
							// $je = mysqli_fetch_assoc(execute_query($sql));
							$je = execute_query($sql);
							if (mysqli_num_rows($je) != 0) {
								$je = mysqli_fetch_assoc($je);
							} else {
								unset($je);
								$je['full_name'] = '';
							}

							$sql = 'select * from dp_personal_info where sno="' . $row['assistant_engineer_name'] . '"';
							// echo $sql;
							// $ae = mysqli_fetch_assoc(execute_query($sql));
							$ae = execute_query($sql);
							if (mysqli_num_rows($ae) != 0) {
								$ae = mysqli_fetch_assoc($ae);
							} else {
								unset($ae);
								$ae['full_name'] = '';
							}

							$sql = 'select * from dp_personal_info where sno="' . $row['executive_engineer_name'] . '"';
							// echo $sql;
							$exe = execute_query($sql);
							if (mysqli_num_rows($exe) != 0) {
								$exe = mysqli_fetch_assoc($exe);
							} else {
								unset($exe);
								$exe['full_name'] = '';
							}

							$sql = 'select * from dp_personal_info where sno="' . $row['superintendent_engineer_name'] . '"';
							// echo $sql;
							// $se = mysqli_fetch_assoc(execute_query($sql));
							$se = execute_query($sql);
							if (mysqli_num_rows($se) != 0) {
								$se = mysqli_fetch_assoc($se);
							} else {
								unset($se);
								$se['full_name'] = '';
							}

							$sql = 'select * from master_tender_status where sno="' . $row['tender_status'] . '"';
							// echo $sql;
							// $tender = mysqli_fetch_assoc(execute_query($sql));
							$tender = execute_query($sql);
							if (mysqli_num_rows($tender) != 0) {
								$tender = mysqli_fetch_assoc($tender);
							} else {
								unset($tender);
								$tender['status'] = '';
							}

							$sql = 'select * from invoice_civil where project_name="' . $row['sno'] . '"order by sno desc limit 1';
							// echo $sql;
							// $res = execute_query($sql);
							$res = execute_query($sql);
							if (mysqli_num_rows($res) != 0) {
								$row_invoice = mysqli_fetch_assoc($res);
								$sql = 'select * from transaction_civil_receipts where invoice_id="' . $row_invoice['sno'] . '"';
								//echo $sql.'<br>';
								$result_rcpt = execute_query($sql);
								$rcpt_txt = '<td colspan="2"><table class="" style="border:none!important;" width="100%">';
								$tot_rcpt = 0;
								$tot_exp = 0;

								if (mysqli_num_rows($result_rcpt) != 0) {
									$count_rcpt = mysqli_num_rows($result_rcpt);
									while ($row_rcpt = mysqli_fetch_assoc($result_rcpt)) {
										$rcpt_txt .= '<tr><td>' . ($row_rcpt['transaction_date'] != '' ? date("d-m-Y", strtotime($row_rcpt['transaction_date'])) : '') . '</td><td >' . $row_rcpt['transaction_amount'] . '</td></tr>';
										$tot_rcpt += (float) $row_rcpt['transaction_amount'];

									}

								} else {

									$count_rcpt = 0;

								}

								$rcpt_txt .= '</table></td>';

								$sql = 'select * from transaction_civil_activities where invoice_id="' . $row_invoice['sno'] . '"';
								$result_act = execute_query($sql);
								$act_txt = '<td colspan="2"><table>';
								if (mysqli_num_rows($result_act) != 0) {
									$count_act = mysqli_num_rows($result_act);
									while ($row_act = mysqli_fetch_assoc($result_act)) {
										$act_txt .= '<tr><td>' . $row_act['activity'] . '</td><td>' . $row_act['remarks'] . '</td></tr>';
									}
								} else {
									$count_act = 0;
								}
								$act_txt .= '</table></td>';

							} else {
								unset($res);
								$rcpt_txt = '<td colspan="2"></td>';
								$act_txt = '<td colspan="2"></td>';

								$row_invoice['balance_amt_project'] = "";
								$row_invoice['tot_exp_project'] = "";
								$row_invoice['current_fy_tot_exp'] = "";
								$row_invoice['current_month_exp'] = "";
								$row_invoice['current_month_exp'] = "";


								$row_invoice['sanction_cost'] = 0;
								$row_invoice['total_received_amount'] = 0;

								$row_invoice['balance_amt_cost'] = "";
								$row_invoice['total_physical_progress'] = "";
								$row_invoice['remark'] = "";
								$row_invoice['last_update'] = "";



							}
							$sql = 'select * from master_tender_status where sno="' . $row_invoice['tender_status'] . '"';
							// echo $sql;
							// $tender = mysqli_fetch_assoc(execute_query($sql));
							$tender = execute_query($sql);
							if (mysqli_num_rows($tender) != 0) {
								$tender = mysqli_fetch_assoc($tender);
							} else {
								unset($tender);
								$tender['status'] = '';
							}
							$sql = 'select * from master_projoect_status_1 where sno="' . $row_invoice['project_status_1'] . '"';
							// echo $sql;
							// $status1 = mysqli_fetch_assoc(execute_query($sql));
							$status1 = execute_query($sql);
							if (mysqli_num_rows($status1) != 0) {
								$status1 = mysqli_fetch_assoc($status1);
							} else {
								unset($status1);
								$status1['status_1'] = '';
							}

							echo '<tr>
								<td>' . $i++ . '</td>';
							if (isset($_POST['chk_project_type'])) {
								echo '
    								<td>';
								if ($row['project_type'] != '') {
									if ($row['project_type'] == '1') {
										echo '<span class="">शासन स्तर</span>';
									} elseif ($row['project_type'] == '2') {
										echo '<span class="">जिला स्तर</span>';
									}
								}
								echo '</td>';
							}

							if (isset($_POST['chk_division_name'])) {
								echo '<td>' . $row['division_name'] . '</td>';
							}
							if (isset($_POST['chk_district_name_hindi'])) {
								echo '<td>' . $row['district_name_hindi'] . '</td>';
							}
							if (isset($_POST['chk_department_name_hindi'])) {
								echo '<td>' . $row['department_name_hindi'] . '</td>';
							}
							if (isset($_POST['chk_project_name_hindi'])) {
								echo '<td>' . $row['project_name_hindi'] . '</td>';
							}
							if (isset($_POST['chk_sacntion_cost'])) {

								echo '<td>' . $row_invoice['sanction_cost'] . '</td>';
							}
							if (isset($_POST['chk_revised_date'])) {
								echo '<td>' . $row_invoice['revised_date'] . '</td>';
							}
							if (isset($_POST['chk_revised_cost'])) {
								echo '<td>' . $row_invoice['revised_cost'] . '</td>';
							}
							if (isset($_POST['chk_land_receive_date'])) {
								echo '<td>' . $row['land_receive_date'] . '</td>';
							}
							if (isset($_POST['chk_work_start_date'])) {
								echo '<td>' . $row['work_start_date'] . '</td>';
							}
							if (isset($_POST['chk_work_completion_date'])) {
								echo '<td>' . $row['work_completion_date'] . '</td>';
							}
							if (isset($_POST['chk_admin_go_no'])) {
								echo '<td>' . $row['admin_go_no'] . '</td>';
							}
							if (isset($_POST['chk_admin_go_date'])) {
								echo '<td>' . date("d-m-Y", strtotime($row['admin_go_date'])) . '</td>';
								// echo date("d-m-Y", strtotime($row['admin_go_date']));
							}
							// if (isset($_POST['chk_admin_go_no'])) {
							// 	echo '<td>' . $row['admin_go_no'] . '</br> ' . $row['admin_go_date'] . '</td>';
							// }
							if (isset($_POST['chk_financial_go_no'])) {
								echo '<td>' . $row['financial_go_no'] . ' </br>' . $row['financial_go_date'] . '</br>' . $row['financial_go_amount'] . '</td>';
							}
							if (isset($_POST['chk_technical_sacntion_date'])) {
								echo '<td>' . $row['technical_sanction_date'] . '</br>' . $row['technical_sanction_no'] . '</td>';
							}
							if (isset($_POST['chk_tender_status'])) {
								echo '<td>' . $tender['status'] . '</td>';
							}
							if (isset($_POST['chk_payable_gst'])) {
								echo '<td>' . $row['payable_gst_on_project'] . '</td>';
							}
							if (isset($_POST['chk_architect_name'])) {
								echo '<td>' . $architect['full_name_english'] . '</td>';
							}
							if (isset($_POST['chk_structural_architect'])) {
								echo '<td>' . $starch['full_name_english'] . '</td>';
							}
							if (isset($_POST['chk_soli_testing'])) {
								echo '<td>' . $row['soil_testing_status'] . '</td>';
							}
							if (isset($_POST['chk_estimation_status'])) {
								echo '<td>' . $row['estimation_formation_status'] . '</td>';
							}
							if (isset($_POST['chk_je_name'])) {
								echo '<td>' . $je['full_name'] . '</td>';
							}
							if (isset($_POST['chk_ae_name'])) {
								echo '<td>' . $ae['full_name'] . '</td>';
							}
							if (isset($_POST['chk_excn_name'])) {
								echo '<td>' . $exe['full_name'] . '</br>
    								' . $row['executive_engineer_from'] . '--
    								' . $row['executive_engineer_to'] . '</td>';
							}
							if (isset($_POST['chk_se_name'])) {

								echo '<td>' . $se['full_name'] . '</br>' . $row['superintendent_engineer_from'] . '--' . $row['superintendent_engineer_to'] . '</td>';
							}
							if (isset($_POST['chk_revised_amount'])) {
								echo '<td>' . $row_invoice['revised_estimate_amount'] . '</td>';
							}
							if (isset($_POST['chk_revision_reason'])) {
								echo '<td>' . $row_invoice['revised_estimate_remark'] . '</td>';
							}
							if (isset($_POST['chk_revision_date'])) {
								echo '<td>' . $row_invoice['revised_estimate_date'] . '</td>';
							}
							if (isset($_POST['chk_revision_sent_status'])) {
								echo '<td>' . $row_invoice['revised_dispatch_status'] . '</br>' . $row['revised_remittance_amount'] . '</td>';
							}
							if (isset($_POST['chk_revision_sent_date'])) {
								echo '<td>' . $row_invoice['revised_remittance_date'] . '</td>';
							}

							if (isset($_POST['chk_total_received_amount_date'])) {
								echo $rcpt_txt;
							}

							if (isset($_POST['chk_total_received_amount'])) {

								echo '<td>' . $row_invoice['total_received_amount'] . '</td>';
							}

							if (isset($_POST['chk_expense_current_fy'])) {

								echo '<td>' . $row_invoice['last_fy_tot_exp'] . '</td>';
							}

							if (isset($_POST['chk_expense_current_fy_month'])) {

								echo '<td>' . $row_invoice['current_fy_last_month_exp'] . '</td>';
							}
							if (isset($_POST['chk_expense_current_month'])) {

								echo '<td>' . $row_invoice['current_month_exp'] . '</td>';
							}

							if (isset($_POST['chk_expense_current_fy_total'])) {

								echo '<td>' . $row_invoice['current_fy_tot_exp'] . '</td>';
							}
							if (isset($_POST['chk_expense_project'])) {

								echo '<td>' . $row_invoice['tot_exp_project'] . '</td>';
							}
							if (isset($_POST['chk_balance_amount'])) {

								echo '<td>' . $row_invoice['balance_amt_cost'] . '</td>';
							}
							if (isset($_POST['chk_balance_amount_refer'])) {

								echo '<td>' . $row_invoice['balance_amt_project'] . '</td>';
							}
							if (isset($_POST['chk_percent_avmukt'])) {
								echo '<td>';
								if ($row_invoice['total_received_amount'] == 0) {
									echo 0;
								} else {
									$row_invoice['total_received_amount'] = (float) $row_invoice['total_received_amount'];
									$row_invoice['sanction_cost'] = ($row_invoice['sanction_cost'] == '' ? 1 : $row_invoice['sanction_cost']);
									echo round($row_invoice['total_received_amount'] / $row_invoice['sanction_cost'] * 100,2);
								}

								echo '</td>';
							}
							if (isset($_POST['chk_percent_physical_progress'])) {
								echo $act_txt;
							}

							if (isset($_POST['chk_percent_physical_progress_per'])) {

								echo '<td>' . $row_invoice['total_physical_progress'] . '</td>';
							}

							if (isset($_POST['chk_project_status_1'])) {


								echo '<td>' . $status1['status_1'] . '<br>' . $row_invoice['status_1_date'] . '</td>';
							}
							if (isset($_POST['chk_remarks'])) {
								echo '<td>' . $row_invoice['remark'] . '</td>';
							}
							if (isset($_POST['chk_third_evaluation'])) {
								echo '<td></td>';
							}
							if (isset($_POST['chk_project_image'])) {
							   
							echo '<td class="text-center">
									<span class="icon-stack">
									  <button type="button" class="icon-btn btn-view" 
											  title="View Files"
											  data-invoice="'.$row['sno'].'"
											  data-division="'.$row['division_name'].'"
											  data-district="'.$row['district_name_hindi'].'"
											  data-project="'.$row['project_name_hindi'].'"
											  data-count="'.$row['files_count'].'">
										<i class="fa fa-eye"></i>
									  </button>
									</span><br>
								  </td>';
							    
							}


							if (isset($_POST['chk_last_update'])) {
								echo '<td>';
								if ($row_invoice['last_update'] == "") {
									echo "NOT UPDATE";
								} else {
									echo date("d-m-Y", strtotime($row_invoice['last_update']));
								}
								'</td>';
							}
							echo '<td>';

							// if($row['status']=='' or $row['status']=='0'){
							// echo '<span class="text-primary">Active</span>';
							// }
							// else{
							// echo '<span class="text-danger">Disabled</span>';
							// }
						
							echo '
								<a href="view_project_temp.php?id=' . $row['sno'] . '" target="_blank"><i class="fa fa-eye"></i></a></td>
								
								</tr>';
						}
						?>
					</tbody>
				</table>

			</div>
		</div>
	</div>
</div>

<!-- View Modal -->
<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <div class="w-100">
          <div id="filesModalTop" class="small text-white-50"></div>
          <h5 class="modal-title m-0" id="filesModalLabel"></h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="filesModalBody">
        <div class="text-center text-muted">Loading...</div>
      </div>
    </div>
  </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imgPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex align-items-center justify-content-center">
        <img id="imgPreviewEl" src="" class="img-preview-full" alt="preview">
      </div>
    </div>
  </div>
</div>


<?php
page_footer_start();
?>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

var actionUrl = 'scripts/ajax.php';
	function fill_sub_department(val, selected = '') {
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
					if (value.id == selected) {
						txt += ' selected="selected" ';
					}
					txt += '>' + value.sub_department_hindi + '</option>';

				});
				$("#sub_department_id").html(txt);
			}
		});
	}

	function fill_scheme(val, selected = '') {
		var data = { "term": "b", "id": "scheme", "val": val };

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
					if (value.id == selected) {
						txt += ' selected="selected" ';
					}
					txt += '>' + value.scheme_name_hindi + '</option>';

				});
				$("#scheme").html(txt);
			}
		});
	}

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

<script>
$(document).ready(function () {
    var t = $('#general_stat_table').DataTable({ paging: false });

    $(document).on('click', '.btn-view', function(){
        var inv  = $(this).data('invoice');
        var cnt  = $(this).data('count');
        var divn = $(this).data('division') || '';
        var dist = $(this).data('district') || '';
        var proj = $(this).data('project') || '';

        // Header: Division • District (small) + Project (title)
        $('#filesModalTop').text(divn + (divn && dist ? ' • ' : '') + dist);
        $('#filesModalLabel').text(proj);

        $('#filesModalBody').html('<div class="text-center text-muted">Loading...</div>');
        $('#filesModal').modal('show');

        $.get('<?php echo $_SERVER["PHP_SELF"]; ?>', { ajax:'files', invoice_id: inv }, function(html){
            $('#filesModalBody').html(html);
        }).fail(function(){
            $('#filesModalBody').html('<div class="alert alert-danger">Failed to load files.</div>');
        });
    });

    // Click image to preview fullscreen
    $(document).on('click', '#filesModalBody img.file-thumb', function(){
        var src = $(this).attr('data-full') || $(this).attr('src');
        $('#imgPreviewEl').attr('src', src);
        $('#imgPreviewModal').modal('show');
    });
});
</script>
<?php
page_footer_end();
?>