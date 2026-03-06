<?php
    include("scripts/settings.php");
    page_header_start('Payslip Report');
?>
<style type="text/css">
.tablepad{
          margin: 0 !important;
          padding: 0 !important;
          box-sizing: border-box !important;
        }
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
	
	@page {
	  size: A4 landscape;
	  margin:0.2in;
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

    .printonly{
        display:none!important;
    }
    
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
						<div class="panel-heading">Search</div>
						<div class="panel-body">
							<span style="float: right;">
								<a onclick="printPage()" class="no-print btn btn-info">Print this page</a>
							</span>
							<table class="table table-hover table-responsive table-bordered table-striped">	
								<thead>
									<tr>
										<td>Division Name</td>
										<td>
											<select class="form-control"  name="company_name" id="company_name" onchange="select_employee();">
												<option value="">-Select All-</option>
												<?php 
													$sql = "SELECT * FROM `uprnss_division` order by division_name ASC";	
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

	<div class="container">
		<div class="row">
		<!-- CATEGROY WISE TABLE PRINTING -->

            <?php
                $catsql="SELECT * FROM `payroll_emp_category`";
                $catres=execute_query($catsql);

                while($catrow=mysqli_fetch_assoc($catres)){
                    ?>
                        <div class="printonly ">
                            <div style="display:flex; justify-content:center;position:relative;margin-bottom:3rem!important;">
                                <div style="position:absolute;left:1rem;">
                                    <img src="images/icon.png" height="75">
                                </div>
                                <div>
                                    <div class="title text-center"> 
                                        <div class="" style="font-size:3rem;">
                                            <!--उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)-->
                                                UTTER PRADESH RAJYA NIRMAN SAHKARI SANGH Ltd.
                                        </div>
                                        
                                        <div class="" style="font-size:2.5rem;">Summary Report</div>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:space-between;margin-bottom:2rem!important;margin-inline:10rem!important;">
                                <div style="font-size:1.8rem;">
                                    Bill No:
                                </div>
                                <div style="font-size:1.8rem;">
                                    Date:<?php echo date("d-m-y"); ?>
                                </div>
                            </div>
                        </div>
                        <h3 class="text-center"><?php echo $catrow['category_name']?></h3>
                        <table class="table table-hover table-responsive table-striped tablepad" border="2">	
                            <thead>    
                                <tr class="tablepad" style='border-bottom:1px solid black!important;'>
                                    <th rowspan="2" style='border:1px solid black;'>S.No.</th>
                                    <th rowspan="2" style='border:1px solid black;'>Division</th>
                                    <th rowspan="2" style='border:1px solid black;'><?php echo $catrow['category_name'];?></th>
                                    <th  colspan="1"  style='border:1px solid black;'><?php echo $catrow['category_name'];?></th>
                                    <?php
                                        // $sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$catrow['sno']}' and (head_type='deduction' or `head_type`='oth_deduction') and payslip_mode!='1'";;
									$sql = "select * from head_type where (head_type='deduction' or `head_type`='oth_deduction') and payslip_mode!='1'";
                                        $result_deduction = execute_query($sql);
                                        $deduction_count = mysqli_num_rows($result_deduction);

                                        //creating a array for storing deduction variables totel value 
                                        

                                        $deduction_count += 1;
                                        echo '<th style="border:1px solid black;" class="text-center" colspan="'.$deduction_count.'">'.$catrow['category_name'].' Employee Deduction</th>';
                                    ?>
                                    <th  rowspan="2"  style='border:1px solid black;'>Net Pay</th>
                                </tr>
                                <tr>
                                    <th style='border:1px solid black;'>Gross Pay</th>
                                    <?Php
                                    // $sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$catrow['sno']}' and (head_type='deduction' or `head_type`='oth_deduction') and payslip_mode!='1'";
									$sql = "select * from head_type where (head_type='deduction' or `head_type`='oth_deduction') and payslip_mode!='1'";
                                    $result_deduction = execute_query($sql);
                                    $initilize=0;
                                    while($row_deduction = mysqli_fetch_assoc($result_deduction)){
                                        $tot_heads[$row_deduction['sno']] = 0;
                                        echo '<th style="border:1px solid black;">'.$row_deduction['head_name'].'</th>';	

                                        $totdeduction[$initilize]=0;
                                        $initilize++;
                                    }

                                    ?>
                                    <th style='border:1px solid black;'>Total Deduction</th>
                                </tr>
                            </thead>
                            <!-- all the header are here -->
                            <tbody>

                                <?php
                                $sql ='SELECT * FROM `uprnss_division` where 1=1 ';
                                    
                                if(isset($_POST['company_name'])){
                                    if($_POST['company_name'] != ''){
                                        $sql .= ' AND `s_no`="'.$_POST['company_name'].'" ';
                                    }
                                }
                                $sql .= ' order by division_name ASC';
                                $result = mysqli_query($db_erp, $sql);

                                $i=1;
                                $tot_regular_emp['c']=0;
                                $tot_regular_emp['gross_pay']=0;
                                $tot_regular_emp['total_deduction']=0;
                                $tot_agrment_emp['c']=0;
                                while($row=mysqli_fetch_array($result)){ 

                                    //getting count and gross_pay total_deduction for regular employee
                                    $sql="SELECT for_month, employee.company_id,employee.employee_category_id, count(*) c, sum(payslip_report.total_income) as gross_pay, sum(payslip_report.total_deduction) as total_deduction  FROM `payslip_report` left join employee on employee.sno = payslip_report.emp_id where payslip_report.for_month='".$_POST['for_month']."' and payslip_report.financial_year='".$_POST['financial_year']."' and employee.employee_category_id='{$catrow['sno']}' and employee.company_id='".$row['s_no']."'";
                                    if(mysqli_num_rows(execute_query($sql))>0){
                                        $regular_emp = mysqli_fetch_assoc(execute_query($sql));
                                        
                                        $regular_emp['c'] = $regular_emp['c']==''?0:$regular_emp['c'];
                                        $tot_regular_emp['c'] += $regular_emp['c'];//totel employee count
                                        $tot_regular_emp['gross_pay'] += $regular_emp['gross_pay'];
                                        $tot_regular_emp['total_deduction'] += $regular_emp['total_deduction'];
                                    }else{
                                        $regular_emp['c']=0;
                                        $regular_emp['gross_pay']=0;
                                        $regular_emp['total_deduction']=0;
                                    }
                                    

                                    
                                
                                    echo"<tr>
                                    <td style='border:1px solid black;'>".$i++."</td>
                                    <td style='border:1px solid black;'>".$row["division_name"]."</td>
                                    <td style='border:1px solid black;'>".$regular_emp['c']."</td>
                                    <td style='border:1px solid black;'>".$regular_emp['gross_pay']."</td>
                                    ";
                                        
                                        //////////////////////////////

                                        // mid section of it

                                        ////////////////////////

                                    
                                        //getting all the deduction and other deduction

                                        // $sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$catrow['sno']}' and (head_type='deduction' or `head_type`='oth_deduction') and payslip_mode!='1'";
                                        $sql_head_deduction = "select * from head_type where (head_type='deduction' or `head_type`='oth_deduction') and payslip_mode!='1'";
                                        $result_head_deduction = execute_query($sql_head_deduction);

                                        
                                        //all deduction are printing here
                                        $deductioni=0;
                                        while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
                                            
                                        $sql_amount_deduction = "SELECT head_id, for_month, employee.company_id, employee.employee_category_id, COUNT(*) AS c, sum(amount) as value
                                            FROM payslip_report
                                            LEFT JOIN payslip_details ON payslip_report.sno = payslip_details.payslip_id
                                            LEFT JOIN employee ON employee.sno = payslip_report.emp_id
                                            WHERE payslip_report.for_month='".$_POST['for_month']."' 
                                                and head_id='".$row_head_deduction['sno']."' 
                                                AND payslip_report.financial_year='".$_POST['financial_year']."'
                                                AND employee.company_id='".$row['s_no']."'
                                                and employee.employee_category_id='{$catrow['sno']}'";
                                            // if(mysqli_num_rows(execute_query($sql))>0){
                                                
                                            $result_deduction = execute_query($sql_amount_deduction);
                                            $row_amount_deduction = mysqli_fetch_array($result_deduction);
                                            
                                            echo "<td style='border:1px solid black;'>".$row_amount_deduction["value"]."</td>";

                                            $totdeduction[$deductioni]+=$row_amount_deduction["value"];
                                            $deductioni++;
                                        }

                                    echo'<th style="text-align: right;border:1px solid black;">'.$regular_emp['total_deduction'].'</th>';
                                } 
                                ?>
                                </tr>
                                <tr>
                                    
                                    <?php
                                        echo'
                                            <th colspan="2" style="text-align: right;border:1px solid black;">Total:</th>
                                            <th style="text-align: right;border:1px solid black;">'.$tot_regular_emp['c'].'</th>
                                            <th style="text-align: right;border:1px solid black;">'.$tot_regular_emp['gross_pay'].'</th>
                                            
                                            ';

                                        foreach($totdeduction as $ded){
                                            ?>
                                                <th style="text-align: right;border:1px solid black;"><?php echo $ded;?></th>
                                            <?php
                                        }
                                        echo'<th style="text-align: right;border:1px solid black;">'.$tot_regular_emp['total_deduction'].'</th>';	
                                        $totdeduction=[];
                                    ?>
                                    
                                    <th style='border:1px solid black;' class="no-print">&nbsp;</th>
                                </tr>
                            </tbody>
                            <tfoot>
                                <!-- <tr>
                                    <td>
                                        <div class="payroll-foot" style="font-size:1.5rem;font-weight:bold;">
                                            Mentioned and passed for payment of Rs. <?php //echo $tot_total_income; ?> 
                                            <?php
                                                // Assuming $tot_heads['gorss_pay'] is an array
                                                //echo '( '.strtoupper(int_to_words($tot_total_income)).' )';

                                            ?>
                                        </div>

                                    </td>
                                </tr> -->
                            </tfoot>
                        </table>
                        <br>
                    <?php
                }
            ?>
			
			   
			<?php
			
							// print_r($totdeduction);
			?>

			<!-- <div class="payroll-foot">
				mentioned and passed for payment of Rs. <?php //echo $tot_heads['gorss_pay']; ?> 
				<?php
					//$abc="123123";
				 //echo int_to_words($abc);?>
				
			</div> -->
			
			

			<div class="payroll-footer" style="">
				<div>Comp Oprator</div>
				<div>Accontant</div>
				<div>DGM(F)</div>
			</div>
			<?php // } ?>
		</div>
	</div> 
<?php
page_footer();
?>
<script type="text/javascript">
	function select_employee(){
		var d_i = document.getElementById("company_name").value;
		if (d_i != "") {
				$.ajax({
				url: "new_ajax.php?company="+d_i,
				data: "data",
				type: "post",
				success: function(resposedata){
				$('#emp_name').html(resposedata);
			}
			});
		}
	}
</script>