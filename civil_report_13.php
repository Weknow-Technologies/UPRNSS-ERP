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


if(isset($_GET['id'])){
	$_POST['project_type'] = $_GET['id'];
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
			z-index:-10;
			opacity:0.15;
			position: fixed;
			top: 50%;
			left: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%); 
			/* display:none; */
		}
		.payroll-footer{
			display:flex;
			justify-content:space-between;
			margin:5rem;
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
				display:block;
				opacity:0.2;
				width:35%!important;
				top: 50%!important;
				-ms-transform: translate(-50%, -50%);
				transform: translate(-50%, -50%);}
			}
			.payroll-footer{
				font-size:2rem;
				display:flex;
				justify-content:space-between;
				margin: 10rem!important;
			}
	
	@page {
	size: A4 landscape;
	}
</style>
	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					
					<table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">
						<tr >
							<th width="25%"></th>
							<th width="25%">परियोजना का प्रकार </th>
							<th width="25%">
								<select name="project_type" id="project_type" class="form-control" onchange="onchangetype()" >
										<option value="">कुल परियोजनाये </option>
										<option value="1"<?php echo ($_POST['project_type']==1?'selected':''); ?>>शासन स्तर </option>
										<option value="2"<?php echo ($_POST['project_type']==2?'selected':''); ?>>जिला स्तर </option>
									</select>
							</th>
							<th width="25%"></th>
						</tr>
					</table>
					
				
			</div>
					<div class="row">
						<div class="col-md-12 text-center" style="position:relative;">
					
							<button type="submit" name="search" class="btn btn-primary">Search</button>
							<input type="hidden" id="id" name="id" value="1">
							<div class="" style="position:absolute;right:2rem;top:0;">
							<button  id="civil_9"class="btn btn-info">download in Excel</button>
						</div>
						</div>
						
					</div>
		</div>
		</form>
	</div>
		
	<div class="row">
			<div class="">
				<img src="images/icon.png"  id="overlays" style=" " alt="overlay image" >
			</div>
			<div class="">
				<div class="card-head">
					
				</div>
			</div>
	
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-danger mt-4">
                        <div style="font-size:1.15rem;text-align:center;text-decoration:underline;" class="text-white">वर्तमान मे संचालित <?php if($_POST['project_type'] == 1){	echo 'शासन स्तर के';	} elseif($_POST['project_type'] == 2) {	echo 'जिला स्तर के';} else {	echo '';}?> समस्त परियोजनाओं का प्रगति विवरण</div>
                    </div>
                    <div class="card-body m-auto">
					<table class="table table-hover table-responsive table-striped " style="width:100%;" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="2">क्रम संख्या </th>
								<th rowspan="2">विभाग का नाम</th>
                                <th rowspan="2"> परियोजनाओ का संख्या</th>
                                <th rowspan="2">Not Updated</th>
                                <th rowspan="2">अनरम्भा कार्य</th>
                                <th rowspan="2"> धनभाव के कारण बाधित </th>
                                <th rowspan="2">विवादित </th>
                                <th rowspan="2">कार्य प्रगति  </th>
                                <th rowspan="2">कुल पूर्ण कार्य </th>
                                <th rowspan="2"> कुल इनवेंट्रि प्रेषित कार्य</th>
                                <th rowspan="2"> कुल हस्तगत कार्य </th>
                                <th rowspan="2"> हस्तगत हेतु अवशेष कार्य  (10-11)</th>
                                <th rowspan="2">कुल स्वीकृत लागत </th>
                                <th colspan="2">कुल अवमुक्त </th>
                                <th colspan="2">कुल व्यय </th>
                                <th rowspan="2">भौतिक प्रगति का प्रतिशत  </th>
                             
							</tr>
							<tr>
                                <th>धनराशि </th>
                                <th>प्रतिशत</th>
                                <th>धनराशि </th>
                                <th>प्रतिशत</th>
							</tr>
							<tr>
								<?php
								for($i=1;$i<=18 ;$i++){
									echo '<th>'.$i.'</th>';
								}
								?>
							</tr>
						
						</thead>
						 
						<tbody>
							<?php
							$sql = 'select * from uprnss_department_name';
							$result_dep = execute_query($sql);
							$i=1;
							$tot_allotted['c'] = 0;
							$tot_allotted['sanction_cost'] = 0;
							$tot_allotted['total_received_amount'] = 0;
							$tot_allotted['tot_exp_project'] = 0;
							$tot_allotted['total_physical_progress'] = 0;
							
							$tot_unstarted['c'] = 0;
							// $tot_unstarted['sanction_cost'] = 0;
							
							$tot_lack_of_money['c'] = 0;
							$tot_lack_of_money['sanction_cost'] = 0;
							
							$tot_controversial['c'] = 0;
							$tot_controversial['sanction_cost'] = 0;
							$tot_progress['c'] = 0;
							$tot_progress['sanction_cost'] = 0;
							
							
							$tot_complete['c'] = 0;
							$tot_complete['sanction_cost'] = 0;
							
							$tot_inventory_sent['c'] = 0;
							$tot_inventory_sent['sanction_cost'] = 0;
							$tot_handover['c'] = 0;
							$tot_handover['sanction_cost'] = 0;
							
							$tot_remain_project['c'] = 0;
							$tot_remain_project['sanction_cost'] = 0;
							$tot_technical_san_complete['c'] = 0;
							$tot_technical_san_complete['sanction_cost'] = 0;
							
							$tot_other_project['c']=0;
							$tot_only_complete['c']=0;
							$total_remain_for_haandover=0;
							$tot_allotted_not_update=0;
							
							
							while($row_dep = mysqli_fetch_assoc($result_dep)){
				/////// Total Project /////////				
								$sql ='SELECT uprnss_project_temp.department_id,uprnss_project_temp.project_type, count(*) c, 
								sum(invoice_civil.sanction_cost) as sanction_cost ,
								sum(invoice_civil.total_physical_progress) as total_physical_progress , sum(invoice_civil.total_received_amount) as total_received_amount , sum(invoice_civil.tot_exp_project) as tot_exp_project , 
								sum(invoice_civil.revised_cost) as revised_cost   
								FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5"  and uprnss_project_temp.reporting_status="0"';
								
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								if(mysqli_num_rows(execute_query($sql))>0){
									$allotted = mysqli_fetch_assoc(execute_query($sql));
									
									$allotted['c'] = $allotted['c']==''?0:$allotted['c'];
									$tot_allotted['c'] += $allotted['c'];
									
									$allotted['sanction_cost'] = $allotted['sanction_cost']==''?0:$allotted['sanction_cost'];
									$tot_allotted['sanction_cost'] += $allotted['sanction_cost'];
									
									$allotted['total_received_amount'] = $allotted['total_received_amount']==''?0:$allotted['total_received_amount'];
									$tot_allotted['total_received_amount'] += $allotted['total_received_amount'];
									
									$allotted['tot_exp_project'] = $allotted['tot_exp_project']==''?0:$allotted['tot_exp_project'];
									$tot_allotted['tot_exp_project'] += $allotted['tot_exp_project'];
									
									$tot_allotted['total_physical_progress'] += $allotted['total_physical_progress'];
									$allotted['total_physical_progress'] = $allotted['total_physical_progress']==''?0:$allotted['total_physical_progress'];
								}else{
									$allotted['c']=0;
									$allotted['sanction_cost']=0;
									$allotted['total_received_amount']=0;
									$allotted['total_physical_progress']=0;								
									$allotted['tot_exp_project']=0;

								}
								
								$sql ='SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="' . $row_dep['sno'] . '" and  uprnss_project_temp.status!="5" and( invoice_civil.project_status_1="" or invoice_civil.project_status_1 IS NUll ) and uprnss_project_temp.reporting_status=0 ';
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								$allotted_not_update = mysqli_num_rows(execute_query($sql));
								$tot_allotted_not_update += $allotted_not_update;
									
									
					//////total project brekup////////

					
								$sql ='SELECT uprnss_project_temp.department_id,uprnss_project_temp.project_type,uprnss_project_temp.reporting_status, invoice_civil.project_status_1, count(*) c, sum(invoice_civil.sanction_cost) as sanction_cost , sum(invoice_civil.revised_cost) as revised_cost   FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5"  and invoice_civil.project_status_1="5"  and uprnss_project_temp.reporting_status=0';
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								if(mysqli_num_rows(execute_query($sql))>0){
									$unstarted = mysqli_fetch_assoc(execute_query($sql));
									
									$unstarted['c'] = $unstarted['c']==''?0:$unstarted['c'];
									$tot_unstarted['c'] += $unstarted['c'];
								}else{
									$unstarted['c']=0;
								}
								
								$sql ='SELECT uprnss_project_temp.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1, count(*) c, sum(invoice_civil.sanction_cost) as sanction_cost , sum(invoice_civil.revised_cost) as revised_cost   FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5"  and invoice_civil.project_status_1="6"  and uprnss_project_temp.reporting_status="0" ';
								
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								 
								if(mysqli_num_rows(execute_query($sql))>0){
									$lack_of_money = mysqli_fetch_assoc(execute_query($sql));
									
									$lack_of_money['c'] = $lack_of_money['c']==''?0:$lack_of_money['c'];
									$tot_lack_of_money['c'] += $lack_of_money['c'];
								}else{
									$lack_of_money['c']=0;
								}
								
								
								$sql ='SELECT uprnss_project_temp.department_id, uprnss_project_temp.project_type, invoice_civil.project_status_1, count(*) c, sum(invoice_civil.sanction_cost) as sanction_cost , sum(invoice_civil.revised_cost) as revised_cost   FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and  uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="4" or invoice_civil.project_status_1="13")  and uprnss_project_temp.reporting_status!="1" ';
								
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								 
								if(mysqli_num_rows(execute_query($sql))>0){
									$controversial = mysqli_fetch_assoc(execute_query($sql));
									
									$controversial['c'] = $controversial['c']==''?0:$controversial['c'];
									$tot_controversial['c'] += $controversial['c'];
								}else{
									$controversial['c']=0;
								}
							
								$sql ='SELECT uprnss_project_temp.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1, count(*) c, sum(invoice_civil.sanction_cost) as sanction_cost , sum(invoice_civil.revised_cost) as revised_cost   FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="10" or invoice_civil.project_status_1="12" ) and uprnss_project_temp.reporting_status!="1" ';
								
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								 
								if(mysqli_num_rows(execute_query($sql))>0){
									$progress = mysqli_fetch_assoc(execute_query($sql));
									
									$progress['c'] = $progress['c']==''?0:$progress['c'];
									$tot_progress['c'] += $progress['c'];
								}else{
									$progress['c']=0;
								}
								
								
								$sql ='SELECT uprnss_project_temp.department_id, uprnss_project_temp.project_type, invoice_civil.project_status_1, count(*) c, sum(invoice_civil.sanction_cost) as sanction_cost , sum(invoice_civil.revised_cost) as revised_cost   FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="7" or invoice_civil.project_status_1="8" or invoice_civil.project_status_1="11") and uprnss_project_temp.reporting_status!="1"  ';
								
								
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								// echo $sql;
								if(mysqli_num_rows(execute_query($sql))>0){
									$complete = mysqli_fetch_assoc(execute_query($sql));
									
									$complete['c'] = $complete['c']==''?0:$complete['c'];
									$tot_complete['c'] += $complete['c'];
								}else{
									$complete['c']=0;
								}
								
								
								$sql ='SELECT uprnss_project_temp.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1, count(*) c, sum(invoice_civil.sanction_cost) as sanction_cost , sum(invoice_civil.revised_cost) as revised_cost   FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="7" or invoice_civil.project_status_1="8" ) and uprnss_project_temp.reporting_status!="1"';
								
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								 
								if(mysqli_num_rows(execute_query($sql))>0){
									$inventory_sent = mysqli_fetch_assoc(execute_query($sql));
									
									$inventory_sent['c'] = $inventory_sent['c']==''?0:$inventory_sent['c'];
									$tot_inventory_sent['c'] += $inventory_sent['c'];
								}else{
									$inventory_sent['c']=0;
								}
								
								$sql ='SELECT uprnss_project_temp.department_id, uprnss_project_temp.project_type, invoice_civil.project_status_1, count(*) c, sum(invoice_civil.sanction_cost) as sanction_cost , sum(invoice_civil.revised_cost) as revised_cost   FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and invoice_civil.project_status_1="8"    and uprnss_project_temp.reporting_status!="1"';
								if($_POST['project_type']!=''){
									$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
								}
								 
								if(mysqli_num_rows(execute_query($sql))>0){
									$handover = mysqli_fetch_assoc(execute_query($sql));
									
									$handover['c'] = $handover['c']==''?0:$handover['c'];
									$tot_handover['c'] += $handover['c'];
								}else{
									$handover['c']=0;
								}
								
								
									$total_remain_for_haandover +=($inventory_sent['c']-$handover['c']);
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_dep['department_name_hindi'].'</td>
								<td>'.$allotted['c'].'</td>
								<td>'.$allotted_not_update.'</td>
								<td>'.$unstarted['c'].'</td>
								<td>'.$lack_of_money['c'].'</td>
								<td>'.$controversial['c'].'</td>
								<td>'.$progress['c'].'</td>
								<td>'.$complete['c'].'</td>
								
								<td>'.$inventory_sent['c'].'</td>
								
								<td>'.$handover['c'].'</td>
								
								<td>'.($inventory_sent['c']-$handover['c']).'</td>
								
								<td>'.number_format($allotted['sanction_cost'], 2).'</td>
								
								
								<td>'.round($allotted['total_received_amount'], 2).'</td>
								<td>';
									if($allotted['sanction_cost']==0){
										echo 0;
									}else{
										echo round($allotted['total_received_amount']/$allotted['sanction_cost']*100,2);
									}
								
								echo '%</td>
								
								<td>'.round($allotted['tot_exp_project'], 2).'</td>
								<td>';
								if($allotted['total_received_amount']==0){
									echo 0;
								}else{
									echo round(($allotted['tot_exp_project']/$allotted['total_received_amount'])*100,2);
								}
								echo '%</td>
								
								<td>';
								if($allotted['c']==0){
									echo 0;
								}else{
									echo round($allotted['total_physical_progress']/$allotted['c']);
								}
								echo '%</td>
								
								</tr>';
								}
								
							echo '
						</tbody>
					
							<tr>
								<th>&nbsp;</th>
								<th>Total</th>
								<th>'.$tot_allotted['c'].'</th>
								<th>'.$tot_allotted_not_update.'</th>
								<th>'.$tot_unstarted['c'].'</th>
								<th>'.$tot_lack_of_money['c'].'</th>
								<th>'.$tot_controversial['c'].'</th>
								<th>'.$tot_progress['c'].'</th>
								<th>'.$tot_complete['c'].'</th>
								
								<th>'.$tot_inventory_sent['c'].'</th>
								
								<th>'.$tot_handover['c'].'</th>
								<th>'.$total_remain_for_haandover.'</th>
								
								<th>'.round($tot_allotted['sanction_cost'], 2).'</th>
								<th>'.round($tot_allotted['total_received_amount'], 2).'</th>
								<th></th>
								<th>'.round($tot_allotted['tot_exp_project'], 2).'</th>
								<th></th>
								<th></th>
								
								
							</tr>';
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

$('select[multiple]').multiselect({
	search: true
});
	
$(document).ready( function () {
    $('#general_stat_table').DataTable({
			paging: false,
			fixedHeader: true,
			// colReorder: true,
			// scrollX: true, // Enable horizontal scrolling if needed
	});
	
    
});
	
</script>

    
<?php		
page_footer_end();
?>