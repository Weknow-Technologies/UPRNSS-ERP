<?php
include ("scripts/settings.php");

if (isset($_GET['id'])) {
	$sql = 'SELECT * FROM inovoice_architect_allotment WHERE sno="' . $_GET['id'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));

	$arch_data = null;
	$str_arch_data = null;

	if (!empty($data['architect_id'])) {
		$sql = 'SELECT * FROM uprnss_architect WHERE sno="' . $data['architect_id'] . '"';
		$arch_data = mysqli_fetch_assoc(execute_query($sql));
	}

	if (!empty($data['structural_architect_id'])) {
		$sql = 'SELECT * FROM uprnss_architect WHERE sno="' . $data['structural_architect_id'] . '"';
		$str_arch_data = mysqli_fetch_assoc(execute_query($sql));
	}

	$sql = 'SELECT * FROM uprnss_project_temp
            LEFT JOIN uprnss_district ON uprnss_project_temp.district_id = uprnss_district.sno
            LEFT JOIN uprnss_division ON uprnss_project_temp.division_id = uprnss_division.s_no
            WHERE uprnss_project_temp.sno = "' . $data['project_id'] . '"';
	$project_data = mysqli_fetch_assoc(execute_query($sql));

	$sql = 'SELECT * FROM  uprnss_division
	LEFT JOIN master_zone ON master_zone.sno = uprnss_division.zone_id
	where s_no = "'.$data['division_id'].'"';
	$zone_data = mysqli_fetch_assoc(execute_query($sql));
}
?>
<style>
	@page {
		size: A4;
		margin-top: 20px;
		/* Adjust this margin based on your header height */
	}

	body {
		font-size: .8rem;
		font-family: Arial, sans-serif;
	}

	table,
	th,
	td {
		border: 1px solid black;
		border-collapse: collapse;
	}

	div {
		font-size: 0.7rem;
	}

	h1 {
		font-size: 1.8rem !important;
	}

	h2 {
		font-size: 1.5rem !important;
	}

	h3 {
		font-size: 1.3rem !important;
	}

	h4 {
		font-size: 1.1rem !important;
	}

	p {
		font-size: .9rem !important;
	}

	td {

		font-size: .6rem !important;
	}

	th {
		font-size: .6rem !important;
	}

	.heads {
		display: flex;
		justify-content: center;
		align-items: center;
		padding-inline: 2rem;
		position: relative;
	}

	#overlays {
		z-index: 0;
		opacity: 0.15;
		position: fixed;
		top: 50%;
		left: 50%;
		-ms-transform: translate(-50%, -50%);
		transform: translate(-50%, -50%);
		/* display:none; */
	}

	@media print {
		* {
			margin: 0px !important;
			margin-block: 2px !important;
			padding: 3px !important;
			box-sizing: border-box !important;
		}

		#overlays {
			display: block;
			width: 45% !important;
			top: 50% !important;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
		}

		body {
			padding: 0rem !important;
		}

		.heads {
			text-align: center;
		}

		.cont {
			padding-right: 50%;
		}

		table {
			width: 100%;
		}

		ol,
		li {
			page-break-inside: auto !important;

		}

		ol {
			padding-left: 0.7rem !important;
		}

		tbody>tr>td>table th {
			font-weight: bolder !important;
			font-size: 0.75rem !important;
		}

		tbody>tr>td>table td {
			font-size: 0.7rem !important;
		}

		td {
			padding: 4px 2px !important;

		}

		@page {
			size: A4;
			margin: 40px 30px 40px;
		}
	}

	.hrline {
		border-top: 1px soild #333;
	}
</style>
<div class="cont">
	<div class="">
		<img src="images/icon.png" id="overlays" style=" " alt="overlay image">
	</div>
	<div class="card mx-auto border-primary">

		<table>
			<thead>
				<tr>
					<td>
						<div class="heads">
							<div class="cont-img" style="position:absolute;left:1rem;">
								<img src="images/icon.png" height="90">
							</div>
							<div class="cont-info " style="text-align:center;">
								<h4 class="title"
									style="font-weight:bolder;margin-left:2rem!important;text-decoration:underline;">
									उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)
								</h4>
								<h5>
									(पूर्ववर्ती नाम पैकफेड)
									<br>
									राजकीय निर्माण एजेन्सी
								</h5>
							</div>
						</div>
					</td>
				</tr>
			</thead>
			<!-- <div class="container no-print ">
				<button class="btn btn-info mt-3" onclick="downloadPDF()" style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Download PDF</button>
			</div> -->
			<tbody>
				<tr>
					<td>
					<div class="cont" style="display:flex;justify-content:space-between;">
							<div>पत्रांकः मु.अभि. /&nbsp;&nbsp; <?php echo $data['invoice_no'] ?> </div>
							<div style="padding-right:50%;">/क्यू०एफ०/सिविल/टी-172(22)</div>
							<div>दिनांक / <?php echo $data['approval_date']; ?></div>
						</div>
						<div class="cont" style="font-weight:bolder;">
							<div><?php
							echo $arch_data['full_name_english']; ?> <br> <?php echo  $arch_data['address'] ?> </div><br>
							<div>विषय-आर्कीटेक्चरल ड्राइंग डिजाइन तथा विस्तृत आगणन तैयार करने के सम्बन्ध में। </div>
						</div>
						<div style="margin-left:3rem;text-align:justify;">
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; कृपया निम्न निर्माण कार्य हेतु यूपीआरएनएसएस को
							कार्यदायी नामित किया गया है। उक्त निर्माण कार्य के विस्तृत आगणन / डी०पी०आर० तैयार करने हेतु
							निम्न शर्तों के अन्तर्गत आपको नामित किया जाता है-
						</div>
						<br>

						<table class="table" width="100%">
							<tr>
								<th>क्रम संख्या </th>
								<th>कार्य का नाम </th>
								<th>शासनादेश संख्या </th>
								<th>जनपद का नाम </th>
								<th>प्रखण्ड का नाम </th>
							</tr>
							<tr>
								<?php
								echo '<th>1.</th>	
										<th>' . $project_data['project_name_hindi'] . '</th>
										<th>' . $project_data['admin_go_no'] . '</th>
										<th>' . $project_data['district_name_hindi'] . '</th>
										<th>' . $project_data['division_name'] . '</th>';
								?>
							</tr>
						</table>
						<div class="m-4">
							<ol>
								<li>निर्माण स्थल के निरीक्षण के सर्वे के उपरान्त ही एवं लोक निर्माण विभाग की <b>स्थानीय
										दरों पर </b> डी०पी०आर० तैयार किया जाये।</li>
								<li>निर्माण स्थल का आवश्यक कन्ट्रर तथा सर्वे प्लान सम्बन्धित प्रखण्ड प्रभारी द्वारा
									उपलब्ध कराया जायेगा जिसके अनुसार डी०पी०आर० तैयार किया जायेगा।</li>
								<li>निर्माण स्थल के निरीक्षण से सम्बन्धित पत्रांक क्यू०एफ०/सि०/24 दिनांक 23 जून, 2006 का
									अनुपालन अनिवार्यता से किया जाय।</li>
								<li>निर्धारित अवधि में डी०पी०आर० उपलब्ध न कराये जाने की स्थिति में जमानत धनराशि जब्त की
									जा सकती है। सम्बन्धित प्रखण्ड प्रभारी को डी०पी०आर० पाँच प्रतियों में उपलब्ध करायी
									जायेगी। </li>
								<li>कन्सल्टेन्सी का भुगतान निम्न प्रकार से किया जायेगा-
									<ol style="margin-left:0.1rem;">
										<li>यदि किसी परियोजना का अनुमोदित प्लान ग्राहक विभाग से प्राप्त नहीं होता है
											अथवा मानकीकृत नहीं है। तो वास्तुविदीय कन्सल्टेन्सी के अन्तर्गत कराये जाने
											वाले कार्य (Scope of Services) हेतु वास्तुविदीय कन्सलटेन्सी हेतु 1.15% की दर
											से भुगतान किया जायेगा।

											<!-- <ol type="I" style="margin-left:0.1rem;">
												<li>वास्तुविदीय कन्सल्टेन्सी के अन्तर्गत कराये जाने वाले कार्य (Scope of
													Services) हेतु आर्कीटेक्चरल कन्सलटेन्सी हेतु 1.15% की दर से भुगतान
													किया जायेगा।</li>
												<li> स्ट्रक्ारल कन्सल्टेन्सी के अन्तर्गत कराये जाने वाले कार्य (Scope of
													Services) हेतु स्ट्रक्चरल कन्सल्टेन्सी हेतु 0.35% की दर से भुगतान
													किया जायेगा।</li>

											</ol> -->
										</li>
										<li>
											यदि किसी परियोजना का अनुमोदित प्लान ग्राहक विभाग से प्राप्त होता है अथवा
											मानकीकृत है तथा उसमें अत्यन्त सूक्ष्म (Very Little) परिवर्तन होता है अथवा
											कोई भी परिवर्तन नहीं होता है तो वास्तुविदीय कन्सल्टेन्सी के अन्तर्गत कराये
											जाने वाले कार्य (Scope of Services) हेतु वास्तुविदीय ड्रॉइंग कन्सलटेन्सी का
											0.85% की दर से भुगतान किया जायेगा।
											<!-- <ol type="I" style="margin-left:0.1rem;">
												<li>
													वास्तुविदीय कन्सल्टेन्सी के अन्तर्गत कराये जाने वाले कार्य (Scope of
													Services) हेतु आर्कीटेक्चरल ड्राइंग कन्सल्टेन्सी का 0.85% की दर से
													भुगतान किया जायेगा।
												</li>
												<li>
													स्ट्रक्चरल कन्सल्टेन्सी के अन्तर्गत कराये जाने वाले कार्य (Scope of
													Services) हेतु स्ट्रक्चरल कन्सल्टेन्सी का 0.35% की दर से भुगतान किया
													जायेगा। 3-प्रत्येक पुनरावृत्ति हेतु कन्सल्टेन्सी की पूर्ण दरों
													(क्रमांक-1) के 50% के आधार पर भुगतान देय होगा।
												</li>
											</ol> -->
										</li>
										<li>
											प्रत्येक पुनरावृत्ति हेतु कन्सल्टेन्सी की पूर्ण दरों (क्रमांक-1) के 50% के
											आधार पर भुगतान देय होगा।
										</li>
									</ol>

								</li>
								<!-- <li>
									उपरोक्त परियोजना / परियोजनाओं के स्ट्रक्चरल डिजायन एवं ड्राइंग स्ट्रक्चरल
									कन्सल्टेन्सी के अन्तर्गत कराये जाने वाले कार्य (Scope of Services) के अनुसार कार्य
									मै० ए.टी.एस. स्ट्रक्चरल कन्सल्टेन्ट द्वारा किया जायेगा।
								</li> -->
								<li>
									कन्सल्टेन्सी फीस की गणना स्वीकृत प्राक्कलन की लागत पर की जायेगी। जिसमें निम्नलिखित
									की लागत सम्मिलित नही होगी:-
									<ol type="I" style="margin-left:0.1rem;">
										<li>
											Contigencies.centage/supervision charges.
										</li>
										<li>
											External power sewer, water supply connection charge.
										</li>
										<li>
											Payment to Development Authorities/Local bodies for sanction of map.
										</li>
										<li>
											Taxes or any other payment made directly to Govt./Deptt
										</li>
										<li>Cost of Earth filling as sanctioned by EFC/GO.
										</li>
										<li>Any other item sanctioned for which architectural services are not required.
										</li>
									</ol>
								</li>
								<li>
									10% of the Running Bit amount for the stages mentioned, shall be deducted toward
									security. This security shall be
									released within 3 months of the completion of the project. In special circumstances,
									when the completion is held up, this
									security money may be released eartier as per merit of the case giving full
									justification.
								</li>
								<li>
									आप द्वारा ऐसा कोई कार्य नहीं किया जायेगा जिसका विपरीत प्रभाव यूपीआरएनएसएस के व्यवसाय
									पर पड़े तथा यूपीआरएनएसएस से सम्बन्धित अभिलेखों की गोपनीयता बनायी रखी जायेगी।
								</li>
								<li>
									शासन से प्रशासनिक / वित्तीय स्वीकृति एवं धन प्राप्त होने पर ही भुगतान देय होगा। यदि
									परियोजना पर धन प्राप्त नही होता है तो भुगतान देय नही होगा।
								</li>
								<li>
									कन्सल्टेन्सी मद का भुगतान तकनीकी स्वीकृति जारी होने के उपरान्त ही किया जायेगा।
								</li>
								<li>
									यूपीआरएनएसएस के पत्रांक सी-84/क्यू०एफ०/सिविल/टी-172 दिनॉक 06.07.12 में दिये गये
									निर्देशों के अनुसार डी०पी०आर० एवं वर्किग ड्राइंग को तैयार किया जाय।
								</li>
								<li>
									प्रस्तुत किये गये साइट प्लान एवं बिल्डिंग प्लान में नियमानुसार फायर फाइटिंग हेतु
									निर्धारित मानक के अनुसार भवन का मानचित्र तैयार किया जायेगा।
								</li>
								<li>उपर्युक्त शर्तों के तहत उल्लिखित निर्माण कार्यों के आर्कीटेक्चरल ड्राइंग / डिजाइन
									तथा विस्तृत आगणन / डी०पी०आ० निर्गत आदेश के 15 दिन के अन्दर देना सुनिश्चित करें।</li>
								<!-- <li>
									निर्धारित समयावधि के अन्दर सम्बन्धित प्रखण्ड प्रभारी को आगणन प्रेषण के उपरान्त
									प्रेषित आगणन की सूचना (केवल कार्य के नाम एवं लागत के साथ) पृथक रूप से मुख्यालय के
									सम्बन्धित पटल प्रभारी-श्री सुरेन्द्र कुमार के मो० न०-9151063603 पर Whatsapp पर देंगे
									तथा Call करके अनिवार्य रूप से अवगत कराया जाना सुनिश्चित करेंगे।
								</li> -->
							</ol>
						</div>
						<div style="margin-left:3rem;text-align:justify;">
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; निर्धारित समयावधि के अन्दर सम्बन्धित प्रखण्ड
							प्रभारी को आगणन प्रेषण के उपरान्त प्रेषित आगणन की सूचना (केवल कार्य के नाम एवं लागत के साथ)
							पृथक रूप से मुख्यालय के सम्बन्धित पटल प्रभारी-श्री सुरेन्द्र कुमार के मो० न०-9151063603 पर
							Whatsapp पर देंगे तथा Call करके अनिवार्य रूप से अवगत कराया जाना सुनिश्चित करेंगे।
						</div>
						<!-- <div style="text-align:right;font-size:1.1rem;font-weight:bolder">
							(ए०के०सिंह) <br>
							मुख्य अभियन्ता
						</div> -->
						<div class="cont" style="display:flex;justify-content:space-between;">
							<div>पत्रांकः मु.अभि. /&nbsp;&nbsp; <?php echo $data['invoice_no'] + 1 ?> -
								<?php echo $data['invoice_no'] + 4 ?>
							</div>
							<div style="padding-right:50%;">क्यू०एफ०/सिविल/टी-172(22)</div>
							<div>दिनांक
								&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;<?php echo $data['approval_date']; ?>
							</div>
						</div>
						<div style="font-weight:bolder">
							प्रतिलिपिः-सूचनार्थ लिपिः सूचनार्थ एवं आवश्यक कार्यवाही हेतु प्रेषित।
						</div>
						<div class="m-4">
							<ol>
								<!-- <li>
									<b><?php
									if ($str_arch_data !== null) {
										echo $str_arch_data['full_name_english'] . ' ' . $str_arch_data['address'];
									} else {
										echo 'Structural Architect Not Alloted';
									}
									?>
									</b> को इस निर्देश के साथ कि वे सम्बन्धित वास्तुविद 
									से आर्कीटेक्चरल ड्राइंग, प्राप्त कर डिजाइन/ड्राइंग, डिजाइनिंग डिटेल एवं वर्किंग ड्राइंग तैयार करते हुए सम्बन्धित वास्तुविद को एक सप्ताह में उपलब्ध करायें एवं मुख्य सचिव, उ०प्र० शासन के निर्देश दि0-174. 06. भूकम्परोधी दिशा निर्देश, नेशनल बिल्डिंग कोड 2005 (उ०प्र० डिजास्टर मैनेजमेन्ट अथारिटी गाइडलाइन्स) का भी अनुपालन सुनिश्चित करें।
								</li> -->
								<li>
								<b>अधीक्षण अभियन्ता परिक्षेत्र मण्डल <?php echo $zone_data['zone_name']; ?></b>
								</li>
								<li>
									<b>प्रखण्ड प्रभारी, यूपीआरएनएसएस, निर्माण प्रखण्ड
										<?php echo $project_data['division_name']; ?></b> को इस निर्देश के साथ प्रेषित कि परियोजना से सम्बन्धित सर्वे रिपोर्ट तत्काल वास्तुविद कन्सल्टेन्ट को उपलब्ध कराना सुनिश्चित करें तथा गहनता से अनुश्रवण करते हुए तत्काल डी०पी०आर० को स्वीकृति हेतु मुख्यालय को प्रेषित करें।
								</li>
								<li>
									सम्बन्धित पटल प्रभारी की ई-मेल आईडी-see843@gmail.com, amitmaurya.lko@gmail.com,
									ajai.prabhakar@rediffmail.com, deep.srid@gmail.com पर सूचनार्थ प्रेषित।
								</li>
								<li>
									उप सामान्य प्रबन्धक (वित्त), यूपीआरएनएसएस मुख्यालय।
								</li>
							</ol>
						</div>
						<p style="text-align:right;font-size:1.1rem;font-weight:bolder">मुख्य अभियन्ता</p>
					</td>
				</tr>
			</tbody>
			<tfoot valign="bottom">
				<tr>
					<td>
						<div class="hrline"></div>
						<div style="text-align:center;font-weight:bolder;">
							पत्ता-जी-4/5, बी गोमतीनगर विस्तार सेक्टर-4 लखनऊ-226010 <br>
							फोन नं0-2390150, 2390151 <br>
							Email-cenacefed@gmail.com.ho@nacefed.orv. nacefedho@email.com web site-unenscore
						</div>
					</td>
				</tr>
			</tfoot>
		</table>

	</div>
</div>

<script>
	function downloadPDF() {
		const element = document.getElementById('project-table');
		html2pdf().from(element).save('project_details.pdf');
	}
</script>