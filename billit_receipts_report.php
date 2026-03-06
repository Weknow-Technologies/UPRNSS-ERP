<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");$msg='';
$msg='';
$msg1='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();



?>		

			<div class="card">

			  <div class="row">
				
				<div class="col-12">
					<div class="text-right">
					<a href="fund_recive.php?view=view"><button type="button" class="btn btn-warning">Create New Recive Voucher</button></a>
				</div>


					<table class="table table-striped table-hover">

						<tr>

							<thead>

								<th>S.No.</th>

								<th>Date</th>

								<th>Unit Name</th>

								<th>Voucher No.</th>

								<th>Particulars</th>

								<th>Amount</th>

								<th></th>

							</thead>

							<tbody>

								<?php

								if($_SESSION['usertype']!='sadmin' && $_SESSION['usertype']!="6" ){

			  					    $sql = 'select * from billit_invoice_erp_receipt where created_by="'.$_SESSION['username'].'"';

								}

								else{

								    $sql = 'select * from billit_invoice_erp_receipt ';

								}

			  					$result = execute_query($sql);

			  					$i=1;

			  					while($row = mysqli_fetch_assoc($result)){

									echo '<tr>

									<td>'.$i++.'</td>

									<td>'.$row['timestamp'].'</td>

									<td>'.get_division($row['unit_id']).'</td>

									<td>'.$row['voucher_no'].'</td>

									<td>'.get_ledger($row['first_by']).'</td>

									<td>'.$row['tot_debit'].'</td>

									<td><a href="billit_recive_print.php?id='.$row['sno'].'" target="_blank">View</a></td>

									</tr>';

								}

								?>

								

							</tbody>

						</tr>

					</table>

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
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['department_name_hindi'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-2"><div class="form-group"><label >उप विभाग</label><select class="form-control" name="sub_department_id_'+id+'" id="sub_department_id_'+id+'" value="" ></select></div></div><div class="col-md-2"><div class="form-group"><label >आच्छादित जनपद</label><select class="form-control" name="district_id_'+id+'" id="district_id_'+id+'" onChange="fill_project(this.value, '+id+')"></select></div></div><div class="col-md-3"><div class="form-group"><label>परियोजना का नाम</label><br><select class="form-control" name="project_id_'+id+'" id="project_id_'+id+'" ></select></div></div><div class="col-md-2"><div class="form-group"><label>परियोजना पर प्राप्त  राशि </label><br><input type="text" name="p_receive_amount_'+id+'" id="p_receive_amount_'+id+'" class="form-control" placeholder="" value="" onInput="addCalc('+id+')"></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
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
