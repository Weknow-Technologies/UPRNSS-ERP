<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		$sql = 'insert into uprnss_sub_department (department_id, sub_department,sub_department_hindi) values ("'.$_POST['department_id'].'", "'.$_POST['sub_department'].'","'.$_POST['sub_department_hindi'].'")';
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
		$sql = 'update uprnss_sub_department set 
		department_id = "'.$_POST['department_id'].'",  
		sub_department = "'.$_POST['sub_department'].'",   
		sub_department_hindi = "'.$_POST['sub_department_hindi'].'"   
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
	$_POST['department_id'] = '';
	$_POST['sub_department'] = '';
	$_POST['sub_department_hindi'] = '';
	$_POST['edit_sno'] = '';
}




if(isset($_GET['edit_sno'])){
	$sql = 'select * from uprnss_sub_department where sno="'.$_GET['edit_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['department_id'] = $data['department_id'];
	$_POST['sub_department'] = $data['sub_department'];
	$_POST['sub_department_hindi'] = $data['sub_department_hindi'];
	
}	

if(isset($_GET['del'])){
	$sql = 'delete from uprnss_sub_department where sno="'.$_GET['del'].'"';
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
                                    <label >Deprtment</label><br>
                                    <select class="form-control" name="department_id" id="department_id" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_department_name";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['department_id'])){
												if($_POST['department_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['department_name_hindi']).'</option>';
										}
										?>
									</select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >Sub-Deprtment in English</label><br>
                                    <input type="text" name="sub_department" id="sub_department" class="form-control" placeholder="" value="<?php echo $_POST['sub_department']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >Sub-Deprtment in Hindi</label><br>
                                    <input type="text" name="sub_department_hindi" id="sub_department_hindi" class="form-control" placeholder="" value="<?php echo $_POST['sub_department_hindi']; ?>" tabindex="<?php echo $tab++; ?>">
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
						<th>Deprtment</th>
						<th>Sub-Deprtment in Engish</th>
						<th>Sub-Deprtment in Hindi</th>
						<th></th>
						<th></th>
						<th></th>
						</tr>
						<?php
						$i=1;
						
						$sql = "select * from uprnss_sub_department";
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							$sql = 'select * from uprnss_department_name where sno="'.$row['department_id'].'"';
								
								$department = execute_query($sql);
								if(mysqli_num_rows($department)!=0){
									$department = mysqli_fetch_assoc($department);
								}
								else{
									unset($department);
									$department['department_name_hindi'] = '';
								}
							
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$department['department_name_hindi'].'</td>
							<td>'.$row['sub_department'].'</td>
							<td>'.$row['sub_department_hindi'].'</td>
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