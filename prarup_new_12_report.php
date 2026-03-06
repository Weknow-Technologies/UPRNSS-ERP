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

<?php //echo $_SERVER['PHP_SELF'];  ?>
<?php //echo $_SESSION['unit_name'];  ?>

<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
			<div class="card card-body">    
        		<div class="row d-flex my-auto">    	
					
					<table width="100%" class="table table-striped table-hover rounded text-right" style="margin:0px; padding:0px;">
						<tr >
							<th> </th>							
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
							<th width="20%"></th>
							<th>माह</th>
							<th width="15%">
								<select name="month" id="month" class="form-control"  value="">
									<option value="">Select--	</option>
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
							<th width="25%"></th>
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
				<h4 class="card-title text-center">प्रखण्ड पर उपलब्ध कन्टीजेन्सी का विवरण </h4></br>
			</div>
		</div>
		<?php echo $msg; ?>
	</div>
</div>
<!-- </form> -->

<table class="table table-striped table-bordered table-hover text-right">
<thead style="position:sticky;top:0; z-index:2;">
		<tr>
			<th>SNo.</th>
			<th>प्रखण्ड</th>
			<th>विभाग का नाम
				<hr>परियोजना का नाम
			</th>
			<th>माह</th>
			<th>कन्टीजेन्सी की धनराशि</th>
			<th>मद का नाम जिस हेतु कन्टीजेन्सी से व्यय किया गया</th>
			<th>कन्टीजेन्सी से माह में व्यय</th>
			<th>कन्ट्रीजेन्सी से क्रमिक व्यय</th>
			<th>अवशेष कन्टीजेन्सी</th>
			<th>अभियुक्ति</th>
		</tr>
	</thead>
	<tbody>
		<?php

	if($_SESSION['usertype']=='1'){
		$sql = 'SELECT `invoice_prarup_new_12`.`division_id`, `invoice_prarup_new_12`.`entry_date`,`trans_prarup_new_12`.`month`,
		`trans_prarup_new_12`.`pariyojnaname`, `trans_prarup_new_12`.`contigencyrupee`, `trans_prarup_new_12`.`contigencyvya`, `trans_prarup_new_12`.`contengencymonvya`, `trans_prarup_new_12`.`kramikvya`, `trans_prarup_new_12`.`restcontigency`, `trans_prarup_new_12`.`remark` FROM `trans_prarup_new_12` LEFT JOIN `invoice_prarup_new_12` ON `invoice_prarup_new_12`.`sno` = `trans_prarup_new_12`.`invoice_id`where invoice_prarup_new_12.created_by = "'.$_SESSION['username'].'"';
		if(isset($_POST['search'])){
			if($_POST['division_id']!=''){
				$sql .= ' and invoice_prarup_new_12.division_id="'.$_POST['division_id'].'"';
			}
			if($_POST['month']!=''){
				$sql .= ' and trans_prarup_new_12.month="'.$_POST['month'].'"';
			}
		}
			$total_month = 0;
			$total_contigencyrupee = 0;
			$total_contigencyvya = 0;
			$total_contengencymonvya = 0;
			$total_kramikvya = 0;
			$total_restcontigency = 0;
			$total_remark = 0;

			$result_prarup_new_12 = execute_query($sql);
			while ($row_prarup_new_12 = mysqli_fetch_assoc($result_prarup_new_12)) {
				$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_new_12['pariyojnaname'] . '")';
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

				$sql = 'select * from uprnss_division where s_no="' . $row_prarup_new_12['division_id'] . '"';
				$row_div = execute_query($sql);
				if (mysqli_num_rows($row_div) != 0) {
					$row_div = mysqli_fetch_assoc($row_div);
				} else {
					unset($row_div);
					$row_div['division_name'] = '';
				}
				$total_month += floatval($row_prarup_new_12['month']);
				$total_contigencyrupee += floatval($row_prarup_new_12['contigencyrupee']);
				$total_contigencyvya += floatval($row_prarup_new_12['contigencyvya']);
				$total_contengencymonvya += floatval($row_prarup_new_12['contengencymonvya']);
				$total_kramikvya += floatval($row_prarup_new_12['kramikvya']);
				$total_restcontigency += floatval($row_prarup_new_12['restcontigency']);
				$total_remark += floatval($row_prarup_new_12['remark']);

				echo '<tr>
					<td>' . $i++ . '</td>
					<td>' . $row_div['division_name'] . '</td>
					<td>' . $row_dep['department_name_hindi'] . '<hr>' . $row_project['project_name_hindi'] . '</td>
					<td>' . $row_prarup_new_12['month'] . '</td>
					<td>' . $row_prarup_new_12['contigencyrupee'] . '</td>
					<td>' . $row_prarup_new_12['contigencyvya'] . '</td>
					<td>' . $row_prarup_new_12['contengencymonvya'] . '</td>
					<td>' . $row_prarup_new_12['kramikvya'] . '</td>
					<td>' . $row_prarup_new_12['restcontigency'] . '</td>
					<td>' . $row_prarup_new_12['remark'] . '</td>
					</tr>';
			}
	}
	else{
		$sql = 'SELECT `invoice_prarup_new_12`.`division_id`, `invoice_prarup_new_12`.`entry_date`, `trans_prarup_new_12`.`month`, `trans_prarup_new_12`.`pariyojnaname`, `trans_prarup_new_12`.`contigencyrupee`, `trans_prarup_new_12`.`contigencyvya`, `trans_prarup_new_12`.`contengencymonvya`, `trans_prarup_new_12`.`kramikvya`, `trans_prarup_new_12`.`restcontigency`, `trans_prarup_new_12`.`remark` FROM `trans_prarup_new_12` LEFT JOIN `invoice_prarup_new_12` ON `invoice_prarup_new_12`.`sno` = `trans_prarup_new_12`.`invoice_id`where 1=1';
		if(isset($_POST['search'])){
			if($_POST['division_id']!=''){
				$sql .= ' and invoice_prarup_new_12.division_id="'.$_POST['division_id'].'"';
			}
			if($_POST['month']!=''){
				$sql .= ' and trans_prarup_new_12.month="'.$_POST['month'].'"';
			}
		}
			$total_month = 0;
			$total_contigencyrupee = 0;
			$total_contigencyvya = 0;
			$total_contengencymonvya = 0;
			$total_kramikvya = 0;
			$total_restcontigency = 0;
			$total_remark = 0;

			$result_prarup_new_12 = execute_query($sql);
			while ($row_prarup_new_12 = mysqli_fetch_assoc($result_prarup_new_12)) {
				$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_new_12['pariyojnaname'] . '")';
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

				$sql = 'select * from uprnss_division where s_no="' . $row_prarup_new_12['division_id'] . '"';
				$row_div = execute_query($sql);
				if (mysqli_num_rows($row_div) != 0) {
					$row_div = mysqli_fetch_assoc($row_div);
				} else {
					unset($row_div);
					$row_div['division_name'] = '';
				}
				$total_month += floatval($row_prarup_new_12['month']);
				$total_contigencyrupee += floatval($row_prarup_new_12['contigencyrupee']);
				$total_contigencyvya += floatval($row_prarup_new_12['contigencyvya']);
				$total_contengencymonvya += floatval($row_prarup_new_12['contengencymonvya']);
				$total_kramikvya += floatval($row_prarup_new_12['kramikvya']);
				$total_restcontigency += floatval($row_prarup_new_12['restcontigency']);
				$total_remark += floatval($row_prarup_new_12['remark']);

				echo '<tr>
					<td>' . $i++ . '</td>
					<td>' . $row_div['division_name'] . '</td>
					<td>' . $row_dep['department_name_hindi'] . '<hr>' . $row_project['project_name_hindi'] . '</td>
					<td>' . $row_prarup_new_12['month'] . '</td>
					<td>' . $row_prarup_new_12['contigencyrupee'] . '</td>
					<td>' . $row_prarup_new_12['contigencyvya'] . '</td>
					<td>' . $row_prarup_new_12['contengencymonvya'] . '</td>
					<td>' . $row_prarup_new_12['kramikvya'] . '</td>
					<td>' . $row_prarup_new_12['restcontigency'] . '</td>
					<td>' . $row_prarup_new_12['remark'] . '</td>
					</tr>';
			}
	}
		?>
	</tbody>
	<tfoot>
		<tr>
			<th class="bold-text" colspan="3"> योग </th>
			<td class="bold-text"> </td>
			<td class="bold-text">
				<?php echo $total_contigencyrupee; ?>
			</td>
			<td class="bold-text"></td>
			<td class="bold-text">
				<?php echo $total_contengencymonvya; ?>
			</td>
			<td class="bold-text">
				<?php echo $total_kramikvya; ?>
			</td>
			<td class="bold-text">
				<?php echo $total_restcontigency; ?>
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