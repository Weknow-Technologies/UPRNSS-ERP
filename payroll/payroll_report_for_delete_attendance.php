<?php
	include("scripts/settings.php");
	page_header_start('Report For Delete(Attendance)');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
	if (isset($_GET['del_id'])) {
		$sql_delete = 'DELETE FROM `attendance_invoice` WHERE `sno`="'.$_GET['del_id'].'"';
		$res = execute_query($sql_delete);
		if($res){
			$sql_delete_details = 'DELETE FROM `attendance_details` WHERE `attendance_id`="'.$_GET['del_id'].'"';
			execute_query($sql_delete_details);
			echo '<div class="alert alert-success">Deleted</div>';
		}
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
											$sql = "SELECT * FROM `uprnss_division`";	
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
										<a href="report_for_delete_attendance.php" class="form-control btn btn-info">Reset</a>
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
				<div class="panel-heading">Attendance Delete Report</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<th>S.No.</th>
									<th>Company Name</th>
									<th>Number Of Employee</th>
									<th>Attendance Session</th>
									<th>Month</th>
									<th class="no-print" colspan="2"></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if(isset($_POST['search'])){
									$sql_company = 'SELECT * FROM `attendance_invoice` WHERE `company_id`="'.$_POST['company_name'].'" AND `financial_year`="'.$_POST['attendance_session'].'" AND `salary_generation_month`="'.$_POST['for_month'].'"';
									$result_company = execute_query($sql_company);
										$t = 1;
										while($row_company=mysqli_fetch_array($result_company)){
											$sql = 'SELECT * FROM `attendance_details` WHERE `attendance_id`="'.$row_company['sno'].'"';
											$result = execute_query($sql);
											$num = mysqli_num_rows($result);
											
											$sql = "SELECT * FROM `uprnss_division` where s_no={$row_company["company_id"]}";	
											$query = mysqli_query($db_erp, $sql);
											$row_com_name = mysqli_fetch_array($query);
											
											echo"<tr>";
											echo '<th>'.$t++.'</th>';
											echo "<td><b>".$row_com_name["division_name"]."</b></td>";
											echo "<td>".$num."</td>";
											echo "<td>".$row_company["financial_year"]."</td>";
											echo "<td>".date('F',strtotime('01.'.$row_company["salary_generation_month"].'.2020'))."</td>";
											echo '<td class="no-print"><a href="payroll_attendance_edit_whole.php?e_id='.$row_company['sno'].'" target="_blank">Edit</a></td>';
											echo '<td class="no-print"><a href="payroll_report_for_delete_attendance.php?del_id='.$row_company['sno'].'">Delete</a></td>';
											echo"</tr>";
										}
									}
									?>
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