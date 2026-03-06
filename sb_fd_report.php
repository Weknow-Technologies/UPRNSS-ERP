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

	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्डों के अभिलेखों में बचत खाता में धनराशि का विवरण</h4></br>
					</div>
						<?php echo $msg; ?>
					<div class="card-body">
					<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>SNo.</th>
							<th>प्रखंड</th>
							<th>खाता का प्रकार</th>
							<th>बैंक का नाम, शाखा </th>
							<th>आईएफएससी कोड </th>
							<th>खाता संख्या</th>
							<th>खाते में धनराशि</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if($_SESSION['usertype']=='1' || $_SESSION ['usertype']=='9'){
						$sql = 'SELECT sb_details_trans.sno as sno,`invoice_id`, `acc_type`, `bankinfo`, `branch`, `ifsc_code`, `accnum`, `closing_amt`, division_id  FROM `sb_details_trans` LEFT JOIN `sb_fd_acount_details_invoice` ON `sb_fd_acount_details_invoice`.`sno` = `sb_details_trans`.`invoice_id` WHERE sb_fd_acount_details_invoice.division_id in ('.implode(",", $_SESSION['divisions']).')';

					}else{
						$sql = 'SELECT sb_details_trans.sno as sno,`invoice_id`, `acc_type`, `bankinfo`, `branch`, `ifsc_code`, `accnum`, `closing_amt`, division_id  FROM `sb_details_trans` LEFT JOIN `sb_fd_acount_details_invoice` ON `sb_fd_acount_details_invoice`.`sno` = `sb_details_trans`.`invoice_id`';
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
						
						$total_dhanrashi += floatval($row_prarup_new_11['closing_amt']);
						
						
						echo '<tr>
						
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_11['acc_type'].'</td>
								<td><b>'.$row_prarup_new_11['bankinfo'].'</b>('.$row_prarup_new_11['branch'].')</td>
								<td>'.$row_prarup_new_11['ifsc_code'].'</td>
								<td>'.$row_prarup_new_11['accnum'].'</td>
								<td class="text-right">'.$row_prarup_new_11['closing_amt'].'</td>
							</tr>';
					}
					?>
					</tbody>
						<tfoot>
							<th class="bold-text" colspan="3"> योग </th>
							<td class="bold-text"> </td>
							<td class="bold-text"> </td>
							<td class="bold-text"> </td>
							<td class="bold-text"> <?php echo $total_dhanrashi; ?> </td>
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