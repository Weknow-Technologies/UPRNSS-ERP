<?php
include("scripts/settings.php");
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

//print_r($_POST);


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

if(!isset($_POST['date_type'])){
	$_POST['date_type'] = 'admin_go_date';
	$_POST['date_from'] = date("Y-m-01");
	$_POST['date_to'] = date("Y-m-d");
	$_POST['department'] = '';
}
?>

	<script>
		function onchangetype() {
			var type = document.getElementById("go_type");

			if (type.value == "1") {
				document.getElementById("admin").style.display = "flex";
				document.getElementById("financial").style.display = "none";
			} else if (type.value == '2' || type.value == '3') {
				document.getElementById("admin").style.display = "flex";
				document.getElementById("financial").style.display = "flex";
			} else {
				document.getElementById("admin").style.display = "none";
				document.getElementById("financial").style.display = "none";
			}
		}

	  window.onload = function() {
		onchangetype(); 
		document.getElementById("go_type").onchange = onchangetype;
	  };
	</script>



	
	<div >	
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
							<div class="col-md-2">
								<div class="form-group">
									<label >Department</label>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_sub_department(this.value)">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_department_name";
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
							<div class="col-md-2">
                                <div class="form-group">
                                    <label>Date Type</label>
                                    <select name="date_type" id="date_type" class="form-control" >
									
                                    <option value="sanction_date" <?php echo ($_POST['date_type']=='sanction_date'?' selected="selected"':''); ?>>Sanction Date</option>
                                    <option value="land_receive_date" <?php echo ($_POST['date_type']=='land_receive_date'?' selected="selected"':''); ?>>Land Receive Date</option>
                                    <option value="work_start_date" <?php echo ($_POST['date_type']=='work_start_date'?' selected="selected"':''); ?>>Work Start Date</option>
                                    <option value="work_completion_date" <?php echo ($_POST['date_type']=='work_completion_date'?' selected="selected"':''); ?>>Work Completion Date</option>
                                    <option value="technical_sanction_date" <?php echo ($_POST['date_type']=='technical_sanction_date'?' selected="selected"':''); ?>>Technical Sanction Date</option>
                                    <option value="admin_go_date" <?php echo ($_POST['date_type']=='admin_go_date'?' selected="selected"':''); ?>>Administrative GO Date</option>
                                    <option value="financial_go_date" <?php echo ($_POST['date_type']=='financial_go_date'?' selected="selected"':''); ?>>Financial GO Date</option>
							        
							    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <button type="submit" name="search" id="search" class="btn btn-primary" >Search</button>
                                </div>
                            </div>
                            
						</div>   	
					</div>
				</div>
			</div>
		</div>
			
		</form>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="card strpied-tabled-with-hover">
				<div class="card-header ">
				</div>
				<div class="card-body table-full-width table-responsive">
					
					<!--/*Sample Report 2nd type starts*/-->
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Department</th>
								<th>G.O.Details</th>
								<th>G.O.Number</th>
								<th>G.O.Date</th>
								<th>Project Name</th>
								<th>Scheme</th>
								<th>Sub scheme</th>
								<th colspan="3">Details</th>
								<th>View</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>	
						</thead>
						<tbody>
							<?php
							$sql = 'SELECT * FROM `invoice_new_project` where status!=5 and go_date>="'.$_POST['date_from'].'" and go_date<="'.$_POST['date_to'].'"';
							if($_POST['department']!=''){
								$sql .= ' and department="'.$_POST['department'].'"';
							}
							// echo $sql;
							$result = execute_query($sql);
							$i=1;
							$grand_tot_count=0;
							while($row = mysqli_fetch_assoc($result)){	
								$sql = 'select * from transaction_new_project where invoice_id="'.$row['sno'].'"';
								$row_transaction = mysqli_fetch_assoc(execute_query($sql));
								
								$sql = 'select * from uprnss_department_name where sno="'.$row['department'].'"';
								
								$department = mysqli_fetch_assoc(execute_query($sql));
								
								$sql = 'select * from uprnss_project_scheme where sno="'.$row['scheme'].'"';
								// echo $sql;
								$scheme = mysqli_fetch_assoc(execute_query($sql));
								$scheme = execute_query($sql);
								if(mysqli_num_rows($scheme)!=0){
									$scheme = mysqli_fetch_assoc($scheme);
								}
								else{
									unset($scheme);
									$scheme['scheme_name_english'] = '';
								}
									
								$sql= 'select count(*) c, division_id, district_id from transaction_new_project where invoice_id="'.$row['sno'].'" and Status!="5" group by division_id, district_id';
								$result_act = execute_query($sql);
								$sub_tot_count=0;
								$act_txt = '<td colspan="2"><table class="table table-hover table-striped table-bordered">
								<tr><th>Division</th><th>District</th><th>Project Quantity</th></tr>';
								if(mysqli_num_rows($result_act)!=0){
									$count_act = mysqli_num_rows($result_act);
									while($row_act = mysqli_fetch_assoc($result_act)){
										$sub_tot_count+=$row_act['c'];
										$sql = 'select * from uprnss_division where s_no="'.$row_act['division_id'].'"';
										$division = mysqli_fetch_assoc(execute_query($sql));
							
										$sql = 'select * from uprnss_district where sno="'.$row_act['district_id'].'"';
										$district = mysqli_fetch_assoc(execute_query($sql));
												
										$act_txt .= '<tr><td>'.$division['division_name'].'</td><td>'.$district['district_name_english'].'</td><td>'.$row_act['c'].'</td></tr>';
									}
								}
								else{
									$count_act = 0;
								}
								$act_txt .= '<tr><th></th><th>Total:</th><th>'.$sub_tot_count.'</th></table></td>';
								$grand_tot_count += $sub_tot_count;
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$department['department_name_hindi'].'</td>
								
								<td>';
									if($row['go_type']!=''){
										if($row['go_type']=='1'){
											echo '<span class="">Administrative</span>';
										}
										elseif($row['go_type']=='2'){
											echo '<span class="">Financial</span>';
										}
										elseif($row['go_type']=='3'){
											echo '<span class="">Administrative + Financial</span>';
										}
									}
									echo '
								</td>
								<td>'.$row['go_number'].'</td>
								<td>'.$row['go_date'].'</td>
								<td>'.$row['project_name_hindi'].'</td>
								<td>'.$scheme['scheme_name_english'].'</td>
								<td>'.$row['sub_scheme'].'</td>
								<td>';echo $act_txt; echo'</td>
								<td><a href="preview_new_project.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
								<td class="no-print text-center">
									<a href="new_project.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure ?\');" target="_blank"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Data"></span></a>
								</td>
								<td class="no-print text-center">
									<a href="new_project.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
								</td>
								</tr>';
							
							}
							echo '<tr>
							<th colspan="8"></th>
							<th>Total:</th>
							<th>'.$grand_tot_count.'</th>
							<th colspan="3"></th></tr>';
							
							?>
						</tbody>
					</table>
					<!--/*Sample Report 2nd type ends*/-->
				</div>
			</div>
		</div>
	</div>
	
<?php
page_footer_start();
?>	
		<script>
	
		
			function add_rows(){
				var id = parseFloat($("#add_rows_id").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();
				
				var txt = id+'<div id="add_rows_length" class="row"><div class="col-md-4 "><div class="form-group"><label>Unit Name</label><select  class="form-control" name="unit_name_'+id+'" id="unit_name_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = "select * from uprnss_division order by division_name ASC";
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['s_no'].'\'>'.$data['division_name'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-4 "><div class="form-group"><label>District Name</label><select  class="form-control" name="district_name_'+id+'" id="district_name_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = "select * from uprnss_district";
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['district_name_english'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-3 "><div class="form-group"><label>project Quantity</label><input type="text" name="project_qun_'+id+'" id="project_qun_'+id+'" class="form-control" placeholder=" " value=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
				$("#test").append(txt);
				$("#add_rows_id").val(id);
			}

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
						
		</script>	
	
				

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>

