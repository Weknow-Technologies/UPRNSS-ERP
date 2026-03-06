<?php
session_cache_limiter('nocache');
include("settings.php");

if(isset($_GET['company'])){   
    $sql = 'select * from `employee` where `company_id`="'.$_GET['company'].'"' ;
    $result= execute_query($sql);
    ?>
    <option value="">-Select All-</option>
    <?php
    while($row = mysqli_fetch_array($result)){
        ?>      
            <option value="<?php echo $row['sno']; ?>"><?php echo $row['employee_name']; ?></option>
        <?php       
    }
}
?>