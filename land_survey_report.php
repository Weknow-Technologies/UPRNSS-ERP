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
        	<div class="row d-flex my-auto">    	
					<table width="100%" class="table table-striped table-hover rounded">	
						
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
                        <tr >
							<th>Architect </th>							
							<th width="18%">
                                <select class="form-control" name="architect" id="architect" tabindex="<?php echo $tab++; ?>">
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $query = "select sno,full_name_english from uprnss_architect WHERE `uprnss_architect`.`type` = 1" ;
                                    $run = mysqli_query($db,$query);
                                    while($data = mysqli_fetch_array($run)){
                                        echo '<option value="'.$data['sno'].'" ';
                                        if(isset($_POST['architect'])){
                                            if($_POST['architect']==$data['sno']){
                                                echo ' selected="Selected"';
                                            }
                                        }
                                        echo '>'.trim($data['full_name_english']).'</option>';
                                    }
                                    ?>
                                </select>
							</th>
							<th>Structural Architect</th>
							<th width="18%">
                                <select class="form-control" name="structural_architect" id="structural_architect" tabindex="<?php echo $tab++; ?>">
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $query = "select sno,full_name_english from uprnss_architect WHERE `uprnss_architect`.`type` = 2" ;
                                    $run = mysqli_query($db,$query);
                                    while($data = mysqli_fetch_array($run)){
                                        echo '<option value="'.$data['sno'].'" ';
                                        if(isset($_POST['structural_architect'])){
                                            if($_POST['structural_architect']==$data['sno']){
                                                echo ' selected="Selected"';
                                            }
                                        }
                                        echo '>'.trim($data['full_name_english']).'</option>';
                                    }
                                    ?>
                                </select>
                            </th>						
							<th>DATE OF ALLOTMENT</th>
							<th width="18%">
								<input type="date" name="allotment_date" id="allotment_date">
							</th>
						</tr>
					</table>
					<div style="text-align: right; width: 100%;">
						<a onclick="printPage()" class="no-print btn btn-success" style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print this page</a>
						<a onclick="printPage()" class="no-print btn btn-default" style="color: black; background-color: #ffffff; border-color: #cccccc; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Download to PDF</a>
						<a onclick="printPage()" class="no-print btn btn-info" style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Export to Excel</a>
					</div>
					<div class="col-md-12 text-center mt-3">
					<button type="submit" name="search" class="btn btn-primary">Search</button>
					
					<!-- <input type="hidden" id="id" name="id" value="1">
					<input type="hidden" id="edit_sno" name="edit_sno" value="<?php //echo $_POST['edit_sno']; ?>"> -->
					</div>
				
			</div>
		</div>
		</form>
	</div>


		<div class="row">

            <div class="col-md-12">
                <div class="card">
                    
                    <div class="card-body m-auto">
					<table class="table table-hover table-responsive table-striped tablepad " id="general_stat_table">
						<thead style="">
							<tr>
								<th>S.No.</th>
								<th>परियोजना का नाम (हिन्दी )</th>
								<th>विभाग का नाम</th>
								<th>जनपद </th>
                                <th>Architect</th>
                                <th>Structural Architect</th>
                                <th>Type of Land</th>
                                <!-- <th>Date of Visit</th>
                                <th>Date of Land Avaible</th>
                                <th>Date of Land Work Start</th>
                                <th>Target Date of Completion</th>
                                <th>Date of Order Handover</th>
                                <th>Date of Site Visit</th> -->
                                <th>Soil Testing</th>
                                <th>Map</th>
                                <th>Techinical Sanction</th>
                                <th>Estimate </th>
                                <th>Invoice From Architect</th>

								
							</tr>
							
							<tr>
								<?php
								for($i=1;$i<=12 ;$i++){
									echo '<th>'.$i.'</th>';
								}
								?>
							</tr>
						
						</thead>
						 
						<tbody>
						
						<?php
						$i=1;
							if(isset($_POST['search'])){
								
								$sql='SELECT * FROM  invoice_survey WHERE 1=1 ';

								if(isset($_POST['search'])){

									if($_POST['architect']!=""){
										$sql.=" and architect='".$_POST['architect']."'";
									}
									if($_POST['structural_architect']!=""){
										$sql.=" and structural_architect='".$_POST['structural_architect']."'";
									}


								}

								// echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
                                 	$sqls = 'SELECT * FROM uprnss_project_temp  
											LEFT JOIN uprnss_department_name ON uprnss_department_name.sno = uprnss_project_temp.department_id
											LEFT JOIN uprnss_district ON uprnss_district.sno = uprnss_project_temp.district_id
											WHERE uprnss_project_temp.sno = "' . $row['project_id'] . '" 
											AND uprnss_project_temp.division_id IN (' . implode(",", $_SESSION['divisions']) . ') 
											AND (uprnss_project_temp.status != "5" or uprnss_project_temp.status != "2")';

                                    //architect and structual architech detials

									if(isset($_POST['search'])){
										// if($_POST['architect']!=""){
										// 	$architech.=" and sno='".$_POST['architect']."'";
										// }
										

										if($_POST['department']!=''){
											$sqls .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
										}
										if($_POST['district']!=''){
											$sqls .= ' and uprnss_project_temp.district_id="'.$_POST['district'].'"';
										}
										if($_POST['division_name']!=''){
											$sqls .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
										}
									
									}
								
									
									$resuprnssprojecttemp=mysqli_query($db,$sqls);
									// echo $sqls;
									if(mysqli_num_rows($resuprnssprojecttemp)>0){
										$architech="SELECT * FROM uprnss_architect WHERE sno='{$row['architect']}'";
                                    

										$structual="SELECT * FROM uprnss_architect WHERE sno='{$row['structural_architect']}'";
										

										$rowproject=mysqli_fetch_assoc($resuprnssprojecttemp);
										$architechres=mysqli_fetch_assoc(execute_query($architech));
										$structualarchires=mysqli_fetch_assoc(execute_query($structual));
										echo '<td>'.$i++.'</td>
										<td>'.$rowproject['project_name_hindi'].'</td>
										<td>'.$rowproject['department_name_hindi'].'</td>
										<td>'.$rowproject['district_name_hindi'].'</td>
										<td>'.$architechres['full_name_english'].'</td>
										<td>'.$structualarchires['full_name_english'].'</td>
										<td>'.$row['land_type'].'</td>
										<td><a href="'.$row['soil_testiong_report_file'].'" target="_blank">Soil Testing</a><br>'.date("d-m-Y",strtotime($row['soil_testing_date'])).'</td>
										<td><a href="'.$row['map_file'].'" target="_blank">Map</a><br>'.date("d-m-Y",strtotime($row['date_of_map_sub'])).'</td>
										<td><a href="'.$row['ts_file'].'" target="_blank">Technical Sanction</a><br>'.date("d-m-Y",strtotime($row['date_of_ts_sub'])).'</td>
										<td><a href="'.$row['estiamate_file'].'" target="_blank">Estimate Report</a><br>'.date("d-m-Y",strtotime($row['date_of_estiamat_sub'])).'</td>
										<td><a href="'.$row['invoice_from_architect_file'].'" target="_blank">Invoice Report</a><br>'.date("d-m-Y",strtotime($row['date_of_invoice_architect_sub'])).'</td>

										
										</tr>'; 
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
function printPage() {
    window.print();
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
		// paging: false
    });
 
    
});
	
</script>

    
<?php		
page_footer_end();
?>