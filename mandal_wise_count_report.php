<?php

include("scripts/settings.php");

$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

// print_r($_POST);
if(!isset($_POST['generate_report'])){
		$_POST['department'] = '';
		$_POST['division'] = '';
		$_POST['date_type'] ='';
		$_POST['date_from'] = '';
		$_POST['date_to'] = date("Y-m-d");
	}
?>
		<div class="row">
			<div class="col-md-12">
			<div style="text-align: right; width: 100%;">
						<a onclick="printPage()" class="no-print btn btn-success" style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print this page</a>
						<a onclick="printPage()" class="no-print btn btn-default" style="color: black; background-color: #ffffff; border-color: #cccccc; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Download to PDF</a>
						<a onclick="printPage()" class="no-print btn btn-info" style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Export to Excel</a>
					</div>
				<div class="card">
					<div class="content">
<!------------------------------Unit wise ----------------------------->						
						<div class="row">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr>
										<th rowspan="2">S.No.</th>
										<th rowspan="2">Mandal Name</th>
										<th colspan="2">कुल परियोजनाओ का विवरण </th>
										<th colspan="2">धनाभाव </th>
										<th colspan="2">विवादित  </th>
										<th colspan="2">प्रगति पर </th>
										<th rowspan="2">कुल पूर्ण<br>(12+14+15)</th>
										<th colspan="2"> पूर्ण</th>
										<th colspan="1"> इन्वेंट्री प्रेषित</th>
										<th colspan="1">हस्तगत</th>
										<th colspan="2">अन्य </th>
										
									</tr>
									<tr>
										<th>संख्या </th>
										<th>धनराशि </th>
										<th>संख्या </th>
										<th>धनराशि </th>
										<th>संख्या </th>
										<th>धनराशि </th>
										<th>संख्या </th>
										<th>धनराशि </th>
										<th>संख्या </th>
										<th>धनराशि </th>
										<th>संख्या </th>
										<th>संख्या </th>
										<th>संख्या </th>
										<th>धनराशि </th>
									</tr>
									<?php
										echo '<tr>';
										for($i=1; $i<=17; $i++){
											echo '<td>'.$i.'</td>';
										}
										echo '</tr>';
									?>
								</thead>
								<tbody>
									<?php
									$sql = 'select * from uprnss_mandal';
									$result_mandal = execute_query($sql);
									$i=1;
									$tot_allotted['c'] = 0;
									$tot_allotted['amount'] = 0;
									$tot_unstarted['c'] = 0;
									$tot_unstarted['amount'] = 0;
									$tot_lack_of_money['c'] = 0;
									$tot_lack_of_money['amount'] = 0;
									$tot_controversial['c'] = 0;
									$tot_controversial['amount'] = 0;
									$tot_progress['c'] = 0;
									$tot_progress['amount'] = 0;
									$tot_complete['c'] = 0;
									$tot_complete['amount'] = 0;
									$tot_inventory_sent['c'] = 0;
									$tot_inventory_sent['amount'] = 0;
									$tot_handover['c'] = 0;
									$tot_handover['amount'] = 0;
									
									$tot_remain_project['c'] = 0;
									$tot_remain_project['amount'] = 0;
									$tot_ci_complete = 0;
									
									
									while($row_mandal = mysqli_fetch_assoc($result_mandal)){
										
										$sql = 'SELECT sno FROM uprnss_district WHERE mandal_name="'.$row_mandal['sno'].'"';
										$result_div = execute_query($sql);	
										// echo $sql;
										$sno_array = array();
										while($row_div = mysqli_fetch_assoc($result_div)){
											$sno_array[] = $row_div["sno"];
										}
										
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1")';
										$allotted = mysqli_fetch_assoc(execute_query($sql));
															
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="6")';
										$lack_of_money = mysqli_fetch_assoc(execute_query($sql));
															
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="4")';
										$controversial = mysqli_fetch_assoc(execute_query($sql));
															
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="10")';
										$progress = mysqli_fetch_assoc(execute_query($sql));
															
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="11")';
										$complete = mysqli_fetch_assoc(execute_query($sql));
															
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="7")';
										$inventory_sent = mysqli_fetch_assoc(execute_query($sql));
															
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="8")';
										$handover = mysqli_fetch_assoc(execute_query($sql));
															
										$sql = 'select * from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="11" or project_status_1="7" or project_status_1="8")';
										$ci_complete = mysqli_num_rows(execute_query($sql));
										
										$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where uprnss_project_temp.district_id in ('.implode(",", $sno_array).') and (status!="5" or status="0" or status is null or status="1") and (project_status_1="" or project_status_1 is null or project_status_1="3" or project_status_1="9" or project_status_1="5" or project_status_1="12" or project_status_1="13")';
										$remain_project = mysqli_fetch_assoc(execute_query($sql));
										
										$tot_ci_complete += $ci_complete;
										
										$tot_remain_project['c'] += $remain_project['c'];
										$tot_remain_project['amount'] += $remain_project['amount'];
										$remain_project['amount'] = $remain_project['amount']==''?0:$remain_project['amount'];
										
										$tot_allotted['c'] += $allotted['c'];
										$tot_allotted['amount'] += $allotted['amount'];
										$allotted['amount'] = $allotted['amount']==''?0:$allotted['amount'];
																				
										$tot_lack_of_money['c'] += $lack_of_money['c'];
										$tot_lack_of_money['amount'] += $lack_of_money['amount'];
										$lack_of_money['amount'] = $lack_of_money['amount']==''?0:$lack_of_money['amount'];
															
										$tot_controversial['c'] += $controversial['c'];
										$tot_controversial['amount'] += $controversial['amount'];
										$controversial['amount'] = $controversial['amount']==''?0:$controversial['amount'];
															
										$tot_progress['c'] += $progress['c'];
										$tot_progress['amount'] += $progress['amount'];
										$progress['amount'] = $progress['amount']==''?0:$progress['amount'];
															
										$tot_complete['c'] += $complete['c'];
										$tot_complete['amount'] += $complete['amount'];
										$complete['amount'] = $complete['amount']==''?0:$complete['amount'];
															
										$tot_inventory_sent['c'] += $inventory_sent['c'];
										$tot_inventory_sent['amount'] += $inventory_sent['amount'];
										$inventory_sent['amount'] = $inventory_sent['amount']==''?0:$inventory_sent['amount'];
															
										$tot_handover['c'] += $handover['c'];
										$tot_handover['amount'] += $handover['amount'];
										$handover['amount'] = $handover['amount']==''?0:$handover['amount'];
											
										echo '<tr>
										<td>'.$i++.'</td>
										<th>'.$row_mandal['mandal_name_english'].'</th>
										<td>'.$allotted['c'].'</td>
										<td>'.round($allotted['amount'], 2).'</td>
										<td>'.$lack_of_money['c'].'</td>
										<td>'.round($lack_of_money['amount'], 2).'</td>
										<td>'.$controversial['c'].'</td>
										<td>'.round($controversial['amount'], 2).'</td>
										<td>'.$progress['c'].'</td>
										<td>'.round($progress['amount'], 2).'</td>
										<th>'.$ci_complete.'</th>
										<td>'.$complete['c'].'</td>
										<td>'.round($complete['amount'], 2).'</td>
										<td>'.$inventory_sent['c'].'</td>
										<td>'.$handover['c'].'</td>
										<td>'.$remain_project['c'].'</td>
										<td>'.round($remain_project['amount'], 2).'</td>
										</tr>';
										}
										echo '
										</tbody>
										<tfoot><tr>
										<th>&nbsp;</th>
										<th>Total</th>
										<th>'.$tot_allotted['c'].'</th>
										<th>'.$tot_allotted['amount'].'</th>
										<th>'.$tot_lack_of_money['c'].'</th>
										<th>'.$tot_lack_of_money['amount'].'</th>
										<th>'.$tot_controversial['c'].'</th>
										<th>'.$tot_controversial['amount'].'</th>
										<th>'.$tot_progress['c'].'</th>
										<th>'.$tot_progress['amount'].'</th>
										<th>'.$tot_ci_complete.'</th>
										<td>'.$tot_complete['c'].'</td>
										<td>'.$tot_complete['amount'].'</td>
										<td>'.$tot_inventory_sent['c'].'</td>
										<td>'.$tot_handover['c'].'</td>
										<th>'.$tot_remain_project['c'].'</th>
										<th>'.$tot_remain_project['amount'].'</th>
										</tr>';
									?>
								</tfoot>
							</table>
						</div>
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
	search: true,
	selectAll: true
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