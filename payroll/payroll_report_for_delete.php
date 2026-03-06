<?php
	include("scripts/settings.php");
	page_header_start('Report For Delete(Payslip)');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
	if (isset($_POST['delete'])) {
		//echo 'a<br/>';
		for ($i=1; $i < $_POST['total_check'] ; $i++) {
		//echo 'b<br/>'; 
			if(isset($_POST['check_'.$i])){
				//echo 'c<br/>';
				$sql_delete = 'DELETE FROM `payslip_report` WHERE `sno`="'.$_POST['check_sno_'.$i].'"';
				$res = execute_query($sql_delete);
				if($res){
					//echo 'd<br/>';
					$sql_delete_details = 'DELETE FROM `payslip_details` WHERE `payslip_id`="'.$_POST['check_sno_'.$i].'"';
					execute_query($sql_delete_details);
					
				}
			}
		}
		echo '<h2>Deleted</h2>';
	}
?>
<div class="container">
	<div class="row">
		<h1 class="print-only">Payroll Bareilly</h1>
		<div class="no-print">
			<div class="panel">
				<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"  name="confirm" enctype="multipart/form-data">
					<div class="panel-heading">
						Search
					</div>
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
									<td>Attendace Session</td>
									<td>
										<select name="attendance_session" class="form-control">
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
													<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['attendance_session'])){if($_POST['attendance_session']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
													<?php
												}
											?>
										</select>
									</td>
									<td>For Month</td>
									<td>
										<select class="form-control"  name="for_month" id="title">
											<?php
											$month=1;
											$current_month = date('m');

											while($month<=12){
												echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
												if(isset($_POST['for_month'])){
													if($_POST['for_month']==$month){
														echo 'selected="selected"';
													}
												}
												else{
													if($month == $current_month){
														echo 'selected="selected"';
													}
												}
												echo '>'.date('F',strtotime('01.'.$month.'.2020')).'</option>';
												$month++;
											}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="3"><input type="submit" id="submit" name="search" Value="Search" class="form-control btn btn-info"></td>
									<td colspan="3">
										<a href="report_for_delete.php" class="form-control btn btn-info">Reset</a>
									</td>
								</tr>
								
							</thead>
						</table>
					</div>
				</form>
			</div>
		</div>

		<div class="col-sm-1"></div>
		<div class="col-sm-10">
			<div class="panel">
				<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"  name="confirm" enctype="multipart/form-data">
				<div class="panel-heading">Payslip Delete Report</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<th>S.No.</th>
									<th>Company Name</th>
									<th>Name Of Employee</th>
									<th>Attendance Session</th>
									<th>Month</th>
									<th class="no-print">Check All&nbsp; &nbsp;<input type="checkbox" name="check_all" id="check_all" onclick="check_all_data();"></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if(isset($_POST['search'])){
									// $sql_company = 'SELECT * FROM `company_details` WHERE `sno`="'.$_POST['company_name'].'"';
									// $result_company = execute_query($sql_company);
									
									$sql = 'SELECT * FROM `uprnss_division`  where s_no = "'.$_POST['company_name'].'" order by division_name ASC';	
									$result_company = mysqli_query($db_erp, $sql);
									
									$row_company = mysqli_fetch_array($result_company);
									$sql_employee = 'SELECT * FROM `employee` WHERE `company_id`="'.$row_company['s_no'].'"';
									$result_employee = execute_query($sql_employee);
										$t = 1;
										while($row_employee=mysqli_fetch_array($result_employee)){
											$sql ='SELECT * FROM `payslip_report` WHERE `financial_year`="'.$_POST['attendance_session'].'" AND `for_month`="'.$_POST['for_month'].'" AND `emp_id`="'.$row_employee['sno'].'" ';
										//echo $sql;
											$result=execute_query($sql);
											while($row = mysqli_fetch_array($result)){
											echo"<tr>";
											echo '<th><input type="hidden" name="check_sno_'.$t.'" id="check_sno_'.$t.'" value="'.$row['sno'].'">'.$t.'</th>';
											echo "<td><b>".$row_company["division_name"]."</b></td>";
											echo "<td>".$row_employee["employee_name"]."</td>";
											echo "<td>".$row["financial_year"]."</td>";
											echo "<td>".date('F',strtotime('01.'.$row["for_month"].'.2020'))."</td>";
											echo '<td class="no-print"><input type="checkbox" name="check_'.$t.'" id="check_'.$t.'"></td>';
											echo"</tr>";
											$t++;
											}
										}
										echo '<input type="hidden" name="total_check" id="total_check" value="'.$t.'">';
									}
									?>
									<tr>
										<th colspan="5">&nbsp;</th>
										<th><input type="submit" name="delete" value="Delete" class="form-control btn btn-danger"></th>
									</tr>
							</tbody>
						</table>
					</div>
				</div>
				</form>
			</div>
		</div>
	</div>
<?php
page_footer();
?>
<script type="text/javascript">
	function check_all_data(){
		var n = document.getElementById('total_check').value;
		//alert(n);
		for(var i=1; i<n; i++){
			if($('#check_all').prop("checked") == true){
                $('#check_'+i).prop('checked', true);
            }
            else{
            	$('#check_'+i).prop('checked', false);
            }
		}
	}
</script>