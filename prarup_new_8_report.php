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
						<h4 class="card-title text-center">प्रखण्डों द्वारा अभिलेखों में दर्शित / मॉग की जा रही ग्राहक विभाग द्वारा कटौती की गई आयकर की धनराशि का विवरण</h4></br>
					</div>
                            <?php echo $msg; ?>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover text-right" id="genral_start_table">
                        <thead style="position:sticky;top:0; z-index:2;">
								<tr>
                                    <th>Sno.</th>
                                    <th>प्रखण्ड का नाम </th>
                                    <th>वित्तीय वर्ष </th>
                                    <th>विभाग का नाम </th>
                                    <th>परियोजना का नाम </th>
                                    <th>ग्राहक विभाग का जी०एस०टिन नम्बर</th>
                                    <th>मुख्यालय पर प्राप्त धनराशि पर टी०डी०एस० की धनराशि</th>
                                    <th>प्रखण्ड पर प्राप्त धनराशि पर टी०डी०एस० की धनराशि  </th>
                                    <th>मुख्यालय द्वारा प्रखण्ड को प्रेषित टी०डी०एस० की धनराशि</th>
                                    <th>अंतर</th>
                                    <th>परियोजना  पूर्ण /अपूर्ण </th>
								</tr>
                                <tr>
                                    <?php
                                    for($i=1; $i<=11; $i++){
                                        echo '<th>' .$i. '</th>';
                                    }
                                    ?>
                                </tr>
							</thead>
							<tbody>
								<?php
								if($_SESSION['usertype']=='1'){
                                    $sql = 'SELECT `invoice_prarup_8`.`division_id`, `invoice_prarup_8`.`entry_date`,
                                    `trans_prarup_8`.`vibhagname`,`trans_prarup_8`.`year`, `trans_prarup_8`.`project_name`,`trans_prarup_8`.`grahakgstnum`, `trans_prarup_8`.`centagegsttds`, `trans_prarup_8`.`prakhandgsttdsdhanrashi`, `trans_prarup_8`.`mukhyalayaprakhandgsttdsdhanrashi`, `trans_prarup_8`.`difference`, `trans_prarup_8`.`projectstatus` FROM `trans_prarup_8` LEFT JOIN `invoice_prarup_8` ON `invoice_prarup_8`.`sno` = `trans_prarup_8`.`invoice_id`where invoice_prarup_8.created_by = "'.$_SESSION['username'].'"';
                                    if (isset($_POST['search'])) {
                                        if ($_POST['division_id'] != '') {
                                            $sql .= ' and invoice_prarup_8.division_id="' . $_POST['division_id'] . '"';
                                        }
                                        if ($_POST['month'] != '') {
                                            $sql .= ' and trans_prarup_8.month="' . $_POST['month'] . '"';
                                        }
                                    }
                                    
                                    $i=1;
                                    $result_prarup_8 = execute_query($sql);
                                    $total_grahakgstnum = 0;
                                    $total_centagegsttds = 0;
                                    $total_prakhandgsttdsdhanrashi = 0;
                                    $total_mukhyalayaprakhandgsttdsdhanrashi = 0;
                                    $total_difference = 0;
                                    $total_projectstatus = 0;

                                    while($row_prarup_8 = mysqli_fetch_assoc($result_prarup_8)){
                                        $sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="'.$row_prarup_8['project_name'].'")';
                                        $row_project = mysqli_fetch_assoc(execute_query($sql));
                                        $row_project = execute_query($sql);
                                        if(mysqli_num_rows($row_project)!=0){
                                        $row_project = mysqli_fetch_assoc($row_project);
                                        }
                                        else{
                                            unset($row_project);
                                            $row_project['project_name_hindi'] = '';
                                        }


                                        $sql = 'select * from uprnss_department_name where sno="'.$row_prarup_8['vibhagname'].'"';
                                        $row_dep = mysqli_fetch_assoc(execute_query($sql));
                                        $row_dep = execute_query($sql);
                                        if(mysqli_num_rows($row_dep)!=0){
                                        $row_dep = mysqli_fetch_assoc($row_dep);
                                        }
                                        else{
                                            unset($row_dep);
                                            $row_dep['department_name_hindi'] = '';
                                        }
                                        $sql = 'select * from uprnss_division where s_no="'.$row_prarup_8['division_id'].'"';
                                        $row_div = mysqli_fetch_assoc(execute_query($sql));
                                        $row_div = execute_query($sql);
                                        if(mysqli_num_rows($row_div)!=0){
                                        $row_div = mysqli_fetch_assoc($row_div);
                                        }
                                        else{
                                            unset($row_div);
                                            $row_div['division_name'] = '';
                                        }
                                        $total_grahakgstnum += floatval($row_prarup_8['grahakgstnum']);
                                        $total_centagegsttds += floatval($row_prarup_8['centagegsttds']);
                                        $total_prakhandgsttdsdhanrashi += floatval($row_prarup_8['prakhandgsttdsdhanrashi']);
                                        $total_mukhyalayaprakhandgsttdsdhanrashi += floatval($row_prarup_8['mukhyalayaprakhandgsttdsdhanrashi']);
                                        $total_difference += floatval($row_prarup_8['difference']);

                                        echo '<tr>
                                        <td>'.$i++.'</td>
                                        <td>'.$row_div['division_name'].'</td>
                                        <td>'.$row_prarup_8['year'].'</td>
                                        <td>'.$row_dep['department_name_hindi'].'</td>
                                        <td>'.$row_project['project_name_hindi'].'</td>
                                        <td>'.$row_prarup_8['grahakgstnum'].'</td>
                                        <td>'.$row_prarup_8['centagegsttds'].'</td>
                                        <td>'.$row_prarup_8['prakhandgsttdsdhanrashi'].'</td>
                                        <td>'.$row_prarup_8['mukhyalayaprakhandgsttdsdhanrashi'].'</td>
                                        <td>'.$row_prarup_8['difference'].'</td>
                                        <td>'.$row_prarup_8['projectstatus'].'</td></tr>';

                                    }
								}			
								else{
									$sql = 'SELECT `invoice_prarup_8`.`division_id`, `invoice_prarup_8`.`entry_date`,
                                    `trans_prarup_8`.`vibhagname`,`trans_prarup_8`.`year`, `trans_prarup_8`.`project_name`,`trans_prarup_8`.`grahakgstnum`, `trans_prarup_8`.`centagegsttds`, `trans_prarup_8`.`prakhandgsttdsdhanrashi`, `trans_prarup_8`.`mukhyalayaprakhandgsttdsdhanrashi`, `trans_prarup_8`.`difference`, `trans_prarup_8`.`projectstatus` FROM `trans_prarup_8` LEFT JOIN `invoice_prarup_8` ON `invoice_prarup_8`.`sno` = `trans_prarup_8`.`invoice_id`where 1=1';
                                    if (isset($_POST['search'])) {
                                        if ($_POST['division_id'] != '') {
                                            $sql .= ' and invoice_prarup_8.division_id="' . $_POST['division_id'] . '"';
                                        }
                                        if ($_POST['month'] != '') {
                                            $sql .= ' and trans_prarup_8.month="' . $_POST['month'] . '"';
                                        }
                                    }
									$i=1;
                                    $result_prarup_8 = execute_query($sql);
                                    $total_grahakgstnum = 0;
                                    $total_centagegsttds = 0;
                                    $total_prakhandgsttdsdhanrashi = 0;
                                    $total_mukhyalayaprakhandgsttdsdhanrashi = 0;
                                    $total_difference = 0;
                                    $total_projectstatus = 0;

                                    while($row_prarup_8 = mysqli_fetch_assoc($result_prarup_8)){
                                        $sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="'.$row_prarup_8['project_name'].'")';
                                        $row_project = mysqli_fetch_assoc(execute_query($sql));
                                        $row_project = execute_query($sql);
                                        if(mysqli_num_rows($row_project)!=0){
                                        $row_project = mysqli_fetch_assoc($row_project);
                                        }
                                        else{
                                            unset($row_project);
                                            $row_project['project_name_hindi'] = '';
                                        }
                                        $sql = 'select * from uprnss_department_name where sno="'.$row_prarup_8['vibhagname'].'"';
                                        $row_dep = mysqli_fetch_assoc(execute_query($sql));
                                        $row_dep = execute_query($sql);
                                        if(mysqli_num_rows($row_dep)!=0){
                                        $row_dep = mysqli_fetch_assoc($row_dep);
                                        }
                                        else{
                                            unset($row_dep);
                                            $row_dep['department_name_hindi'] = '';
                                        }

                                        $sql = 'select * from uprnss_division where s_no="'.$row_prarup_8['division_id'].'"';
                                        $row_div = mysqli_fetch_assoc(execute_query($sql));
                                        $row_div = execute_query($sql);
                                        if(mysqli_num_rows($row_div)!=0){
                                        $row_div = mysqli_fetch_assoc($row_div);
                                        }
                                        else{
                                            unset($row_div);
                                            $row_div['division_name'] = '';
                                        }

                                        $total_grahakgstnum += floatval($row_prarup_8['grahakgstnum']);
                                        $total_centagegsttds += floatval($row_prarup_8['centagegsttds']);
                                        $total_prakhandgsttdsdhanrashi += floatval($row_prarup_8['prakhandgsttdsdhanrashi']);
                                        $total_mukhyalayaprakhandgsttdsdhanrashi += floatval($row_prarup_8['mukhyalayaprakhandgsttdsdhanrashi']);
                                        $total_difference += floatval($row_prarup_8['difference']);
                                        

                                        echo '<tr>
                                        <td>'.$i++.'</td>
                                        <td>'.$row_div['division_name'].'</td>
                                        <td>'.$row_prarup_8['year'].'</td>
                                        <td>'.$row_dep['department_name_hindi'].'</td>
                                        <td>'.$row_project['project_name_hindi'].'</td>
                                        <td>'.$row_prarup_8['grahakgstnum'].'</td>
                                        <td>'.$row_prarup_8['centagegsttds'].'</td>
                                        <td>'.$row_prarup_8['prakhandgsttdsdhanrashi'].'</td>
                                        <td>'.$row_prarup_8['mukhyalayaprakhandgsttdsdhanrashi'].'</td>
                                        <td>'.$row_prarup_8['difference'].'</td>
                                        <td>'.$row_prarup_8['projectstatus'].'</td></tr>';

                                    }
								}
								?>							
							</tbody>
                            <tfoot>
                                <tr>
                                    <td class="bold-text" colspan="5"> योग </td>
                                    <td></td>
                                    <td class="bold-text"> <?php echo $total_centagegsttds; ?> </td>
                                    <td class="bold-text"> <?php echo $total_prakhandgsttdsdhanrashi; ?> </td>
                                    <td class="bold-text"> <?php echo $total_mukhyalayaprakhandgsttdsdhanrashi; ?> </td>
                                    <td class="bold-text"> <?php echo $total_difference; ?> </td>
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