<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();
?>		
	<div class="row">
		<div class="col-md-12">
			<div class="card strpied-tabled-with-hover">
				<div class="card-header ">
				</div>
				<div class="card-body table-full-width table-responsive">
					
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Fund Recevied at</th>
								<th>Order No</th>
								<th>Order Date</th>
								<th>Installment</th>
								<th>Tot Received Amount</th>
								<th>Credit Date</th>
								<th >Remark</th>
								<th class="no-print">View Voucher</th>
								<th class="no-print">Edit</th>
								<th class="no-print">Delete</th>
								
								
							</tr>	
						</thead>
						<tbody>
							<?php
							$sql = 'SELECT * FROM `invoice_fund_receive` where  sno="'.$_GET['id'].'" ORDER BY sno desc';
							// echo $sql;
							$result = execute_query($sql);
							$i=1;
							while($row = mysqli_fetch_assoc($result)){	
							    
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row['fund_receive_to'].'</td>
								
								<td>'.$row['order_no'].'</td>
								<td>'.$row['order_date'].'</td>
								<td>'.$row['installment'].'</td>
								<td>'.$row['tot_receive_amount'].'</td>';
								echo '<td>'.$row['receive_date'].'</td>';
								echo'<td>'.$row['remark'].'</td>
								<td class="no-print"><a href="billit_recive_print.php?voucher='.$row['sno'].'" target="_blank">View</a></td>
								<td class="no-print text-center">
									<a href="fund_recive.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');" target="_blank"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Data"></span></a>
								</td>
								<td class="no-print text-center">
									<a href="fund_recive.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
								</td>
								</tr>';
							
							}
							$sql= 'select *from transaction_fund_receive where invoice_id="'.$_GET['id'].'"';
								$result_act = execute_query($sql);
								$act_txt = '
								<tr><td colspan="11">
								<table>
								<tr>
									<th>Department</th>
									<th>District</th>
									<th>Project Name </th>
									<th>Amount</th>
								</tr>';
								if(mysqli_num_rows($result_act)!=0){
									$count_act = mysqli_num_rows($result_act);
									while($row_transaction = mysqli_fetch_assoc($result_act)){
										$sql = 'select * from uprnss_department_name where sno="'.$row_transaction['department_id'].'"';
										$department = mysqli_fetch_assoc(execute_query($sql));
							
										$sql = 'select * from uprnss_district where sno="'.$row_transaction['district_id'].'"';
										$district = mysqli_fetch_assoc(execute_query($sql));
										
										$sql = 'select project_name_hindi from uprnss_project_temp where sno="'.$row_transaction['project_id'].'"';
										$project = mysqli_fetch_assoc(execute_query($sql));
												
										$act_txt .= '<tr>
											<td>'.$department['department_name_english'] . " (" . $department['department_name_hindi'] . ")".'</td>
											<td>'.$district['district_name_english'].'</td>
											<td>'.$project['project_name_hindi'].'</td>
											<td>'.$row_transaction['p_receive_amount'].'</td>
										</tr>';
									}
								}
								else{
									$count_act = 0;
								}
								$act_txt .= '</table></td></tr>';
								echo $act_txt;
							?>
						</tbody>
					</table>
					<!--/*Sample Report 2nd type ends*/-->
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
		
	function percent_amt_calc(){
	// Get the values from the respective elements

		let totamount = document.getElementById('tot_receive_amount').value;
		let tdsper=document.getElementById('tds_per').value;
		let tdsdeducted=document.getElementById('tds_deducted');

		let gsttdsper=document.getElementById('gst_tds_per').value;
		let gsttdsdeducted=document.getElementById('gsttds_deducted');

		let laboursess = document.getElementById('labour_sess');

		let totcreditamount = document.getElementById('tot_credit_amount')


		tdsdeducted.value=((totamount*tdsper)/100).toFixed(2);
		gsttdsdeducted.value=((totamount*gsttdsper)/100).toFixed(2);
		totcreditamount.value=totamount-tdsdeducted.value-gsttdsdeducted.value-laboursess.value;


		
	}


	
	function addCalc(line_serial){
		var id = parseFloat($("#add_rows_id").val());
		var tot=0;
		for(i=1; i<=id; i++){
			var amt = $('#p_receive_amount_'+i).val();
			amt = parseFloat(amt);
			if(!amt){
				amt = 0;
			}
			tot += amt;
			console.log("AMT : "+amt+" : ID: "+i);
		}
		$("#tot_receive_amount").val(tot);
		percent_amt_calc();
		
		
	}			

	$('select[multiple]').multiselect();
		
			function add_rows(){
				var id = parseFloat($("#add_rows_id").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();
				
				var txt = id+'<div id="add_rows_length" class="row"><div class="col-md-2 "><div class="form-group"><label >विभाग</label><br><select class="form-control" name="department_id_'+id+'" id="department_id_'+id+'" onChange="fill_district(this.value, '+id+'),fill_sub_department(this.value, '+id+')"><option value="">--- Select ---</option>';
				<?php
				if(!empty($_SESSION['department'])){
					$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in ('.implode(",", $_SESSION['department']).') group by department_id) ';
				}
				elseif(!empty($_SESSION['divisions'])){
					$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in ('.implode(",", $_SESSION['divisions']).') group by division_id ) ';
				}
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['department_name_english'] . " (" . $data['department_name_hindi'] . ")".'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-2"><div class="form-group"><label >उप विभाग</label><select class="form-control" name="sub_department_id_'+id+'" id="sub_department_id_'+id+'" value="" ></select></div></div><div class="col-md-2"><div class="form-group"><label >आच्छादित जनपद</label><select class="form-control" name="district_id_'+id+'" id="district_id_'+id+'" onChange="fill_project(this.value, '+id+')"></select></div></div><div class="col-md-3"><div class="form-group"><label>परियोजना का नाम</label><br><select class="form-control" name="project_id_'+id+'" id="project_id_'+id+'" ></select></div></div><div class="col-md-2"><div class="form-group"><label>Project Amount</label><br><input type="text" name="p_receive_amount_'+id+'" id="p_receive_amount_'+id+'" class="form-control" placeholder="" value="" onInput="addCalc('+id+')"></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
				$("#test").append(txt);
				$("#add_rows_id").val(id);
			}
	
		var actionUrl = 'scripts/ajax.php';

		function fill_sub_department(val, selected){
			var data = {"term":"b", "id":"sub_dep", "val":val};

			$.ajax({
				type: "POST",
				url: actionUrl,
				data: data, // serializes the form's elements.
				success: function(ajaxdata){
					console.log(ajaxdata);
					var txt = '<option value="">--Select--</option>';
					ajaxdata = JSON.parse(ajaxdata);
					$.each(ajaxdata, function(key, value){
						txt += '<option value="'+value.id+'" ';
						if(selected==value.id){
							txt += ' selected ';
						}
						txt += '>'+value.sub_department_hindi+'</option>';
						
					});
					$("#sub_department_id_"+selected).html(txt);
				}
			});
		}

		function fill_district(val, selected){
			// alert(selected);
			var data = {"term":"b", "id":"dist", "val":val};
			$.ajax({
				type: "POST",
				url: actionUrl,
				data: data, // serializes the form's elements.
				success: function(data){
					var txt = '<option value="">--Select--</option>';
					data = JSON.parse(data);
					$.each(data, function(key, value){
						txt += '<option value="'+value.id+'" ';
						if(selected==value.id){
							txt += ' selected ';
						}
						txt += '>'+value.district_name+'</option>';
						
					});
					$("#district_id_"+selected).html(txt);
				}
			});
		}
			
		function fill_project(val, selected){
			//"department_1";
			
			var data = {"term":"b", "id":"proj", "val":val, "dept":$("#department_id_"+selected).val()};
			$.ajax({
				type: "POST",
				url: actionUrl,
				data: data, // serializes the form's elements.
				success: function(data){
					var txt = '<option value="">--Select--</option>';
					data = JSON.parse(data);
					$.each(data, function(key, value){
						txt += '<option value="'+value.id+'" ';
						if(selected==value.id){
							txt += ' selected ';
						}
						txt += '>'+value.project_name_hindi+'</option>';
						
					});
					$("#project_id_"+selected).html(txt);
				}
			});
		}
	
	function view_ledger(val){
		var txt = '<a href="report_ledger_project.php?id='+val+'" target="_blank"><small>View Ledger</small></a>';
		$("#ledger_link").html(txt);
		
	}

	




<?php
	if(isset($_GET['edit_sno'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
		fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district_id']; ?>);
		fill_project(<?php echo $_POST['district_id']; ?>, <?php echo $invoice['project_id']; ?>);
		//fill_project_details(<?php echo $invoice['project_id']; ?>);
		
	}); 
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>
