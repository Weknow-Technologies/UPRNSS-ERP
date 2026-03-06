<?php
include ("scripts/settings.php");

$msg = '';
$tab = 1;

page_header_start();
page_header_end();
page_sidebar();


if (isset($_POST['submit'])) {
	if ($_POST['edit_sno'] == '') {
		$sql = 'insert into master_pay_level_grade (level_name, basic_pay, pay_scale, status, created_by, creation_time) values ("' . $_POST['level_name'] . '", "' . $_POST['basic_pay'] . '", "'.$_POST['pay_scale'].'" , "0", "' . $_SESSION['usersno'] . '", "' . date("Y-m-d H:i:s") . '")';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		}
		if ($msg == '') {
			$msg .= '<p class="text text-success">Data save</p>';
			unset($_POST);
			goto postblank;
		}
	} else {
		$sql = 'update master_pay_level_grade set 
		level_name = "' . $_POST['level_name'] . '",    
		basic_pay = "' . $_POST['basic_pay'] . '", 
		pay_scale =  "'.$_POST['pay_scale'].'" ,   
		edited_by = "' . $_SESSION['usersno'] . '",  
		edition_time = "' . date("Y-m-d H:i:s") . '" 
		where sno="' . $_POST['edit_sno'] . '"';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		}
		if ($msg == '') {
			$msg .= '<p class="text text-success">Data Update</p>';
			unset($_POST);
			goto postblank;
		}
	}
} else {

	postblank:
	$_POST['level_name'] = '';
	$_POST['basic_pay'] = '';
	$_POST['pay_scale'] = '';

	$_POST['edit_sno'] = '';
}


if (isset($_GET['edit_sno'])) {
	$sql = 'select * from master_pay_level_grade where sno="' . $_GET['edit_sno'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['level_name'] = $data['level_name'];
	$_POST['basic_pay'] = $data['basic_pay'];
	$_POST['pay_scale'] = $data['pay_scale'];

}

if (isset($_GET['del'])) {
	$sql = 'delete from master_pay_level_grade where sno="' . $_GET['del'] . '"';
	execute_query($sql);

	$msg .= '<p class="text text-danger">Data Deleted.</p>';
}

?>


<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">


	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title text-center"></h4></br>
				</div>
				<?php echo $msg; ?>
				<div class="card-body">
					<div class="row" id="">
						<div class="col-md-3">
							<div class="form-group">
								<label>Pay Scale</label>
								<select name="pay_scale" id="pay_scale" class="form-control" onchange="onchangetype()">
									<option value="">--- Select ---</option>
									<option value="1" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 1 ? 'selected' : ''); ?>>5th Pay</option>
									<option value="2" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 2 ? 'selected' : ''); ?>>6th Pay</option>
									<option value="3" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 3 ? 'selected' : ''); ?>>7th Pay</option>
								</select>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form_group">
								<label>Pay level</label>
								<select class="form-control" name="level_name" id="level_name"
									tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from master_pay_level";
									$run = mysqli_query($db, $query);
									while ($data = mysqli_fetch_array($run)) {
										echo '<option value="' . $data['sno'] . '" ';
										if (isset($_POST['level_name'])) {
											if ($_POST['level_name'] == $data['sno']) {
												echo ' selected="Selected"';
											}
										}
										echo '>' . trim($data['level_name']) . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Basic Pay</label>
								<input type="text" name="basic_pay" id="basic_pay" class="form-control" placeholder=""
									value="<?php echo $_POST['basic_pay']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<!--<div class="col-md-3">
								<div class="form-group">
									<label >Level</label>
									<input type="text" name="level_name" id="level_name" class="form-control" placeholder="" value="<?php //echo $_POST['level_name']; ?>" tabindex="<?php //echo $tab++; ?>">
								</div>
							</div>-->
					</div>
					<div id="test"></div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success">Submit</button>
								<input type="hidden" id="edit_sno" name="edit_sno"
									value="<?php echo $_POST['edit_sno']; ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h4 class="card-title text-center"></h4></br>
			</div>
			<div class="card-body">
				<table class="table table-striped table-hover table-bordered" id="general_stat_table">
					<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							<th>S.No.</th>
							<th>Pay Scale</th>
							<th>Level</th>
							<th>Basic Pay</th>
							<th class="no-print text-center">Edit</th>
							<th class="no-print text-center">Delete</th>
						</tr>
						<tr>
							<?php
							// for($i=1;$i<=4;$i++){
							// echo '<th>'.$i.'</th>';
							// }
							?>

						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						// if(isset($_POST['search'])){
						$sql = 'select * from master_pay_level_grade where 1=1';
						$result = execute_query($sql);
						while ($row = mysqli_fetch_assoc($result)) {
							echo '<tr>
									<td>' . $i++ . '</td>
									<td>' . $row['pay_scale'] . '</td>
									<td>' . $row['level_name'] . '</td>
									<td>' . $row['basic_pay'] . '</td>
									<td><a href="' . $_SERVER['PHP_SELF'] . '?edit_sno=' . $row['sno'] . '" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
									<td><a href="' . $_SERVER['PHP_SELF'] . '?del=' . $row['sno'] . '" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete Entry"></span></a></td>
								</tr>';
						}
						// }				
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
<script>

	$('select[multiple]').multiselect();
</script>


<?php
page_footer_end();
?>