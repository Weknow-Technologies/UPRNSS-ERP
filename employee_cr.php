<?php
include("scripts/settings.php");
 
$msg='';
$error_msg='';
$msg1='';
$tab=1;
// print_r($_POST);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
if(isset($_POST['submit'])){
		$_POST['prativedak'] = isset($_POST['prativedak'])?$_POST['prativedak']:'';
		$_POST['samiksha'] = isset($_POST['samiksha'])?$_POST['samiksha']:'';
		$_POST['swikrita'] = isset($_POST['swikrita'])?$_POST['swikrita']:'';
		
		if($_POST['edit']==''){
			
			$errors = [];
			$fileName=$_FILES["cr_file"]["name"];
			$fileTmpName=$_FILES["cr_file"]["tmp_name"];
			$fileSize=$_FILES["cr_file"]['size'];
			$newFileName=$fileName;
			
			if ($_FILES["cr_file"]["size"] > 5 * 1024 * 1024) {
				$errors[] = "File is too large. Maximum file size allowed is 5MB.";
			}
		
			// Allowed file extensions
			
			$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
		
			// Check if the file extension is allowed
			if (!in_array($fileExtension, $allowedExtensions)) {
				$errors[] = "Only JPG, JPEG, PNG, and PDF files are allowed.";
			}
			
			if (empty($errors)) {
				
				$sql='SELECT `sno`, `emp_id`, `cr_session`,`status` FROM `employee_cr` WHERE cr_session="'.$_POST['cr_session'].'" and emp_id="'.$_POST['employee_id'].'"';
				$result = execute_query($sql);
				$count = mysqli_num_rows($result);
				if($count == 0){
				
					$sql = 'INSERT INTO `employee_cr`(`grade_id`,`designation_id`, `emp_id`, `cr_session`, `prativedak`, `samiksha`, `swikrita`, `status`, `created_by`, `creation_time`,cr_from, cr_to,cr_remark, cr_file) VALUES ("'.$_POST['grade_id'].'","'.$_POST['designation_id'].'","'.$_POST['employee_id'].'", "'.$_POST['cr_session'].'", "'.$_POST['prativedak'].'", "'.$_POST['samiksha'].'", "'.$_POST['swikrita'].'","0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'", "'.$_POST['cr_from'].'", "'.$_POST['cr_to'].'", "'.$_POST['cr_remark'].'","'.$newFileName.'")';
					execute_query($sql);
					
					$getsno=mysqli_insert_id($db);
					$uploadPath="Cr-File/".$getsno.".".$fileExtension;
					
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
					else{
						if(move_uploaded_file($fileTmpName,$uploadPath)){
							execute_query("UPDATE employee_cr SET cr_file = '$uploadPath' where sno='$getsno'");
							$msg .='<p class="alert alert-success">File Upload  Successfully </p>';   

						}
						$msg .= '<div class="alert alert-success text-center">CR Add Successfully</div>';
						$_POST['grade_id'] = '';
						$_POST['designation_id'] = '';
						$_POST['employee_id'] = '';
						$_POST['cr_session'] = '';
						$_POST['prativedak'] = '';
						$_POST['samiksha'] = '';
						$_POST['swikrita'] = '';
						$_POST['cr_from'] = '';
						$_POST['cr_to'] = '';
						$_POST['cr_file'] = '';
						$_POST['cr_remark'] = '';
					}
				}else{
					$msg .= '<div class="alert alert-danger text-center">Already CR Added</div>';
				}
			}
		}
		else{
			
		$oldimgname= $_POST['old_file'];
		// old_file
		// echo "hello update";
		if($_FILES['cr_file']['name']!=""){
			$newerrors = [];
			
			$newfileName=$_FILES["cr_file"]["name"];
			$newfileTmpName=$_FILES["cr_file"]["tmp_name"];
			$newfileSize=$_FILES["cr_file"]['size'];

			if ($newfileSize > 5 * 1024 * 1024) {
				$newerrors[] = "File is too large. Maximum file size allowed is 5MB.";
			}
		
			// Allowed file extensions
			
			$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
		
			// Check if the file extension is allowed
			if (!in_array($newfileExtension, $allowedExtensions)) {
				$newerrors[] = "Only JPG, JPEG, PNG, and PDF files are allowed.";
			}
			
			$getsno = $_POST['edit'];
			$uploadPath="Cr-File/".$getsno.".".$newfileExtension;
			if(move_uploaded_file($newfileTmpName,$uploadPath)){
				execute_query("UPDATE employee_cr SET cr_file = '$uploadPath' where sno='$getsno'");
				$msg .='<p class="alert alert-success">File Upload  Successfully  </p>';   

			}
	
		}
			
			
			$sql = 'UPDATE `employee_cr` SET 
			`grade_id`="'.$_POST['grade_id'].'",
			`designation_id`="'.$_POST['designation_id'].'",
			`emp_id`="'.$_POST['employee_id'].'",
			`cr_session`="'.$_POST['cr_session'].'",
			`cr_from`="'.$_POST['cr_from'].'",
			`cr_to`="'.$_POST['cr_to'].'",
			`cr_remark`="'.$_POST['cr_remark'].'",
			`prativedak`="'.$_POST['prativedak'].'",
			`samiksha`="'.$_POST['samiksha'].'",
			`swikrita`="'.$_POST['swikrita'].'",
			`status`="0",
			`edited_by`="'.$_SESSION['usersno'].'", 
			`edition_time`="'.date("Y-m-d H:i:s").'"	
			
			where sno="'.$_POST['edit'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<div class="alert alert-danger">Update successfully</div>';
			}
		}
		
}
else{
		$_POST['grade_id'] = '';
		$_POST['designation_id'] = '';
		$_POST['employee_id'] = '';
		$_POST['cr_session'] = '';
		$_POST['prativedak'] = '';
		$_POST['samiksha'] = '';
		$_POST['swikrita'] = '';
		$_POST['cr_from'] = '';
		$_POST['cr_to'] = '';
		$_POST['cr_file'] = '';
		$_POST['cr_remark'] = '';
		
		$_POST['edit'] = '';
		// $_GET['edit'] = '';
		
	}
if(isset($_GET['edit'])){
	if($_GET['edit']!=''){
		$sql = 'select * from employee_cr where sno="'.$_GET['edit'].'"';
		$data = mysqli_fetch_assoc(execute_query($sql));
		
		$_POST['grade_id'] = $data['grade_id'];
		$_POST['designation_id'] = $data['designation_id'];
		$_POST['employee_id'] = $data['emp_id'];
		$_POST['cr_session'] = $data['cr_session'];
		$_POST['prativedak'] = $data['prativedak'];
		$_POST['samiksha'] = $data['samiksha'];
		$_POST['swikrita'] = $data['swikrita'];
		$_POST['cr_remark'] = $data['cr_remark'];
		$_POST['cr_from'] = $data['cr_from'];
		$_POST['cr_to'] = $data['cr_to'];
		$path = $data['cr_file'];
		
		$_POST['edit'] = $data['sno'];
	}  
	else{
		$_POST['employee_id'] = '';
		$_POST['appo_id_1'] = '';
		$_POST['appointment_no_1'] = '';
		$_POST['appointment_date_1'] = '';
		$_POST['appoint_division_1'] = '';
		$_POST['post_1'] = '';
		$_POST['joinning_date_1'] = '';
		$_POST['edit'] = '';
	}
}

if(isset($_GET['del'])){
	$sql = 'delete from employee_appointment where sno="'.$_GET['del'].'"';
	execute_query($sql);
	// $msg1 .= '<p class="text text-danger">Data Deleted.</p>';
	$msg1 .= '<div class="alert alert-danger">Data Deleted</div>';
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
	<script>

	function onchangetype()
	{
		var status = document.getElementById("appo_id");
		
		if(status.value == "5")
		{
			document.getElementById("hide").style.display="flex";
		}
		else{
			document.getElementById("hide").style.display="none";
			
			
		}
	}
	
	</script>

   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
   
        <div class="row " >
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="bg-danger text-white text-center mt-1">Add CR</h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body ">
                    	<div class="row ">
							<div class="col-md-3 ">
								<div class="form-group">
									<label >Grade</label><br>
									<select class="form-control" name="grade_id" id="grade_id" tabindex="<?php echo $tab++; ?>" onChange="fill_designation(this.value)">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from dp_type order by type_name ASC";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['grade_id'])){
												if($_POST['grade_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['type_name']).'</option>';
										}
										?>
									</select>
								</div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Designation</label>
                                    <select class="form-control" name="designation_id" id="designation_id"  tabindex="<?php echo $tab++; ?>" onChange="fill_employee(this.value)">
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Employee Name</label>
                                    <select class="form-control" name="employee_id" id="employee_id"  tabindex="<?php echo $tab++; ?>" onChange="fill_emp_details(this.value)">
									</select>
                                </div>
                            </div>						
                        </div>
						
						<div class="mt-1 pt-1" >
							<h4 class="bg-info text-white text-center mt-1">CR Details</h4>
							<div class="border rounded m-2 p-2 border-secondary" id="">
								<div id="add_rows_length" class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label>वर्ष </label>
											<select name="cr_session" class="form-control">
												<?php  
													if(date('m')>3){
														$select_start_session = date('Y');
													}
													else{
														$select_start_session = date('Y')-1;
													}
													$session_start = date('Y')-50;
													for($i = $session_start; $i<=$session_start+100;$i++){
														$end_session = $i+1;
														?>
														<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['cr_session'])){if($_POST['cr_session']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
														<?php
													}
												?>
											</select>
										</div>
									</div>
									<div class="col-md-3">							
										<label>कब से  </label>
										<input type="date" name="cr_from" id="cr_from" class="form-control" value="<?php echo $_POST['cr_from']; ?>" tabindex="<?php echo $tab++; ?>">
									</div>
									<div class="col-md-3">							
										<label>कब तक </label>
										<input type="date" name="cr_to" id="cr_to" class="form-control" value="<?php echo $_POST['cr_to']; ?>" tabindex="<?php echo $tab++; ?>" >
									</div>
									<div class="col-md-3">							
										<label>श्रेणी</label>
										<input type="text" name="cr_remark" id="cr_remark" class="form-control" value="<?php echo $_POST['cr_remark']; ?>" tabindex="<?php echo $tab++; ?>" >
									</div>
									<div class="col-md-3">							
										<label>फाइल </label>
										<input type="file" name="cr_file" id="cr_file" class="form-control" value="<?php echo $_POST['cr_file']; ?>" tabindex="<?php echo $tab++; ?>" <?php echo isset($_GET['edit'])?"":"required" ?>>
									</div>
									<?php 
										if(isset($_GET['edit'])){
											
											?>
											<a href="<?php echo $path;?>" target="_blank" class="d-block text-right">View File</a>
											<input type="hidden" name="old_file" value="<?php echo $path;?>">
											<?php
										}
									
									?>
									<div class="col-md-3">							
										<label>प्रतिवेदक अधिकारी </label>
										<input type="checkbox" name="prativedak" id="prativedak" class="form-control" <?php if($_POST['prativedak']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
									</div>
									<div class="col-md-3">							
										<label>समीक्षा अधिकारी</label>
										<input type="checkbox" name="samiksha" id="samiksha" class="form-control" <?php if($_POST['samiksha']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
									</div>
									<div class="col-md-3">							
										<label>स्वीकृता अधिकारी</label>
										<input type="checkbox" name="swikrita" id="swikrita" class="form-control" <?php if($_POST['swikrita']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
									</div>
									
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success">Submit</button>
									<input type="hidden" id="id" name="id" value="1">
									<input type="hidden" id="edit" name="edit" value="<?php echo $_POST['edit']; ?>">
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
					<?php echo $msg1; ?>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;">
						<tr>
						<th rowspan="2">S.No.</th>
						<th rowspan="2">कर्मचारी का नाम </th>
						<th rowspan="2">कर्मचारी का पद </th>
						<th rowspan="2">वर्ष </th>
						<th colspan="2">कार्य अवधि </th>
						<th rowspan="2">फाइल  </th>
						<th rowspan="2" class="no-print text-center">Edit</th>
						<th rowspan="2" class="no-print text-center">Delete</th>
						</tr>
						<tr>
						<th >कब से </th>
						<th >कब तक </th>
						</tr>
						
						<tr>
							<?php
							for($i=1;$i<=7;$i++){
								echo '<th>'.$i.'</th>';
							}
							?>
							<th class="no-print text-center">8</th>
							<th class="no-print text-center">9</th>
						</tr>
						</thead>
						<tbody>
						<?php
								$i=1;	
								if($_SESSION['usertype']=="sadmin"){
									$sql ='select * from employee_cr';
								}else{
									$sql ='select * from employee_cr where created_by="'.$_SESSION['usersno'].'"';
								}
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									
									$sql = 'SELECT * FROM dp_personal_info 
									LEFT JOIN dp_designation ON dp_personal_info.employee_designation_id = dp_designation.sno 
									where dp_personal_info.sno="'.$row['emp_id'].'"';
									
									$row_emp = execute_query($sql);
									if(mysqli_num_rows($row_emp)!=0){
										$row_emp = mysqli_fetch_assoc($row_emp);
									}
									else{
										unset($row_emp);
										$row_emp['full_name'] = '';
									}
									
									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$row_emp['full_name'].'</td>
									<td>'.$row_emp['designation'].'</td>
									<td>'.$row['cr_session'].'</td>
									<td>'.$row['cr_to'].'</td>
									<td>'.$row['cr_from'].'</td>';
								?>
									<td><a href="<?php echo $row['cr_file'] ?>" target="_blank">View</a></td>
									
								<?php 
								
									echo'<td class="no-print"><a href="'.$_SERVER['PHP_SELF'].'?edit='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit"><span class="far fa-edit" aria-hidden="true"></span></a></td>
									<td class="no-print"><a href="employee_appointment.php?del='.$row['sno'].'" onClick="return confirm(\'Are you sure? \');" > <span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span> </a></td>
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
	
	var actionUrl = 'scripts/ajax.php';
	function fill_payband(val, element_id, selected=''){
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"payband", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.band_name+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}
	function fill_paylevel(val, element_id, selected=''){
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"paylevel", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.level_name+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}	
	function fill_basicpay(val, element_id, selected=''){
		console.log(val);
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"basicpay", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.basic_pay+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}	
	</script>	

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

var actionUrl = 'scripts/ajax.php';
function fill_designation(val, selected=''){
	var data = {"term":"b", "id":"designation", "val":val};

	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(ajaxdata){
			console.log(ajaxdata);
			var txt = '<option value="">--Select--</option>';
			ajaxdata = JSON.parse(ajaxdata);
			$.each(ajaxdata, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(value.id==selected){
					txt += ' selected="selected" ';
				}
				txt += '>'+value.designation+'</option>';
				
			});
          	$("#designation_id").html(txt);
        }
    });
}
function fill_employee(val, selected=''){
	var data = {"term":"b", "id":"employee", "val":val};

	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(ajaxdata){
			console.log(ajaxdata);
			var txt = '<option value="">--Select--</option>';
			ajaxdata = JSON.parse(ajaxdata);
			$.each(ajaxdata, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(value.id==selected){
					txt += ' selected="selected" ';
				}
				txt += '>'+value.emp_code + ' ' + value.full_name + '</option>';
				
			});
          	$("#employee_id").html(txt);
        }
    });
}
function fill_emp_details(val){
	var data = {"term":"b", "id":"emp_detail", "val":val};
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(data){
			var txt = '<option value="">--Select--</option>';
			data = JSON.parse(data);
          	$("#appo_id").val(data.appo_id);
          	$("#appointment_no").val(data.appointment_no);
          	$("#appointment_date").val(data.appointment_date);
          	$("#appoint_division").val(data.appoint_division);
          	$("#appoint_post").val(data.post);
          	$("#pay_scale").val(data.pay_scale);
          	$("#pay_band_id").val(data.pay_band_id);
          	$("#pay_level_id").val(data.pay_level_id);
          	$("#grade_pay_id").val(data.grade_pay_id);
          	$("#joinning_date").val(data.joinning_date);
          	$("#deputation_dep_name").val(data.deputation_dep_name);
          	$("#deputation_from").val(data.deputation_from);
          	$("#deputation_to").val(data.deputation_to);
          	// abc
			$("#place_1").val(data.appoint_division);
          	$("#post_1").val(data.post);
          	$("#pay_scale_1").val(data.pay_scale);
          	$("#pay_band_id_1").val(data.pay_band_id);
          	$("#pay_level_id_1").val(data.pay_level_id);
          	$("#grade_pay_id_1").val(data.grade_pay_id);
        }
    });
}

<?php
	if(isset($_GET['edit'])){
?>
	$(document).ready(function() {
		fill_designation(<?php echo $_POST['grade_id']; ?>, <?php echo $_POST['designation_id']; ?> );
		fill_employee(<?php echo $_POST['designation_id']; ?>, <?php echo $_POST['employee_id']; ?>  );
		
	});
	
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>