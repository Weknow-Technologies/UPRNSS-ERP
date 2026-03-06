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
				<h4 style="text-align:center; font-size:28px; margin:0px; ">&nbsp; परियोजनाओ की संख्या और उनका स्थिति
					विभाग के आधार पर </h4>
				<div class="content">
					<!------------------------------Unit wise ----------------------------->
					<div class="row">
						<table class="table table-striped table-bordered table-hover">
							<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th>S.No.</th>
									<th>यूनिट का नाम </th>
									<th>कुल परियोजना </th>
									<th>अनुबंध की प्रक्रिया </th>
									<th>अनारम्भ </th>
									<th>धनाभाव </th>
									<th>विवादित</th>
									<th>प्रगति पर </th>
									<th> पूर्ण</th>
									<th>इन्वेंट्री प्रेषित</th>
									<th>हस्तगत</th>
									<th>स्टेटस नॉट अपडेट </th>
								</tr>
							</thead>
							<tbody>
								<?php
								$sql = 'select * from uprnss_department_name';
								$result_dep = execute_query($sql);
								$i = 1;
								$tot_anubandh = 0;
								$tot_unstarted = 0;
								$tot_lack_of_money = 0;
								$tot_controversial = 0;
								$tot_progress = 0;
								$tot_complete = 0;
								$tot_inventory_sent = 0;
								$tot_handover = 0;
								$tot_not_update = 0;
								$tot_allotted = 0;
								while ($row_dep = mysqli_fetch_assoc($result_dep)) {
									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1")';
									$allotted = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="12")';
									$anubandh = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="5")';
									$unstarted = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="6")';
									$lack_of_money = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="4")';
									$controversial = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="10")';
									$progress = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="11")';
									$complete = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="7")';
									$inventory_sent = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="8")';
									$handover = mysqli_num_rows(execute_query($sql));

									$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1") and (project_status_1="" or project_status_1 is null or project_status_1="3" or project_status_1="9")';
									$not_update = mysqli_num_rows(execute_query($sql));


									$tot_anubandh += $anubandh;
									$tot_unstarted += $unstarted;
									$tot_lack_of_money += $lack_of_money;
									$tot_controversial += $controversial;
									$tot_progress += $progress;
									$tot_complete += $complete;
									$tot_inventory_sent += $inventory_sent;
									$tot_handover += $handover;
									$tot_not_update += $not_update;
									$tot_allotted += $allotted;

									echo '<tr>
													<td>' . $i++ . '</td>
													<td><a href="dashboard_department.php?id=' . $row_dep['sno'] . '" target="_blank">' . $row_dep['department_name_hindi'] . '</a></td>
													<td>' . $allotted . '</td>
													<td>' . $anubandh . '</td>
													<td>' . $unstarted . '</td>
													<td>' . $lack_of_money . '</td>
													<td>' . $controversial . '</td>
													<td>' . $progress . '</td>
													<td>' . $complete . '</td>
													<td>' . $inventory_sent . '</td>
													<td>' . $handover . '</td>
													<td class="bg-danger text-white">' . $not_update . '</td>
													
													</tr>';
								}
								echo '
												</tbody>
												<tfoot><tr>
												<th>&nbsp;</th>
												<th>Total</th>
												<th>' . $tot_allotted . '</th>
												<th>' . $tot_anubandh . '</th>
												<th>' . $tot_unstarted . '</th>
												<th>' . $tot_lack_of_money . '</th>
												<th>' . $tot_controversial . '</th>
												<th>' . $tot_progress . '</th>
												<th>' . $tot_complete . '</th>
												<th>' . $tot_inventory_sent . '</th>
												<th>' . $tot_handover . '</th>
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