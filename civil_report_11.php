<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;


	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['department'] = '';
		$_POST['district'] = '';
		$_POST['division_name'] = '';
		$_POST['project_name'] = '';
		$_POST['project_name_hindi'] = '';
		$_POST['work_start_date'] = '';
		$_POST['sanction_cost'] = '';
		$_POST['type'] = '';
		$_POST['type1'] = '';
		$_POST['tot_exp_project'] = '';
		
		$_POST['date_from'] = '';
		$_POST['date_to'] = '';
		$_POST['date_type'] ='';
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
<style>
		.printonly{
			display:none!important;
		}
		
		#overlays{
			z-index:0;
			opacity:0.15;
			position: fixed;
			top: 50%;
			left: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%); 
			/* display:none; */
		}
		
		table, th, td {
			border: 1px solid black;
			border-collapse: collapse;
		}
		th,td{
			padding:0.2rem;
		}
		@media print{
			.printonly{
				display:block!important;
			}
			#overlays{
				/* display:block; */
				opacity:0.2;
				width:35%!important;
				top: 50%!important;
				-ms-transform: translate(-50%, -50%);
				transform: translate(-50%, -50%);}
			}
			
	
	@page {
	size: A4 landscape;
	}
</style>

	
		<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">
			<div class="">
				<img src="images/icon.png"  id="overlays" style=" " alt="overlay image" >
			</div>
			<div class="printonly">
				<div class="card-head">
					<div style="font-size:1.15rem;text-align:center">उOप्र  राज्य निर्माण सहकारी संघ लि - यू पी आर एन यस यस <br></div>
				</div>
			</div>
        	<div class="row d-flex my-auto">    	
					<table width="100%" class="table table-striped table-hover rounded">	
						<tr >
							<th width="16%">Date Type</th>
							<th width="18%"><select name="date_type" id="date_type" class="form-control" >
									<option value="">--- Select ---</option>
									<option value="last_update" <?php echo ($_POST['date_type']=='last_update'?' selected="selected"':''); ?>>Last update </option>
							        
							    </select>
							</th>
							<th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%">To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
						</tr>
						<tr >
							<th>विभाग </th>							
							<th width="18%">
								<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">
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
							</th>
							<th>प्रखण्ड </th>
							<th width="18%"><select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>">
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
								</select></th>						
							<th>आच्छादित जनपद</th>
							<th width="18%">
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
							</th>
						</tr>
					</table>
				
					<div class="col-md-12 text-center mt-3">
					<button type="submit" name="search" class="btn btn-primary">Search</button>
					<input type="hidden" id="id" name="id" value="1">
					<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
					</div>
				
			</div>
		</div>
		</form>
	</div>


		<div class="row">

            <div class="col-md-12">
                <div class="card">
                    
                    <div class="card-body m-auto">
					<table class="table table-hover table-responsive table-striped tablepad " id="">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="2">S.No.</th>
								<th rowspan="2">परियोजना का नाम (हिन्दी )</th>
								<th rowspan="2">विभाग का नाम</th>
								<th rowspan="2">जनपद </th>
								<th rowspan="2">स्वीकृत दिनांक</th>
								<th rowspan="2">परियोजना <br>लागत <br>(रुo लाख मे)</th>
								
								
								<th colspan='7'></th>
							</tr>
							<tr>
								<th>Mpr Date</th>
								<th>कुल प्राप्त धनराशि ( लाख में )</th>
								<th>माह में व्यय</th>
								<th>अब तक का कुल व्यय </th>
								<th>शेष धनराशि अवमुक्त के सापेक्ष</th>
								<th>भौतिक प्रगति समग्र </th>
								<th>अभियुक्ति  </th>
							</tr>
							<tr>
								<?php
								for($i=1;$i<=13 ;$i++){
									echo '<th>'.$i.'</th>';
								}
								?>
							</tr>
						
						</thead>
						 
						<tbody>
						
						<?php
						$i=1;
							if(isset($_POST['search'])){
								
								// $sql='SELECT * FROM  invoice_civil
								// LEFT JOIN uprnss_project_temp  on uprnss_project_temp.invoice_civil_sno=invoice_civil.sno 
								// left join uprnss_department_name on uprnss_department_name.sno = invoice_civil.department_id
								// left join uprnss_district on uprnss_district.sno = invoice_civil.district_id
								// WHERE (uprnss_project_temp.status !="5" and uprnss_project_temp.status!="2") and  uprnss_project_temp.invoice_civil_sno!="0"';
								$sql='select uprnss_project_temp.sno as sno, division_name,sub_department_id, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, financial_go_amount, technical_sanction_date, technical_sanction_no, last_update,status,zone_id, master_freeze from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								where uprnss_project_temp.status !="5" and uprnss_project_temp.status!="2"';	
									
								if(isset($_POST['search'])){
									if($_POST['department']!=''){
										$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
									}
									if($_POST['district']!=''){
										$sql .= ' and uprnss_project_temp.district_id="'.$_POST['district'].'"';
									}
									if($_POST['division_name']!=''){
										$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
									}
								
								}
								// echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									
									$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'" ';
									if($_POST['date_type']!=''){
										$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
									}
									$res = execute_query($sql);
									$rowspan=mysqli_num_rows($res);
									echo '<tr>
									<td rowspan="'.$rowspan.'">'.$i++.'</td>
									<td rowspan="'.$rowspan.'">'.$row['project_name_hindi'].'</td>
									<td rowspan="'.$rowspan.'">'.$row['department_name_hindi'].'</td>
									<td rowspan="'.$rowspan.'">'.$row['district_name_hindi'].'</td>
									
									<td rowspan="'.$rowspan.'">';if ($row['technical_sanction_date'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($row['technical_sanction_date']));};echo'</td>
									<td rowspan="'.$rowspan.'">'.$row['sanction_cost'].'</td>';
									
									// $date_from = date("Y-m-01", strtotime($_POST['date_from']));
									// $date_from = date("Y-m-13", strtotime($_POST['date_from']));
									
									if($rowspan>0){
										
									
										while($mpr_row_invoice = mysqli_fetch_assoc($res)){
										
											echo'
											
											<td>';if ($mpr_row_invoice['last_update'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($mpr_row_invoice['last_update']));};echo'</td>
											<td>'.$mpr_row_invoice['total_received_amount'].'</td>
											<td>'.$mpr_row_invoice['current_month_exp'].'</td>
											<td>'.$mpr_row_invoice['tot_exp_project'].'</td>
											<td>'.$mpr_row_invoice['balance_amt_project'].'</td>
											<td>'.$mpr_row_invoice['total_physical_progress'].'</td>
											<td>'.$mpr_row_invoice['remark'].'</td>
											
											
											</tr>'; 
										}
									}else{
										echo "<td colspan='7' style='text-align:center'>No Entry Found</td>";
									}
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