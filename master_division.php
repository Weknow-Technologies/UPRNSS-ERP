<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['division_name'])){
	if($_POST['edit_sno']!=''){
		$sql = 'update uprnss_division set 
		zone_id="'.$_POST['zone_id'].'", 
		division_name="'.$_POST['division_name'].'" 
		where s_no="'.$_POST['edit_sno'].'"';
	}
	else{
		$sql = 'insert into uprnss_division (zone_id, division_name) values ("'.$_POST['zone_id'].'","'.$_POST['division_name'].'")';
    	
	}
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$_POST['zone_id'] = '';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
	}
}
else{
	$_POST['zone_id'] = '';
	$_POST['division_name'] = '';
	$_POST['edit_sno'] = '';
}

if(isset($_GET['id'])){
	$sql = 'select * from uprnss_division where s_no="'.$_GET['id'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	
	$_POST['zone_id'] = $data['zone_id'];
	$_POST['division_name'] = $data['division_name'];
	$_POST['edit_sno'] = $data['s_no'];
}

if(isset($_GET['act'])){
	$sql = 'select * from uprnss_division where s_no="'.$_GET['act'].'"';
	
	$product = mysqli_fetch_assoc(execute_query($sql));
	
	if($product['active_inactive']=='' or $product['active_inactive']=='0'){
		$sql = 'update uprnss_division set active_inactive=1 where s_no='.$_GET['act'];
		execute_query($sql);
	}
	else{
		$sql = 'update uprnss_division set active_inactive=0 where s_no='.$_GET['act'];
		execute_query($sql);
	}
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
	}
}


if(isset($_GET['delid'])){
	$sql='select * from uprnss_project_temp where division_id='.$_GET['delid']; 
	if(mysqli_num_rows(execute_query($sql))!=0){
		$msg.='<div class="alert alert-danger">Cannot Delete,You have Projects related to this division.</div>';
	}
	if($msg==''){
		$sql = 'delete from uprnss_project_temp where s_no='.$_GET['delid'];
		execute_query($sql);
		$msg.='<div class="alert alert-danger">Delete Successful</div>';
	}
}

if(isset($_GET['mid'])){
	$sql = 'update uprnss_project_temp set division_id="'.$_GET['mid'].'" where division_id="'.$_GET['alt'].'"';
	execute_query($sql);
	
	$sql = 'delete from uprnss_division where s_no='.$_GET['alt'];
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Merge Completed</p>';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
	}
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
                                    <label > Zone</label><br>
                                    <select class="form-control" name="zone_id" id="zone_id" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
										<?php
										$query = "select * from master_zone";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['zone_id'])){
												if($_POST['zone_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['zone_name']).'</option>';
										}
										?>
									</select>
								</div>						
							</div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label > Division Name</label><br>
                                    <input type="text" name="division_name" id="division_name" class="form-control" value="<?php echo $_POST['division_name']; ?>" tabindex="<?php echo $tab++; ?>">
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
						<th>zone Name</th>
						<th>Division Name</th>
						<th>Districts</th>
						<th>Officer In-Charge</th>
						<th>Officer Designation</th>
						<th>CUG</th>
						<th>Address</th>
						<th>Geolocation</th>
						<th>Division ID</th>
						<th>Project Count</th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						</tr>
						<?php
						$i=1;
						$sql = 'select * from uprnss_division order by zone_id';
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							$sql = 'select * from uprnss_project_temp where division_id="'.$row['s_no'].'" and (status!="5" or status="0" or status is null or status="1")';
							$count = execute_query($sql);
							if(mysqli_num_rows($count)!=0){
								$count = mysqli_num_rows($count);
							}
							else{
								$count = '0';
							}
							$sql = 'select * from master_zone where sno="'.$row['zone_id'].'"';
								$zone = execute_query($sql);
								if(mysqli_num_rows($zone)!=0){
									$zone = mysqli_fetch_assoc($zone);
								}
								else{
									unset($zone);
									$zone['zone_name'] = '';
								}
							
							echo '<tr>
							<td>'.$i++.'</td>
							<th>'.$zone['zone_name'].'</th>
							<td>'.$row['division_name'].'</td>
							<td>'.$row['district_under_division'].'</td>
							<td>'.$row['officer_incharge'].'</td>
							<td>'.$row['officer_designation'].'</td>
							<td>'.$row['cug_no'].'</td>
							<td>'.$row['address'].'</td>
							<td><a href="https://www.google.com/maps?q='.$row['latitude'].','.$row['longitude'].'" target="_blank">'.$row['latitude'].' '.$row['longitude'].'</a></td>
							<td>'.$row['s_no'].'</td>
							<td>'.$count.'</td>
							<td class="no-print text-center">
								<a href="master_division.php?id='.$row['s_no'].'"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="master_division.php?delid='.$row['s_no'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="#" onclick="return alternate_value('.$row['s_no'].')"><span class="fa fa-compress-alt" aria-hidden="true" data-toggle="tooltip" title="Merge"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="master_division.php?act='.$row['s_no'].'" onclick="return confirm(\'Are you sure you ?\');"><span class="fa fa-ban" aria-hidden="true" data-toggle="tooltip" title="Activate/Inactivate" style="color:#f00"></span>
							</td>
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
function alternate_value(id){
	var alternate = prompt("Please enter product id to merge with this id.","");
	if(!alternate){
		alert("Can not merge without product id.");
		return false;
	}
	else{
		window.open("master_division.php?mid="+id+"&alt="+alternate, '_self');
		return true;
	}
}

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>