<?php
	include("scripts/settings.php");
	page_header_start('Bonus Report');
$msg = '';
if (isset($_GET['del_id'])) {
	$sql_delete = 'DELETE FROM `bonus_invoice` WHERE `sno`="'.$_GET['del_id'].'"';
	$res_delete = execute_query($sql_delete);
	if($res_delete){
		$sql_delete_detail = 'DELETE FROM `bonus_details` WHERE `bonus_id`="'.$_GET['del_id'].'"';
		$res_delete_detail = execute_query($sql_delete_detail);
		if ($res_delete_detail) {
			$msg .= '<div class="col-sm-12 alert-danger">Record Deleted.</div>';
		}
	} 
}
?>
<style type="text/css">
	table thead tr th{
		text-align: center;
	}
</style>
<?php
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
?>
<div class="container">
	<div class="row">
		<?php echo $msg; ?>
	</div>
	<div class="row">
		<div class="no-print">
			<div class="panel">
				<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"  name="confirm" enctype="multipart/form-data">
					<div class="panel-heading">Search</div>
					<div class="panel-body">
						<span style="float: right;">
							<a onclick="printPage()" class="no-print btn btn-info">Print this page</a>
						</span>
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<td>Company Name</td>
									<td>
										<select class="form-control"  name="company_name" id="company_name" onchange="select_employee();">
											<option value="">-Select All-</option>
											<?php 
												$sql = "SELECT * FROM `company_details`";	
												$query = execute_query($sql);
												 while ($row = mysqli_fetch_array($query)){ 
													echo '<option value="'.$row['sno'].'" ';
													if(isset($_POST['company_name'])){
														if($row['sno']==$_POST['company_name']){
															echo 'selected="selected"';
														}
													}
													echo '>'. $row['company_name'] . "</option>";
												}?>
											</select>
									</td>
									<!--<td>Employee Name</td>
									<td><select class="form-control"  name="emp_name" id="emp_name">
										<option value="">-Select All-</option>

										<?php 
										$sql = 'SELECT * FROM `employee`';	
										$result = execute_query($sql);
										while ($row = mysqli_fetch_array($result)){ 
											echo '<option value="'.$row['sno'].'" ';
											if(isset($_POST['emp_name'])){
												if($row['sno']==$_POST['emp_name']){
													echo 'selected="selected"';
												}
											}
											echo '>' . $row['employee_name'] . "</option>";

										}?>
										</select>
									</td>-->
									<td>Financial Year</td>
									<td>
										<select name="financial_year" class="form-control">
											<?php  
												if(date('m')>3){
													$select_start_session = date('Y');
												}
												else{
													$select_start_session = date('Y')-1;
												}
												$session_start = date('Y')-50;
												for($i = $session_start; $i<=$session_start+100;$i++){
													$end_session = $i+1;
													?>
													<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['financial_year'])){if($_POST['financial_year']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
													<?php
												}
											?>
										</select>
									</td>
								</tr>
								
							</thead>
						</table>
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" name="search" id="search" value="SEARCH" class="form-control btn btn-info">
							</div>
							<div class="col-sm-6">
								<a href="<?php echo $_SERVER['PHP_SELF'];?>" class="form-control btn btn-info">Reset</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php 
			if (!isset($_GET['view_id'])) {
		?>
		<h1 class="print-only">Payroll Bareilly</h1>
		<div class="col-sm-12">
			<div class="panel">
				<div class="panel-heading">Bonus Report</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<th>S.No.</th>
									<th>Company Name</th>
									<!--<th>Employee Name</th>-->
									<th>Financial Year</th>
									<th>Bonus Amount</th>
									<th>View</th>
									<th>Delete</th>
								</tr>
							</thead>
							<?php 
								if(isset($_POST['search'])){
							?>
							<tbody>
								<?php 
									$n = 1;
									$grand_total = 0;
									$sql_invoice = 'SELECT * FROM `bonus_invoice` WHERE 1=1 ';
									if(isset($_POST['search'])){
										if($_POST['company_name'] != ''){
											$sql_invoice .= ' AND `company_id`="'.$_POST['company_name'].'" ';
										}
										if($_POST['financial_year'] != ''){
											$sql_invoice .= ' AND `financial_year`="'.$_POST['financial_year'].'" ';
										}
									}
									else{
										$sql_invoice .= ' AND `financial_year`="2020-2021" ';
									}
									$result_invoice = execute_query($sql_invoice);
									while($row_invoice = mysqli_fetch_array($result_invoice)){
										$sql_company = 'SELECT * FROM `company_details` WHERE `sno`="'.$row_invoice['company_id'].'"';
										$result_company = execute_query($sql_company);
										$row_company = mysqli_fetch_array($result_company);
										/**$sql_details = 'SELECT * FROM `bonus_details` WHERE `bonus_id`="'.$row_invoice['sno'].'" ';
										if(isset($_POST['emp_name'])){
											if($_POST['emp_name'] != ''){
												$sql_details .= ' AND `emp_id`="'.$_POST['emp_name'].'" ';
											}
										}
										$result_details = execute_query($sql_details);
										while($row_details = mysqli_fetch_array($result_details)){
											$sql = 'SELECT `employee`.`employee_name` , `company_details`.`company_name` FROM `employee` JOIN `company_details` ON `company_details`.`sno`=`employee`.`company_id` WHERE `employee`.`sno`="'.$row_details['emp_id'].'"';
											$result = execute_query($sql);
											$row = mysqli_fetch_array($result);**/
											?>
								<tr>
									<th><?php echo $n++; ?></th>
									<th><?php echo $row_company['company_name']; ?></th>
									<!--<td><?php echo $row['employee_name']; ?></td>-->
									<td><?php echo $row_invoice['financial_year']; ?></td>
									<td><?php echo round($row_invoice['invoice_amount']); ?></td>
									<td>
										<a href="report_bonus.php?view_id=<?php echo $row_invoice['sno']; ?>">View</a>
									</td>
									<td>
										<a href="javascript:AlertIt(<?php echo $row_invoice['sno']; ?>);">Delete</a>
									</td>
								</tr>
											<?php
											$grand_total += $row_invoice['invoice_amount'];
									}
								?>
								<tr>
									<th colspan="3" style="text-align: right;">Total :</th>
									<th><?php echo round($grand_total); ?></th>
									<th colspan="2">&nbsp;</th>
								</tr>
							</tbody>
						<?php } ?>
						</table>
					</div>
				</div>
			</div>
			<?php 
				}
			else{
				$sql_bonus_invoice = 'SELECT * FROM `bonus_invoice` WHERE `sno`="'.$_GET['view_id'].'"';
				$result_bonus_invoice = execute_query($sql_bonus_invoice);
				$row_bonus_invoice = mysqli_fetch_array($result_bonus_invoice);
				$sql_company_bonus = 'SELECT * FROM `company_details` WHERE `sno`="'.$row_bonus_invoice['company_id'].'"';
				$result_company_bonus = execute_query($sql_company_bonus);
				$row_company_bonus = mysqli_fetch_array($result_company_bonus);
				?>
			<div class="col-sm-12">
				<div class="panel">
					<div class="panel-heading">
						<table width="100%" style="border: 0px;">
							<tr>
								<td colspan="4" style="text-align: center;border: 0px;"><b><u>Bonus Details</u></b></td>
							</tr>
							<tr>
								<td style="border: 0;"><b>Company Name : <?php echo $row_company_bonus['company_name']; ?></b></td>								
								<td style="text-align: right;border: 0;"><b>Financial Year:<?php echo $row_bonus_invoice['financial_year']; ?></b></td>
							</tr>
							<tr>
								<td style="border: 0;">Company Address : <?php echo $row_company_bonus['address']; ?></td>
								<td style="border: 0;">&nbsp;</td>
							</tr>
						</table>
					</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<th>S.No.</th>
									<th>Name of the Employee</th>
									<th>Father's Name</th>
									<th>Designation</th>
									<th>Total Salary Amount</th>
									<th>No. of Days Worked in the year</th>
									<th>Total Payable Amount</th>
									<th>Amount For Bonus Calculation</th>
									<th>Total Bonus Amount</th>
									<th>Date On Which Paid</th>
									<th>Signature Or Thumb Impression Of The Employee</th>
								</tr>
							</thead>
							<tbody>
							<?php 
								$n = 1;
								$grand_total = 0;
								$sql_bonus_details = 'SELECT * FROM `bonus_details` WHERE `bonus_id`="'.$_GET['view_id'].'"';
								$result_bonus_details = execute_query($sql_bonus_details);
								while ($row_bonus_details = mysqli_fetch_array($result_bonus_details)) {
									$sql_employee_bonus = 'SELECT * FROM `employee` WHERE `sno`="'.$row_bonus_details['emp_id'].'"';
									$row_employee_bonus = mysqli_fetch_array(execute_query($sql_employee_bonus));
									echo '<tr><th>'.$n++.'</th><td>'.$row_employee_bonus['employee_name'].'</td><td>'.$row_employee_bonus['father_name'].'</td><td>'.$row_employee_bonus['employee_designation'].'</td><td>'.$row_bonus_details['salary_amount'].'</td><td>'.$row_bonus_details['presented_days'].'</td><td>'.$row_bonus_details['payable_salary_amount'].'</td><td>'.$row_bonus_details['amount_for_bonus'].'</td><td>'.round($row_bonus_details['bonus_amount']).'</td><td></td><td></td></tr>';
									$grand_total += $row_bonus_details['bonus_amount'];
								}
							?>
								<tr>
									<th colspan="8" style="text-align: right;">Total :</th>
									<th><?php echo round($grand_total); ?></th>
									<th colspan="2">&nbsp;</th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
				<?php
			}
			?>
		</div>
	</div>
<?php
page_footer();
?>
<script type="text/javascript">
	function select_employee(){
		var d_i = document.getElementById("company_name").value;
		if (d_i != "") {
				$.ajax({
				url: "new_ajax.php?company="+d_i,
				data: "data",
				type: "post",
				success: function(resposedata){
				$('#emp_name').html(resposedata);
			}
			});
		}
	}
</script>
<script type="text/javascript">
	function AlertIt(id) {
		var answer = confirm ("Are You Sure.")
		if (answer)
		window.location="report_bonus.php?del_id="+id;
	}
</script>