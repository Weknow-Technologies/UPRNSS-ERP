<?php
include ("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i = 1;
$msg = '';

if (isset($_POST['submit'])) {
	if ($_POST['avsheshdate_1'] != "") {
		$sql = 'insert into invoice_prarup_new_6 (entry_date, total_trans, division_id, created_by, creation_time) values("' . date("Y-m-d") . '", "' . $_POST['prarup_new_6_id'] . '","' . $_POST['division_id'] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
		execute_query($sql);

		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {

			$inv_id = mysqli_insert_id($db);

			for ($i = 1; $i <= $_POST['prarup_new_6_id']; $i++) {
				if ($_POST['avsheshdate_' . $i] != "") {
					$sql = 'insert into trans_prarup_new_6 (`invoice_id`, `month`,`avsheshdate`, `totelpayment`, `tenderfee`, `tenderfeemonth`, `utrnumber`,  `utrdate`, `maintenderfee`, `avsheshtenderfee`, `otherremark`, created_by, creation_time) values ("' . $inv_id . '", "' . $_POST['month_' . $i] . '","' . $_POST['avsheshdate_' . $i] . '", "' . $_POST['totelpayment_' . $i] . '", "' . $_POST['tenderfee_' . $i] . '", "' . $_POST['tenderfeemonth_' . $i] . '", "' . $_POST['utrdate_' . $i] . '", "' . $_POST['utrnumber_' . $i] . '", "' . $_POST['maintenderfee_' . $i] . '", "' . $_POST['avsheshtenderfee_' . $i] . '", "' . $_POST['otherremark_' . $i] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '");';
					execute_query($sql);
					if (mysqli_error($db)) {
						$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
					}
				}
			}
			if ($msg == "") {
				$msg = "<p class='alert alert-success'>Data stored all</p>";
			}
		}
	} else {
		echo '<script>alert("Please Fill Form properly!");</script>';
	}
}
if (isset($_GET['delid'])) {

	$sql = 'delete from invoice_prarup_new_6 where sno="' . $_GET['delid'] . '"';
	execute_query($sql);
	if (mysqli_error($db)) {
		$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';

	} else {
		$sql = 'delete from trans_prarup_new_6 where invoice_id="' . $_GET['delid'] . '"';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
	}


}

?>

<style>
	.bold-text {
		font-weight: bold;
	}
</style>

<?php //echo $_SERVER['PHP_SELF'];     ?>
<?php //echo $_SESSION['unit_name'];     ?>

<div id="container" class="no-print">
	<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">
			<div class="row d-flex my-auto">

				<table width="100%" class="table table-striped table-hover rounded"
					style="margin:0px; padding:0px;">
					<tr>
						<th width="5%"></th>
						<th>परियोजना का नाम </th>
						<th width="15%">
							<select class="form-control" name="project_id" id="project_id"
								tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = '(SELECT sno, project_name_hindi, Project_name, status FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
								// echo $query;
								$run = mysqli_query($db, $query);
								$a = 1;
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['project_id_' . $a])) {
										if ($_POST['project_id_' . $a] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . trim($data['project_name_hindi']) . '</option>';
								}
								?>
							</select>
						</th>
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
								<option value="">--Select--</option>
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
						<th width="5%"></th>
					</tr>
				</table>
				<div class="col-md-12 text-center mt-3">
					<button type="submit" name="search" class="btn btn-primary">Search</button>
					<input type="hidden" id="id" name="id" value="1">
				</div>
			</div>
		</div>
	</form>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h4 class="card-title text-center">प्रखण्डों द्वारा अभिलेखों में दर्शित / मॉग की जा रही ग्राहक विभाग द्वारा कटौती की गई जी०एस०टी० टी०डी०एस० की धनराशि का विवरण
				</h4></br>
			</div>
			<div class="card-body">

				<table class="table table-striped table-bordered table-hover">
				<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							<th>SNo.</th>
							<th>प्रखण्ड</th>
							<th>वित्तीय वर्ष </th>
							<th>विभाग का नाम </th>
							<th>परियोजना का नाम </th>
							<th>ग्राहक विभाग का जी०एस०टिन नम्बर</th>
							<th>मुख्यालय पर प्राप्त धनराशि पर जी० एस० टी० टी०डी०एस० की धनराशि</th>
							<th>प्रखण्ड पर प्राप्त धनराशि पर जी० एस० टी० टी०डी०एस० की धनराशि </th>
							<th>मुख्यालय द्वारा प्रखण्ड को प्रेषित जी० एस० टी० टी०डी०एस० की धनराशि</th>
							<th>अंतर</th>
							<th>परियोजना पूर्ण /अपूर्ण </th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ($_SESSION['usertype'] == '1') {
							$sql = 'SELECT `invoice_prarup_7`.`division_id`, `invoice_prarup_7`.`entry_date`, `trans_prarup_7`.`vibhagname`,`trans_prarup_7`.`year`, `trans_prarup_7`.`project_name`,`trans_prarup_7`.`grahakgstnum`, `trans_prarup_7`.`centagegsttds`, `trans_prarup_7`.`prakhandgsttdsdhanrashi`, `trans_prarup_7`.`mukhyalayaprakhandgsttdsdhanrashi`, `trans_prarup_7`.`difference`, `trans_prarup_7`.`projectstatus`  FROM `trans_prarup_7` LEFT JOIN `invoice_prarup_7` ON `invoice_prarup_7`.`sno` = `trans_prarup_7`.`invoice_id`where invoice_prarup_7.created_by = "'.$_SESSION['username'].'"';
							if (isset($_POST['search'])) {
								if ($_POST['division_id'] != '') {
									$sql .= ' and invoice_prarup_7.division_id="' . $_POST['division_id'] . '"';
								}
								if ($_POST['month'] != '') {
									$sql .= ' and trans_prarup_7.month="' . $_POST['month'] . '"';
								}
							}
							$result_prarup_3 = execute_query($sql);

							$total_grahakgstnum = 0;
							$total_centagegsttds = 0;
							$total_prakhandgsttdsdhanrashi = 0;
							$total_mukhyalayaprakhandgsttdsdhanrashi = 0;
							$total_difference = 0;
							$total_projectstatus = 0;

							$result_prarup_7 = execute_query($sql);
							while ($row_prarup_7 = mysqli_fetch_assoc($result_prarup_7)) {
								$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_7['project_name'] . '")';
								$row_project = mysqli_fetch_assoc(execute_query($sql));
								$row_project = execute_query($sql);
								if (mysqli_num_rows($row_project) != 0) {
									$row_project = mysqli_fetch_assoc($row_project);
								} else {
									unset($row_project);
									$row_project['project_name_hindi'] = '';
								}
								$sql1 = 'select * from uprnss_department_name where sno="' . $row_prarup_7['vibhagname'] . '"';
								$row_dep = execute_query($sql1);
								if (mysqli_num_rows($row_dep) != 0) {
									$row_dep = mysqli_fetch_assoc($row_dep);
								} else {
									unset($row_dep);
									$row_dep['department_name_hindi'] = '';
								}

								$sql1 = 'select * from uprnss_division where s_no="' . $row_prarup_7['division_id'] . '"';
								$row_div = execute_query($sql1);
								if (mysqli_num_rows($row_div) != 0) {
									$row_div = mysqli_fetch_assoc($row_div);
								} else {
									unset($row_div);
									$row_div['division_name'] = '';
								}

								$total_grahakgstnum += floatval($row_prarup_7['grahakgstnum']);
								$total_centagegsttds += floatval($row_prarup_7['centagegsttds']);
								$total_prakhandgsttdsdhanrashi += floatval($row_prarup_7['prakhandgsttdsdhanrashi']);
								$total_mukhyalayaprakhandgsttdsdhanrashi += floatval($row_prarup_7['mukhyalayaprakhandgsttdsdhanrashi']);
								$total_difference += floatval($row_prarup_7['difference']);
								$total_projectstatus += floatval($row_prarup_7['projectstatus']);

								echo '<tr>
								<td>' . $i++ . '</td>
								<td>' . $row_div['division_name'] . '</td>
								<td>' . $row_prarup_7['year'] . '</td>
								<td>' . $row_dep['department_name_hindi'] . '</td>
								<td>' . $row_project['project_name_hindi'] . '</td>
								<td>' . $row_prarup_7['grahakgstnum'] . '</td>
								<td>' . $row_prarup_7['centagegsttds'] . '</td>
								<td>' . $row_prarup_7['prakhandgsttdsdhanrashi'] . '</td>
								<td>' . $row_prarup_7['mukhyalayaprakhandgsttdsdhanrashi'] . '</td>
								<td>' . $row_prarup_7['difference'] . '</td>
								<td>' . $row_prarup_7['projectstatus'] . '</td></tr>';
							}
						} else {
							$sql = 'SELECT `invoice_prarup_7`.`division_id`, `invoice_prarup_7`.`entry_date`, `trans_prarup_7`.`vibhagname`,`trans_prarup_7`.`year`, `trans_prarup_7`.`project_name`,`trans_prarup_7`.`grahakgstnum`, `trans_prarup_7`.`centagegsttds`, `trans_prarup_7`.`prakhandgsttdsdhanrashi`, `trans_prarup_7`.`mukhyalayaprakhandgsttdsdhanrashi`, `trans_prarup_7`.`difference`, `trans_prarup_7`.`projectstatus` FROM `trans_prarup_7` LEFT JOIN `invoice_prarup_7` ON `invoice_prarup_7`.`sno` = `trans_prarup_7`.`invoice_id`where 1=1';
							if (isset($_POST['search'])) {
								if ($_POST['division_id'] != '') {
									$sql .= ' and invoice_prarup_7.division_id="' . $_POST['division_id'] . '"';
								}
								if ($_POST['month'] != '') {
									$sql .= ' and trans_prarup_7.month="' . $_POST['month'] . '"';
								}
							}
							$result_prarup_7 = execute_query($sql);

							$total_grahakgstnum = 0;
							$total_centagegsttds = 0;
							$total_prakhandgsttdsdhanrashi = 0;
							$total_mukhyalayaprakhandgsttdsdhanrashi = 0;
							$total_difference = 0;
							$total_projectstatus = 0;

							$result_prarup_7 = execute_query($sql);
							while ($row_prarup_7 = mysqli_fetch_assoc($result_prarup_7)) {
								$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_7['project_name'] . '")';
								$row_project = mysqli_fetch_assoc(execute_query($sql));
								$row_project = execute_query($sql);
								if (mysqli_num_rows($row_project) != 0) {
									$row_project = mysqli_fetch_assoc($row_project);
								} else {
									unset($row_project);
									$row_project['project_name_hindi'] = '';
								}
								$sql1 = 'select * from uprnss_department_name where sno="' . $row_prarup_7['vibhagname'] . '"';
								$row_dep = execute_query($sql1);
								if (mysqli_num_rows($row_dep) != 0) {
									$row_dep = mysqli_fetch_assoc($row_dep);
								} else {
									unset($row_dep);
									$row_dep['department_name_hindi'] = '';
								}

								$sql1 = 'select * from uprnss_division where s_no="' . $row_prarup_7['division_id'] . '"';
								$row_div = execute_query($sql1);
								if (mysqli_num_rows($row_div) != 0) {
									$row_div = mysqli_fetch_assoc($row_div);
								} else {
									unset($row_div);
									$row_div['division_name'] = '';
								}

								$total_grahakgstnum += floatval($row_prarup_7['grahakgstnum']);
								$total_centagegsttds += floatval($row_prarup_7['centagegsttds']);
								$total_prakhandgsttdsdhanrashi += floatval($row_prarup_7['prakhandgsttdsdhanrashi']);
								$total_mukhyalayaprakhandgsttdsdhanrashi += floatval($row_prarup_7['mukhyalayaprakhandgsttdsdhanrashi']);
								$total_difference += floatval($row_prarup_7['difference']);
								$total_projectstatus += floatval($row_prarup_7['projectstatus']);

								echo '<tr>
									<td>' . $i++ . '</td>
									<td>' . $row_div['division_name'] . '</td>
									<td>' . $row_prarup_7['year'] . '</td>
									<td>' . $row_dep['department_name_hindi'] . '</td>
									<td>' . $row_project['project_name_hindi'] . '</td>
									<td>' . $row_prarup_7['grahakgstnum'] . '</td>
									<td>' . $row_prarup_7['centagegsttds'] . '</td>
									<td>' . $row_prarup_7['prakhandgsttdsdhanrashi'] . '</td>
									<td>' . $row_prarup_7['mukhyalayaprakhandgsttdsdhanrashi'] . '</td>
									<td>' . $row_prarup_7['difference'] . '</td>
									<td>' . $row_prarup_7['projectstatus'] . '</td></tr>';
							}
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<td class="bold-text" colspan="6"> योग </td>

							<td class="bold-text">
								<?php echo $total_centagegsttds; ?>
							</td>
							<td class="bold-text">
								<?php echo $total_prakhandgsttdsdhanrashi; ?>
							</td>
							<td class="bold-text">
								<?php echo $total_mukhyalayaprakhandgsttdsdhanrashi; ?>
							</td>
							<td class="bold-text">
								<?php echo $total_difference; ?>
							</td>
							<td class="bold-text"> </td>
						</tr>
					</tfoot>
				</table>

				<?php
				page_footer_start();
				?>

				<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
				<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
				<script>

					$('select[multiple]').multiselect();

					$(document).ready(function () {
						/*$('#general_stat_table').DataTable({
							paging: false,
							fixedHeader: true,
							colReorder: true
							});
						});	*/


						var t = $('#general_stat_table').DataTable({
							// paging: false
						});


					});
				</script>


				<?php
				page_footer_end();
				?>