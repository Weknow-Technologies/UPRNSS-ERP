<?php
	include("scripts/settings.php");
	page_header_start('Attendance Report');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
?>
<div class="container">
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
	<div class="row">
		<div class="col-sm-1"></div>
		<div class="col-sm-10">
			<div class="panel">
				<div class="panel-heading">Attendance Report</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">
							<tr>
								<td>Select Head</td>
								<td>
									<select class="form-control"  name="head_name" id="title">
									<option value="">Select</option>

									<?php 
									$sql = 'SELECT `sno`, `head_name` FROM `head_type`';	
									$result = execute_query($sql);
									while ($row = mysqli_fetch_array($result)){ 
										echo '<option value="'.$row['sno'].'" ';
										if(isset($_POST['head_name'])){
											if($row['sno']==$_POST['head_name']){
												echo 'selected="selected"';
											}
										}
										echo '>' . $row['head_name'] . "</option>";

									}?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2"><input type="submit" name="submit" value="Submit" class="form-control"></td>
							</tr>
						</table>
						<?php if(isset($_POST['submit'])){?>
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<th>Sno</th>
									<th>Employee</th>
									<th>Start Date</th>
									<th>End date</th>
									<th>For Month</th>
									<th>Amount</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$sql ="SELECT payslip_details.sno as sno, employee_name, start_from, end_to, for_month, amount FROM `payslip_details` join payslip_report on payslip_report.sno = payslip_details.payslip_id join employee on employee.sno = employee_id where head_id=".$_POST['head_name'];	
								//echo $sql;
								$result = execute_query($sql);
								$i=1;
								$tot=0;
								while($row=mysqli_fetch_array($result)){ 
									echo"<tr>";
									echo "<td>".$i++."</td>";
									echo "<td>".$row["employee_name"]."</td>";
									echo "<td>".$row["start_from"]."</td>";
									echo "<td>".$row["end_to"]."</td>";
									echo "<td>".$row["for_month"]."</td>";
									echo "<td>".$row["amount"]."</td>";
									echo"</tr>";
									$tot += $row['amount'];
								}
								echo '<tr><td colspan="5" class="text-right">Total : </td><td>'.$tot.'</td></tr>';
								?>
								
							</tbody>
						</table>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
page_footer();
?>