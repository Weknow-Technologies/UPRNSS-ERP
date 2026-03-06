<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';
?>

<style>
    .bold-text {
        font-weight: bold;
    }
</style>

<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>
	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					
					<table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">
						<tr >
							<th width="5%"></th>
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
							<th width="15%"></th>
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
						<h4 class="card-title text-center">मुख्यालय को टेंडर फीस का विवरण  </h4></br>
					</div>
						<?php //echo $msg; ?>
					<div class="card-body">

						<table class="table table-striped table-bordered table-hover text-right" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th>Sno.</th>
									<th>प्रखण्ड</th>
									<th>माह</th>
									<th>देय टेण्डर फीस का प्रारम्भिक अवशेष 01.04.2023</th>
									<th>टेण्डर के सापेक्ष माह में प्राप्त टेण्डर फीस</th>
									<th>टेण्डर के सापेक्ष क्रमिक प्राप्त टेण्डर फीस</th>
									<th>माह में मुख्यालय को प्रेषित टेण्डर फीस</th>
									<th>यू०टी०आर०नम्बर एवं दिनांक</th>
									<th>मुख्यालय को क्रमिक प्रेषित टेण्डर फीस</th>
									<th>अवशेष टेण्डर फीस</th>
									<th>अन्य विवरण</th>
								</tr>
								<tr>
								<?php
									for($i=1;$i<=11;$i++){
										echo '<th>'.$i.'</th>';
									}
									?>
								</tr>
							</thead>
							<tbody>
							<?php
							// echo $_SESSION['usertype'];
							if($_SESSION['usertype']=='1' || $_SESSION['usertype']=='9'){
								$sql = 'SELECT * FROM `trans_prarup_new_6` LEFT JOIN `invoice_prarup_new_6` ON `invoice_prarup_new_6`.`sno` = `trans_prarup_new_6`.`invoice_id` WHERE invoice_prarup_new_6.division_id in ('.implode(",", $_SESSION['divisions']).')';
							}else{
								$sql = 'SELECT * FROM `trans_prarup_new_6` LEFT JOIN `invoice_prarup_new_6` ON `invoice_prarup_new_6`.`sno` = `trans_prarup_new_6`.`invoice_id` where 1=1';
							}
							if(isset($_POST['search'])){
								
								if($_POST['division_id']!=''){
									$sql .= ' and invoice_prarup_new_6.division_id="'.$_POST['division_id'].'"';
								}
								if($_POST['month']!=''){
									$sql .= ' and trans_prarup_new_6.month="'.$_POST['month'].'"';
								}
							}
							
							$total_month = 0;
							$total_avsheshdate = 0;
							$total_totelpayment = 0;
							$total_tenderfee = 0;
							$total_tenderfeemonth = 0;
							$total_utrdate = 0;
							$total_maintenderfee = 0;
							$total_avsheshtenderfee = 0;
							$total_otherremark = 0;
							
							
							$result_prarup_new_6 = execute_query($sql);
							$a=1;
							while($row_prarup_new_6 = mysqli_fetch_assoc($result_prarup_new_6)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_6['division_id'].'"';
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								$total_month += floatval($row_prarup_new_6['month']);
								$total_avsheshdate += floatval($row_prarup_new_6['avsheshdate']);
								$total_totelpayment += floatval($row_prarup_new_6['totelpayment']);
								$total_tenderfee += floatval($row_prarup_new_6['tenderfee']);
								$total_tenderfeemonth += floatval($row_prarup_new_6['tenderfeemonth']);

								$total_maintenderfee += floatval($row_prarup_new_6['maintenderfee']);
								$total_avsheshtenderfee += floatval($row_prarup_new_6['avsheshtenderfee']);
								$total_otherremark += floatval($row_prarup_new_6['otherremark']);

								echo '<tr>

									<td>'.$a++.'</td>
									<td>'.$row_div['division_name'] .'</td>
									<td>'.$row_prarup_new_6['month'].'</td>
									<td>'.$row_prarup_new_6['avsheshdate'].'</td>
									<td>'.$row_prarup_new_6['totelpayment'].'</td>
									<td>'.$row_prarup_new_6['tenderfee'].'</td>
									<td>'.$row_prarup_new_6['tenderfeemonth'].'</td>'; ?>
									<td>
										<?php
										
										echo  $row_prarup_new_6['utrnumber'];echo '</br>';
										if (!empty($row_prarup_new_6['utrdate'])) {
											echo date('d-m-Y', strtotime($row_prarup_new_6['utrdate'])); 
										} else {
											echo '-';
											}
										?>
									<?php echo'
									</td>
									<td>'.$row_prarup_new_6['maintenderfee'].'</td>
									<td>'.$row_prarup_new_6['avsheshtenderfee'].'</td>
									<td>'.$row_prarup_new_6['otherremark'].'</td>
								</tr>';
							}
							?>
							</tbody>
							<tfoot>
								<tr>
									<td class="bold-text" colspan="3"> योग </td>
									
									<td class="bold-text"> <?php echo $total_avsheshdate; ?> </td>
									<td class="bold-text"> <?php echo $total_totelpayment; ?> </td>
									<td class="bold-text"> <?php echo $total_tenderfee; ?> </td>
									<td class="bold-text"> <?php echo $total_tenderfeemonth; ?> </td>
									<td class="bold-text"> </td>
									<td class="bold-text"> <?php echo $total_maintenderfee; ?> </td>
									<td class="bold-text"> <?php echo $total_avsheshtenderfee; ?> </td>
									<td class="bold-text"> </td>
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

$(document).ready( function () {
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