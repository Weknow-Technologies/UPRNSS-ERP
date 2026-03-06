<?php
include("scripts/settings.php");
page_header_start('Navigation Report');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$msg1='';


	if(isset($_POST['submit'])){
		if($_POST['edit_sno']==''){
		$sql = 'insert into navigation (
		  `hyper_link` ,
		  `icon_image` ,
		  `link_description` ,
		  `parent`, 
		  `Priority` 
		   ) 
		
		values (
		"'.$_POST['hyper_link'].'", 
		"'.$_POST['icon_image'].'", 
		"'.$_POST['link_description'].'", 
		"'.$_POST['parent'].'",
		"'.$_POST['Priority'].'"
		 )';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data save</p>';
			unset($_POST);
			goto postblank;
		}
	}
		else{
			$sql = 'update navigation set

		    `hyper_link` ="'.$_POST['hyper_link'].'",
			`icon_image` ="'.$_POST['icon_image'].'",
			`link_description` ="'.$_POST['link_description'].'",
			`parent` ="'.$_POST['parent'].'",
			`Priority` ="'.$_POST['Priority'].'"
			
		
			where sno="'.$_POST['edit_sno'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data Update</p>';
			unset($_POST);
			goto postblank;
		}
	}	
}
else{
	
	postblank:
	$_POST['hyper_link'] = '';
	$_POST['icon_image'] = '';
	$_POST['link_description'] = '';
	$_POST['parent'] = '';
	$_POST['Priority'] = '';
	$_POST['edit_sno'] = '';
}


if(isset($_GET['edit_sno'])){
	$sql = 'select * from navigation where sno="'.$_GET['edit_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	
	$_POST['hyper_link'] = $data['hyper_link'];
	$_POST['icon_image'] = $data['icon_image'];
	$_POST['link_description'] = $data['link_description'];
	$_POST['parent'] = $data['parent'];
	$_POST['color'] = $data['color'];
	$_POST['sub_parent'] = $data['sub_parent'];
	$_POST['sort_no'] = $data['sort_no'];
	
	$_POST['edit_sno'] = $data['sno'];
}

if(isset($_GET['del'])){
	$sql = 'delete from navigation where sno="'.$_GET['del'].'"';
	execute_query($sql);
	
	$msg1 .= '<p class="text text-danger">Data Deleted.</p>';
}

?>			
	
<div class="container">
	<div class="row">
		
		<div class="col-sm-12">
			 
		</div>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="panel">
				<div class="panel-heading">Add Navigation</div>
				<div class="panel-body">
					<div class="card-body">
						<?php echo $msg; ?>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label >Hyper Link</label>
									<input type="text" name="hyper_link" id="hyper_link" class="form-control" placeholder="" value="<?php echo $_POST['hyper_link']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-2 ">
								<div class="form-group">
									<label for="">Icon Image</label>
									<input type="file" name="icon_image" id="icon_image" class="form-control" placeholder=""value="<?php echo $_POST['icon_image']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label for="">Navigation Name</label>
									<input type="text" name="link_description" id="link_description" class="form-control" placeholder="" value="<?php echo $_POST['link_description']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label for="">Parent</label>
									<input type="text" name="parent" id="parent" class="form-control" placeholder="" value="<?php echo $_POST['parent']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label for="">Priority</label>
									<input type="text" name="Priority" id="Priority" class="form-control" placeholder="" value="<?php echo $_POST['Priority']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6" >
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
									<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	
	<div class="panel">
		<?php echo $msg1; ?>
		<table class="table table-hover table-responsive table-bordered table-striped">
			<thead>
				<tr class="panel-heading">
						<th>S.No.</th>
						<th>Hyper Link</th>
						<th>Navigation Id</th>
						<th>Icon Image</th>
						<th>Navigation Name</th>
						<th>Parent</th>
						<th>Priority</th>
						<th></th>
						<th></th>
				</tr>
			</thead>
			<tbody>
			<?php
				$i=1;
				$sql = 'select * from navigation ';
				$result = execute_query($sql);
				while($row = mysqli_fetch_assoc($result)){
					echo '<tr>
					<td>'.$i++.'</td>
					<td>'.$row['hyper_link'].'</td>
					<td>'.$row['sno'].'</td>
					<td>'.$row['icon_image'].'</td>
					<td>'.$row['link_description'].'</td>
					<td>'.$row['parent'].'</td>
					<td>'.$row['priority'].'</td>
					<td><a href="payroll_navigation.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details">Edit</a></td>
					<td><a href="payroll_navigation.php?del='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry">Delete</a></td>
					</tr>';
				}
				?>
			</tbody>
		</table>
	</div>
</div>

<?php		
page_footer();
?>
