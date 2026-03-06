<?php 
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset( $_POST['submit'])){
	if($_POST['edit_sno'] == '1'){
		$msg='<div class="alert alert-danger">Sorry Not Allowed !</div>';
	}
	elseif($_POST['edit_sno'] != '' and $_POST['edit_sno'] != '1'){
		if(!isset($_POST['percent_of'])){
			$_POST['percent_of']='';
		}
		$sql='UPDATE `head_type` SET `head_name`="'.$_POST['head_name'].'" , `head_type`="'.$_POST['head_type'].'" ,`payslip_mode`="'.$_POST['payslip_mode'].'" , `value_type`="'.$_POST['value_type'].'" , `percent_of`="'.$_POST['percent_of'].'" , `recurrence_period`="'.$_POST['recurrence_period'].'"  WHERE sno="'.$_POST['edit_sno'].'"';
		
		if ($db->query($sql) === TRUE) {
			$msg='<div class="alert alert-success">New Record Updated Successfully!</div>';
		} 
		else{
			$msg = '<div class="alert alert-danger">Error # AH_001. '.mysqli_error($db).' >> '.$sql.'</div>';
		}
	}
	else{
		if(!isset($_POST['percent_of'])){
			$_POST['percent_of']='';
		}
		$sql = "INSERT INTO `head_type`(`head_name`, `head_type`,`payslip_mode`, `value_type`, `percent_of`, `recurrence_period`) VALUES 
		('".$_POST['head_name']."','".$_POST['head_type']."','".$_POST['payslip_mode']."', '".$_POST['value_type']."', '".$_POST['percent_of']."', '".$_POST['recurrence_period']."')";
		execute_query($sql);
		if(!mysqli_error($db)) {
			$msg .= '<div class="alert alert-success">Done.</div>';
			$head_id = mysqli_insert_id($db);
			if($_POST['value_type']=='formula'){
				for($i=1;$i<=$_POST['id'];$i++){
					if($_POST['var_a_'.$i]!=$_POST['var_b_'.$i]){
						$sql = 'insert into head_type_formula (head_id, var_a, operator, var_b, step) values ("'.$head_id.'", "'.$_POST['var_a_'.$i].'", "'.$_POST['calc_'.$i].'", "'.$_POST['var_b_'.$i].'", "'.$i.'")';
						execute_query($sql);
						if(mysqli_error($db)){
							$msg .= '<div class="alert alert-danger">Error # AH_003. >> '.$sql.' >> '.mysqli_error($db).'</div>';
						}
					}
				}
			}
		} 
		else {			
			$msg .= '<div class="alert alert-danger">Error # AH_002.</div>';
		}
	}
}

if(isset($_GET['id'])){
		$var=$_GET['id'];
		$sql ="select * from `head_type` where sno='".$var."'";
		$query = execute_query($sql);
		$row_edit = mysqli_fetch_array($query);
		$head_name=$row_edit['head_name'];
		$head_type=$row_edit['head_type'];
		$payslip_mode=$row_edit['payslip_mode'];
		$value_type=$row_edit['value_type'];
		$percent_of=$row_edit['percent_of'];
		$recurrence_period=$row_edit['recurrence_period'];
		
}

if(isset($_GET['del']) ){
	$var=$_GET['del'];
	$sql ="delete from head_type where `sno`='$var'";
	$result = execute_query($sql);
	if ($result == true) {
		$msg.='<div class="alert alert-success">Data Deleted !</div>';
		$sql ="delete from head_type_formula where `head_id`='$var'";
		$result = execute_query($sql);

	}
	else {
		$msg.='<div class="alert alert-danger">Sorry !</div>';
	}
}

page_header_start('Salary Heads');
page_header_end();
navigation($_SERVER['PHP_SELF']);

?>

<script type="text/javascript">
    $(function () {
        $("#title1").change(function () {
			
            if ($(this).val() == "percent") {
				$("#hide1").show();
				$("#percent_of").removeAttr("disabled");
            }
			else if($(this).val() == "formula"){
				/*document.getElementById('hide1').style.display = 'none';
				document.getElementById('hide2').style.display = 'block';*/
				$("#hide1").hide();
				$("#hide2").show();
			} else {
				$("#hide1").hide();
				$("#percent_of").attr("disabled", "disabled");
				
            }
        });
    });
     $(function () {
     	if($("#edit_sno").val() !='' && $("#percent_of").val() !=''){
     		$("#hide1").show();
     		$("#percent_of").removeAttr("disabled");
     	}
     });
	
function tab_fill(id,tab){
	var current = document.getElementById('current').value;
	id = parseFloat(document.getElementById('id').value)+1;
	var var_a = $("#var_a_1").html();
	tab = (id*3)+10;
	$("#add_step").remove();
	inputHTML = '<tr id="step_'+id+'" style=""><td>Formula Step '+id+'</td><td><table><tbody><tr><td><select name="var_a_'+id+'" class="form-control" id="var_a_'+id+'" onFocus="getCurrent('+id+')">';
	for(a=1; a<=id-1; a++){
		inputHTML += '<option value="step_'+a+'">Step '+a+'</option>';
	}
	
	inputHTML += var_a+'</select></td><td><select name="calc_'+id+'" class="form-control"><option value="+">+</option><option value="-">-</option><option value="*">*</option><option value="/">/</option></select></td><td><select name="var_b_'+id+'" class="form-control" id="var_b_'+id+'">';
	for(a=1; a<=id-1; a++){
		inputHTML += '<option value="step_'+a+'">Step '+a+'</option>';
	}

	inputHTML += var_a+'</select></td><td><button type="button" class="btn btn-info" id="add_step" onClick="tab_fill(1,12);">Add Step</button></td></tr></tbody></table></td></tr>';
	$(inputHTML).insertBefore("tr#finalValues");
	document.getElementById('id').value = id;
}

function getCurrent(id){
	document.getElementById('current').value = id;
}
</script>

<div class="container ">
	<?php echo $msg; ?>
	 <div class="col-sm-6 col-md-offset-3">
	 <form method="post" action="payroll_admin_heads.php" enctype="multipart/form-data" name="admin_head" id="admin_head">
			<div class="panel">
				<table class="table table-hover table-responsive table-bordered table-striped">
					<div class="panel-heading"> Salary Heads</div>
					<div class="panel-body">
					<tr>
						<td>Head Name</td>
						<td>
							<input type="text" name="head_name"  required value="<?php if(isset($_GET['id'])){echo $head_name;}?>"placeholder="Enter head name" class="form-control" tabindex="<?php echo $tab++; ?>">
						</td>
					</tr>
					<tr>
						<td>Type</td>
						<td>
							<select class="form-control" name="head_type" value="<?php if(isset($_GET['id'])){echo $head_type;}?>" id="title"  tabindex="<?php echo $tab++; ?>">
								<option value="income" <?php if(isset($_GET['id'])){if($row_edit['head_type']=="income"){echo "selected='selected'";}}?>>Income</option>
								<option value="deduction" <?php if(isset($_GET['id'])){if($row_edit['head_type']=="deduction"){echo "selected='selected'";}}?>>Deduction</option>
								<option value="oth_deduction" <?php if(isset($_GET['id'])){if($row_edit['head_type']=="oth_deduction"){echo "selected='selected'";}}?>>Other Deduction</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>Payslip Show</td>
						<td>
							<select class="form-control" name="payslip_mode" value="<?php if(isset($_GET['id'])){echo $payslip_mode;}?>" id="title"  tabindex="<?php echo $tab++; ?>">
								<option value="0" <?php if(isset($_GET['id'])){if($row_edit['payslip_mode']=="0"){echo "selected='selected'";}}?>>Active</option>
								<option value="1" <?php if(isset($_GET['id'])){if($row_edit['payslip_mode']=="1"){echo "selected='selected'";}}?>>Inactive</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>Recurrence Period</td>
						<td>
							<select class="form-control"  required name="recurrence_period" value="" id="title" tabindex="<?php echo $tab++; ?>">
								<option value="1"  <?php if(isset($_GET['id'])){if($row_edit['recurrence_period']=="1"){echo "selected='selected'";}}?>>Monthly</option>
								<option value="3"  <?php if(isset($_GET['id'])){if($row_edit['recurrence_period']=="3"){echo "selected='selected'";}}?>>Quarterly</option>
								<option value="6"  <?php if(isset($_GET['id'])){if($row_edit['recurrence_period']=="6"){echo "selected='selected'";}}?>>Half Yearly</option>
								<option value="12" <?php if(isset($_GET['id'])){if($row_edit['recurrence_period']=="12"){echo "selected='selected'";}}?>>Yearly</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>Calculation Method</td>
						<td>
							<div id="show1">
								<select name="value_type" id="title1" class="form-control" tabindex="<?php echo $tab++; ?>">
									<option value="value" <?php if(isset($_GET['id'])){if($row_edit['value_type']=="value"){echo "selected='selected'";}}?>>Value</option>
									<option value="percent" <?php if(isset($_GET['id'])){if($row_edit['value_type']=="percent"){echo "selected='selected'";}}?>>Percent(%)</option>
									<option value="formula" <?php if(isset($_GET['id'])){if($row_edit['value_type']=="formula"){echo "selected='selected'";}}?>>Formula</option>
								</select>
							</div>
						</td>
					</tr>
					<tr id="hide1" style="display: none;">
						<td>Percent of</td>
						<td><select name="percent_of"  class="form-control" id="percent_of" disabled="disabled" tabindex="<?php echo $tab++; ?>">
							<option value="">-SELECT ANY ONE-</option>
							<?php 
							$sql = 'SELECT * FROM `head_type`';	
							$query = execute_query($sql);
							while ($row = mysqli_fetch_array($query)){ 
								echo "<option value='".$row['sno']."' ";
									if(isset($_GET['id'])){
												if($row_edit['percent_of'] == $row['sno']){
													echo 'selected="selected"';
												}
									}
											echo ">" . $row['head_name'] . "</option>";
										
								
							}?>
						</select></td>
					</tr>
					<tr id="hide2" style="display: none;">
						<td colspan="2">
							<table width="100%">
								<tr>
									<td>Formula Step 1</td>
									<td><select name="var_a_1" class="form-control" id="var_a_1" tabindex="<?php echo $tab++; ?>">
										<?php 
										$sql = 'SELECT * FROM `head_type`';	
										$query = execute_query($sql);
										while ($row = mysqli_fetch_array($query)){ 
											echo "<option value='".$row['sno']."' ";
												if(isset($_GET['id'])){
															if($row_edit['percent_of'] == $row['sno']){
																echo 'selected="selected"';
															}
												}
														echo ">" . $row['head_name'] . "</option>";


										}?>
									</select></td>
									<td>
										<select class="form-control" name="calc_1" id="calc_1">
											<option value="+">+</option>
											<option value="-">-</option>
											<option value="*">*</option>
											<option value="/">/</option>
										</select>
									</td>
									<td><select name="var_b_1" class="form-control" id="var_b_1" tabindex="<?php echo $tab++; ?>">
										<?php 
										$sql = 'SELECT * FROM `head_type`';	
										$query = execute_query($sql);
										while ($row = mysqli_fetch_array($query)){ 
											echo "<option value='".$row['sno']."' ";
												if(isset($_GET['id'])){
															if($row_edit['percent_of'] == $row['sno']){
																echo 'selected="selected"';
															}
												}
														echo ">" . $row['head_name'] . "</option>";


										}?>
									</select></td>
									<td>
										<input type="hidden" value="1" name="id" id="current">
										<input type="hidden" value="1" name="id" id="id">
										<button type="button" class="btn btn-info" id="add_step" onClick="tab_fill(1,12);">Add Step</button>
									</td>
								</tr>
								<tr id="finalValues"></tr>
							</table>
						</td>
					</tr>
					
				</table>
				<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['id'])){echo $_GET['id'];}?>">
				<input type="submit" name="submit" id="submit" value="Create Record" class="form-control" tabindex="<?php echo $tab++; ?>">
			
			</div>
		</div>
		<div class="col-sm-12">
		<div class="panel" style="margin-top:40px;">
			<table class="table table-hover table-responsive table-bordered table-striped">
				<thead>
					<tr class="panel-heading">
						<th>Head Name</th>
						<th>Type</th>
						<th>Payslip Show</th>
						<th>Value or Percent</th>
						<th>Percent Of</th>
						<th>Recurrence Period</th>
						<th>Edit</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$sql="SELECT * FROM `head_type`";
				$result = execute_query($sql);
				while($row=mysqli_fetch_array($result)){ 
					$percent_of = '';
					if($row["percent_of"]!=''){
						$sql = 'select * from head_type where sno="'.$row["percent_of"].'"';
						$percent_of = mysqli_fetch_assoc(execute_query($sql));
						$percent_of = $percent_of['head_name'];
					}
					echo '<tr>
					<td>'. $row["head_name"].'</td>
					<td>'. $row["head_type"].'</td>
					<td>'. $row["payslip_mode"].'</td>
					<td>'. $row["value_type"].'</td>';
					echo '<td>'.($percent_of!=''?$percent_of:'').'</td>';
					
					echo '<td>'. $row["recurrence_period"].'</td>
					<td><a href="payroll_admin_heads.php?id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a></td>
					<td><a href="payroll_admin_heads.php?del='.$row["sno"].'"><i class="glyphicon glyphicon-remove"></i></a></td>';
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	</form>
</div>
</div>
<?php
page_footer();
?>