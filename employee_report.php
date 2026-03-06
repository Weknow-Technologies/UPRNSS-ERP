<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['department'] = '';
		$_POST['division_id'] = '';
		$_POST['district'] = '';
		$_POST['division_name'] = '';
		$_POST['project_name'] = '';
		$_POST['project_name_hindi'] = '';
		$_POST['project_name_hindi_unicode'] = '';
		$_POST['project_type'] = '';
		$_POST['work_start_date'] = '';
		$_POST['date_from'] = '';
		$_POST['date_to'] = '';
		$_POST['date_type'] ='';
		$_POST['sanction_cost'] = '';
		$_POST['type'] = '';
		$_POST['type1'] = '';
		$_POST['tot_exp_project'] = '';
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
	
	@media print {
		table {
		page-break-inside: avoid;
	}
	}
</style>


<?php
page_header_end();
page_sidebar();

?>

	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					<table width="100%" class="table table-striped table-hover rounded" id="general_stat_table">	
						<tr >
							<th width="16%">Date Type</th>
							<th width="18%"><select name="date_type" id="date_type" class="form-control" >
									<option value="">--- Select ---</option>
                                    <option value="date_of_birth" <?php echo ($_POST['date_type']=='date_of_birth'?' selected="selected"':''); ?>>Date of Birth</option>
									<option value="date_of_joinning" <?php echo ($_POST['date_type']=='date_of_joinning'?' selected="selected"':''); ?>>Date of Joinning</option>
                                    
							    </select>
							</th>
							<th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%">To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
						</tr>
						<tr >
							<th>Employee Designation </th>							
							<th width="18%">
								<select class="form-control" name="employee_designation" id="employee_designation" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from dp_designation ORDER BY ABS(sort_no)";
									$run = execute_query($query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_GET['edit'])){
											if($row['employee_designation_id']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['designation']).'</option>';
									}
									?>
								</select>
							</th>
							<th>Employee Type </th>
							<th width="18%"><select  name="employee_type" id="employee_type" tabindex="<?php echo $tab++; ?>" class="form-control">
								<option value="">--Select--</option>
								<?php 
								$query = "select * from dp_type";
								$run = execute_query($query);
								while($data = mysqli_fetch_array($run)){
									echo '<option value="'.$data['sno'].'" ';
									if(isset($_GET['edit'])){
										if($row['employee_type_id']==$data['sno']){
											echo ' selected="Selected"';
										}
									}
									echo '>'.trim($data['type_name']).'</option>';
								}
								?>
							</select></th>						
							<th>Employee Group</th>
							<th width="18%">
								<select  name="employee_category" id="employee_category" tabindex="<?php echo $tab++; ?>" class="form-control">
									<option value="">--Select--</option>
									<?php 
									$query = "select * from dp_category";
									$run = execute_query($query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_GET['edit'])){
											if($row['employee_category_id']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['category_name']).'</option>';
									}
									?>
								</select>
							</th>
						</tr>
						<tr >
							<th>Category</th>
							<th width="18%">
								<select name="caste" class="form-control" tabindex="<?php echo $tab++; ?>">
									<option value=""> --Select--</option>
									<option value="Un-Reserved/General" ">Un-Reserved /General</option>
									<option value="OBC" <?php if(isset($row['caste']))if($row['caste']=='OBC'){ echo ' selected="Selected"'; }?>>OBC</option>
									<option value="SC" <?php if(isset($row['caste']))if($row['caste']=='SC'){ echo ' selected="Selected"'; }?>>SC</option>
									<option value="ST" <?php if(isset($row['caste']))if($row['caste']=='ST'){ echo ' selected="Selected"'; }?>>ST</option>
									<option value="Minority" <?php if(isset($row['caste']))if($row['caste']=='Minority'){ echo ' selected="Selected"'; }?>>Minority</option>
									<option value="EWS" <?php if(isset($row['caste']))if($row['caste']=='EWS'){ echo ' selected="Selected"'; }?>>EWS</option>
									
								</select>
							</th>
							<th>Division</th>
							<th>
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
							</th>						
							<th></th>
							<th>
								
							</th>
						</tr>
					</table>
					<button type="submit" name="search" class="btn btn-primary">Search</button>
					<input type="hidden" id="id" name="id" value="1">
					<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
				
			</div>
		</div>
		</form>
	</div>
	
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;" >
							<tr>
								<th>S.No.</th>
								<th scope="col">Old Employee Code</th>
								<th scope="col">Division</th>
								<th scope="col">Date Of Joinning </th>
								<th scope="col">Full Name</th>
								<th scope="col">Date Of Birth</th>
								<th scope="col">Employee Designation</th>
								<th scope="col">Employee Type</th>
								<th scope="col">Employee Group</th>
					<!---		<th scope="col">Employee Sub-Group</th> --->
								<th scope="col">Category</th>
								<th scope="col">Father Name</th>
								<th scope="col">Contact Number</th>
								<th scope="col">Email Id</th>
								<th scope="col">Aadhar Number</th>
								
								
								<th class="no-print text-center">view</th>
								<th class="no-print text-center">Edit</th>
								<th class="no-print text-center">Service Book</th>
								<th class="no-print text-center">Print</th>
							</tr>
							<tr>
								<?php
								for($i=1;$i<=14;$i++){
									echo '<th>'.$i.'</th>';
								}
								?>
								
								
							<th class="no-print text-center">15</th>
							<th class="no-print text-center">16</th>
							<th class="no-print text-center">17</th>
							<th class="no-print text-center">18</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=1;
							if(isset($_POST['search'])){
								$sql = 'SELECT dp_personal_info.sno, old_employee_code, division_id, employee_type_id, employee_category_id, full_name, father_name,date_of_birth, designation, caste, c_number, aadhar_number , date_of_joinning,email
								
								from dp_personal_info
								LEFT JOIN dp_designation ON dp_personal_info.employee_designation_id = dp_designation.sno where 1=1';
										
									if($_POST['employee_designation']!=''){
										$sql .= ' AND dp_designation.sno="'.$_POST['employee_designation'].'"';
									}
									if($_POST['division_id']!=''){
										
										$sql .= ' AND division_id="'.$_POST['division_id'].'"';
									}
									if($_POST['employee_type']!=''){
										$sql .= ' AND dp_personal_info.employee_type_id="'.$_POST['employee_type'].'"';
									}
									if($_POST['employee_category']!=''){
										$sql .= ' AND dp_personal_info.employee_category_id="'.$_POST['employee_category'].'"';
									}
									if($_POST['caste']!=''){
										$sql .= ' AND dp_personal_info.caste="'.$_POST['caste'].'"';
									}
									if($_POST['date_type']!=''){
										$sql .= ' AND '.$_POST['date_type'].'>="'.$_POST['date_from'].'" AND '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
									}
								
								$sql .= ' ORDER BY ABS(sort_no)';
								// echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									
									$sql = 'select * from uprnss_division where s_no="'.$row['division_id'].'"';
									$row_division = execute_query($sql);
									if(mysqli_num_rows($row_division)!=0){
										$row_division = mysqli_fetch_assoc($row_division);
									}
									else{
										unset($row_division);
										$row_division['division_name'] = '';
									}
									
									$sql = 'select * from dp_type where sno="'.$row['employee_type_id'].'"';
									$etype = execute_query($sql);
									if(mysqli_num_rows($etype)!=0){
										$etype = mysqli_fetch_assoc($etype);
										}
									else{
										unset($etype);
										$etype['type_name'] = '';
									}
									
									$sql = 'select * from dp_category where sno="'.$row['employee_category_id'].'"';
									$ecat = execute_query($sql);
									if(mysqli_num_rows($ecat)!=0){
										$ecat = mysqli_fetch_assoc($ecat);
										}
									else{
										unset($ecat);
										$ecat['category_name'] = '';
									}
		
									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$row['old_employee_code'].'</td>
									<td>'.$row_division['division_name'].'</td>
									<td>'.$row['date_of_joinning'].'</td>
									<td>'.$row['full_name'].'</td>
									<td>'.$row['date_of_birth'].'</td>
									<td>'.$row['designation'].'</td>
									<td>'.$etype['type_name'].'</td>
									<td>'.$ecat['category_name'].'</td>
									<td>'.$row['caste'].'</td>
									<td>'.$row['father_name'].'</td>
									<td>'.$row['c_number'].'</td>
									<td>'.$row['email'].'</td>
									<td>'.$row['aadhar_number'].'</td>
									
									<td class="no-print"><a href="view_digital_profile.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td class="no-print">
										<a href="master_employees.php?edit='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit " ></span></a>
									</td>
									<td class="no-print">
										<a href="print_emp_details.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a>
									</td>
									<td class="no-print">
										<a href="emp_service_book.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a>
									</td>
								</tr>'; 
								}
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
	
</script>

    
<?php		
page_footer_end();
?>