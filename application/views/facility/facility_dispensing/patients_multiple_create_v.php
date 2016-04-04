<style type="text/css">
.row div p,.row-fluid div p{
	padding:10px;

}
.form-control {

font-size: 12px !important;
}
</style>
<link href="<?php echo base_url().'assets/bower_components/intro.js/introjs.css'?>" type="text/css" rel="stylesheet"/>
<div class="container-fluid" style="">

	<div class="row">
		<div class="col-md-6" id=""><p class="bg-info"><span class="badge ">1</span>
		<strong>Add multiple Patients Below </strong></p></div>
		<div class="col-md-6" id="">

	</div>
	</div>
	
		
	<hr />
	<center>
	<div style="width:94%">
<div class="table-responsive" style="min-height:300px; overflow-y: auto;">
 <?php $att=array("name"=>'myform','id'=>'myform','method'=>'post','enctype'=>'application/x-www-form-urlencoded'); echo form_open('dispensing/save_patient_multiple',$att); ?>
<table  class="table table-hover table-bordered table-update" id="add_multiple_patients_table"> 
	<thead style="background-color: white">
		<tr>
			<th>First Name <span style="color:#e60000"> * <i>required</i></span></th>
			<th>Last Name <span style="color:#e60000"> * <i>required</i></span></th>
			<th>Date of Birth <span style="color:#e60000"> * <i>required</i></span></th>
			<th>Gender <span style="color:#e60000"> * <i>required</i></span></th>
			<th>Phone Number <span style="color:#e60000"> * <i>required</i></span></th>
			<th>Email <span style="color:green">(optional)</span></th>
			<th>Residential Address <span style="color:#e60000"> * <i>required</i></span></th>
			<th>Work Address <span style="color:green">(optional)</span></th>
			<th>Patient Number <span style="color:#e60000"> * <i>required</i></span></th>			
			<th>Action</th>   
		</tr>
	</thead>
					<tbody>
						<tr row_id='0'>
						<td>
						<input type="text" required="required" name="first_name[0]" id="first_name" class="form-control first_name" placeholder="Enter First Name" >
						</td>
						<td>
						<input type="text" name="last_name[0]" required="required" id="last_name" class="form-control last_name" placeholder="Last Name" >						
						</td>
						<td>
						<input class='form-control input-small clone_datepicker_normal_limit_today' type="text" name="clone_datepicker_normal_limit_today[0]"  value="" required="required" />
						</td>
						<!-- <td>						
						<input type="text" name="dob[0]" required="required" id="dob" class="form-control dob datepicker" placeholder="Date of Birth" >
						</td> -->
						<td>						
						<div class="input-group form-group u_mgt">
							<select class="form-control gender user_type" id="gender" name="gender[0]" required="required">
								<option value='0'>Select Gender</option>
								<option value='1'>Male</option>
								<option value='2'>Female</option>
								
							</select>
						</div>
						</td>
						<td>
						<input type="telephone" name="telephone[0]" required="required" id="telephone" class="form-control telephone" placeholder="254 XXX XXX XXX" tabindex="5">
						</td>
						<td>
						<input type="email" name="email[0]" id="email" class="form-control email" placeholder="email@domain.com" tabindex="6">
						</td>						
						<td>
						<input type="text" required="required" name="physical_address[0]" id="physical_address" class="form-control physical_address" placeholder="Enter Physical Address" >
						</td>
						<td>
						<input type="text" name="work_address[0]" id="work_address" class="form-control work_address" placeholder="Enter Work Address" >
						</td>
						<td>
						<input type="text" required="required" name="patient_number[0]" id="patient_number" class="form-control patient_number" placeholder="Enter Patient Number" >
						</td>
						
						<td>
							<button type="button" class="remove btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span>row</button>
							<button type="button" id="step7" class="add btn btn-primary btn-xs"><span class="glyphicon glyphicon-plus"></span>row</button>
						</td>
			</tr>
		           </tbody>
		           </table>
</div>
</div>
</center>
<hr />
<div class="container-fluid">
<div style="float: right">
<button class="btn btn-primary borderless" id="create_new">
					Save Users
				</button>
</div>
</div>
</form>
<?php //echo form_close();?>
<div class="modal fade modal-body" style="padding:0;" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-footer">
		<button class="btn btn-default borderless" data-dismiss="modal">Close</button>
		<button class="btn btn-primary borderless" id="">Save changes</button>
	</div>
</div>
<script>
$(document).ready(function() {	
	$("#create_new").click(function(){
		var loading_icon="<?php echo base_url().'assets/img/Preloader_4.gif' ?>";
    	var message = confirm("Are you sure you want to proceed?");
        if (message){
            /*$('.modal-body').html("<img style='margin:30% 0 20% 42%;' src="+loading_icon+">");
            setTimeout(function () {
	      	$('.modal-body').html("<div class='bg-warning' style='height:30px'>"+
							"<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>"+
							"<h3>Success!!! A new patient was added to the system. Please Close to continue</h3></div>")
							
			$('.modal-footer').html("<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>")
				location.reload();
	    	}, 4000); */
			setTimeout(function(){
				//notification = '<ol>' + '<li>Patient Added</li>' + '</ol>';
				//hcmp_message_box(title="Success!",notification, message_type="success");
			}, 3000);
	    	// $("#myform").submit(); 
        } else {
            return false;
        }   
		$("#myform").submit();
	});
	
	var $table = $('table');
	
	$table.floatThead({ 
		 scrollingTop: 100,
		 zIndex: 1001,
		 scrollContainer: function($table){ return $table.closest('.table-responsive'); }
	});	
	$(".add").click(function() {
        var selector_object=$('#add_multiple_patients_table tr:last');
        var form_data=check_if_the_form_has_been_filled_correctly(selector_object);
        if(isNaN(form_data[0])){
        	var notification='<ol>'+form_data[0]+'</ol>&nbsp;&nbsp;&nbsp;&nbsp;';           
          	hcmp_message_box(title='HCMP error message',notification,message_type='error');
        	return;
        }
        clone_the_last_row_of_the_table();
	});	
	$('.remove').on('click',function(){
		var row_id=$(this).closest("tr").index();
        var count_rows=$('#add_multiple_patients_table tr').length;
        count_rows--;
       	if(count_rows==1){
       		clone_the_last_row_of_the_table();
       	 	$(this).parent().parent().remove(); 
       	}else{
       		$(this).parent().parent().remove(); 
       	}	         
 	});

 	$("#myform").validate();

 	$('.save').button().click(function() {
 		console.log("Save Button Function Invoked");
		var selector_object=$('#add_multiple_patients_table tr:last');
        var form_data=check_if_the_form_has_been_filled_correctly(selector_object);
        if(isNaN(form_data[0])){
        	var notification='<ol>'+form_data[0]+'</ol>&nbsp;&nbsp;&nbsp;&nbsp;';        
           	hcmp_message_box(title='HCMP error message',notification,message_type='error');       
        	return;
        }
        else{ 
        }
        confirm_if_the_user_wants_to_save_the_form("#myform");
     });

	function clone_the_last_row_of_the_table(){
        var last_row = $('#add_multiple_patients_table tr:last');
        var cloned_object = last_row.clone(true);
        var table_row = cloned_object.attr("row_id");
        var next_table_row = parseInt(table_row) + 1;    
        var blank_value = "";       
	    cloned_object.attr("row_id", next_table_row);
		cloned_object.find(".first_name").attr('name','first_name['+next_table_row+']'); 
		cloned_object.find(".last_name").attr('name','last_name['+next_table_row+']'); 
		cloned_object.find(".telephone").attr('name','telephone['+next_table_row+']');
		cloned_object.find(".email").attr('name','email['+next_table_row+']');
		cloned_object.find(".patient_number").attr('name','patient_number['+next_table_row+']');
		cloned_object.find(".gender").attr('name','gender['+next_table_row+']'); 	
		cloned_object.find(".clone_datepicker_normal_limit_today").attr('name','clone_datepicker_normal_limit_today['+next_table_row+']');
		// cloned_object.find(".dob").attr('name','dob['+next_table_row+']'); 	
		cloned_object.find(".quantity_issued").attr('name','quantity_issued['+next_table_row+']'); 			
		cloned_object.find(".physical_address").attr('name','physical_address['+next_table_row+']'); 
		cloned_object.find(".work_address").attr('name','work_address['+next_table_row+']'); 
        cloned_object.find("input").val(blank_value);
        cloned_object.find("label.error").remove();             
		cloned_object.insertAfter('#add_multiple_patients_table tr:last');	
		refresh_clone_datepicker_normal_limit_today();
    }

    //random comments
    function check_if_the_form_has_been_filled_correctly(selector_object){
		var alert_message='';
		var first_name=selector_object.closest("tr").find(".first_name").val();
		var last_name=selector_object.closest("tr").find(".last_name").val();			
		var gender=selector_object.closest("tr").find(".gender").val();
		var telephone=selector_object.closest("tr").find(".telephone").val();
		var physical_address=selector_object.closest("tr").find(".physical_address").val();
		var patient_number=selector_object.closest("tr").find(".patient_number").val();
		var dob=selector_object.closest("tr").find(".clone_datepicker_normal_limit_today").val();		
		//set the message here
		if (first_name=='') {alert_message+="<li>Enter the First Name</li>";}
		if (last_name=='') {alert_message+="<li>Enter the Last Name</li>";}
		if (gender==0) {alert_message+="<li>Select the Gender</li>";}
		if (telephone=='') {alert_message+="<li>Enter the Telephone Number</li>";}
		if (physical_address=='') {alert_message+="<li>Enter the Physical Address</li>";}
	    if (patient_number=='') {alert_message+="<li>nter the Patient Number</li>";}
	    if (dob=='') {alert_message+="<li>Select the Date of Birth</li>";}
	    return[alert_message];	
	}
	$('.datepicker').datepicker();
});

</script>
<script src="<?php echo base_url().'assets/bower_components/intro.js/intro.js'?>" type="text/javascript"></script>
    