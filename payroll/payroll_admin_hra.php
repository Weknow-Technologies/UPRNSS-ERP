<?php 
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
		if($_POST['edit_sno']==''){
		$sql = 'insert into payroll_hra_master (
		`pay_level_id` ,`a_hra` ,`b_hra` ) values ("'.$_POST['pay_level_id'].'", "'.$_POST['a_hra'].'", "'.$_POST['b_hra'].'")';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data save</p>';
			unset($_POST);
			goto postblank;
		}
	}
		else{
			$sql = 'update payroll_hra_master set
		    `pay_level_id` ="'.$_POST['pay_level_id'].'",
			`a_hra` ="'.$_POST['a_hra'].'",
			`b_hra` ="'.$_POST['b_hra'].'"
			where sno="'.$_POST['edit_sno'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 

			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data Update</p>';
			unset($_POST);
			goto postblank;
		}
	}	
}
else{
	postblank:
	$_POST['pay_level_id']='';
	$_POST['a_hra']='';
	$_POST['b_hra']='';
	$_POST['var_a_1']='';
}

if(isset($_GET['id'])){
		$var=$_GET['id'];
		$sql ="select * from `payroll_hra_master` where sno='".$var."'";
		$query = execute_query($sql);
		$row_edit = mysqli_fetch_array($query);
		$_POST['pay_level_id']=$row_edit['pay_level_id'];
		$_POST['a_hra']=$row_edit['a_hra'];
		$_POST['b_hra']=$row_edit['b_hra'];
		
		
}

if(isset($_GET['del']) ){
	$var=$_GET['del'];
	$sql ="delete from payroll_hra_master where `sno`='$var'";
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

page_header_start('HRA');
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
			}else if ($(this).val() == "other"){
				$("#hide1").hide();
				$("#hide2").hide();
				$("#hide3").show();
			}
			else {
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
	 <form method="post" action="payroll_admin_hra.php" enctype="multipart/form-data" name="admin_head" id="admin_head">
			<div class="panel">
				<table class="table table-hover table-responsive table-bordered table-striped">
					<div class="panel-heading"> Salary Heads</div>
					<div class="panel-body">
					<tr>
						<td>Pay Level</td>
						<td>
							<select class="form-control" name="pay_level_id" id="pay_level_id" tabindex="<?php echo $tab++; ?>">
								<option value="">-SELECT-</option>
								<?php
								$query = "select * from master_pay_level";
								$run = mysqli_query($db_erp, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['pay_level_id'])) {
										if ($_POST['pay_level_id'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . $data['level_name'] . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>A City HRA</td>
						<td>
							<input type="text" name="a_hra"  value="<?php echo $_POST['a_hra']; ?>" placeholder="Enter A City HRA" class="form-control" tabindex="<?php echo $tab++; ?>">
						</td>
					</tr>
					<tr>
						<td>B City HRA</td>
						<td>
							<input type="text" name="b_hra"  value="<?php echo $_POST['b_hra']; ?>" placeholder="Enter B City HRA" class="form-control" tabindex="<?php echo $tab++; ?>">
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
						<th>Sno</th>
						<th>Pay Level</th>
						<th>A City HRA</th>
						<th>B City HRA</th>
						
						<th>Edit</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$sql="SELECT * FROM `payroll_hra_master` order by abs(pay_level_id) ASC";
				$result = execute_query($sql);
				$i=1;
				while($row=mysqli_fetch_array($result)){ 
					echo '<tr>
					<td>'. $i++.'</td>
					<td>'. $row["pay_level_id"].'</td>
					<td>'. $row["a_hra"].'</td>
					<td>'. $row["b_hra"].'</td>
					<td><a href="payroll_admin_hra.php?id='.$row["sno"].'" onClick="return confirm(\'Are you sure?\');"><span><i class="glyphicon glyphicon-pencil"></i></span></a></td>
					<td><a href="payroll_admin_hra.php?del='.$row["sno"].'" onClick="return confirm(\'Are you sure?\');"><i class="glyphicon glyphicon-remove"></i></a></td>';
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