<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();



if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		$sql = 'insert into general_settings (`desc` , `rate`, remark, created_by, creation_time)values ("'.$_POST['desc'].'","'.$_POST['rate'].'", "'.$_POST['remark'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
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
		$sql = 'update general_settings set 
		`rate` = "'.$_POST['rate'].'",
		`desc` = "'.$_POST['desc'].'",
		`remark` = "'.$_POST['remark'].'",
		`edited_by` = "'.$_SESSION['username'].'", 
		`edition_time` = "'.date("Y-m-d H:i:s").'"  
		   
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
	$_POST['desc'] = '';
	$_POST['rate'] = '';
	$_POST['remark'] = '';
	$_POST['edit_sno'] = '';
}




if(isset($_GET['edit_sno'])){
	$sql = 'select * from general_settings where sno="'.$_GET['edit_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['desc'] = $data['desc'];
	$_POST['rate'] = $data['rate'];
	$_POST['remark'] = $data['remark'];
	
}	

if(isset($_GET['del'])){
	$sql = 'delete from general_settings where sno="'.$_GET['del'].'"';
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
                                    <label >Desc</label><br>
                                    <input type="text" name="desc" id="desc" class="form-control" placeholder="" value="<?php echo $_POST['desc']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >Rate</label><br>
                                    <input type="text" name="rate" id="rate" class="form-control" placeholder="" value="<?php echo $_POST['rate']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >Remark</label><br>
                                    <input type="text" name="remark" id="remark" class="form-control" placeholder="" value="<?php echo $_POST['remark']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-12 text-center">
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
					<table class="table table-striped table-hover table-bordered" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th>Sr.No.</th>
								<th>Desc</th>
								<th>Rate</th>
								<th>Remark</th>
								<th>&nbsp; </th>
								<th>&nbsp; </th>
								
							</tr>
						</thead>
						 
						<tbody>
						<?php
						$i=1;
						$sql = 'select * from general_settings order by sno desc';
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							echo '<tr>
							<td>'.$i++.'</td>
							<td><b>(desc):</b>'.$row['desc'].'</td>
							<td><b>(rate):</b>'.$row['rate'].'</td>
							<td><b>(remark):</b>'.$row['remark'].'</td>
							
							<td><a href="'.$_SERVER['PHP_SELF'].'?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?del='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete Entry"></span></a></td>
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

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>