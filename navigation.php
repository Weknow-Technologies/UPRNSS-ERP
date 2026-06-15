<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();



	if(isset($_POST['submit'])){
		if($_POST['edit_sno']==''){
		$sql = 'insert into navigation (
		  `hyper_link` ,
		  `icon_image` ,
		  `link_description` ,
		  `parent` ,
		  `color` ,
		  `admin_parent` ,
		  `admin_sub_parent` ,		  
		  `sort_no` 
		   ) 
		
		values (
		"'.$_POST['hyper_link'].'", 
		"'.$_POST['icon_image'].'", 
		"'.$_POST['link_description'].'", 
		"'.$_POST['parent'].'", 
		"'.$_POST['color'].'", 
		"'.$_POST['sub_parent'].'",
		"'.$_POST['sub_sub_parent'].'",
		"'.$_POST['sort_no'].'"
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
			`color` ="'.$_POST['color'].'",
			`admin_parent` ="'.$_POST['sub_parent'].'",
			`admin_sub_parent` ="'.$_POST['sub_sub_parent'].'",	
			`sort_no` ="'.$_POST['sort_no'].'"
			
		
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
	$_POST['color'] = '';
	$_POST['sub_parent'] = '';
	$_POST['sort_no'] = '';
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

		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
			<div class="card">
				<div class="card-body">
				<?php echo $msg; ?>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label >Hyper Link</label>
								<input type="text" name="hyper_link" id="hyper_link" class="form-control" placeholder="" value="<?php echo $_POST['hyper_link']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3 ">
							<div class="form-group">
								<label for="">Icon code</label>
								<input type="text" name="icon_image" id="icon_image" class="form-control" placeholder=""value="<?php echo $_POST['icon_image']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="">Navigation Name</label>
								<input type="text" name="link_description" id="link_description" class="form-control" placeholder="" value="<?php echo $_POST['link_description']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="">Parent</label>
								<input type="text" name="parent" id="parent" class="form-control" placeholder="" value="<?php echo $_POST['parent']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="">Color</label>
								<input type="text" name="color" id="color" class="form-control" placeholder="" value="<?php echo $_POST['color']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="">Sub-Parent</label>
								<input type="text" name="sub_parent" id="sub_parent" class="form-control" placeholder="" value="<?php echo $_POST['sub_parent']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="">Sub-Sub-Parent</label>
								<input type="text" name="sub_sub_parent" id="sub_sub_parent" class="form-control" placeholder="" value="<?php echo $_POST['sub_sub_parent']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="">Sort no.</label>
								<input type="text" name="sort_no" id="sort_no" class="form-control" placeholder="" value="<?php echo $_POST['sort_no']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						
					</div>
					<div class="row">
						<div class="col-md-11"  align = "center">
							<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
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
					<?php echo $msg1; ?>
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover">
						<tr class="table-success">
						<th>S.No.</th>
						<th>Hyper Link</th>
						<th>Navigation Id</th>
						<th>Icon code</th>
						<th>Navigation Name</th>
						<th>Color</th>
						<th>Parent</th>
						<th>Sub-Parent</th>
						<th>Sub-Sub-Parent</th>
						<th>Sort no.</th>
						<th></th>
						<th></th>
						</tr>
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
							<td>'.$row['color'].'</td>
							<td>'.$row['parent'].'</td>
							<td>'.$row['admin_parent'].'</td>
							<td>'.$row['admin_sub_parent'].'</td>
							<td>'.$row['sort_no'].'</td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?del='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete Entry"></span></a></td>
							</tr>';
						}
						?>
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

<?php		
page_footer_end();
?>
