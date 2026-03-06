<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;
	if(isset($_POST['submit'])){
		
		$qryemp='SELECT employee.sno as sno, head_id, company_id,working_status, employee_category_id, head_value,emp_id  FROM employee 
		LEFT JOIN salary_structure  ON employee.sno = salary_structure.emp_id
		WHERE `company_id`="'.$_POST['company_id'].'" AND `working_status`!="1" AND head_id="18"';
		if($_POST['employee_category_id']!=''){
			$qryemp .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
		}
		// echo $qryemp;
		$resemp=execute_query($qryemp);
			while($rowemp=mysqli_fetch_array($resemp)){
				$sql_update = 'UPDATE `salary_structure` 
				SET 
					`head_value` = "'.$_POST['head_value_'.$rowemp['sno']].'"
				WHERE 
					`head_id` = "18" AND `emp_id` = "'.$rowemp['sno'].'"';

				execute_query($sql_update);
				//echo $sql_insert.'<br/>';
				if(mysqli_error($db)){
					$msg .= '<div class="alert alert-danger">Error # AE002 : '.mysqli_error($db).' >> '.$sql_update.'</div>';
				}
				else{
					$msg1 = '<div class="alert alert-success">Data Saved</div>';
				}
			}
			if($msg1!=""){
				$msg = '<div class="alert alert-success">Update Income Tax</div>';
			}	
	}	
page_header_start('Income Tax Update');
page_header_end();
navigation($_SERVER['PHP_SELF']);
?>
	<div class="container">
		<?php echo $msg; ?>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" >
			<div class="row">
				<div class="col-sm-3"></div>
				<div class="col-sm-6">
					<div class="panel">
						<div class="panel-heading">Attendance</div>
						<div class="panel-body">
							<table class="table table-hover table-responsive table-bordered table-striped">

								<tr>
									<td>Select Division</td>
									<td>
										<select required  class="form-control"  name="company_id" id="company_id">
										<?php 
											$sql = "SELECT * FROM `uprnss_division` order by division_name ASC";	
												$query = mysqli_query($db_erp, $sql);
											 while ($row = mysqli_fetch_array($query)){
											 	?>
										<option value="<?php echo $row['s_no']; ?>" <?php if(isset($_POST['calculate'])){if($_POST['company_id']==$row['s_no']){echo 'selected';}} ?>><?php echo $row['division_name']; ?></option>
											 	<?php
											}?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Employee Type</td>
									<td>
										<select required name="employee_category_id" id="employee_category_id" class="form-control" tabindex="<?php echo $tab++; ?>" >
										<?php 
											$sql_details = 'SELECT * FROM `dp_category`';
											$result_details = mysqli_query($db_erp, $sql_details);
											while($row_details = mysqli_fetch_array($result_details)){
												?>
										
										
										<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_POST['calculate'])){if($_POST['employee_category_id']==$row_details['sno']){echo 'selected';}} ?>><?php echo $row_details['category_name']; ?></option>
												<?php
											}
										?>
									</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<?php if(!isset($_POST['calculate'])){ ?>
					<input type="submit" class="form-control btn btn-info" id="calculate" name="calculate" value="Search">	
				<?php } ?>
				</div>
			</div>
			<div class="row"><div class="col-sm-12">&nbsp;</div></div>
			<div class="row">
				<div class="col-sm-12">
					<div class="panel">
						<div class="panel-heading">Result</div>
						<div class="panel-body" id="total_calculation">
							<?php
								if (isset($_POST['calculate'])) {
									$n = 0;
									$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_id'].'"';
									$result_company = mysqli_query($db_erp, $sql_company);
									$row_company = mysqli_fetch_array($result_company);
									?>
									<div class="row" style="background-color: lightblue;font-size: 15px;">
										<div class="col-sm-7" style="font-size: 25px;">
											<b><?php echo $row_company['division_name']; ?></b>
										</div>	
									</div>
									<div class="row" style="font-size: 20px;text-align: center;">
										<div class="col-sm-1"><b>S.No.</b></div>
										<div class="col-sm-3"><b>Employee code</b></div>
										<div class="col-sm-3"><b>Employee Name/ Designation</b></div>
										<div class="col-sm-4"><b>Income Tax</b></div>
											
									</div>
									<hr/>
									<?php
									$sql_employee = 'SELECT employee.sno as sno, employee_name, employee_code,  head_id, company_id,working_status, employee_category_id, head_value,emp_id, employee_designation  FROM employee 
									LEFT JOIN salary_structure  ON employee.sno = salary_structure.emp_id
									 WHERE `company_id`="'.$_POST['company_id'].'" AND `working_status`!="1" AND head_id="18"';
									
									if($_POST['employee_category_id']!=''){
										$sql_employee .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
									}
									$sql_employee .= ' ORDER BY ABS(head_value) DESC';
									// echo $sql_employee;
									$result_employee = execute_query($sql_employee);
									while($row_employee = mysqli_fetch_array($result_employee)){
										
										$sql = 'SELECT * FROM `dp_designation` WHERE `sno`="'.$row_employee['employee_designation'].'"';
										$result = mysqli_query($db_erp, $sql);
										$row_des = mysqli_fetch_array($result);
										?>
										<div class="row">
											<div class="col-sm-1">
											<h4><b><?php echo ++$n; ?>.</b></h4></div>
											<div class="col-sm-3">
												<h4><?php echo $row_employee['employee_code']; ?></h4>
											</div>
											<div class="col-sm-3">
												<h4><?php echo htmlspecialchars($row_employee['employee_name']); ?></h4>
												<h5><b><?php echo htmlspecialchars($row_des['designation']); ?></b></h5>
											</div>
	
											<div class="col-sm-4">
												<input  type="number" name="head_value_<?php echo $row_employee['sno']; ?>" id="head_value_<?php echo $row_employee['sno']; ?>" value="<?php echo $row_employee['head_value']; ?>" class="form-control" placeholder="Enter Income Tax">
											</div>
										</div>
										<hr>
										<?php
									}
									?>
									<input type="hidden" id="nubmer_of_entry" name="nubmer_of_entry" value="<?php echo $n; ?>">
									<?php
								}
							?>
							
						</div>
						
						<input type="submit" class="form-control" id="submit" name="submit" value="Submit">
					</div>
				</div>
			</div>
		</form>
	</div>

<?php
page_footer();
?>
