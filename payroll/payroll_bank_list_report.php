<?php
include("scripts/settings.php");
page_header_start('Bank List Report');
$_SESSION['payroll_sql_01'] = $sql;
$_SESSION['payroll_post'] = $_POST;

if (isset($_GET['del_id'])) {
	$sql_delete = 'DELETE FROM `payslip_report` WHERE `sno`="'.$_GET['del_id'].'"';
	$res = execute_query($sql_delete);
	if($res){
		$sql_delete_detail = 'DELETE FROM `payslip_details` WHERE `payslip_id`="'.$_GET['del_id'].'"';
		execute_query($sql_delete_detail);
		$msg = '<div class="alert alert-success">Payslip Information Deleted...</div>';
	}
}
?>
<script src="js/table2excel.js"></script>
<style type="text/css">
	table thead tr th{
		text-align: center;
		border: 2px solid black;
	}
	table tbody tr td{
		border: 2px solid black;
	}
	th{
		font-size: 14px;
	}
	td{
		font-size: 14px;
	}
	@media print {
        *{
          margin: 0 !important;
          padding: 0 !important;
          box-sizing: border-box !important;
        }
       body{
        padding:1rem!important;
       }
        td{
          padding: 8px !important;
          /* margin: 10px !important; */
        }
        .no-print{
          display:none !important;
        }
        
        .btn-print{
          display: none;
        }
        
       
      }
</style>
<?php
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
?>
<div class="container">
	<div class="row">
		
		<div class="no-print">
			<div class="panel">
				<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"  name="confirm" enctype="multipart/form-data">
					<div class="panel-heading">Bank List Report</div>
					<div class="panel-body">
						<span style="float: right;">
							<a onclick="printPage()" class="no-print btn btn-info">Print this page</a>
						</span>
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<td>Company Name</td>
									<td>
									<select class="form-control"  name="company_name" id="company_name" onchange="select_employee();">
										<option value="">-Select All-</option>
										<?php 
											$sql = "SELECT * FROM `uprnss_division`";	
											$query = mysqli_query($db_erp, $sql);
											 while ($row = mysqli_fetch_array($query)){ 
												echo '<option value="'.$row['s_no'].'" ';
												if(isset($_POST['company_name'])){
													if($row['s_no']==$_POST['company_name']){
														echo 'selected="selected"';
													}
												}
												echo '>'. $row['division_name'] . "</option>";
											}?>
										</select>
									</td>
									<td>Employee Name</td>
									<td><select class="form-control"  name="emp_name" id="emp_name">
										<option value="">-Select All-</option>

										<?php 
										$sql = 'SELECT * FROM `employee`';	
										$result = execute_query($sql);
										while ($row = mysqli_fetch_array($result)){ 
											echo '<option value="'.$row['sno'].'" ';
											if(isset($_POST['emp_name'])){
												if($row['sno']==$_POST['emp_name']){
													echo 'selected="selected"';
												}
											}
											echo '>' . $row['employee_name'] . "</option>";

										}?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Financial Year</td>
									<td>
										<select name="financial_year" class="form-control">
											<?php  
												if(date('m')>3){
													$select_start_session = date('Y');
												}
												else{
													$select_start_session = date('Y')-1;
												}
												$session_start = date('Y')-50;
												for($i = $session_start; $i<=$session_start+100;$i++){
													$end_session = $i+1;
													?>
													<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['financial_year'])){if($_POST['financial_year']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
													<?php
												}
											?>
										</select>
									</td>
									<td>For Month</td>
									<td>
										<select class="form-control"  name="for_month" id="title">
											<?php
											$month=1;
											$current_month = date('m');

											while($month<=12){
												echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
												if(isset($_POST['for_month'])){
													if($_POST['for_month']==$month){
														echo 'selected="selected"';
													}
												}
												else{
													if($month == $current_month){
														echo 'selected="selected"';
													}
												}
												echo '>'.date('F',strtotime('01.'.$month.'.2020')).'</option>';
												$month++;
											}
											?>
										</select>
									</td>
								</tr>
								
							</thead>
						</table>
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" name="search" id="search" value="SEARCH" class="form-control btn btn-info">
							</div>
							<div class="col-sm-6">
								<a href="<?php echo $_SERVER['PHP_SELF'];?>" class="form-control btn btn-info">Reset</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	</div>
		<?php 
		
		if(isset($_POST['search'])){ 
		
		?>
		
				<?php 
				if (isset($_POST['company_name'])) {
					
					if($_POST['company_name'] != ''){
					$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_name'].'"';
					$result_company = mysqli_query($db_erp, $sql_company);
					$row_company = mysqli_fetch_array($result_company);
					?>
						<table width="100%" border="" id="no_border" class="print-only" style="overflow-x:auto; border:none!important;">
							<tr>
								<td width="30%" style="border:none;" ><img src="images/icon.png" height="130"></td>
								<td width="70%"  colspan="2" style="border:none;" >
									<h4 class="title text-justify"> उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.) <br/> UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH LTD.(UPRNSS).
										<br/>Bank List Report:<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?></h4><h5>Construction Division:<?php echo $row_company['division_name']; ?></h5></td>
							</tr>
							<tr>
								<td width="30%" style="border:none;" >Bill No:</td>
								<td width="60%" style="border:none;" ></td>
								<td width="10%" style="border:none;" >Date:<?php echo date("d-m-y"); ?></td>
							</tr>
						</table>
					<?php
					}
					else{
						?>
							<table width="100%" border="" id="no_border" class="print-only" style="overflow-x:auto; border:none!important;">
								<tr>
									<td width="30%" style="border:none;" ><img src="images/icon.png" height="100"></td>
									<td width="70%"  colspan="2" style="border:none;" >
										<h4 class="title text-justify"> उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.) <br/> UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH LTD.(UPRNSS).
											<br/>Bank List Report:<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?></h4></td>
								</tr>
							<!--	<tr>
									<td width="30%" style="border:none;" >Bill No:</td>
									<td width="60%" style="border:none;" ></td>
									<td width="10%" style="border:none;" >Date:<?php echo date("d-m-y"); ?></td>
								</tr>-->
							</table>
						<?php
					}
				}
				?>
	<div class="container">
		<div class="row">
			<table class="table table-hover table-responsive table-striped" id="tblData" border="2">	
			<thead>
				<tr>
					<th style='border:2px solid black;'>S.No.</th>
					<th style='border:2px solid black;'>Employee Code</th>
					<th style='border:2px solid black;'>Employee Name</th>
					<th style='border:2px solid black;'>Designation</th>
					<th style='border:2px solid black;'>Division Name</th>
					<th style='border:2px solid black;'>Name Of Bank</th>
					<th style='border:2px solid black;'>Branch Name</th>
					<th style='border:2px solid black;'>IFSC Code </th>
					<th style='border:2px solid black;'>Account Number</th>
					<th style='border:2px solid black;'>Net Payment </th>
					
				</tr>
			</thead>
				
			<tbody>
				<?php
				$sql ='SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as emp_id , `employee`.`employee_name` ,`payslip_report`.`company_id` ,`employee`.`employee_code` ,`employee`.`bank_name` ,`employee`.`acount_number` ,`employee`.`ifsc_code` ,`employee`.`branch` , `employee`.`employee_designation` , `payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`total_income` , `payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id`  WHERE 1=1 ';
					
				if(isset($_POST['company_name'])){
					if($_POST['company_name'] != ''){
						$sql .= ' AND `payslip_report`.`company_id`="'.$_POST['company_name'].'" ';
					}
				}
				if(isset($_POST['emp_name'])){
					if($_POST['emp_name'] != ''){
						$sql .= ' AND `payslip_report`.`emp_id`="'.$_POST['emp_name'].'" ';
					}
				}
				if(isset($_POST['financial_year'])){
					if($_POST['financial_year'] != ''){
						$sql .= ' AND `payslip_report`.`financial_year`="'.$_POST['financial_year'].'" ';
					}
				}
				if(isset($_POST['for_month'])){
					if($_POST['for_month'] != ''){
						$sql .= ' AND `payslip_report`.`for_month`="'.$_POST['for_month'].'" ';
					}
				}
				$sql .= ' ORDER BY ABS(`payslip_report`.`financial_year`) DESC , ABS(`payslip_report`.`for_month`) DESC , ABS(`payslip_report`.`company_id`) DESC , ABS(`payslip_report`.`total`) DESC';
				// echo $sql;
				$result = execute_query($sql);
				$i=1;
				$total_basic_salary = 0;
				$total_payable_amount = 0;
				$total_allowances = 0;
				$total_deduction = 0;
				$total_grand = 0;
				$allo[] = 0;
				$allo = array();
				$dedu = array();
				$advance_amount = 0;
				$remainnung_amount = 0;
				while($row=mysqli_fetch_array($result)){ 
					if($row['total'] > 0){
						$sql_amount_payable = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="salary_amount"';
						$result_amount_payable = execute_query($sql_amount_payable);
						$row_amount_payable = mysqli_fetch_array($result_amount_payable);
						// $total_payable_amount += $row_amount_payable['amount'];

						$sql = 'select * from dp_designation where sno="'.$row['employee_designation'].'"';
							$des = mysqli_query($db_erp, $sql);
							if(mysqli_num_rows($des)!=0){
								$des = mysqli_fetch_assoc($des);
								}
							else{
								unset($des);
								$des['designation'] = '';
							}
						$sql = 'select * from uprnss_division where s_no="'.$row['company_id'].'"';
							$row_div = mysqli_query($db_erp, $sql);
							if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
							else{
								unset($row_div);
								$row_div['division_name'] = '';
							}
						
						echo"<tr>";
						echo "<td style='border:2px solid black;'>".$i++."</td>";
						// <td  style='border:2px solid black;'>".date('F',strtotime('01.'.$row["for_month"].'.2020'))."</td>";
						
						echo "
						<td style='border:2px solid black;'>".$row["employee_code"]."</td>
						<td style='border:2px solid black;'>".$row["employee_name"]."</td>
						<td style='border:2px solid black;'>".$des['designation']."</td>
						<td  style='border:2px solid black;'>".$row_div["division_name"]."</td>
						<td style='border:2px solid black;'>".$row["bank_name"]."</td>
						<td style='border:2px solid black;'>".$row["branch"]."</td>
						<td style='border:2px solid black;'>".$row["ifsc_code"]."</td>
						<td style='border:2px solid black;'>".$row['acount_number']."</td>
						<td style='border:2px solid black;'>".$row['total']."</td>";
						$total_grand += $row['total'];
						
						
						echo "</tr>";
						
						
					}
				}
				?>
				<tr>
					
					<th colspan="9" style="text-align: right;border:2px solid black;">Total:</th>
					<th style='border:2px solid black;'><?php echo round($total_grand); ?></th>
					
				</tr>
			</tbody>
		</table>
		<?php } ?>
		</div>
	</div>
	<a href="payroll_bank_list_report_export.php" target="_blank"><button type="button" id="btn">Export Table Data To Excel File</button></a>
	<script>
	    /*document.getElementById("btn").addEventListener('click', function(){
            var table2excel = new Table2Excel();
            table2excel.export(document.querySelectorAll("#tblData"),"banklist_report");
        });*/
    </script>
<?php
page_footer();
?>
