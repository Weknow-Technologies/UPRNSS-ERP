<?php
include ("scripts/settings.php");

$msg = '';
$tab = 1;

page_header_start();
page_header_end();
page_sidebar();


if (!isset($_POST['search'])) {
	$_POST['department'] = '';
	$_POST['district'] = '';
}

?>

<!------------------------------Department wise ----------------------------->
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
		<div class="card"></br>
			<p style="text-align:center; font-size:15px;">&nbsp; यू . पी . आर. एन. एस. एस. </p>
			<h4 style="text-align:center; font-size:28px; margin:0px; ">&nbsp; मासिक प्रगति रिपोर्ट </h4>
			<div class="content">
				<div class="row">
					<?php
					$html = '<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>S.No.</th>
							<th>विभाग का नाम </th>
							<th>दिनांक 31-03 -2023 के पश्चात अवशेष परियोजनाओ की संख्या </th>
							<th>दिनांक 31-03 -2023 के पश्चात अवशेष परियोजनाओ की धनराशि </th>
							<th>आलोच्य माह मे प्राप्त परियोजनाओ का संख्या </th>
							<th>आलोच्य माह मे प्राप्त परियोजनाओ का लागत </th>
							<th>आलोच्य माह मे प्राप्त धनराशि </th>
							<th>आलोच्य माह मे व्यय  धनराशि </th>
							<th>कुल व्यय धनराशि (दिनांक 01-04 -2023 से अब तक )</th>
							<th>आलोच्य माह मे पूर्ण परियोजनाओ</th>
							<th>आलोच्य माह मे हस्तगत परियोजनाओ</th>
							<th>कुल पूर्ण परियोजनाओ की संख्या (दिनांक 01-04 -2023 से अब तक )</th>
						</tr>
						<tr>';

					for ($i = 1; $i <= 12; $i++) {
						$html .= '<th>' . $i . '</th>';
					}

					$html .= '</tr>
						</thead>
						<tbody>';

					$i = 1;
					$tot_freeze = 0;
					$tot_allotted = 0;
					$tot_remain['c'] = 0;
					$tot_remain['amount'] = 0;
					$tot_current_month_project['c'] = 0;
					$tot_current_month_project['amount'] = 0;

					$sql = 'select * from uprnss_department_name';

					$result_dep = execute_query($sql);
					// echo $sql;
					while ($row_dep = mysqli_fetch_assoc($result_dep)) {

						$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (status!="5" or status="0" or status is null or status="1")';
						$allotted = mysqli_num_rows(execute_query($sql));

						$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (project_status_1!="8" or project_status_1!="11" ) and (status!="5" or status="0" or status is null or status="1")';
						// echo $sql;
						$remain = mysqli_fetch_assoc(execute_query($sql));

						$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and (project_status_1!="8" or project_status_1!="11" ) and (status!="5" or status="0" or status is null or status="1") and financial_go_date>="' . date("Y-m-01") . '" and financial_go_date<="' . date("Y-m-t") . '"';
						// echo $sql;
						$current_month_project_go = mysqli_fetch_assoc(execute_query($sql));


						$tot_allotted += $allotted;
						$tot_remain['c'] += $remain['c'];
						$tot_remain['amount'] += $remain['amount'];
						$tot_current_month_project['c'] += $current_month_project_go['c'];
						$tot_current_month_project['amount'] += $current_month_project_go['amount'];
						$current_month_project_go['amount'] = $current_month_project_go['amount'] == '' ? 0 : $current_month_project_go['amount'];
						$remain['amount'] = $remain['amount'] == '' ? 0 : $remain['amount'];

						$html .= '<tr>
							<td>' . $i++ . '</td>
							<td><a href="sanction_cost.php?id=' . $row_dep['sno'] . '" target="_blank">' . $row_dep['department_name_hindi'] . '</a></td>
							<td>' . $remain['c'] . '</td>
							<td>' . round($remain['amount'], 2) . '</td>
							<td>' . $current_month_project_go['c'] . '</td>
							<td>' . round($current_month_project_go['amount'], 2) . '</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							
							</tr>';
					}
					$html .= '
						</tbody>
						<tfoot><tr>
						<th>&nbsp;</th>
						<th>Total</th>
						
						<th>' . $tot_remain['c'] . '</th>
						<th>' . round($tot_remain['amount'], 2) . '</th>
						<th>' . $tot_current_month_project['c'] . '</th>
						<th>' . round($tot_current_month_project['amount'], 2) . '</th>
						
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						
						</tr>';

					$html .= '</tfoot>
										</table>';

					echo $html;
					?>
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
	function alternate_value(id) {
		var alternate = prompt("Please enter product id to merge with this id.", "");
		if (!alternate) {
			alert("Can not merge without product id.");
			return false;
		}
		else {
			window.open("master_division.php?mid=" + id + "&alt=" + alternate, '_self');
			return true;
		}
	}

	$('select[multiple]').multiselect();
</script>


<?php
page_footer_end();
?>