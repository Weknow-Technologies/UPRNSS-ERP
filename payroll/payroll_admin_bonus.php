<?php
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$sql='UPDATE `admin_bonus` SET `maximum_salary`="'.$_POST['maximum_salary'].'" , `minimum_working_days`="'.$_POST['minimum_working_days'].'" , `maximum_applicable_bonus_wages`="'.$_POST['maximum_applicable_bonus_wages'].'" , `minimum_wages`="'.$_POST['minimum_wages'].'" , `bonus_rate`="'.$_POST['bonus_rate'].'" , `edited_by`="'.$_SESSION['username'].'" , `edition_time`="'.date('Y-m-d h:i:s').'" WHERE `sno`="'.$_POST['edit_sno'].'"' ;
		$res = execute_query($sql);
		//echo $sql;
		if ($res) {
			$msg='<div class="alert alert-success"> Record Updated Successfully!</div>';
		} 
		else{
				echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
}

if(isset($_GET['del'])){
	$sql ='delete from `admin_bonus` where sno="'.$_GET['del'].'"';
	$result = execute_query($sql);
	if ($result == true) {
		$msg='<div class="alert alert-success">Data Deleted !</div>';
	}
	else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
	}
}
	
if(isset($_GET['id'])){
	$sql ='select * from `admin_bonus` where sno="'.$_GET['id'].'"';
	$query = execute_query($sql);
	$row_edit = mysqli_fetch_array($query);
}

page_header_start('Admin Bonus');
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
				<div class="panel-heading">Admin Bonus</div>
				<?php if(isset($_GET['id'])){ ?>
				<div class="panel-body"> 
					<div class="row">
						<!--<div class="col-sm-3">Company Name</div>
						<div class="col-sm-3">
							<select name="company_id" class="form-control" tabindex="<?php echo $tab++; ?>">
								<option value="">-SELECT ANY ONE-</option>
								<?php 
									$sql_details = 'SELECT * FROM `company_details`';
									$result_details = execute_query($sql_details);
									while($row_details = mysqli_fetch_array($result_details)){
										?>
								<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_GET['id'])){if($row_details['sno']==$row_edit['company_id']){echo 'selected';}} ?>><?php echo $row_details['company_name']; ?></option>
										<?php
									}
								?>
							</select>
						</div>-->
						<div class="col-sm-3">Maximum Salary(Per Month)</div>
						<div class="col-sm-3">
							<input type="text" name="maximum_salary" value="<?php if(isset($_GET['id'])){echo $row_edit['maximum_salary']; }?>" class="form-control" tabindex="<?php echo $tab++; ?>" required>
						</div>
						<div class="col-sm-3">Minimum Working Days(Per Year)</div>
						<div class="col-sm-3">
							<input type="text" name="minimum_working_days" value="<?php if(isset($_GET['id'])){echo $row_edit['minimum_working_days']; }?>" class="form-control" tabindex="<?php echo $tab++; ?>" required>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-3">Minimum Wages(Preferrd)</div>
						<div class="col-sm-3">
							<input type="text" name="minimum_wages" value="<?php if(isset($_GET['id'])){echo $row_edit['minimum_wages']; }?>" class="form-control" tabindex="<?php echo $tab++; ?>" required>
						</div>
						<div class="col-sm-3">Maximum Wages</div>
						<div class="col-sm-3">
							<input type="text" name="maximum_applicable_bonus_wages" value="<?php if(isset($_GET['id'])){echo $row_edit['maximum_applicable_bonus_wages']; }?>" class="form-control" tabindex="<?php echo $tab++; ?>" required>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-3">Bonus Rate(%)</div>
						<div class="col-sm-3">
							<input type="text" name="bonus_rate" value="<?php if(isset($_GET['id'])){echo $row_edit['bonus_rate']; }?>" class="form-control" tabindex="<?php echo $tab++; ?>" required>
						</div>
						<div class="col-sm-6">
							<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['id'])){echo $_GET['id'];}?>">
							<input type="submit" id="submit" name="submit" class="form-control" value="Submit" tabindex="<?php echo $tab++; ?>">
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</form>
	</div>
	<div class="panel">
		<table class="table table-hover table-responsive table-bordered table-striped">
			<thead>
				<tr class="panel-heading">
					<!--<th>Sno</th>
					<th>Company Name</th>-->
					<th>Maximum Salary(Per Days)</th>
					<th>Minimum Working Days(Per Days)</th>
					<th>Minimum Wages(Preferred)</th>
					<th>Maximum Applicable Bonus Wages(For Calculation Of Bonus)</th>
					<th>Bonus Rate(%)</th>
					<th>Edit</th>
					<!--<th>Delete</th>-->
				</tr>
			</thead>
			<tbody>
			<?php
			$n = 1;
			$sql ="SELECT * FROM `admin_bonus`";	
			$query = execute_query($sql);
			while($row=mysqli_fetch_array($query)){ 
				/**echo '<td>'.$n++.'</td>';
				if($row['company_id'] == 0){
					echo '<td>Default</td>';
				}
				else{
					$sql_company = 'SELECT `company_name` FROM `company_details` WHERE `sno`="'.$row['company_id'].'"';
					$row_company = mysqli_fetch_array(execute_query($sql_company));
					echo'<td>'.$row_company['company_name'].'</td>';
				}**/
					echo '<td>'.$row['maximum_salary'].'</td>';
					echo '<td>'.$row['minimum_working_days'].'</td>';
					echo '<td>'.$row['minimum_wages'].'</td>';
					echo '<td>'.$row['maximum_applicable_bonus_wages'].'</td>';
					echo '<td>'.$row['bonus_rate'].'</td>';
					echo'<td><a href="admin_bonus.php?id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a></td>';
				/**if($row['company_id'] == 0){
					echo '<td>&nbsp;</td>';
				}
				else{
					echo'<td><a href="admin_company_type.php?del='.$row["sno"].'"><i class="glyphicon glyphicon-remove"></i></a></td></tr>';
				}**/
			}
			?>
			</tbody>
		</table>
	</div>
</div>
<?php
page_footer();
?>	