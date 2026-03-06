<?php


include("scripts/settings.php");
 
$msg='';
$tab=1;
// print_r($_POST);
foreach ($_POST as $k => $v) {
    // Exclude keys that you want to skip
    if ($k !== 'student_ledger' && $k !== 'general_stat_table_length') {
        $chk[] = $v;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    
</head>
<style>
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
			/* display:none; */
		}
		
		table, th, td {
			border: 1px solid black;
			border-collapse: collapse;
		}
		th,td{
			padding:0.2rem;
		}
		@media print{
			.printonly{
				display:block!important;
			}
			#overlays{
				/* display:block; */
				opacity:0.05;
				width:50%!important;
				top: 50%!important;
				-ms-transform: translate(-50%, -50%);
				transform: translate(-50%, -50%);}
			}
		
	@page{
		size:A4 portrait;
	}
	h1{
		font-size: 1.8rem !important;
	}
	h2{
		font-size: 1.5rem !important;
	}
	h3{
		font-size: 1.3rem !important;
	}
	h4{
		font-size: 1rem !important;
	}
	p{
		font-size: .8rem !important;
		padding-left:5rem;
	}
	td{

		font-size: .8rem !important;
	}
	th{
		font-size: .7rem !important;
	}

</style>
<body>

		<div class="row">
            <div class="col-md-12">
                <div class="card">
					<div class="">
						<img src="images/icon.png"  id="overlays" style=" " alt="overlay image" >
					</div>
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="col-md-10 ml-5">
						<p><b><u>अधिशासी अभियन्ता / मुख्य अभियन्ता /प्रबन्ध निदेशक</u></b></p>
						
						<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  सिविल अनुभाग में प्राप्त सूचनाओं के आधार पर विभिन्न विभागों के आवण्टित निर्माण कार्यों हेतु वास्तुविद एवं स्ट्रक्चरल नामित किया जाना हैं। उक्त कार्य हेतु सम्बन्धित अधिशासी अभियन्ताओं द्वारा किये गये अनुरोध के क्रम में परियोजना हेतु वास्तुविद एवं स्ट्रक्चरल कन्सलटेन्ट को नामित किया जाना प्रस्तावित है, जिसका विवरण निम्नवत है:-</p>
                    </div>
					
                    <div class="card-body">
						<table class="table table-hover table-striped" id="general_stat_table">
								<thead>
									<tr>
										<th>क्रं  सं.</th>
										<th>कार्य का नाम </th>
										<th>धनराशि (लाख मे )</th>
										<th>स्तर </th>
										<th>शासनादेश  संख्या/ दिनांक</th>
										<th>जनपद का नाम  </th>
										<th>प्रखण्ड का नाम  </th>
										<th>आर्किटेक्ट </th>
										<th>स्ट्रक्चरल आर्किटेक्ट  </th>
									</tr>
								</thead>
								
								<tbody>
									<?php
										$sql = 'SELECT * FROM `inovoice_architect_allotment` WHERE status!="5" order by sno DESC';
										// echo $sql;
										$result = execute_query($sql);
										$i=1;
										while($row = mysqli_fetch_assoc($result)){
											if(in_array($row['sno'], $chk)){
												$sql = 'select * from uprnss_division where s_no="'.$row['division_id'].'"';
												// echo $sql;
												$divisions = mysqli_fetch_assoc(execute_query($sql));

												$sql = 'select * from uprnss_district where sno="'.$row['district_id'].'"';
												// echo $sql;
												$district = mysqli_fetch_assoc(execute_query($sql));
												
												$sql = 'select * from uprnss_project_temp where sno="'.$row['project_id'].'"';
												// echo $sql;
												$row_project = mysqli_fetch_assoc(execute_query($sql));
												
												$sql = 'select * from uprnss_architect where sno="'.$row['architect_id'].'"';
												// echo $sql;
												$architect = mysqli_fetch_assoc(execute_query($sql));
												
												$sql = 'select * from uprnss_architect where sno="'.$row['structural_architect_id'].'"';
												// echo $sql;
												$starch = mysqli_fetch_assoc(execute_query($sql));
												
												echo '<tr>
												
												<td>'.$i++.'</td>
												<td>'.$row_project['project_name_hindi'].'</td>
												<td>'.$row_project['sanction_cost'].'</td>
												<td>';
												if($row_project['project_type']!=''){
													if($row_project['project_type']=='1'){
														echo '<span class="">शासन स्तर </span>';
													}
													elseif($row_project['project_type']=='2'){
														echo '<span class="">जिला स्तर </span>';
													}
												}
												echo '
												</td>
												<td>'.$row_project['admin_go_no'].'<br>';if ($row_project['admin_go_date'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($row_project['admin_go_date']));};echo'</td>
												
												<td>'.$district['district_name_hindi'].'</td>
												<td>'.$divisions['division_name'].'</td>
												
												
												
												<td>'.$architect['full_name_english'].'</td>
												<td>'.$starch['full_name_english'].'</td>
											
												</tr>';
											}
										}	
									
										?>
									</tbody>
							</table>
					
					</div>
					<div class="col-md-10 ml-5">
						<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  अतः उपरोक्तानुसार वास्तुविद एवं स्ट्रक्चर्स का नाम अंकित कर अनुमोदनार्थ प्रस्तुत है, कृपया सहमति की दशा में स्वीकृति प्रदान करना चाहें।</p>
                    </div>
                </div>
            </div>
		</div>

<script>
window.onload = function() {
            window.print();
        }
</script>
</body>
</html>