<?php
include ("scripts/settings.php");

$msg = '';
$tab = 1;

if (isset ($_POST['search'])) {
	$_SESSION['post_custom_report'] = $_POST;
}

if (isset ($_POST['submit'])) {
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
	if (!isset ($_POST['search'])) {
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
	}
}

if (isset ($_GET['eid'])) {
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
if (isset ($_GET['delid'])) {
	$sql = 'update uprnss_project_temp set status="5" where sno="' . $_GET['delid'] . '"';
	execute_query($sql);
	$msg .= '<div class="alert alert-warning">Data Delete</div>';
}

if (isset ($_GET['freeze_sno'])) {
	$sql = 'update uprnss_project_temp set master_freeze="1" where sno="' . $_GET['freeze_sno'] . '"';
	execute_query($sql);
	$msg .= '<div class="alert alert-warning">Data Freezed</div>';
}

if (isset ($_GET['del'])) {
	$sql = 'delete from uprnss_project_temp where sno="' . $_GET['del'] . '"';
	execute_query($sql);
	$msg .= '<p class="text text-danger">Data Deleted.</p>';
}

if (isset ($_GET['act'])) {
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
</style>

<?php
page_header_end();
page_sidebar();

?>


<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	<div id="container" class="no-print">
		<div class="card card-body">

			<div class="" style="font-size:1.2rem;text-align:center;font-family:arial;">विगत 02 वर्षों में आवंटित कार्य
				एवं उसकी प्रगति</div>
			<div class="row d-flex my-auto">
				<table width="100%" class="table table-striped table-hover rounded">
					<tr>
						<th width="16%">Date Type</th>
						<th width="18%"><select name="date_type" id="date_type" class="form-control">

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
						<!-- <th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%">To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th> -->
					</tr>
					<tr>
						<th>विभाग </th>
						<th width="18%">
							<select class="form-control" name="department" id="department"
								tabindex="<?php echo $tab++; ?>">
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
									if (isset ($_POST['department'])) {
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
									if (isset ($_POST['division_name'])) {
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
									if (isset ($_POST['district'])) {
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
							<select class="form-control" name="project_status_1[]" id="project_status_1[]" tabindex="<?php echo $tab++; ?>" multiple>
									<?php
									$query = "select * from master_projoect_status_1";
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['project_status_1'])){
											foreach($_POST['project_status_1'] as $k=>$v){
												if($v==$data['sno']){
													echo ' selected="selected"';
												}
											}
										}
										echo '>'.trim($data['status_1']).'</option>';
									}
									?>
								</select>
						</th>
						<th>टेंडर की स्थिति</th>
						<th>
							<select class="form-control" name="tender_status" id="tender_status"
								tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = "select * from master_tender_status";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset ($_POST['tender_status'])) {
										if ($_POST['tender_status'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . trim($data['status']) . '</option>';
								}
								?>
							</select>
						</th>
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


					<!-- "chk_department_name_hindi" => "विभाग", "chk_project_name_hindi" => "परियोजना का नाम (हिन्दी )", "chk_technical_sacntion_date" => "तकनीकी स्वीकृति दिनांक/तकनीकी स्वीकृति क्रमांक", "chk_tender_status" => "टेंडर की स्थिति", "chk_payable_gst" => "योजना पर देय जी . एस. टी .", "chk_architect_name" => "आर्किटेक्ट", "chk_structural_architect" => "स्ट्राक्चरल आर्किटेक्ट", "chk_soli_testing" => "मृदा परीक्षण / Survey", "chk_estimation_status" => "आगणन गठन की स्थिति", "chk_je_name" => "परियोजना  पर तैनात  अवर  अभियंता का नाम", "chk_ae_name" => "परियोजना  पर तैनात  सहायक  अभियंता का नाम", "chk_excn_name" => "परियोजना  के  अधिशासी  अभियंता का नाम कब से/कब तक", "chk_se_name" => "परियोजना  के  अधीक्षण  अभियंता का नाम कब से/कब तक", "chk_revised_amount" => "पुनरीक्षित आगण की लागत ( लाख में )", "chk_revision_reason" => "पुनरीक्षित आगणन का  कारण", "chk_revision_date" => "पुनरीक्षित आगण की तिथि", "chk_revision_sent_status" => "पुनरीक्षित प्रेषण की स्थिति /धनराशि ( लाख में )", "chk_revision_sent_date" => "पुनरीक्षित प्रेषण की तिथि", "chk_expense_current_fy" => "गत वित्तीय वर्ष में कुल व्यय", "chk_expense_current_fy_month" => "वर्तमान वित्तीय वर्ष में गत माह तक व्यय ( लाख में )", "chk_expense_current_month" => "वर्तमान माह में व्यय ( लाख में )", "chk_expense_current_fy_total" => "वर्तमान वित्तीय वर्ष में कुल व्यय ( लाख में )", "chk_expense_project" => "परियोजना में अब तक का कुल व्यय ( लाख में )", "chk_balance_amount" => "शेष धनराशि परियोजना लागत के सापेक्ष ( लाख में )", "chk_balance_amount_refer" => "परियोजना पर शेष धनराशि अवमुक्त के सापेक्ष ( लाख में )", "chk_project_status_1" => "परियोजना की स्थिति  1", "chk_project_status_2" => "परियोजना की स्थिति  2", "chk_remarks" => "अभियुक्ति", "chk_last_update" => "Last Update" -->
				</table>
				<?php $select_array = array("chk_department_name_hindi" => "विभाग", "chk_project_name_hindi" => "कार्य  का नाम (हिन्दी )", "chk_admin_go_date" => "स्वीकृति दिनांक", "chk_sacntion_cost" => "मूल परियोजना लागत ( लाख में ) &amp;", "chk_balance_amount_refer" => "परियोजना पर शेष धनराशि अवमुक्त के सापेक्ष ( लाख में )", "chk_project_status_1" => "परियोजना की स्थिति  1", "chk_remarks" => "अभियुक्ति");
				?>
				<table class="table table-border table-striped ">
					<?php
					$i = 1;
					foreach ($select_array as $k => $v) {
						if ($i == 1) {
							echo '<tr>';
						}
						if ($i % 10 == 0) {
							echo '</tr><tr>';
						}
						$i++;
						echo '<td class="small"><input type="checkbox" name="' . $k . '" ';
						if ($_POST['date_type'] != '') {
							if (isset ($_POST[$k])) {
								echo ' checked="checked" ';
							}
						} else {
							echo 'checked="checked"';
						}
						echo '>' . $v . '</td>';
					}
					echo '</tr>';
					?>
				</table>
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
				<h4 class="card-title text-center">विगत 02 वर्षों में आवंटित कार्य एवं उसकी प्रगति</h4></br>
			</div>
			<div class="card-body">
				<table class="table table-striped table-hover table-bordered" id="general_stat_table">
					<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							<th>क्रं०</th>
							<?php
							foreach ($select_array as $k => $v) {
								if (isset ($_POST[$k])) {
									echo '<th>' . $v . '</th>';
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
								if (isset ($_POST[$k])) {
									echo '<th>' . $i++ . '</th>';
								}
							}
							?>
						</tr>

					</thead>

					<tbody>

						<?php
						$i = 1;
						if ($_SESSION['usertype'] == 'sadmin') {
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
							status 
							from uprnss_project_temp 
							left join uprnss_district on uprnss_district.sno = district_id
							left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
							left join uprnss_department_name on uprnss_department_name.sno = department_id
							where 1=1 and (status!="5" or status="0" or status is null or status="1") ';
							//print_r($_POST);
							if (isset ($_POST['search'])) {
								if ($_POST['department'] != '') {
									$sql .= ' and department_id="' . $_POST['department'] . '"';
								}
								if ($_POST['district'] != '') {
									$sql .= ' and district_id="' . $_POST['district'] . '"';
								}
								if ($_POST['division_name'] != '') {
									$sql .= ' and uprnss_project_temp.division_id="' . $_POST['division_name'] . '"';
								}
								if(!empty($_POST['project_status_1'])){
									$sql .= ' and project_status_1 in ('.implode(",", $_POST['project_status_1']).')';	
								}
								if ($_POST['project_type'] != '') {
									$sql .= ' and project_type="' . $_POST['project_type'] . '"';
								}
								if ($_POST['sanction_cost'] != '') {
									$sanction_cost = $_POST['sanction_cost'];
									$sql .= ' and abs(sanction_cost)' . $_POST['type'] . '"' . $sanction_cost . '"';
								}
								if ($_POST['tot_exp_project'] != '') {
									$sql .= ' and abs(tot_exp_project)' . $_POST['type1'] . '"' . $_POST['tot_exp_project'] . '"';
								}
								if ($_POST['tender_status'] != '') {
									$sql .= ' and tender_status="' . $_POST['tender_status'] . '"';
								}
								$today=date("Y-m-d");
                             $todate = date("Y-m-d", strtotime($today.'-2 Years'));
								// if($_POST['date_type']!=''){
									// $sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.date("d-m-Y").'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
								// }
								$sql .= '  and '.$_POST['date_type'].'<"'.$todate.'"';
								//if($_POST['work_start_date_from']!=''){
									// $sql .= ' and work_start_date>="'.$_POST['work_start_date_from'].'" and `work_start_date`<"'.$_POST['work_start_date_to'].'"';
								//}
                                // echo $sql;
							
								
							} else {
								$sql .= ' and "a"="b"';
							}
							//echo $sql;
							$result = execute_query($sql);
							while ($row = mysqli_fetch_assoc($result)) {
								$totrec = 0;
								$totrest = 0;
								// $totrecamount2="SELECT * FROM invoice_civil  WHERE project_name='".$row['sno']."'";
								// $restotrecamount2=mysqli_query($db,$totrecamount2);
								// while($totrecamountrow2=mysqli_fetch_assoc($restotrecamount2)){
								// 	// echo $totrecamountrow['total_received_amount']." hello <br>";
								// 	$totrec+=(float)$totrecamountrow2['total_received_amount'];
								// }
						
								$totrecamount = "SELECT * FROM invoice_civil  WHERE project_name='" . $row['sno'] . "'  order by abs(sno) desc LIMIT 1";
								$restotrecamount = mysqli_query($db, $totrecamount);
								$rowtotrecamount = mysqli_fetch_assoc($restotrecamount);
								if (isset ($rowtotrecamount['total_received_amount'])) {
									$totrec = (float) $rowtotrecamount['total_received_amount'];
								}
								if (isset ($rowtotrecamount['balance_amt_project'])) {
									$totrest = (float) $rowtotrecamount['balance_amt_project'];
								}



								$sql = 'select * from master_projoect_status_1 where sno="' . $row['project_status_1'] . '"';
								// echo $sql;
								// $status1 = mysqli_fetch_assoc(execute_query($sql));
								$status1 = execute_query($sql);
								if (mysqli_num_rows($status1) != 0) {
									$status1 = mysqli_fetch_assoc($status1);
								} else {
									unset($status1);
									$status1['status_1'] = '';
								}

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


								echo '<tr>
								<td>' . $i++ . '</td>';

								if (isset ($_POST['chk_department_name_hindi'])) {
									echo '<td>' . $row['department_name_hindi'] . '</td>';
								}
								if (isset ($_POST['chk_project_name_hindi'])) {
									echo '<td>' . $row['project_name_hindi'] . '</td>';
								}

								if (isset ($_POST['chk_admin_go_date'])) {
									echo '<td>';
									echo date("d-m-Y", strtotime($row['admin_go_date']));
									echo '</td>';
								}

								if (isset ($_POST['chk_sacntion_cost'])) {
									echo '<td>' . ($row['sanction_cost'] != '' ? $row['sanction_date'] . ' (' . $row['sanction_cost'] . ').' : '') . '</td>';
								}
								// echo "<td>{$totrec} mil gya hai, {$totrest} Bacha hua hai</td>";                    
								echo "<td>{$totrest} </td>";
								if (isset ($_POST['chk_project_status_1'])) {

									echo '<td>' . $status1['status_1'] . '</td>';
								}

								if (isset ($_POST['chk_remarks'])) {
									echo '<td>' . $row['remark'] . '</td>';
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
						}
						 elseif ($_SESSION['usertype'] == '2') {
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
							status, master_freeze
							from uprnss_project_temp 
							left join uprnss_district on uprnss_district.sno = district_id
							left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
							left join uprnss_department_name on uprnss_department_name.sno = department_id
							where department_id in (' . implode(",", $_SESSION['department']) . ') and (status !="5" or status is null ) LIMIT 1 ';

							// print_r($_POST);
							if (isset ($_POST['search'])) {
								if ($_POST['department'] != '') {
									$sql .= ' and department_id="' . $_POST['department'] . '"';
								}
								if ($_POST['district'] != '') {
									$sql .= ' and district_id="' . $_POST['district'] . '"';
								}
								if ($_POST['division_name'] != '') {
									$sql .= ' and uprnss_project_temp.division_id="' . $_POST['division_name'] . '"';
								}
								if(!empty($_POST['project_status_1'])){
									$sql .= ' and project_status_1 in ('.implode(",", $_POST['project_status_1']).')';	
								}
								if ($_POST['project_type'] != '') {
									$sql .= ' and project_type="' . $_POST['project_type'] . '"';
								}
								if ($_POST['sanction_cost'] != '') {
									$sanction_cost = $_POST['sanction_cost'];
									$sql .= ' and abs(sanction_cost)' . $_POST['type'] . '"' . $sanction_cost . '"';
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
							//echo $sql;
							$result = execute_query($sql);
							while ($row = mysqli_fetch_assoc($result)) {

								$sql = 'select * from master_projoect_status_1 where sno="' . $row['project_status_1'] . '"';
								// echo $sql;
								// $status1 = mysqli_fetch_assoc(execute_query($sql));
								$status1 = execute_query($sql);
								if (mysqli_num_rows($status1) != 0) {
									$status1 = mysqli_fetch_assoc($status1);
								} else {
									unset($status1);
									$status1['status_1'] = '';
								}

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
								

								echo '<tr>
								<td>' . $i++ . '</td>';
								if (isset ($_POST['chk_project_type'])) {
									echo '
    								<td>';
									if ($row['project_type'] != '') {
										if ($row['project_type'] == '1') {
											echo '<span class="">Ho level</span>';
										} elseif ($row['project_type'] == '2') {
											echo '<span class="">Division Level</span>';
										}
									}
									echo '</td>';
								}

								if (isset ($_POST['chk_division_name'])) {
									echo '<td>' . $row['division_name'] . '</td>';
								}
								if (isset ($_POST['chk_district_name_hindi'])) {
									echo '<td>' . $row['district_name_hindi'] . '</td>';
								}
								if (isset ($_POST['chk_department_name_hindi'])) {
									echo '<td>' . $row['department_name_hindi'] . '</td>';
								}
								if (isset ($_POST['chk_project_name'])) {
									echo '<td>' . $row['project_name'] . '</td>';
								}
								if (isset ($_POST['chk_project_name_hindi'])) {
									echo '<td>' . $row['project_name_hindi'] . '</td>';
								}
								if (isset ($_POST['chk_sacntion_cost'])) {
									echo '<td>' . ($row['sanction_cost'] != '' ? $row['sanction_date'] . ' (' . $row['sanction_cost'] . ').' : '') . '</td>';
								}
								if (isset ($_POST['chk_revised_date'])) {
									echo '<td>' . $row['revised_date'] . '</td>';
								}
								if (isset ($_POST['chk_revised_cost'])) {
									echo '<td>' . $row['revised_cost'] . '</td>';
								}
								if (isset ($_POST['chk_land_receive_date'])) {
									echo '<td>' . $row['land_receive_date'] . '</td>';
								}
								if (isset ($_POST['chk_work_start_date'])) {
									echo '<td>' . $row['work_start_date'] . '</td>';
								}
								if (isset ($_POST['chk_work_completion_date'])) {
									echo '<td>' . $row['work_completion_date'] . '</td>';
								}
								if (isset ($_POST['chk_admin_go_no'])) {
									echo '<td>' . $row['admin_go_no'] . '</br> ' . $row['admin_go_date'] . '</td>';
								}
								if (isset ($_POST['chk_financial_go_no'])) {
									echo '<td>' . $row['financial_go_no'] . ' </br>' . $row['financial_go_date'] . '</br>' . $row['financial_go_amount'] . '</td>';
								}
								if (isset ($_POST['chk_technical_sacntion_date'])) {
									echo '<td>' . $row['technical_sanction_date'] . '</br>' . $row['technical_sanction_no'] . '</td>';
								}
								if (isset ($_POST['chk_tender_status'])) {
									echo '<td>' . $tender['status'] . '</td>';
								}
								if (isset ($_POST['chk_payable_gst'])) {
									echo '<td>' . $row['payable_gst_on_project'] . '</td>';
								}
								if (isset ($_POST['chk_architect_name'])) {
									echo '<td>' . $architect['full_name_english'] . '</td>';
								}
								if (isset ($_POST['chk_structural_architect'])) {
									echo '<td>' . $starch['full_name_english'] . '</td>';
								}
								if (isset ($_POST['chk_soli_testing'])) {
									echo '<td>' . $row['soil_testing_status'] . '</td>';
								}
								if (isset ($_POST['chk_estimation_status'])) {
									echo '<td>' . $row['estimation_formation_status'] . '</td>';
								}
								if (isset ($_POST['chk_je_name'])) {
									echo '<td>' . $je['full_name'] . '</td>';
								}
								if (isset ($_POST['chk_ae_name'])) {
									echo '<td>' . $ae['full_name'] . '</td>';
								}
								if (isset ($_POST['chk_excn_name'])) {
									echo '<td>' . $exe['full_name'] . '</br>
    								' . $row['executive_engineer_from'] . '--
    								' . $row['executive_engineer_to'] . '</td>';
								}
								if (isset ($_POST['chk_se_name'])) {

									echo '<td>' . $se['full_name'] . '</br>' . $row['superintendent_engineer_from'] . '--' . $row['superintendent_engineer_to'] . '</td>';
								}
								if (isset ($_POST['chk_revised_amount'])) {
									echo '<td>' . $row['revised_estimate_amount'] . '</td>';
								}
								if (isset ($_POST['chk_revision_reason'])) {
									echo '<td>' . $row['revised_estimate_remark'] . '</td>';
								}
								if (isset ($_POST['chk_revision_date'])) {
									echo '<td>' . $row['revised_estimate_date'] . '</td>';
								}
								if (isset ($_POST['chk_revision_sent_status'])) {
									echo '<td>' . $row['revised_dispatch_status'] . '</br>(' . $row['revised_remittance_amount'] . ')</td>';
								}
								if (isset ($_POST['chk_revision_sent_date'])) {
									echo '<td>' . $row['revised_remittance_date'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_fy'])) {

									echo '<td>' . $row['last_fy_tot_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_fy_month'])) {
									echo '<td>' . $row['current_fy_last_month_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_month'])) {
									echo '<td>' . $row['current_month_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_fy_total'])) {
									echo '<td>' . $row['current_fy_tot_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_project'])) {
									echo '<td>' . $row['tot_exp_project'] . '</td>';
								}
								if (isset ($_POST['chk_balance_amount'])) {
									echo '<td>' . $row['balance_amt_cost'] . '</td>';
								}
								if (isset ($_POST['chk_balance_amount_refer'])) {
									echo '<td>' . $row['balance_amt_project'] . '</td>';
								}
								if (isset ($_POST['chk_project_status_1'])) {


									echo '<td>' . $status1['status_1'] . '</td>';
								}
								if (isset ($_POST['chk_project_status_2'])) {
									echo '<td>' . $status2['status_2'] . '</td>';
								}

								if (isset ($_POST['chk_last_update'])) {
									echo '<td>' . $row['last_update'] . '</td>';
								}
								if (isset ($_POST['chk_third_party_evaluation'])) {
									echo '<td>' . $row['chk_third_party_evaluation'] . '</td>';
								}
								if (isset ($_POST['chk_remarks'])) {
									echo '<td>' . $row['remark'] . '</td>';
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
						} else {
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
							status, master_freeze
							from uprnss_project_temp 
							left join uprnss_district on uprnss_district.sno = district_id
							left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
							left join uprnss_department_name on uprnss_department_name.sno = department_id
							where uprnss_project_temp.division_id in (' . implode(",", $_SESSION['divisions']) . ') and (status!="5" or status="0" or status is null or status="1")';
							//print_r($_POST);
							if (isset ($_POST['search'])) {
								if ($_POST['department'] != '') {
									$sql .= ' and department_id="' . $_POST['department'] . '"';
								}
								if ($_POST['district'] != '') {
									$sql .= ' and district_id="' . $_POST['district'] . '"';
								}
								if ($_POST['division_name'] != '') {
									$sql .= ' and uprnss_project_temp.division_id="' . $_POST['division_name'] . '"';
								}
								if(!empty($_POST['project_status_1'])){
									$sql .= ' and project_status_1 in ('.implode(",", $_POST['project_status_1']).')';	
								}
								if ($_POST['project_type'] != '') {
									$sql .= ' and project_type="' . $_POST['project_type'] . '"';
								}
								if ($_POST['sanction_cost'] != '') {
									$sanction_cost = $_POST['sanction_cost'];
									$sql .= ' and abs(sanction_cost)' . $_POST['type'] . '"' . $sanction_cost . '"';
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
							//echo $sql;
							$result = execute_query($sql);
							while ($row = mysqli_fetch_assoc($result)) {
								$sql = 'select * from master_projoect_status_1 where sno="' . $row['project_status_1'] . '"';
								// echo $sql;
								// $status1 = mysqli_fetch_assoc(execute_query($sql));
								$status1 = execute_query($sql);
								if (mysqli_num_rows($status1) != 0) {
									$status1 = mysqli_fetch_assoc($status1);
								} else {
									unset($status1);
									$status1['status_1'] = '';
								}

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


								echo '<tr>
								<td>' . $i++ . '</td>';
								if (isset ($_POST['chk_project_type'])) {
									echo '
    								<td>';
									if ($row['project_type'] != '') {
										if ($row['project_type'] == '1') {
											echo '<span class="">Ho level</span>';
										} elseif ($row['project_type'] == '2') {
											echo '<span class="">Division Level</span>';
										}
									}
									echo '</td>';
								}

								if (isset ($_POST['chk_division_name'])) {
									echo '<td>' . $row['division_name'] . '</td>';
								}
								if (isset ($_POST['chk_district_name_hindi'])) {
									echo '<td>' . $row['district_name_hindi'] . '</td>';
								}
								if (isset ($_POST['chk_department_name_hindi'])) {
									echo '<td>' . $row['department_name_hindi'] . '</td>';
								}
								if (isset ($_POST['chk_project_name'])) {
									echo '<td>' . $row['project_name'] . '</td>';
								}
								if (isset ($_POST['chk_project_name_hindi'])) {
									echo '<td>' . $row['project_name_hindi'] . '</td>';
								}
								if (isset ($_POST['chk_sacntion_cost'])) {
									echo '<td>' . ($row['sanction_cost'] != '' ? $row['sanction_date'] . ' (' . $row['sanction_cost'] . ').' : '') . '</td>';
								}
								if (isset ($_POST['chk_revised_date'])) {
									echo '<td>' . $row['revised_date'] . '</td>';
								}
								if (isset ($_POST['chk_revised_cost'])) {
									echo '<td>' . $row['revised_cost'] . '</td>';
								}
								if (isset ($_POST['chk_land_receive_date'])) {
									echo '<td>' . $row['land_receive_date'] . '</td>';
								}
								if (isset ($_POST['chk_work_start_date'])) {
									echo '<td>' . $row['work_start_date'] . '</td>';
								}
								if (isset ($_POST['chk_work_completion_date'])) {
									echo '<td>' . $row['work_completion_date'] . '</td>';
								}
								if (isset ($_POST['chk_admin_go_no'])) {
									echo '<td>' . $row['admin_go_no'] . '</br> ' . $row['admin_go_date'] . '</td>';
								}
								if (isset ($_POST['chk_financial_go_no'])) {
									echo '<td>' . $row['financial_go_no'] . ' </br>' . $row['financial_go_date'] . '</br>' . $row['financial_go_amount'] . '</td>';
								}
								if (isset ($_POST['chk_technical_sacntion_date'])) {
									echo '<td>' . $row['technical_sanction_date'] . '</br>' . $row['technical_sanction_no'] . '</td>';
								}
								if (isset ($_POST['chk_tender_status'])) {
									echo '<td>' . $tender['status'] . '</td>';
								}
								if (isset ($_POST['chk_payable_gst'])) {
									echo '<td>' . $row['payable_gst_on_project'] . '</td>';
								}
								if (isset ($_POST['chk_architect_name'])) {
									echo '<td>' . $architect['full_name_english'] . '</td>';
								}
								if (isset ($_POST['chk_structural_architect'])) {
									echo '<td>' . $starch['full_name_english'] . '</td>';
								}
								if (isset ($_POST['chk_soli_testing'])) {
									echo '<td>' . $row['soil_testing_status'] . '</td>';
								}
								if (isset ($_POST['chk_estimation_status'])) {
									echo '<td>' . $row['estimation_formation_status'] . '</td>';
								}
								if (isset ($_POST['chk_je_name'])) {
									echo '<td>' . $je['full_name'] . '</td>';
								}
								if (isset ($_POST['chk_ae_name'])) {
									echo '<td>' . $ae['full_name'] . '</td>';
								}
								if (isset ($_POST['chk_excn_name'])) {
									echo '<td>' . $exe['full_name'] . '</br>
    								' . $row['executive_engineer_from'] . '--
    								' . $row['executive_engineer_to'] . '</td>';
								}
								if (isset ($_POST['chk_se_name'])) {

									echo '<td>' . $se['full_name'] . '</br>' . $row['superintendent_engineer_from'] . '--' . $row['superintendent_engineer_to'] . '</td>';
								}
								if (isset ($_POST['chk_revised_amount'])) {
									echo '<td>' . $row['revised_estimate_amount'] . '</td>';
								}
								if (isset ($_POST['chk_revision_reason'])) {
									echo '<td>' . $row['revised_estimate_remark'] . '</td>';
								}
								if (isset ($_POST['chk_revision_date'])) {
									echo '<td>' . $row['revised_estimate_date'] . '</td>';
								}
								if (isset ($_POST['chk_revision_sent_status'])) {
									echo '<td>' . $row['revised_dispatch_status'] . '</br>(' . $row['revised_remittance_amount'] . ')</td>';
								}
								if (isset ($_POST['chk_revision_sent_date'])) {
									echo '<td>' . $row['revised_remittance_date'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_fy'])) {

									echo '<td>' . $row['last_fy_tot_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_fy_month'])) {
									echo '<td>' . $row['current_fy_last_month_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_month'])) {
									echo '<td>' . $row['current_month_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_current_fy_total'])) {
									echo '<td>' . $row['current_fy_tot_exp'] . '</td>';
								}
								if (isset ($_POST['chk_expense_project'])) {
									echo '<td>' . $row['tot_exp_project'] . '</td>';
								}
								if (isset ($_POST['chk_balance_amount'])) {
									echo '<td>' . $row['balance_amt_cost'] . '</td>';
								}
								if (isset ($_POST['chk_balance_amount_refer'])) {
									echo '<td>' . $row['balance_amt_project'] . '</td>';
								}
								if (isset ($_POST['chk_project_status_1'])) {


									echo '<td>' . $status1['status_1'] . '</td>';
								}
								if (isset ($_POST['chk_project_status_2'])) {
									echo '<td>' . $status2['status_2'] . '</td>';
								}

								if (isset ($_POST['chk_last_update'])) {
									echo '<td>' . $row['last_update'] . '</td>';
								}
								if (isset ($_POST['chk_third_party_evaluation'])) {
									echo '<td>' . $row['chk_third_party_evaluation'] . '</td>';
								}
								if (isset ($_POST['chk_remarks'])) {
									echo '<td>' . $row['remark'] . '</td>';
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
						}

						?>
					</tbody>
				</table>

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

$('select[multiple]').multiselect({
	search: true,
	selectAll: true
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