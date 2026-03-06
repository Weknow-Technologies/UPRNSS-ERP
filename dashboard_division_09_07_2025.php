<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['unit_name'])){
	
	/*$sql = 'insert into invoice_format_1 (user_id, entry_date, creation_time) values ("'.$_SESSION['usersno'].'", "'.date("Y-m-d").'", "'.date("Y-m-d H:i:s").'")';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$inv_id = mysqli_insert_id($db);
		
		for($i=1; $i<=$_POST['id']; $i++){
			$sql= ' insert into transaction_format_1 (invoice_id, month, head_type, amount, utr_no, transaction_date, other) values ("'.$inv_id.'", "' .$_POST['month_'.$i].'", "'.$_POST['head_type_'.$i].'", "'.$_POST['amount_'.$i].'", "'.$_POST['utr_no_'.$i].'", "'.$_POST['transaction_date_'.$i].'", "'.$_POST['other_'.$i].'")';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="text text-success">Data Saved</p>';
			}
		}
	}*/
}
else{
		
	$_POST['unit_name_1'] = '';
	$_POST['month_1'] = '';
	$_POST['head_type_1'] = '';
	$_POST['amount_1'] = '';
	$_POST['utr_no_1'] = '';
	$_POST['transaction_date_1'] = '';
	$_POST['other_1'] = '';


}
if(isset($_GET['id'])){
	$sql = 'select * from uprnss_division where s_no="'.$_GET['id'].'"';
	$division = mysqli_fetch_assoc(execute_query($sql));
}

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">Civil Report <?php echo $division['division_name']; ?></h4></br>
                    </div>
					<?php echo $msg; ?>
                    <div class="card-body">
						<table class="table table-hover table-striped table-bordered">
							<thead>
								<tr>
									<th rowspan="2">S.No.</th>
									<th rowspan="2">Department Name</th>
									<th rowspan="2">District Name</th>
									<th rowspan="2">Project Name</th>
									<th rowspan="2">Master Freeze</th>
									<th colspan="2">Reports as On</th>
								</tr>
								<tr>
									<th>4th</th>
									<th>16th</th>
								</tr>	
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT uprnss_project_temp.sno as sno, project_name_hindi, master_freeze, department_name_hindi, district_name_hindi, admin_go_no  FROM `uprnss_project_temp` 
							left join uprnss_district on uprnss_district.sno = district_id 
							left join uprnss_department_name on uprnss_department_name.sno = department_id 
							where uprnss_project_temp.division_id in ('.$_GET['id'].') and (status!="5" or status="0" or status is null or status="1") ORDER BY `uprnss_department_name`.`department_name_hindi` ASC';
							$result = execute_query($sql);
							$i=1;
							while($row = mysqli_fetch_assoc($result)){
								$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-01").'" and last_update<="'.date("Y-m-14").'"';
								$result_invoice_4 = execute_query($sql);
								if(mysqli_num_rows($result_invoice_4)!=0){
									$report_4 = '<p class="text text-success">Yes</p>';
								}
								else{
									$report_4 = '<p class="text text-danger">No</p>';
								}

								$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-14").'" and last_update<="'.date("Y-m-t").'"';
								//echo $sql.'<br>';
								$result_invoice_16 = execute_query($sql);
								if(mysqli_num_rows($result_invoice_16)!=0){
									$report_16 = '<p class="text text-success">Yes</p>';
								}
								else{
									$report_16 = '<p class="text text-danger">No</p>';
								}

								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row['department_name_hindi'].'</td>
								<td>'.$row['district_name_hindi'].'</td>
								<td>'.$row['project_name_hindi'].'<h6> '.$row['admin_go_no'].'</h6></td>
								<td>'.($row['master_freeze']=='1'?'<p class="text text-success">Yes</p>':'<p class="text text-danger">No</p>').'</td>
								<td>'.$report_4.'</td>
								<td>'.$report_16.'</td>
								</tr>
								';
							}
							?>
							</tbody>
						</table>
					</div>
                </div>
            </div>
        </div>
    </form>
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