<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';
	
?>
<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>
<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					
					<table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">
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
							<th width="20%"></th>
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
						<h4 class="card-title text-center">अपूर्ण परियोजनाओ का विवरण </h4></br>
					</div>
                            <?php echo $msg; ?>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover text-right" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;">
                                <tr class="border border-light ">
                                    <th rowspan="2">Sno</th>
                                    <th rowspan="2">प्रखण्ड का नाम </th>
                                    <th rowspan="2">परियोजना का नाम </th>
                                    <th colspan="4">परियोजना के पूर्ण न होने का कारण </th>
                                    <th rowspan="2">अभ्युक्ति</th>
                                </tr>
                                <tr>
                                    <th>जी०एस०टी० राशि प्राप्त न होने के कारण</th>
                                    <th>टी०डी०एस० की राशि प्राप्त न होने के कारण</th>
                                    <th>किश्त न मिलने के कारण</th>
                                    <th>अन्य कारण यदि कोई हो</th>
                                </tr>
								<tr>
                                <?php
                                    for($i=1; $i<=8; $i++){
                                        echo '<th>' .$i. '</th>';
                                    }
                                ?>
                            </tr>
							</thead>
							<tbody>
								<?php
								if($_SESSION['usertype']=='1'){
									$sql = 'SELECT * FROM `trans_prarup_14` LEFT JOIN `invoice_prarup_14` ON `invoice_prarup_14`.`sno` = `trans_prarup_14`.`invoice_id` where invoice_prarup_14.created_by = "'.$_SESSION['username'].'"';
									
									if(isset($_POST['search'])){
										if($_POST['project_id']!=''){
											$sql .= ' and trans_prarup_14.monthlytarget="'.$_POST['project_id'].'"';
										}
										if($_POST['division_id']!=''){
											$sql .= ' and invoice_prarup_14.division_id="'.$_POST['division_id'].'"';
										}
									}
                                    
                                    $result_prarup_14 = execute_query($sql);
                                    $i=1;
                                    while($row_prarup_14 = mysqli_fetch_assoc($result_prarup_14)){
                                        $query = 'SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . ') and sno="'.$row_prarup_14['monthlytarget'].'"';
                                        $pariyojnaname=mysqli_fetch_assoc(mysqli_query($db,$query));

                                        $sql = 'select * from uprnss_division where s_no="'.$row_prarup_14['division_id'].'"';
                                        // $row_div = mysqli_fetch_assoc(execute_query($sql));
                                        $row_div = execute_query($sql);
                                        if(mysqli_num_rows($row_div)!=0){
                                        $row_div = mysqli_fetch_assoc($row_div);
                                        }
                                        else{
                                            unset($row_div);
                                            $row_div['division_name'] = '';
                                        }
                                        echo '<tr>
                                            <td>'.$i++.'</td>
                                            <td>'.$row_div['division_name'].'</td>
                                            <td>'.$pariyojnaname['project_name_hindi'].'</td>
                                            <td>'.$row_prarup_14['monthprogress'].'</td>
                                            <td>'.$row_prarup_14['totalprogress'].'</td>
                                            <td>'.$row_prarup_14['swekritdhanrashi'].'</td>
                                            <td>'.$row_prarup_14['avmuktdhanrashi'].'</td>
                                            <td>'.$row_prarup_14['vyadhanrashi'].'</td>
                                        </tr>';
                                    }
								}			
								else{
									$sql = 'SELECT * FROM `trans_prarup_14` LEFT JOIN `invoice_prarup_14` ON `invoice_prarup_14`.`sno` = `trans_prarup_14`.`invoice_id` where 1=1 ';
									if(isset($_POST['search'])){
										if($_POST['project_id']!=''){
											$sql .= ' and trans_prarup_14.monthlytarget="'.$_POST['project_id'].'"';
										}
										if($_POST['division_id']!=''){
											$sql .= ' and invoice_prarup_14.division_id="'.$_POST['division_id'].'"';
										}
									}
                                    
                                    $result_prarup_14 = execute_query($sql);
                                    $i=1;
                                    while($row_prarup_14 = mysqli_fetch_assoc($result_prarup_14)){
                                        $query = 'SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . ') and sno="'.$row_prarup_14['monthlytarget'].'"';
                                        $pariyojnaname=mysqli_fetch_assoc(mysqli_query($db,$query));

                                        $sql = 'select * from uprnss_division where s_no="'.$row_prarup_14['division_id'].'"';
                                        // $row_div = mysqli_fetch_assoc(execute_query($sql));
                                        $row_div = execute_query($sql);
                                        if(mysqli_num_rows($row_div)!=0){
                                        $row_div = mysqli_fetch_assoc($row_div);
                                        }
                                        else{
                                            unset($row_div);
                                            $row_div['division_name'] = '';
                                        }
                                        echo '<tr>
                                            <td>'.$i++.'</td>
                                            <td>'.$row_div['division_name'].'</td>
                                            <td>'.$pariyojnaname['project_name_hindi'].'</td>
                                            <td>'.$row_prarup_14['monthprogress'].'</td>
                                            <td>'.$row_prarup_14['totalprogress'].'</td>
                                            <td>'.$row_prarup_14['swekritdhanrashi'].'</td>
                                            <td>'.$row_prarup_14['avmuktdhanrashi'].'</td>
                                            <td>'.$row_prarup_14['vyadhanrashi'].'</td>
                                        </tr>';
                                    }
								}
								?>							
							
							</tbody>
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