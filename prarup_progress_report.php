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
						<?php if(isset($_POST['search'])){?>   <h4 class="card-title text-center"> Progress Month-<?php echo $_POST['month']; ?></h4><?php };?></br>
					</div>
					<div class="card-body">
						    <table class="table table-striped table-bordered table-hover" >
								<thead style="position:sticky;top:0; z-index:2;">
									<tr>
										<th>Sno.</th>
										<th>Division Name</th>
										<th>Progress (Monthly)</th>
										<th>Progressive</th>
										<!-- <th>Total</th> -->
										
									</tr>
									<tr>
										<?php
										for($i=1;$i<=4;$i++){
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
                                    $total_monthprogress  = 0;
                                    $total_totalprogress  = 0;

									while($row_division = mysqli_fetch_assoc($result_division)){
							
										 $sql = 'SELECT 
										`invoice_prarup_13`.`division_id`, 
										count(*) c,
										sum(abs(`trans_prarup_13`.`monthprogress`)) as monthprogress,  
										sum(abs(`trans_prarup_13`.`totalprogress`)) as totalprogress
										FROM `trans_prarup_13` 
										LEFT JOIN `invoice_prarup_13` ON `invoice_prarup_13`.`sno` = `trans_prarup_13`.`invoice_id` where 1=1
										and `invoice_prarup_13`.`division_id`="'.$row_division['s_no'].'" and month="'.$_POST['month'].'"';
										
                                        
										$row_prarup_13 = mysqli_fetch_assoc(execute_query($sql));
										
											$total_monthprogress += floatval($row_prarup_13['monthprogress']);
											$total_totalprogress += floatval($row_prarup_13['totalprogress']);
											// $total_monthsentageutr += floatval($row_prarup_13['monthsentageutr']);
											
										$row_prarup_13['monthprogress'] = ($row_prarup_13['monthprogress']=='')?0:$row_prarup_13['monthprogress'];
										$row_prarup_13['totalprogress'] = ($row_prarup_13['totalprogress']=='')?0:$row_prarup_13['totalprogress'];
										

											echo '<tr>
												<td>'.$i++.'</td>
												<td>'.$row_division['division_name'].'</td>
												<td class="number">'.round($row_prarup_13['monthprogress'],2).'</td>
												<td class="number">'.round($row_prarup_13['totalprogress'],2).'</td>
												
											</tr>';
										}
								?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="2"> Total </td>
										<td style="text-align: right; font-weight: bold;"> <?php echo $total_monthprogress; ?></td>
										<td style="text-align: right; font-weight: bold;"> <?php echo $total_totalprogress; ?></td>
										
										
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