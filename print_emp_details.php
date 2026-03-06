<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

if(isset($_GET['id'])){
	$sql = 'select * from dp_personal_info where sno='.$_GET['id'];
	//echo $sql;
	$row = mysqli_fetch_assoc(execute_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>
	<script>

	function onchangetype()
	{
		var status = document.getElementById("appo_id");
		
		if(status.value == "5")
		{
			document.getElementById("hide").style.display="flex";
		}
		else{
			document.getElementById("hide").style.display="none";
			
			
		}
	}
	
	</script>
	<div class="container col-md-10">
		<div class="card mx-auto border-primary">
			<div class="card-body  ">
				<div class="row d-flex my-auto">		
					<div class="col-md-12">
					<?php
						echo'<table class="table table-bordered" width="100%">
							<tr>
							  <th scope="col" colspan="3" class="p-3 text-center bg-danger text-white"><h3>मानव सम्पदा  मानव संशाधन प्रबंधन प्रणाली के लिए कार्मिक पंजीकरण विवरण </h3></th>
							</tr>
							<tr>
							  <th scope="col" colspan="3" style="background-color:#7dd0fa;">कर्मचारी का बेसिक विवरण :</th>
							</tr>
							<tr>
								<th  width="10%">1</th>
								<th  width="45%">नाम हिन्दी मे </th>
								<td  width="45%">'.$row['full_name'].'</td>
							</tr>
							<tr>
								<th  width="10%">2</th>
								<th  width="45%">नाम अंग्रेजी मे </th>
								<td  width="45%">'.$row['full_name'].'</td>
							</tr>
							<tr>
								<th  width="10%">3</th>
								<th  width="45%">पिता का नाम  </th>
								<td  width="45%">'.$row['father_name'].'</td>
							</tr>
							<tr>
								<th  width="10%">4</th>
								<th  width="45%">लिंग </th>
								<td  width="45%">'.$row['gender'].'</td>
							</tr>
							<tr>
								<th  width="10%">5</th>
								<th  width="45%">जन्मतिथि </th>
								<td  width="45%">'.$row['date_of_birth'].'</td>
							</tr>
							<tr>
								<th  width="10%">6</th>
								<th  width="45%">सेवानिवृत तिथि </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">7</th>
								<th  width="45%">नियुक्ति तिथि </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">8</th>
								<th  width="45%">सेवा आरंभ की तिथि </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">9</th>
								<th  width="45%">राष्ट्रियता </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">10</th>
								<th  width="45%">कैडर </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">11</th>
								<th  width="45%">कैडर मे स्तर /लेवल </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">12</th>
								<th  width="45%">वरिष्ठता क्रमांक </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">13</th>
								<th  width="45%">ई-सैलरी कोड (यदि है )</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">14</th>
								<th  width="45%">कर्मचारी का प्रकार </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">15</th>
								<th  width="45%">कर्मचारी का  वर्ग </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">16</th>
								<th  width="45%">नियुक्ति का प्रकार </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">17</th>
								<th  width="45%">विभागीय कर्मचारी कोड (यदि है )</th>
								<td  width="45%">'.$row['old_employee_code'].'</td>
							</tr>
							<tr>
								<th  width="10%">18</th>
								<th  width="45%">पति / पत्नी का एचआरएमएस कोड (यदि सरकारी सेवा मे है )</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">19</th>
								<th  width="45%">स्वास्थ स्थिति </th>
								<td  width="45%"></td>
							</tr>
							<tr>
							  <th scope="col" colspan="3" style="background-color:#7dd0fa;"> स्थायी  पता विवरण :</th>
							</tr>
							<tr>
								<th  width="10%">20</th>
								<th  width="45%">पता </th>
								<td  width="45%">'.$row['p_address1'].'</td>
							</tr>
							<tr>
								<th  width="10%">21</th>
								<th  width="45%"> गृह राज्य	</th>
								<td  width="45%">'.$row['P_state'].'</td>
							</tr>
							<tr>
								<th  width="10%">22</th>
								<th  width="45%">गृह जनपद</th>
								<td  width="45%">'.$row['p_district'].'</td>
							</tr>
							<tr>
								<th  width="10%">23</th>
								<th  width="45%">पिन कोड</th>
								<td  width="45%">'.$row['p_pin'].'</td>
							</tr>
							<tr>
								<th  width="10%">24</th>
								<th  width="45%">ई -मेल</th>
								<td  width="45%">'.$row['email'].'</td>
							</tr>
							<tr>
								<th  width="10%">25</th>
								<th  width="45%">मोबाइल नंबर</th>
								<td  width="45%">'.$row['c_number'].'</td>
							</tr>
							<tr>
							  <th scope="col" colspan="3" style="background-color:#7dd0fa;">वर्तमान तैनाती  विवरण: </th>
							</tr>
							<tr>
								<th  width="10%">26</th>
								<th  width="45%">वर्तमान तैनाती राज्य </th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">27</th>
								<th  width="45%">वर्तमान तैनाती जनपद	</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">28</th>
								<th  width="45%">वर्तमान तैनाती कार्यालय</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">29</th>
								<th  width="45%"> वर्तमान पद	नाम</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">30</th>
								<th  width="45%"> वर्तमान उप	 पद	नाम</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">31</th>
								<th  width="45%">वर्तमान पद पर कार्यग्रहण की तिथि</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">32</th>
								<th  width="45%">वर्तमान पद पर तैनाती का आदेश संख्या</th>
								<td  width="45%"></td>
							</tr>
							<tr>
								<th  width="10%">33</th>
								<th  width="45%">वर्तमान पद पर तैनाती आदेश की तिथि</th>
								<td  width="45%"></td>
							</tr>
							<tr>
							  <th scope="col" colspan="3" style="background-color:#7dd0fa;">कर्मचारी का स्थापना कार्यालय : </th>
							</tr>
							<tr>
								<th  width="10%">34</th>
								<th  width="45%">स्थापना कार्यालय का विभाग</th>
								<td  width="45%"></td>
							</tr><tr>
								<th  width="10%">35</th>
								<th  width="45%">स्थापना कार्यालय का जिला</th>
								<td  width="45%"></td>
							</tr><tr>
								<th  width="10%">36</th>
								<th  width="45%">स्थापना कार्यालय नाम</th>
								<td  width="45%"></td>
							</tr>
						</table>';
					?>
					</div>
					<table width="100%">
						<tr class="m-3">
							<th colspan="2" style=""><span style="margin:20px;"	>मै ...................................................................... प्रणीत करता हूं /करती हूं की ऊपर दी गई सूचना मेरी जानकारी मे सही है |</span><th>
						</tr>
						<tr >
							<th width="70%"><span style="margin:20px;">दिनांक </span></th>
							<th width="30%"><br><br><br>Signature<br><br><br><br>नाम <br><br>पदनाम<br><br>कार्यालय का नाम </th>
						</tr>
					
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
   
<?php		
page_footer_end();
?>