<?php
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$sql='UPDATE `company_type` SET `company_type_name`="'.$_POST['company_type_name'].'",`description`="'.$_POST['description'].'" , `edited_by`="'.$_SESSION['username'].'" , `edition_time`="'.date('Y-m-d h:i:s').'" WHERE `sno`="'.$_POST['edit_sno'].'"' ;
		$res = execute_query($sql);
		//echo $sql;
		if ($res) {
			$msg='<div class="alert alert-success"> Record Updated Successfully!</div>';
		} 
		else{
				echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
	else {
		$sql='INSERT INTO `company_type`(`company_type_name`, `description` , `created_by` , `creation_time`) VALUES ("'.$_POST['company_type_name'].'" , "'.$_POST['description'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
		$res = execute_query($sql);
		if ($res) {
		$msg='<div class="alert alert-success">New Record Created Successfully</div>';
		}
		else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
		}
	}
}

if(isset($_GET['del'])){
	$sql ='delete from company_type where sno="'.$_GET['del'].'"';
	$result = execute_query($sql);
	if ($result == true) {
		$msg='<div class="alert alert-success">Data Deleted !</div>';
	}
	else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
	}
}
	
if(isset($_GET['id'])){
	$sql ='select * from company_type where sno="'.$_GET['id'].'"';
	$query = execute_query($sql);
	$row_edit = mysqli_fetch_array($query);
}

page_header_start('Company Type Master');
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
				<div class="panel-heading">Add Company Type</div>
				<div class="panel-body"> 
					<div class="col-sm-6">
						<table class="table table-borderless table-hover " cellpadding="20px;">
							<tr>
								<td >Company Type</td>
								<td>
									<input type="text" name="company_type_name" placeholder="Enter Company Type name" value="<?php 
									if(isset($_GET['id'])){echo $row_edit['company_type_name']; }?>" class="form-control" tabindex="1"  required>
								</td>
							</tr>
						</table>
					</div>
					<div class="col-sm-6">
						<table class="table table-hover table-responsive table-condensed">
							<tr>
								<td>Description</td>
								<td>
									<textarea class="form-control"  name="description" value="" id="title" tabindex="2"><?php if(isset($_GET['id'])){echo $row_edit['description'];}?></textarea>
								</td>

							</tr>
						</table>
					</div>
					<div class="col-sm-3"></div>
					<div class="col-sm-6 ">
						<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['id'])){echo $_GET['id'];}?>">
						<input type="submit" id="submit" name="submit" class="form-control" value="Submit" tabindex="8">
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
					<th>Company Type</th>
					<th>Description</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$n = 1;
			$sql ="SELECT * FROM `company_type`";	
			$query = execute_query($sql);
			while($row=mysqli_fetch_array($query)){ 
				echo '<td>'.$n++.'</td><td>'.$row['company_type_name'].'</td><td>'.$row['description'].'</td><td>';
				echo'<a href="admin_company_type.php?id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a></td><td>';
				echo'<a href="admin_company_type.php?del='.$row["sno"].'"><i class="glyphicon glyphicon-remove"></i></a></td></tr>';
			}
			?>
			</tbody>
		</table>
	</div>
</div>
<?php
page_footer();
?>	