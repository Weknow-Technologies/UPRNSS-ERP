<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		$sql = 'insert into uprnss_architect (full_name_english, type, address, valid_to) values ("'.$_POST['architect_name'].'", "'.$_POST['architect_type'].'", "'.$_POST['address'].'", "'.$_POST['valid_to'].'")';
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
	else{
		$sql = 'update uprnss_architect set 
		full_name_english = "'.$_POST['architect_name'].'",  
		type = "'.$_POST['architect_type'].'" ,  
		address = "'.$_POST['address'].'" ,
		valid_to = "'.$_POST['valid_to'].'"  
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
	$_POST['architect_name'] = '';
	$_POST['architect_type'] = '';
	$_POST['address'] = '';
	$_POST['valid_to'] = '';
	$_POST['edit_sno'] = '';
}

if(isset($_GET['edit_sno'])){
	$sql = 'select * from uprnss_architect where sno="'.$_GET['edit_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['architect_name'] = $data['full_name_english'];
	$_POST['architect_type'] = $data['type'];
	$_POST['address'] = $data['address'];
	$_POST['valid_to'] = $data['valid_to'];
}	

if(isset($_GET['del'])){
	$sql = 'delete from uprnss_architect where sno="'.$_GET['del'].'"';
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >Architect Name</label><br>
                                    <input type="text" name="architect_name" id="architect_name" class="form-control" placeholder="" value="<?php echo $_POST['architect_name']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >Architect Type</label><br>
                                    <select name="architect_type" id="architect_type" class="form-control" tabindex="<?php echo $tab++; ?>">
										<option>----Select----</option>
                                    	<option value="1" <?php if($_POST['architect_type']=='1'){ echo ' selected="selected" ';}?>>Architect</option>
                                    	<option value="2" <?php if($_POST['architect_type']=='2'){ echo ' selected="selected" ';}?>>Structural Architect</option>
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Address</label><br>
                                    <input type="text" name="address" id="address" class="form-control" placeholder="" value="<?php echo strtotime($_POST['address']); ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Valid To</label><br>
                                    <input type="date" name="valid_to" id="valid_to" class="form-control" placeholder="" value="<?php echo $_POST['valid_to']; ?>" tabindex="<?php echo $tab++; ?>">
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
						<tr class="table-success">
						<th>S.No.</th>
						<th>Architect Type</th>
						<th>Architect Name</th>
						<th>Address</th>
						<th>Valid To</th>
						<th></th>
						<th></th>
						</tr>
						<?php
						$i=1;
						$sql = 'select * from uprnss_architect';
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.(($row['type']==1)?'Architect':'Structure Architect').'</td>
							<td>'.$row['full_name_english'].'</td>
							<td>'.$row['address'].'</td>
							<td>'.$row['valid_to'].'</td>
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