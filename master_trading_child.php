<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['child_name'])){
	
	$sql = 'insert into master_trading_child (group_id, child_name, creation_time) values ("'.$_POST['group_name'].'", "'.$_POST['child_name'].'", "'.date("Y-m-d H:i:s").'")';
    execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
	}
}
else{
		
	$_POST['child_name'] = '';
	
	
}

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<div class="row" >
								<div class="col-md-4 ">
									<div class="form-group">
										<label>Group Name</label>
										<?php
										$query = "select * from master_trading_group";
										$run = mysqli_query($db,$query);
										?>
										<select class="form-control" name="group_name" id="group_name"  >
										<option value="">--- Select ---</option>
										<?php
										while($data = mysqli_fetch_array($run))
										{
											echo"<option value='$data[sno]'>$data[group_name]</option>";
										}
										
										?>
										</select>
									</div>
								</div>
							</div>
						<div class="row" id="">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label > Child Name</label><br>
                                    <input type="text" name="child_name" id="child_name" class="form-control" placeholder="" value="<?php echo $_POST['child_name']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success">Submit</button>
									<input type="hidden" id="id" name="id" value="1">
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
					<table class="table table-striped table-hover">
						<tr class="table-success">
						<th>S.No.</th>
						<th>Group Name</th>
						<th>Child Name</th>
						<th></th>
						<th></th>
						</tr>
						<?php
						$i=1;
						$sql = 'select master_trading_child.sno as sno, group_name, child_name from master_trading_child left join master_trading_group on master_trading_group.sno = group_id order by group_name, child_name';
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row['group_name'].'</td>
							<td>'.$row['child_name'].'</td>
							<td>Edit</td>
							<td>Deleted</td>
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
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>