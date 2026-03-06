<?php
include ("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i = 1;
$msg = '';

if (isset($_POST['submit'])) {
	if ($_POST['project_id_1'] != "") {
		$sql = 'insert into invoice_prarup_new_4 (entry_date, total_trans, division_id, created_by, creation_time) values("' . date("Y-m-d") . '", "' . $_POST['prarup_new_4_id'] . '","' . $_POST['division_id'] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '");';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {

			$inv_id = mysqli_insert_id($db);

			for ($i = 1; $i <= $_POST['prarup_new_4_id']; $i++) {
				if ($_POST['project_id_' . $i] != "") {
					$sql = 'insert into trans_prarup_new_4 (`invoice_id`, `project_id`, `praptdhanrashi`, `vyadhanrashi`, `avsheshdhanrashi`, `bankdebitdate`, `utrnum`, `agrimsamayojan`, `ayakarsamayojan`, `gsttds`, `totel`, `condition`, created_by, creation_time) values ("' . $inv_id . '", "' . $_POST['project_id_' . $i] . '", "' . $_POST['praptdhanrashi_' . $i] . '", "' . $_POST['vyadhanrashi_' . $i] . '", "' . $_POST['avsheshdhanrashi_' . $i] . '", "' . $_POST['bankdebitdate_' . $i] . '", "' . $_POST['utrnum_' . $i] . '", "' . $_POST['agrimsamayojan_' . $i] . '", "' . $_POST['ayakarsamayojan_' . $i] . '", "' . $_POST['gsttds_' . $i] . '", "' . $_POST['totel_' . $i] . '", "' . $_POST['condition_' . $i] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '");';
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

	$sql = 'delete from invoice_prarup_new_4 where sno="' . $_GET['delid'] . '"';
	execute_query($sql);
	$sql = 'delete from trans_prarup_new_4 where invoice_id="' . $_GET['delid'] . '"';
	execute_query($sql);
	if (mysqli_error($db)) {
		$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
	} else {
		$msg .= '<div class="alert alert-warning">Data Delete</div>';
	}

}

?>

<style>
	.bold-text {
		font-weight: bold;
	}
</style>

<?php //echo $_SERVER['PHP_SELF'];?>
<?php //echo $_SESSION['unit_name'];?>
<div id="container" class="no-print">
<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					
					<table width="100%" class="table table-striped table-hover rounded text-right" style="margin:0px; padding:0px;">
						<tr >
							<th width="5%"></th>
							<th>परियोजना का नाम  </th>							
							<th width="15%">
								<select class="form-control" name="project_id" id="project_id" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
										$query = '(SELECT sno, project_name_hindi, Project_name, status FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
										// echo $query;
										$run = mysqli_query($db,$query);
										$a=1;
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_id_'.$a])){
												if($_POST['project_id_'.$a]==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_name_hindi']).'</option>';
										}
									?>
								</select>
							</th>
							<th width="10%"></th>
							<th>प्रखण्ड </th>
							<th width="15%"><select class="form-control" name="division_id" id="division_id" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from uprnss_division order by division_name ASC';
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['s_no'].'" ';
										if(isset($_POST['division_id'])){
											if($_POST['division_id']==$data['s_no']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.$data['division_name'].'</option>';
									}
									?>
								</select>
							</th>
							<th width="10%"></th>
							<th>माह</th>
							<th width="15%">
								<select name="month" id="month" class="form-control"  value="">
									<option value="">--Select--</option>
									<option value="January" >January</option>
									<option value="February" >February</option>
									<option value="March" >March</option>
									<option value="April" >April</option>
									<option value="May" >May</option>
									<option value="June" >June</option>
									<option value="July" >July</option>
									<option value="August" >August</option>
									<option value="September" >September</option>
									<option value="October" >October</option>
									<option value="November" >November</option>
									<option value="December" >December</option>
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
					<h4 class="card-title text-center">निर्माण कार्य की अवशेष धनराशि जो मुख्यालय को प्रेषित की गई विवरण
						वर्ष
						2023-24 </h4></br>
				</div>
				<div class="card-body">
					<table class="table table-striped table-bordered table-hover">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th></th>
								<th>प्रखण्ड का नाम </th>
								<th>विभाग का नाम
									<hr>परियोजना का नाम
								</th>
								<th>परियोजना पर प्राप्त धनराशि</th>
								<th>परियोजना पर व्यय धनराशि</th>
								<th>अवशेष धनराशि</th>
								<th>बैंक से डेबिट होने का दिनांक</th>
								<th>यू०टी०आर० नम्बर</th>
								<th>अग्रिम सेंटेज धनराशि (समायोजन)</th>
								<th>आयकर (समायोजन)</th>
								<th>जी०एस०टी० टी०डी०एस० (समायोजन)</th>
								<th>कुल धनराशि</th>
								<th>परियोजना की स्थिति</th>
							</tr>
							<tr>
									<?php
									for($i=1;$i<=14;$i++){
										echo '<th>'.$i.'</th>';
									}
									?>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($_SESSION['usertype'] == '1') {
								$sql = 'SELECT `invoice_prarup_new_4`.`sno`, `invoice_prarup_new_4`.`division_id`, `invoice_prarup_new_4`.`entry_date`, `trans_prarup_new_4`.`project_id`, `trans_prarup_new_4`.`praptdhanrashi`, `trans_prarup_new_4`.`vyadhanrashi`, `trans_prarup_new_4`.`avsheshdhanrashi`, `trans_prarup_new_4`.`bankdebitdate`, `trans_prarup_new_4`.`utrnum`, `trans_prarup_new_4`.`agrimsamayojan`, `trans_prarup_new_4`.`ayakarsamayojan`, `trans_prarup_new_4`.`gsttds`, `trans_prarup_new_4`.`totel`, `trans_prarup_new_4`.`condition` FROM `trans_prarup_new_4` LEFT JOIN `invoice_prarup_new_4` ON `invoice_prarup_new_4`.`sno` = `trans_prarup_new_4`.`invoice_id`  where invoice_prarup_new_4.created_by = "' . $_SESSION['username'] . '"';

								$result_prarup_new_4 = execute_query($sql);
								$i = 1;
								$total_praptdhanrashi = 0;
								$total_vyadhanrashi = 0;
								$total_avsheshdhanrashi = 0;
								$total_bankdebitdate = 0;
								$total_utrnum = 0;
								$total_agrimsamayojan = 0;
								$total_ayakarsamayojan = 0;
								$total_gsttds = 0;
								$total_totel = 0;
								$total_condition = 0;
								$a = 1;
								while ($row_prarup_new_4 = mysqli_fetch_assoc($result_prarup_new_4)) {
									$sql = 'select * from uprnss_division where s_no="' . $row_prarup_new_4['division_id'] . '"';
									$row_div = mysqli_fetch_assoc(execute_query($sql));
									$row_div = execute_query($sql);
									if (mysqli_num_rows($row_div) != 0) {
										$row_div = mysqli_fetch_assoc($row_div);
									} else {
										unset($row_div);
										$row_div['division_name'] = '';
									}

									$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_new_4['project_id'] . '")';
									$row_project = mysqli_fetch_assoc(execute_query($sql));
									$row_project = execute_query($sql);
									if (mysqli_num_rows($row_project) != 0) {
										$row_project = mysqli_fetch_assoc($row_project);
									} else {
										unset($row_project);
										$row_project['project_name_hindi'] = '';
									}
									// $sql = 'select * from uprnss_department_name where sno="' . $row_project['department_id'] . '"';
									// $row_dep = mysqli_fetch_assoc(execute_query($sql));
									// $row_dep = execute_query($sql);
									// if (mysqli_num_rows($row_dep) != 0) {
									// 	$row_dep = mysqli_fetch_assoc($row_dep);
									// } else {
									// 	unset($row_dep);
									// 	$row_dep['department_name_hindi'] = '';
									// }
									$total_praptdhanrashi += floatval($row_prarup_new_4['praptdhanrashi']);
									$total_vyadhanrashi += floatval($row_prarup_new_4['vyadhanrashi']);
									$total_avsheshdhanrashi += floatval($row_prarup_new_4['avsheshdhanrashi']);
									$total_bankdebitdate += floatval($row_prarup_new_4['bankdebitdate']);
									$total_utrnum += floatval($row_prarup_new_4['utrnum']);
									$total_agrimsamayojan += floatval($row_prarup_new_4['agrimsamayojan']);
									$total_ayakarsamayojan += floatval($row_prarup_new_4['ayakarsamayojan']);
									$total_gsttds += floatval($row_prarup_new_4['gsttds']);
									$total_totel += floatval($row_prarup_new_4['totel']);


									echo '<tr>
											<td>' . $a++ . '</td>
											<td>' . $row_div['division_name'] . '</td>
											<td>' . $row_project['project_name_hindi'] . '</td>
											<td>' . $row_prarup_new_4['praptdhanrashi'] . '</td>
											<td>' . $row_prarup_new_4['vyadhanrashi'] . '</td>
											<td>' . $row_prarup_new_4['avsheshdhanrashi'] . '</td>
											<td>' . $row_prarup_new_4['bankdebitdate'] . '</td>
											<td>' . $row_prarup_new_4['utrnum'] . '</td>
											<td>' . $row_prarup_new_4['agrimsamayojan'] . '</td>
											<td>' . $row_prarup_new_4['ayakarsamayojan'] . '</td>
											<td>' . $row_prarup_new_4['gsttds'] . '</td
											><td>' . $row_prarup_new_4['totel'] . '</td>
											<td>' . $row_prarup_new_4['condition'] . '</td>
										</tr>';
								}
							} else {
								$sql = 'SELECT `invoice_prarup_new_4`.`sno`,`invoice_prarup_new_4`.`division_id`, `invoice_prarup_new_4`.`entry_date`, `trans_prarup_new_4`.`project_id`, `trans_prarup_new_4`.`praptdhanrashi`, `trans_prarup_new_4`.`vyadhanrashi`, `trans_prarup_new_4`.`avsheshdhanrashi`, `trans_prarup_new_4`.`bankdebitdate`, `trans_prarup_new_4`.`utrnum`, `trans_prarup_new_4`.`agrimsamayojan`, `trans_prarup_new_4`.`ayakarsamayojan`, `trans_prarup_new_4`.`gsttds`, `trans_prarup_new_4`.`totel`, `trans_prarup_new_4`.`condition` FROM `trans_prarup_new_4` LEFT JOIN `invoice_prarup_new_4` ON `invoice_prarup_new_4`.`sno` = `trans_prarup_new_4`.`invoice_id` WHERE 1=1';

								$result_prarup_new_4 = execute_query($sql);
								$i = 1;

								$total_praptdhanrashi = 0;
								$total_vyadhanrashi = 0;
								$total_avsheshdhanrashi = 0;
								$total_bankdebitdate = 0;
								$total_utrnum = 0;
								$total_agrimsamayojan = 0;
								$total_ayakarsamayojan = 0;
								$total_gsttds = 0;
								$total_totel = 0;
								$total_condition = 0;


								while ($row_prarup_new_4 = mysqli_fetch_assoc($result_prarup_new_4)) {
									$sql = 'select * from uprnss_division where s_no="' . $row_prarup_new_4['division_id'] . '"';
									$row_div = mysqli_fetch_assoc(execute_query($sql));
									$row_div = execute_query($sql);
									if (mysqli_num_rows($row_div) != 0) {
										$row_div = mysqli_fetch_assoc($row_div);
									} else {
										unset($row_div);
										$row_div['division_name'] = '';
									}

									$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_new_4['project_id'] . '")';
									$row_project = mysqli_fetch_assoc(execute_query($sql));
									$row_project = execute_query($sql);
									if (mysqli_num_rows($row_project) != 0) {
										$row_project = mysqli_fetch_assoc($row_project);
									} else {
										unset($row_project);
										$row_project['project_name_hindi'] = '';
									}
									// $sql = 'select * from uprnss_department_name where sno="' . $row_project['department_id'] . '"';
									// $row_dep = mysqli_fetch_assoc(execute_query($sql));
									// $row_dep = execute_query($sql);
									// if (mysqli_num_rows($row_dep) != 0) {
									// 	$row_dep = mysqli_fetch_assoc($row_dep);
									// } else {
									// 	unset($row_dep);
									// 	$row_dep['department_name_hindi'] = '';
									// }

									$total_praptdhanrashi += floatval($row_prarup_new_4['praptdhanrashi']);
									$total_vyadhanrashi += floatval($row_prarup_new_4['vyadhanrashi']);
									$total_avsheshdhanrashi += floatval($row_prarup_new_4['avsheshdhanrashi']);
									$total_bankdebitdate += floatval($row_prarup_new_4['bankdebitdate']);
									$total_utrnum += floatval($row_prarup_new_4['utrnum']);
									$total_agrimsamayojan += floatval($row_prarup_new_4['agrimsamayojan']);
									$total_ayakarsamayojan += floatval($row_prarup_new_4['ayakarsamayojan']);
									$total_gsttds += floatval($row_prarup_new_4['gsttds']);
									$total_totel += floatval($row_prarup_new_4['totel']);

									echo '<tr>
											<td>' . $i++ . '</td>
											<td>' . $row_div['division_name'] . '</td>
											<td>' . $row_project['project_name_hindi'] . '</td>
											<td>' . $row_prarup_new_4['praptdhanrashi'] . '</td>
											<td>' . $row_prarup_new_4['vyadhanrashi'] . '</td>
											<td>' . $row_prarup_new_4['avsheshdhanrashi'] . '</td>
											<td>' . $row_prarup_new_4['bankdebitdate'] . '</td>
											<td>' . $row_prarup_new_4['utrnum'] . '</td>
											<td>' . $row_prarup_new_4['agrimsamayojan'] . '</td>
											<td>' . $row_prarup_new_4['ayakarsamayojan'] . '</td>
											<td>' . $row_prarup_new_4['gsttds'] . '</td
											><td>' . $row_prarup_new_4['totel'] . '</td>
											<td>' . $row_prarup_new_4['condition'] . '</td>
										</tr>';
								}
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td class="bold-text" colspan="3"> योग </td>
								<td class="bold-text">
									<?php echo $total_praptdhanrashi; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_vyadhanrashi; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_avsheshdhanrashi; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_bankdebitdate; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_utrnum; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_agrimsamayojan; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_ayakarsamayojan; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_gsttds; ?>
								</td>
								<td class="bold-text">
									<?php echo $total_totel; ?>
								</td>

							</tr>
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


		function addCalc(line_serial) {
			var id = parseFloat($("#prarup_new_4_id").val());
			var tot = 0;
			for (i = 1; i <= id; i++) {
				var amt = $('#p_receive_amount_' + i).val();
				amt = parseFloat(amt);
				if (!amt) {
					amt = 0;
				}
				tot += amt;
				console.log("AMT : " + amt + " : ID: " + i);
			}
			$("#tot_receive_amount").val(tot);


		}

		$('select[multiple]').multiselect();
	</script>


	<?php
	page_footer_end();
	?>