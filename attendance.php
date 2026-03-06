<?php
include("scripts/settings.php");
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

//print_r($_POST);
//print_r($_SESSION);

if(isset($_POST['submit_location'])){
	$sql = 'INSERT INTO `attendance`(`user_id`, `datetime`, `selected_location`, `selected_lat`, `selected_long`, `current_lat`, `current_long`, `created_by`, `creation_time`) VALUES ("'.$_SESSION['usersno'].'","'.date('d-m-Y H:i:s').'","'.$_POST['department'].'","'.$_POST['latitude'].'","'.$_POST['longitude'].'","'.$_POST['lat'].'","'.$_POST['long'].'","'.$_SESSION['username'].'","'.date('d-m-Y H:i:s').'")';
	//	echo $sql;
	$res = execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<div class="alert alert-success">Attendance Submitted</div>';	
	}
}
	


/*
if(isset($_POST['department'])){
    $sql= ' insert into invoice_new_project (department,sub_department_id, go_type, go_number, go_date, go_short_number, sanction_cost, sanction_date, central_share, state_share, project_name_english, project_name_hindi, scheme, sub_scheme, project_under, status, created_by, creation_time)
	
    values ("' .$_POST['department'].'", "' .$_POST['sub_department_id'].'", "' .$_POST['go_type'].'", "'.$_POST['go_number'].'", "'.$_POST['go_date'].'", "'.$_POST['go_short_number'].'", "'.$_POST['sanction_cost'].'", "'.$_POST['sanction_date'].'", "'.$_POST['central_share'].'", "'.$_POST['state_share'].'", "'.$_POST['project_name_english'].'", "'.$_POST['project_name_hindi'].'", "'.$_POST['scheme'].'", "'.$_POST['sub_scheme'].'", "'.$_POST['project_under'].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'")';
    execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		
		$inv_id = mysqli_insert_id($db);
		
		for($i=1; $i<=$_POST['add_rows_id']; $i++){
			for($a=1; $a<=$_POST['project_qun_'.$i]; $a++){
				
				$sql= ' insert into transaction_new_project (invoice_id, division_id, district_id, sub_project_name,sub_project_name_hindi, status, created_by, creation_time, civil_status) values ("'.$inv_id.'", "' .$_POST['unit_name_'.$i].'", "'.$_POST['district_name_'.$i].'", "'.$_POST['project_name_english'].' '.$a.'","'.$_POST['project_name_hindi'].' '.$a.'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'","0")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					//$msg .= '<p class="text text-success">Data Saved</p>';
					$msg .= '<div class="alert alert-success">Successfully Add New project  </div>';
				}
			}
		}
		for($i=1; $i<=$_POST['add_rows_id']; $i++){
			for($a=1; $a<=$_POST['project_qun_'.$i]; $a++){
				
				$sql= ' insert into uprnss_project_temp 
				(new_project_id, division_id, district_id, project_name, project_name_hindi, department_id, sub_department_id, scheme, sub_scheme, project_under, status, created_by, creation_time, master_freeze, civil_status)
				
				values ("'.$inv_id.'", "' .$_POST['unit_name_'.$i].'", "'.$_POST['district_name_'.$i].'", "'.$_POST['project_name_english'].' '.$a.'","'.$_POST['project_name_hindi'].' '.$a.'","' .$_POST['department'].'", "' .$_POST['sub_department_id'].'", "'.$_POST['scheme'].'", "'.$_POST['sub_scheme'].'", "'.$_POST['project_under'].'","2", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'","0","0")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$project_id = mysqli_insert_id($db);
					switch($_POST['go_type']){
						case '1':{
							$sql = 'update uprnss_project_temp set 
								admin_go_no = "'.$_POST['go_number'].'",
								admin_go_date = "'.$_POST['go_date'].'"
								where sno="'.$project_id.'"';
								execute_query($sql);
								
							break;
						}
						case '2':{
							$sql = ' update uprnss_project_temp set 
							financial_go_no = "'.$_POST['go_number'].'",
							financial_go_date = "'.$_POST['go_date'].'",
							sanction_cost =  "'.$_POST['sanction_cost'].'",
							sanction_date =  "'.$_POST['sanction_date'].'",
							central_share = "' .$_POST['central_share'].'",
							state_share = "'.$_POST['state_share'].'"
							where sno="'.$project_id.'"';
							execute_query($sql);
							
							break;
						}
						case '3':{
							$sql = ' update uprnss_project_temp set 
							admin_go_no = "'.$_POST['go_number'].'",
							financial_go_no = "'.$_POST['go_number'].'",
							admin_go_date = "'.$_POST['go_date'].'",
							financial_go_date = "'.$_POST['go_date'].'",
							sanction_cost =  "'.$_POST['sanction_cost'].'",
							sanction_date =  "'.$_POST['sanction_date'].'",
							central_share = "' .$_POST['central_share'].'",
							state_share = "'.$_POST['state_share'].'"
							where sno="'.$project_id.'"';
							execute_query($sql);
							
							break;
						}
					}
				}
				$msg .= '<div class="alert alert-success">Successfully Add New project  </div>';
			}
		}
	}
}
else{
	$_POST['department'] = '';
	$_POST['sub_department_id'] = '';
	$_POST['go_type'] = '';
	$_POST['go_number'] = '';
	$_POST['go_date'] = '';
	$_POST['go_short_number'] = '';
	$_POST['sanction_cost'] = '';
	$_POST['sanction_date'] = '';
	$_POST['central_share'] = '';
	$_POST['state_share'] = '';
	$_POST['project_name_english'] = '';
	$_POST['project_name_hindi'] = '';
	$_POST['scheme'] = '';
	$_POST['sub_scheme'] = '';
	$_POST['project_under'] = '';
	$_POST['unit_name_1'] = '';
	$_POST['district_name_1'] = '';
	$_POST['project_qun_1'] = '';
	
	$_POST['add_rows_id'] = 1;
}

if(isset($_GET['edit_sno'])){
	$sql = 'select * from invoice_new_project where sno="'.$_GET['edit_sno'].'"';
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	// print_r($invoice);
	$_POST['department'] = $invoice['department'];
	$_POST['sub_department_id'] = $invoice['sub_department_id'];
	$_POST['go_type'] = $invoice['go_type'];
	$_POST['go_number'] = $invoice['go_number'];
	$_POST['go_date'] = $invoice['go_date'];
	$_POST['go_short_number'] = $invoice['go_short_number'];
	$_POST['sanction_cost'] = $invoice['sanction_cost'];
	$_POST['sanction_date'] = $invoice['sanction_date'];
	$_POST['central_share'] = $invoice['central_share'];
	$_POST['state_share'] = $invoice['state_share'];
	$_POST['project_name_english'] = $invoice['project_name_english'];
	$_POST['project_name_hindi'] = $invoice['project_name_hindi'];
	$_POST['scheme'] = $invoice['scheme'];
	$_POST['sub_scheme'] = $invoice['sub_scheme'];
	$_POST['project_under'] = $invoice['project_under'];
	
	$sql = 'select * from transaction_new_project where invoice_id="'.$_GET['edit_sno'].'"';
	// echo $sql.'<br>';
	$result_rcpt = execute_query($sql);
	if(mysqli_num_rows($result_rcpt)!=0){
		$_POST['add_rows_id'] = mysqli_num_rows($result_rcpt);
		$i=1;
		while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
			//echo '<h1>Test '.$i.'</h1>'.$_POST['add_rows_id_revised_remittance'].'<br/>';
			$_POST['unit_name_'.$i] = $row_rcpt['division_id'];
			$_POST['district_name_'.$i] = $row_rcpt['district_id'];
			$_POST['project_qun_'.$i] = $row_rcpt['sub_project_name'];
			$i++;
		}
	}
	else{
		
		$_POST['unit_name_1'] = '';
		$_POST['district_name_1'] = '';
		$_POST['project_qun_1'] = '';

	}


	
	$_POST['edit_sno'] = $invoice['sno'];
}
	
	if(isset($_GET['delid'])){
		$sql = 'update invoice_new_project set status="5" where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		
		$sql = 'update transaction_new_project set status="5" where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'update uprnss_project_temp set status="5" where new_project_id="'.$_GET['delid'].'"';
		execute_query($sql);
		
		$msg .= '<div class="alert alert-warning">Data Delete</div>';
	}
*/
?>
	<div>	
		<form id="form" name="form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
						
		<div class="row" >
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
					<h5 class="card-title"></h5>
					<?php
							if($msg!=''){
								echo '<h5>'.$msg.'</h5>';
							}
							?>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-3 ">
								<div class="form-group">
									<label ><h6>Location</h6></label>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="get_map('ofc', this.value);">
										<option value="">--- Select ---</option>
										<option value="office"><?php echo $_SESSION['unit_name']; ?></option>
										<?php
										/*$query = 'select * from uprnss_project_temp where division_id in ('.implode(",", $_SESSION['divisions']).') and (status="0" or status is null or status="1") ';
										//echo $sql;
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['department'])){
												if($_POST['department']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_name_hindi']).'</option>';
										}*/
										?>
									</select>
								</div>
                            	<div class="row" id="ofc">
									
								</div>
                            </div>
                            <div class="col-md-4">
                            	<div class="row" id="client">
									
								</div>
                            </div>
						</div>
						   	
					</div>
				</div>
			</div>
		</div>
			
		</form>
	</div>
	
	</div>
<script>

x = document.getElementById("map_container");
	
var actionUrl = 'scripts/ajax.php';

function get_map(type, val){
	var data = {"term":"b", "id":"location", "type":type, "val":val};
	//alert(val);
	if(val!=''){
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(data){
				data = JSON.parse(data);
				$.each(data, function(key, value){
					txt = '<div class="col-md-12"><input type="hidden" name="latitude" value="'+value.latitude+'"><input type="hidden" name="longitude" value="'+value.longitude+'"><button type="button" class="btn btn-info" onClick="getLocation();">Update Attendance</button><div id="map_container"><iframe id="officemap" src="https://maps.google.com/maps?q='+value.latitude+','+value.longitude+'&hl=en&z=17&amp;output=embed" width="100%" height="100%" style="border:1px solid; border-radius:10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div></div>';

				});
				$("#"+type).html(txt);
			}
		});
	}
	else{
		$("#"+type).html('');
	}
}	
	
function getLocation() {
	//console.log('test');
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(showPosition, showError);
	} 
	else { 
		x.innerHTML = "Geolocation is not supported by this browser.";
	}
}	
	
function showPosition(position) {
	/*$("#latitude").val(position.coords.latitude);
	$("#longitude").val(position.coords.longitude);
	$("#lat").val(position.coords.latitude);
	$("#long").val(position.coords.longitude);
	var url = "https://maps.google.com/maps?q="+position.coords.latitude+","+position.coords.longitude+"&hl=en&z=18&output=embed";
	//console.log(url);*/
	
	txt = '<div class="col-md-12"><h5>Your Current Location</h5></div><div class="col-md-6"><label>Latitude</label><input type="text" id="lat" name="lat" readonly value="'+position.coords.latitude+'" class="form-control"></div><div class="col-md-6"><label>Longitude</label><input type="text" id="long" name="long" readonly value="'+position.coords.longitude+'" class="form-control"></div><div class="col-md-12" id="map_container"><iframe id="googlemap" src="https://maps.google.com/maps?q='+position.coords.latitude+','+position.coords.longitude+'&hl=en&z=17&amp;output=embed" width="100%" height="100%" style="border:1px solid; border-radius:10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div><div class="col-md-12"><button type="submit" class="btn btn-success" name="submit_location" value="submit_location">Submit Attendance</button></div>';
	
	$("#client").html(txt);
}	

function showError(error) {
	x = document.getElementById("map_container");
  switch(error.code) {
    case error.PERMISSION_DENIED:
      x.innerHTML = "User denied the request for Geolocation."
      break;
    case error.POSITION_UNAVAILABLE:
      x.innerHTML = "Location information is unavailable."
      break;
    case error.TIMEOUT:
      x.innerHTML = "The request to get user location timed out."
      break;
    case error.UNKNOWN_ERROR:
      x.innerHTML = "An unknown error occurred."
      break;
  }
} 
</script>	
	
<?php
page_footer_start();
?>	
<?php		
page_footer_end();
?>

