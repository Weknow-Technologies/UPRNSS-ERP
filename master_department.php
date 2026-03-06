<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['dept_eng'])){
	if(isset($_POST['department_sort_name']) && $_POST['department_sort_name']!=''){
		$checksql="SELECT * FROM uprnss_department_name WHERE department_sort_name='".$_POST['department_sort_name']."'";
		$checkres=mysqli_query($db,$checksql);
		if(mysqli_num_rows($checkres)!=0){
			$msg.='<div class="alert alert-danger">Already This Short Name Exist</div>';
		}else{
			if($_POST['edit_sno']!=''){
				//department name must be unique so we are checking if it is unique or not
				
		
				$sql = 'update uprnss_department_name set 
				department_name_english="'.$_POST['dept_eng'].'", 
				department_name_hindi="'.$_POST['dept_hindi'].'", 
				department_sort_name="'.$_POST['department_sort_name'].'" 
				where sno="'.$_POST['edit_sno'].'"';
			}
			else{
				$sql = 'insert into uprnss_department_name (department_name_english, department_name_hindi, department_sort_name) values ("'.$_POST['dept_eng'].'", "'.$_POST['dept_hindi'].'","'.$_POST['department_sort_name'].'")';
				
			}
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="alert alert-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="alert alert-success">Data Saved</p>';
				$_POST['dept_eng'] = '';
				$_POST['dept_hindi'] = '';
				$_POST['department_sort_name'] = '';
				$_POST['edit_sno'] = '';
			}
		}
	}
	
}else{
	$_POST['dept_eng'] = '';
	$_POST['dept_hindi'] = '';
	$_POST['department_sort_name'] = '';
	$_POST['edit_sno'] = '';
}

if(isset($_GET['id'])){
	$sql = 'select * from uprnss_department_name where sno="'.$_GET['id'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	
	$_POST['dept_eng'] = $data['department_name_english'];
	$_POST['dept_hindi'] = $data['department_name_hindi'];
	$_POST['department_sort_name'] = $data['department_sort_name'];
	
	$_POST['edit_sno'] = $data['sno'];
}

if(isset($_GET['act'])){
	$sql = 'select * from uprnss_department_name where sno="'.$_GET['act'].'"';
	
	$product = mysqli_fetch_assoc(execute_query($sql));
	
	if($product['active_inactive']=='' or $product['active_inactive']=='0'){
		$sql = 'update uprnss_department_name set active_inactive=1 where sno='.$_GET['act'];
		execute_query($sql);
	}
	else{
		$sql = 'update uprnss_department_name set active_inactive=0 where sno='.$_GET['act'];
		execute_query($sql);
	}
	if(mysqli_error($db)){ 
		$msg .= '<p class="alert alert-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="alert alert-success">Data Saved</p>';
		$_POST['division_name'] = '';
		$_POST['department_sort_name'] = '';
		$_POST['edit_sno'] = '';
	}
}


if(isset($_GET['delid'])){
	$sql='select * from uprnss_project_temp where department_id='.$_GET['delid']; 
	if(mysqli_num_rows(execute_query($sql))!=0){
		$msg.='<div class="alert alert-danger">Cannot Delete,You have Projects related to this department.</div>';
	}
	if($msg==''){
		$sql = 'delete from uprnss_department_name where sno='.$_GET['delid'];
		execute_query($sql);
		$msg.='<div class="alert alert-danger">Delete Successful</div>';
	}
}

if(isset($_GET['mid'])){
	$sql = 'update uprnss_project_temp set department_id="'.$_GET['mid'].'" where department_id="'.$_GET['alt'].'"';
	execute_query($sql);
	
	$sql = 'update invoice_civil set department_id="'.$_GET['mid'].'" where department_id="'.$_GET['alt'].'"';
	execute_query($sql);
	
	$sql = 'delete from uprnss_department_name where sno='.$_GET['alt'];
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="alert alert-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="alert alert-success">Merge Completed</p>';
		$_POST['division_name'] = '';
		$_POST['department_sort_name'] = '';
		$_POST['edit_sno'] = '';
	}
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
                                    <label > Department Name English</label><br>
                                    <input type="text" name="dept_eng" id="dept_eng" class="form-control" value="<?php echo $_POST['dept_eng']; ?>" tabindex="<?php echo $tab++; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label > Department Name Hindi</label><br>
                                    <input type="text" name="dept_hindi" id="dept_hindi" class="form-control" value="<?php echo $_POST['dept_hindi']; ?>" tabindex="<?php echo $tab++; ?>" required>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label > Sort Name.</label>
                                    <input type="text" name="department_sort_name" id="department_sort_name" class="form-control" value="<?php echo $_POST['department_sort_name']; ?>" tabindex="<?php echo $tab++; ?>" required>
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
					<table class="table table-striped table-hover">
						<tr class="table-success">
						<th>S.No.</th>
						<th>Department Short Name</th>
						<th>Department Name English</th>
						<th>Department Name Hindi</th>
						<th>No of Project</th>
						<th>Department ID</th>
						<th>Ledger ID</th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						</tr>
						<?php
						$i=1;
						$sql = 'select * from uprnss_department_name ';
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							$sql = 'select * from uprnss_project_temp where department_id="'.$row['sno'].'" and (status!="5" or status="0" or status is null or status="1")';
							$count = execute_query($sql);
							if(mysqli_num_rows($count)!=0){
								$count = mysqli_num_rows($count);
							}
							else{
								$count = '0';
							}
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row['department_sort_name'].'</td>
							<td>'.$row['department_name_english'].'</td>
							<td>'.$row['department_name_hindi'].'</td>
							<td>'.$count.'</td>
							<td>'.$row['sno'].'</td>
							<td>'.$row['ledger_id'].'</td>
							<td class="no-print text-center">
								<a href="master_department.php?id='.$row['sno'].'"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="master_department.php?delid='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="#" onclick="return alternate_value('.$row['sno'].')"><span class="fa fa-compress-alt" aria-hidden="true" data-toggle="tooltip" title="Merge"></span></a>
							</td>
							<td class="no-print text-center">
								<a href="master_department.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');"><span class="fa fa-ban" aria-hidden="true" data-toggle="tooltip" title="Activate/Inactivate" style="color:#f00"></span>
							</td>
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
function alternate_value(id){
	var alternate = prompt("Please enter product id to merge with this id.","");
	if(!alternate){
		alert("Can not merge without product id.");
		return false;
	}
	else{
		window.open("master_department.php?mid="+id+"&alt="+alternate, '_self');
		return true;
	}
}

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>