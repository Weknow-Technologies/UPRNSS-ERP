<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
// print_r($_POST);
if(isset($_POST['submit'])){
	$sql = 'select uprnss_project_temp.sno as sno, division_name,sub_department_hindi, uprnss_project_temp.department_id, uprnss_project_temp.sub_department_id, district_name_hindi, department_name_hindi, project_name, project_name_hindi, project_type, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date,scheme, sub_scheme, project_status_1, master_freeze,uprnss_project_temp.status 
	from uprnss_project_temp 
	left join uprnss_district on uprnss_district.sno = district_id
	left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
	left join uprnss_department_name on uprnss_department_name.sno = department_id
	left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
	where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and (uprnss_project_temp.status!="5" or uprnss_project_temp.status="0" or uprnss_project_temp.status is null )';
	//print_r($_POST);
	// echo $sql;
	if($_POST['department']!=''){
		$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
	}
	if($_POST['sub_department_id']!=''){
		$sql .= ' and sub_department_id="'.$_POST['sub_department_id'].'"';
	}
	if($_POST['district']!=''){
		$sql .= ' and district_id="'.$_POST['district'].'"';
	}
	if($_POST['division_name']!=''){
		$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
	}
	if($_POST['project_type']!=''){
		$sql .= ' and project_type="'.$_POST['project_type'].'"';
	}
							

	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		if($_POST['department_'.$row['sno']]!=$row['department_id']){
			/*Update query*/
			$sql = 'update uprnss_project_temp set department_id="'.$_POST['department_'.$row['sno']].'" where sno="'.$row['sno'].'"';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$msg .= '<p class="text text-success">Update Deparment</p>';
				}
			}
		if($_POST['sub_department_id_'.$row['sno']]!=$row['sub_department_id'] || $row['sub_department_id']==''){
			/*Update query*/
			$sql = 'update uprnss_project_temp set sub_department_id="'.$_POST['sub_department_id_'.$row['sno']].'" where sno="'.$row['sno'].'"';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$msg .= '<p class="text text-success">Update Sub Deparment</p>';
				}
		}
		if($_POST['scheme_'.$row['sno']]!=$row['scheme'] || $row['scheme']==''){
			/*Update query*/
			$sql = 'update uprnss_project_temp set scheme="'.$_POST['scheme_'.$row['sno']].'" where sno="'.$row['sno'].'"';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$msg .= '<p class="text text-success">Scheme Updated</p>';
				}
		}
		if($_POST['sub_scheme_'.$row['sno']]!=$row['sub_scheme'] || $row['sub_scheme']==''){
			/*Update query*/
			$sql = 'update uprnss_project_temp set sub_scheme="'.$_POST['sub_scheme_'.$row['sno']].'" where sno="'.$row['sno'].'"';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$msg .= '<p class="text text-success">Sub-Scheme Updated</p>';
				}
		}
		if($_POST['project_type_'.$row['sno']]!=$row['project_type']){
			/*Update query*/
			$sql = 'update uprnss_project_temp set project_type="'.$_POST['project_type_'.$row['sno']].'" where sno="'.$row['sno'].'"';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$msg .= '<p class="text text-success">Update Project Type</p>';
				}
			}
}
}
else{
	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['department'] = '';
		$_POST['sub_department_id'] = '';
		$_POST['district'] = '';
		$_POST['division_name'] = '';
		$_POST['project_name'] = '';
		$_POST['project_name_hindi'] = '';
		$_POST['project_name_hindi_unicode'] = '';
		$_POST['project_type'] = '';
	}
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
	
	

#update_department input{
	margin:0px !important;
	padding:0px !important;
	text-align:center !important;
	font-size:11px !important;
	
}
#update_department td, #update_department th{
	margin:0px!important;
	padding:0px!important;
	font-size:11px;
	/* border: 1px solid black!important;*/
}


</style>

<?php
page_header_end();
page_sidebar();

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row no-print" >
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
                    	<div class="row">
                    		<div class="col-md-3 ">
								<div class="form-group">
									<label >Deprtment</label><br>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_sub_department(this.value)">
										<option value="">--- Select ---</option>
										<?php
										if($_SESSION['usertype']=='sadmin' || $_SESSION['usertype']=='1'){
										$query = 'select * from uprnss_department_name';
										}
										else{
										$query = 'select * from uprnss_department_name where sno in ('.implode(",", $_SESSION['department']).') ';
										}
										
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['department'])){
												if($_POST['department']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['department_name_hindi']).'</option>';
										}
										?>
									</select>
								</div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Sub-Deprtment</label>
                                    <select class="form-control" name="sub_department_id" id="sub_department_id" value="<?php echo $_POST['sub_department_id']; ?>" tabindex="<?php echo $tab++; ?>" >
									</select>
                                </div>
                            </div>
							<div class="col-3">
                    			<label >Unit/Division Name</label>
                    			<select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from uprnss_division order by division_name ASC';
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['s_no'].'" ';
										if(isset($_POST['division_name'])){
											if($_POST['division_name']==$data['s_no']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.$data['division_name'].'</option>';
									}
									?>
								</select>
							</div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >आच्छादित जनपद</label>
                                    <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
                                    	<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_district";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['district'])){
												if($_POST['district']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.$data['district_name_hindi'].'</option>';
										}
										?>
									</select>
                                </div>
                            </div>
						    <div class="col-md-3">
                                <div class="form-group">
                                    <label >Project Name</label><br>
                                    <input type="text" name="project_name" id="project_name" class="form-control" placeholder="" value="<?php echo $_POST['project_name']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >Project Name in Hindi (KrutiDev)</label><br>
                                    <input type="text" name="project_name_hindi" id="project_name_hindi" class="form-control" placeholder="" value="<?php echo $_POST['project_name_hindi']; ?>" tabindex="<?php echo $tab++; ?>" onChange="krutiunicode()" onKeyDown="krutiunicode()" onKeyPress="krutiunicode()" onKeyUp="krutiunicode()">
                                </div>
                            </div>
                            <div class="col-md-3">
                            	<div class="form-group">
                                    <label >Project Name in Hindi (Unicode)</label><br>
                                    <input type="text" name="project_name_hindi_unicode" id="project_name_hindi_unicode" class="form-control" placeholder="" value="<?php echo $_POST['project_name_hindi_unicode']; ?>" tabindex="<?php echo $tab++; ?>">
                                    
                                    
									<!--<script language="javascript" type="text/javascript">CreateCustomHindiTextArea("languageTextareaNew","",30,5,true);</script>-->
                                </div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Project Type :</label>
									<select name="project_type" id="project_type" class="form-control" onchange="onchangetype()" >
										<option value="">--- Select ---</option>
										<option value="1"<?php echo ($_POST['project_type']==1?'selected':''); ?>>Ho Level</option>
										<option value="2"<?php echo ($_POST['project_type']==2?'selected':''); ?>>Division Level</option>
									</select>
								</div>
							</div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									
									<button type="submit" name="search" class="btn btn-primary">Search</button>
									<input type="hidden" id="id" name="id" value="1">
									<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>

	
	
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table" id="update_department">
						<thead>
						<tr>
						<th>S.No.</th>
						<th>Project Type</th>
						<th>Division Name</th>
						<th>District Name</th>
						<th>Department/Sub-Department Name</th>
						<th>Sub-Department Name</th>
						<th>Scheme Name</th>
						<th>Sub-Scheme Name</th>
						<th>Project Name Hindi</th>
						<th>Sanction Date &amp; Cost</th>
						<th>G.O Number</th>
						
						</tr>
						<tr>
							<?php
							for($i=1;$i<=11;$i++){
								echo '<th>'.$i.'</th>';
							}
							?>
						</tr>
						</thead>
						<tbody>
						<?php
						$i=1;
						
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name,sub_department_hindi, uprnss_project_temp.department_id, uprnss_project_temp.sub_department_id, district_name_hindi, department_name_hindi, project_name, project_name_hindi, project_type, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date,scheme, sub_scheme, project_status_1, master_freeze,uprnss_project_temp.status 
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
								where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and (uprnss_project_temp.status!="5" or uprnss_project_temp.status="0" or uprnss_project_temp.status is null )';
								//print_r($_POST);
								// echo $sql;
								if($_POST['department']!=''){
									$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
								}
								if($_POST['sub_department_id']!=''){
									$sql .= ' and sub_department_id="'.$_POST['sub_department_id'].'"';
								}
								if($_POST['district']!=''){
									$sql .= ' and district_id="'.$_POST['district'].'"';
								}
								if($_POST['division_name']!=''){
									$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
								}
								if($_POST['project_type']!=''){
									$sql .= ' and project_type="'.$_POST['project_type'].'"';
								}
							
								// echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									$sql = 'select * from master_projoect_status_1 where sno="'.$row['project_status_1'].'"';
									// echo $sql;
									// $status1 = mysqli_fetch_assoc(execute_query($sql));
									$status1 = execute_query($sql);
									if(mysqli_num_rows($status1)!=0){
										$status1 = mysqli_fetch_assoc($status1);
									}
									else{
										unset($status1);
										$status1['status_1'] = '';
									}

									echo '<tr>
									<td>'.$i++.'</td>
									<td>';
									?><select name="project_type_<?php echo $row['sno']?>" id="project_type" class="form-control" onchange="onchangetype()" >
									<?php
											echo '<option value="">--- Select ---</option>';
											echo'<option value="1" '.($row['project_type']==1?'selected':'').' >Ho Level</option>';
											echo'<option value="2" '.($row['project_type']==2?'selected':'').'>Division Level</option>';
											
										echo'</select>
									</td>
									
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									
									<td>';
										$query = "select * from uprnss_department_name";
										$run = mysqli_query($db,$query);
										?>
										<select class="form-control" name="department_<?php echo $row['sno']; ?>" id="department_<?php echo $row['sno']; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_sub_department_row(this.value, <?php echo $row['sno']; ?>)">
										<option value="">--- Select ---</option>
										<?php
										while($data = mysqli_fetch_array($run))	{
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['search'])){
												if($row['department_id']==$data['sno']){
													echo ' selected="Selected"';
												}
												echo '>'.$data['department_name_hindi'].'</option>';
											}
										}
										echo'</select>
									</td>
									<td>';
									?>
										<select class="form-control" name="sub_department_id_<?php echo $row['sno']; ?>" id="sub_department_id_<?php echo $row['sno']; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_scheme_row($('#department_<?php echo $row['sno']; ?>').val(), <?php echo $row['sno']; ?>);">
										<option value="">--- Select ---</option>
										<?php
											$query = "select * from uprnss_sub_department where department_id='".$row['department_id']."'";
											//echo $sql;
											$run = mysqli_query($db,$query);
											while($data = mysqli_fetch_assoc($run)){
												echo '<option value="'.$data['sno'].'"';
												if(isset($_POST['search'])){
													if($row['sub_department_id']==$data['sno']){
														echo ' selected="Selected"';
													}
												echo '>'.$data['sub_department_hindi'].'</option>';
												}
											}
											
											
										echo'</select>
									</td>
									<td>';
										$query = "select * from uprnss_project_scheme where 1=1";
										if($row['department_id']!=''){
											$query .= ' and department_id="'.$row['department_id'].'"';
										}
										if($row['sub_department_id']!=''){
											$query .= ' and sub_department_id="'.$row['sub_department_id'].'"';
										}
										$run = mysqli_query($db,$query);
										?>
										<select class="form-control" name="scheme_<?php echo $row['sno']; ?>" id="scheme_<?php echo $row['sno']; ?>" tabindex="<?php echo $tab++; ?>" >
										<option>-select-</option>
										<?php
										while($data = mysqli_fetch_array($run))	{
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['search'])){
												if($row['scheme']==$data['sno']){
													echo ' selected="Selected"';
												}
												echo '>'.$data['scheme_name_hindi'].'</option>';
												}
											}
										echo'</select>
									</td>
									
									<td>';
										$query = "select * from uprnss_project_sub_scheme";
										$run = mysqli_query($db,$query);
										?>
										<select class="form-control" name="sub_scheme_<?php echo $row['sno']; ?>" id="sub_scheme" tabindex="<?php echo $tab++; ?>" >
											<option>-select-</option>
										<?php
										while($data = mysqli_fetch_array($run))	{
											echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['search'])){
											if($row['sub_scheme']==$data['sno']){
												echo ' selected="Selected"';
											}
											echo '>'.$data['sub_scheme_hindi'].'</option>';
											}
										}
										echo'</select>
									</td>
									
									<td>'.$row['project_name_hindi'].'</td>
									<td>'.($row['sanction_cost']!=''?$row['sanction_date'].' ('.$row['sanction_cost'].').':'').'</td>
									<td>'.$row['admin_go_no'].'</td>
									
									
									</tr>';
								}
							}
										
						
						?>
						</tbody>
					</table>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success">Update Department</button>
								
							</div> 
						</div>
					</div>
					</div>
					
                </div>
            </div>
		</div>
	</form>
	
	
	
	
	
<?php
page_footer_start();
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

var actionUrl = 'scripts/ajax.php';
function fill_sub_department(val, selected=''){
	var data = {"term":"b", "id":"sub_dep", "val":val};
	
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
				txt += '>'+value.sub_department_hindi+'</option>';
				
			});
          	$("#sub_department_id").html(txt);
        }
    });
}
	
function fill_sub_department_row(val, element_id, selected=''){
	fill_scheme_row(val, element_id, selected);
	var data = {"term":"b", "id":"sub_dep", "val":val};
	$("#sub_department_id_"+element_id).html('<option value="">--Select--</option>');
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(ajaxdata){
			// console.log(ajaxdata);
			var txt = '<option value="">--Select--</option>';
			ajaxdata = JSON.parse(ajaxdata);
			$.each(ajaxdata, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(value.id==selected){
					txt += ' selected="selected" ';
				}
				txt += '>'+value.sub_department_hindi+'</option>';
				
			});
          	$("#sub_department_id_"+element_id).html(txt);
        }
    });
}	

function fill_scheme_row(val, element_id, selected=''){
	var sub_dept = $("#sub_department_id_"+element_id).val();
	if(sub_dept!=''){
		var data = {"term":"b", "id":"scheme", "val":val, "subval":sub_dept};
	}
	else{
		var data = {"term":"b", "id":"scheme", "val":val};	
	}
	
	$("#scheme_"+element_id).html('<option value="">--Select--</option>');
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
				txt += '>'+value.scheme_name_hindi+'</option>';
				
			});
          	$("#scheme_"+element_id).html(txt);
        }
    });
}	
	
<?php
	if(isset($_POST['search'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>  );
		
	});
	
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>