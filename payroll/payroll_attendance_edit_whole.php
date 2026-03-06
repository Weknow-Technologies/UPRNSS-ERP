<?php
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
		echo $sql='UPDATE `attendance_invoice` SET 
			`working_days`="'.$_POST['company_working_days'].'" , 
			`granted_leave`="'.$_POST['company_granted_days'].'" , 
			`edited_by`="'.$_SESSION['username'].'" , 
			`edition_time`="'.date('Y-m-d h:i:s').'" 
			WHERE `sno`="'.$_POST['edit_sno'].'"';
		execute_query($sql);
		//echo $sql.'<br/>';
		if(!mysqli_error($db)) {
			$msg = '<div class="alert alert-success">Data Update</div>';
			$qryemp='SELECT * FROM `attendance_details` WHERE `attendance_id`="'.$_POST['edit_sno'].'"';
			$resemp=execute_query($qryemp);
			while($rowemp=mysqli_fetch_array($resemp)){
				echo $sql_update = 'UPDATE `attendance_details` SET  `working_days`="'.$_POST['working_days_'.$rowemp['sno']].'" , `granted_leave`="'.$_POST['granted_leave_'.$rowemp['sno']].'" , `presented_days`="'.$_POST['present_'.$rowemp['sno']].'", `edited_by`="'.$_SESSION['username'].'", `edition_time`="'.date('Y-m-d h:i:s').'" WHERE `sno`="'.$rowemp['sno'].'"';
				execute_query($sql_update);
				//echo $sql_update.'<br/>';
			}
		} else {
			$msg = '<div class="alert alert-danger">Error # AH_001. '.mysqli_error($db).' >> '.$sql.'</div>';
		}
}	
page_header_start('Attendance Edit');
page_header_end();
navigation($_SERVER['PHP_SELF']);
?>
	<div class="container">
		<?php echo $msg; ?>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" >
			<div class="row"><div class="col-sm-12">&nbsp;</div></div>
			<div class="row">
				<div class="col-sm-12">
					<div class="panel">
						<div class="panel-heading">Attendance Edit</div>
						<div class="panel-body" id="total_calculation">
							<?php
								if (isset($_GET['e_id'])) {
									$n = 0;
									$sql_edit = 'SELECT * FROM `attendance_invoice` WHERE `sno`="'.$_GET['e_id'].'"';
									$result_edit = execute_query($sql_edit);
									$row_edit = mysqli_fetch_array($result_edit);
									
									$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$row_edit['company_id'].'"';
									$result_company = mysqli_query($db_erp, $sql_company);
									$row_company = mysqli_fetch_array($result_company);
								?>
							<div class="row" style="background-color: lightblue;font-size: 15px;">
								<div class="col-sm-7" style="font-size: 25px;">
									<b><?php echo $row_company['division_name']; ?></b>
									
								</div>	
								<div class="col-sm-5"><b>Number Of Working List:</b>
									
									<input type="number" oninput="fill_info();" name="company_working_days" id="company_working_days" class="form-control" placeholder="Enter The Working Days" value="<?php if(isset($_GET['e_id'])){echo $row_edit['working_days'];} ?>">
								</div>
								<!--<div class="col-sm-5"><b>Number Of Granted Leave:</b>
									<input type="number" name="company_granted_days" id="company_granted_days" class="form-control" placeholder="Enter The Granted Leave Days" onblur="fill_info();" value="<?php echo $row_edit['granted_leave'] ?>">-->
								</div>
							</div>
							<div class="row" style="font-size: 20px;text-align: center;">
								<div class="col-sm-1"><b>S.No.</b></div>
								<div class="col-sm-2"><b>Employee code</b></div>
								<div class="col-sm-3"><b>Employee Name</b></div>
								<div class="col-sm-2"><b>Working Days</b></div>
								
								<div class="col-sm-2"><b>Presented Days</b></div>
								<div class="col-sm-2"><b>Granted Leave</b></div>
							</div>
							<hr/>
								<?php
									$sql_employee = 'SELECT * FROM `attendance_details` 
									
									WHERE `attendance_id`="'.$row_edit['sno'].'"';
									$result_employee = execute_query($sql_employee);
									//echo $sql_employee;
									while($row_employee = mysqli_fetch_array($result_employee)){
										$sql = 'SELECT * FROM employee WHERE `sno`="'.$row_employee['emp_id'].'"';
										$result = execute_query($sql);
										$row_name = mysqli_fetch_array($result);
										?>
							<div class="row">
								<div class="col-sm-1"><h4><b><?php echo ++$n; ?>.</b></h4></div>
								<div class="col-sm-2"><h4><?php echo $row_name['employee_code']; ?></h4></div>
								<div class="col-sm-3"><h4><?php echo employee_name($row_employee['emp_id']); ?></h4></div>
								<div class="col-sm-2"><input type="number" name="working_days_<?php echo $row_employee['sno']; ?>" id="working_days_<?php echo $n; ?>" class="form-control" placeholder="Enter The Working Days" readonly value="<?php echo $row_employee['working_days']; ?>"></div>
								<div class="col-sm-2"><input type="number" oninput="fill_info2();" name="present_<?php echo $row_employee['sno']; ?>" id="present_<?php echo $n; ?>" class="form-control" placeholder="Enter The Presented Days" value="<?php echo $row_employee['presented_days']; ?>"></div>
								<div class="col-sm-2"><input type="number" name="granted_leave_<?php echo $row_employee['sno']; ?>" id="granted_leave_<?php echo $n; ?>" class="form-control" placeholder="Enter The Leave Days" value="<?php echo $row_employee['granted_leave']; ?>"></div>
								
							</div>
										<?php
									}
									?>
								<input type="hidden" id="nubmer_of_entry" name="nubmer_of_entry" value="<?php echo $n; ?>">
									<?php
								}
							?>
							
						</div>
						<input type="hidden" name="edit_sno" id="edit_sno" value="<?php echo $_GET['e_id']; ?>">
						<input type="submit" class="form-control" id="submit" name="submit" value="Submit">
					</div>
				</div>
			</div>
		</form>
	</div>
<?php
page_footer();
?>
<script type="text/javascript">
	function fill_info() {
		var nubmer_of_entry = document.getElementById('nubmer_of_entry').value;
		var working_days = document.getElementById('company_working_days').value;

		if (working_days > 0) {
			for (var i = 1; i <= nubmer_of_entry; i++) {
				$('#working_days_'+i).val(working_days);
				$('#present_'+i).val(working_days);
			}
		}
		// if (granted_leave > 0) {
			// for (var i = 1; i <= nubmer_of_entry; i++) {
				// $('#granted_leave_'+i).val(granted_leave);
			// }
		// }
		if (working_days > 0) {
			for (var i = 1; i <= nubmer_of_entry; i++) {
				var leave_day = document.getElementById('working_days_' + i).value - document.getElementById('present_' + i).value; 
				console.log(leave_day);

				$('#granted_leave_' + i).val(leave_day);
			}
		}
	}
	
	function fill_info2(){
		var nubmer_of_entry = document.getElementById('nubmer_of_entry').value;
		// var working_days = document.getElementById('company_working_days').value;
		
		// var grantleave=parseInt(document.getElementById('granted_leave_edit').value);
		
			for (var i = 1; i <= nubmer_of_entry; i++) {
				// var workingday=parseInt(document.getElementById('working_days_'+i).value);
				// var present= parseInt(document.getElementById('present_'+i).value);
			
				if(parseInt(document.getElementById('present_'+i).value)>parseInt(document.getElementById('working_days_'+i).value)){
					document.getElementById('submit').style.height="0";
					alert("Present Days is greater than Working Days");
					
					
				}else{
					document.getElementById('submit').style.height="fit-content";
				}
				document.getElementById('granted_leave_'+i).value=document.getElementById('working_days_'+i).value-document.getElementById('present_'+i).value;
				
				
				
			}
		
	}
	
</script>