<?php
	include("scripts/settings.php");
	page_header_start('Employee Report');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
	
	$msg ="";
	if(isset($_GET['del'])){
	$var=$_GET['del'];
	//echo $var;
	//0 for working and 1 for not-working.... 
	// $sql ="delete `employee` SET `working_status`='1' WHERE `sno`='$var'";
	$sql ="DELETE FROM employee WHERE `employee`.`sno` = '$var'";
	$result = execute_query($sql);
	if ($result == true) {
		$sql ="DELETE FROM salary_structure WHERE emp_id = '$var'";
		$result1 = execute_query($sql);
		
		if ($result1 == true) {
			$msg='<div class="alert alert-success">Data Deleted !</div>';
		}
	}
	else{
			$msg='<div class="alert alert-danger">Sorry !</div>';
	}
	
}
?>
<div class="container">
	<div class="row">
		<h1 class="print-only">Payroll Bareilly</h1>
		<div class="no-print">
			<div class="panel">
				<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"  name="confirm" enctype="multipart/form-data">
					<div class="panel-heading">Search</div>
					<?php echo $msg; ?>
					<div class="panel-body">
						<span style="float: right;">
							<a onclick="printPage()" class="no-print btn btn-info">Print this page</a>
						</span>
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<td>Division Name</td>
									<td>
										<select class="form-control"  name="company_name" id="company_name" onchange="select_employee();">
											<option value="">-Select All-</option>
											<?php 
												$sql = "SELECT * FROM `uprnss_division` order by division_name ASC";	
												$query = mysqli_query($db_erp, $sql);
												 while ($row = mysqli_fetch_array($query)){ 
													echo '<option value="'.$row['s_no'].'" ';
													if(isset($_POST['company_name'])){
														if($row['s_no']==$_POST['company_name']){
															echo 'selected="selected"';
														}
													}
													echo '>'. $row['division_name'] . "</option>";
												}?>
											</select>
									</td>
									<td>Employee Type</td>
									<td>
										<select name="employee_category_id" id="employee_category_id" class="form-control" tabindex="<?php echo $tab++; ?>" >
										<option value="">-Select All-</option>
										<?php 
											$sql_details = 'SELECT * FROM `dp_category`';
											$result_details = mysqli_query($db_erp, $sql_details);
											while($row_details = mysqli_fetch_array($result_details)){
												?>
										<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_GET['edit_id'])){if($row_details['sno']==$editrow['employee_category_id']){echo 'selected';}} ?>><?php echo $row_details['category_name']; ?></option>
												<?php
											}
										?>
									</select>
									</td>
								</tr>
								<tr>
									<td>Employee Designation: </td>
									<td>
										<select name="employee_designation_id" id="employee_designation_id" class="form-control" tabindex="<?php echo $tab++; ?>" >
											<option value="">-SELECT-</option>
											<?php 
												// $sql_details = 'SELECT * FROM `employee_designation`';
												$sql_details = 'select * from dp_designation order by abs(sort_no)';
												$result_details = mysqli_query($db_erp, $sql_details);
												while($row_details = mysqli_fetch_array($result_details)){
													?>
											<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_GET['edit_id'])){if($row_details['sno']==$editrow['employee_designation']){echo 'selected';}} ?>><?php echo $row_details['designation']; ?></option>
													<?php
												}
											?>
										</select>
									</td>
									<td>Employee Name</td>
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
									</td>
								</tr>
								<tr>
									<td colspan="2"><input type="submit" id="submit" name="search" Value="Search" class="form-control btn btn-info"></td>
									<td colspan="2">
										<a href="payroll_employee_report.php" class="form-control btn btn-info">Reset</a>
									</td>
								</tr>
								
							</thead>
						</table>
					</div>
				</form>
			</div>
		</div>
	</div>
		</div>
		<?php 
			 if(isset($_POST['search'])){ 
		?>
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-heading">Employee Report</div>
					<div class="panel-body" style="overflow: scroll;width: auto;">
						<table class="table table-hover table-responsive table-bordered table-striped">
							<thead>
								<tr class="panel-heading">
								<th class="no-print">&nbsp;</th>
								<th>Sno</th>
								<th>Emp Code</th>
								<th>Emp Name</th>
								<th>Emp Division</th>
								<th>Emp Designation</th>
								<th>Pay Level</th>
								<th>Aadhar</th>
								<th>PAN</th>
								<th>Acount no</th>
								<th>Bank name</th>
								<th>Branch Name</th>
								<th>ifsc code</th>
								<th>UAN No</th>
								<th>Emp Erp id</th>

								</tr>
							</thead>
							<tbody>
								<?php 
									$sql="SELECT * FROM `employee` WHERE 1=1 ";
									if (isset($_POST['company_name'])) {
										if ($_POST['company_name'] != '') {
											$sql .= ' AND `company_id`="'.$_POST['company_name'].'"';
										}
									}
									if (isset($_POST['emp_name'])) {
										if ($_POST['emp_name'] != '') {
											$sql .= ' AND `sno`="'.$_POST['emp_name'].'"';
										}
									}
									if($_POST['employee_category_id']!=''){
										$sql .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
									}
									if($_POST['employee_designation_id']!=''){
										$sql .= ' and employee_designation="'.$_POST['employee_designation_id'].'"';
									}
									$result = execute_query($sql);
									$i=1;
									 while($row=mysqli_fetch_array($result)){
									 	$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$row['company_id'].'"';
									 	$result_company = mysqli_query($db_erp, $sql_company);
									 	$row_company = mysqli_fetch_array($result_company);
										 if($row['working_status']=='1'){
											 $col = '#F90';
										 }
										 else{
											 $col = '';
										 }
										 
										$sql_details = 'select * from dp_designation where sno="'.$row['employee_designation'].'"  order by abs(sort_no)';
										$row_designation = mysqli_query($db_erp, $sql_details);
										// $row_designation = execute_query($sql);
										if(mysqli_num_rows($row_designation)!=0){
											$row_designation = mysqli_fetch_assoc($row_designation);
										}
										else{
											unset($row_designation);
											$row_designation['designation'] = '';
										}
										$sql = 'select * from master_pay_level where sno="'.$row['pay_level_id'].'"';
										$row_level = mysqli_query( $db_erp ,$sql);
										if(mysqli_num_rows($row_level)!=0){
											$row_level = mysqli_fetch_assoc($row_level);
										}
										else{
											unset($row_level);
											$row_level['level_name'] = '';
										}
										 echo '<tr style="background:'.$col.'">
										<td class="no-print" ><a target="_blank" href="payroll_admin_employees.php?edit_id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a></td>
										<th>'.$i++.'</th>
										<td>'. $row['employee_code'].'</td>
										<td>'. $row['employee_name'].'</td>
										<td>'. $row_company['division_name'].'</td>
										<td>'. $row_designation['designation'].'</td>
										<td>'. $row_level['level_name'].'</td>
										<td>'. $row["aadhar"].'</td>
										<td>'. $row["pan"].'</td>
										<td>'. $row["acount_number"].'</td>
										<td>'. $row["bank_name"].'</td>
										<td>'. $row["branch"].'</td>
										<td>'. $row["ifsc_code"].'</td>
										<td>'. $row["esic_no"].'</td>
										<td>'. $row["dp_name_id"].'</td>
										';
									}
									
								?>
							</tbody>
							<?php  //echo '<div class="alert alert-danger">Alredy Save !</div>';
							?>
						</table>
					</div>
				</div>
			</div>
		<?php } ?>

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