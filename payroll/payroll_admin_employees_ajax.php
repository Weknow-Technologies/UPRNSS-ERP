<?php
include("scripts/settings.php");	

// $_POST['cat_id'] = $_GET['cat_id'];

$cat_id=$_POST['cat_id'];

$_GET['edit_id'] ="";

$_GET['edit_id'] = $_POST['edit_id'];
if($_GET['edit_id']!=""){

    if(isset($_GET['edit_id'])){
        $sql ="select * from `employee` where sno='".$_GET['edit_id']."'";
        $result = execute_query($sql);
        $editrow = mysqli_fetch_array($result);
        $cat_ids=$editrow['employee_category_id'];
        // echo $cat_ids;
        $sql1="SELECT * FROM `salary_structure` WHERE `emp_id`='".$_GET['edit_id']."'";
        $result1= execute_query($sql1);
        $numrowcount=mysqli_num_rows($result1);
        if($numrowcount != 0){
            while($row1 = mysqli_fetch_array($result1)){			
                $_POST['head_'.$row1['head_id']] = $row1['head_value'];
                // echo print_r($row1);
            }
        }
    }



}

?>
<div class="row">
    <div class="panel">
        <div class="panel-heading">Income Details</div>
        <div class="panel-body">
            <?php
            $i = 0;
            $sql="SELECT *,head_type.sno as snoo FROM payroll_head_access LEFT JOIN `head_type` on payroll_head_access.head_id=head_type.sno  WHERE payroll_head_access.cat_id={$cat_id} and head_type.head_type='income'";
            
            $result= execute_query($sql);

            while($row = mysqli_fetch_array($result)){
            ?>
            <div class="col-sm-4">	
                <b><?php echo $row['head_name']; 
                    if($row['value_type'] == 'percent'){
                    echo ' (Percentage of '.head_name($row['percent_of']).')';
                    }
                    elseif($row['value_type']=='formula'){
                        $sql = 'select * from head_type_formula where head_id="'.$row['snoo'].'"';
                        $result_formula = execute_query($sql);
                        $i=1;
                        while($row_formula = mysqli_fetch_assoc($result_formula)){
                            echo 'Step '.($i++).' : '.head_name($row_formula['var_a']).' '.$row_formula['operator'].' '.head_name($row_formula['var_a']);
                        }
                    
                    }?></b>
                <input type="text" name="head_<?php echo $row['snoo'];?>" id="head_<?php echo $row['snoo'];?>" <?php if($row['value_type'] == 'percent'){?>id="income_p_id_<?php echo ++$i; ?>"<?php } ?> placeholder="Enter <?php echo $row['head_name']; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($_GET['edit_id']) && $numrowcount > 0){ echo $_POST['head_'.$row['snoo']]; }?>" onblur="validate_percent();">
                <?php if($row['value_type'] == 'percent'){?>
                <input type="hidden" id="income_name_<?php echo $i; ?>" value="<?php echo $row['head_name']; ?>">
                <?php } ?>
            </div>

            <?php } ?>
            <input type="hidden" name="income_n" value="<?php echo $i; ?>" id="income_n">
        </div>
    </div>
</div>


<div class="row">
    <div class="panel">
        <div class="panel-heading">Deduction Details</div>
        <div class="panel-body">
            <?php
             $sql="SELECT *,head_type.sno as snoo FROM payroll_head_access LEFT JOIN `head_type` on payroll_head_access.head_id=head_type.sno  WHERE payroll_head_access.cat_id={$cat_id} and head_type.head_type='deduction'";
            // $sql="SELECT * FROM `head_type` WHERE head_type='deduction'";
            $result= execute_query($sql);
            $d = 0;
            while($row = mysqli_fetch_array($result)){
            ?>
            <div class="col-sm-4">	
                <b><?php echo $row['head_name']; 
                    if($row['value_type'] == 'percent'){
                    echo ' (Percentage of '.head_name($row['percent_of']).')';
                    }
                    elseif($row['value_type']=='formula'){
                        $sql = 'select * from head_type_formula where head_id="'.$row['snoo'].'"';
                        $result_formula = execute_query($sql);
                        $i=1;
                        echo ' | Percent of : ';
                        while($row_formula = mysqli_fetch_assoc($result_formula)){
                            echo ' (Step '.($i++).' : '.head_name($row_formula['var_a']).' '.$row_formula['operator'].' '.head_name($row_formula['var_b']).')';
                        }
                    
                    }?></b>
                <input type="text" id="head_<?php echo $row['snoo'];?>" name="head_<?php echo $row['snoo']; ?>" <?php if($row['value_type'] == 'percent'){?>id="deduction_p_id_<?php echo ++$d; ?>"<?php } ?> placeholder="Enter <?php echo $row['head_name']; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($_GET['edit_id']) && $numrowcount > 0){ echo $_POST['head_'.$row['snoo']]; }?>" onblur="validate_percent();">
                <?php if($row['value_type'] == 'percent'){?>
                <input type="hidden" id="deduction_name_<?php echo $d; ?>" value="<?php echo $row['head_name']; ?>">
                <?php } ?>
            </div>
            <?php }?>
            <input type="hidden" name="deduction_n" id="deduction_n" value="<?php echo $d; ?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="panel">
        <div class="panel-heading">Other Deduction Details</div>
        <div class="panel-body">
            <?php
            $sql="SELECT *,head_type.sno as snoo FROM payroll_head_access LEFT JOIN `head_type` on payroll_head_access.head_id=head_type.sno  WHERE payroll_head_access.cat_id={$cat_id} and head_type.head_type='oth_deduction'";
            
            // $sql="SELECT * FROM `head_type` WHERE head_type='oth_deduction'";
            $result= execute_query($sql);
            $n = 0;
            while($row = mysqli_fetch_array($result)){
            ?>
            <div class="col-sm-4">	
                <b><?php echo $row['head_name']; 
                    if($row['value_type'] == 'percent'){
                    echo ' (Percentage of '.head_name($row['percent_of']).')';
                    }
                    elseif($row['value_type']=='formula'){
                        $sql = 'select * from head_type_formula where head_id="'.$row['snoo'].'"';
                        $result_formula = execute_query($sql);
                        $i=1;
                        echo ' | Percent of : ';
                        while($row_formula = mysqli_fetch_assoc($result_formula)){
                            echo ' (Step '.($i++).' : '.head_name($row_formula['var_a']).' '.$row_formula['operator'].' '.head_name($row_formula['var_b']).')';
                        }
                    
                    }?></b>
                <input type="text" id="head_<?php echo $row['snoo'];?>" name="head_<?php echo $row['snoo']; ?>" <?php if($row['value_type'] == 'percent'){?>id="oth_deduction_p_id_<?php echo ++$n; ?>"<?php } ?> placeholder="Enter <?php echo $row['head_name']; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($_GET['edit_id']) && $numrowcount > 0){ echo $_POST['head_'.$row['snoo']]; }?>" onblur="validate_percent();">
                <?php if($row['value_type'] == 'percent'){?>
                <input type="hidden" id="oth_deduction_name_<?php echo $n; ?>" value="<?php echo $row['head_name']; ?>">
                <?php } ?>
            </div>
            <?php }?>
            <input type="hidden" name="oth_deduction_n" id="oth_deduction_n" value="<?php echo $n; ?>">
        </div>
    </div>
</div>
