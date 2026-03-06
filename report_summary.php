<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
$response=1;

page_header_start();
page_header_end();
page_sidebar();

if(!isset($_POST['date_from'])){
	$_POST['date_from'] = date("Y-m-01");
	$_POST['date_to'] = date("Y-m-d");
}

if(isset($_GET['df'])){
	$_POST['date_from'] = $_GET['df'];
	$_POST['date_to'] = $_GET['dt'];
}

if(isset($_GET['zone'])){
	$response=2;
}
if(isset($_GET['div'])){
	$response=3;
}
?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	<div id="container" class="no-print">	
		<div class="card card-body">    
            
            <div class="" style="font-size:1.2rem;text-align:center;font-family:arial;"></div>
			<div class="row d-flex my-auto">    	
				<table width="100%" class="table table-striped table-hover rounded" >	
					<tr >
						<th width="16%">Date Type</th>
						<th width="18%"><select name="date_type" id="date_type" class="form-control" >
								<option value="admin_go_date">Administrative GO Date</option>
							</select>
						</th>
						<th  width="15%">Form</th>
						<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
						<th  width="15%">To</th>
						<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
						<th><button type="submit" name="search" class="btn btn-primary">Search</button></th>
					</tr>
				</table>
			</div>
		</div>
	</div>
<?php
	   switch($response){
		   case 1:{
?>
	   
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<p style="text-align:center; font-size:15px;">&nbsp; यू . पी . आर. एन. एस. एस.   </p>
						<h4 style="text-align:center; font-size:28px; margin:0px; ">&nbsp; परियोजनाओ की संख्या, धनराशि  और उनका स्थिति विभाग के आधार पर </h4>
						<div class="content">
	<!------------------------------Unit wise ----------------------------->						
							<div class="row">
								<table class="table table-striped table-bordered table-hover" id="general_stat_table">
									<thead style="position:sticky;top:0; z-index:2;">
										<tr>
											<th>S.No.</th>
											<th>जोन का नाम </th>
											<th>कुल  परियोजना </th>
											<th>कुल परियोजना लागत</th>
											<th>परियोजना जिनका MPR भरा नही गय</th>
											<th>अनुबंध की प्रक्रिया </th>
											<th>अनुबंध की प्रक्रिया ( लागत ) </th>
											<th>अनारम्भ </th>
											<th>अनारम्भ  ( लागत ) </th>
											<th>धनाभाव </th>
											<th>धनाभाव ( लागत ) </th>
											<th>विवादित</th>
											<th>विवादित ( लागत ) </th>
											<th>प्रगति पर </th>
											<th>प्रगति पर ( लागत ) </th>
											<th> पूर्ण</th>
											<th> पूर्ण ( लागत ) </th>
											<th>इन्वेंट्री प्रेषित</th>
											<th>इन्वेंट्री प्रेषित ( लागत ) </th>
											<th>हस्तगत</th>
											<th>हस्तगत ( लागत ) </th>
											<th>स्टेटस नॉट अपडेट </th>
										</tr>
									</thead>
									<tbody>
										<?php
										$sql = 'select * from master_zone';
										$result_dep = execute_query($sql);
										$i=1;
										$tot_anubandh['c'] = 0;
										$tot_anubandh['amount'] = 0;
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
										$tot_allotted = 0;
										$tot_allotted_amount = 0;
										$tot_not_update = 0;
										$tot_missing_mpr=0;
										while($row_zone = mysqli_fetch_assoc($result_dep)){
											$sql = 'select * from uprnss_division where zone_id="'.$row_zone['sno'].'"';
											$result_division = execute_query($sql);
											$allotted['c']=0;
											$allotted['amount']=0;
											$anubandh['c']=0;
											$anubandh['amount']=0;
											$unstarted['c']=0;
											$unstarted['amount']=0;
											$lack_of_money['c']=0;
											$lack_of_money['amount']=0;
											$controversial['c']=0;
											$controversial['amount']=0;
											$progress['c']=0;
											$progress['amount']=0;
											$complete['c']=0;
											$complete['amount']=0;
											$inventory_sent['c']=0;
											$inventory_sent['amount']=0;
											$handover['c']=0;
											$handover['amount']=0;
											$not_update['c']=0;
											$not_update['amount']=0;
											$division_id_array = array();

											while($row_division = mysqli_fetch_assoc($result_division)){
												$division_id_array[] = $row_division['s_no'];
											}

											//$row_division['sno'] = $row_division['s_no'];
											$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id in ('.implode(",", $division_id_array).') and (status!="5" or status="0" or status is null or status="1") and admin_go_date>="'.$_POST['date_from'].'" and admin_go_date<="'.$_POST['date_to'].'"';
											//echo $sql.'<br>';
											$allotted_row = mysqli_fetch_assoc(execute_query($sql));
											$allotted['c'] += $allotted_row['c'];
											$allotted['amount'] += $allotted_row['amount'];

											$missing_mpr = 'SELECT f.sno, f.project_name_hindi FROM uprnss_project_temp f LEFT JOIN invoice_civil s ON f.sno=s.project_name WHERE s.project_name is NULL  and f.division_id in ('.implode(",", $division_id_array).') and f.admin_go_date>="'.$_POST['date_from'].'" and f.admin_go_date<="'.$_POST['date_to'].'" and (f.status!="5" or f.status="0" or f.status is null or f.status="1")';
											//echo $missing_mpr.'<br>';
											$missing_mpr_count = mysqli_num_rows(execute_query($missing_mpr));

											$sql = 'WITH ranked_messages AS (SELECT m.*, ROW_NUMBER() OVER (PARTITION BY project_name ORDER BY sno DESC) AS rn  FROM invoice_civil AS m where division_id in ('.implode(",", $division_id_array).') and (status!="5" or status="0" or status is null or status="1") and admin_go_date>="'.$_POST['date_from'].'" and admin_go_date<="'.$_POST['date_to'].'")SELECT count(*) c, sum(abs(sanction_cost)) as amount, project_status_1 FROM ranked_messages WHERE rn = 1 group by project_status_1;';

											$sql = 'SELECT p1.sno, count(*) c, sum(abs(sanction_cost)) as amount, project_status_1 FROM invoice_civil p1 INNER JOIN (SELECT MAX(pi.sno) AS maxsno FROM invoice_civil pi where division_id in ('.implode(",", $division_id_array).') GROUP BY pi.project_name) p2 ON (p1.sno = p2.maxsno) WHERE p1.division_id in ('.implode(",", $division_id_array).')  and p1.admin_go_date>="'.$_POST['date_from'].'" and p1.admin_go_date<="'.$_POST['date_to'].'" and (status!="5" or status="0" or status is null or status="1") and project_name in (select sno from uprnss_project_temp where division_id in ('.implode(",", $division_id_array).') and admin_go_date>="'.$_POST['date_from'].'" and admin_go_date<="'.$_POST['date_to'].'")  group by project_status_1';
											//echo $sql.'<br>=======================================';
											$result_status = execute_query($sql);
											while($row_status = mysqli_fetch_assoc($result_status)){
												switch($row_status['project_status_1']){
													case 12:{
														$anubandh['c'] += $row_status['c'];
														$anubandh['amount'] += $row_status['amount'];
														break;
													}
													case 5:{
														$unstarted['c'] += $row_status['c'];
														$unstarted['amount'] += $row_status['amount'];
														break;
													}
													case 6:{
														$lack_of_money['c'] += $row_status['c'];
														$lack_of_money['amount'] += $row_status['amount'];
														break;
													}
													case 4:{
														$controversial['c'] += $row_status['c'];
														$controversial['amount'] += $row_status['amount'];
														break;
													}
													case 10:{
														$progress['c'] += $row_status['c'];
														$progress['amount'] += $row_status['amount'];
														break;
													}
													case 11:{
														$complete['c'] += $row_status['c'];
														$complete['amount'] += $row_status['amount'];
														break;
													}
													case 7:{
														$inventory_sent['c'] += $row_status['c'];
														$inventory_sent['amount'] += $row_status['amount'];
														break;
													}
													case 8:{
														$handover['c'] += $row_status['c'];
														$handover['amount'] += $row_status['amount'];
														break;
													}
													case 9:{
														$not_update['c'] += $row_status['c'];
														$not_update['amount'] += $row_status['amount'];
														break;
													}
													default:{
														$not_update['c'] += $row_status['c'];
														$not_update['amount'] += $row_status['amount'];
														break;
													}

												}

											}
											$tot_allotted += $allotted['c'];
											$tot_allotted_amount += $allotted['amount'];
											$tot_missing_mpr+=$missing_mpr_count;
											echo '<tr>
											<td>'.$i++.'</td>
											<td><a href="report_summary.php?zone='.$row_zone['sno'].'&df='.$_POST['date_from'].'&dt='.$_POST['date_to'].'" target="_blank">'.$row_zone['zone_name'].'</a></td>
											<td>'.$allotted['c'].'</td>
											<td>'.round($allotted['amount'],2).'</td>
											<td>'.$missing_mpr_count.'</td>
											<td>'.$anubandh['c'].'</td>
											<td>'.round($anubandh['amount'], 2).'</td>
											<td>'.$unstarted['c'].'</td>
											<td>'.round($unstarted['amount'], 2).'</td>
											<td>'.$lack_of_money['c'].'</td>
											<td>'.round($lack_of_money['amount'], 2).'</td>
											<td>'.$controversial['c'].'</td>
											<td>'.round($controversial['amount'], 2).'</td>
											<td>'.$progress['c'].'</td>
											<td>'.round($progress['amount'], 2).'</td>
											<td>'.$complete['c'].'</td>
											<td>'.round($complete['amount'], 2).'</td>
											<td>'.$inventory_sent['c'].'</td>
											<td>'.round($inventory_sent['amount'], 2).'</td>
											<td>'.$handover['c'].'</td>
											<td>'.round($handover['amount'], 2).'</td>
											<td class="bg-danger text-white">'.$not_update['c'].'</td>

											</tr>';

											$tot_not_update += $not_update['c'];

											$tot_anubandh['c'] += $anubandh['c'];
											$tot_anubandh['amount'] += $anubandh['amount'];
											$anubandh['amount'] = $anubandh['amount']==''?0:$anubandh['amount'];


											$tot_unstarted['c'] += $unstarted['c'];
											$tot_unstarted['amount'] += $unstarted['amount'];
											$unstarted['amount'] = $unstarted['amount']==''?0:$unstarted['amount'];

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

										}
										echo '
										</tbody>
										<tfoot><tr>
										<th>&nbsp;</th>
										<th>Total</th>
										<th>'.$tot_allotted.'</th>
										<th>'.round($tot_allotted_amount,2).'</th>
										<th>'.$tot_missing_mpr.'</th>
										<th>'.$tot_anubandh['c'].'</th>
										<th>'.$tot_anubandh['amount'].'</th>
										<th>'.$tot_unstarted['c'].'</th>
										<th>'.$tot_unstarted['amount'].'</th>
										<th>'.$tot_lack_of_money['c'].'</th>
										<th>'.$tot_lack_of_money['amount'].'</th>
										<th>'.$tot_controversial['c'].'</th>
										<th>'.$tot_controversial['amount'].'</th>
										<th>'.$tot_progress['c'].'</th>
										<th>'.$tot_progress['amount'].'</th>
										<th>'.$tot_complete['c'].'</th>
										<th>'.$tot_complete['amount'].'</th>
										<th>'.$tot_inventory_sent['c'].'</th>
										<th>'.$tot_inventory_sent['amount'].'</th>
										<th>'.$tot_handover['c'].'</th>
										<th>'.$tot_handover['amount'].'</th>
										<th class="bg-danger text-white">'.$tot_not_update.'</th>
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
		   break;
	   }
		   case 2:{
?>
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<p style="text-align:center; font-size:15px;">&nbsp; यू . पी . आर. एन. एस. एस.   </p>
						<h4 style="text-align:center; font-size:28px; margin:0px; ">&nbsp; परियोजनाओ की संख्या, धनराशि  और उनका स्थिति विभाग के आधार पर </h4>
						<div class="content">
	<!------------------------------Unit wise ----------------------------->						
							<div class="row">
								<table class="table table-striped table-bordered table-hover" id="general_stat_table">
									<thead style="position:sticky;top:0; z-index:2;">
										<tr>
											<th>S.No.</th>
											<th>प्रखंड का नाम </th>
											<th>कुल परियोजना </th>
											<th>कुल परियोजना लागत</th>
											<th>परियोजना जिनका MPR भरा नही गय</th>
											<th>अनुबंध की प्रक्रिया </th>
											<th>अनुबंध की प्रक्रिया ( लागत ) </th>
											<th>अनारम्भ </th>
											<th>अनारम्भ  ( लागत ) </th>
											<th>धनाभाव </th>
											<th>धनाभाव ( लागत ) </th>
											<th>विवादित</th>
											<th>विवादित ( लागत ) </th>
											<th>प्रगति पर </th>
											<th>प्रगति पर ( लागत ) </th>
											<th> पूर्ण</th>
											<th> पूर्ण ( लागत ) </th>
											<th>इन्वेंट्री प्रेषित</th>
											<th>इन्वेंट्री प्रेषित ( लागत ) </th>
											<th>हस्तगत</th>
											<th>हस्तगत ( लागत ) </th>
											<th>स्टेटस नॉट अपडेट </th>
										</tr>
									</thead>
									<tbody>
										<?php
										$sql = 'select * from master_zone where sno="'.$_GET['zone'].'"';
										$result_dep = execute_query($sql);
										$i=1;
										$tot_anubandh['c'] = 0;
										$tot_anubandh['amount'] = 0;
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
										$tot_allotted = 0;
										$tot_allotted_amount = 0;
										$tot_not_update = 0;
										$tot_missing_mpr=0;
										while($row_zone = mysqli_fetch_assoc($result_dep)){
											$sql = 'select * from uprnss_division where zone_id="'.$row_zone['sno'].'"';
											$result_division = execute_query($sql);
											$allotted['c']=0;
											$allotted['amount']=0;
											$anubandh['c']=0;
											$anubandh['amount']=0;
											$unstarted['c']=0;
											$unstarted['amount']=0;
											$lack_of_money['c']=0;
											$lack_of_money['amount']=0;
											$controversial['c']=0;
											$controversial['amount']=0;
											$progress['c']=0;
											$progress['amount']=0;
											$complete['c']=0;
											$complete['amount']=0;
											$inventory_sent['c']=0;
											$inventory_sent['amount']=0;
											$handover['c']=0;
											$handover['amount']=0;
											$not_update['c']=0;
											$not_update['amount']=0;
											$division_id_array = array();

											while($row_division = mysqli_fetch_assoc($result_division)){

												//$row_division['sno'] = $row_division['s_no'];
												$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="'.$row_division['s_no'].'" and (status!="5" or status="0" or status is null or status="1") and admin_go_date>="'.$_POST['date_from'].'" and admin_go_date<="'.$_POST['date_to'].'"';
												//echo $sql.'<br>';
												$allotted_row = mysqli_fetch_assoc(execute_query($sql));
												$allotted['c'] = $allotted_row['c'];
												$allotted['amount'] = $allotted_row['amount'];

												$missing_mpr = 'SELECT f.sno, f.project_name_hindi FROM uprnss_project_temp f LEFT JOIN invoice_civil s ON f.sno=s.project_name WHERE s.project_name is NULL  and f.division_id="'.$row_division['s_no'].'" and f.admin_go_date>="'.$_POST['date_from'].'" and f.admin_go_date<="'.$_POST['date_to'].'" and (f.status!="5" or f.status="0" or f.status is null or f.status="1")';
												//echo $missing_mpr.'<br>';
												$missing_mpr_count = mysqli_num_rows(execute_query($missing_mpr));

												$sql = 'WITH ranked_messages AS (SELECT m.*, ROW_NUMBER() OVER (PARTITION BY project_name ORDER BY sno DESC) AS rn  FROM invoice_civil AS m where division_id="'.$row_division['s_no'].'" and (status!="5" or status="0" or status is null or status="1") and admin_go_date>="'.$_POST['date_from'].'" and admin_go_date<="'.$_POST['date_to'].'")SELECT count(*) c, sum(abs(sanction_cost)) as amount, project_status_1 FROM ranked_messages WHERE rn = 1 group by project_status_1;';

												$sql = 'SELECT p1.sno, count(*) c, sum(abs(sanction_cost)) as amount, project_status_1 FROM invoice_civil p1 INNER JOIN (SELECT MAX(pi.sno) AS maxsno FROM invoice_civil pi where division_id="'.$row_division['s_no'].'" GROUP BY pi.project_name) p2 ON (p1.sno = p2.maxsno) WHERE p1.division_id="'.$row_division['s_no'].'"  and p1.admin_go_date>="'.$_POST['date_from'].'" and p1.admin_go_date<="'.$_POST['date_to'].'" and (status!="5" or status="0" or status is null or status="1") and project_name in (select sno from uprnss_project_temp where division_id="'.$row_division['s_no'].'" and admin_go_date>="'.$_POST['date_from'].'" and admin_go_date<="'.$_POST['date_to'].'")  group by project_status_1';
												//echo $sql.'<br>=======================================';
												$result_status = execute_query($sql);
												while($row_status = mysqli_fetch_assoc($result_status)){
													switch($row_status['project_status_1']){
														case 12:{
															$anubandh['c'] = $row_status['c'];
															$anubandh['amount'] = $row_status['amount'];
															break;
														}
														case 5:{
															$unstarted['c'] = $row_status['c'];
															$unstarted['amount'] = $row_status['amount'];
															break;
														}
														case 6:{
															$lack_of_money['c'] = $row_status['c'];
															$lack_of_money['amount'] = $row_status['amount'];
															break;
														}
														case 4:{
															$controversial['c'] = $row_status['c'];
															$controversial['amount'] = $row_status['amount'];
															break;
														}
														case 10:{
															$progress['c'] = $row_status['c'];
															$progress['amount'] = $row_status['amount'];
															break;
														}
														case 11:{
															$complete['c'] = $row_status['c'];
															$complete['amount'] = $row_status['amount'];
															break;
														}
														case 7:{
															$inventory_sent['c'] = $row_status['c'];
															$inventory_sent['amount'] = $row_status['amount'];
															break;
														}
														case 8:{
															$handover['c'] = $row_status['c'];
															$handover['amount'] = $row_status['amount'];
															break;
														}
														case 9:{
															$not_update['c'] = $row_status['c'];
															$not_update['amount'] = $row_status['amount'];
															break;
														}
														default:{
															$not_update['c'] = $row_status['c'];
															$not_update['amount'] = $row_status['amount'];
															break;
														}

													}

												}
												$tot_allotted += $allotted['c'];
												$tot_allotted_amount += $allotted['amount'];
												$tot_missing_mpr += $missing_mpr_count;
												echo '<tr>
												<td>'.$i++.'</td>
												<td><a href="report_summary.php?div='.$row_division['s_no'].'&df='.$_GET['df'].'&dt='.$_GET['dt'].'" target="_blank">'.$row_division['division_name'].'</a></td>
												<td>'.$allotted['c'].'</td>
												<td>'.round($allotted['amount'],2).'</td>
												<td>'.$missing_mpr_count.'</td>
												<td>'.$anubandh['c'].'</td>
												<td>'.round($anubandh['amount'], 2).'</td>
												<td>'.$unstarted['c'].'</td>
												<td>'.round($unstarted['amount'], 2).'</td>
												<td>'.$lack_of_money['c'].'</td>
												<td>'.round($lack_of_money['amount'], 2).'</td>
												<td>'.$controversial['c'].'</td>
												<td>'.round($controversial['amount'], 2).'</td>
												<td>'.$progress['c'].'</td>
												<td>'.round($progress['amount'], 2).'</td>
												<td>'.$complete['c'].'</td>
												<td>'.round($complete['amount'], 2).'</td>
												<td>'.$inventory_sent['c'].'</td>
												<td>'.round($inventory_sent['amount'], 2).'</td>
												<td>'.$handover['c'].'</td>
												<td>'.round($handover['amount'], 2).'</td>
												<td class="bg-danger text-white">'.$not_update['c'].'</td>

												</tr>';

												$tot_not_update += $not_update['c'];

												$tot_anubandh['c'] += $anubandh['c'];
												$tot_anubandh['amount'] += $anubandh['amount'];
												$anubandh['amount'] = $anubandh['amount']==''?0:$anubandh['amount'];


												$tot_unstarted['c'] += $unstarted['c'];
												$tot_unstarted['amount'] += $unstarted['amount'];
												$unstarted['amount'] = $unstarted['amount']==''?0:$unstarted['amount'];

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
											}

										}
										echo '
										</tbody>
										<tfoot><tr>
										<th>&nbsp;</th>
										<th>Total</th>
										<th>'.$tot_allotted.'</th>
										<th>'.$tot_allotted_amount.'</th>
										<th>'.$tot_missing_mpr.'</th>
										<th>'.$tot_anubandh['c'].'</th>
										<th>'.$tot_anubandh['amount'].'</th>
										<th>'.$tot_unstarted['c'].'</th>
										<th>'.$tot_unstarted['amount'].'</th>
										<th>'.$tot_lack_of_money['c'].'</th>
										<th>'.$tot_lack_of_money['amount'].'</th>
										<th>'.$tot_controversial['c'].'</th>
										<th>'.$tot_controversial['amount'].'</th>
										<th>'.$tot_progress['c'].'</th>
										<th>'.$tot_progress['amount'].'</th>
										<th>'.$tot_complete['c'].'</th>
										<th>'.$tot_complete['amount'].'</th>
										<th>'.$tot_inventory_sent['c'].'</th>
										<th>'.$tot_inventory_sent['amount'].'</th>
										<th>'.$tot_handover['c'].'</th>
										<th>'.$tot_handover['amount'].'</th>
										<th class="bg-danger text-white">'.$tot_not_update.'</th>
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
		   break;
	   }
	   case 3:{
?>
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<p style="text-align:center; font-size:15px;">&nbsp; यू . पी . आर. एन. एस. एस.   </p>
						<h4 style="text-align:center; font-size:28px; margin:0px; ">&nbsp; परियोजनाओ की संख्या, धनराशि  और उनका स्थिति विभाग के आधार पर </h4>
						<div class="content">
	<!------------------------------Unit wise ----------------------------->						
							<div class="row">
								<table class="table table-striped table-bordered table-hover" id="general_stat_table">
									<thead style="position:sticky;top:0; z-index:2;">
										<tr>
											<th>S.No.</th>
											<th>विभाग का नाम </th>
											<th>जिले का नाम </th>
											<th>परियोजना का नाम </th>
											<th>परियोजना की लागत </th>
											<th>शाशनादेश संख्या</th>
											<th>शाशनादेश का दिनांक</th>
											<th>परियोजना कि स्थिति</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i=1;
										$tot_anubandh['c'] = 0;
										$tot_anubandh['amount'] = 0;
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
										$tot_allotted = 0;
										$tot_not_update = 0;
										$tot_missing_mpr=0;
										$allotted['c']=0;
										$allotted['amount']=0;
										$anubandh['c']=0;
										$anubandh['amount']=0;
										$unstarted['c']=0;
										$unstarted['amount']=0;
										$lack_of_money['c']=0;
										$lack_of_money['amount']=0;
										$controversial['c']=0;
										$controversial['amount']=0;
										$progress['c']=0;
										$progress['amount']=0;
										$complete['c']=0;
										$complete['amount']=0;
										$inventory_sent['c']=0;
										$inventory_sent['amount']=0;
										$handover['c']=0;
										$handover['amount']=0;
										$not_update['c']=0;
										$not_update['amount']=0;
										$division_id_array = array();
		   
		   								$sql = 'SELECT uprnss_project_temp.sno as sno,
										uprnss_project_temp.project_name_hindi as project_name_hindi,
										uprnss_project_temp.sanction_cost as sanction_cost,
										uprnss_project_temp.sanction_date as sanction_date,
										uprnss_project_temp.admin_go_no as admin_go_no,
										uprnss_project_temp.admin_go_date as admin_go_date,
										uprnss_project_temp.last_update as last_update,
										uprnss_district.district_name_hindi as district_name_hindi,
										uprnss_department_name.department_name_hindi as department_name_hindi
										from uprnss_project_temp
										left join uprnss_district on uprnss_district.sno = district_id
										left join uprnss_department_name on uprnss_department_name.sno = department_id 
										where uprnss_project_temp.division_id="'.$_GET['div'].'" and (uprnss_project_temp.status!="5" or uprnss_project_temp.status="0" or uprnss_project_temp.status is null or uprnss_project_temp.status="1") and uprnss_project_temp.admin_go_date>="'.$_POST['date_from'].'" and uprnss_project_temp.admin_go_date<="'.$_POST['date_to'].'"
										order by abs(department_id), abs(district_id)';
		   								//echo $sql;
		   								$result_division = execute_query($sql);
		   								while($row_division = mysqli_fetch_assoc($result_division)){
											echo '<tr><td>'.$i++.'</td>
											<td>'.$row_division['department_name_hindi'].'</td>
											<td>'.$row_division['district_name_hindi'].'</td>
											<td>'.$row_division['project_name_hindi'].'</td>
											<td>'.$row_division['sanction_cost'].'</td>
											<td>'.$row_division['admin_go_no'].'</td>
											<td>'.$row_division['admin_go_date'].'</td>';
											
											$sql = 'select * from invoice_civil where project_name="'.$row_division['sno'].'" order by sno desc limit 1';
											$result_invoice = execute_query($sql);
											if(mysqli_num_rows($result_invoice)!=0){
												$row_invoice = mysqli_fetch_assoc($result_invoice);
												echo '<td>'.$row_invoice['project_status_1'].'</td>';
											}
											else{
												echo '<td></td>';
											}
											echo '</tr>';

										}
										?>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php
		   break;
	   }
	   }
?>
	   		   
					

    </form>
<?php
page_footer_start();
?>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect();
	
$(document).ready( function () {
	var t = $('#general_stat_table').DataTable({
		paging: false
    });
 
});
	
	
</script>

    
<?php		
page_footer_end();
?>