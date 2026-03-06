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
    <div class="row no-print">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title text-center">प्रखण्डों के मध्य संतुलन पत्र मे प्रदर्शित पाना-देना का विवरण </h4></br>
				</div>
					<?php echo $msg; ?>
				<div class="card-body">
					<div class="row">
                        <div class="col-md-3">
                            <label>प्रखण्ड का नाम जिसे देना है/पाना है</label>
							<select class="form-control" name="prakhandname" id="prakhandname">
								<option value="" selected >--Select--</option>
							<?php 
								$divnamesql="SELECT * FROM uprnss_division";
								$divnameres=mysqli_query($db,$divnamesql);
                                
								while($divrow=mysqli_fetch_assoc($divnameres)){
									?>
										<option value="<?php echo $divrow['s_no']?>" <?php  if(isset($_POST['prakhandname'])){ echo $_POST['prakhandname']==$divrow['s_no']?"selected":"";}?>><?php echo $divrow['division_name']?></option>
									<?php
								}
							
							?>
							</select>
                        </div>
                        <div class="col-md-3">
                            <label>देना है/पाना है</label>
							<select class="form-control " name="denalena" id="denalena" >
								<option selected value="">--Select--</option>
								<option <?php  if(isset($_POST['denalena'])){ echo $_POST['denalena']=='देना'?"selected":"";}?> value="देना">देना</option>
								<option <?php  if(isset($_POST['denalena'])){ echo $_POST['denalena']=='पाना'?"selected":"";}?> value="पाना">पाना</option>
							</select>
                           
                        </div>
                        <div class="col-md-3">
                            <input type="submit" name="submit" value="Search" class="btn btn-info">
                        </div>
					</div>
                </div>
            </div>
        </div>
    </div>
    </form>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title text-center">प्रखंडों के मध्य संतुलन पत्र मे दर्शित पाना देना का विवरण </h4></br>
                </div>
                        <?php echo $msg; ?>
                <div class="card-body">
                    <table class="table table-striped table-bordered table-hover text-right" id="general_start_table">
                    <thead style="position:sticky;top:0; z-index:2;">
                            <tr>
                                <th>क्रम सं.</th>
                                <th>प्रखण्ड का नाम</th>
                                <th>देना है/पाना है </th>
                                <th>प्रखण्ड का नाम जिसे देना है/पाना है</th>
                                <th>मद का नाम/</th>
                                <th>धनराशि</th>
                                <th>यदि धनराशि वर्तमान वित्तीय वर्ष में प्रेषित / समायोजित की गई है तो उसका दिनांक </th>
                                <th>अभ्युक्ति</th>
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
                                $sql = 'SELECT * FROM `trans_prarup_15` LEFT JOIN `invoice_prarup_15` ON `invoice_prarup_15`.`sno` = `trans_prarup_15`.`invoice_id` LEFT JOIN uprnss_division ON trans_prarup_15.prakhandrecevergetter=uprnss_division.s_no where invoice_prarup_15.created_by = "'.$_SESSION['username'].'"  and status!="5"';
                                

                                if(isset($_POST['submit'])){
                                    if(isset($_POST['prakhandname']) && $_POST['prakhandname']!=""){
                                        // echo "YES";
                                        $sql.=' and prakhandrecevergetter = "'.$_POST['prakhandname'].'"';
                                    }
                                    if(isset($_POST['denalena']) && $_POST['denalena']!=""){
                                        // echo "YES2";
                                        $sql.=' and denalena = "'.$_POST['denalena'].'"';
                                    }
                                    
                                }
                                // echo $sql;
                                $result_prarup_15 = execute_query($sql);
                                $a=1;
                                $total_dhanrashi = 0;
                           
                                while($row_prarup_15 = mysqli_fetch_assoc($result_prarup_15)){
                                     $sql = 'select * from uprnss_division where s_no="'.$row_prarup_15['division_id'].'"';
										$row_div = mysqli_fetch_assoc(execute_query($sql));
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
										$row_div = mysqli_fetch_assoc($row_div);
										}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
                                    $total_dhanrashi += floatval($row_prarup_15['dhanrashi']);
                                    
                                    echo '<tr>
                                        <td>'.$a++.'</td> 
                                       <td>'. $row_div['division_name'].'</td>  
                                        <td>'.$row_prarup_15['denalena'].'</td>
                                        <td>'.$row_prarup_15['division_name'].'</td>
                                        <td>'.$row_prarup_15['madname'].'</td>
                                        <td>'.$row_prarup_15['dhanrashi'].'</td>';?>
										<td>
										  <?php
											if (!empty($row_prarup_15['dateprashit'])) {
												echo date('d-m-Y', strtotime($row_prarup_15['dateprashit'])); 
											} else {
												echo '-';
												}
											?>
										</td>
									<?php echo'
										<td>'.$row_prarup_15['remark'].'</td>
                                    </tr>';
                                }
                            }			
                            else{
                               $sql = 'SELECT * FROM `trans_prarup_15` 
								LEFT JOIN `invoice_prarup_15` ON `invoice_prarup_15`.`sno` = `trans_prarup_15`.`invoice_id` 
								LEFT JOIN uprnss_division ON trans_prarup_15.prakhandrecevergetter = uprnss_division.s_no where 1=1 and status!="5"';

								if(isset($_POST['submit'])){
									if(isset($_POST['prakhandname']) && $_POST['prakhandname']!=""){
										$sql.=' and prakhandrecevergetter = "'.$_POST['prakhandname'].'"';
									}
									if(isset($_POST['denalena']) && $_POST['denalena']!=""){
										$sql.=' and denalena = "'.$_POST['denalena'].'"';
									}
								}
 
								
                                $result_prarup_15 = execute_query($sql);
                                $a=1;
                                $total_dhanrashi = 0;
                                while($row_prarup_15 = mysqli_fetch_assoc($result_prarup_15)){
                                $sql = 'select * from uprnss_division where s_no="'.$row_prarup_15['division_id'].'"';
                                $row_div = mysqli_fetch_assoc(execute_query($sql));
                                $row_div = execute_query($sql);
                                if(mysqli_num_rows($row_div)!=0){
                                $row_div = mysqli_fetch_assoc($row_div);
                                }
                                else{
                                    unset($row_div);
                                    $row_div['division_name'] = '';
                                }
                                
                                $total_dhanrashi += floatval($row_prarup_15['dhanrashi']);
                                

                                echo '<tr>
                                    <td>'.$a++.'</td> 
                                    <td>'. $row_div['division_name'].'</td> 
                                    <td>'.$row_prarup_15['denalena'].'</td>
                                    <td>'.$row_prarup_15['division_name'].'</td>
                                    <td>'.$row_prarup_15['madname'].'</td>
                                    <td>'.$row_prarup_15['dhanrashi'].'</td>';?>
									<td>
									  <?php
										if (!empty($row_prarup_15['dateprashit'])) {
											echo date('d-m-Y', strtotime($row_prarup_15['dateprashit'])); 
										} else {
											echo '-';
											}
										?>
									</td>
								<?php echo'
									<td>'.$row_prarup_15['remark'].'</td>
                                </tr>';
                                }
                            }
                            ?>							
                        
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="bold-text">योग</th>
                                <td class="bold-text"><?php echo $total_dhanrashi; ?></td>
                                <td class="bold-text"></td>
                                <td class="bold-text"></td>
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