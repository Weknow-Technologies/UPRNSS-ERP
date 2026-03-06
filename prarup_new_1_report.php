<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();

$msg='';

if(isset($_POST['submit'])){
	if($_POST['project_id_1']!=""){
		$sql='insert into invoice_prarup_1 (entry_date, total_trans, division_id, created_by, creation_time, status) values("'.date("Y-m-d").'", "'.$_POST['prarup_1_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'" , "'.$_POST['status'].'");';
		
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			
			$inv_id = mysqli_insert_id($db);
			
			for($i=1; $i<=$_POST['prarup_1_id']; $i++){
				$sql = 'insert into trans_prarup_1 (`invoice_id`, `project_id`, `prambhik_dey`, `monthduemukhyalaya`, `monthdueprakhand`, `monthduemukhyalayasentage`, `monthdueprakhandsentage`, `monthsentageutr`, `khamikdeymukhyalaya`, `khamikdeyprakhand`, `khamikpreshitmukhyalaya`, `khamikpreshitprakhand`, `remaindey`, `remainreason`, created_by, creation_time)
				 values ("'.$inv_id.'", "'.$_POST['project_id_'.$i].'", "'.$_POST['prambhik_dey_'.$i].'", "'.$_POST['monthduemukhyalaya_'.$i].'", "'.$_POST['monthdueprakhand_'.$i].'", "'.$_POST['monthduemukhyalayasentage_'.$i].'", "'.$_POST['monthdueprakhandsentage_'.$i].'", "'.$_POST['monthsentageutr_'.$i].'", "'.$_POST['khamikdeymukhyalaya_'.$i].'", "'.$_POST['khamikdeyprakhand_'.$i].'", "'.$_POST['khamikpreshitmukhyalaya_'.$i].'", "'.$_POST['khamikpreshitprakhand_'.$i].'", "'.$_POST['remaindey_'.$i].'", "'.$_POST['remainreason_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
			}
			if($msg==""){
				$msg="<p class='alert alert-success'>Data stored all</p>";
			}
		}
	}
	else{
		echo '<script>alert("Please Fill Form properly!");</script>';
	}
}


if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_1 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_1 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
		
	}
?>

<style>
    .bold-text {
        font-weight: bold;
		text-align: right;
		table {
			border-collapse: collapse;
			width: 100%;
		}
		th, td {
			border: 1px solid #dddddd;
			text-align: left;
			padding: 8px;
		}
		td {
			text-align: right!important;
		}

    }
</style>
<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>

	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					
					<table width="100%" class="table table-striped table-hover rounded text-right" style="margin:0px; padding:0px;">
						<tr >
							<th width="5%"></th>
							<th>परियोजना का नाम  </th>							
							<th width="15%">
								<select class="form-control" name="project_id" id="project_id" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
										$query = '(SELECT sno, project_name_hindi, Project_name, status FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
										// echo $query;
										$run = mysqli_query($db,$query);
										$a=1;
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_id_'.$a])){
												if($_POST['project_id_'.$a]==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_name_hindi']).'</option>';
										}
									?>
								</select>
							</th>
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
							<th width="10%"></th>
							<th>माह</th>
							<th width="15%">
								<select name="month" id="month" class="form-control"  value="">
									<option value="">--Select--</option>
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
							<th width="5%"></th>
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
						<h4 class="card-title text-center">मुख्यालय  को सेंटेज प्रेषण का विवरण वर्ष 2023-24 </h4></br>
					</div>
					<div class="card-body">
						    <table class="table table-striped table-bordered table-hover" >
								<thead style="position:sticky;top:0; z-index:2;">
									<tr>
										<th></th>
										<th>प्रखण्ड का नाम </th>
										<th>विभाग का नाम/परियोजना का नाम </th>
										<th>माह </th>
										<th>प्रारम्भिक अवशेष 01.04. 2023</th>
										<th>माह में देय सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष</th>
										<th>माह में देय सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष</th>
										<th>माह में प्रेषित सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष समायोजित सेंटेज </th>
										<th>माह में प्रेषित सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष प्राप्त</th>
										<th>माह में प्रेषित सेंटेज प्रेषित धनराशि का यू०टी०आर० नम्बर एवं दिनांक</th>
										<th>क्रमिक देय सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष </th>
										<th>क्रमिक देय सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष  </th>
										<th>क्रमिक प्रेषित सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष  </th>
										<th>क्रमिक प्रेषित सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष  </th>
										<th>अवशेष देय सेंटेज</th>
										<th>अवशेष का कारण </th>
									</tr>
									<tr>
										<?php
										for($i=1;$i<=16;$i++){
											echo '<th>'.$i.'</th>';
										}
										?>
									</tr>
								</thead>
								<tbody>
								<?php
								if($_SESSION['usertype']=='1'){
									$sql = 'SELECT `invoice_prarup_1`.`division_id`, `invoice_prarup_1`.`entry_date`, `trans_prarup_1`.`project_id`, `trans_prarup_1`.`prambhik_dey`, `trans_prarup_1`.`monthduemukhyalaya`, `trans_prarup_1`.`monthdueprakhand`, `trans_prarup_1`.`monthduemukhyalayasentage`, `trans_prarup_1`.`month`,  `trans_prarup_1`.`monthdueprakhandsentage`, `trans_prarup_1`.`monthsentageutr`, `trans_prarup_1`.`khamikdeymukhyalaya`, `trans_prarup_1`.`khamikdeyprakhand`, `trans_prarup_1`.`khamikpreshitmukhyalaya`, `trans_prarup_1`.`khamikpreshitprakhand`, `trans_prarup_1`.`remaindey`, `trans_prarup_1`.`remainreason` FROM `trans_prarup_1` LEFT JOIN `invoice_prarup_1` ON `invoice_prarup_1`.`sno` = `trans_prarup_1`.`invoice_id`where invoice_prarup_1.created_by = "'.$_SESSION['username'].'"';
									
									if(isset($_POST['search'])){
										if($_POST['project_id']!=''){
											$sql .= ' and trans_prarup_1.project_id="'.$_POST['project_id'].'"';
										}
										if($_POST['division_id']!=''){
											$sql .= ' and invoice_prarup_1.division_id="'.$_POST['division_id'].'"';
										}
										if($_POST['month']!=''){
											$sql .= ' and trans_prarup_1.month="'.$_POST['month'].'"';
										}
									}
									// echo $sql;

									$result_prarup_1 = execute_query($sql);
									$total_prambhik_dey = 0;
									$total_monthduemukhyalaya = 0;
									$total_monthdueprakhand = 0;
									$total_monthduemukhyalayasentage = 0;
									$total_monthduemukhyalayasentage = 0;
									$total_monthdueprakhandsentage = 0;
									$total_monthsentageutr = 0;
									$total_khamikdeymukhyalaya = 0;
									$total_khamikdeyprakhand = 0;
									$total_khamikpreshitmukhyalaya = 0;
									$total_khamikpreshitprakhand = 0;
									$total_remaindey = 0;
									$total_remainreason = 0;
									$i=1;
									while($row_prarup_1 = mysqli_fetch_assoc($result_prarup_1)){
										$sql = 'select * from uprnss_division where s_no="'.$row_prarup_1['division_id'].'"';
										$row_div = mysqli_fetch_assoc(execute_query($sql));
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
										$row_div = mysqli_fetch_assoc($row_div);
										}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
										
										$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="'.$row_prarup_1['project_id'].'")';
										$row_project = mysqli_fetch_assoc(execute_query($sql));
											$row_project = execute_query($sql);
											if(mysqli_num_rows($row_project)!=0){
											$row_project = mysqli_fetch_assoc($row_project);
											}
											else{
												unset($row_project);
												$row_project['project_name_hindi'] = '';
											}
											$sql = 'select * from uprnss_department_name where sno="'.$row_project['department_id'].'"';
											$row_dep = mysqli_fetch_assoc(execute_query($sql));
											$row_dep = execute_query($sql);
											if(mysqli_num_rows($row_dep)!=0){
											$row_dep = mysqli_fetch_assoc($row_dep);
											}
											else{
												unset($row_dep);
												$row_dep['department_name_hindi'] = '';
											}
											$total_prambhik_dey += floatval($row_prarup_1['prambhik_dey']);
											$total_monthduemukhyalaya += floatval($row_prarup_1['monthduemukhyalaya']);
											$total_monthdueprakhand += floatval($row_prarup_1['monthdueprakhand']);
											$total_monthduemukhyalayasentage += floatval($row_prarup_1['monthduemukhyalayasentage']);
											$total_monthdueprakhandsentage += floatval($row_prarup_1['monthdueprakhandsentage']);
											// $total_monthsentageutr += floatval($row_prarup_1['monthsentageutr']);
											$total_khamikdeymukhyalaya += floatval($row_prarup_1['khamikdeymukhyalaya']);
											$total_khamikdeyprakhand += floatval($row_prarup_1['khamikdeyprakhand']);
											$total_khamikpreshitmukhyalaya += floatval($row_prarup_1['khamikpreshitmukhyalaya']);
											$total_khamikpreshitprakhand += floatval($row_prarup_1['khamikpreshitprakhand']);
											$total_remaindey += floatval($row_prarup_1['remaindey']);
											$total_remainreason += floatval($row_prarup_1['remainreason']);
										
										echo '<tr>
											<td>'.$i++.'</td>
											<td>'.$row_div['division_name'].'</td>
											<td>'.$row_dep['department_name_hindi'].'<hr>'.$row_project['project_name_hindi'].'</td>
											<td>'.$row_prarup_1['month'].'</td>
											<td>'.$row_prarup_1['prambhik_dey'].'</td>
											
											<td>'.$row_prarup_1['monthduemukhyalaya'].'</td>
											<td>'.$row_prarup_1['monthdueprakhand'].'</td>
											<td>'.$row_prarup_1['monthduemukhyalayasentage'].'</td><td>'.$row_prarup_1['monthdueprakhandsentage'].'</td><td>'.$row_prarup_1['monthsentageutr'].'</td><td>'.$row_prarup_1['khamikdeymukhyalaya'].'</td><td>'.$row_prarup_1['khamikdeyprakhand'].'</td><td>'.$row_prarup_1['khamikpreshitmukhyalaya'].'</td><td>'.$row_prarup_1['khamikpreshitprakhand'].'</td><td>'.$row_prarup_1['remaindey'].'</td><td>'.$row_prarup_1['remainreason'].'</td></tr>';
									}
									
								}			
								else{
									$sql = 'SELECT `invoice_prarup_1`.`division_id`, `invoice_prarup_1`.`entry_date`, `trans_prarup_1`.`project_id`, `trans_prarup_1`.`prambhik_dey`, `trans_prarup_1`.`monthduemukhyalaya`, `trans_prarup_1`.`monthdueprakhand`, `trans_prarup_1`.`month`,  `trans_prarup_1`.`monthduemukhyalayasentage`, `trans_prarup_1`.`monthdueprakhandsentage`, `trans_prarup_1`.`monthsentageutr`, `trans_prarup_1`.`khamikdeymukhyalaya`, `trans_prarup_1`.`khamikdeyprakhand`, `trans_prarup_1`.`khamikpreshitmukhyalaya`, `trans_prarup_1`.`khamikpreshitprakhand`, `trans_prarup_1`.`remaindey`, `trans_prarup_1`.`remainreason` FROM `trans_prarup_1` LEFT JOIN `invoice_prarup_1` ON `invoice_prarup_1`.`sno` = `trans_prarup_1`.`invoice_id` where 1=1';
									
									if(isset($_POST['search'])){
										if($_POST['project_id']!=''){
											$sql .= ' and trans_prarup_1.project_id="'.$_POST['project_id'].'"';
										}
										if($_POST['division_id']!=''){
											$sql .= ' and invoice_prarup_1.division_id="'.$_POST['division_id'].'"';
										}
										if($_POST['month']!=''){
											$sql .= ' and trans_prarup_1.month="'.$_POST['month'].'"';
										}
									}
									$sql .= ' ORDER BY invoice_prarup_1.division_id';

									// echo $sql;
									
									$total_prambhik_dey = 0;
									$total_monthduemukhyalaya = 0;
									$total_monthdueprakhand = 0;
									$total_monthduemukhyalayasentage = 0;
									$total_monthduemukhyalayasentage = 0;
									$total_monthdueprakhandsentage = 0;
									$total_monthsentageutr = 0;
									$total_khamikdeymukhyalaya = 0;
									$total_khamikdeyprakhand = 0;
									$total_khamikpreshitmukhyalaya = 0;
									$total_khamikpreshitprakhand = 0;
									$total_remaindey = 0;
									$total_remainreason = 0;

									$result_prarup_1 = execute_query($sql); 
									$i=1;
									while($row_prarup_1 = mysqli_fetch_assoc($result_prarup_1)){
										$sql = 'select * from uprnss_division where s_no="'.$row_prarup_1['division_id'].'"';
										$row_div = mysqli_fetch_assoc(execute_query($sql));
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
										$row_div = mysqli_fetch_assoc($row_div);
										}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
										
										$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="'.$row_prarup_1['project_id'].'")';
										$row_project = mysqli_fetch_assoc(execute_query($sql));
											$row_project = execute_query($sql);
											if(mysqli_num_rows($row_project)!=0){
											$row_project = mysqli_fetch_assoc($row_project);
											}
											else{
												unset($row_project);
												$row_project['project_name_hindi'] = '';
											}
											$sql = 'select * from uprnss_department_name where sno="'.$row_project['department_id'].'"';
											$row_dep = mysqli_fetch_assoc(execute_query($sql));
											$row_dep = execute_query($sql);
											if(mysqli_num_rows($row_dep)!=0){
											$row_dep = mysqli_fetch_assoc($row_dep);
											}
											else{
												unset($row_dep);
												$row_dep['department_name_hindi'] = '';
											}
										$total_prambhik_dey += floatval($row_prarup_1['prambhik_dey']);
										$total_monthduemukhyalaya += floatval($row_prarup_1['monthduemukhyalaya']);
										$total_monthdueprakhand += floatval($row_prarup_1['monthdueprakhand']);
										$total_monthduemukhyalayasentage += floatval($row_prarup_1['monthduemukhyalayasentage']);
										$total_monthdueprakhandsentage += floatval($row_prarup_1['monthdueprakhandsentage']);
										// $total_monthsentageutr += floatval($row_prarup_1['monthsentageutr']);
										$total_khamikdeymukhyalaya += floatval($row_prarup_1['khamikdeymukhyalaya']);
										$total_khamikdeyprakhand += floatval($row_prarup_1['khamikdeyprakhand']);
										$total_khamikpreshitmukhyalaya += floatval($row_prarup_1['khamikpreshitmukhyalaya']);
										$total_khamikpreshitprakhand += floatval($row_prarup_1['khamikpreshitprakhand']);
										$total_remaindey += floatval($row_prarup_1['remaindey']);
										$total_remainreason += floatval($row_prarup_1['remainreason']);

										echo '<tr>
											<td>'.$i++.'</td>
											<td>'.$row_div['division_name'].'</td>
											<td>'.$row_dep['department_name_hindi'].'<hr>'.$row_project['project_name_hindi'].'</td>
											<td>'.$row_prarup_1['month'].'</td>
											<td>'.$row_prarup_1['prambhik_dey'].'</td>
											<td>'.$row_prarup_1['monthduemukhyalaya'].'</td>
											<td>'.$row_prarup_1['monthdueprakhand'].'</td>
											<td>'.$row_prarup_1['monthduemukhyalayasentage'].'</td>
											<td>'.$row_prarup_1['monthdueprakhandsentage'].'</td>
											<td>'.$row_prarup_1['monthsentageutr'].'</td>
											<td>'.$row_prarup_1['khamikdeymukhyalaya'].'</td>
											<td>'.$row_prarup_1['khamikdeyprakhand'].'</td>
											<td>'.$row_prarup_1['khamikpreshitmukhyalaya'].'</td>
											<td>'.$row_prarup_1['khamikpreshitprakhand'].'</td>
											<td>'.$row_prarup_1['remaindey'].'</td>
											<td>'.$row_prarup_1['remainreason'].'</td>
										</tr>';
									}
								}
								?>
								</tbody>
								<tfoot>
    <tr>
        <td colspan="4"> Total </td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_prambhik_dey; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_monthduemukhyalaya; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_monthdueprakhand; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_monthduemukhyalayasentage; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_monthdueprakhandsentage; ?></td>
        <td class="bold-text"> </td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_khamikdeymukhyalaya; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_khamikdeyprakhand; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_khamikpreshitmukhyalaya; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_khamikpreshitprakhand; ?></td>
        <td style="text-align: right; font-weight: bold;"> <?php echo $total_remaindey; ?></td>
        <!-- <td style="text-align: right; font-weight: bold;"> <?php echo $total_remainreason; ?></td> -->
    </tr>
</tfoot>

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