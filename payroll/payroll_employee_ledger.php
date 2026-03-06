<?php
include("scripts/settings.php");
page_header_start('Customer Ledger');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno=1;
$tab=1;
$date= date("y-m-d");
$newdate = strtotime ( '-29 day' , strtotime ( $date ) ) ;
$newdate = date ( 'j-m-d' , $newdate );



if(!isset($_POST['search'])){
	$_POST['employee_code'] ="";
	$_POST['financial_session'] = "";
	$_POST['from_date'] = "";
	$_POST['to_date'] = "";
	$_POST['for_month'] = array();
}



?>
<div class="container no-print">
	<div class="row">
	<?php echo $msg; ?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="panel">
				<div class="panel-heading">
					Customer Ledger
				</div>	
				<div class="panel-body"> 
					<div class="pull-right">
						
					</div>
					<table class="table taable-responsibe table-bordered table-condensed table-hover ">
						<tbody>
							<tr>
								<td>Employee Code</td>
								<td><input type="text" tabindex="<?php echo $tab++; ?>" name="employee_code" id="employee_code" class="form-control" value="" required>
								
								<td>From </td>
								<td><input type="date" name="from_date" id="from_date" value="<?php if(isset($_POST['search'])){echo $_POST['from_date'];} ?>" required></td>
								
								<td>To</td>
								<td><input type="date" name="to_date" id="to_date" value="<?php if(isset($_POST['search'])){echo $_POST['to_date'];} ?>" required></td>
								
							</tr>
						</tbody>
					</table>
					<div class=" pull-left" style="width:200px; margin:10px;">
						<input type="submit" name="search" id="search" value="SEARCH" class="form-control btn btn-info">
					</div>
					
					<div class=" pull-right" style="width:200px; margin:10px;">
						<a onclick="printPage()" class="no-print btn btn-danger">Print this page</a>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<style>
		.printonly{
			display:none!important;
		}
		/* .watermarkz{
			width:100%;
			height:100%;
			position:fixed;
			right: 0;
			left: 0;
			top: 0;
			bottom: 0;
			margin: auto;
			background-color:red;
			background-image:url('images/icon.png');
			background-repeat:no-repeat;
			background-size:40%;
			z-index: -10;
		} */
		#overlays{
			z-index:0;
			opacity:0.15;
			position: fixed;
			top: 50%;
			left: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%); 
			display:none;
		}
		.payroll-footer{
			display:flex;
			justify-content:space-between;
			margin:5rem;
		}
		@media print{
			.printonly{
				display:block!important;
			}
			#overlays{
				display:block;
				width:35%!important;
				top: 50%!important;
				-ms-transform: translate(-50%, -50%);
				transform: translate(-50%, -50%);}
			}
			.payroll-footer{
				font-size:2rem;
				display:flex;
				justify-content:space-between;
				margin: 10rem!important;
			}
			 
		}
		.abc{
			padding:50px !important;
		}
		td,  th{
			margin:5px!important;
			padding:5px!important;
			font-size:15px;
			/* border: 1px solid black!important;*/
		}
		@page {
			size: A4 landscape; /* Set the size of the page, you can use other predefined sizes like 'letter', 'legal', etc. or specify custom sizes using 'width' and 'height' */
			margin: 0px; /* Set the margin of the page */
			
			
		}
	</style>
	

		<div class="row abc" style="margin:30px!important;">
					
			<?php

				$cat_sql='SELECT * FROM `employee` where employee_code="'.$_POST['employee_code'].'"';
				$cat_row_result = mysqli_query($db, $cat_sql);
				$cat_row = mysqli_fetch_array($cat_row_result);
				if(isset($_POST['search'])){ 
				$cat_id=$cat_row['employee_category_id'];
				}
				
				if(isset($_POST['search'])){ 
					//if (isset($_POST['employee_code'])) {
							$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$cat_row['company_id'].'"';
							$result_company = mysqli_query($db_erp, $sql_company);
							$row_company = mysqli_fetch_array($result_company);
							
							$sql = 'select * from dp_designation where sno="'.$cat_row['employee_designation'].'"';
							$des = mysqli_query($db_erp, $sql);
							if(mysqli_num_rows($des)!=0){
								$des = mysqli_fetch_assoc($des);
								}
							else{
								unset($des);
								$des['designation'] = '';
							}
							?>
								<div class="">
									<div style="display:flex; justify-content:center;position:relative;margin-bottom:3rem!important;">
										<div style="position:absolute;left:5rem;">
											<img src="images/icon.png" height="75">
										</div>
										<div>
											<div class="title text-center"> 
												<div class="" style="font-size:2.5rem;">
													<!--उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)-->
													UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH Ltd.
												</div>	
												<div class="" style="font-size:1.8rem;">Construction Division:<?php echo $row_company['division_name']; ?></div>
												<div class="" style="font-size:1.8rem;">Designation:<?php echo $des['designation']; ?></div>
												<div class="" style="font-size:1.8rem;">Date:<?php echo date("d-m-y"); ?></div>
											</div>
										</div>
									</div>
									<div style="display:flex; justify-content:space-between;margin-bottom:2rem!important;margin-inline:4rem!important;">
										<div>
												<b>Employee Code:</b><?php echo $cat_row['employee_code']; ?>
										</br>	<b>Employee Name:</b><?php echo $cat_row['employee_name']; ?>
										</br>	<b>Paymatrix:</b><?php echo $cat_row['pay_level_id']; ?>
										</div>
										<div>
												<b>Aadhar No:</b><?php echo $cat_row['aadhar']; ?>
										</br>	<b>Pan No:</b><?php echo $cat_row['pan']; ?>
										</br>	<b>Account No:</b><?php echo $cat_row['acount_number']; ?>
										</div>
									</div>
								</div>
							<?php
					//}
				
			?>
		
			
			<table width="100%" class="table table-hover table-responsive table-striped tablepad abc" border="2" ">	
					
					<tr class="" style='border-bottom:1px solid black!important;'>
						<th rowspan="2" style='border:1px solid black;'>S.No.</th>
						<th rowspan="2"  style='border:1px solid black;'>Company Name</th>
						<th rowspan="2"  style='border:1px solid black;'>Financial Year</th>
						<th rowspan="2"  style='border:1px solid black;'>For Month</th>
						<th colspan="3" style='border:1px solid black;'>Attendance (Days)</th>
						<th rowspan="2" style='border:1px solid black;'>level</th>
						<th rowspan="2" style='border:1px solid black;'>@ BAsic</th>
						<?php


						// income sql 


						$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='income' and head_type.payslip_mode!='1' and cat_id='{$cat_id}'";
						// echo $sql = 'select * from head_type where head_type="income" and payslip_mode!="1"';
						$result_income = execute_query($sql);
						$income_count = mysqli_num_rows($result_income);
						$income_count += 1;
						echo '<th style="border:1px solid black;" colspan="'.$income_count.'">Income</th>';
						

						// Deducaiton sql


						$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='deduction' and head_type.payslip_mode!='1' and cat_id='{$cat_id}'";

						// $sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1"';
						$result_deduction = execute_query($sql);
						$deduction_count = mysqli_num_rows($result_deduction);
						// $deduction_count += 1;
						echo '<th style="border:1px solid black;" colspan="'.$deduction_count.'">Deduction</th>';
						

						// other deduction sql


						$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='oth_deduction' and head_type.payslip_mode!='1' and cat_id='{$cat_id}'";
						
						// $sql = 'select * from head_type where head_type="oth_deduction" and payslip_mode!="1"';

						$result_deduction = execute_query($sql);
						$deduction_count = mysqli_num_rows($result_deduction);
						$deduction_count += 1;
						echo '<th style="border:1px solid black;" colspan="'.$deduction_count.'">Other Deduction</th>';
						

						?>
						
						
						<th  rowspan="2"  style='border:1px solid black;'>Net Pay</th>
					</tr>
					<tr class="tablepad">
						<!--<th>Income</th>
						<th>Deduction</th>
						<th>Total</th>-->
						<th style='border:1px solid black;'>Working</th>
						<th style='border:1px solid black;'>Leave</th>
						<th style='border:1px solid black;'>Present</th>
						<?php
						$tot_heads = array();
						
						$sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='income' and payslip_mode!='1'";
						$result_income = execute_query($sql);
						while($row_income = mysqli_fetch_assoc($result_income)){
							$tot_heads[$row_income['sno']] = 0;
							echo '<th style="border:1px solid black;">'.$row_income['head_name'].'</th>';	
						}
						$tot_heads['gorss_pay'] = 0;
						echo'<th  style="border:1px solid black;" >Gross Pay</th>';
						
						$sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='deduction' and payslip_mode!='1'";
						
						// $sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1"';
						$result_deduction = execute_query($sql);
						while($row_deduction = mysqli_fetch_assoc($result_deduction)){
							$tot_heads[$row_deduction['sno']] = 0;
							echo '<th style="border:1px solid black;">'.$row_deduction['head_name'].'</th>';	
						}
						
						$sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='oth_deduction' and payslip_mode!='1'";
						
						
						// $sql = 'select * from head_type where head_type="oth_deduction" and payslip_mode!="1"';
						$result_deduction = execute_query($sql);
						while($row_deduction = mysqli_fetch_assoc($result_deduction)){
							$tot_heads[$row_deduction['sno']] = 0;
							echo '<th style="border:1px solid black;">'.$row_deduction['head_name'].'</th>';	
						}
						echo'<th style="border:1px solid black;">Total Deduction</th>';
						$tot_heads['total_deduction'] = 0;

						?>
						
					</tr>
				
				<tbody>
					<?php
					$sql ='SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as emp_id ,payslip_report.creation_time,  `employee`.`employee_name` ,`employee`.`employee_code` ,`payslip_report`.`company_id`, `employee`.`pay_level_id` , `employee`.`employee_designation` ,`employee`.`employee_category_id` ,`payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`main_basic`, 
					`payslip_report`.`presented_days` , 
					`payslip_report`.`granted_leave` , 
					`payslip_report`.`working_days` , 
					`payslip_report`.`total_income` , 
					`payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id`  WHERE 1=1 and DATE(payslip_report.salary_month) BETWEEN "' . $_POST['from_date'] . '" AND "' . $_POST['to_date'] . '"';
						
					if(isset($_POST['company_name'])){
						if($_POST['company_name'] != ''){
							$sql .= ' AND `employee`.`company_id`="'.$_POST['company_name'].'" ';
						}
					}
					if(isset($_POST['employee_code'])){
						if($_POST['employee_code'] != ''){
							$sql .= ' AND `employee`.`employee_code`="'.$_POST['employee_code'].'" ';
						}
					}
					if(isset($_POST['employee_category_id'])){
						if($_POST['employee_category_id']!=''){
							$sql .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
						}
					}
					
					$sql .= "ORDER BY 
    CAST(SUBSTRING_INDEX(`financial_year`, '-', 1) AS UNSIGNED) ASC, 
    CAST(SUBSTRING_INDEX(`financial_year`, '-', -1) AS UNSIGNED) ASC, 
    CASE 
        WHEN `for_month` = 4 THEN 1  -- April
        WHEN `for_month` = 5 THEN 2  
        WHEN `for_month` = 6 THEN 3  
        WHEN `for_month` = 7 THEN 4  
        WHEN `for_month` = 8 THEN 5  
        WHEN `for_month` = 9 THEN 6  
        WHEN `for_month` = 10 THEN 7  
        WHEN `for_month` = 11 THEN 8  
        WHEN `for_month` = 12 THEN 9  -- December
        WHEN `for_month` = 1 THEN 10  -- January
        WHEN `for_month` = 2 THEN 11  
        WHEN `for_month` = 3 THEN 12  -- March
    END ASC ";
					
					// echo $sql;
					$result = execute_query($sql);
					$i=1;
					$tot_main_basic=0; 
					$tot_net_pay=0; 
					$tot_net_totoal_deduction=0; 
					$tot_total_income=0; 
					while($row=mysqli_fetch_array($result)){ 
						$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$row['company_id'].'"';
							$result_company = mysqli_query($db_erp, $sql_company);
							$row_company = mysqli_fetch_array($result_company);
					
						$sql_attendance = 'SELECT * FROM `attendance_details` WHERE `emp_id`="'.$row['emp_id'].'" AND `salary_generation_month`="'.$row['for_month'].'" AND `financial_year`="'.$row['financial_year'].'"';
						$result_attendance = execute_query($sql_attendance);
						$row_attendance = mysqli_fetch_array($result_attendance);
							
							$sql = 'select * from dp_designation where sno="'.$row['employee_designation'].'"';
							$des = mysqli_query($db_erp, $sql);
							if(mysqli_num_rows($des)!=0){
								$des = mysqli_fetch_assoc($des);
								}
							else{
								unset($des);
								$des['designation'] = '';
							}
									
						if ($row['total']!=""){
							echo"<tr>";
							echo "<td style='border:1px solid black;'>".$i++."</td>";
							
							echo "
							<td style='border:1px solid black;'>".$row_company["division_name"]."</td>
							<td style='border:1px solid black;'>".$row["financial_year"]."</td>
							<td style='border:1px solid black;'>".date('F',strtotime('01.'.$row["for_month"].'.2020'))."</td>
							<td style='border:1px solid black;'>".$row['working_days']."</td>
							<td style='border:1px solid black;'>".$row['granted_leave']."</td>
							<td style='border:1px solid black;'>".$row['presented_days']."</td>
							<td style='border:1px solid black;'>".$row['pay_level_id']."</td>
							<td style='border:1px solid black;'>".$row['main_basic']."</td>";
							$tot_main_basic+=$row['main_basic'];
							
							$sql_head_income = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='income' and payslip_mode!='1'";
						
							// $sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" and payslip_mode!="1"';
							$result_head_income = execute_query($sql_head_income);
							while($row_head_income = mysqli_fetch_array($result_head_income)){
								
								$sql_amount_income = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_income['sno'].'"';
								
								$result_income = execute_query($sql_amount_income);
								$row_amount_income = mysqli_fetch_array($result_income);
								
								echo "<td style='border:1px solid black;'>".$row_amount_income["amount"]."</td>";
								$tot_heads[$row_head_income['sno']] += $row_amount_income["amount"];
								
							}
							echo "<th style='border:1px solid black;'>".$row['total_income']."</th>";
							$tot_total_income += $row['total_income'];
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction" and payslip_mode!="1"';
							$result_head_deduction = execute_query($sql_head_deduction);
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								
								$sql_amount_deduction = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_deduction['sno'].'"';
								
								$result_deduction = execute_query($sql_amount_deduction);
								$row_amount_deduction = mysqli_fetch_array($result_deduction);
								
								echo "<td style='border:1px solid black;'>".round($row_amount_deduction["amount"])."</td>";
								$tot_heads[$row_head_deduction['sno']] += $row_amount_deduction["amount"];
								
							}
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='oth_deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="oth_deduction" and payslip_mode!="1"';
							$result_head_deduction = execute_query($sql_head_deduction);
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								
								$sql_amount_deduction = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_deduction['sno'].'"';
								
								$result_deduction = execute_query($sql_amount_deduction);
								$row_amount_deduction = mysqli_fetch_array($result_deduction);
								
								echo "<td style='border:1px solid black;'>".round($row_amount_deduction["amount"])."</td>";
								$tot_heads[$row_head_deduction['sno']] += $row_amount_deduction["amount"];
								
							}
							$tot_heads['total_deduction'] += $row['total_deduction'];
							echo "<th style='border:1px solid black;'>".$row['total_deduction']."</th>";
							$tot_net_totoal_deduction+=$row['total_deduction'];
							echo "<th style='border:1px solid black;'>".$row['total']."</th>";
							$tot_net_pay+=$row['total'];
							
						}
					} 
					?>
					<tr>
						
						
						<th colspan="8" style="text-align: right;border:1px solid black;">Total:</th>
						<?php 
						
							echo "<th style='border:1px solid black;'>".$tot_main_basic."</th>";
							
							$sql_head_income = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='income' and payslip_mode!='1'";
						
							// $sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" and payslip_mode!="1"';
							
							$result_head_income = execute_query($sql_head_income);
							$total_income =0;
							while($row_head_income = mysqli_fetch_array($result_head_income)){
								echo "<th style='border:1px solid black;'>".$tot_heads[$row_head_income["sno"]]."</th>";
								
							}
							echo "<th style='border:1px solid black;'>".$tot_total_income."</th>";
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction" and payslip_mode!="1"';
							
							$result_head_deduction = execute_query($sql_head_deduction);
							
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								echo "<th style='border:1px solid black;'>".$tot_heads[$row_head_deduction["sno"]]."</th>";
								
							}
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$cat_id}' and head_type.head_type='oth_deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="oth_deduction" and payslip_mode!="1"';
							$result_head_deduction = execute_query($sql_head_deduction);
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								echo "<th style='border:1px solid black;'>".$tot_heads[$row_head_deduction["sno"]]."</th>";
								
							}
							echo "<th style='border:1px solid black;'>".$tot_net_totoal_deduction."</th>";
							echo "<th style='border:1px solid black;'>".$tot_net_pay."</th>";
						?>
						
					</tr>
				</tbody>
			</table>

			<!-- <div class="payroll-foot">
				mentioned and passed for payment of Rs. <?php //echo $tot_heads['gorss_pay']; ?> 
				<?php
					//$abc="123123";
				 //echo int_to_words($abc);?>
				
			</div> -->
			<div class="payroll-foot text-center mt-10" style="font-size:1.7rem;font-weight:bold;">
				This is Computer Generated Salary Slip. Hence No Signature Is Required.
			</div>
			<?php  } ?>
		</div>
	

<?php 
page_footer();	
?>
<script>
	$( function() {
		$("#employee_name").autocomplete({
			source: function( request, response ) {
				$.ajax( {
					url: "ajax.php?id=employee_detail",
					dataType: "json",
					data: {
						term: request.term
					},
					success: function( data ) {
						response(data);
					}
				} );
			},
			minLength: 1,
			select: function( event, ui ) {
			   $('#employee_name').val(ui.item.employee_name); // display the selected text
			   $('#emp_id').val(ui.item.id); 
			}
		});
	});
	

				
		$('select[multiple]').multiselect({

			search: true,

			selectAll: true

		});

</script>