

function sale_form_verify(){
	var msg = '';
	if($("#supplier_sno").val()==''){
		msg += "\n ER#01 : No customr selected";
	}
	if($("#grand_total").val()==''){
		msg += "\n ER#02 : Incorrect Data";
	}
	
	var id = $("#id").val();
	for(var i=1; i<=id; i++){
		if($("#part_desc1_sno").val()==''){
			msg += "\n ER#03 : Incorrect Product at Row : "+i;
		}
	}
	if(msg!=''){
		alert(msg);	
		return false;
	}
	else{
		//alert("All okay");
		return true;
	}
	
	
}