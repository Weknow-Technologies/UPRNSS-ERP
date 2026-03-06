<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

if(isset($_POST['search'])){
	$_SESSION['pragti_report_print'] = $_POST;
}
// print_r($_POST);
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
		font-size:20px;
	}
	textarea{
		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
	}

table, th, td {
  border: 0.1px solid black!important;
  border-collapse: collapse!important;
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
					<table width="100%" class="table table-striped table-hover rounded">	
						<tr >
							<th width="16%">Date Type</th>
							<th width="18%"><select name="date_type" id="date_type" class="form-control" >
                                    <option value="admin_go_date" <?php echo ($_POST['date_type']=='admin_go_date'?' selected="selected"':''); ?>>Administrative Date</option>
							        
							    </select>
							</th>

							<th>Project Status</th>
							<th>
								<select class="form-control" name="project_status_1" id="project_status_1" tabindex="<?php echo $tab++; ?>">										
										<?php
										$query = "SELECT * FROM master_projoect_status_1 WHERE sno = 8";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_status_1'])){
												if($_POST['project_status_1']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['status_1']).'</option>';
										}
										?>
								</select>
							</th>								
						</tr>
						<th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%";>To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
						<tr>							
												
							
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
	<form name="test" action="pragti_report_print.php" method="POST" enctype="multipart/form-data">
		<div class="row">
            <div class="col-md-12">
                <div class="card">
					<div class="col-md-12 text-center">
						<button  formtarget="_blank" type="submit" name="student_ledger" class="btn btn-primary"  >Print</button>
					</div>
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table" border="1">
						<thead style="position:sticky;top:0; z-index:2;">
						<tr>
						
							<th rowspan="2"> क्र० सं० </th>
							<th rowspan="2"> विभागों की संख्या </th>
							<th rowspan="2"> <?php echo $_POST['date_to']; ?> को शेष परियोजनाओ की संख्या </th>
							<th rowspan="2"> <?php echo $_POST['date_to']; ?> को शेष परियोजनाओ की लागत </th>
							<th rowspan="2"> <?php echo $_POST['date_to']; ?> को शेष परियोजनाओ की धनराशि </th>
							<th rowspan="2"> <?php echo $_POST['date_from']; ?> से <?php echo $_POST['date_to']; ?> तक प्राप्त परियोजनाओ की संख्या </th>
							<th rowspan="2"> <?php echo $_POST['date_from']; ?> से <?php echo $_POST['date_to']; ?> तक प्राप्त परियोजनाओ की लागत </th>
							<th rowspan="2"> माह फरवरी, <?php echo date("Y"); ?> तक परियोजनाओ की कुल संख्या </th>
							<th rowspan="2"> माह फरवरी, <?php echo date("Y"); ?> तक परियोजनाओ की कुल लागत </th>
							<th rowspan="2"> <?php echo $_POST['date_from']; ?> से <?php echo $_POST['date_to']; ?> तक प्राप्त धनराशि </th>
							<th rowspan="2"> <?php echo $_POST['date_from']; ?> से <?php echo $_POST['date_to']; ?> तक कुल उपलब्ध धनराशि </th>
							<th rowspan="2"> <?php echo $_POST['date_from']; ?> से <?php echo $_POST['date_to']; ?> तक कुल व्यय धनराशि </th>
							<th rowspan="2"> माह फरवरी, <?php echo date("Y"); ?> तक कुल व्यय धनराशि </th>
						
						</tr>
						
						
						
						</thead>
						 
						<tbody>
						
						<?php
						$i=1;
							if(isset($_POST['search'])){
								
								$sql = 'SELECT COUNT(*) AS c, department_id FROM uprnss_project_temp where status!="5" GROUP BY department_id';

								//echo $sql.'<br>';
								
								$run = mysqli_query($db, $sql);
								$project_count = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT COUNT(*) AS c, project_name FROM invoice_civil WHERE STATUS != "5" GROUP BY project_name';

								$run = mysqli_query($db, $sql);
								$departmemt_count = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT  sum(sanction_cost) as sanction_cast FROM uprnss_project_temp where status!="5"';
								
								$run = mysqli_query($db, $sql);
								$result = mysqli_fetch_assoc($run);
    							$sanction_cast = $result['sanction_cast'] !== null ? $result['sanction_cast'] : 0;


								$sql = 'SELECT  sum(total_received_amount) as total_received_amount FROM  invoice_civil where status!="5"';
								
								$run = mysqli_query($db, $sql);
								$result = mysqli_fetch_assoc($run);
    							$total_received_amount = $result['total_received_amount'] !== null ? $result['total_received_amount'] : 0;

								$sql = "SELECT COUNT(*) AS c, department_id 
								FROM uprnss_project_temp 
								WHERE status != '5' 
								and admin_go_date>='".$_POST['date_from']."' and admin_go_date<='".$_POST['date_to']."'
								GROUP BY department_id";

								$run = mysqli_query($db, $sql);
								$year_wise_project_count = mysqli_num_rows(execute_query($sql));


								
								$sql = "SELECT sum(sanction_cost) as sanction_cast
								FROM uprnss_project_temp 
								WHERE status != '5' 
								and admin_go_date>='".$_POST['date_from']."' and admin_go_date<='".$_POST['date_to']."' ";

								$run = mysqli_query($db, $sql);
								$year_wise_sanction_cast = mysqli_num_rows(execute_query($sql));


								$sql = "SELECT SUM(total_received_amount) as total_received_amount
								FROM invoice_civil 
								WHERE status != '5' 
								and admin_go_date>='".$_POST['date_from']."' and admin_go_date<='".$_POST['date_to']."' ";

								$run = mysqli_query($db, $sql);
								$year_wise_total_received_amount = mysqli_fetch_assoc(execute_query($sql));


								

								$sql = "SELECT SUM(tot_exp_project) AS tot_exp_project
								FROM uprnss_project_temp
								WHERE status != '5'
								AND admin_go_date>='".$_POST['date_from']."' and admin_go_date<='".$_POST['date_to']."' ";

								$run = mysqli_query($db, $sql);
								$tot_exp_project = mysqli_fetch_assoc(execute_query($sql));
								

								$sql = "SELECT SUM(sanction_cost) AS sanction_cast
								FROM uprnss_project_temp
								WHERE status != '5'
								AND admin_go_date>='".$_POST['date_from']."' and admin_go_date<='".$_POST['date_to']."' ";

								$run = mysqli_query($db, $sql);
								$cast_upto_feb = mysqli_fetch_assoc(execute_query($sql));



								$tot_project = $departmemt_count + $year_wise_project_count;
								$tot_amount = $sanction_cast + $year_wise_sanction_cast;
								// $amount= $total_received_amount + $year_wise_total_received_amount;


									echo '<tr>
									
									<td>'.$i++.'</td>
									<td>'.$project_count.'</td>
									<td>'.$departmemt_count.'</td>
									<td>'. round($sanction_cast).'</td>
									<td>'. round($total_received_amount).'</td>
									<td>'.$year_wise_project_count.'</td>
									<td>'. round($year_wise_sanction_cast).'</td>
									<td>'. round($tot_project).'</td>
									<td>'. round($tot_amount).'</td>
									<td>'. round($year_wise_total_received_amount['total_received_amount']).'</td>
									<td>'. round($year_wise_total_received_amount['total_received_amount']).'</td>
									
									<td>'. round($tot_exp_project['tot_exp_project']).'</td>
									
									<td>'. round($cast_upto_feb['sanction_cast']).'</td>
									
									
									</tr>'; 
								
							}
						
						
						?>
						</tbody>
					</table>
					
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
	
</script>

    
<?php		
page_footer_end();
?>