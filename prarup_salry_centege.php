<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();

$msg='';

if(!isset($_POST['month'])){
	$_POST['date_from'] = date("Y-m-01");
	$_POST['date_to'] = date("Y-m-d");
	$_POST['month'] = "";
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
						<th><label >माह</label></th>							
						<th>
							
							<select name="month" id="month" class="form-control"  value="" tabindex="<?php echo $tab++; ?>">
								<option value="">--Select--</option>
								<option value="January"<?php echo ($_POST['month']=="January" ? 'selected' : ''); ?> >January</option>
								<option value="February"<?php echo ($_POST['month']=="February"?'selected':''); ?> >February</option>
								<option value="March"<?php echo ($_POST['month']=="March"?'selected':''); ?> >March</option>
								<option value="April"<?php echo ($_POST['month']=="April"?'selected':''); ?> >April</option>
								<option value="May"<?php echo ($_POST['month']=="May"?'selected':''); ?> >May</option>
								<option value="June"<?php echo ($_POST['month']=="June"?'selected':''); ?> >June</option>
								<option value="July"<?php echo ($_POST['month']=="July"?'selected':''); ?> >July</option>
								<option value="August"<?php echo ($_POST['month']=="August"?'selected':''); ?> >August</option>
								<option value="September"<?php echo ($_POST['month']=="September"?'selected':''); ?> >September</option>
								<option value="October"<?php echo ($_POST['month']=="October"?'selected':''); ?> >October</option>
								<option value="November"<?php echo ($_POST['month']=="November"?'selected':''); ?> >November</option>
								<option value="December"<?php echo ($_POST['month']=="December"?'selected':''); ?> >December</option>
							</select>
						</th>
						<th>
						</th>
						<th>
							<button type="submit" name="search" class="btn btn-primary">Search</button>
							<input type="hidden" id="id" name="id" value="1">
						</th>
						<th>
						</th>
						<th>
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
						<?php if(isset($_POST['search'])){?>   <h4 class="card-title text-center"> Salary And Centage Month-<?php echo $_POST['month']; ?></h4><?php };?></br>
					</div>
					<div class="card-body">
						    <table class="table table-striped table-bordered table-hover" >
								<thead style="position:sticky;top:0; z-index:2;">
									<tr>
										<th>Sno.</th>
										<th>Division Name</th>
										<th>Total Centage Earned By Division</th>
										<th>Total Paid </th>
										<th>Difference</th>
										
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
									
										$total_vetan  = 0;
										$total_monthdueprakhand = 0;
										$total_monthduemukhyalayasentage = 0;
										
										$i=1;
									while($row_division = mysqli_fetch_assoc($result_division)){
							
										$sql = 'SELECT 
										`invoice_prarup_1`.`division_id`, 
										count(*) c,
										sum(abs(`trans_prarup_1`.`monthdueprakhand`)) as monthdueprakhand,  
										sum(abs(`trans_prarup_1`.`monthduemukhyalayasentage`)) as monthduemukhyalayasentage
										FROM `trans_prarup_1` 
										LEFT JOIN `invoice_prarup_1` ON `invoice_prarup_1`.`sno` = `trans_prarup_1`.`invoice_id` where 1=1
										and `invoice_prarup_1`.`division_id`="'.$row_division['s_no'].'"
										and month="'.$_POST['month'].'" ';
										
										$row_prarup_1 = mysqli_fetch_assoc(execute_query($sql));
										$sum_total=$total_monthduemukhyalayasentage+$total_monthduemukhyalayasentage;
										
										$total_monthdueprakhand += floatval($row_prarup_1['monthdueprakhand']);
										$total_monthduemukhyalayasentage += floatval($row_prarup_1['monthduemukhyalayasentage']);
										// $total_monthsentageutr += floatval($row_prarup_1['monthsentageutr']);
										$row_prarup_1['monthdueprakhand'] = ($row_prarup_1['monthdueprakhand']=='')?0:$row_prarup_1['monthdueprakhand'];
										$row_prarup_1['monthduemukhyalayasentage'] = ($row_prarup_1['monthduemukhyalayasentage']=='')?0:$row_prarup_1['monthduemukhyalayasentage'];
											
										$sql = 'SELECT 
										`invoice_prarup_13`.`division_id`, 
										count(*) c,
										sum(abs(`trans_prarup_13`.`vyadhanrashi`)) as vyadhanrashi
										FROM `trans_prarup_13` 
										LEFT JOIN `invoice_prarup_13` ON `invoice_prarup_13`.`sno` = `trans_prarup_13`.`invoice_id` where 1=1
										and `invoice_prarup_13`.`division_id`="'.$row_division['s_no'].'"
										and month="'.$_POST['month'].'" ';
										
										$row_prarup_13 = mysqli_fetch_assoc(execute_query($sql));
										
										$total_vetan += floatval($row_prarup_13['vyadhanrashi']);
										// $total_monthsentageutr += floatval($row_prarup_1['monthsentageutr']);
										$row_prarup_13['vyadhanrashi'] = ($row_prarup_13['vyadhanrashi']=='')?0:$row_prarup_13['vyadhanrashi'];
											
										$centage=round(($row_prarup_1['monthdueprakhand']+$row_prarup_1['monthduemukhyalayasentage']),2);

											echo '<tr>
												<td>'.$i++.'</td>
												<td>'.$row_division['division_name'].'</td>
												<td class="number">'.round(($row_prarup_1['monthdueprakhand']+$row_prarup_1['monthduemukhyalayasentage']),2).'</td>
												<td class="number">'.round($row_prarup_13['vyadhanrashi'],2).'</td>
												<td class="number">'.round(($centage-$row_prarup_13['vyadhanrashi']),2).'</td>
											</tr>';
										}
								?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="2"> Total </td>
										<td style="text-align: right; font-weight: bold;"> <?php echo $sum_total; ?></td>
										<td style="text-align: right; font-weight: bold;"> <?php echo $total_vetan; ?></td>
										<td style="text-align: right; font-weight: bold;"> <?php echo $sum_total-$total_vetan; ?></td>
										
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