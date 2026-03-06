<?php
include ("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i = 1;
$msg = '';
?>

<style>
    .bold-text {
        font-weight: bold;
    }
</style>

<?php //echo $_SERVER['PHP_SELF'];   ?>
<?php //echo $_SESSION['unit_name'];   ?>

<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
	<div class="card card-body">
		<div class="row d-flex my-auto">

			<table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">
				<tr>
					<th width="5%"></th>
					<th width="10%"></th>
					<th>प्रखण्ड </th>
					<th width="15%"><select class="form-control" name="division_id" id="division_id"
							tabindex="<?php echo $tab++; ?>">
							<option value="">--- Select ---</option>
							<?php
							$query = 'select * from uprnss_division order by division_name ASC';
							$run = mysqli_query($db, $query);
							while ($data = mysqli_fetch_array($run)) {
								echo '<option value="' . $data['s_no'] . '" ';
								if (isset($_POST['division_id'])) {
									if ($_POST['division_id'] == $data['s_no']) {
										echo ' selected="Selected"';
									}
								}
								echo '>' . $data['division_name'] . '</option>';
							}
							?>
						</select>
					</th>
					<th width="10%"></th>
					<th>माह</th>
					<th width="15%">
						<select name="month" id="month" class="form-control" value="">
							<option value="">Select-- </option>
							<option value="January">January</option>
							<option value="February">February</option>
							<option value="March">March</option>
							<option value="April">April</option>
							<option value="May">May</option>
							<option value="June">June</option>
							<option value="July">July</option>
							<option value="August">August</option>
							<option value="September">September</option>
							<option value="October">October</option>
							<option value="November">November</option>
							<option value="December">December</option>
						</select>
					</th>
					<th width="15%"></th>
				</tr>
			</table>
			<div class="col-md-12 text-center mt-3">
				<button type="submit" name="search" class="btn btn-primary">Search</button>
				<input type="hidden" id="id" name="id" value="1">
			</div>
		</div>
	</div>
</form>
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h4 class="card-title text-center">प्रखण्डों के अभिलेखों में दर्शित बी०आर०जी०एफ० की अवशेष धनराशि का
					विवरण </h4></br>
			</div>
			<div class="card-body">
				<table class="table table-striped table-bordered table-hover">
				<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							<th>SNo.</th>
							<th>प्रखण्ड </th>
							<th>विभाग का नाम
								<hr>परियोजना का नाम
							</th>
							<th>परियोजना की अवशेष धनराशि</th>
							<th>बैंक का नाम</th>
							<th>खाते का प्रकार </th>
							<th>अर्जित ब्याज की धनराशि</th>
							<th>कुल धनराशि</th>
							<th>दर्शित अवधि (प्रखण्ड स्तर पर किस दिनाक से लंबित है )</th>
							<th>अभियुक्ति</th>
						</tr>
						<tr>
							<?php
							for
							($i = 1; $i <= 10; $i++) {
								echo '<th>' . $i . '</th>';
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						if($_SESSION['usertype']==1){
							$sql = 'SELECT `invoice_prarup_new_10`.`division_id`, `invoice_prarup_new_10`.`entry_date`, `trans_prarup_new_10`.`project_id`,`trans_prarup_new_10`.`avsheshrashi`, `trans_prarup_new_10`.`bankname`, `trans_prarup_new_10`.`accname`,`trans_prarup_new_10`.`utrdate`, `trans_prarup_new_10`.`toteldhanrashi`, `trans_prarup_new_10`.`dersitavadhi`, `trans_prarup_new_10`.`remark`  FROM `trans_prarup_new_10` LEFT JOIN `invoice_prarup_new_10` ON `invoice_prarup_new_10`.`sno` = `trans_prarup_new_10`.`invoice_id`where invoice_prarup_new_10.created_by = "'.$_SESSION['username'].'"';
								if(isset($_POST['search'])){
									if($_POST['division_id']!=''){
										$sql .= ' and invoice_prarup_new_10.division_id="'.$_POST['division_id'].'"';
									}
									if($_POST['month']!=''){
										$sql .= ' and trans_prarup_new_10.month="'.$_POST['month'].'"';
									}
								}
						
							$total_avsheshrashi = 0;
							$total_bankname = 0;
							$total_accname = 0;
							$total_utrdate = 0;
							$total_toteldhanrashi = 0;
							$total_dersitavadhi = 0;
							$total_remark = 0;

							$result_prarup_new_10 = execute_query($sql);
							while ($row_prarup_new_10 = mysqli_fetch_assoc($result_prarup_new_10)) {
								$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_new_10['project_id'] . '")';
								$row_project = mysqli_fetch_assoc(execute_query($sql));
								$row_project = execute_query($sql);
								if (mysqli_num_rows($row_project) != 0) {
									$row_project = mysqli_fetch_assoc($row_project);
								} else {
									unset($row_project);
									$row_project['project_name_hindi'] = '';
								}
								$sql = 'select * from uprnss_department_name where sno="' . $row_project['department_id'] . '"';
								$row_dep = mysqli_fetch_assoc(execute_query($sql));
								$row_dep = execute_query($sql);
								if (mysqli_num_rows($row_dep) != 0) {
									$row_dep = mysqli_fetch_assoc($row_dep);
								} else {
									unset($row_dep);
									$row_dep['department_name_hindi'] = '';
								}

								$sql = 'select * from uprnss_division where s_no="' . $row_prarup_new_10['division_id'] . '"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if (mysqli_num_rows($row_div) != 0) {
									$row_div = mysqli_fetch_assoc($row_div);
								} else {
									unset($row_div);
									$row_div['division_name'] = '';
								}

								$total_avsheshrashi += floatval($row_prarup_new_10['avsheshrashi']);
								$total_bankname += floatval($row_prarup_new_10['bankname']);
								$total_accname += floatval($row_prarup_new_10['accname']);
								$total_utrdate += floatval($row_prarup_new_10['utrdate']);
								$total_toteldhanrashi += floatval($row_prarup_new_10['toteldhanrashi']);
								$total_dersitavadhi += floatval($row_prarup_new_10['dersitavadhi']);
								$total_remark += floatval($row_prarup_new_10['remark']);

								echo '<tr>
								<td>' . $i++ . '</td>
								<td>' . $row_div['division_name'] . '</td>
								<td>' . $row_dep['department_name_hindi'] . '<hr>' . $row_project['project_name_hindi'] . '</td>
								<td>' . $row_prarup_new_10['avsheshrashi'] . '</td>
								<td>' . $row_prarup_new_10['bankname'] . '</td>
								<td>' . $row_prarup_new_10['accname'] . '</td>
								<td>' . $row_prarup_new_10['utrdate'] . '</td>
								<td>' . $row_prarup_new_10['toteldhanrashi'] . '</td>
								<td>' . $row_prarup_new_10['dersitavadhi'] . '</td>
								<td>' . $row_prarup_new_10['remark'] . '</td>
								</tr>';
							}
						}
						else{
							$sql = 'SELECT `invoice_prarup_new_10`.`division_id`, `invoice_prarup_new_10`.`entry_date`, `trans_prarup_new_10`.`project_id`,`trans_prarup_new_10`.`avsheshrashi`, `trans_prarup_new_10`.`bankname`, `trans_prarup_new_10`.`accname`,`trans_prarup_new_10`.`utrdate`, `trans_prarup_new_10`.`toteldhanrashi`, `trans_prarup_new_10`.`dersitavadhi`, `trans_prarup_new_10`.`remark`  FROM `trans_prarup_new_10` LEFT JOIN `invoice_prarup_new_10` ON `invoice_prarup_new_10`.`sno` = `trans_prarup_new_10`.`invoice_id` WHERE 1=1';
								if(isset($_POST['search'])){
									if($_POST['division_id']!=''){
										$sql .= ' and invoice_prarup_new_10.division_id="'.$_POST['division_id'].'"';
									}
									if($_POST['month']!=''){
										$sql .= ' and trans_prarup_new_10.month="'.$_POST['month'].'"';
									}
								}
						
						
							$total_avsheshrashi = 0;
							$total_bankname = 0;
							$total_accname = 0;
							$total_utrdate = 0;
							$total_toteldhanrashi = 0;
							$total_dersitavadhi = 0;
							$total_remark = 0;

							$result_prarup_new_10 = execute_query($sql);
							while ($row_prarup_new_10 = mysqli_fetch_assoc($result_prarup_new_10)) {
								$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_new_10['project_id'] . '")';
								$row_project = mysqli_fetch_assoc(execute_query($sql));
								$row_project = execute_query($sql);
								if (mysqli_num_rows($row_project) != 0) {
									$row_project = mysqli_fetch_assoc($row_project);
								} else {
									unset($row_project);
									$row_project['project_name_hindi'] = '';
								}
								$sql = 'select * from uprnss_department_name where sno="' . $row_project['department_id'] . '"';
								$row_dep = mysqli_fetch_assoc(execute_query($sql));
								$row_dep = execute_query($sql);
								if (mysqli_num_rows($row_dep) != 0) {
									$row_dep = mysqli_fetch_assoc($row_dep);
								} else {
									unset($row_dep);
									$row_dep['department_name_hindi'] = '';
								}

								$sql = 'select * from uprnss_division where s_no="' . $row_prarup_new_10['division_id'] . '"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if (mysqli_num_rows($row_div) != 0) {
									$row_div = mysqli_fetch_assoc($row_div);
								} else {
									unset($row_div);
									$row_div['division_name'] = '';
								}

								$total_avsheshrashi += floatval($row_prarup_new_10['avsheshrashi']);
								$total_bankname += floatval($row_prarup_new_10['bankname']);
								$total_accname += floatval($row_prarup_new_10['accname']);
								$total_utrdate += floatval($row_prarup_new_10['utrdate']);
								$total_toteldhanrashi += floatval($row_prarup_new_10['toteldhanrashi']);
								$total_dersitavadhi += floatval($row_prarup_new_10['dersitavadhi']);
								$total_remark += floatval($row_prarup_new_10['remark']);

								echo '<tr>
								<td>' . $i++ . '</td>
								<td>' . $row_div['division_name'] . '</td>
								<td>' . $row_dep['department_name_hindi'] . '<hr>' . $row_project['project_name_hindi'] . '</td>
								<td>' . $row_prarup_new_10['avsheshrashi'] . '</td>
								<td>' . $row_prarup_new_10['bankname'] . '</td>
								<td>' . $row_prarup_new_10['accname'] . '</td>
								<td>' . $row_prarup_new_10['utrdate'] . '</td>
								<td>' . $row_prarup_new_10['toteldhanrashi'] . '</td>
								<td>' . $row_prarup_new_10['dersitavadhi'] . '</td>
								<td>' . $row_prarup_new_10['remark'] . '</td>
								</tr>';
							}
						}
						?>
					</tbody>
					<tfoot>
						<th class="bold-text" colspan="3"> योग </th>
						<td class="bold-text">
							<?php echo $total_avsheshrashi; ?>
						</td>
						<td 
						</td>
						<td 
						</td>
						<td class="bold-text">
							<?php echo $total_utrdate; ?>
						</td>
						<td class="bold-text">
							<?php echo $total_toteldhanrashi; ?>
						</td>
						<td class="bold-text">
							<?php echo $total_dersitavadhi; ?>
						</td>

					</tfoot>
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

	$('select[multiple]').multiselect();
	$(document).ready(function () {

		var t = $('#general_stat_table').DataTable({
			// paging: false
		});


	});
</script>


<?php
page_footer_end();
?>