<?php
include ("scripts/settings.php");

$msg = '';
$tab = 1;
page_header_start();
page_header_end();
page_sidebar();


?>


<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">


	<div class="row">
		<div class="col-md-12">


			<div style="text-align: right; width: 100%;">
				<a onclick="printPage()" class="no-print btn btn-success"
					style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print
					this page</a>
				<a onclick="printPage()" class="no-print btn btn-default"
					style="color: black; background-color: #ffffff; border-color: #cccccc; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Download
					to PDF</a>
				<a onclick="printPage()" class="no-print btn btn-info"
					style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Export
					to Excel</a>
			</div>

			<div class="card">
				<p style="text-align:center; font-size:15px;">&nbsp; यू . पी . आर. एन. एस. एस. </p>
				<h4 style="text-align:center; font-size:28px; margin:0px; ">&nbsp; परियोजनाओ की संख्या, धनराशि और उनका
					स्थिति यूनिट के आधार पर </h4>
				<div class="content">
					<!------------------------------Unit wise ----------------------------->
					<div class="row">
						<table class="table table-striped table-bordered table-hover">
							<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th>S.No.</th>
									<th>यूनिट का नाम </th>
									<th>कुल परियोजना </th>
									<th>कुल परियोजना ( लागत )</th>
									<th>अनुबंध की प्रक्रिया </th>
									<th>अनुबंध की प्रक्रिया ( लागत ) </th>
									<th>अनारम्भ </th>
									<th>अनारम्भ ( लागत ) </th>
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
								$sql = 'select * from uprnss_division order by division_name ASC';
								$result_div = execute_query($sql);
								$i = 1;
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
								$tot_not_update = 0;
								// $tot_allotted = 0;
								
								$tot_allotted['c'] = 0;
								$tot_allotted['amount'] = 0;

								while ($row_div = mysqli_fetch_assoc($result_div)) {
									// $sql = 'select * from uprnss_project_temp where division_id="'.$row_div['s_no'].'" and (status!="5" or status="0" or status is null or status="1")';
									// $allotted = mysqli_num_rows(execute_query($sql));
								
									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1")';
									// echo $sql;
									$allotted = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="12")';
									// echo $sql;
									$anubandh = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="5")';
									$unstarted = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="6")';
									$lack_of_money = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="4")';
									$controversial = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="10")';
									$progress = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="11")';
									$complete = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="7")';
									$inventory_sent = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="8")';
									$handover = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="" or project_status_1 is null or project_status_1="3" or project_status_1="9")';
									$not_update = mysqli_num_rows(execute_query($sql));

									$tot_not_update += $not_update;
									$tot_anubandh['c'] += $anubandh['c'];
									$tot_anubandh['amount'] += $anubandh['amount'];
									$anubandh['amount'] = $anubandh['amount'] == '' ? 0 : $anubandh['amount'];


									$tot_unstarted['c'] += $unstarted['c'];
									$tot_unstarted['amount'] += $unstarted['amount'];
									$unstarted['amount'] = $unstarted['amount'] == '' ? 0 : $unstarted['amount'];

									$tot_lack_of_money['c'] += $lack_of_money['c'];
									$tot_lack_of_money['amount'] += $lack_of_money['amount'];
									$lack_of_money['amount'] = $lack_of_money['amount'] == '' ? 0 : $lack_of_money['amount'];

									$tot_controversial['c'] += $controversial['c'];
									$tot_controversial['amount'] += $controversial['amount'];
									$controversial['amount'] = $controversial['amount'] == '' ? 0 : $controversial['amount'];

									$tot_progress['c'] += $progress['c'];
									$tot_progress['amount'] += $progress['amount'];
									$progress['amount'] = $progress['amount'] == '' ? 0 : $progress['amount'];

									$tot_complete['c'] += $complete['c'];
									$tot_complete['amount'] += $complete['amount'];
									$complete['amount'] = $complete['amount'] == '' ? 0 : $complete['amount'];

									$tot_inventory_sent['c'] += $inventory_sent['c'];
									$tot_inventory_sent['amount'] += $inventory_sent['amount'];
									$inventory_sent['amount'] = $inventory_sent['amount'] == '' ? 0 : $inventory_sent['amount'];

									$tot_handover['c'] += $handover['c'];
									$tot_handover['amount'] += $handover['amount'];
									$handover['amount'] = $handover['amount'] == '' ? 0 : $handover['amount'];

									$tot_allotted['c'] += $allotted['c'];
									$tot_allotted['amount'] += $allotted['amount'];
									$allotted['amount'] = $allotted['amount'] == '' ? 0 : $allotted['amount'];

									$tot_allotted += $allotted;

									echo '<tr>
													<td>' . $i++ . '</td>
													<td><a href="dashboard_division.php?id=' . $row_div['s_no'] . '" target="_blank">' . $row_div['division_name'] . '</a></td>
													<td>' . $allotted['c'] . '</td>
													<td>' . round($allotted['amount'], 2) . '</td>
													<td>' . $anubandh['c'] . '</td>
													<td>' . round($anubandh['amount'], 2) . '</td>
													<td>' . $unstarted['c'] . '</td>
													<td>' . round($unstarted['amount'], 2) . '</td>
													<td>' . $lack_of_money['c'] . '</td>
													<td>' . round($lack_of_money['amount'], 2) . '</td>
													<td>' . $controversial['c'] . '</td>
													<td>' . round($controversial['amount'], 2) . '</td>
													<td>' . $progress['c'] . '</td>
													<td>' . round($progress['amount'], 2) . '</td>
													<td>' . $complete['c'] . '</td>
													<td>' . round($complete['amount'], 2) . '</td>
													<td>' . $inventory_sent['c'] . '</td>
													<td>' . round($inventory_sent['amount'], 2) . '</td>
													<td>' . $handover['c'] . '</td>
													<td>' . round($handover['amount'], 2) . '</td>
													<td class="bg-danger text-white">' . $not_update . '</td>
													</tr>';
								}
								echo '
												</tbody>
												<tfoot><tr>
												<th>&nbsp;</th>
												<th>Total</th>
												<th>' . $tot_allotted['c'] . '</th>
												<th>' . $tot_allotted['amount'] . '</th>
												<th>' . $tot_anubandh['c'] . '</th>
												<th>' . $tot_anubandh['amount'] . '</th>
												<th>' . $tot_unstarted['c'] . '</th>
												<th>' . $tot_unstarted['amount'] . '</th>
												<th>' . $tot_lack_of_money['c'] . '</th>
												<th>' . $tot_lack_of_money['amount'] . '</th>
												<th>' . $tot_controversial['c'] . '</th>
												<th>' . $tot_controversial['amount'] . '</th>
												<th>' . $tot_progress['c'] . '</th>
												<th>' . $tot_progress['amount'] . '</th>
												<th>' . $tot_complete['c'] . '</th>
												<th>' . $tot_complete['amount'] . '</th>
												<th>' . $tot_inventory_sent['c'] . '</th>
												<th>' . $tot_inventory_sent['amount'] . '</th>
												<th>' . $tot_handover['c'] . '</th>
												<th>' . $tot_handover['amount'] . '</th>
												<th class="bg-danger text-white">' . $tot_not_update . '</th>
												</tr>';
								?>
								</tfoot>
						</table>
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
	function printPage() {
		window.print();
	}
</script>

<script>

	$('select[multiple]').multiselect();
</script>


<?php
page_footer_end();
?>