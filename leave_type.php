<?php
include ("scripts/settings.php");
$msg = '';

page_header_start();
page_header_end();
page_sidebar();

if (isset($_POST['submit'])) {
	if (empty($_POST['edit_sno'])) {
		// status 5= deleted
		$sql = "SELECT * FROM `leave_type`";
		$result = execute_query($sql);

		$sql = 'INSERT INTO leave_type (leave_type, leave_for_regular, leave_for_contractual, leave_for_deputation, created_by, creation_time, status)
                VALUES ("' . $_POST['leave_type'] . '", "' . $_POST['leave_for_regular'] . '", "' . $_POST['leave_for_contractual'] . '", "' . $_POST['leave_for_deputation'] . '", "' . $_SESSION['usersno'] . '", "' . date("Y-m-d H:i:s") . '", "0")';

		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {
			$msg .= '<p class="alert alert-success">Data Saved</p>';
		}
	} else {
		$sql = 'UPDATE leave_type SET
                leave_type = "' . $_POST['leave_type'] . '",  
                leave_for_regular = "' . $_POST['leave_for_regular'] . '",
                leave_for_contractual = "' . $_POST['leave_for_contractual'] . '", 
                leave_for_deputation = "' . $_POST['leave_for_deputation'] . '",                  
                edited_by = "' . $_SESSION['usersno'] . '", 
                edition_time = "' . date("Y-m-d H:i:s") . '" 
                WHERE sno="' . $_POST['edit_sno'] . '"';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {
			$msg .= '<p class="alert alert-success">Data Updates</p>';
		}
	}
} else {
	$resetFields = ['leave_type', 'leave_for_regular', 'leave_for_contractual', 'leave_for_deputation'];
	foreach ($resetFields as $field) {
		$_POST[$field] = "";
	}
}

if (isset($_GET['edit_sno'])) {
	$sql = 'SELECT * FROM leave_type WHERE sno="' . mysqli_real_escape_string($db, $_GET['edit_sno']) . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	if ($data) {
		$_POST['leave_type'] = $data['leave_type'];
		$_POST['leave_for_regular'] = $data['leave_for_regular'];
		$_POST['leave_for_contractual'] = $data['leave_for_contractual'];
		$_POST['leave_for_deputation'] = $data['leave_for_deputation'];
		$_POST['edit_sno'] = $data['sno'];
	} else {
	}
}

if (isset($_GET['delid'])) {
	$sql = 'UPDATE leave_type SET status="5" WHERE sno="' . $_GET['delid'] . '"';
	execute_query($sql);
	if (mysqli_error($db)) {
		$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
	} else {
		$msg .= '<p class="alert alert-success">Deleted</p>';
	}
}

?>

<form id="form" name="form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	<div class="row no-print">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h4 class="text-center card-title" style="font-weight: bold; background-color: #f5f8fa">Leave Form
					</h4>
					<?php echo $msg; ?>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px;  padding: 3px 6px; border-radius: 4px;">Leave
									Name</label>
								<input type="text" name="leave_type" id="leave_type" class="form-control" placeholder=""value="<?php echo isset($_POST['leave_type']) ? $_POST['leave_type'] : ''; ?>">
							</div>
						</div>
						<!-- <div class="col-md-4">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px;  padding: 3px 6px; border-radius: 4px;">Leaves
									for Grant In-Aid</label>
								<input type="text" name="leave_grant" id="leave_grant" class="form-control" placeholder=""value="<?php echo isset($_POST['leave_grant']) ? $_POST['leave_grant'] : ''; ?>">
							</div>
						</div> -->
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px;  padding: 3px 6px; border-radius: 4px;">Leave
									For Regular Employee</label>
								<input type="text" name="leave_for_regular" id="leave_for_regular" class="form-control"
									placeholder=""value="<?php echo isset($_POST['leave_for_regular']) ? $_POST['leave_for_regular'] : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px;  padding: 3px 6px; border-radius: 4px;">Leave
									For Contractual Employee</label>
									<input type="text" name="leave_for_contractual" id="leave_for_contractual" class="form-control" placeholder=""value="<?php echo isset($_POST['leave_for_contractual']) ? $_POST['leave_for_contractual'] : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px;  padding: 3px 6px; border-radius: 4px;">Leave
									For Deputation Employee</label>
								<input type="text" name="leave_for_deputation" id="leave_for_deputation" class="form-control" placeholder=""value="<?php echo isset($_POST['leave_for_deputation']) ? $_POST['leave_for_deputation'] : ''; ?>">
							</div>
						</div>
					</div>
					
					<div class="col-md-11 pr-1" align="center">
						<div class="form-group">
							<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
							<input type="hidden" name="edit_sno" value="<?php echo isset($_POST['edit_sno']) ? $_POST['edit_sno'] : ''; ?>">

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
            <div class="card-body">
            <h4 class="card-title">Report</h4><br>
                <table class="table table-hover table-striped" id="">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Leave Name</th>
                            <th>Leaves for Grand in Aid</th>
                            <th>Leaves for Regular Employee</th>
                            <th>Leaves for Contractual Employee</th>
                            <th>Leaves for Deputation Employee</th>                            
                            <th></th>                            
                            <th></th>                            
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $sql = "select * from leave_type where status !=5";
                        $result = execute_query($sql);

                        $i = 1;
                        while ($res = mysqli_fetch_array($result)) {
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $res['leave_type']; ?></td>
                                <td><?php echo $res['leave_for_regular'] ?></td>
                                <td><?php echo $res['leave_for_contractual']; ?></td>                                
                                <td><?php echo $res['leave_for_deputation']; ?></td>                                
                                <td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?edit_sno=<?php echo $res['sno']; ?>"
                                        onClick="return confirm('Are you sure?');" alt="Edit Details" data-toggle="tooltip"
                                        title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
                                <td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?delid=<?php echo $res['sno']; ?>"
                                        onclick="return confirm('Are you sure?');" style="color:#f00"
                                        alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true"
                                            data-toggle="tooltip" title="Delete Entry"></span></a></td>
                            </tr>
                            <?php
                            $i++;
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
			colReorder: true,
			scrollX: true, // Enable horizontal scrolling if needed
		});


	});

	$('select[multiple]').multiselect();

	var actionUrl = 'scripts/ajax.php';

	function fill_sub_department(val, selected) {
		var data = { "term": "b", "id": "sub_dep", "val": val };

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (ajaxdata) {
				console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.sub_department_hindi + '</option>';

				});
				$("#sub_department_id").html(txt);
			}
		});
	}

	function fill_district(val, selected) {
		var data = { "term": "b", "id": "dist", "val": val };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.district_name + '</option>';

				});
				$("#district").html(txt);
			}
		});
	}

	function fill_project(val, selected) {
		var data = { "term": "b", "id": "proj", "val": val, "dept": $("#department").val() };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.project_name_hindi + '</option>';

				});
				$("#project_name").html(txt);
			}
		});
	}

	<?php
	if (isset($_GET['edit_sno'])) {
		?>
	$(document).ready(function () {
			fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
					fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
		fill_project(<?php echo $_POST['district']; ?>, <?php echo $_POST['project_name']; ?>);
				});
				<?php
	}
	?>

</script>

<?php
page_footer_end();
?>