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
	</div>
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्ड की प्रगति </h4></br>
					</div>
                            <?php echo $msg; ?>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover text-right">
						<thead style="position:sticky;top:0; z-index:2;">
								<tr>
                                    <th>Sno.</th>
                                    <th>प्रखण्ड का नाम </th>
                                    <th>माह </th>
                                    <th>माह का लक्ष्य </th>
                                    <th>माह की प्रगति (वर्क डन)</th>
                                    <th>कुल प्रगति (01 अप्रैल 23 से अब तक)</th>
                                    <th>वेतन</th>
                                    <th>अन्य मद</th>
                                    <th>कुल योग</th>
                                    <th>कुल प्रगति के सापेक्ष व्यय का %</th>
								</tr>
                                <tr>
                                    <?php
                                        for($i=1; $i<=10; $i++){
                                            echo '<th>' .$i. '</th>';
                                        }
                                    ?>
                                </tr>
							</thead>
							<tbody>
								<?php
									if($_SESSION['usertype']=='1'){
										$sql = 'SELECT * FROM `trans_prarup_13` LEFT JOIN `invoice_prarup_13` ON `invoice_prarup_13`.`sno` = `trans_prarup_13`.`invoice_id`where invoice_prarup_13.created_by = "'.$_SESSION['username'].'"';
										
									}else{
										$sql = 'SELECT * FROM `trans_prarup_13` LEFT JOIN `invoice_prarup_13` ON `invoice_prarup_13`.`sno` = `trans_prarup_13`.`invoice_id` where 1=1';
									}
									if(isset($_POST['search'])){
										
										if($_POST['division_id']!=''){
											$sql .= ' and invoice_prarup_13.division_id="'.$_POST['division_id'].'"';
										}
										if($_POST['month']!=''){
											$sql .= ' and trans_prarup_13.month="'.$_POST['month'].'"';
										}
									}
                                    $a=1;
                                    $result_prarup_13 = execute_query($sql);
                                    $total_month  = 0;
                                    $total_monthlytarget  = 0;
                                    $total_monthprogress  = 0;
                                    $total_totalprogress  = 0;
                                    $total_swekritdhanrashi  = 0;
                                    $total_avmuktdhanrashi  = 0;
                                    $total_vyadhanrashi  = 0;
                                    $total_avsheshdhanrashi  = 0;
                                    
                                    while($row_prarup_13 = mysqli_fetch_assoc($result_prarup_13)){
                                        $sql = '(SELECT division_name FROM uprnss_division WHERE s_no ="'.$row_prarup_13['division_id'].'")';
                                        $row_division = mysqli_fetch_assoc(execute_query($sql));
                                            $row_division = execute_query($sql);
                                            if(mysqli_num_rows($row_division)!=0){
                                            $row_division = mysqli_fetch_assoc($row_division);
                                            }
                                            else{
                                                unset($row_division);
                                                $row_division['division_name'] = '';
                                            }
                                            // $total_month += floatval($row_prarup_13['month']);
                                            $total_monthlytarget += floatval($row_prarup_13['monthlytarget']);
                                            $total_monthprogress += floatval($row_prarup_13['monthprogress']);
                                            $total_totalprogress += floatval($row_prarup_13['totalprogress']);
                                            $total_swekritdhanrashi += floatval($row_prarup_13['swekritdhanrashi']);
                                            $total_avmuktdhanrashi += floatval($row_prarup_13['avmuktdhanrashi']);
                                            $total_vyadhanrashi += floatval($row_prarup_13['vyadhanrashi']);
                                            $total_avsheshdhanrashi += floatval($row_prarup_13['avsheshdhanrashi']);
                            
                                        echo '<tr>
                                            <td>'.$i++.'</td> 
                                            <td>'.$row_division['division_name'].'</td> 
                                            <td>'.$row_prarup_13['month'].'</td> 
                                            <td>'.$row_prarup_13['monthlytarget'].'</td>
                                            <td>'.$row_prarup_13['monthprogress'].'</td>
                                            <td>'.$row_prarup_13['totalprogress'].'</td>
                                            <td>'.$row_prarup_13['swekritdhanrashi'].'</td>
                                            <td>'.$row_prarup_13['avmuktdhanrashi'].'</td>
                                            <td>'.$row_prarup_13['vyadhanrashi'].'</td>
                                            <td>'.$row_prarup_13['avsheshdhanrashi'].'</td>
                                        </tr>';
                                    }			
								?>							
							
							</tbody>
                            <tfoot>
                                <th class="bold-text" colspan="3"> योग </th>
                                
                                <td class="bold-text"> <?php echo $total_monthlytarget; ?> </td>
                                <td class="bold-text"> <?php echo $total_monthprogress; ?> </td>
                                <td class="bold-text"> <?php echo $total_totalprogress; ?> </td>
                                <td class="bold-text"> <?php echo $total_swekritdhanrashi; ?> </td>
                                <td class="bold-text"> <?php echo $total_avmuktdhanrashi; ?> </td>
                                <td class="bold-text"> <?php echo $total_vyadhanrashi; ?> </td>
                                <td class="bold-text"> </td>
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