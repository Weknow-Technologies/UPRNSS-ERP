<?php
include("scripts/settings.php");

$msg = '';
$tab = 1;

// Input
$note_sheet_id = $_GET['id'] ?? 0;

// Step 1: project_note_sheet से bill sno निकालना
$sql1 = "SELECT * FROM `project_bill_adviser` WHERE id = $note_sheet_id";
$note_result = execute_query($sql1);
$note_data = mysqli_fetch_assoc($note_result);
$bill_snos = $note_data['related_bill_snos'];

// Step 2: invoice_account_fund_transafer से data लाना
$sql2 = "SELECT to_account_no, SUM(praposemoney) AS total_amount FROM `invoice_account_fund_transafer` WHERE sno IN ($bill_snos) GROUP BY to_account_no";
$fund_result = execute_query($sql2);

// Step 3: Data extract करके bank_data बनाना
$bank_data = [];
$total_amount = 0;
$i = 1;

while ($row = mysqli_fetch_assoc($fund_result)) {
    // अब हम fund_transafer_to_account से customer_id (billit_customer foreign key) निकालेंगे
   
        $cust_q = "SELECT * FROM billit_customer WHERE sno = {$row['to_account_no']}";
        $cust = mysqli_fetch_assoc(execute_query($cust_q));

        // Fallback values if data missing
        $department = 'Uttar Pradesh Rajya Nirman Sahkari Sangh Limited(UPRNSS).';
        $bank_name = 'HDFC';
        $account_no = $cust['account_no'] ?? '0000000000';
        $ifsc = $cust['ifsc'] ?? 'UNKNOWN';
        $amount = floatval($row['total_amount']);

        $bank_data[] = [
            'department' => $department,
            'bank' => $bank_name,
            'account' => $account_no,
            'ifsc' => $ifsc,
            'amount' => $amount
        ];

        $total_amount += $amount;
    
}

// HO बैंक डिटेल्स
$ho_bank_row = mysqli_fetch_assoc(execute_query('SELECT * FROM billit_customer WHERE sno="1"'));
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Bank Advice Letter</title>
    <style>
        body {
            font-family: "Kruti Dev", Arial, sans-serif;
            font-size: 14px;
            /* margin-top: 90px; */
            padding: 0;
            margin: 20px;
        }
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        .header-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-top: -50px;
        }
        .header-text {
            text-align: center;
            flex: 1;
        }
        .header-text h1 {
            margin: 0;
            font-size: 25px;
            color: #252964;
            line-height: 1.2;
        }
        .header-text p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #252964;
        }
        .header-meta {
            text-align: right;
            font-size: 12px;
            min-width: 140px;
            margin-top: 20px;
        }
        /* hr.sep {
            border: none;
            border-top: 2px solid rgba(0,0,0,0.06);
            margin: 10px 0 20px 0;
        } */

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
        }
		#overlays {
            z-index: 0;
            opacity: 0.15;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .header {
            text-align: left;
            margin-bottom: 1.2rem;
            font-weight: bold;
            font-size: 1rem;
        }

        .body-text {
            text-align: left;
            margin: 1rem 0;
            text-indent: 2rem;
            font-size: 0.95rem;
        }

        .right {
            text-align: right;
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
	<div>
                <img src="images/icon.png" id="overlays" alt="overlay image">
            </div>
            <div class="header-meta">
            <div>दूरभाष : 0522-2390150</div>
            <div>फैक्स : 0522-2390150</div>
            <div>वेबसाइट : www.uprnss.org</div>
            <div>ईमेल  :  paccfedho@gmail.com </div>
        </div><br>
            <div class="header">
        <img src="images/icon.png" alt="uprnss logo" class="header-logo">
        <div class="header-text">
            <h1>उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि० <span style="color: #b90b1a;"><br>(UPRNSS)</span></h1>
            <p>मुख्यालय : जी-4/5बी, सेक्टर-4, गोमती नगर विस्तार, लखनऊ - 226010<br><span style="color: #b90b1a; font-size:15px;">(राजकीय निर्माण एजेन्सी )</span></p>
        </div>
       
    </div>
    <!-- <hr class="sep"> -->
    <div class="header">
        <div style="font-weight: lighter;">पत्रांक : ______________</div>
        <div style="font-weight: lighter;">दिनांक  : ______________</div>
    </div>
    </div>
    <div style="margin-top: 30px; padding:50px;">
    <div style="font-weight: bolder;">
     शाखा प्रबन्धक<br>
        एच.डी.एफ.सी. बैंक लि0<br>
        जनपद-लखनऊ।
    </div>
    <div class="body-text">
        आपकी शाखा में उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि०, का खाता संख्या-<strong><?php echo $ho_bank_row['account_no']; ?></strong> संचालित हैं, अधोलिखित विवरण के अनुसार उनके नाम के आगे अंकित धनराशि को उनके खाते में Transfer करने का कष्ट करें।
    </div>

    <table>
        <thead>
            <tr>
                <th>क्र0सं0</th>
                <th>विभाग का नाम</th>
                <th>बैंक का नाम</th>
                <th>खाता संख्या</th>
                <th>आईएफएससी</th>
                <th>धनराशि</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($bank_data as $entry) {
                echo "<tr>
                    <td>{$i}</td>
                    <td>{$entry['department']}</td>
                    <td>{$entry['bank']}</td>
                    <td>{$entry['account']}</td>
                    <td>{$entry['ifsc']}</td>
                    <td class='right'>".number_format($entry['amount'], 2)."</td>
                </tr>";
                $i++;
            }
            ?>
            <tr>
                <th colspan="5" class="right">Total</th>
                <th class="right"><?php echo number_format($total_amount, 2); ?></th>
            </tr>
        </tbody>
    </table>
</body>
        </div>
<script>
    window.onload = function() {
        window.print();
    }
</script>
</html>
