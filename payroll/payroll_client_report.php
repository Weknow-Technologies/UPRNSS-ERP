<?php
	include("scripts/settings.php");
	page_header_start('Client Report');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);

?>

<script>
	function fetch_select(val){
		$("#project").html(''); 
		$.ajax({
	        url: "ajax.php?id=projects&term="+val,
	        dataType:"json"
	    })
	    .done(function( data ) {
			console.log(data);
			var txt='<option value="">ALL</option>';
			$.each(data, function(key, value){
				console.log(value);
			//	txt='<option value="" >''</option>';
				txt += '<option value="'+value.id+'">'+value.label+'</option>';
			});
			$("#project").html(txt);             
	    }); 
	}
</script>
<div class="container">
	<form method="POST" action="">
		<div class="row">
			<div class="col-sm-3"></div>
			<div class="col-sm-6">
				<div class="panel" style="margin-top:40px;">
					<div class="panel-heading">Client Reoprt</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">
							<tr>
								<td>Select Client</td>
								<td>
									<select class="form-control"  name="client" id="country" onchange="fetch_select(this.value);">
									<option value="">Select</option>
										<?php 
											$sql = "SELECT `sno`, `client_name` FROM `client_details`";	
											$query = execute_query($sql);
											 while ($row = mysqli_fetch_array($query)) { 
											 	echo '<option value="'.$row['sno'].'">'. $row['client_name'] . "</option>";
											 }
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Select Project(optinal)</td>
								<td>
									<select class="form-control" id="project" name="project"></select>

								</td>
							</tr>

						</table>	
						<input type="submit" name="submit" id="submit" value="Submit" class="form-control">			
					</div>
				</div>
			</div>
		</div>
			<div class="panel">
				<div class="panel-heading"></div>
				<div class="panel-body">
					<table class="table table-hover table-responsive table-bordered table-striped">
						<thead>
							  <tr>
								<th>Sno</th>
								<th>Project Name</th>
								<th>Employee Name</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Rate</th>
								<th>Total</th>

							 </tr>
						</thead>
	   					<tbody>
		 					<?php
		 					if(isset($_POST['submit'])){
		 						$var=1;
		 						$id=$_POST['client'];
		 						$project_id=$_POST['project'];
		 						echo $project_id;
		 						if($project_id !=""){
			 						$sql="SELECT projects.project_name as project_name, employee.employee_name as employee_name , attendance.start_from as start_from,
			 						 attendance.from_to as from_to,attendance.rate as rate, attendance.total as total FROM attendance left JOIN projects on 
			 						 attendance.project_id=projects.sno left join employee on attendance.employee_id=employee.sno WHERE attendance.client_id='$id' AND attendance.project_id='$project_id'";
			 						//echo $sql;
			 						
		 						}
		 						else{
		 							$sql="SELECT projects.project_name as project_name, employee.employee_name as employee_name , attendance.start_from as start_from,
			 						 attendance.from_to as from_to,attendance.rate as rate, attendance.total as total FROM attendance left JOIN projects on 
			 						 attendance.project_id=projects.sno left join employee on attendance.employee_id=employee.sno WHERE attendance.client_id='$id'";
		 						}

		 						$result = execute_query($sql);
								if($result == TRUE){						
								  $rowcount=mysqli_num_rows($result);
								 // echo $rowcount;
								  	if($rowcount > 0){
										while($row=mysqli_fetch_array($result)){ 
											echo"<tr>";
											echo "<td>".$var++."</td>";
											//echo "<td>".$row["client_name"]."</td>";
											echo "<td>".$row["project_name"]."</td>";
											echo "<td>".$row["employee_name"]."</td>";
											echo "<td>".$row["start_from"]."</td>";
											echo "<td>".$row["from_to"]."</td>";
											echo "<td>".$row["rate"]."</td>";
											echo "<td>".$row["total"],"</td>";
											echo"</tr>";
										}
					 				}
								}
							}?>
							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</form>
</div>	