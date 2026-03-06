<?php
include ("scripts/settings.php");
page_header_start('Income Tax Report');
$_SESSION['payroll_sql_01'] = $sql;
if (isset($_POST['company_name'])) {
	$_SESSION['payroll_post'] = $_POST;
}
// set_time_limit(0);
if (isset($_GET['del_id'])) {
	$sql_delete = 'DELETE FROM `payslip_report` WHERE `sno`="' . $_GET['del_id'] . '"';
	$res = execute_query($sql_delete);
	if ($res) {
		$sql_delete_detail = 'DELETE FROM `payslip_details` WHERE `payslip_id`="' . $_GET['del_id'] . '"';
		execute_query($sql_delete_detail);
		$msg = '<div class="alert alert-success">Payslip Information Deleted...</div>';
	}
}
?>
<style type="text/css">
	table thead tr th {
		text-align: center;
		border: 2px solid black;
	}

	table tbody tr td {
		border: 2px solid black;
	}

	th {
		font-size: 14px;
	}

	td {
		font-size: 14px;
	}

	@media print {
		* {
			margin: 0 !important;
			padding: 0 !important;
			box-sizing: border-box !important;
		}

		body {
			padding: 1rem !important;
		}

		td {
			padding: 8px !important;
			/* margin: 10px !important; */
		}

		.no-print {
			display: none !important;
		}

		.btn-print {
			display: none;
		}


	}
</style>
<?php
page_header_end();
navigation($_SERVER['PHP_SELF']);
?>
<div class="container">
	<div class="row">

		<div class="no-print">
			<div class="panel">
				<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="confirm"
					enctype="multipart/form-data">
					<div class="panel-heading">Search</div>
					<div class="panel-body">
						<!-- <span style="float: right;">
							<a onclick="printPage()" class="no-print btn btn-info">Print this page</a>
						</span> -->

						<div style="text-align: right; width: 100%;">
							<a onclick="printPage()" class="no-print btn btn-success"
								style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print this page
							</a>
							<a href="payroll_income_tax_report_export.php" target="_blank" class="no-print btn btn-info"
								style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Download Excel
							</a>
						</div>

						<table class="table table-hover table-responsive table-bordered table-striped">
							<thead>
								<tr>
									<td>Company Name</td>
									<td>
										<select class="form-control" name="company_name" id="company_name"
											onchange="select_employee();">
											<option value="">-Select All-</option>
											<?php
											$sql = "SELECT * FROM `uprnss_division`";
											$query = mysqli_query($db_erp, $sql);
											while ($row = mysqli_fetch_array($query)) {
												echo '<option value="' . $row['s_no'] . '" ';
												if (isset($_POST['company_name'])) {
													if ($row['s_no'] == $_POST['company_name']) {
														echo 'selected="selected"';
													}
												}
												echo '>' . $row['division_name'] . "</option>";
											}
											?>
										</select>
									</td>
									<td>Employee Name</td>
									<td>
										<select class="form-control" name="emp_name" id="emp_name">
											<option value="">-Select All-</option>
											<?php
											$sql = 'SELECT * FROM `employee`';
											$result = execute_query($sql);
											while ($row = mysqli_fetch_array($result)) {
												echo '<option value="' . $row['sno'] . '" ';
												if (isset($_POST['emp_name'])) {
													if ($row['sno'] == $_POST['emp_name']) {
														echo 'selected="selected"';
													}
												}
												echo '>' . $row['employee_name'] . "</option>";
											}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Financial Year</td>
									<td>
										<select name="financial_year" class="form-control">
											<?php
											if (date('m') > 3) {
												$select_start_session = date('Y');
											} else {
												$select_start_session = date('Y') - 1;
											}
											$session_start = date('Y') - 50;
											for ($i = $session_start; $i <= $session_start + 100; $i++) {
												$end_session = $i + 1;
												echo '<option value="' . $i . '-' . $end_session . '" ';
												if (isset($_POST['financial_year'])) {
													if ($_POST['financial_year'] == $i . '-' . $end_session) {
														echo 'selected';
													}
												} elseif ($i == $select_start_session) {
													echo 'selected';
												}
												echo '>' . $i . '-' . $end_session . '</option>';
											}
											?>
										</select>
									</td>
									<td>For Month</td>
									<td>
										<select class="form-control" name="for_month" id="title">
											<?php
											$month = 1;
											$current_month = date('m');
											while ($month <= 12) {
												echo '<option value="' . date('m', strtotime('01.' . $month . '.2020')) . '"';
												if (isset($_POST['for_month'])) {
													if ($_POST['for_month'] == $month) {
														echo 'selected="selected"';
													}
												} else {
													if ($month == $current_month) {
														echo 'selected="selected"';
													}
												}
												echo '>' . date('F', strtotime('01.' . $month . '.2020')) . '</option>';
												$month++;
											}
											?>
										</select>
									</td>
								</tr>
							</thead>
						</table>
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" name="search" id="search" value="SEARCH"
									class="form-control btn btn-info">
							</div>
							<div class="col-sm-6">
								<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-control btn btn-info">Reset</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php
		if (isset($_POST['search'])) {
			?>
			<table class="table table-hover table-responsive table-striped" border="2">
				<thead>
					<tr style="border: 0px;">
						<td colspan="22" style="border: 0px;"><span class="print-only">
								<?php
								if (isset($_POST['company_name'])) {
									if ($_POST['company_name'] != '') {
										$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="' . $_POST['company_name'] . '"';
										$result_company = mysqli_query($db_erp, $sql_company);
										$row_company = mysqli_fetch_array($result_company);
										?>
										<table width="100%" border="" id="no_border" class="print-only"
											style="overflow-x:auto; border:none!important;">
											<tr>
												<td width="30%" style="border:none;"><img src="images/icon.png" height="130"></td>
												<td width="70%" colspan="2" style="border:none;">
													<h4 class="title text-justify"> उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू.
														पी. आर. एन. एस. एस.) <br /> UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH
														LTD.(UPRNSS).
														<br />Income Tax Report:
														<?php echo date('F', strtotime('01.' . $_POST['for_month'] . '.2020')) . '&nbsp;' . $_POST['financial_year']; ?>
													</h4>
													<h5>Construction Division: <?php echo $row_company['division_name']; ?></h5>
												</td>
											</tr>
										</table>
										<?php
									} else {
										?>
										<table width="100%" border="" id="no_border" class="print-only"
											style="overflow-x:auto; border:none!important;">
											<tr>
												<td width="30%" style="border:none;"><img src="images/icon.png" height="130"></td>
												<td width="70%" colspan="2" style="border:none;">
													<h4 class="title text-justify"> उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू.
														पी. आर. एन. एस. एस.) <br /> UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH
														LTD.(UPRNSS).
														<br />Income Tax Report: <?php echo date('F', strtotime('01.' . $_POST['for_month'] . '.2020')) . '&nbsp;' .
															$_POST['financial_year']; ?>
													</h4>
												</td>
											</tr>
										</table>
										<?php
									}
								}
								?>
							</span></td>
					</tr>
					<tr>
						<th rowspan="2" style="border:2px solid black;">S.No.</th>
						<th rowspan="2" style="border:2px solid black;">Employee Code</th>
						<th rowspan="2" style="border:2px solid black;">Employee Name</th>
						<th rowspan="2" style="border:2px solid black;">Designation</th>
						<th rowspan="2" style="border:2px solid black;">Division Name</th>
						<th rowspan="2" style="border:2px solid black;">Pan no.</th>
						<th rowspan="2" style="border:2px solid black;">Gross Salary</th>
						<th rowspan="2" style="border:2px solid black;">CPF</th>
						<th rowspan="3" style="border:2px solid black;">Income Tax</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sql = 'SELECT `payslip_report`.`sno` AS payslip_id, 
					`employee`.`sno` AS emp_id, 
					`employee`.`employee_name`,
					`employee`.`company_id`,
					`employee`.`employee_code`,
					`employee`.`bank_name`,
					`employee`.`acount_number`, 
					`employee`.`pan`, 
					`employee`.`ifsc_code`,
					`employee`.`branch`,
					`employee`.`employee_designation`,
					`payslip_report`.`financial_year`, 
					`payslip_report`.`for_month`, 
					`payslip_report`.`total_income`, 
					`payslip_report`.`advance_amount`,
					`payslip_report`.`total_deduction`,
					`payslip_report`.`total` 
					FROM `payslip_report` 
					JOIN `employee` ON `employee`.`sno` = `payslip_report`.`emp_id` 
					WHERE 1=1';
					
					if (isset($_POST['company_name']) && !empty($_POST['company_name'])) {
						$sql .= ' AND `employee`.`company_id`="' . $_POST['company_name'] . '"';
					}
					
					if (isset($_POST['emp_name']) && !empty($_POST['emp_name'])) {
						$sql .= ' AND `payslip_report`.`emp_id`="' . $_POST['emp_name'] . '"';
					}
					
					if (isset($_POST['financial_year']) && !empty($_POST['financial_year'])) {
						$sql .= ' AND `payslip_report`.`financial_year`="' . $_POST['financial_year'] . '"';
					}
					
					if (isset($_POST['for_month']) && !empty($_POST['for_month'])) {
						$sql .= ' AND `payslip_report`.`for_month`="' . $_POST['for_month'] . '"';
					}
					
					$sql .= ' ORDER BY ABS(`payslip_report`.`financial_year`) DESC, 
									ABS(`payslip_report`.`for_month`) DESC, 
									ABS(`employee`.`company_id`) DESC, 
									`employee`.`employee_designation` ASC, 
									`payslip_report`.`main_basic` DESC, 
									ABS(`payslip_report`.`main_basic`) DESC, 
									ABS(`payslip_report`.`total`) DESC';
					
					$result = execute_query($sql);
	 
					$i = 1;
					$total_income_tax = 0;
					$total_cpf = 0;
					$total_deduction = 0;
					$total_grand = 0;
					$grand_total_income = 0;
					while ($row = mysqli_fetch_array($result)) {
						$sql_amount_income_tax = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="' . $row['payslip_id'] . '" AND `head_id`="18" AND amount != "0"';
						$result_amount_income_tax = execute_query($sql_amount_income_tax);
						$row_amount_income_tax = mysqli_fetch_array($result_amount_income_tax);
					
						// Check if $row_amount_income_tax is not null before accessing its elements
						if ($row_amount_income_tax !== null) {
							$income_tax = $row_amount_income_tax['amount'];
						} else {
							// Handle the case when $row_amount_income_tax is null
							$income_tax = 0; // or any default value you prefer
						}
					
						if ($income_tax > 0 && $row['total'] >= 0) {
							$sql = 'SELECT * FROM dp_designation WHERE sno="' . $row['employee_designation'] . '"';
							$des = mysqli_query($db_erp, $sql);
							$des = mysqli_fetch_assoc($des);
					
							$sql = 'SELECT * FROM uprnss_division WHERE s_no="' . $row['company_id'] . '"';
							$row_div = mysqli_query($db_erp, $sql);
							$row_div = mysqli_fetch_assoc($row_div);
					
							$sql_amount_payable = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="' . $row['payslip_id'] . '" AND `head_id`="salary_amount"';
							$result_amount_payable = execute_query($sql_amount_payable);
							$row_amount_payable = mysqli_fetch_array($result_amount_payable);
					
							$sql_attendance = 'SELECT * FROM `attendance_details` WHERE `emp_id`="' . $row['emp_id'] . '" AND `salary_generation_month`="' . $row['for_month'] . '" AND `financial_year`="' . $row['financial_year'] . '"';
							$result_attendance = execute_query($sql_attendance);
							$row_attendance = mysqli_fetch_array($result_attendance);
					
							echo "<tr>";
							echo "<td style='border:2px solid black;'>" . $i++ . "</td>";
							echo "<td style='border:2px solid black;'>" . $row["employee_code"] . "</td>
								<td style='border:2px solid black;'>" . $row["employee_name"] . "</td>
								<td style='border:2px solid black;'>" . $des['designation'] . "</td>
								<td style='border:2px solid black;'>" . $row_div['division_name'] . "</td>
								<td style='border:2px solid black;'>" . $row["pan"] . "</td>
								<td style='border:2px solid black;'>" . $row["total_income"] . "</td>";
							$grand_total_income += $row["total_income"];
					
							$sql_head_cpf = 'SELECT * FROM `head_type` WHERE `head_type`="deduction"';
							$result_head_cpf = execute_query($sql_head_cpf);
							$cpf = 0;
							while ($row_head_cpf = mysqli_fetch_array($result_head_cpf)) {
								$sql_amount_cpf = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="' . $row['payslip_id'] . '" AND `head_id`="9"';
								$result_amount_cpf = execute_query($sql_amount_cpf);
								$row_amount_cpf = mysqli_fetch_array($result_amount_cpf);
								$cpf = $row_amount_cpf['amount'];
							}
							echo '<td style="border:2px solid black;">' . round($cpf) . '</td>';
					
							echo '<td style="border:2px solid black;">' . round($income_tax) . '</td>';
					
							echo "</tr>";
					
							$total_income_tax += $income_tax;
							$total_cpf += $cpf;
						}
					}
					
					?>
					<tr>
						<th colspan="6" style="text-align: right;border:2px solid black;">Total:</th>
						<th style="border:2px solid black;"><?php echo $grand_total_income; ?></th>
						<th style="border:2px solid black;"><?php echo round($total_cpf); ?></th>
						<th style="border:2px solid black;"><?php echo round($total_income_tax); ?></th>
					</tr>
				</tbody>
			</table>
		<?php } ?>
	</div>
	<?php
	page_footer();
	?>
	<script type="text/javascript">
		function select_employee() {
			var d_i = document.getElementById("company_name").value;
			if (d_i != "") {
				$.ajax({
					url: "new_ajax.php?company=" + d_i,
					data: "data",
					type: "post",
					success: function (resposedata) {
						$('#emp_name').html(resposedata);
					}
				});
			}
		}
	</script>