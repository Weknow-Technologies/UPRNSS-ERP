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
                        <div style="font-size:1.15rem;text-align:center;text-decoration:underline;" class="text-white">वर्तमान मे संचालित समस्त परियोजनाओं का प्रगति विवरण</div>
                    </div>
                    <div class="card-body m-auto">
					<table class="table table-hover table-responsive table-striped " style="width:100%;" id="">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="2">क्रम संख्या </th>
								<th rowspan="2">प्रखण्ड का नाम</th>
								<th rowspan="2">जिला का नाम</th>
                                <th rowspan="2">परियोजनाओ का संख्या</th>
                                <th rowspan="2">कुल स्वीकृत लागत </th>
                                <th rowspan="2">पुनरीक्षित लागत </th>
                                <th colspan="2">कुल अवमुक्त </th>
                                <th colspan="2">कुल व्यय </th>
                                <th rowspan="2">लागत के सापेक्ष अवशेष </th>
                                <th rowspan="2">आवमुक्त के सापेक्ष अवशेष </th>
                                
                             
							</tr>
							<tr>
                                <th>धनराशि </th>
                                <th>प्रतिशत</th>
                                <th>धनराशि </th>
                                <th>प्रतिशत</th>
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
							$sql = 'SELECT uprnss_project_temp.division_id, division_name, district_name_hindi, uprnss_project_temp.district_id, uprnss_project_temp.project_type, count(*) c, 
								sum(invoice_civil.sanction_cost) as sanction_cost ,
								sum(invoice_civil.revised_cost_diff) as revised_cost_diff ,
								sum(invoice_civil.total_physical_progress) as total_physical_progress , sum(invoice_civil.total_received_amount) as total_received_amount , sum(invoice_civil.tot_exp_project) as tot_exp_project , 
								sum(invoice_civil.revised_cost) as revised_cost   
								FROM `uprnss_project_temp` 
                                left join invoice_civil on invoice_civil_sno = invoice_civil.sno
                                left join uprnss_division on uprnss_project_temp.division_id = uprnss_division.s_no
                                left join uprnss_district on uprnss_project_temp.district_id = uprnss_district.sno

							where  uprnss_project_temp.status!="5"  and uprnss_project_temp.reporting_status="0" GROUP by uprnss_project_temp.division_id, uprnss_project_temp.district_id order by division_name ASC';
 
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
							$tot_allotted['revised_cost'] = 0;
							
							$tot_other_project['c']=0;
							$tot_only_complete['c']=0;
							$total_remain_for_haandover=0;
							$tot_allotted_not_update=0;
							$total_sanction_cost=0;
							
							
							while($row_dep = mysqli_fetch_assoc($result_dep)){
							
								if(mysqli_num_rows(execute_query($sql))>0){
									
									$row_dep['c'] = $row_dep['c']==''?0:$row_dep['c'];
									$tot_allotted['c'] += $row_dep['c'];
									
									
									$row_dep['total_received_amount'] = $row_dep['total_received_amount']==''?0:$row_dep['total_received_amount'];
									$tot_allotted['total_received_amount'] += $row_dep['total_received_amount'];
									
									$row_dep['tot_exp_project'] = $row_dep['tot_exp_project']==''?0:$row_dep['tot_exp_project'];
									$tot_allotted['tot_exp_project'] += $row_dep['tot_exp_project'];
									
									$row_dep['revised_cost'] = $row_dep['revised_cost']==''?0:$row_dep['revised_cost'];
									$tot_allotted['revised_cost'] += $row_dep['revised_cost'];
									
								}
								
								// $tot_allotted['c']+=$row_dep['c'];
								// $tot_allotted['c']+=$row_dep['c'];
								// $tot_allotted['c']+=$row_dep['c'];
								
									// $total_remain_for_haandover +=($inventory_sent['c']-$handover['c']);
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_dep['division_name'].'</td>
								<td>'.$row_dep['district_name_hindi'].'</td>
								<td>'.$row_dep['c'].'</td>
									
								<td>';
								if (isset($row_dep['sanction_cost'])) {
									$row_dep['revised_cost_diff'] = ($row_dep['revised_cost_diff']==''?0:$row_dep['revised_cost_diff']);
									$val_amount = round(($row_dep['sanction_cost']+$row_dep['revised_cost_diff']), 2);
									echo $val_amount;
									$total_sanction_cost += $val_amount;
								} else {
									echo "0.00"; 
								}
								echo'</td>
								<td>'.round($row_dep['revised_cost'],2).'</td>
								
								<td>';
								if (isset($row_dep['total_received_amount'])) {
									echo round($row_dep['total_received_amount'], 2);
								} else {
									echo "0.00"; 
								}
								echo'</td>
								<td>';
									if($val_amount==0){
										echo 0;
									}else{
										echo round($row_dep['total_received_amount']/$val_amount*100);
									}
								
								echo '%</td>
								
								<td>';
								if (isset($row_dep['tot_exp_project'])) {
									echo round($row_dep['tot_exp_project'], 2);
								} else {
									echo "0.00"; 
								}
								echo'</td>
								
								<td>';
								if($row_dep['total_received_amount']==0){
									echo 0;
								}else{
									echo round(($row_dep['tot_exp_project']/$row_dep['total_received_amount'])*100);
								}
								echo '%</td>
								
								<td>';
									
										$sanction_cost = isset($row_dep['sanction_cost']) ? round($row_dep['sanction_cost'], 2) : 0.00;
										$total_received_amount = isset($row_dep['total_received_amount']) ? round($row_dep['total_received_amount'], 2) : 0.00;
										echo $remain_on_project= number_format($sanction_cost - $total_received_amount, 2);
										
										// $tot_remain_on_project +=$remain_on_project;
								echo'</td>
								<td>';
									
										$total_received_amount = isset($row_dep['total_received_amount']) ? round($row_dep['total_received_amount'], 2) : 0.00;
									$tot_exp_project = isset($row_dep['tot_exp_project']) ? round($row_dep['tot_exp_project'], 2) : 0.00;
									echo number_format($total_received_amount - $tot_exp_project, 2);
									
								echo'</td>

								
								
								</tr>';
							}
							echo '
							<tr>
								<th>&nbsp;</th>
								<th>&nbsp;</th>
								<th>Total</th>
								<th>'.$tot_allotted['c'].'</th>
								<th>'.$total_sanction_cost.'</th>
								<th>'.round($tot_allotted['revised_cost'],2).'</th>
								<th>'.$tot_allotted['total_received_amount'].'</th>
								<th></th>
								<th>'.$tot_allotted['tot_exp_project'].'</th>
								<th></th>
								<th>'.(round($total_sanction_cost, 2)-round($tot_allotted['total_received_amount'], 2)).'</th>
								<th>'.(round($tot_allotted['total_received_amount'], 2)-round($tot_allotted['tot_exp_project'], 2)).'</th>
								
								
							</tr>
								</tbody>';
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