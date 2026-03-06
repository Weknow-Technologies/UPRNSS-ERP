<?php
	include("scripts/settings.php");
	page_header_start('Attendance Report');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
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
									<td>Division Name</td>
									<td>
										<select class="form-control"  name="company_name" id="company_name" onchange="select_employee();">
											<option value="">-Select All-</option>
											<?php 
												$sql = "SELECT * FROM `uprnss_division` order by division_name ASC";	
												$query = mysqli_query($db_erp,$sql);
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
									<td colspan="2"><input type="submit" id="submit" name="search" Value="Search" class="form-control btn btn-info"></td>
									<td colspan="2">
										<a href="report_attendance.php" class="form-control btn btn-info">Reset</a>
									</td>
								</tr>
								
							</thead>
						</table>
					</div>
				</form>
			</div>
		</div>
		<?php 
			 if(isset($_POST['search'])){ 
		?>
		<div class="col-sm-1"></div>
		<div class="col-sm-10">
			<div class="panel">
				<div class="panel-heading">Attendance Report</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<th>sno</th>
									<th>Division Name</td>
									<th>Employee</th>
									<th>Attendance Session</th>
									<th>Month</th>
									<th>Working Days</th>
									<th>Leave(Days)</th>
									<th>Present(Days)</th>
									<th class="no-print">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$sql ='SELECT * FROM `attendance_details` WHERE 1=1 ';
									if(isset($_POST['attendance_session'])){
										if ($_POST['attendance_session'] != '') {
											$sql .= ' AND `financial_year`="'.$_POST['attendance_session'].'"';
										}
									}
									if(isset($_POST['for_month'])){
										if ($_POST['for_month'] != '') {
											$sql .= ' AND `salary_generation_month`="'.$_POST['for_month'].'"';
										}
									}
									if(isset($_POST['company_name'])){
										if($_POST['company_name'] != ''){
											$sql .= ' AND `company_id`="'.$_POST['company_name'].'"';
										}
									}
									if(isset($_POST['emp_name'])){
										if($_POST['emp_name'] != ''){
											$sql .= ' AND `emp_id`="'.$_POST['emp_name'].'"';
										}
									}
									//echo $sql;
									$result=execute_query($sql);
									$t = 1;
									$working_days = 0;
									$granted_leave = 0;
									$presented_days = 0;
									while($row=mysqli_fetch_array($result)){
										$working_days += $row["working_days"];
										$granted_leave += $row['granted_leave'];
										$presented_days += $row['presented_days']; 
										$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$row['company_id'].'"'; 
										$result_company = mysqli_query($db_erp,$sql_company);
										$row_company = mysqli_fetch_array($result_company);
										$sql_employee = 'SELECT * FROM `employee` WHERE `sno`="'.$row['emp_id'].' order by division_name ASC"'; 
										$result_employee = execute_query($sql_employee);
										$row_employee = mysqli_fetch_array($result_employee);
										echo"<tr>";
										echo "<th>".$t++."</th>";
										echo "<td><b>".$row_company["division_name"]."</b></td>";
										echo "<td>".$row_employee["employee_name"]."</td>";
										echo "<td>".$row["financial_year"]."</td>";
										echo "<td>".date('F',strtotime('01.'.$row["salary_generation_month"].'.2020'))."</td>";
										echo "<td>".$row["working_days"]."</td>";
										echo "<td>".$row["granted_leave"]."</td>";
										echo "<td>".$row["presented_days"]."</td>";
										echo '<td class="no-print"><a target="_blank"   href="payroll_attendance_edit.php?e_id='.$row['sno'].'">Edit</a></td>';
										echo"</tr>";
									}
								?>
								<tr>
									<th colspan="5" style="text-align: right;">Total :</th>
									<th><?php echo $working_days; ?></th>
									<th><?php echo $granted_leave; ?></th>
									<th><?php echo $presented_days; ?></th>
									<th class="no-print">&nbsp;</th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
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