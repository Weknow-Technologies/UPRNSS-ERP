<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['bankname_1']!=""){
		$sql='insert into invoice_prarup_2 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_2_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			
			$inv_id = mysqli_insert_id($db);
			
			for($i=1; $i<=$_POST['prarup_2_id']; $i++){
				$sql = 'insert into trans_prarup_2 (`invoice_id`,`month`, `bankname`, `acctype`, `rate`, `startingavshesh`, `monthbyaj`, `kramikarjit`, `mukhyalayamahpreshit`, `utrdate`, `mukhyalayakramik`, `avsheshvyaj`, `resonavshesh`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['month_'.$i].'","'.$_POST['bankname_'.$i].'", "'.$_POST['acctype_'.$i].'", "'.$_POST['rate_'.$i].'", "'.$_POST['startingavshesh_'.$i].'", "'.$_POST['monthbyaj_'.$i].'", "'.$_POST['kramikarjit_'.$i].'", "'.$_POST['mukhyalayamahpreshit_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['mukhyalayakramik_'.$i].'", "'.$_POST['avsheshvyaj_'.$i].'", "'.$_POST['resonavshesh_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
	
		$sql = 'delete from invoice_prarup_2 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_2 where invoice_id="'.$_GET['delid'].'"';
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
    }
</style>

	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
			<div class="card card-body">    
        		<div class="row d-flex my-auto">    	
					
					<table width="100%" class="table table-striped table-hover rounded text-right" style="margin:0px; padding:0px;">
						<tr >
							<th> </th>							
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
							<th width="20%"></th>
							<th>माह</th>
							<th width="15%">
								<select name="month" id="month" class="form-control"  value="">
									<option value="">Select--	</option>
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
							<th width="25%"></th>
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
	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">मुख्यालय को ब्याज मद में धनराशि प्रेषण का विवरण वर्ष 2023-24 </h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th></th>
									<th>प्रखण्ड का नाम </th>
									<th>माह </th>
									<th>बैंक का नाम </th>
									<th>बचत / सावधि जमा संख्या एवं खाते का प्रकार </th>
									<th>ब्याज की दर </th>
									<th>प्रारम्भिक अवशेष 01.04. 2023 </th>
									<th>माह मे अर्जित ब्याज </th>
									<th>क्रमिक अर्जित ब्याज </th>
									<th>मुख्यालय को माह मे प्रेषित ब्याज  </th>
									<th>यू०टी०आर० नम्बर दिनांक  </th>
									<th>मुख्यालय को क्रमिक प्रेषित ब्याज </th>
									<th>अवशेष देय ब्याज </th>
									<th>अवशेष का कारण </th>
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
								if($_SESSION['usertype']=='1'){
									$sql = 'SELECT `invoice_prarup_2`.`division_id`, `invoice_prarup_2`.`entry_date`, `trans_prarup_2`.`bankname`, `trans_prarup_2`.`acctype`, `trans_prarup_2`.`month`, `trans_prarup_2`.`rate`, `trans_prarup_2`.`startingavshesh`, `trans_prarup_2`.`monthbyaj`, `trans_prarup_2`.`kramikarjit`, `trans_prarup_2`.`mukhyalayamahpreshit`, `trans_prarup_2`.`utrdate`, `trans_prarup_2`.`mukhyalayakramik`, `trans_prarup_2`.`avsheshvyaj`, `trans_prarup_2`.`resonavshesh` FROM `trans_prarup_2` LEFT JOIN `invoice_prarup_2` ON `invoice_prarup_2`.`sno` = `trans_prarup_2`.`invoice_id`where invoice_prarup_2.created_by = "'.$_SESSION['username'].'"';
									if(isset($_POST['search'])){
										if($_POST['division_id']!=''){
											$sql .= ' and invoice_prarup_2.division_id="'.$_POST['division_id'].'"';
										}
										if($_POST['month']!=''){
											$sql .= ' and trans_prarup_2.month="'.$_POST['month'].'"';
										}
									}
									$result_prarup_2 = execute_query($sql);
									
									$i=1;
									$total_rate = 0;
									$total_startingavshesh = 0;
									$total_monthbyaj = 0;
									$total_kramikarjit = 0;
									$total_mukhyalayamahpreshit = 0;
									$total_utrdate = 0;
									$total_mukhyalayakramik = 0;
									$total_avsheshvyaj = 0;
									$total_resonavshesh = 0;
									$a=1;
									while($row_prarup_2 = mysqli_fetch_assoc($result_prarup_2)){
										$sql = 'select * from uprnss_division where s_no="'.$row_prarup_2['division_id'].'"';
										$row_div = mysqli_fetch_assoc(execute_query($sql));
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
										$row_div = mysqli_fetch_assoc($row_div);
										}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
										// $total_rate += floatval($row_prarup_2['rate']);
										$total_startingavshesh += floatval($row_prarup_2['startingavshesh']);
										$total_monthbyaj += floatval($row_prarup_2['monthbyaj']);
										$total_kramikarjit += floatval($row_prarup_2['kramikarjit']);
										$total_mukhyalayamahpreshit += floatval($row_prarup_2['mukhyalayamahpreshit']);
										// $total_mukhyalayamahpreshit += floatval($row_prarup_2['']);
										$total_utrdate += floatval($row_prarup_2['utrdate']);
										$total_mukhyalayakramik += floatval($row_prarup_2['mukhyalayakramik']);
										$total_avsheshvyaj += floatval($row_prarup_2['avsheshvyaj']);
										$total_resonavshesh += floatval($row_prarup_2['resonavshesh']);										
										
										echo '<tr>
											<td>'.$a++.'</td>
											<td>'.$row_div['division_name'].'</td>
											<td>'.$row_prarup_2['month'].'</td>
											<td>'.$row_prarup_2['bankname'].'</td>
											<td>'.$row_prarup_2['acctype'].'</td>
											<td>'.$row_prarup_2['rate'].'</td>
											<td>'.$row_prarup_2['startingavshesh'].'</td>
											<td>'.$row_prarup_2['monthbyaj'].'</td>
											<td>'.$row_prarup_2['kramikarjit'].'</td>
											<td>'.$row_prarup_2['mukhyalayamahpreshit'].'</td>
											<td>'.$row_prarup_2['utrdate'].'</td>
											<td>'.$row_prarup_2['mukhyalayakramik'].'</td>
											<td>'.$row_prarup_2['avsheshvyaj'].'</td>
											<td>'.$row_prarup_2['resonavshesh'].'</td>
										</tr>';
									}	
								}			
								else{
									$sql = 'SELECT `invoice_prarup_2`.`division_id`, `invoice_prarup_2`.`entry_date`, `trans_prarup_2`.`month`,`trans_prarup_2`.`bankname`, `trans_prarup_2`.`acctype`, `trans_prarup_2`.`rate`, `trans_prarup_2`.`startingavshesh`, `trans_prarup_2`.`monthbyaj`, `trans_prarup_2`.`kramikarjit`, `trans_prarup_2`.`mukhyalayamahpreshit`, `trans_prarup_2`.`utrdate`, `trans_prarup_2`.`mukhyalayakramik`, `trans_prarup_2`.`avsheshvyaj`, `trans_prarup_2`.`resonavshesh` FROM `trans_prarup_2` LEFT JOIN `invoice_prarup_2` ON `invoice_prarup_2`.`sno` = `trans_prarup_2`.`invoice_id` where 1=1';
									if(isset($_POST['search'])){
										if($_POST['division_id']!=''){
											$sql .= ' and invoice_prarup_2.division_id="'.$_POST['division_id'].'"';
										}
										if($_POST['month']!=''){
											$sql .= ' and trans_prarup_2.month="'.$_POST['month'].'"';
										}
									}
									$i=1;
									$total_rate = 0;
									$total_startingavshesh = 0;
									$total_monthbyaj = 0;
									$total_kramikarjit = 0;
									$total_mukhyalayamahpreshit = 0;
									$total_utrdate = 0;
									$total_mukhyalayakramik = 0;
									$total_avsheshvyaj = 0;
									$total_resonavshesh = 0;

									$result_prarup_2 = execute_query($sql);
									$a=1;
									
									while($row_prarup_2 = mysqli_fetch_assoc($result_prarup_2)){
										$sql = 'select * from uprnss_division where s_no="'.$row_prarup_2['division_id'].'"';
										$row_div = mysqli_fetch_assoc(execute_query($sql));
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
										$row_div = mysqli_fetch_assoc($row_div);
										}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
										
										// $total_rate += floatval($row_prarup_2['rate']);
										$total_startingavshesh += floatval($row_prarup_2['startingavshesh']);
										$total_monthbyaj += floatval($row_prarup_2['monthbyaj']);
										$total_kramikarjit += floatval($row_prarup_2['kramikarjit']);
										$total_mukhyalayamahpreshit += floatval($row_prarup_2['mukhyalayamahpreshit']);
										// $total_mukhyalayamahpreshit += floatval($row_prarup_2['']);
										$total_utrdate += floatval($row_prarup_2['utrdate']);
										$total_mukhyalayakramik += floatval($row_prarup_2['mukhyalayakramik']);
										$total_avsheshvyaj += floatval($row_prarup_2['avsheshvyaj']);
										$total_resonavshesh += floatval($row_prarup_2['resonavshesh']);

										echo '<tr>
											<td>'.$a++.'</td>
											<td>'.$row_div['division_name'].'</td>
											<td>'.$row_prarup_2['month'].'</td>
											<td>'.$row_prarup_2['bankname'].'</td>
											<td>'.$row_prarup_2['acctype'].'</td>
											<td>'.$row_prarup_2['rate'].'</td>
											<td>'.$row_prarup_2['startingavshesh'].'</td>
											<td>'.$row_prarup_2['monthbyaj'].'</td>
											<td>'.$row_prarup_2['kramikarjit'].'</td>
											<td>'.$row_prarup_2['mukhyalayamahpreshit'].'</td>
											<td>'.$row_prarup_2['utrdate'].'</td>
											<td>'.$row_prarup_2['mukhyalayakramik'].'</td>
											<td>'.$row_prarup_2['avsheshvyaj'].'</td>
											<td>'.$row_prarup_2['resonavshesh'].'</td>
										</tr>';
									}
								}
								?>
							</tbody>
								<tfoot>
									<tr>
										<td class="bold-text" colspan="5"> योग </td>
										<td></td>
										<td class="bold-text"> <?php echo $total_startingavshesh; ?></td>
										<td class="bold-text"> <?php echo $total_monthbyaj; ?></td>
										<td class="bold-text"> <?php echo $total_kramikarjit; ?></td>
										<td class="bold-text"> <?php echo $total_mukhyalayamahpreshit; ?></td>
										<td class="bold-text"> </td>
										<td class="bold-text"> <?php echo $total_mukhyalayakramik; ?></td>
										<td class="bold-text"> <?php echo $total_avsheshvyaj; ?></td>
										<td class="bold-text"> </td>
										
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