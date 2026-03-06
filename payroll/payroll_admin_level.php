<?php
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$sql='UPDATE `pay_level` SET `level_name`="'.$_POST['level_name'].'", `edited_by`="'.$_SESSION['username'].'" , `edition_time`="'.date('Y-m-d h:i:s').'"  WHERE sno="'.$_POST['edit_sno'].'"';
		//echo $sql;
		if ($db->query($sql) === TRUE) {
			$msg='<div class="alert alert-success">New Record Updated Successfully!</div>';
		} 
		else{
				echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
	else {
		$sql='INSERT INTO `pay_level`(`level_name` , `created_by` , `creation_time`) VALUES ("'.$_POST['level_name'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
		if ($db->query($sql) === TRUE) {
		$msg='<div class="alert alert-success">New Record Created Successfully</div>';
		}
		else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
		}
	}
}
if(isset($_GET['del'])){
	$sql ="delete from pay_level where sno='".$_GET['del']."'";
	$result = execute_query($sql);
	if ($result == true) {
		$msg='<div class="alert alert-success">Data Deleted !</div>';
	}
	else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
	}
}	
if(isset($_GET['id'])){
	$sql ="select * from pay_level where sno='".$_GET['id']."'";
	$query = execute_query($sql);
	$row_edit = mysqli_fetch_array($query);
}
page_header_start('Pay Level');
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
				<div class="panel-heading">Pay level </div>
				<div class="panel-body">
					<div class="row"> 
						<div class="col-sm-6">
							<table class="table table-hover table-responsive table-condensed">
								<tr>
									<td >Level Name *</td>
									<td>
										<input type="text" name="level_name" placeholder="Enter level name" value="<?php 
										if(isset($_GET['id'])){echo $row_edit['level_name']; }?>" class="form-control" tabindex="1"  required>
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
					<th>Level Name</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$n = 1;
			$sql ="SELECT * FROM `pay_level`";	
			$query = mysqli_query($db,$sql);
			while($row=mysqli_fetch_array($query)){ 
				echo "<tr>";
				echo '<td>';
				echo $n++;
				echo '</td>';
				echo '<td>';
				echo $row["level_name"];
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
	