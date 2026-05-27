<?php
include("scripts/settings.php");
include("scripts/setting_dbase_emb.php");

$msg='';
$tab=1;


if(!isset($_POST['search'])){
    $_POST['department']='';
    $_POST['district']='';
    $_POST['division_name']='';
    $_POST['project_type']='';
}


page_header_start();
?>
<script src="js/krutidev.js"></script>
<script src="js/unicode_keyboard.js"></script>
<style>
    #project_name_hindi {
        font-family: 'Kruti Dev 010';
        font-size: 20px;
    }
    textarea {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }

	.add-emb-btn {
  background: linear-gradient(45deg, #28a745, #20c997);
  color: #fff !important;
  font-weight: 600;
  border: none;
  border-radius: 30px;
  transition: 0.3s ease;
}
.add-emb-btn:hover {
  background: linear-gradient(45deg, #20c997, #28a745);
  transform: translateY(-1px);
}

</style>
<?php
page_header_end();
page_sidebar();
?>

<!-- ===================== FILTER FORM ===================== -->
<div id="container" class="no-print">
    <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" 
          method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="card card-body">
            <?php echo $msg?>
            <div class="row d-flex my-auto">
                <table width="100%" class="table table-striped table-hover rounded">    
                    <tr>
                        <th>विभाग </th>
                        <th width="18%">
                            <select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">
                                <option value="">--- Select ---</option>
                                <?php
                                $query = "select * from uprnss_department_name";
                                $run = mysqli_query($db,$query);
                                while($data = mysqli_fetch_array($run)){
                                    echo '<option value="'.$data['sno'].'" ';
                                    if($_POST['department']==$data['sno']) echo ' selected';
                                    echo '>'.trim($data['department_name_hindi']).'</option>';
                                }
                                ?>
                            </select>
                        </th>
                        <th>प्रखण्ड </th>
                        <th width="18%">
                            <select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>">
                                <option value="">--- Select ---</option>
                                <?php
                                $query = 'select * from uprnss_division where S_no!=53 order by division_name ASC';
                                $run = mysqli_query($db,$query);
                                while($data = mysqli_fetch_array($run)){
                                    echo '<option value="'.$data['s_no'].'" ';
                                    if($_POST['division_name']==$data['s_no']) echo ' selected';
                                    echo '>'.$data['division_name'].'</option>';
                                }
                                ?>
                            </select>
                        </th>
                        <th>आच्छादित जनपद</th>
                        <th width="18%">
                            <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
                                <option value="">--- Select ---</option>
                                <?php
                                $query = "select * from uprnss_district";
                                $run = mysqli_query($db,$query);
                                while($data = mysqli_fetch_array($run)){
                                    echo '<option value="'.$data['sno'].'" ';
                                    if($_POST['district']==$data['sno']) echo ' selected';
                                    echo '>'.$data['district_name_hindi'].'</option>';
                                }
                                ?>
                            </select>
                        </th>
                        <th>परियोजना का स्तर</th>
                        <th width="18%">
                            <select name="project_type" id="project_type" class="form-control">
                                <option value="">--- Select ---</option>
                                <option value="1"<?php echo ($_POST['project_type']==1?'selected':''); ?>>शासन स्तर</option>
                                <option value="2"<?php echo ($_POST['project_type']==2?'selected':''); ?>>जिला स्तर </option>
                            </select>
                        </th>
                    </tr>
                </table>
                <div class="col-md-12 text-center">
                    <button type="submit" name="search" class="btn btn-primary">Search</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ===================== PROJECT LIST TABLE ===================== -->
<?php if(isset($_POST['search'])): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white mt-2">
                <h4 class="card-title text-center text-white">Project List</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover table-striped" id="general_stat_table">
                    <thead>
                        <tr>
                            <th>क्रं सं.</th>
                            <th>ERP Code</th>
                            <th>प्रखण्ड का नाम</th>
                            <th>जनपद का नाम</th>
                            <th>विभाग का नाम</th>
                            <th>कार्य का नाम</th>
                            <th>धनराशि</th>
                            <th>स्तर</th>
                            <th>शासनादेश संख्या</th>
                            <th class="no-print">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    
                    $divisions_list = !empty($_SESSION['divisions']) ? implode(",", $_SESSION['divisions']) : "0";
                    $sql = "SELECT * 
						FROM cloudice_uprnss.uprnss_project_temp 
						WHERE division_id IN ($divisions_list)  
						  AND status != '5' 
						  AND erp_code IS NOT NULL 
						  AND erp_code != ''";

                    if($_POST['department']!=''){
                        $sql .= " AND department_id='".mysqli_real_escape_string($db,$_POST['department'])."'";
                    }
                    if($_POST['district']!=''){
                        $sql .= " AND district_id='".mysqli_real_escape_string($db,$_POST['district'])."'";
                    }
                    if($_POST['division_name']!=''){
                        $sql .= " AND division_id='".mysqli_real_escape_string($db,$_POST['division_name'])."'";
                    }
                    if($_POST['project_type']!=''){
                        $sql .= " AND project_type='".mysqli_real_escape_string($db,$_POST['project_type'])."'";
                    }

                    $sql .= " ORDER BY sno DESC";
					// echo $sql;
                    $result = execute_query($sql);

                    $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
						$erpCode = $row['erp_code'];
						$sqlEmb = "SELECT erp_code  
								   FROM projects 
								   WHERE TRIM(erp_code) = TRIM('" . mysqli_real_escape_string($db_emb, $erpCode) . "')";
								//    echo $sqlEmb;
						$resEmb = mysqli_query($db_emb, $sqlEmb);

						if (mysqli_num_rows($resEmb) > 0) {
							goto skip;
						}

						
						$district   = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_district WHERE sno="' . $row['district_id'] . '"'));
						$divisions  = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_division WHERE s_no="' . $row['division_id'] . '"'));
						$department = mysqli_fetch_assoc(execute_query('SELECT department_name_hindi FROM uprnss_department_name WHERE sno="' . $row['department_id'] . '"'));

						echo '<tr>
							<td>' . $i++ . '</td>
							<td>' . $row['erp_code'] . '</td>
							<td>' . $divisions['division_name'] . '</td>
							<td>' . $district['district_name_hindi'] . '</td>
							<td>' . $department['department_name_hindi'] . '</td>
							<td>' . $row['project_name_hindi'] . '</td>
							<td>' . $row['sanction_cost'] . '</td>
							<td>';
							if ($row['project_type'] == '1') {
								echo 'शासन स्तर';
							} elseif ($row['project_type'] == '2') {
								echo 'जिला स्तर';
							}
						echo '</td>
							<td>' . $row['admin_go_no'] . '<br>' .
							($row['admin_go_date'] == "" ? "-" : date("d-m-Y", strtotime($row['admin_go_date']))) . '</td>
							<td class="no-print text-center">
								<a href="emb_create_project.php?id=' . $row['sno'] . '"
								class="btn btn-sm btn-success shadow-sm px-3 py-2 d-inline-flex align-items-center"
								onClick="return confirm(\'क्या आप सुनिश्चित हैं कि इस परियोजना को EMB में जोड़ना चाहते हैं?\');">
									<i class="far fa-edit me-2"></i> Add Project In EMB
								</a>
							</td>
						</tr>';

						skip:;  
					}

                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
$('select[multiple]').multiselect({ search: true });
$(document).ready(function () {
    $('#general_stat_table').DataTable({ paging: false });
});
</script>
<?php
page_footer_end();
?>
