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
						
					</div>
			</div>
		</div>
		</form>
	</div>		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्डों के अभिलेखों में बचत खाता एवं सावधि जमा में धनराशि का विवरण</h4></br>
					</div>
						<?php echo $msg; ?>
					<div class="card-body">
					<table class="table table-striped table-bordered table-hover text-right">
					<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							<th>SNo.</th>
							<th>प्रखंड</th>
							<th>बैंक का नाम, शाखा एवं जनपद</th>
							<th>खाता संख्या</th>
							<th>बचत खाते में धनराशि</th>
							<th>सावधि खाते में धनराशि</th>
							<th>योग</th>
							<th>बचत / सावधि की दर</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if($_SESSION['usertype']=='1'){
						$sql = 'SELECT `invoice_prarup_new_11`.`division_id`, `invoice_prarup_new_11`.`entry_date`,`trans_prarup_new_11`.`bankinfo`,`trans_prarup_new_11`.`branch`,`trans_prarup_new_11`.`accnum`, `trans_prarup_new_11`.`dhanrashi`, `trans_prarup_new_11`.`savadhidhanrashi`, `trans_prarup_new_11`.`sum`, `trans_prarup_new_11`.`rate`
						 FROM `trans_prarup_new_11` LEFT JOIN `invoice_prarup_new_11` ON `invoice_prarup_new_11`.`sno` = `trans_prarup_new_11`.`invoice_id`where invoice_prarup_new_11.created_by = "'.$_SESSION['username'].'"';
						if(isset($_POST['search'])){
							if($_POST['division_id']!=''){
								$sql .= ' and invoice_prarup_new_11.division_id="'.$_POST['division_id'].'"';
							}
							if($_POST['month']!=''){
								$sql .= ' and trans_prarup_new_11.month="'.$_POST['month'].'"';
							}
						}

					$total_bankinfo = 0;
					$total_accnum = 0;
					$total_dhanrashi = 0;
					$total_savadhidhanrashi = 0;
					$total_sum = 0;
					$total_rate = 0;


					$result_prarup_new_11 = execute_query($sql);
					while($row_prarup_new_11 = mysqli_fetch_assoc($result_prarup_new_11)){
						$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_11['division_id'].'"';

						$row_div = mysqli_fetch_assoc(execute_query($sql));
						$row_div = execute_query($sql);
						if(mysqli_num_rows($row_div)!=0){
						$row_div = mysqli_fetch_assoc($row_div);
						}
						else{
							unset($row_div);
							$row_div['division_name'] = '';
						}

						// $total_bankinfo += floatval($row_prarup_new_11['bankinfo']);
						$total_accnum += floatval($row_prarup_new_11['accnum']);
						$total_dhanrashi += floatval($row_prarup_new_11['dhanrashi']);
						$total_savadhidhanrashi += floatval($row_prarup_new_11['savadhidhanrashi']);
						$total_sum += floatval($row_prarup_new_11['sum']);
						$total_rate += floatval($row_prarup_new_11['rate']);
						
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_11['bankinfo'].','.$row_prarup_new_11['branch'].'</td>
								<td>'.$row_prarup_new_11['accnum'].'</td>
								<td>'.$row_prarup_new_11['dhanrashi'].'</td>
								<td>'.$row_prarup_new_11['savadhidhanrashi'].'</td>
								<td>'.$row_prarup_new_11['sum'].'</td>
								<td>'.$row_prarup_new_11['rate'].'</td>
							</tr>';
					}
				}
				else{
					$sql = 'SELECT `invoice_prarup_new_11`.`division_id`, `invoice_prarup_new_11`.`entry_date`,`trans_prarup_new_11`.`bankinfo`,`trans_prarup_new_11`.`branch`,`trans_prarup_new_11`.`accnum`, `trans_prarup_new_11`.`dhanrashi`, `trans_prarup_new_11`.`savadhidhanrashi`, `trans_prarup_new_11`.`sum`, `trans_prarup_new_11`.`rate`
						 FROM `trans_prarup_new_11` LEFT JOIN `invoice_prarup_new_11` ON `invoice_prarup_new_11`.`sno` = `trans_prarup_new_11`.`invoice_id`where invoice_prarup_new_11.created_by = "'.$_SESSION['username'].'"';
						if(isset($_POST['search'])){
							if($_POST['division_id']!=''){
								$sql .= ' and invoice_prarup_new_11.division_id="'.$_POST['division_id'].'"';
							}
							if($_POST['month']!=''){
								$sql .= ' and trans_prarup_new_11.month="'.$_POST['month'].'"';
							}
						}

					$total_bankinfo = 0;
					$total_accnum = 0;
					$total_dhanrashi = 0;
					$total_savadhidhanrashi = 0;
					$total_sum = 0;
					$total_rate = 0;


					$result_prarup_new_11 = execute_query($sql);
					while($row_prarup_new_11 = mysqli_fetch_assoc($result_prarup_new_11)){
						$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_11['division_id'].'"';

						$row_div = mysqli_fetch_assoc(execute_query($sql));
						$row_div = execute_query($sql);
						if(mysqli_num_rows($row_div)!=0){
						$row_div = mysqli_fetch_assoc($row_div);
						}
						else{
							unset($row_div);
							$row_div['division_name'] = '';
						}

						// $total_bankinfo += floatval($row_prarup_new_11['bankinfo']);
						$total_accnum += floatval($row_prarup_new_11['accnum']);
						$total_dhanrashi += floatval($row_prarup_new_11['dhanrashi']);
						$total_savadhidhanrashi += floatval($row_prarup_new_11['savadhidhanrashi']);
						$total_sum += floatval($row_prarup_new_11['sum']);
						$total_rate += floatval($row_prarup_new_11['rate']);
						
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_11['bankinfo'].','.$row_prarup_new_11['branch'].'</td>
								<td>'.$row_prarup_new_11['accnum'].'</td>
								<td>'.$row_prarup_new_11['dhanrashi'].'</td>
								<td>'.$row_prarup_new_11['savadhidhanrashi'].'</td>
								<td>'.$row_prarup_new_11['sum'].'</td>
								<td>'.$row_prarup_new_11['rate'].'</td>
							</tr>';
					}
				}

					?>
					</tbody>
						<tfoot>
							<th class="bold-text" colspan="2"> योग </th>
							<td class="bold-text"> </td>
							<td class="bold-text"> </td>
							<td class="bold-text"> <?php echo $total_dhanrashi; ?> </td>
							<td class="bold-text"> <?php echo $total_savadhidhanrashi; ?> </td>
							<td class="bold-text"> <?php echo $total_sum; ?> </td>
							<td class="bold-text"> <?php echo $total_rate; ?> </td>
						</tfoot>
				</table>
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