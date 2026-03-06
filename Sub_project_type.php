<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		$sql = 'insert into master_sub_project_type (project_type_id, sub_project_type, status, created_by, creation_time) values ("'.$_POST['project_type_id'].'", "'.$_POST['sub_project_type'].'","0", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data Save</p>';
			unset($_POST);
			goto postblank;
		}
	}
	else{
		$sql = 'update master_sub_project_type set 
		project_type_id = "'.$_POST['project_type_id'].'",  
		sub_project_type = "'.$_POST['sub_project_type'].'",   
		edited_by = "'.$_SESSION['username'].'", 
		edition_time = "'.date("Y-m-d H:i:s").'"   
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
	$_POST['project_type_id'] = '';
	$_POST['sub_project_type'] = '';
	$_POST['edit_sno'] = '';
}




if(isset($_GET['edit_sno'])){
	$sql = 'select * from master_sub_project_type where sno="'.$_GET['edit_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['project_type_id'] = $data['project_type_id'];
	$_POST['sub_project_type'] = $data['sub_project_type'];
	
}	

if(isset($_GET['del'])){
	$sql = 'delete from master_sub_project_type where sno="'.$_GET['del'].'"';
	execute_query($sql);
	
	
	$msg .= '<p class="text text-danger">Data Deleted.</p>';
}

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
						<div class="row" id="">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >Project Type</label><br>
                                    <select class="form-control" name="project_type_id" id="department_id" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from master_project_type";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_type_id'])){
												if($_POST['project_type_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_type']).'</option>';
										}
										?>
									</select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >Sub-Project Type</label><br>
                                    <input type="text" name="sub_project_type" id="sub_project_type" class="form-control" placeholder="" value="<?php echo $_POST['sub_project_type']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success">Submit</button>
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
					<table class="table table-striped table-hover">
						<tr class="table-danger">
						<th>S.No.</th>
						<th>Project Type</th>
						<th>Sub-Project Type</th>
						<th></th>
						<th></th>
						</tr>
						<?php
						$i=1;
						
						$sql = "select * from master_sub_project_type";
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							$sql = 'select * from master_project_type where sno="'.$row['project_type_id'].'"';
								
								$type = execute_query($sql);
								if(mysqli_num_rows($type)!=0){
									$type = mysqli_fetch_assoc($type);
								}
								else{
									unset($type);
									$type['project_type'] = '';
								}
							
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$type['project_type'].'</td>
							<td>'.$row['sub_project_type'].'</td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?del='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete Entry"></span></a></td>
							</tr>';
						}
						?>
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

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>