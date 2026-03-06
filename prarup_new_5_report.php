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
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">जी०एस०टी० टी०डी०एस० का विवरण वर्ष 2023-24 </h4></br>
					</div>
                            <?php echo $msg; ?>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover text-right">
							<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th></th>
									<th>प्रखण्ड का नाम </th>
									<th>माह</th>
									<th>देय जी०एस०टी० टी०डी०एस० का प्रारम्भिक अवशेष 01.04.2023</th>
									<th>माह में किया गया कुल भुगतान</th>
									<th>भुगतान के सापेक्ष काटी गई जी०एस०टी० टी०डी०एस० की राशि</th>
									<th>मुख्यालय को प्रेषित जी०एस०टी० टी०डी०एस० की राशि</th>
									<th>यू०टी०आर०नम्बर एवं दिनांक</th>
									<th>भुगतान के सापेक्ष काटी गई जी०एस०टी० टी०डी०एस० की क्रमिक राशि</th>
									<th>अवशेष</th>
								</tr>
								<tr>
									<?php 
										for ($i=1; $i<=10; $i++) {
											echo '<th>' .$i.'</th>';
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
								// Retrieve data based on filters
								if(isset($_POST['search'])){
									$filter_division = $_POST['division_id'];
									$filter_month = $_POST['month'];

									$sql = 'SELECT `invoice_prarup_new_5`.`sno`,`invoice_prarup_new_5`.`division_id`, `invoice_prarup_new_5`.`entry_date`, `trans_prarup_new_5`.`month`, `trans_prarup_new_5`.`avsheshdate`,  `trans_prarup_new_5`.`totelpayment`,  `trans_prarup_new_5`.`gsttdsbhuktan`, `trans_prarup_new_5`.`gsttdsrashi`, `trans_prarup_new_5`.`utrdate`, `trans_prarup_new_5`.`gsttdssno`, `trans_prarup_new_5`.`avshesh` FROM `trans_prarup_new_5` LEFT JOIN `invoice_prarup_new_5` ON `invoice_prarup_new_5`.`sno` = `trans_prarup_new_5`.`invoice_id` ';

									// Apply filters
									if(!empty($filter_division)) {
										$sql .= ' WHERE `invoice_prarup_new_5`.`division_id` = "'.$filter_division.'"';
									}
									if(!empty($filter_month)) {
										if(strpos($sql, 'WHERE') !== false) {
											$sql .= ' AND ';
										} else {
											$sql .= ' WHERE ';
										}
										$sql .= ' `trans_prarup_new_5`.`month` = "'.$filter_month.'"';
									}

									$result_prarup_new_5 = execute_query($sql);
									$a=1;
									$total_avsheshdate = 0;
									$total_totelpayment = 0;
									$total_gsttdsbhuktan = 0;
									$total_gsttdsrashi = 0;
									$total_utrdate = 0;
									$total_gsttdssno = 0;
									$total_avshesh = 0;
									$result_prarup_new_5 = execute_query($sql);
									
									while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
										$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
										$row_div = mysqli_fetch_assoc(execute_query($sql));
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
										$row_div = mysqli_fetch_assoc($row_div);
										}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
										// $total_month += floatval($row_prorup_new_5['month']);
										$total_avsheshdate += floatval($row_prarup_new_5['avsheshdate']);
										$total_totelpayment += floatval($row_prarup_new_5['totelpayment']);
										$total_gsttdsbhuktan += floatval($row_prarup_new_5['gsttdsbhuktan']);
										$total_gsttdsrashi += floatval($row_prarup_new_5['gsttdsrashi']);
										// $total_utrdate += floatval($row_prarup_new_5['utrdate']);
										$total_gsttdssno += floatval($row_prarup_new_5['gsttdssno']);
										$total_avshesh += floatval($row_prarup_new_5['avshesh']);

										echo '<tr>
											
											<td>'.$a++.'</td>
											<td>'.$row_div['division_name'].'</td>
											<td>'.$row_prarup_new_5['month'].'</td>
											<td>'.$row_prarup_new_5['avsheshdate'].'</td>
											<td>'.$row_prarup_new_5['totelpayment'].'</td>
											<td>'.$row_prarup_new_5['gsttdsbhuktan'].'</td>
											<td>'.$row_prarup_new_5['gsttdsrashi'].'</td>
											<td>'.$row_prarup_new_5['utrdate'].'</td>
											<td>'.$row_prarup_new_5['gsttdssno'].'</td>
											<td>'.$row_prarup_new_5['avshesh'].'</td>
										</tr>';
									}
								} else {
									// Fetch all data if no filters applied
									$sql = 'SELECT `invoice_prarup_new_5`.`sno`,`invoice_prarup_new_5`.`division_id`, `invoice_prarup_new_5`.`entry_date`, `trans_prarup_new_5`.`month`, `trans_prarup_new_5`.`avsheshdate`,  `trans_prarup_new_5`.`totelpayment`,  `trans_prarup_new_5`.`gsttdsbhuktan`, `trans_prarup_new_5`.`gsttdsrashi`, `trans_prarup_new_5`.`utrdate`, `trans_prarup_new_5`.`gsttdssno`, `trans_prarup_new_5`.`avshesh` FROM `trans_prarup_new_5` LEFT JOIN `invoice_prarup_new_5` ON `invoice_prarup_new_5`.`sno` = `trans_prarup_new_5`.`invoice_id` ';

									$result_prarup_new_5 = execute_query($sql);
									$a=1;
									$total_avsheshdate = 0;
									$total_totelpayment = 0;
									$total_gsttdsbhuktan = 0;
									$total_gsttdsrashi = 0;
									$total_utrdate = 0;
									$total_gsttdssno = 0;
									$total_avshesh = 0;
									$result_prarup_new_5 = execute_query($sql);
									
									while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
										$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
										$row_div = mysqli_fetch_assoc(execute_query($sql));
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
										$row_div = mysqli_fetch_assoc($row_div);
										}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
										// $total_month += floatval($row_prorup_new_5['month']);
										$total_avsheshdate += floatval($row_prarup_new_5['avsheshdate']);
										$total_totelpayment += floatval($row_prarup_new_5['totelpayment']);
										$total_gsttdsbhuktan += floatval($row_prarup_new_5['gsttdsbhuktan']);
										$total_gsttdsrashi += floatval($row_prarup_new_5['gsttdsrashi']);
										// $total_utrdate += floatval($row_prarup_new_5['utrdate']);
										$total_gsttdssno += floatval($row_prarup_new_5['gsttdssno']);
										$total_avshesh += floatval($row_prarup_new_5['avshesh']);

										echo '<tr>
											
											<td>'.$a++.'</td>
											<td>'.$row_div['division_name'].'</td>
											<td>'.$row_prarup_new_5['month'].'</td>
											<td>'.$row_prarup_new_5['avsheshdate'].'</td>
											<td>'.$row_prarup_new_5['totelpayment'].'</td>
											<td>'.$row_prarup_new_5['gsttdsbhuktan'].'</td>
											<td>'.$row_prarup_new_5['gsttdsrashi'].'</td>
											<td>'.$row_prarup_new_5['utrdate'].'</td>
											<td>'.$row_prarup_new_5['gsttdssno'].'</td>
											<td>'.$row_prarup_new_5['avshesh'].'</td>
										</tr>';
									}
								}
								?>							
							</tbody>
								<tfoot>
									<tr>
										<td class="bold-text" colspan="3"> योग </td>
										
										<td class="bold-text"> <?php echo $total_avsheshdate; ?> </td>
										<td class="bold-text"> <?php echo $total_totelpayment; ?> </td>
										<td class="bold-text"> <?php echo $total_gsttdsbhuktan; ?> </td>
										<td class="bold-text"> <?php echo $total_gsttdsrashi; ?> </td>
										<td class="bold-text"> </td>
										<td class="bold-text"> <?php echo $total_gsttdssno; ?> </td>
										<td class="bold-text"> <?php echo $total_avshesh; ?> </td>
									
								</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	<!-- </form> -->
 
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
