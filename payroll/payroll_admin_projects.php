<?php 
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$id=$_POST['edit_sno'];
		$client_id=$_POST['client_id'];
		$project_name=$_POST['project_name'];
		$rate=$_POST['rate'];
		$start_date=$_POST['start_date'];
		$rate_type=$_POST['rate_type'];
		$over_time_rate =$_POST['over_time_rate'];
		$over_time_days_rate=$_POST['over_time_days_rate'];
		$full_night_rate = $_POST['full_night_rate'];
		$sql="UPDATE `projects` SET `client_id` ='$client_id', `project_name`='$project_name', `rate`='$rate', `rate_type`='$rate_type', `over_time_rate`='$over_time_rate', `full_over_time_days_rate`='$over_time_days_rate', `full_night_rate`='$full_night_rate', `start_date`='$start_date' WHERE sno='".$id."'";
		execute_query($sql);
		if ($db->query($sql) === TRUE) {
			$msg='<div class="alert alert-success">New Record Updated Successfully!</div>';
		} 
		else{
			$msg = '<div class="alert alert-danger">Error # AH_001. '.mysqli_error($db).' >> '.$sql.'</div>';
		}
	}
	else{
		$client_name=$_POST['client_id'];
		$project_name=$_POST['project_name'];
		$rate=$_POST['rate'];
		$rate_type=$_POST['rate_type'];
		$over_time_rate=$_POST['over_time_rate'];
		$over_time_days_rate=$_POST['over_time_days_rate'];
		//echo $over_time_days_rate;
		$full_night_rate = $_POST['full_night_rate'];
		$start_date=$_POST['start_date'];
		$sql="INSERT INTO `projects`(`client_id`, `project_name`, `rate`, `rate_type`, `over_time_rate`,  `full_over_time_days_rate`,  `full_night_rate`, `start_date`) VALUES 
		('$client_name','$project_name','$rate','$rate_type','$over_time_rate', '$over_time_days_rate', '$full_night_rate', '$start_date')";
		execute_query($sql);
		if(!mysqli_error($db)) {
			$msg = '<div class="alert alert-success">Data Saved Successfully</div>';
		} else {
			$msg = '<div class="alert alert-danger">Error # AH_002. '.mysqli_error($db).' >> '.$sql.'</div>';
		}
	}
}

if(isset($_GET['id'])){
	$var=$_GET['id'];
	$sql ="select * from projects where sno='".$var."'";
	$result = execute_query($sql);
	$row_edit = mysqli_fetch_array($result);
	$client_id=$row_edit['client_id'];
	$project_name=$row_edit['project_name'];
	$rate=$row_edit['rate'];
	$start_date=$row_edit['start_date'];
	$rate_type=$row_edit['rate_type'];
	$over_time_days_rate=$row_edit['full_over_time_days_rate'];
	$over_time_rate=$row_edit['over_time_rate'];
	$full_night_rate=$row_edit['full_night_rate'];
	$over_time_days_rate=$row_edit['full_over_time_days_rate'];
}

if(isset($_GET['del'])){
	$var=$_GET['del'];
	//echo $var;
	
	$sql ="delete from projects where sno='$var'";
	$result = execute_query($sql);
	if ($result == true) {
		$msg='<div class="alert alert-success">Data Deleted !</div>';
	}
	else {
	    	$msg='<div class="alert alert-danger">Sorry !</div>';
	}
		
}
page_header_start('Projects Master');
page_header_end();
navigation($_SERVER['PHP_SELF']);

?>
<div class="container">
	<?php echo $msg; ?>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
		<div class="row">
			
			<div class="col-sm-3"></div>
			<div class="col-sm-6">
				<div class="panel">
					<div class="panel-heading">Projects</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">
							<tr>
								<td>Select Client Name</td>
								<td>	
									<select   name="client_id" class="form-control"  required tabindex="<?php echo $tab++;?>">
									<option value="">Select Client Name</option>
									<?php 
										$sql="SELECT `sno`, `client_name` FROM `client_details`";	
										$result = execute_query($sql);
										while ($row = mysqli_fetch_array($result)){ 
											echo "<option value='".$row['sno']."' ";
											if(isset($_GET['id'])){
												if($row_edit['client_id']==$row['sno']){
													echo 'selected="selected"';
												}
											}
											echo ">" . $row['client_name'] . "</option>";
										}
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Project Name</td>
									<td>
									<input type="text" name="project_name" placeholder="Enter Project name" value="<?php if(isset($_GET['id'])){echo $row_edit['project_name'];}?>" class="form-control" tabindex="<?php echo $tab++;?>">
									</td>
								</tr>	
							<tr>
								<td>Project Rate</td>
								<td>
								<input type="text" name="rate"  required placeholder="Enter Rate" value="<?php if(isset($_GET['id'])){echo $rate;}?>" class="form-control" tabindex="<?php echo $tab++;?>">
								</td>
							</tr>

							<tr>
								<td>Rate Type</td>
								<td>
									<select   name="rate_type" value="<?php if(isset($_GET['id'])){echo $rate_type;}?>" class="form-control" tabindex="<?php echo $tab++;?>">
										<option value="">Select rate type</option>
										<option value="perday" <?php if(isset($_GET['id'])){if($row_edit['rate_type']=="perday"){echo "selected='selected'";}}?>>Perday</option>
										<option value="monthly" <?php if(isset($_GET['id'])){if($row_edit['rate_type']=="monthly"){echo "selected='selected'";}}?>>Monthly</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Over Time Rate</td>
								<td>
									<input type="text" name="over_time_rate" placeholder="Enter over time rate per hours" value="<?php if(isset($_GET['id'])){echo $over_time_rate;}?>" class="form-control" tabindex="<?php echo $tab++;?>">
								</td>
							</tr>
							<tr>
								<td>Extra Over Time Days Rate</td>
								<td>
									<input type="text" name="over_time_days_rate" placeholder="Enter Over Time Days Rate" value="<?php if(isset($_GET['id'])){echo $over_time_days_rate;}?>" class="form-control" tabindex="<?php echo $tab++;?>">
								</td>
							</tr>
							<tr>
								<td>Full Night Rate</td>
								<td>
									<input type="text" name="full_night_rate" placeholder="Enter Full Night Rate" value="<?php if(isset($_GET['id'])){echo $full_night_rate;}?>" class="form-control" tabindex="<?php echo $tab++;?>">
								</td>
							</tr>
							<tr>
								<td>Start Date</td>
								<td>
									<script type="text/javascript" language="javascript">
									document.writeln(DateInput('start_date', 'admin_employee_from', true, 'YYYY-MM-DD', '<?php if(isset($_POST['start_date'])){echo $_POST['start_date'];}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
									 </script> 
								</td>
							</tr>	
						</table>
					</div>
				</div>
				<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['id'])){echo $_GET['id'];}?>">
				<input type="submit" name="submit" value="Submit" id="submit" class="form-control" tabindex="<?php echo $tab++;?>">
			</div>
		</div>
	</form>
</div>
<div class="panel" style="margin-top:40px;">
	<table class="table table-hover table-responsive table-bordered table-striped">
			 <thead>
			  <tr class="panel-heading">
				<th>Sno</th>
				<th>Client Id</th>
				<th>Project Name</th>
				<th>Rate</th>
				<th>Rate Type</th>
				<th>Over time Rate</th>
				<th>Full over time days Rate</th>
				<th>Night Rate</th>
				<th>Start Date</th>
				<th>Edit</th>
				<th>Delete</th>
				</thead>
			<tbody>
		 <?php
			$sql ="SELECT * FROM `projects`";	
			$query = mysqli_query($db,$sql);
			while($row=mysqli_fetch_array($query)){ 
				echo "<tr>";
				echo '<td>'.$row["sno"].'</td>';
				echo '<td>'.client_name($row["client_id"]).'</td>';
				echo '<td>'.$row["project_name"].'</td>';
				echo '<td>'.$row["rate"].'</td>';
				echo '<td>'.$row["rate_type"].'</td>';
				echo'<td>'.$row["over_time_rate"].'</td>';
				echo'<td>'.$row["full_over_time_days_rate"].'</td>';
				echo'<td>'.$row["full_night_rate"].'</td>';
				echo '<td>'.$row["start_date"].'</td>';
				 echo '<td>';
				 echo '<a href="admin_projects.php?id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a>';
				 echo '</td>';
				echo '<td>';
				 echo '<a href="admin_projects.php?del='.$row["sno"].'"><i class="glyphicon glyphicon-remove"></i></a>';
				 echo '</td>';

			}
		?>
				</tbody>
	</table>
					
</div>
<?php
page_footer();
?>