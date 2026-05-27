<?php
include("scripts/settings.php");
include("scripts/alerts.php");
$msg = '';
$debug_data = [];

/**
 * Robust Krutidev to Unicode conversion
 */
function kruti_to_unicode($text)
{
    if ($text === null || trim($text) === "")
        return "";
    $text = (string) $text;
    $kruti = array("ñ", "Q+Z", "sas", "aa", ")Z", "ZZ", "‘", "’", "“", "”", "å", "ƒ", "„", "…", "†", "‡", "ˆ", "‰", "Š", "‹", "¶+", "d+", "[+k", "[+", "x+", "T+", "t+", "M+", "<+", "Q+", ";+", "j+", "u+", "Ùk", "Ù", "ä", "–", "—", "é", "™", "=kk", "f=k", "à", "á", "â", "ã", "ºz", "º", "í", "{k", "{", "=", "«", "Nî", "Vî", "Bî", "Mî", "<î", "|", "K", "}", "J", "Vª", "Mª", "<ªª", "Nª", "Ø", "Ý", "nzZ", "æ", "ç", "Á", "xz", "#", ":", "v‚", "vks", "vkS", "vk", "v", "b±", "Ã", "bZ", "b", "m", "Å", ",s", ",", "_", "ô", "d", "Dk", "D", "[k", "[", "x", "Xk", "X", "Ä", "?k", "?", "³", "pkS", "p", "Pk", "P", "N", "t", "Tk", "T", ">", "÷", "¥", "ê", "ë", "V", "B", "ì", "ï", "M+", "<+", "M", "<", ".k", "r", "Rk", "R", "Fk", "F", ")", "n", "/k", "èk", "/", "Ë", "è", "u", "Uk", "U", "i", "Ik", "I", "Q", "¶", "c", "Ck", "C", "Hk", "H", "e", "Ek", "E", ";", "¸", "j", "y", "Yk", "Y", "G", "o", "Ok", "O", "'k", "'", "\"k", "\"", "l", "Lk", "L", "g", "È", "z", "Ì", "Í", "Î", "Ï", "Ñ", "Ò", "Ó", "Ô", "Ö", "Ø", "Ù", "Ük", "Ü", "‚", "ks", "kS", "k", "h", "q", "w", "`", "s", "S", "a", "¡", "%", "W", "•", "·", "∙", "·", "~j", "~", "\\", "+", "ः", "^", "*", "Þ", "ß", "(", "¼", "½", "¿", "À", "¾", "A", "-", "&", "&", "Œ", "]", "~ ", "@");
    $unicode = array("॰", "QZ+", "sa", "a", "र्द्ध", "Z", "\"", "\"", "'", "'", "०", "१", "२", "३", "४", "५", "६", "७", "८", "९", "फ़्", "क़", "ख़", "ख़्", "ग़", "ज़्", "ज़", "ड़", "ढ़", "फ़", "य़", "ऱ", "ऩ", "त्त", "त्त्", "क्त", "दृ", "कृ", "न्न", "न्न्", "=k", "f=", "ह्न", "ह्य", "हृ", "ह्म", "ह्र", "ह्", "द्द", "क्ष", "क्ष्", "त्र", "त्र्", "छ्य", "ट्य", "ठ्य", "ड्य", "ढ्य", "द्य", "ज्ञ", "द्व", "श्र", "ट्र", "ड्र", "ढ्र", "छ्र", "क्र", "फ्र", "र्द्र", "द्र", "प्र", "प्र", "ग्र", "रु", "रू", "ऑ", "ओ", "औ", "आ", "अ", "ईं", "ई", "ई", "इ", "उ", "ऊ", "ऐ", "ए", "ऋ", "क्क", "क", "क", "क्", "ख", "ख्", "ग", "ग", "ग्", "घ", "घ", "घ्", "ङ", "चै", "च", "च", "च्", "छ", "ज", "ज", "ज्", "झ", "झ्", "ञ", "ट्ट", "ट्ठ", "ट", "ठ", "ड्ड", "ड्ढ", "ड़", "ढ़", "ड", "ढ", "ण", ".", "त", "त", "त्", "थ", "थ्", "द्ध", "द", "ध", "ध", "ध्", "ध्", "ध्", "न", "न", "न्", "प", "प", "प्", "फ", "फ्", "ब", "ब", "ब्", "भ", "भ्", "म", "म", "म्", "य", "य्", "र", "ल", "ल", "ल्", "ळ", "व", "व", "व्", "श", "श्", "ष", "ष्", "स", "स", "स्", "ह", "ीं", "्र", "द्द", "ट्ट", "ट्ठ", "ड्ड", "कृ", "भ", "्य", "ड्ढ", "झ्", "क्र", "त्त्", "श", "श्", "ॉ", "ो", "ौ", "ा", "ी", "ु", "ू", "ृ", "े", "ै", "ं", "ँ", "ः", "ॅ", "ऽ", "ऽ", "ऽ", "ऽ", "्र", "्", "?", "़", ":", "‘", "’", "“", "”", ";", "(", ")", "{", "}", "=", "।", "-", "µ", "॰", ",", "् ", "/");

    $text = str_replace($kruti, $unicode, $text);
    $text = str_replace(array("±", "Zं"), "ंZ", $text);
    $text = preg_replace('/f(.)/u', '$1ि', $text);
    $text = str_replace(array("Ç", "É"), array("fa", "र्fa"), $text);
    $text = preg_replace('/fa(.)/u', '$1िं', $text);
    $text = str_replace("Ê", "ीZ", $text);
    $text = preg_replace('/ि्(.)/u', '्$1ि', $text);

    $matras = "अ आ इ ई उ ऊ ए ऐ ओ औ ा ि ी ु ू ृ े ै ो ौ ं : ँ ॅ";
    $text = preg_replace_callback('/([^' . $matras . ']|[\x{0900}-\x{097F}])([' . $matras . ']*?)Z/u', function ($match) {
        return "र्" . $match[1] . $match[2];
    }, $text);

    return trim($text);
}

function parse_go_info($text)
{
    if ($text == "")
        return ['num' => '', 'date' => '0000-00-00'];
    $out = ['num' => $text, 'date' => '0000-00-00'];
    if (preg_match('/(?:संख्या|सं\.|सं०)\s*([^\s,]+)/u', $text, $m)) {
        $out['num'] = $m[1];
    }

    // More robust date matching (Handles दि०, दिनांक, दि., दि- or just raw date string)
    if (preg_match('/(?:दिनांक|दि\.|दि०|दि\-)?\s*([0-9]{1,2}[\.\-\/][0-9]{1,2}[\.\-\/][0-9]{2,4})/u', $text, $m)) {
        $val = str_replace(['.', '/'], '-', $m[1]);
        $parts = explode('-', $val);
        if (count($parts) == 3) {
            $d = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $m_ = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            $y = $parts[2];

            if (strlen($y) == 4) { // DD-MM-YYYY format
                $out['date'] = "$y-$m_-$d";
            } elseif (strlen($y) == 2) { // DD-MM-YY format
                $y = ($y < 50) ? "20$y" : "19$y";
                $out['date'] = "$y-$m_-$d";
            } elseif (strlen($parts[0]) == 4) { // YYYY-MM-DD format
                $out['date'] = "$parts[0]-" . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . "-" . str_pad($parts[2], 2, '0', STR_PAD_LEFT);
            }
        }
    }
    return $out;
}

if (isset($_POST['submit_import'])) {
    mysqli_set_charset($db, "utf8mb4");
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $fileTempPath = $_FILES['csv_file']['tmp_name'];
        $content = file_get_contents($fileTempPath);
        $first2 = substr($content, 0, 2);
        $encoding = ($first2 === "\xFF\xFE") ? 'UTF-16LE' : (($first2 === "\xFE\xFF") ? 'UTF-16BE' : mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'ASCII', 'CP1252'], true));
        if ($encoding && $encoding != 'UTF-8') {
            file_put_contents($fileTempPath, mb_convert_encoding($content, 'UTF-8', $encoding));
        }

        $handle = fopen($fileTempPath, "r");
        $skip_rows = isset($_POST['rows_to_skip']) ? (int) $_POST['rows_to_skip'] : 0;
        for ($i = 0; $i < $skip_rows; $i++)
            fgetcsv($handle);

        $success = 0;
        $failed = 0;
        $now = date("Y-m-d H:i:s");
        $usersno = $_SESSION['usersno'];
        $is_already_unicode = isset($_POST['is_unicode_file']);
        $go_invoice_map = []; // Map GO+Dept to Invoice ID

        while (($csv_data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            if (count($debug_data) < 10)
                $debug_data[] = $csv_data;
            if (!isset($csv_data[4]) || trim($csv_data[4]) == "")
                continue;

            // Mapping (Updated based on latest screenshot: Dist:1, Div:2, AuxDate:3, GO:4, Name:5, Cost:6, S.Date:7)
            $csv_dist_name = ($is_already_unicode ? trim($csv_data[1] ?? '') : kruti_to_unicode(trim($csv_data[1] ?? '')));
            $csv_div_name = ($is_already_unicode ? trim($csv_data[2] ?? '') : kruti_to_unicode(trim($csv_data[2] ?? '')));
            $csv_go_info_raw = ($is_already_unicode ? trim($csv_data[4] ?? '') : kruti_to_unicode(trim($csv_data[4] ?? '')));
            $csv_proj_name = ($is_already_unicode ? trim($csv_data[5] ?? '') : kruti_to_unicode(trim($csv_data[5] ?? '')));
            $csv_cost_raw = trim($csv_data[6] ?? '0');
            $csv_cost = preg_replace('/[^0-9.]/', '', $csv_cost_raw);

            // Metadata Lookups (Col 15 Dept, 16 Sub-Dept, 17 Type, 18 GO Type, 19 Scheme, 20 Sub-Scheme, 21 Under)
            $csv_dept_name = ($is_already_unicode ? trim($csv_data[15] ?? '') : kruti_to_unicode(trim($csv_data[15] ?? '')));
            $csv_sub_dept_name = ($is_already_unicode ? trim($csv_data[16] ?? '') : kruti_to_unicode(trim($csv_data[16] ?? '')));
            $csv_proj_type = preg_replace('/[^0-9]/', '', ($csv_data[17] ?? '1'));
            $csv_go_type = preg_replace('/[^0-9]/', '', ($csv_data[18] ?? '1'));
            $csv_scheme_name = ($is_already_unicode ? trim($csv_data[19] ?? '') : kruti_to_unicode(trim($csv_data[19] ?? '')));
            $csv_sub_sch_name = ($is_already_unicode ? trim($csv_data[20] ?? '') : kruti_to_unicode(trim($csv_data[20] ?? '')));
            $csv_proj_under = ($is_already_unicode ? trim($csv_data[21] ?? '') : kruti_to_unicode(trim($csv_data[21] ?? '')));

            // ID Lookups
            $dept_id = 38; // Defaulting to Karagaar (Jail Department)
            $dist_id = 0;
            $div_id = 0;
            $sub_dept_id = 0;
            $scheme_id = 0;
            $sub_sch_id = 0;
            /*
            if ($csv_dept_name != '') {
                $res = execute_query("SELECT sno FROM uprnss_department_name WHERE department_name_hindi LIKE '%$csv_dept_name%' OR department_name_english LIKE '%$csv_dept_name%' LIMIT 1");
                if ($row = mysqli_fetch_assoc($res))
                    $dept_id = $row['sno'];
            }
            */
            if ($csv_sub_dept_name != '') {
                $res = execute_query("SELECT sno FROM uprnss_sub_department WHERE sub_department_hindi LIKE '%$csv_sub_dept_name%' OR sub_department_english LIKE '%$csv_sub_dept_name%' LIMIT 1");
                if ($row = mysqli_fetch_assoc($res))
                    $sub_dept_id = $row['sno'];
            }
            if ($csv_dist_name != '') {
                $res = execute_query("SELECT sno FROM uprnss_district WHERE district_name_hindi LIKE '%$csv_dist_name%' OR district_name_english LIKE '%$csv_dist_name%' LIMIT 1");
                if ($row = mysqli_fetch_assoc($res))
                    $dist_id = $row['sno'];
            }
            if ($csv_div_name != '') {
                $res = execute_query("SELECT s_no FROM uprnss_division WHERE division_name LIKE '%$csv_div_name%' LIMIT 1");
                if ($row = mysqli_fetch_assoc($res))
                    $div_id = $row['s_no'];
            }
            if ($csv_scheme_name != '') {
                $res = execute_query("SELECT sno FROM uprnss_project_scheme WHERE scheme_name_hindi LIKE '%$csv_scheme_name%' OR scheme_name_english LIKE '%$csv_scheme_name%' LIMIT 1");
                if ($row = mysqli_fetch_assoc($res))
                    $scheme_id = $row['sno'];
            }
            if ($csv_sub_sch_name != '') {
                $res = execute_query("SELECT sno FROM uprnss_project_sub_scheme WHERE sub_scheme_hindi LIKE '%$csv_sub_sch_name%' OR sub_scheme_english LIKE '%$csv_sub_sch_name%' LIMIT 1");
                if ($row = mysqli_fetch_assoc($res))
                    $sub_sch_id = $row['sno'];
            }

            $parsed_go = parse_go_info($csv_go_info_raw);
            $csv_go_num = mysqli_real_escape_string($db, $parsed_go['num']);
            $csv_go_date = $parsed_go['date'];

            // Extract Sanction Date from Column 7 (SDate index 7)
            $csv_manual_sdate = trim($csv_data[7] ?? '');
            $actual_sanction_date = $csv_go_date; // Default to GO date if manual is empty
            if ($csv_manual_sdate != '') {
                $val = str_replace(['.', '/'], '-', $csv_manual_sdate);
                $parts = explode('-', $val);
                if (count($parts) == 3 && strlen($parts[2]) == 4) {
                    $actual_sanction_date = "$parts[2]-" . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . "-" . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                } elseif (count($parts) == 3 && strlen($parts[0]) == 4) {
                    $actual_sanction_date = "$parts[0]-" . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . "-" . str_pad($parts[2], 2, '0', STR_PAD_LEFT);
                }
            }

            // 1. Resolve invoice_new_project
            $safe_proj_under = mysqli_real_escape_string($db, $csv_proj_under);
            $cache_key = ($csv_go_num != '') ? $dept_id . '_' . $csv_go_num : '';
            $inv_id = 0;

            if ($cache_key != '' && isset($go_invoice_map[$cache_key])) {
                $inv_id = $go_invoice_map[$cache_key];
            } else {
                if ($cache_key != '') {
                    $res_check = execute_query("SELECT sno FROM invoice_new_project WHERE department='$dept_id' AND go_number='$csv_go_num' ORDER BY sno DESC LIMIT 1");
                    if ($row_check = mysqli_fetch_assoc($res_check)) {
                        $inv_id = $row_check['sno'];
                        $go_invoice_map[$cache_key] = $inv_id;
                    }
                }
            }

            $safe_proj_name = mysqli_real_escape_string($db, $csv_proj_name);

            if ($inv_id == 0) {
                $sql_inv = "INSERT INTO invoice_new_project (project_type, department, sub_department_id, go_type, go_number, go_date, sanction_cost, sanction_date, project_name_hindi, project_name_english, scheme, sub_scheme, project_under, status, created_by, creation_time) 
                            VALUES ('$csv_proj_type', '$dept_id', '$sub_dept_id', '$csv_go_type', '$csv_go_num', '$csv_go_date', '$csv_cost', '$actual_sanction_date', '$safe_proj_name', '$safe_proj_name', '$scheme_id', '$sub_sch_id', '$safe_proj_under', '0', '$usersno', '$now')";

                if (execute_query($sql_inv)) {
                    $inv_id = mysqli_insert_id($db);
                    if ($cache_key != '')
                        $go_invoice_map[$cache_key] = $inv_id;
                } else {
                    echo "Invoice Error: " . mysqli_error($db) . " Row Proj: $csv_proj_name <br>";
                    $failed++;
                    continue; // Skip the child records if parent fails
                }
            }

            // 2. Insert into transaction_new_project
            $sql_trns = "INSERT INTO transaction_new_project (invoice_id, division_id, district_id, sub_project_name, sub_project_name_hindi, status, created_by, creation_time, civil_status, project_cost) 
                         VALUES ('$inv_id', '$div_id', '$dist_id', '$safe_proj_name', '$safe_proj_name', '0', '$usersno', '$now', '0', '$csv_cost')";
            if (!execute_query($sql_trns)) {
                echo "Transaction Error: " . mysqli_error($db) . "<br>";
            }
            $trns_id = mysqli_insert_id($db);

            // 3. Insert into uprnss_project_temp
            $sql_temp = "INSERT INTO uprnss_project_temp (new_project_id, new_project_trans_id, division_id, district_id, project_name, project_name_hindi, department_id, sub_department_id, scheme, sub_scheme, project_under, status, created_by, creation_time, master_freeze, civil_status, project_type, sanction_cost, sanction_date)
                         VALUES ('$inv_id', '$trns_id', '$div_id', '$dist_id', '$safe_proj_name', '$safe_proj_name', '$dept_id', '$sub_dept_id', '$scheme_id', '$sub_sch_id', '$safe_proj_under', '2', '$usersno', '$now', '0', '0', '$csv_proj_type', '$csv_cost', '$actual_sanction_date')";
            if (!execute_query($sql_temp)) {
                echo "Project Temp Error: " . mysqli_error($db) . "<br>";
            }
            $project_id = mysqli_insert_id($db);

            // 4. ERP Code Generation
            $res_code = execute_query("SELECT uprnss_project_temp.sno, uprnss_department_name.department_sort_name, uprnss_project_temp.creation_time FROM uprnss_project_temp LEFT JOIN uprnss_department_name ON uprnss_department_name.sno = department_id WHERE uprnss_project_temp.sno = '$project_id'");
            if ($row_code = mysqli_fetch_assoc($res_code)) {
                $year = date("Y", strtotime($row_code['creation_time']));
                $sort = $row_code['department_sort_name'] ?? '--';
                $erp_code = "$sort/$year/$project_id";
                execute_query("UPDATE uprnss_project_temp SET erp_code = '$erp_code' WHERE sno = '$project_id'");
            }

            // 5. G.O. Conditionals
            switch ($csv_go_type) {
                case '1':
                    execute_query("UPDATE uprnss_project_temp SET admin_go_no = '$csv_go_num', admin_go_date = '$csv_go_date' WHERE sno = '$project_id'");
                    break;
                case '2':
                    execute_query("UPDATE uprnss_project_temp SET sanction_cost = '$csv_cost', financial_go_no = '$csv_go_num', financial_go_date = '$csv_go_date', financial_go_amount = '$csv_cost', sanction_date = '$actual_sanction_date' WHERE sno = '$project_id'");
                    break;
                case '3':
                    execute_query("UPDATE uprnss_project_temp SET sanction_cost = '$csv_cost', admin_go_no = '$csv_go_num', admin_go_date = '$csv_go_date', financial_go_no = '$csv_go_num', financial_go_date = '$csv_go_date', financial_go_amount = '$csv_cost', sanction_date = '$actual_sanction_date' WHERE sno = '$project_id'");
                    break;
            }
            $success++;
        }
        fclose($handle);
        $msg = "Success: $success, Failed: $failed";
    } else {
        $error_code = $_FILES['csv_file']['error'] ?? 4;
        switch ($error_code) {
            case 1:
                $msg = "File too large (exceeds upload_max_filesize).";
                break;
            case 2:
                $msg = "File too large (exceeds MAX_FILE_SIZE).";
                break;
            case 3:
                $msg = "File only partially uploaded.";
                break;
            case 4:
                $msg = "Attach CSV file.";
                break;
            case 6:
                $msg = "Server Error: Missing temporary folder.";
                break;
            case 7:
                $msg = "Server Error: Failed to write to disk.";
                break;
            case 8:
                $msg = "Server Error: PHP extension blocked upload.";
                break;
            default:
                $msg = "Unknown upload error (Code: $error_code).";
                break;
        }
    }
}

page_header_start();
page_header_end();
page_sidebar();
?>
<div>
    <form id="form" name="form" autocomplete="off" enctype="multipart/form-data" method="post"
        action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">100% Automated Bulk Import (Logic Synced)</h5>
                        <?php if ($msg != '') {
                            echo '<h5>' . alert($msg) . '</h5>';
                        } ?>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <b>CSV Columns:</b> 1:Dist, 2:Div, 3:Aux Date, 4:GO Info (Num/Date), 5:Name, 6:Cost,
                            7:Sanction Date
                            (Optional) | 15:Dept, 16:Sub-Dept, 17:Type, 18:GO Type, 19:Scheme.
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"><label>Upload CSV File *</label><input type="file"
                                        name="csv_file" class="form-control" accept=".csv" required></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group"><label>Skip Header Rows *</label><input type="number"
                                        name="rows_to_skip" class="form-control" value="5" min="0"></div>
                            </div>
                            <div class="col-md-3" style="padding-top:35px;"><input type="checkbox"
                                    name="is_unicode_file" id="is_unicode_file" value="1"> <label
                                    for="is_unicode_file">File is already Unicode</label></div>
                            <div class="col-md-3 text-right" style="padding-top:20px;"><button type="submit"
                                    name="submit_import" class="btn btn-success btn-fill">Start Import</button></div>
                        </div>
                        <?php if (!empty($debug_data)): ?>
                            <hr>
                            <h6>Data Preview (Indices):</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" style="font-size:10px;">
                                    <thead>
                                        <tr><?php for ($i = 0; $i < 22; $i++)
                                            echo "<th>Col $i</th>"; ?></tr>
                                    </thead>
                                    <tbody><?php foreach ($debug_data as $row): ?>
                                            <tr><?php for ($i = 0; $i < 22; $i++): ?>
                                                    <td><?php echo htmlspecialchars($row[$i] ?? 'N/A'); ?></td><?php endfor; ?>
                                            </tr><?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php page_footer_start(); ?>