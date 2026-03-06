<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

    // $number = ""; 
    // $error = "";  
    // if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // $number = $_POST["promotion_no_of_posts"];
       // if (is_numeric($number) || $number === ''){
            // $error = "";
        // }else{
			// $error = "Please enter a valid number.";
		// }
    // }


if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		$sql = 'insert into dp_designation (grade_id, designation, sort_no, direct_no_of_posts, promotion_no_of_posts, deputation_no_of_posts, status, created_by, creation_time) values ("'.$_POST['grade_id'].'","'.$_POST['designation'].'", "'.$_POST['sort_no'].'","'.$_POST['direct_no_of_posts'].'", "'.$_POST['promotion_no_of_posts'].'", "'.$_POST['deputation_no_of_posts'].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'")';
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
		$sql = 'update dp_designation set 
		grade_id = "'.$_POST['grade_id'].'",  
		designation = "'.$_POST['designation'].'",  
		sort_no = "'.$_POST['sort_no'].'",  
		direct_no_of_posts = "'.$_POST['direct_no_of_posts'].'",  
		promotion_no_of_posts = "'.$_POST['promotion_no_of_posts'].'",  
		deputation_no_of_posts = "'.$_POST['deputation_no_of_posts'] .'",  
		edited_by = "'.$_SESSION['usersno'].'",  
		edition_time = "'.date("Y-m-d H:i:s").'" 
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
	$_POST['designation'] = '';
	$_POST['sort_no'] = '';
	$_POST['direct_no_of_posts'] = '';
	$_POST['promotion_no_of_posts'] = '';
	$_POST['deputation_no_of_posts'] = '';
	$_POST['edit_sno'] = '';
}




if(isset($_GET['edit_sno'])){
	$sql = 'select * from dp_designation where sno="'.$_GET['edit_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['sort_no'] = $data['sort_no'];
	$_POST['designation'] = $data['designation'];
	$_POST['direct_no_of_posts'] = $data['direct_no_of_posts'];
	$_POST['promotion_no_of_posts'] = $data['promotion_no_of_posts'];
	$_POST['deputation_no_of_posts'] = $data['deputation_no_of_posts'];
}	

if(isset($_GET['del'])){
	$sql = 'delete from dp_designation where sno="'.$_GET['del'].'"';
	execute_query($sql);
	
	
	$msg .= '<p class="text text-danger">Data Deleted.</p>';
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
						<div class="row" id="">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >Grade</label><br>
                                    <select  name="grade_id" id="grade_id" tabindex="<?php echo $tab++; ?>" class="form-control">
										<option value="">--Select--</option>
										<?php 
											$query = "select * from dp_type";
											$run = mysqli_query($db,$query);
											while($data = mysqli_fetch_array($run)){
												echo '<option value="'.$data['sno'].'" ';
												if(isset($_POST['grade_id'])){
													if($_POST['grade_id']==$data['sno']){
														echo ' selected="Selected"';
													}
												}
												echo '>'.trim($data['type_name']).'</option>';
											}
										?>
									</select>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >Designation</label><br>
                                    <input type="text" name="designation" id="designation" class="form-control" placeholder="" value="<?php echo $_POST['designation']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-2">
                                <div class="form-group">
                                    <label >Sort No.</label>
                                    <input type="number" name="sort_no" id="sort_no" class="form-control" placeholder="" value="<?php echo $_POST['sort_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div class="row" id="">
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >Direct No.of posts</label>
                                    <input type="number" name="direct_no_of_posts" id="direct_no_of_posts" class="form-control" placeholder="" value="<?php echo $_POST['direct_no_of_posts']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >Promotation No.of posts</label>
                                    <input type="number" name="promotion_no_of_posts" id="promotion_no_of_posts" class="form-control" placeholder="" value="<?php echo $_POST['promotion_no_of_posts']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >Deputation No.of posts</label><br>
                                    <input type="number" name="deputation_no_of_posts" id="deputation_no_of_posts" class="form-control" placeholder="" value="<?php echo $_POST['deputation_no_of_posts']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success">Submit</button>
									<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
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
							<tr >
							<th>S.No.</th>
							<th>Grade</th>
							<th>Designation</th>
							<th>Direct No.of posts</th>
							<th>Promotation No.of posts</th>
							<th>Deputation No.of posts</th>
							<th>Sort No.</th>
							<th></th>
							<th></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=1;
							$sql = 'select * from dp_designation order by abs(sort_no) ';
							$result = execute_query($sql);
							while($row = mysqli_fetch_assoc($result)){
								$sql = 'select * from dp_type where sno="'.$row['grade_id'].'"';
										$grade_type = execute_query($sql);
										if(mysqli_num_rows($grade_type)!=0){
											$grade_type = mysqli_fetch_assoc($grade_type);
										}
										else{
											unset($grade_type);
											$grade_type['type_name'] = '';
										}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$grade_type['type_name'].'</td>
								<td>'.$row['designation'].'</td>
								<td>'.$row['direct_no_of_posts'].'</td>
								<td>'.$row['promotion_no_of_posts'].'</td>
								<td>'.$row['deputation_no_of_posts'].'</td>
								<td>'.$row['sort_no'].'</td>
								<td><a href="'.$_SERVER['PHP_SELF'].'?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
								<td><a href="'.$_SERVER['PHP_SELF'].'?del='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete Entry"></span></a></td>
								</tr>';
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
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>