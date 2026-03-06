<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

if(isset($_POST['submit'])){
	//print_r($_POST);
	if($_POST['edit_sno']==''){
		$sql = 'insert into user_type (user_type) values ("'.$_POST['user_type'].'")';
	}
	else{
		$sql = 'update user_type set 
		user_type = "'.$_POST['user_type'].'"
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
		
		$sql = 'delete from user_access where user_id="'.$id.'"';
		execute_query($sql);
		if(isset($_POST['user_access'])){
			foreach($_POST['user_access'] as $k=>$v){
				$sql = 'insert into user_access (user_id, file_name, created_by, creation_time) values ("'.$id.'", "'.$v.'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 2 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
			}
		}
		
		
		if($msg==''){
			$msg .= '<p class="text text-success">Data Saved</p>';
			$_POST['edit_sno'] = '';
			$_POST['user_type'] = '';
			$_POST['user_access'] = array();
		}
	}
}
else{
	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['user_type'] = '';
		$_POST['user_access'] = array();
	}
}

if(isset($_GET['eid'])){
	$sql = 'select * from user_type where sno="'.$_GET['eid'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['user_type'] = $data['user_type'];
	
	$access = array();
	$sql = 'select * from user_access where user_id="'.$_GET['eid'].'"';
	$result = execute_query($sql);
	if(mysqli_num_rows($result)!=0){
		while($row = mysqli_fetch_assoc($result)){
			$access[] = $row['file_name'];
		}
	}
	$_POST['user_access'] = $access;
}

if(isset($_GET['delid'])){
	$sql = 'delete from user_type where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	
	$sql = 'delete from user_division where user_id="'.$_GET['delid'].'"';
	execute_query($sql);
	
	$sql = 'delete from user_department where user_id="'.$_GET['delid'].'"';
	execute_query($sql);
	
	$msg .= '<p class="text text-danger">Data Deleted.</p>';
}

page_header_start();

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
</style>

<?php
page_header_end();
page_sidebar();

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
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
									<input type="text" name="user_type" id="user_type" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['user_type']; ?>">
								</div>
                            </div>
                            <div class="col-md-5">
								<div class="form-group">
									<label >User Access</label>
									<select name="user_access[]" id="user_access" class="form-control" tabindex="<?php echo $tab++; ?>" multiple>
										<?php
										$sql = 'select * from navigation where (parent in ("", "P") or parent is null) and sno not in (1, 7)';
										$result = execute_query($sql);
										while($row = mysqli_fetch_assoc($result)){
											echo '<optgroup label="'.$row['link_description'].'">';
											echo '<option value="'.$row['sno'].'" ';
											if(in_array($row['sno'], $_POST['user_access'])){
												echo ' selected="selected" ';
											}
											echo '>'.$row['link_description'].'</option>';
											$sql = 'select * from navigation where parent = "'.$row['sno'].'" order by sno';
											$result_child = execute_query($sql);
											while($row_child = mysqli_fetch_assoc($result_child)){
												echo '<option value="'.$row_child['sno'].'" ';
												if(in_array($row_child['sno'], $_POST['user_access'])){
													echo ' selected="selected" ';
												}

												echo '>'.$row_child['link_description'].'</option>';
											}
											
											echo '</optgroup>';
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
						$sql = 'select * from user_type';
						//echo $sql;
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							
							$access = array();
							$sql = 'select link_description from user_access left join navigation on navigation.sno = user_access.file_name where user_id="'.$row['sno'].'"';
							//echo $sql;
							$result_access = execute_query($sql);
							if(mysqli_num_rows($result_access)!=0){
								while($row_access = mysqli_fetch_assoc($result_access)){
									$access[] = $row_access['link_description'];
								}
							}
							
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row['user_type'].'</td>
							<td>'.implode(", ", $access).'</td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?eid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?delid='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete Entry"></span></a></td>
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
page_footer_start();
?>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect({
	search: true,
	selectAll: true
});
	
$(document).ready( function () {
    
});
	
</script>

    
<?php		
page_footer_end();
?>