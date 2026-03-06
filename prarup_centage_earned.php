<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();

$msg='';

if(!isset($_POST['date_from'])){
	$_POST['date_from'] = date("Y-m-01");
	$_POST['date_to'] = date("Y-m-d");
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
	.number{
		text-align: right;
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
						<th>Date From</th>							
						<th>
							<input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $_POST['date_from']; ?>">
						</th>
						<th>Date To</th>							
						<th>
							<input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $_POST['date_to']; ?>">
						</th>
						<th>
							<button type="submit" name="search" class="btn btn-primary">Search</button>
							<input type="hidden" id="id" name="id" value="1">


						</th>
					</tr>
				</table>
			</div>
		</div>
		</form>
	</div>
	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">Bank Balance </h4></br>
					</div>
					<div class="card-body">
						    <table id="bank_balance_table" class="table table-striped table-bordered table-hover" >
								<thead style="position:sticky;top:0; z-index:2;">
									<tr>
										<th>Sno.</th>
										<th>Division Name</th>
										<th>Advanced Centage Adjustment</th>
										<th>Centage</th>
										<th>Total Centage earned by Division</th>
										
									</tr>
									<tr>
										<?php
										for($i=1;$i<=5;$i++){
											echo '<th>'.$i.'</th>';
										}
										?>
									</tr>
								</thead>
								<tbody>
								<?php
									$sql = 'select * from uprnss_division';
									$result_division = execute_query($sql);
									$i=1;
									$total_prambhik_dey = 0;
										$total_monthduemukhyalaya = 0;
										$total_monthdueprakhand = 0;
										$total_monthduemukhyalayasentage = 0;
										$total_monthduemukhyalayasentage = 0;
									while($row_division = mysqli_fetch_assoc($result_division)){
							
										$sql = 'SELECT 
										`invoice_prarup_1`.`division_id`, 
										count(*) c,
										sum(abs(`trans_prarup_1`.`monthdueprakhand`)) as monthdueprakhand,  
										sum(abs(`trans_prarup_1`.`monthduemukhyalayasentage`)) as monthduemukhyalayasentage
										FROM `trans_prarup_1` 
										LEFT JOIN `invoice_prarup_1` ON `invoice_prarup_1`.`sno` = `trans_prarup_1`.`invoice_id` where 1=1
										and `invoice_prarup_1`.`division_id`="'.$row_division['s_no'].'"
										and entry_date>="'.$_POST['date_from'].'" and entry_date<="'.$_POST['date_to'].'"';
										
										$row_prarup_1 = mysqli_fetch_assoc(execute_query($sql));
										
											$total_prambhik_dey += floatval($row_prarup_1['monthdueprakhand']);
											$total_monthduemukhyalaya += floatval($row_prarup_1['monthduemukhyalayasentage']);
											// $total_monthsentageutr += floatval($row_prarup_1['monthsentageutr']);
											
										$row_prarup_1['monthdueprakhand'] = ($row_prarup_1['monthdueprakhand']=='')?0:$row_prarup_1['monthdueprakhand'];
										$row_prarup_1['monthduemukhyalayasentage'] = ($row_prarup_1['monthduemukhyalayasentage']=='')?0:$row_prarup_1['monthduemukhyalayasentage'];
										

											echo '<tr>
												<td>'.$i++.'</td>
												<td>'.$row_division['division_name'].'</td>
												<td class="number">'.round($row_prarup_1['monthdueprakhand'],2).'</td>
												<td class="number">'.round($row_prarup_1['monthduemukhyalayasentage'],2).'</td>
												<td class="total-centage number"></td>
											</tr>';
										}
								?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="2"> Total </td>
										<td style="text-align: right; font-weight: bold;"> <?php echo $total_monthdueprakhand; ?></td>
										<td style="text-align: right; font-weight: bold;"> <?php echo $total_monthduemukhyalayasentage; ?></td>
										<td style="text-align: right; font-weight: bold;"> </td>
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
	var table = $('#bank_balance_table').DataTable();

	// Calculate and display Total Centage earned by Division
	table.rows().every(function () {
		var row = this.data();
		var total = parseFloat(row[2]) + parseFloat(row[3]);
		$(this.node()).find('.total-centage').html(total.toFixed(2));
	});
});
</script>
<?php		
page_footer_end();
?>
