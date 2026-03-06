<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Note Sheet</title>
    <style>
        body {
            font-family: "Kruti Dev", Arial, sans-serif;
            font-size: 14px;
            margin-top: 90px;
            padding: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 0.4rem;
            text-align: center;
            word-wrap: break-word;
            max-width: 200px;
        }
		#overlays {
            z-index: 0;
            opacity: 0.15;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
         .body-text {
			display: flex;
			justify-content: space-between;
			text-align: right;
			margin: 1rem 0;
			font-size: 1.54rem;
		}

		.left-div {
			width: 15%; 
		}

		.right-div {
			width: 85%;
			text-indent: 1rem;
			text-align: left;
		}

        .bold {
            font-weight: bold;
        }
		@media print {
            .printonly {
                display: block !important;
            }
            #overlays {
                opacity: 0.05;
                width: 50% !important;
            }
        }
    </style>
</head>
<body>
	<div>
		<img src="images/icon.png" id="overlays" alt="overlay image">
	</div>

    <div class="body-text">
		<div class="left-div">
			<p></p>
		</div>
		<div class="right-div">
			<p><b><u>लेखाकार/वित्तीय सलाहकार/अधिशासी अभियन्ता</u></b></p><br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; कृपया पत्रावली के दाहिनी तरफ संलग्न बिलों का अवलोकन करना चाहें जो कि अवर अभियन्ता/सहायक अभियन्ता द्वारा मापें अंकित कर बिल का भुगतान हेतु प्रस्तुत किया गया है। बिल का विवरण निम्नवत् है-
		</div>
	</div>

				<table>
					<thead>
                        <tr>
                            <th>SL NO.</th>
                            <th>Firm Name</th>
                            <th>Project Name</th>
                            <th>Bill No.</th>
                            <th>Bill Date</th>
                            <th>Bill Amt.After T. Less</th>
                            <th>I.Tax</th>
                            <th>Security</th>
                            <th>Royalty</th>
                            <th>GST TDS</th>
                            <th>GST</th>
                            <th>Other</th>
                            <th>Net Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!isset($_GET['id'])) {
                            echo "<tr><td colspan='13' style='text-align: center; color: red;'>Error: ID parameter is required</td></tr>";
                        } else {
                        $sql1 = "SELECT * FROM `project_note_sheet` WHERE id= {$_GET['id']}";
                        $result_sheet = execute_query($sql1);
                        $row_fund_id = mysqli_fetch_assoc($result_sheet);
                        $bill_snos = $row_fund_id['related_bill_snos'];

                        $sql = "SELECT * FROM `invoice_account_fund_transafer` WHERE sno IN ($bill_snos) ORDER BY sno DESC";
                        $result = execute_query($sql);
                        $row_count = mysqli_num_rows($result);
                        $i = 1;
						$net_payment=0;
						$total_transafer_amount=0;
							$total_incometax=0;
							$total_security=0;
							$total_royalty=0;
							$total_gsttds=0;
							$total_other_amount=0;
							$total_sentage=0;
							$total_gstdeduction=0;
							$total_total_expen=0;
                        while($row = mysqli_fetch_assoc($result)) {
                            // Use working queries from note_sheet_trail.php
                            $divisions = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_division WHERE s_no="' . intval($row['unit_id']) . '"'));
                            $district = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_district WHERE sno="' . intval($row['district']) . '"'));
                            $row_project = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_project_temp WHERE sno="' . intval($row['project_name']) . '"'));
                            $vendor = mysqli_fetch_assoc(execute_query('SELECT tender_allotment.sno, firm_name FROM tender_allotment LEFT JOIN vendor ON tender_allotment.project_awarded_to = vendor.sno WHERE project_id = "' . intval($row['project_name']) . '" AND tender_allotment.status != 5'));
                            
                            // NaN fix: Check if other_title is numeric
                            $other_title_id = is_numeric($row['other_title']) ? intval($row['other_title']) : 0;
                            $row_oth = mysqli_fetch_assoc(execute_query('SELECT * FROM fund_transfar_other_title WHERE sno="' . $other_title_id . '"'));
							
							$net_payment += floatval($row['praposemoney']);
							$total_transafer_amount += floatval($row['transafer_amount']);
							$total_incometax += floatval($row['incometax']);
							$total_security += floatval($row['security']);
							$total_royalty += floatval($row['royalty']);
							$total_gsttds += floatval($row['gsttds']);
							$total_other_amount += floatval(preg_replace('/[^\d.]/', '', $row['other_amount'] ?? "0"));
							$total_sentage += floatval($row['sentage']);
							$total_gstdeduction += floatval($row['gstdeduction']);
							$total_total_expen += floatval($row['total_expen']);

                            echo '<tr>
                                <td>'. $i++ .'</td>
                                <td>'. $vendor['firm_name'] .'</td>
                                <td>'. $row_project['project_name_hindi'] .'</td>
                                <td>'. $row['bill_no'] .'</td>
                                <td>'. ($row['bill_date'] ? date("d-m-Y", strtotime($row['bill_date'])) : '-') .'</td>
                                <td>'. $row['transafer_amount'] .'</td>
                                <td>'. $row['incometax'] .'('. $row['it_per'] .'%)</td>
                                <td>'. $row['security'] .'('. $row['security_per'] .'%)</td>
                                <td>'. ($row['royalty']?? "0") .'</td>
                                <td>'. $row['gsttds'] .'('. $row['gsttdspercentage'] .'%)</td>
                                <td>'. $row['gstdeduction'] .'('. $row['gst_per'] .'%)</td>
                                <td>'. (isset($row_oth['title_name']) ? $row_oth['title_name'] : '') .'<br>'. floatval(preg_replace('/[^\d.]/', '', $row['other_amount'] ?? "0")) .'</td>
                                <td>'. $row['praposemoney'] .'</td>
                            </tr>';
							}
						?>
						<tr>
							<th colspan="5">Total</th>
							<th><?php echo $total_transafer_amount; ?></th>
							<th><?php echo $total_incometax; ?></th>
							<th><?php echo $total_security; ?></th>
							<th><?php echo $total_royalty; ?></th>
							<th><?php echo $total_gsttds; ?></th>
							<th><?php echo $total_gstdeduction; ?></th>
							<th><?php echo $total_other_amount; ?></th>
							<th><?php echo $net_payment; ?></th>
						</tr>
						<?php } // Close the else block for ID check ?>
                    </tbody>
                </table>
			 <div class="body-text">
				<div class="left-div">
					<p></p>
				</div>
				<div class="right-div">
                    <p>
					
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;उक्त निर्माण की स्वीकृत लागत रू0 <b><?php echo $row_project['sanction_cost']; ?></b> लाख है। 
						
						जिसके सापेक्ष रू0 <b><?php echo number_format($row_fund_id['total_rcv_amt'] / 100000, 2); ?></b> लाख की धनराशि प्राप्त हुई है। 
						
						उक्त प्रस्तावित बिल को जोड़कर रू0 <b><?php echo number_format(intval($row_fund_id['grand_total_expense']) / 100000, 2); ?></b> लाख का व्यय हो जायेगा। 
						
						विभाग के पास रू0 <b><?php echo number_format(($row_fund_id['total_rcv_amt'] - $row_fund_id['grand_total_expense']) / 100000, 2); ?></b> लाख का अवशेष रह जायेगा।
					
					</br> 
					</br> 
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;महोदय यदि उक्त प्रस्ताव से सहमत हो तो कार्यहित में उपरोक्त फर्म के पक्ष में रू0 <b><?php echo $net_payment;?></b> <b>(<?php echo int_to_words($net_payment)  ?>)</b> का एंव Centege के मद मे रू0 <b><?php echo $total_sentage;?></b>  एवं I.Tax मद मे रू0 <b><?php echo $total_incometax;?></b>  व GST TDS मद मे रू0 <b><?php echo $total_gsttds;?></b> का UPRNSS के पक्ष में भुगतान करने की स्वीकृति प्रदान करना चाहें।
					</p>
				</div>
            </div>

</body>
<script>
    window.onload = function() {
        window.print();
    }
</script>
</html>
