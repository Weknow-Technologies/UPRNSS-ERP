<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';
	
if(isset($_POST['submit'])){
	if($_POST['project_id_1']!=""){
		$sql='insert into invoice_prarup_3 (entry_date, total_trans, division_id, created_by, creation_time, status) values("'.date("Y-m-d").'", "'.$_POST['prarup_3_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'" , "'.$_POST['status'].'");';
		
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			
			$inv_id = mysqli_insert_id($db);
			
			for($i=1; $i<=$_POST['prarup_3_id']; $i++){
				if($_POST['yojnaname_'.$i]!= ""){
					$sql = 'insert into trans_prarup_3 (`invoice_id`, `yojnaname`, `yojnalagat`, `getdhanrashi`, `dateofcredit`, `utrnum`, `mukhyalayaagrimsentage`, `mukhyalayawithhold`, `gsttds`, `leboreses`, `mukhyalaya_gstdeduction`, `totalrupees`, `avsheshdhanrashi`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['yojnaname_'.$i].'", "'.$_POST['yojnalagat_'.$i].'", "'.$_POST['getdhanrashi_'.$i].'", "'.$_POST['dateofcredit_'.$i].'", "'.$_POST['utrnum_'.$i].'", "'.$_POST['mukhyalayaagrimsentage_'.$i].'", "'.$_POST['mukhyalayawithhold_'.$i].'", "'.$_POST['gsttds_'.$i].'", "'.$_POST['leboreses_'.$i].'", "'.$_POST['mukhyalaya_gstdeduction_'.$i].'", "'.$_POST['totalrupees_'.$i].'", "'.$_POST['avsheshdhanrashi_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
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
	
		$sql = 'delete from invoice_prarup_3 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_3 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
		
	}
?>
<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>

<style>
    .bold-text {
        font-weight: bold;
    }
</style>

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
						<h4 class="card-title text-center">निर्माण कार्य हेतु मुख्यालय से प्राप्त धनराशि का विवरण वर्ष 2023-24 </h4></br>
					</div>
					<div class="card-body">
						 <table class="table table-striped table-bordered table-hover" id="general_stat_table">
							<thead>
								<tr>
									<th>SNo.</th>
									<th>Type</th>
									<th>प्रखंड</th>
									<th>योजना का नाम </th>
									<th>योजना की लागत </th>
									<th>प्राप्त धनराशि </th>
									<th>बैंक में क्रेडिट होने का दिनांक</th>
									<th>यू०टी०आर० नम्बर </th>
									<th>मुख्यालय द्वारा अग्रिम सेंटेज कटौती </th>
									<th>मुख्यालय द्वारा विदहेल्ड आयकर </th>
									<th>जी०एस०टी० टी०डी०एस०</th>
									<th>लेबर सेस</th>
									<th>मुख्यालय द्वारा अग्रिम GST कटौती</th>
									<th>कुल धनराशि  </th>
									<th>अवशेष धनराशि</th>
								</tr>
								<tr>
									<?php
									for($i=1;$i<=14;$i++){
										echo '<th>'.$i.'</th>';
									}
									?>
								</tr>
							</thead>
							<tbody>
							<?php
							// echo $_SESSION['usertype'];
							// if($_SESSION['usertype']=='1' || $_SESSION ['usertype']=='9'){
							// 	 $sql = 'SELECT *  FROM `trans_prarup_3` LEFT JOIN `invoice_prarup_3` ON `invoice_prarup_3`.`sno` = `trans_prarup_3`.`invoice_id` WHERE invoice_prarup_3.division_id in ('.implode(",", $_SESSION['divisions']).')';

							// }else{
							// 	$sql = 'SELECT *  FROM `trans_prarup_3` LEFT JOIN `invoice_prarup_3` ON `invoice_prarup_3`.`sno` = `trans_prarup_3`.`invoice_id`';
							// }

							if($_SESSION['usertype']=='1'){
								$sql = 'SELECT `invoice_prarup_3`.`division_id`, `invoice_prarup_3`.`entry_date`, `trans_prarup_3`.`yojnaname`, `trans_prarup_3`.`yojnalagat`, `trans_prarup_3`.`getdhanrashi`,`trans_prarup_3`.`dateofcredit`, `trans_prarup_3`.`utrnum`, `trans_prarup_3`.`mukhyalayaagrimsentage`, `trans_prarup_3`.`mukhyalayawithhold`, `trans_prarup_3`.`gsttds`, `trans_prarup_3`.`leboreses`, `trans_prarup_3`.`mukhyalaya_gstdeduction`, `trans_prarup_3`.`totalrupees`, `trans_prarup_3`.`avsheshdhanrashi` FROM `trans_prarup_3` LEFT JOIN `invoice_prarup_3` ON `invoice_prarup_3`.`sno` = `trans_prarup_3`.`invoice_id`where invoice_prarup_3.created_by = "'.$_SESSION['username'].'"';
								if(isset($_POST['search'])){
									if($_POST['division_id']!=''){
										$sql .= ' and invoice_prarup_3.division_id="'.$_POST['division_id'].'"';
									}
									if($_POST['month']!=''){
										$sql .= ' and trans_prarup_3.month="'.$_POST['month'].'"';
									}
								}
								$result_prarup_3 = execute_query($sql);							
								
								$total_yojnalagat = 0;
								$total_getdhanrashi = 0;
								$total_dateofcredit = 0;
								$total_utrnum = 0;
								$total_mukhyalayaagrimsentage = 0;
								$total_mukhyalayawithhold = 0;
								$total_gsttds = 0;
								$total_leboreses = 0;
								$total_mukhyalaya_gstdeduction = 0;
								$total_totalrupees = 0;
								$total_avsheshdhanrashi = 0;
								
								// $result_prarup_3 = execute_query($sql);
								$a=1;
								while($row_prarup_3 = mysqli_fetch_assoc($result_prarup_3)){
									$sql = 'select * from uprnss_division where s_no="'.$row_prarup_3['division_id'].'"';
									$row_div = mysqli_fetch_assoc(execute_query($sql));
									$row_div = execute_query($sql);
									if(mysqli_num_rows($row_div)!=0){
									$row_div = mysqli_fetch_assoc($row_div);
									}
									else{
										unset($row_div);
										$row_div['division_name'] = '';
									}


									$sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="'.$row_prarup_3['yojnaname'].'")';
									$row_project = mysqli_fetch_assoc(execute_query($sql));
										$row_project = execute_query($sql);
										if(mysqli_num_rows($row_project)!=0){
										$row_project = mysqli_fetch_assoc($row_project);
										}
										else{
											unset($row_project);
											$row_project['project_name_hindi'] = '';
										}
										$total_yojnalagat += floatval($row_prarup_3['yojnalagat']);
										$total_getdhanrashi += floatval($row_prarup_3['getdhanrashi']);
										// $total_dateofcredit += floatval($row_prarup_3['dateofcredit']);
										$total_utrnum += floatval($row_prarup_3['utrnum']);
										$total_mukhyalayaagrimsentage += floatval($row_prarup_3['mukhyalayaagrimsentage']);
										$total_mukhyalayawithhold += floatval($row_prarup_3['mukhyalayawithhold']);
										$total_gsttds += floatval($row_prarup_3['gsttds']);
										$total_leboreses += floatval($row_prarup_3['leboreses']);
										$total_mukhyalaya_gstdeduction += floatval($row_prarup_3['mukhyalaya_gstdeduction']);
										$total_totalrupees += floatval($row_prarup_3['totalrupees']);
										$total_avsheshdhanrashi += floatval($row_prarup_3['avsheshdhanrashi']);

									
									echo '<tr>
									<td>'.$a++.'</td>								
									<td>'.$row_div['division_name'].'</td> 								
									<td>'.$row_project['project_name_hindi'].'</td>
									<td>'.$row_prarup_3['yojnalagat'].'</td>
									<td>'.$row_prarup_3['getdhanrashi'].'</td> 
									<td>'.$row_prarup_3['dateofcredit'].'</td> 
									<td>'.$row_prarup_3['utrnum'].'</td> 
									<td>'.$row_prarup_3['mukhyalayaagrimsentage'].'</td>
									<td>'.$row_prarup_3['mukhyalayawithhold'].'</td> 
									<td>'.$row_prarup_3['gsttds'].'</td> 
									<td>'.$row_prarup_3['leboreses'].'</td> 
									<td>'.$row_prarup_3['mukhyalaya_gstdeduction'].'</td> 
									<td>'.$row_prarup_3['totalrupees'].'</td>
									<td>'.$row_prarup_3['avsheshdhanrashi'].'</td> 
									</tr>';
								}
							}
							else{
								$sql = '(SELECT "Manual" as type, `invoice_prarup_3`.`division_id`, `invoice_prarup_3`.`entry_date`, `trans_prarup_3`.`yojnaname`, `trans_prarup_3`.`yojnalagat`, `trans_prarup_3`.`getdhanrashi`,`trans_prarup_3`.`dateofcredit`, `trans_prarup_3`.`utrnum`, `trans_prarup_3`.`mukhyalayaagrimsentage`, `trans_prarup_3`.`mukhyalayawithhold`, `trans_prarup_3`.`gsttds`, `trans_prarup_3`.`leboreses`, `trans_prarup_3`.`mukhyalaya_gstdeduction`, `trans_prarup_3`.`totalrupees`, `trans_prarup_3`.`avsheshdhanrashi` FROM `trans_prarup_3` LEFT JOIN `invoice_prarup_3` ON `invoice_prarup_3`.`sno` = `trans_prarup_3`.`invoice_id`)
								
								union all
								
								(SELECT "Automatic" as type, unit_id as `division_id`, transafer_date as `entry_date`, project_name as `yojnaname`, "" as `yojnalagat`, praposemoney as `getdhanrashi`, transafer_date as `dateofcredit`, "" as `utrnum`, sentage as `mukhyalayaagrimsentage`, incometax as `mukhyalayawithhold`, gsttds as `gsttds`, leborses as `leboreses`, gstdeduction as `mukhyalaya_gstdeduction`, transafer_amount as `totalrupees`, "" as `avsheshdhanrashi` FROM `invoice_account_fund_transafer`) ';
								if(isset($_POST['search'])){
									if($_POST['division_id']!=''){
										$sql .= ' and invoice_prarup_3.division_id="'.$_POST['division_id'].'"';
									}
									if($_POST['month']!=''){
										$sql .= ' and trans_prarup_3.month="'.$_POST['month'].'"';
									}
								}
								$sql .= ' order by entry_date desc';
								$result_prarup_3 = execute_query($sql);							
								
								$total_yojnalagat = 0;
								$total_getdhanrashi = 0;
								// $total_dateofcredit = 0;
								$total_utrnum = 0;
								$total_mukhyalayaagrimsentage = 0;
								$total_mukhyalayawithhold = 0;
								$total_gsttds = 0;
								$total_leboreses = 0;
								$total_mukhyalaya_gstdeduction = 0;
								$total_totalrupees = 0;
								$total_avsheshdhanrashi = 0;
								
								// $result_prarup_3 = execute_query($sql);
								$a=1;
								while($row_prarup_3 = mysqli_fetch_assoc($result_prarup_3)){
									$sql = 'select * from uprnss_division where s_no="'.$row_prarup_3['division_id'].'"';
									$row_div = mysqli_fetch_assoc(execute_query($sql));
									$row_div = execute_query($sql);
									if(mysqli_num_rows($row_div)!=0){
									$row_div = mysqli_fetch_assoc($row_div);
									}
									else{
										unset($row_div);
										$row_div['division_name'] = '';
									}


									$sql = '(SELECT sno,department_id, project_name_hindi, Project_name, sanction_cost, revised_cost FROM uprnss_project_temp WHERE sno ="'.$row_prarup_3['yojnaname'].'")';
									//echo $sql.'<br>';
									$row_project = mysqli_fetch_assoc(execute_query($sql));
									$row_project = execute_query($sql);
									if(mysqli_num_rows($row_project)!=0){
										$row_project = mysqli_fetch_assoc($row_project);
									}
									else{
										unset($row_project);
										$row_project['project_name_hindi'] = '';
									}
									$total_yojnalagat += floatval($row_prarup_3['yojnalagat']);
									$total_getdhanrashi += floatval($row_prarup_3['getdhanrashi']);
									// $total_dateofcredit += floatval($row_prarup_3['dateofcredit']);
									$total_utrnum += floatval($row_prarup_3['utrnum']);
									$total_mukhyalayaagrimsentage += floatval($row_prarup_3['mukhyalayaagrimsentage']);
									$total_mukhyalayawithhold += floatval($row_prarup_3['mukhyalayawithhold']);
									$total_gsttds += floatval($row_prarup_3['gsttds']);
									$total_leboreses += floatval($row_prarup_3['leboreses']);
									$total_mukhyalaya_gstdeduction += floatval($row_prarup_3['mukhyalaya_gstdeduction']);
									$total_totalrupees += floatval($row_prarup_3['totalrupees']);
									$total_avsheshdhanrashi += floatval($row_prarup_3['avsheshdhanrashi']);
									
									if($row_prarup_3['type']=='Automatic'){
										$cost = $row_project['revised_cost']!=''?$row_project['revised_cost']:$row_project['sanction_cost'];
										$row_prarup_3['yojnalagat'] = (float)$cost*100000;
										$row_prarup_3['avsheshdhanrashi'] = (float)$row_prarup_3['yojnalagat']-(float)$row_prarup_3['totalrupees'];
									}

									
									echo '<tr>
									<td>'.$a++.'</td>						
									<td>'.$row_prarup_3['type'].'</td>
									<td>'.$row_div['division_name'].'</td> 								
									<td>'.$row_project['project_name_hindi'].'</td>
									<td>'.$row_prarup_3['yojnalagat'].'</td>
									<td>'.$row_prarup_3['getdhanrashi'].'</td> 
									<td>'.$row_prarup_3['dateofcredit'].'</td> 
									<td>'.$row_prarup_3['utrnum'].'</td> 
									<td>'.$row_prarup_3['mukhyalayaagrimsentage'].'</td>
									<td>'.$row_prarup_3['mukhyalayawithhold'].'</td> 
									<td>'.$row_prarup_3['gsttds'].'</td> 
									<td>'.$row_prarup_3['leboreses'].'</td> 
									<td>'.$row_prarup_3['mukhyalaya_gstdeduction'].'</td> 
									<td>'.$row_prarup_3['totalrupees'].'</td>
									<td>'.$row_prarup_3['avsheshdhanrashi'].'</td> 
									</tr>';
								}
							}							
							?>
							</tbody>
								<tfoot>
									<tr>
										<td class="bold-text" colspan="3"> योग  </td>
										<td class="bold-text"> <?php echo $total_yojnalagat; ?> </td>
										<td class="bold-text"> <?php echo $total_getdhanrashi; ?> </td>
										<td class="bold-text"></td>	
										<td class="bold-text"> </td>
										<td class="bold-text"> <?php echo $total_mukhyalayaagrimsentage; ?> </td>
										<td class="bold-text"> <?php echo $total_mukhyalayawithhold; ?> </td>
										<td class="bold-text"> <?php echo $total_gsttds; ?> </td>
										<td class="bold-text"> <?php echo $total_leboreses; ?> </td>
										<td class="bold-text"> <?php echo $total_mukhyalaya_gstdeduction; ?> </td>
										<td class="bold-text"> <?php echo $total_totalrupees; ?> </td>
										<td class="bold-text"> <?php echo $total_avsheshdhanrashi; ?> </td>
									</tr>
								</tfoot>
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

$(document).ready( function () {
    $('#general_stat_table').DataTable({
			paging: false,
			fixedHeader: true,
			// colReorder: true,
			// scrollX: true, // Enable horizontal scrolling if needed
	});
	
});
</script>

<?php		
page_footer_end();
?>