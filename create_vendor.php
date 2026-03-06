<?php
include ("scripts/settings.php");
$msg = '';

page_header_start();
page_header_end();
page_sidebar();

if (isset($_POST['submit'])) {
	if (empty($_POST['edit_sno'])) {
		// status 5= deleted
		$sql = "SELECT * FROM `vendor`";
		$result = execute_query($sql);

		$sql = 'INSERT INTO vendor (firm_name, contractor_name, contractor_code, address, pincode, mobile_no,email_id, pan_no, gst_no, password, contractor_status, validity_start_date, validity_end_date,  created_by, creation_time, status)
                VALUES ("' . $_POST['firm_name'] . '","' . $_POST['contractor_name'] . '", "' . $_POST['contractor_code'] . '", "' . $_POST['address'] . '", "' . $_POST['pincode'] . '", "' . $_POST['mobile_no'] . '", "' . $_POST['email_id'] . '", "' . $_POST['pan_no'] . '", "' . $_POST['gst_no'] . '", "' . $_POST['password'] . '", "' . $_POST['contractor_status'] . '", "' . $_POST['validity_start_date'] . '", "' . $_POST['validity_end_date'] . '",  "' . $_SESSION['usersno'] . '", "' . date("Y-m-d H:i:s") . '", "0")';

		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {
			$msg .= '<p class="alert alert-success">Data Saved</p>';
		}
	} else {
		$sql = 'UPDATE vendor SET
                firm_name = "' . $_POST['firm_name'] . '",  
                contractor_name = "' . $_POST['contractor_name'] . '",  
                contractor_code = "' . $_POST['contractor_code'] . '",
                address = "' . $_POST['address'] . '", 
                pincode = "' . $_POST['pincode'] . '", 
                mobile_no = "' . $_POST['mobile_no'] . '", 
                email_id = "' . $_POST['email_id'] . '", 
                pan_no = "' . $_POST['pan_no'] . '", 
                gst_no = "' . $_POST['gst_no'] . '", 
                password = "' . $_POST['password'] . '",  
                contractor_status = "' . $_POST['contractor_status'] . '", 
                validity_start_date = "' . $_POST['validity_start_date'] . '", 
                validity_end_date = "' . $_POST['validity_end_date'] . '", 
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
	// Reset form fields if not submitted
	$resetFields = ['firm_name', 'contractor_name', 'contractor_code', 'address', 'pincode', 'mobile_no', 'email_id', 'pan_no', 'gst_no', 'password', 'confirm_password', 'contractor_status', 'validity_start_date', 'validity_end_date'];
	foreach ($resetFields as $field) {
		$_POST[$field] = "";
	}
}

if (isset($_GET['edit_sno'])) {
	$sql = 'SELECT * FROM vendor WHERE sno="' . mysqli_real_escape_string($db, $_GET['edit_sno']) . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	if ($data) {
		$_POST['firm_name'] = $data['firm_name'];
		$_POST['contractor_name'] = $data['contractor_name'];
		$_POST['contractor_code'] = $data['contractor_code'];
		$_POST['address'] = $data['address'];
		$_POST['pincode'] = $data['pincode'];
		$_POST['mobile_no'] = $data['mobile_no'];
		$_POST['email_id'] = $data['email_id'];
		$_POST['pan_no'] = $data['pan_no'];
		$_POST['gst_no'] = $data['gst_no'];
		$_POST['password'] = $data['password'];
		$_POST['confirm_password'] = $data['confirm_password'];
		$_POST['contractor_status'] = $data['contractor_status'];
		$_POST['validity_start_date'] = $data['validity_start_date'];
		$_POST['validity_end_date'] = $data['validity_end_date'];
		$_POST['edit_sno'] = $data['sno'];
	} else {
		// Handle the case where no data is returned for the provided edit_sno
	}
}

if (isset($_GET['delid'])) {
	$sql = 'UPDATE vendor SET status="5" WHERE sno="' . mysqli_real_escape_string($db, $_GET['delid']) . '"';
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
					<h4 class="text-center card-title" style="font-weight: bold; background-color: #f5f8fa">
						<?php echo isset($_POST['edit_sno']) ? 'Edit Contractor' : 'Create Contractor'; ?>
					</h4>
					<?php echo $msg; ?>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Firm
									Name</label>
								<input type="text" name="firm_name" id="firm_name" class="form-control" placeholder="" value="<?php echo isset($_POST['firm_name']) ? htmlspecialchars($_POST['firm_name']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Contractor
									Name</label>
								<input type="text" name="contractor_name" id="contractor_name" class="form-control"
									placeholder="" value="<?php echo isset($_POST['contractor_name']) ? htmlspecialchars($_POST['contractor_name']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Contractor
									Code</label>
								<input type="text" name="contractor_code" id="contractor_code" class="form-control"
									placeholder="" value="<?php echo isset($_POST['contractor_code']) ? htmlspecialchars($_POST['contractor_code']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Address</label>
								<input type="text" name="address" id="address" class="form-control" placeholder="" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
							</div>
						</div>
					</div>
					<div class="row">

						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Pincode</label>
								<input type="text" name="pincode" id="pincode" class="form-control" placeholder="" value="<?php echo isset($_POST['pincode']) ? htmlspecialchars($_POST['pincode']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Mobile
									No.</label>
								<input type="text" name="mobile_no" id="mobile_no" class="form-control" placeholder="" value="<?php echo isset($_POST['mobile_no']) ? htmlspecialchars($_POST['mobile_no']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Email
									Id</label>
								<input type="text" name="email_id" id="email_id" class="form-control" placeholder="" value="<?php echo isset($_POST['email_id']) ? htmlspecialchars($_POST['email_id']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">PAN
									No.</label>
								<input type="text" name="pan_no" id="pan_no" class="form-control" placeholder="" value="<?php echo isset($_POST['pan_no']) ? htmlspecialchars($_POST['pan_no']) : ''; ?>">
							</div>
						</div>
					</div>
					<div class="row">

						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">GST
									No.</label>
								<input type="text" name="gst_no" id="gst_no" class="form-control" placeholder="" value="<?php echo isset($_POST['gst_no']) ? htmlspecialchars($_POST['gst_no']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Password</label>
								<input type="text" name="password" id="password" class="form-control" placeholder="" value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Confirm
									Password</label>
								<input type="text" name="confirm_password" id="confirm_password" class="form-control"
									placeholder="" value="<?php echo isset($_POST['confirm_password']) ? htmlspecialchars($_POST['confirm_password']) : ''; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Contractor
									Status</label>
								<select name="contractor_status" id="contractor_status" class="form-control">
									<option value="">-select-</option>
									<option value="Whitelist" <?php echo (isset($_POST['contractor_status']) && $_POST['contractor_status'] == 'Whitelist') ? 'selected' : ''; ?>>Whitelist</option>
									<option value="Blacklist" <?php echo (isset($_POST['contractor_status']) && $_POST['contractor_status'] == 'Blacklist') ? 'selected' : ''; ?>>Blacklist</option>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Validity
									Start Date</label>
								<script type="text/javascript" language="javascript">
									var startDate = '<?php echo isset($_POST['validity_start_date']) && !empty($_POST['validity_start_date']) ? $_POST['validity_start_date'] : '2024-04-01'; ?>';
									document.writeln(DateInput('validity_start_date', 'user_form', true, 'YYYY-MM-DD', startDate, 1));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label
									style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Validity
									End Date</label>
								<script type="text/javascript" language="javascript">
									var endDate = '<?php echo isset($_POST['validity_end_date']) && !empty($_POST['validity_end_date']) ? $_POST['validity_end_date'] : '2024-04-01'; ?>';
									document.writeln(DateInput('validity_end_date', 'user_form', true, 'YYYY-MM-DD', endDate, 1));
								</script>
							</div>
						</div>
					</div>


					<?php if (isset($_POST['edit_sno'])) { ?>
						<input type="hidden" name="edit_sno" value="<?php echo htmlspecialchars($_POST['edit_sno']); ?>">
					<?php } ?>
					<div class="col-md-11 pr-1" align="center">
						<div class="form-group">
							<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">
								<?php echo isset($_POST['edit_sno']) ? 'Update' : 'Submit'; ?>
							</button>
							<?php if (isset($_POST['edit_sno'])) { ?>
								<a href="create_vendor.php" class="btn btn-secondary btn-fill pull-right" style="margin-right: 10px;">Cancel</a>
							<?php } ?>
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
		<h4 class="card-title"></h4>
			<div class="card-body">
				<table class="table table-hover table-striped" id="datatable">
					<thead>
						<tr>
							<th>S.No.</th>
							<th>Firm Name</th>
							<th>Contractor Name</th>
							<th>Contractor Code</th>
							<th>Address</th>
							<th>Mobile No.</th>
							<th>Email Id</th>
							<th>PAN No.</th>
							<th>GST No.</th>
							<th>Contractor Status</th>
							<th>Validity Start Date</th>
							<th>Validity End Date</th>
							<th>Project Awarded</th>
							<th>Action</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
<?php
$sql = "SELECT * FROM vendor WHERE status != '5' OR status IS NULL ORDER BY sno DESC";
$result = execute_query($sql);

$i = 1;
while ($res = mysqli_fetch_array($result)) {

    // 🔥 Project awarded count query
    $vendor_id = $res['sno'];
    $sql2 = "SELECT COUNT(*) AS total FROM tender_allotment 
             WHERE project_awarded_to = '$vendor_id' 
             AND status != '5'";
    $count_res = execute_query($sql2);
    $count_row = mysqli_fetch_assoc($count_res);
    $project_awarded_count = $count_row['total'];
?>
    <tr>
        <td><?php echo $i; ?></td>
        <td><?php echo $res['firm_name']; ?></td>
        <td><?php echo $res['contractor_name']; ?></td>
        <td><?php echo $res['contractor_code'] ?></td>
        <td><?php echo $res['address']; ?></td>
        <td><?php echo $res['mobile_no']; ?></td>
        <td><?php echo $res['email_id']; ?></td>
        <td><?php echo $res['pan_no']; ?></td>
        <td><?php echo $res['gst_no']; ?></td>
        <td><?php echo $res['contractor_status']; ?></td>
        <td><?php echo $res['validity_start_date']; ?></td>
        <td><?php echo $res['validity_end_date']; ?></td>
        <td><b><?php echo $project_awarded_count; ?></b></td>

        <td>
            <a href="create_vendor.php?edit_sno=<?php echo $res['sno']; ?>" class="btn btn-sm btn-primary">
                <i class="fa fa-edit"></i> Edit
            </a>
        </td>
        <td>
            <a href="create_vendor.php?delid=<?php echo $res['sno']; ?>" class="btn btn-sm btn-danger" 
               onclick="return confirm('Are you sure you want to delete this record?');">
                <i class="fa fa-trash"></i> Delete
            </a>
        </td>
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
		$('#datatable').DataTable({
			// paging: false,
			fixedHeader: true,
			colReorder: true,
			scrollX: true, // Enable horizontal scrolling if needed
		});
	});



</script>

<?php
page_footer_end();
?>