<?php
include("scripts/settings.php");
 
$msg='';
$msg1='';
$tab=1;
page_header_start();
page_header_end();
page_sidebar();

if(isset($_GET['reject_sno'])){
	$sql = 'update uprnss_contract_employees set status="1" where sno="'.$_GET['reject_sno'].'"';
	execute_query($sql);
	$msg .= '<div class="alert alert-warning">contract Reject</div>';
}


?>

	
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body text-center">
					<div class="row">
					<?php echo $msg1; ?>
						<div class="col-md-12">
							<h5>अनुबंध के लिए प्राप्त  आवेदन प्रपत्र </h5>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<table class="table table-striped">
								<thead>
									<tr>
										<th scope="col">S No.</th>
										<th scope="col">Application Date</th>
										<th scope="col">Division/Unit Name</th>
										<th scope="col">Full Name</th>
										<th scope="col">Father Name</th>
										<th scope="col">DOB</th>
										<th scope="col">Religion</th>
										<th scope="col">E-mail</th>
										<th scope="col">Contact Number</th>
										<th scope="col">Status</th>
										<th scope="col">Action</th>
										<th scope="col">Print</th>
										
										
									</tr>
								</thead>
								<?php
								$sql = 'SELECT uprnss_contract_employees.sno as sno, entry_date, dp_designation.designation as designation, full_name, father_name, date_of_birth, religion, email, c_number, sanctioned_posts, occupied_posts, running_projects, previous_working_date_from, previous_working_date_to, created_by, status from uprnss_contract_employees left join dp_personal_info on dp_personal_info.sno = employee_id left join dp_designation on dp_designation.sno = uprnss_contract_employees.designation';
								//echo $sql;
								$result = execute_query($sql);
								$i=1;
								while($row = mysqli_fetch_assoc($result)){
									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$row['entry_date'].'</td>
									<td>'.$row['created_by'].'</td>
									<td>'.$row['full_name'].'</td>
									<td>'.$row['father_name'].'</td>
									<td>'.date("d-m-Y", strtotime($row['date_of_birth'])).'</td>
									<td>'.$row['religion'].'</td>
									<td>'.$row['email'].'</td>
									<td>'.$row['c_number'].'</td>
									<td class="no-print text-center">';
										
										if ($row['status']=='0') {
											echo '<span class="text text-warning">Requested</span>';
										}
										elseif ($row['status']=='1') {
											echo '<span class="text text-denger">Reject</span>';
										}
										elseif ($row['status']=='2') {
											echo '<span class="text text-warning">Approve</span></br>';
											
										}
										else {
											echo '<span class="text text-warning">Contact Adm</span>';
										}
										echo '
									</td>
									<td class="no-print text-center">';
										if($row['status']=='0'){
											echo '<a href="verify_contract.php?approve_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Approve"></span></a><br/><br/>
											<a href="verify_contract_employees.php?reject_sno='.$row['sno'].'" class="text text-danger" onClick="return confirm(\'Are you sure want to Reject Contract?\');">Reject Contract</a>';
										}
										
										elseif ($row['status']=='1') {
											echo '<span class="text text-warning">Reject</span>';
										}
										elseif ($row['status']=='2') {
											echo'<a href="print_final_contract.php?id='.$row['sno'].'" target="_blank">print Contract<i class="fa fa fa-print"></i></a>';
										}
										else {
											echo '<span class="text text-warning">Contact Adm</span>';
										}
										echo '
									</td>
									</td>
									<td><a href="print_contract.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>';
								}
								?>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<script>
	$('select[multiple]').multiselect();

	
</script>

</article>
<?php
	
page_footer_start();
page_footer_end();
?>