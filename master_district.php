<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['district_english'])){
	if($_POST['edit_sno']!=''){
		$sql = 'update uprnss_district set 
		division_id="'.$_POST['division_id'].'", 
		district_name_english="'.$_POST['district_english'].'", 
		district_name_hindi="'.$_POST['district_hindi'].'" 
		where sno="'.$_POST['edit_sno'].'"';
	}
	else{
		$sql = 'insert into uprnss_district (division_id, district_name_english, district_name_hindi) values ("'.$_POST['division_id'].'", "'.$_POST['district_english'].'", "'.$_POST['district_hindi'].'")';
    	
	}
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$_POST['division_id'] = '';
		$_POST['district_english'] = '';
		$_POST['district_hindi'] = '';
		$_POST['edit_sno'] = '';
	}
}
else{
	$_POST['division_id'] = '';
	$_POST['district_english'] = '';
	$_POST['district_hindi'] = '';
	$_POST['edit_sno'] = '';
}

if(isset($_GET['id'])){
	$sql = 'select * from uprnss_district where sno="'.$_GET['id'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	
	$_POST['division_id'] = $data['division_id'];
	$_POST['district_english'] = $data['district_name_english'];
	$_POST['district_hindi'] = $data['district_name_hindi'];
	
	$_POST['edit_sno'] = $data['sno'];
}

if(isset($_GET['delid'])){
	$sql='select * from uprnss_project_temp where district_id='.$_GET['delid']; 
	if(mysqli_num_rows(execute_query($sql))!=0){
		$msg.='<div class="alert alert-danger">Cannot Delete,You have Projects related to this district.</div>';
	}
	if($msg==''){
		$sql = 'delete from uprnss_district where sno='.$_GET['delid'];
		execute_query($sql);
		$msg.='<div class="alert alert-danger">Delete Successful</div>';
	}
}

if(isset($_GET['mid'])){
	$sql = 'update uprnss_project_temp set district_id="'.$_GET['mid'].'" where district_id="'.$_GET['alt'].'"';
	execute_query($sql);
	
	$sql = 'delete from uprnss_district where sno='.$_GET['alt'];
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
							<div class="col-3">
                    			<label >Unit/Division Name</label>
                    			<select class="form-control" name="division_id" id="division_id" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from uprnss_division order by division_name ASC';
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['s_no'].'" ';
										if(isset($_POST['division_id'])){
											if($_POST['division_id']==$data['s_no']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.$data['division_name'].'</option>';
									}
									?>
								</select>
							</div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label > District Name English</label><br>
                                    <input type="text" name="district_english" id="district_english" class="form-control" value="<?php echo $_POST['district_english']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label > District Name Hindi</label><br>
                                    <input type="text" name="district_hindi" id="district_hindi" class="form-control" value="<?php echo $_POST['district_hindi']; ?>" tabindex="<?php echo $tab++; ?>">
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
						<th>Division Name</th>
						<th>District Name English</th>
						<th>District Name Hindi</th>
						<th>District ID</th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						</tr>
						<?php
						$i=1;
						$sql = 'select * from uprnss_district order by district_name_english ASC';
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							$sql = 'select * from uprnss_division where s_no="'.$row['division_id'].'"';
							$row_div = execute_query($sql);
							if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
							}
							else{
								unset($row_div);
								$row_div['division_name'] = '';
							}
							
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row_div['division_name'].'</td>
							<td>'.$row['district_name_english'].'</td>
							<td>'.$row['district_name_hindi'].'</td>
							<td>'.$row['sno'].'</td>
							<td class="no-print text-center">
								<a href="master_district.php?id='.$row['sno'].'"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="master_district.php?delid='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="#" onclick="return alternate_value('.$row['sno'].')"><span class="fa fa-compress-alt" aria-hidden="true" data-toggle="tooltip" title="Merge"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="master_district.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');"><span class="fa fa-ban" aria-hidden="true" data-toggle="tooltip" title="Activate/Inactivate" style="color:#f00"></span>
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
		window.open("master_district.php?mid="+id+"&alt="+alternate, '_self');
		return true;
	}
}

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>