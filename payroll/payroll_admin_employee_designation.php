<?php
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$sql='UPDATE `employee_designation` SET `designation_name`="'.$_POST['designation_name'].'", `edited_by`="'.$_SESSION['username'].'" , `edition_time`="'.date('Y-m-d h:i:s').'"  WHERE sno="'.$_POST['edit_sno'].'"';
		//echo $sql;
		if ($db->query($sql) === TRUE) {
			$msg='<div class="alert alert-success">New Record Updated Successfully!</div>';
		} 
		else{
				echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
	else {
		$sql='INSERT INTO `employee_designation`(`designation_name` , `created_by` , `creation_time`) VALUES ("'.$_POST['designation_name'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
		if ($db->query($sql) === TRUE) {
		$msg='<div class="alert alert-success">New Record Created Successfully</div>';
		}
		else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
		}
	}
}
if(isset($_GET['del'])){
	$sql ="delete from employee_designation where sno='".$_GET['del']."'";
	$result = execute_query($sql);
	if ($result == true) {
		$msg='<div class="alert alert-success">Data Deleted !</div>';
	}
	else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
	}
}	
if(isset($_GET['id'])){
	$sql ="select * from employee_designation where sno='".$_GET['id']."'";
	$query = execute_query($sql);
	$row_edit = mysqli_fetch_array($query);
}
page_header_start('Employee Designationt');
page_header_end();
navigation($_SERVER['PHP_SELF']);
?>
<style type="text/css">
	table, tr{
		padding:  20px;
	}
</style>

<div class="container">
	<div class="row">
		<?php echo $msg; ?>   
		<div class="col-sm-12">
			 
		</div>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="panel">
				<div class="panel-heading">Employee Designation </div>
				<div class="panel-body">
					<div class="row"> 
						<div class="col-sm-6">
							<table class="table table-hover table-responsive table-condensed">
								<tr>
									<td >Designation Name *</td>
									<td>
										<input type="text" name="designation_name" placeholder="Enter Designation name" value="<?php 
										if(isset($_GET['id'])){echo $row_edit['designation_name']; }?>" class="form-control" tabindex="1"  required>
									</td>
								</tr>
							</table>
						</div>
						
					</div>
					<div class="row">
						<div class="col-sm-3">&nbsp;</div>
						<div class="col-sm-6 ">
							<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['id'])){echo $_GET['id'];}?>">
							<input type="submit" id="submit" name="submit" class="form-control" value="Submit" tabindex="15">
						</div>
						<div class="col-sm-3">&nbsp;</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="panel">
		<table class="table table-hover table-responsive table-bordered table-striped">
			<thead>
				<tr class="panel-heading">
					<th>Sno</th>
					<th>Designation Name</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$n = 1;
			$sql ="SELECT * FROM `employee_designation`";	
			$query = mysqli_query($db,$sql);
			while($row=mysqli_fetch_array($query)){ 
				echo "<tr>";
				echo '<td>';
				echo $n++;
				echo '</td>';
				echo '<td>';
				echo $row["designation_name"];
				echo '</td><td>';
				echo'<a href="'.$_SERVER['PHP_SELF'].'?id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a>';
				echo '</td>';
				echo '<td>';
				echo'<a href="'.$_SERVER['PHP_SELF'].'?del='.$row["sno"].'"><i class="glyphicon glyphicon-remove"></i></a>';
				echo'</td>';
				echo '</tr>';
			}
			?>
			</tbody>
		</table>
	</div>
</div>
<?php
page_footer();
?>
	