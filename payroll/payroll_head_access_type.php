<?php
include("scripts/settings.php");
// page_header_start();
$msg='';
$tab=1;

if(isset($_POST['submit'])){
	//print_r($_POST);
	if($_POST['edit_sno']==''){
		$sql = 'insert into payroll_emp_category (category_name) values ("'.$_POST['category_name'].'")';
	}
	else{
		$sql = 'update payroll_emp_category set 
		category_name = "'.$_POST['category_name'].'"
		where sno="'.$_POST['edit_sno'].'"';
		
	}
    execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		if($_POST['edit_sno']==''){
			$id = mysqli_insert_id($db);
		}
		else{
			$id = $_POST['edit_sno'];
		}
		
		$sql = 'delete from payroll_head_access where cat_id="'.$id.'"';
		execute_query($sql);
		if(isset($_POST['payroll_head_access'])){
			foreach($_POST['payroll_head_access'] as $k=>$v){
				$sql = 'insert into payroll_head_access (cat_id, head_id, created_by, creation_time) values ("'.$id.'", "'.$v.'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 2 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
			}
		}
		
		
		if($msg==''){
			$msg .= '<p class="text text-success">Data Saved</p>';
			$_POST['edit_sno'] = '';
			$_POST['cat_id'] = '';
			$_POST['payroll_head_access'] = array();
		}
	}
}
else{
	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['cat_id'] = '';
		$_POST['payroll_head_access'] = array();
	}
}

if(isset($_GET['eid'])){
	$sql = 'select * from payroll_emp_category where sno="'.$_GET['eid'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['cat_id'] = $data['category_name'];
	
	$access = array();
	$sql = 'select * from payroll_head_access where cat_id="'.$_GET['eid'].'"';
	$result = execute_query($sql);
	if(mysqli_num_rows($result)!=0){
		while($row = mysqli_fetch_assoc($result)){
			$access[] = $row['head_id'];
		}
	}
	$_POST['payroll_head_access'] = $access;
}

if(isset($_GET['delid'])){
	$sql = 'delete from payroll_head_access where cat_id="'.$id.'"';
	// $sql = 'delete from users where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	
	
	
	$sql = 'delete from payroll_emp_category where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	
	$msg .= '<p class="text text-danger">Data Deleted.</p>';
}


// page_header_start();

page_header_end();

?>

<script src="js/krutidev.js"></script>

<script src="js/unicode_keyboard.js"></script>

<style>

	#project_name_hindi{

		font-family: 'Kruti Dev 010';

		font-size: 20px;

	}

	textarea{

		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;

	}

	

	@media print {

		table {

		page-break-inside: avoid;

	}

	}

</style>





<?php

page_header_start('Head Access');
page_header_end();



?>


   <form id="" name="" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
                    	<div class="row">
                    		<div class="col-md-2">
								<div class="form-group">
									<label >User Type</label>
									<input type="text" name="cat_id" id="cat_id" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['cat_id']; ?>">
								</div>
                            </div>
                            <div class="col-md-5">
								<div class="form-group">
									<label >User Access</label>
									<select name="payroll_head_access[]" id="payroll_head_access" class="form-control" tabindex="<?php echo $tab++; ?>" multiple>
										<?php
										$sql = 'select * from head_type';
										$result = execute_query($sql);
										while($row = mysqli_fetch_assoc($result)){
											
											echo '<option value="'.$row['sno'].'" ';
											// if(in_array($row['sno'], $_POST['payroll_head_access'])){
												// echo ' selected="selected" ';
											// }
											echo '>'.$row['head_name'].'</option>';
										}
										?>
									</select>
								</div>
                            </div>
							<div class="col-md-3">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success col-12">Submit</button>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<input type="hidden" id="id" name="id" value="1">
									<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </form>
	
		
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover" id="general_stat_table">
						<thead>
						<tr>
						<th>S.No.</th>
						<th>User Type</th>
						<th>Access Details</th>
						<th></th>
						<th></th>
						</tr>
						</thead>
						<tbody>
						<?php
						$i=1;
						$access_details = '';
						$sql = 'select * from payroll_emp_category';
						$sql;
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							
							$access = array();
							$sql = 'select * from payroll_head_access left join head_type on head_type.sno = payroll_head_access.head_id where cat_id="'.$row['sno'].'"';
							$sql;
							$result_access = execute_query($sql);
							if(mysqli_num_rows($result_access)!=0){
								while($row_access = mysqli_fetch_assoc($result_access)){
									$access[] = $row_access['head_name'];
								}
							}
							
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row['category_name'].'</td>
							<td>'.implode(", ", $access).'</td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?eid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details">Edit</a></td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?delid='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry">Delete</a></td>
							</tr>';
						}
						?>
						</tbody>
					</table>
					
					</div>
                </div>
            </div>
		</div>
	
	

<?php

?>



    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->

	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

<script>



$('select[multiple]').multiselect({

	search: true

});

	

$(document).ready( function () {

    /*$('#general_stat_table').DataTable({

		paging: false,

		fixedHeader: true,

		colReorder: true

		});

	});	*/



	

	var t = $('#general_stat_table').DataTable({

		paging: false

    });

 

    

});

	

</script>



    

<?php		

page_footer();

?>