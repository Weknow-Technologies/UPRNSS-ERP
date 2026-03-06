<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
$response=1;
page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['submit_form'])){
	if(!isset($_POST['final_submit'])){
		$msg .= '<div class="alert alert-primary">Report Preview</div>';
		$report = '';
		$cols = array();
		$sql = 'select * from report_custom_variables';
		$result_report = execute_query($sql);
		while($row_report = mysqli_fetch_assoc($result_report)){
			if(isset($_POST['filter_'.$row_report['sno']])){
				$cols[$row_report['sno']]['sno'] = $row_report['sno'];
				$cols[$row_report['sno']]['var_name'] = $row_report['display_name'];
				$cols[$row_report['sno']]['sequence'] = $_POST['sequence_'.$row_report['sno']];
			}
		}
		usort($cols, function($a, $b) {
			return $a['sequence'] <=> $b['sequence'];
		});
		$report = '<table class="table table-stripped bordered"><tr><th>S.No.</th>';
		foreach($cols as $k=>$v){
			$report .= '<th>'.$v['var_name'].'<input type="hidden" name="filter_'.$v['sno'].'" value="'.$v['sequence'].'"></th>';
		}
		$report .= '</tr><tr><th>1</th>';
		$i=2;
		foreach($cols as $k=>$v){
			$report .= '<th>'.$i++.'</th>';
		}
		
		$report .= '</table>
		<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Add Total Column</button>
		<button type="button" class="btn btn-secondaty" data-toggle="modal" data-target="#exampleModal2">Add Custom Column</button>
		<input type="hidden" name="final_submit" value="">
		<input type="hidden" name="template_name" value="'.$_POST['template_name'].'">
		
		<!--Modal Dialog Box for Total Column-->
		<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Total Column</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-12">
								<label>Column Title</label>
								<input type="text" name="total_column_title" id="total_column_title" class="form-control">
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<label>Select Columns</label>
								<select name="column_names" id="column_names" class="form-control" multiple="multiple">';
		foreach($cols as $k=>$v){
			$report .= '<option value="'.$v['sno'].'">'.$v['var_name'].'</option>';
		}
		$report .= '
									
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<label>Display After</label>
								<select name="display_after" id="display_after" class="form-control">';
		foreach($cols as $k=>$v){
			$report .= '<option value="'.$v['sno'].'">'.$v['var_name'].'</option>';
		}
		$report .= '
									
								</select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary">Save changes</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
		<!--Modal Dialog Box for Custom Column-->
		<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Custom Column</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-12">
								<label>Custom Column Title</label>
								<input type="text" name="custom_column_title" id="custom_column_title" class="form-control">
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<label>Display After</label>
								<select name="display_after" id="display_after" class="form-control">';
		foreach($cols as $k=>$v){
			$report .= '<option value="'.$v['sno'].'">'.$v['var_name'].'</option>';
		}
		$report .= '
									
								</select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary">Save changes</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		';
		$response=2;
	}
	else{
		
		$sql = 'insert into report_custom_template (template_name) values ("'.$_POST['template_name'].'")';
		execute_query($sql);
		if(mysqli_error($db)){
			$msg .= '<div class="alert alert-danger">Error # 1 : '.mysqli_error($db).' >> '.$sql.'</div>';
		}
		$id = mysqli_insert_id($db);

		$sql = 'select * from report_custom_variables';
		$result_report = execute_query($sql);
		while($row_report = mysqli_fetch_assoc($result_report)){
			if(isset($_POST['filter_'.$row_report['sno']])){
				$sql = 'insert into report_custom_template_details (template_id, variable_id, sequence) values ("'.$id.'", "'.$row_report['sno'].'", "'.$_POST['filter_'.$row_report['sno']].'")';	
				execute_query($sql);
				if(mysqli_error($db)){
					$msg .= '<div class="alert alert-danger">Error # 1 : '.mysqli_error($db).' >> '.$sql.'</div>';
				}

			}		
		}
		if($msg == ''){
			$msg .= '<div class="alert alert-success">Data Saved</div>';
		}
	}
}
else{
		
	$_POST['unit_name_1'] = '';
	$_POST['month_1'] = '';
	$_POST['head_type_1'] = '';
	$_POST['amount_1'] = '';
	$_POST['utr_no_1'] = '';
	$_POST['transaction_date_1'] = '';
	$_POST['other_1'] = '';


}

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Create Custom Report Template</h4></br>
					</div>                        
					<?php echo $msg; ?>
					<div class="card-body">
					<?php
						switch($response){
							case 1 : {
					?>
								<div class="row">
									<div class="col-md-3">
										<label>Template Name</label>
										<input type="text" name="template_name" id="template_name" class="form-control">
									</div>
								</div>
								<?php
								$sql = 'select * from report_custom_variables';
								$result_report = execute_query($sql);
								while($row_report = mysqli_fetch_assoc($result_report)){
								?>
								<div class="row border rounded m-1">
									<div class="col-md-1"><input type="checkbox" class="form-control" name="filter_<?php echo $row_report['sno']; ?>" id="filter_<?php echo $row_report['sno']; ?>"></div>
									<div class="col-md-3"><?php echo $row_report['display_name']; ?></div>
									<div class="col-md-3"><input type="text" class="form-control" placeholder="Enter Sequence" name="sequence_<?php echo $row_report['sno']; ?>" id="sequence_<?php echo $row_report['sno']; ?>"></div>
								</div>

								<?php
								}
								
								break;
							}
							case 2 : {
								echo $report;
								break;
							}
						}
						?>
						<div class="row">
							<div class="col-6">
								<button type="submit" class="btn btn-success" name="submit_form" id="submit_form">Create/Edit Template</button>
							</div>
						</div>
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
</script>

    
<?php		
page_footer_end();
?>