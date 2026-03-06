<?php
include ("scripts/settings.php");
$msg = '';

page_header_start();
page_header_end();
page_sidebar();

?>

<div class="row">
	<div class="col-md-12">
		<div class="card strpied-tabled-with-hover">
			<div class="card-header text-right">
				<div class="ml-auto no-print">
					<a href="ts_alloted_report.php" class="text-right" target=""><u><i
								class="icon fas fa-file-alt" aria-hidden="true"></i> Alloted Report</u></a>
				</div>
			</div>
			<div class="card-body table-full-width table-responsive">
				<table class="table table-hover table-striped" id="general_stat_table">
					<thead>
						<tr>
							<th>क्रं सं.</th>
							<th>कार्य का नाम </th>
							<th>धनराशि </th>
							<th>स्तर </th>
							<th>शासनादेश संख्या</th>
							<th>जनपद का नाम </th>
							<th>प्रखण्ड का नाम </th>
							<th class="no-print">&nbsp; </th>

					</thead>
					<tbody>
						<?php
						// $sql = 'SELECT * FROM `uprnss_project_temp` WHERE status!="5" and status!="2" ORDER BY  division_id, District_id';
						// status 5= deleted
						$sql = "SELECT *
                        FROM `uprnss_project_temp`
                        WHERE status != '5' AND status != '2'
                            AND NOT EXISTS (
                                SELECT 1
                                FROM `technical_sanction`
                                WHERE `technical_sanction`.`project_id` = `uprnss_project_temp`.`sno` and technical_sanction.status!='5'
                            )
                        ORDER BY sno DESC";
						// echo $sql;
						$result = execute_query($sql);
						$i = 1;
						while ($row = mysqli_fetch_assoc($result)) {

							$sql = 'select * from uprnss_district where sno="' . $row['district_id'] . '"';
							// echo $sql;
							$district = mysqli_fetch_assoc(execute_query($sql));

							$sql = 'select * from uprnss_division where s_no="' . $row['division_id'] . '"';
							// echo $sql;
							$divisions = mysqli_fetch_assoc(execute_query($sql));

							echo '<tr>
                            <td>' . $i++ . '</td>
                            <td>' . $row['project_name_hindi'] . '</td>
                            <td>' . $row['sanction_cost'] . '</td>
                            <td>';
							if ($row['project_type'] != '') {
								if ($row['project_type'] == '1') {
									echo '<span class="">शासन स्तर </span>';
								} elseif ($row['project_type'] == '2') {
									echo '<span class="">जिला स्तर </span>';
								}
							}
							echo '
                            </td>
                            
                            <td>' . $row['admin_go_no'] . '<br>';
							if ($row['admin_go_date'] == "") {
								echo "-";
							} else {
								echo date("d-m-Y", strtotime($row['admin_go_date']));
							}
							;
							echo '</td>
                            <td>' . $district['district_name_hindi'] . '</td>
                            <td>' . $divisions['division_name'] . '</td>
                            
                            <td class="no-print text-center">
                                <a href="create_ts.php?id=' . $row['sno'] . '" onClick="return confirm(\'Are you sure you?\');">Allot<span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title=""></span></a>
                            </td>
                        
                            </tr>';
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
<!--  Charts Plugin -->
<script src="js/chartist.min.js"></script>

<script>

	$(document).ready(function () {
		$('#general_stat_table').DataTable({
			// paging: false,
			fixedHeader: true,
			// colReorder: true,
			// scrollX: true, // Enable horizontal scrolling if needed
		});


	});

	$('select[multiple]').multiselect();



</script>

<?php
page_footer_end();
?>