<?php //echo "<pre>";print_r($facilities);echo "</pre>";exit; ?>
<style type="text/css">
	.panel-body,span:hover,.status_item:hover
	{ 
		cursor: pointer !important; 
	}
	.panel {
		border-radius: 0;
	}
	.panel-body {
		padding: 8px;
	}
	#addModal .modal-dialog {
		width: 54%;
	}
	.borderless{
		border-radius: 0px;	
	}
	.form-group{
		margin-bottom: 10px;
	}
</style>
<div class="container-fluid">
	<div class="page_content">
		<div class="" style="width:65%;margin:auto;">
				<div class="row ">
					<div class="col-md-3">
						
					</div>
					<?php $x = array();
					foreach ($counts as $key) {
						$x[] = $key['count'];
					}
					?>
					<div class="col-md-3">
						
					</div>
				</div>
			</div>
		<div class="container-fluid">
			<?php 
							//echo "<pre>";print_r($facilities);die;

						?>
			<div class="row">

				<!-- <div class="col-md-1" style="padding-left: 0; right:0; float:right; margin-bottom:5px;">
					<button class="btn btn-primary add" data-toggle="modal" data-target="#addModal" id="add_new">
						<span class="glyphicon glyphicon-plus"></span>Add Facility
					</button>
				</div> -->

				<div class="col-md-12 dt" style="border: 1px solid #ddd;padding-top: 2%; " id="test">
					<span style="margin-top: 1%;margin-bottom: 2%;float: left;font-size: 16px">Select Facility: </span>
					<select id="facility_select" class="form-control" style="width:30%;margin-left: 1%;margin-top: 1%;margin-bottom: 2%;float: left;">

						<?php 
							foreach ($facilities as $key => $value) {
								$id = $value['id'];
								$facility_code = $value['facility_code'];
								$name = $value['facility_name'];
								$status = $value['using_hcmp'];
								$full_name = $name.'-'.$facility_code;?>
								<option value="<?php echo $facility_code;?>" status="<?php echo $status;?>"><?php echo $full_name;?></option>
						<?php	}

						?>
					</select>
					<button id="filter_facility" class="form-control btn btn-success" style="width:20%;margin-top: 1%;margin-bottom: 2%;float: left">Get Details</button>

				</div>
				<div id="details_list"  class="col-md-12 dt" style="border: 1px solid #ddd;padding-top: 2%;">		
					<div id="step_1">
						<h3>Step 1 </h3>
						<div id="activate">
							<p style="margin-top:0%;margin-left:6%;color:#000;font-size:16px;margin-bottom: 3%">
								<span style="float: left;">This Facility has not been activated. Do you wish to activate it? </span>
								<br/>
								<button id="btn_activate_facility" class="form-control btn btn-danger" style="width:10%;margin-top: 1%;float: left;margin-left:1%;margin-bottom: 1%">Yes, Activate
								</button>							
							</p>
							<br/>
							<span style="margin-top: 1%"></span>
						</div>
						<div id="activated">
							<p style="margin-top:0%;margin-left:6%;color:#000;font-size:16px;margin-bottom: 3%">
								<span style="float: left;">This Facility is active</span>
								<br/>
								<button id="step1_advance" class="form-control step2 btn btn-success" style="width:10%;margin-top: 1%;float: left;margin-left:1%;margin-bottom: 1%">Step 2
								</button>							
							</p>
							<br/>
							<span style="margin-top: 1%"></span>
						</div>	
					</div>

					<!-- Step two, Adding Users -->
					<div id="step_2">
						<h3>Step 2: Users </h3>
						<div id="active_users">
							<p style="margin-top:0%;margin-left:6%;color:#000;font-size:16px;margin-bottom: 3%">
								<span style="float: left;">The Following users are in your Facility</span>
								<button id="reset_pass" class="form-control btn btn-danger" style="width:10%;float: left;margin-left:1%;">Reset Passwords
								</button>
								<button id="add_user_active" class="form-control add_user btn btn-success" data-toggle="modal" data-target="#addModal" style="width:10%;float: left;margin-left:1%;">Add User
								</button>							
							</p>
							<br/>
							<table id="users_table" class="table table-hover table-bordered table-update" style="margin-left: 6%;width: 90%;margin-top: 1%;float: left;">
								<tr>
									<th><input name="select_all" value="1" id="select_all_users" type="checkbox" />Select</th>
									<th>Full Name</th>
									<th>Date Created</th>
								</tr>
							</table>
							<br/>
							<button id="step2a_advance" class="form-control step3 btn btn-success" style="width:10%;margin-top: 1%;float: left;margin-left:1%;margin-bottom: 1%">	Step 3
							</button>						
						</div>

						<div id="no_users">
							<p style="margin-top:0%;margin-left:6%;color:#000;font-size:16px;margin-bottom: 3%">
								<span style="float: left;">You have no Users</span>
								<button id="add_user_inactive" class="form-control add_user btn btn-success" data-toggle="modal" data-target="#addModal" style="width:10%;float: left;margin-left:1%;margin-bottom: 1%">Add User
								</button>							
							</p>
							<br/>																			
							
						</div>
						

					</div>

					<!-- Step 3 Download Data -->

					<div id="step_3">
						<h3>Step 3: Download Database </h3>						
						<p style="margin-top:0%;margin-left:6%;color:#000;font-size:16px;margin-bottom: 3%">
							<span style="float: left;">
							You can now be able to Download your Facility Data. THe data will have Users added in Step 2, which you can use to log in once set up. 
							</span>													
						</p>
						<br/>							
						<br/>
						<button id="step3_advance" class="form-control make_db btn btn-success" style="width:10%;margin-top: 1%;float: left;margin-left:1%;margin-bottom: 1%">		Download Data
						</button>						
						
					</div>
					<div id="step_4">
						<h3>Step 4: Download Database Installer File </h3>						
						<p style="margin-top:0%;margin-left:6%;color:#000;font-size:16px;margin-bottom: 3%">
							<span style="float: left;">
							You can now be able to Download required Files for Setup. Place the bat file and the Database File in the same folder as the setup. 
							</span>													
						</p>
						<br/>							
						<br/>
						<button id="step4_advance" class="form-control make_bat btn btn-success" style="width:10%;margin-top: 1%;float: left;margin-left:1%;margin-bottom: 1%">		Download Additional Files
						</button>						
						
					</div>
				</div>			
			</div>
		</div>
	</div>
</div>
</div>




<!-- <div id="users_none">						

<button class="form-control btn btn-success make_db" style="width:20%;float;left;margin-left:6%;margin-top: 2%;margin-bottom: 2%">
		DOWNLOAD FACILITY DATA
</button>
</div>

<div id="users_all">
	<p style="margin-top:0%;margin-left:6%;color:#000;font-size:16px;margin-bottom: 3%">
		<span style="float: left;">The Following users are in your Facility</span>
		<button id="reset_pass" class="form-control btn btn-danger" style="width:10%;float: left;margin-left:1%;">Reset Passwords
		</button>							
	</p>
	<br/>
	<table id="users_table" class="table table-hover table-bordered table-update" style="margin-left: 6%;width: 90%;margin-top: 1%;float: left;">
		<tr><th>Full Name</th><th>Date Created</th><th>Last Login</th></tr>
	</table>
	<br/>
	<button class="form-control btn btn-success make_db" style="width:20%;float;left;margin-left:6%;margin-top: 2%;margin-bottom: 2%">
			DOWNLOAD FACILITY DATA
	</button>
</div> -->

<script>
   $(document).ready(function () {
   	hideAll();
   	function hideAll(){
		$("#activate").hide();
	   	$("#activated").hide();
	   	$("#active_users").hide();
	   	$("#no_users").hide();
	   	$("#step_1").hide();
	   	$("#step_2").hide();
	   	$("#step_3").hide();	   	
	   	$("#step_4").hide();	   	
   	}

   	function activateFacility(){
   		var facility_code = $("#facility_select").val();
	  	var facility_name = $("#facility_select").text();
	  	var status = $("#facility_select").find(':selected').attr('status');
	  	if(status==0){
	  		$("#activate").show();
	  		$("#activated").hide();
	  	}else{
	  		$("#activate").hide();	  		
	  		var base_url = "<?php echo base_url() . 'facility_activation/get_facility_stats/'; ?>";
		    var url = base_url+facility_code;				
			$.ajax({
				url: url,
				dataType: 'json',
				success: function(s){
					console.log(s);
					var count = s.number;
					var users = s.list;
					if(count==0){
						$("#users_none").show();
					}else{
						$("#users_table tbody > tr").remove();
						var table_header = $("<tr><th><input type=\"checkbox\" id=\"select_all_users\" name=\"select_all\"/>Select</th><th>Full Name</th><th>Date Created</th></tr>");
						$("#users_table").append(table_header);
						// $.each(users, function( index, value ) {
	     //                   var row = $("<tr><td>" + value[0] + "</td><td>" + value[1] + "</td><td>"+value[2]+"</td></tr>");
	     //                   var row = $("<tr><td><input type=\"checkbox\"/></td><td>" + value[0] + "</td><td>" + value[1] + "</td></tr>");
	     //                   $("#users_table").append(row);
	     //                });
						$("#activate").hide();	                    
						// $("#users_none").hide();	                    
						// $("#users_all").show();	                    
					}
				},
				error: function(e){
					console.log(e.responseText);
				}
			});
			$("#activated").show();
	  	}
   	}


   	function getUsers(){
   		var facility_code = $("#facility_select").val();
	  	var facility_name = $("#facility_select").text();	  	
  		var base_url = "<?php echo base_url() . 'facility_activation/get_facility_stats/'; ?>";
	    var url = base_url+facility_code;				
		$.ajax({
			url: url,
			dataType: 'json',
			success: function(s){				
				var count = s.number;
				var users = s.list;
				if(count==0){
					$("#no_users").show();
				}else{
					//$("#users_table tbody > tr").remove();
					$.each(users, function( index, value ) {

		               var row = $("<tr><td><input class=\"selected_users\" type=\"checkbox\" value=\""+value[2]+"\"/></td><td>" + value[0] + "</td><td>" + value[1] + "</td></tr>");
		               $("#users_table").append(row);
		            });
					$("#active_users").show();	                    			
				}			
			},
			error: function(e){
				console.log(e.responseText);
			}
		});
		 	
   	}


   	$("#select_all_users").change(function () {
    	$("input:checkbox").prop('checked', $(this).prop("checked"));
	});


   	function loadStep1(){
   		hideAll();   		
	   	$("#step_1").show();   		
   		activateFacility();
   	}
   	
   	function loadStep2(){
   		hideAll();   	   	
   		getUsers();
   		$("#step_2").show();   		
   	}

   	function loadStep3(){
   		hideAll();   		
	   	$("#step_3").show();   		   		
   	}

	function loadStep4(){
   		hideAll();   		
	   	$("#step_4").show();   		   		
   	}
	$('#filter_facility').click(function() {	    
	  	loadStep1();	  	
	});

	$('#step1_advance').click(function() {	    
	  	loadStep2();	  	
	});

	$('.step3').click(function() {	    
	  	loadStep3();	  	
	});

	// $('#reset_pass').click(function() {
	//     // handle deletion here
	//   	var facility_code = $("#facility_select").val();
	//   	var my_message = '';
 //  		var base_url = "<?php echo base_url() . 'user/reset_multiple_pass/'; ?>";
	//     var url = base_url+facility_code;			    
	// 	$.ajax({
	// 		url: url,
	// 		dataType: 'json',
	// 		success: function(s){				
	// 			my_message = "User passwords reset successfully";				
	// 			alertify.set({ delay: 10000 });
 //          		alertify.success(my_message, null);
	// 		},
	// 		error: function(e){
	// 			console.log(e.responseText);
	// 		}
	// 	});
	  	
	// });



	$('#reset_pass').click(function() {
		var my_message = '';
		var url = "<?php echo base_url() .'user/reset_select_multiple_pass/'; ?>";
		var users_array = [];
		$(".selected_users:checked").each(function(){
			var user_id = $(this).val();
			users_array.push(user_id);					
							
		});
		if(users_array.length<1){
			//console.log("No user selected");
			my_message = "Kindly select a user";				
			alertify.set({ delay: 3000 });
	        alertify.error(my_message, null);
		}else{
			$.ajax({
					url: url,
					dataType: 'json',
					data: users_array,
				success: function(s){				
					my_message = "User passwords reset successfully";				
					alertify.set({ delay: 3000 });
	          		alertify.success(my_message, null);
				},
				error: function(e){
					console.log(e.responseText);
				}
			});

		}
		// console.log(my_array);
		
	});


	$("#add_user_inactive").click(function(){
		$("#addModal").show();
	})

	
	$('#facility_select').change(function(){
	  	hideAll();
	});

	$('.make_db').click(function(e){
		var base_url = "<?php echo base_url() . 'dumper/dump_db/'; ?>";
	  	var facility_code = $("#facility_select").val();		
		var url = base_url+facility_code+'/hcmp_rtk';				
		window.open(url, '_blank'); 
		loadStep4();
	});

	$('.make_bat').click(function(e){
		var base_url = "<?php echo base_url() . 'dumper/gen_bat/'; ?>";
	  	var facility_code = $("#facility_select").val();		
		var url = base_url+facility_code+'/hcmp_rtk';				
		window.open(url, '_blank'); 
		// loadStep4();
	});
	$("#btn_activate_facility").click(function(){
		var facility_code = $("#facility_select").val();
		$('#confirmActivateModal').data('id', facility_code).modal('show');
	});
	
	$('#btnNoActivate').click(function() {
	    message_denial = "No action has been taken";
		alertify.set({ delay: 10000 });
	 	alertify.success(message_denial, null);       
	  	$('#confirmActivateModal').modal('hide');
	  	 return false;
	});
	$('#btnNoDeactivate').click(function() {
	    message_denial = "No action has been taken";
    	alertify.set({ delay: 10000 });
     	alertify.success(message_denial, null);       
	  	$('#confirmDeActivateModal').modal('hide');
	  	 return false;
	});
	
	$('#btnYesActivate').click(function() {
	    // handle deletion here
	  	var facility_code = $('#confirmActivateModal').data('id');
	  	change_status_new(facility_code,0,1);
	  	$('#confirmActivateModal').modal('hide');
	});
	$('#btnYesActivateNoUsers').click(function() {
	    // handle deletion here
	  	var facility_code = $('#confirmActivateModal').data('id');
	  	change_status_new(facility_code,0,0);
	  	$('#confirmActivateModal').modal('hide');
	  	$("#facility_select").find(':selected').attr('status','1');
	  	loadStep1();
	  	// window.location.reload(true);
	});
	$('#btnYesDeactivate').click(function() {
	    // handle deletion here
	  	var facility_code = $('#confirmDeActivateModal').data('id');
	  	change_status_new(facility_code,1,0);
	  	$('#confirmDeActivateModal').modal('hide');
	});

	$(".dataTable").on('click','.status_btn',function(event) {
	    if ( $(this).hasClass("activate") ) {
	       var id = $(this).data('id');
	   	   // alert(id);
	    	$('#confirmActivateModal').data('id', id).modal('show');
	    } else if ( $(this).hasClass("deactivate") ) {
	        $("#confirm_deactivate_table > tbody").html("");
		    var facility_code = $(this).data('id');
		    $('#confirmDeActivateModal').data('id', facility_code).modal('show');
		    var base_url = "<?php echo base_url() . 'facility_activation/get_facility_user_data/'; ?>";
		    var url = base_url+facility_code;
				
			$.ajax({
				url: url,
				dataType: 'json',
				success: function(s){
				// console.log(s);
				 $.each(s, function( index, value ) {
                       var row = $("<tr><td>" + value[0] + "</td><td>" + value[1] + "</td><td>"+value[2]+"</td></tr>");
                       $("#confirm_deactivate_table").append(row);
                    });
				
				},
				error: function(e){
					console.log(e.responseText);
				}
			});
	    }
	});

	 //make sure email==username  for edits
  $('#email_edit').keyup(function() {
  	var email = $('#email_edit').val();
   	$('#username_edit').val(email);
   	$('#username').val(email);
   	
   	$.ajax({
      type: "POST",
      dataType: "json",
      url: "<?php echo base_url()."user/check_user_json";?>", //Relative or absolute path to response.php file
      data:{ 'email': $('#email_edit').val()},
      success: function(data) {
        if(data.response=='false'){
        	$('.err').html(data.msg);
			$( '.err' ).addClass( "alert-danger alert-dismissable" );
			$(".edit_user,#create_new").attr("disabled", "disabled");
		}else if(data.response=='true'){
			$(".err").empty();
			$(".err").removeClass("alert-danger alert-dismissable");
			$( '.err' ).addClass( "alert-success alert-dismissable" );
			$(".edit_user,#create_new").attr("disabled", false);
			$('.err').html(data.msg);
		}
      }
    });
    return false;
	});

  $('#email').keyup(function() {

  var email = $('#email').val()

   $('#username').val(email)
   
   $.ajax({
      type: "POST",
      dataType: "json",
      url: "<?php echo base_url()."user/check_user_json";?>", //Relative or absolute path to response.php file
      data:{ 'email': $('#email').val()},
      success: function(data) {
        if(data.response=='false'){
						
						 $('#err').html(data.msg);
							$( '#err' ).addClass( "alert-danger alert-dismissable" );
							$(".edit_user,#create_new").attr("disabled", "disabled");
							}else if(data.response=='true'){
								
								$("#err").empty();
								$("#err").removeClass("alert-danger alert-dismissable");
								$( '#err' ).addClass( "alert-success alert-dismissable" );
								$(".edit_user,#create_new").attr("disabled", false);
								$('#err').html(data.msg);
								
								
							}
      }
    });
    return false;
  });

  $('#email').bind('input change paste keyup mouseup',function() {
  	// var email = $('#email').val();   	   	
   	
   	$.ajax({
      type: "POST",
      dataType: "json",
      url: "<?php echo base_url()."user/check_user_json";?>", //Relative or absolute path to response.php file
      data:{ 'email': $('#email').val()},
      beforeSend: function(){
        	$('#processing').html('Checking Email...');

      },
      success: function(data) {
      	console.log(data);
        if(data.response=='false'){
        	$('#processing').html(data.msg);
			$( '#processing' ).addClass( "alert-danger alert-dismissable" );
			$("#create_new").attr("disabled", "disabled");
		}else if(data.response=='true'){
			$("#processingr").val('');
			$("#processing").removeClass("alert-danger alert-dismissable");
			$('#processing' ).addClass( "alert-success alert-dismissable" );
			$("#create_new").attr("disabled", false);
			$('#processing').html(data.msg);
		}
      }
    });
    return false;
	});

$("#create_new").click(function() {

      var first_name = $('#first_name').val();
      var last_name = $('#last_name').val();
      var telephone = $('#telephone').val();
      var email = $('#email').val();
      var username = $('#username').val();
      var facility_id = $('#facility_select').val();
      var district_name = '<?php echo $district_id ?>';
      var user_type = $('#user_type').val();
   	if(first_name==""||last_name==""||telephone==""||email==""||user_type=="NULL"||district_name=="NULL"){
		alert('Please make sure you have selected all relevant fields.');
	return;
	}

      var div="#processing";
      var url = "<?php echo base_url()."user/addnew_user_offline";?>";
      ajax_post_process (url,div);
      loadStep2();
    });

function ajax_post_process (url,div){
    var url =url;

     //alert(url);
    // return;
     var loading_icon="<?php echo base_url().'assets/img/Preloader_4.gif' ?>";
     var facility_code = $("#facility_select").val();
     // alert(facility_code);
     $.ajax({
          type: "POST",
          data:{ 'first_name': $('#first_name').val(),'last_name': $('#last_name').val(),
          'telephone': $('#telephone').val(),'email': $('#email').val(),
          'username': $('#username').val(),'facility_id': facility_code,
          'district_name': $('#district_name').val(),'user_type': $('#user_type').val()},
          url: url,
          beforeSend: function() {
           
            var message = confirm("Are you sure you want to proceed?");
        if (message){
            $('.modal-body').html("<img style='margin:30% 0 20% 42%;' src="+loading_icon+">");
        } else {
            return false;
        }
           
          },
          success: function(msg) {
          	
          	//$('.modal-body').html(msg);return;
         
        setTimeout(function () {
          	$('.modal-body').html("<div class='bg-warning' style='height:30px'>"+
							"<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>"+
							"<h3 style='font-size:12px'>Success!!! A new user was added to the system. Please Close to continue</h3></div>")
							
			$('.modal-footer').html("<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>")
				
        }, 4000);
            
                  
          }
        }); 
        
}


	function change_status_new(facility_code,stati,add_users){//seth      
      message = "";
     
      var loading_icon="<?php echo base_url().'assets/img/Preloader_4.gif' ?>";
      // alert(stati);

      $.ajax({
          type:"POST",
          data:{
            'facility_code': facility_code,
            'status': stati
        },

      url:"<?php echo base_url()."facility_activation/change_status_new";?>",

      beforeSend: function() {
       
    	},
        success: function(msg){            	
          var data = jQuery.parseJSON(msg);
          using_hcmp = data.using_hcmp;
          date = data.date_of_activation;
          // var date = jQuery.parseJSON(msg.date_of_activation);
          if(using_hcmp==1){
        	message_after = "Facility: "+ facility_code +" has been Activated";
        	// $('#chkbx_'+facility_code).removeAttr('checked');	        	
        	// $('#chkbx_'+facility_code).addAttr('checked');	        	
        	$('#chkbx_'+facility_code).prop('checked' ,true);
        	$('#date_'+facility_code).html(date);
        	$('#btn_'+facility_code).html('Deactivate');
        	$('#btn_'+facility_code).attr('data-value','1');
        	$('#btn_'+facility_code).removeClass('btn-success');
        	$('#btn_'+facility_code).addClass('btn-danger');
        	$('#btn_'+facility_code).removeClass('activate');
        	$('#btn_'+facility_code).addClass('deactivate');
        	if(add_users==1){
        		var base_url = "<?php echo base_url().'user/user_create_multiple/' ?>";
   				window.location.href = base_url+facility_code;	      
        	}
        	  	
          }else{
          	message_after = "Facility: "+ facility_code +" has been Deactivated";
        	$('#chkbx_'+facility_code).removeAttr('checked');	        	
        	// $('#chkbx_'+facility_code).addAttr('checked');
        	// $('#chkbx_'+facility_code).prop('checked' ,false);	        	
        	$('#date_'+facility_code).html('Not Active');	 
        	$('#btn_'+facility_code).html('Activate');
        	$('#btn_'+facility_code).attr('data-value','0');
        	$('#btn_'+facility_code).removeClass('deactivate');
        	$('#btn_'+facility_code).addClass('activate');	  
        	$('#btn_'+facility_code).removeClass('btn-danger');	        	      	
        	$('#btn_'+facility_code).addClass('btn-success ');
        	

          }
          alertify.set({ delay: 10000 });
          alertify.success(message_after, null);
        }

      });
    }//end of change status function
		function initialize_checkboxes(){
			$('#btnNoActivate').click(function() {
		    message_denial = "No action has been taken";
        	alertify.set({ delay: 10000 });
         	alertify.success(message_denial, null);       
		  	$('#confirmActivateModal').modal('hide');
		  	 return false;
		});
		$('#btnNoDeactivate').click(function() {
		    message_denial = "No action has been taken";
        	alertify.set({ delay: 10000 });
         	alertify.success(message_denial, null);       
		  	$('#confirmDeActivateModal').modal('hide');
		  	 return false;
		});
		$('#btnYesActivate').click(function() {
		    // handle deletion here
		  	var facility_code = $('#confirmActivateModal').data('id');
		  	change_status_new(facility_code,0,0);
		  	$('#confirmActivateModal').modal('hide');
		});
		$('#btnYesDeactivate').click(function() {
		    // handle deletion here
		  	var facility_code = $('#confirmDeActivateModal').data('id');
		  	change_status_new(facility_code,1,0);
		  	$('#confirmDeActivateModal').modal('hide');
		});
   		$('.deactivate').on('click', function(e) {
		    e.preventDefault();
		    var facility_code = $(this).data('id');
		    $('#confirmDeActivateModal').data('id', facility_code).modal('show');
		    var base_url = "<?php echo base_url() . 'facility_activation/get_facility_user_data/'; ?>";
		    var url = base_url+facility_code;
		    var oTable = $('.confirm_deactivate_table').dataTable(
			{	
				retrieve: true,
    			paging: false,
				"bPaginate":false, 
			    "bFilter": false,
			    "bSearchable":false,
			    "bInfo":false
			});				
			$.ajax({
				url: url,
				dataType: 'json',
				success: function(s){
				// console.log(s);
				// alert(s);
				oTable.fnClearTable();
				for(var i = 0; i < s.length; i++) {
					oTable.fnAddData([
					s[i][0],
					s[i][1],
					s[i][2]
					]);
					} // End For
				},
				error: function(e){
					console.log(e.responseText);
				}
			});
		    
		});

		$('.activate').on('click', function(e) {
		    e.preventDefault();
		    var id = $(this).data('id');
		    $('#confirmActivateModal').data('id', id).modal('show');
		});
		}

		$('.modal').on('hidden.bs.modal', function(e)
    { 
        $(this).removeData();
    }) ;
		
	
	
				
	

    function change_status(facility_code,stati,checked){//seth
      // alert(checked);return;
      message = "";
      if (stati == 0) {
        message_after = "Facility: "+ facility_code +" has been Deactivated";
      }else{
        message_after = "Facility: "+ facility_code +" has been Activated";

      };
      var loading_icon="<?php echo base_url().'assets/img/Preloader_4.gif' ?>";
      // alert(stati);

      $.ajax({
          type:"POST",
          data:{
            'facility_code': facility_code,
            'status': stati
        },

          url:"<?php echo base_url()."facility_activation/change_status";?>",

          beforeSend: function() {
            //$(div).html("");
            // alert($('#email_recieve_edit').prop('checked'));return;
            var answer = confirm("Are you sure you want to proceed?");
            if (answer){
                $('.modal-body').html("<img style='margin:30% 0 20% 42%;' src="+loading_icon+">");
            } else {
            	message_denial = "No action has been taken";
            	alertify.set({ delay: 10000 });
             	alertify.success(message_denial, null);
            	if (checked == "checked") {
            		// alert("im checked");
            		$('input[data-attr="'+facility_code+'"]').prop('checked' ,false);
            	}else{
            		// alert("im unchecked");
            		$('input[data-attr="'+facility_code+'"]').prop('checked' ,true);


            	};
                return false;
            }},
            success: function(msg){
            	// alert(msg);return;
              alertify.set({ delay: 10000 });
              alertify.success(message_after, null);
            }

        });
    }//end of change status function
			
			});
    </script>

<div class="modal fade" id="confirmActivateModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Confirm Activation</h4>
      </div>
      <div class="modal-body" style="font-size:14px;text-align:centre">
        <p>This facility will now be active and users will be able to submit data. <p/>
        <p>Confirm Activation?</p>
      </div>
      <div class="modal-footer">
        <button type="button"  id="btnYesActivateNoUsers" class="btn btn-success" data-dismiss="modal">Activate</button>
        <button type="button"  id="btnNoActivate" class="btn btn-danger" data-dismiss="modal">Cancel</button>
        <!-- <button type="button" id="btnYesActivate" class="btn btn-primary" id="btn-ok">Activate adding Users</button> -->
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="confirmDeActivateModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Confirm Deactivation</h4>
      </div>
      <div class="modal-body" style="font-size:14px;text-align:centre">
      <center>
      	<!-- <center><img src="<?php echo base_url().'assets/img/Alert_resized.png'?>" style="height:150px;width:150px;"></center><br/> -->
        <p>The following users are currently active under this facility. Deactivation of the facility will render them unable to use the system.</p>
        <table  id="confirm_deactivate_table" class="display table table-bordered confirm_deactivate_table" cellspacing="0" width="100%">
        	<thead>
        		<tr><th>User Details</th><th>Date Activated</th><th>Date Last Logged In</th></tr>
        	</thead>
        	<tbody></tbody>
        </table>
         <br/>Are you sure you want to deactivate this facility?</p>
        </center>
      </div>
      <div class="modal-footer">
        <button type="button"  id="btnNoDeactivate"  class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="btnYesDeactivate" class="btn btn-danger" id="btn-ok">Yes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- Modal add user -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" id="myform">
	<div class="modal-dialog editable" >
		<div class="modal-content">
			<div class="modal-header" style="">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title" id="myModalLabel" style="text-align: center;line-height: 1">Add User</h4>
			</div>
			<div class="row" style="margin:auto" id="error_msg">
				<div class=" col-md-12">
					<div class="form-group">

					</div>
				</div>

			</div>
			<div class="modal-body" style="padding:0">
				<div class="row" style="margin:auto">
					<div class="col-md-12 ">
					<center>
						<form role="form">

							<fieldset class = "col-md-12">
							<center>
							<!--
								<legend style="font-size:1.5em">
									Add User
								</legend>
								-->

								<div class="input-group form-group u_mgt">
									<span class="input-group-addon sponsor">First Name</span>
									<input type="text" required="required" name="first_name" id="first_name" class="form-control " placeholder="Enter First Name" >
								</div>

								<div class="input-group form-group u_mgt">
									<span class="input-group-addon sponsor">Last Name</span>
									<input type="text" name="last_name" required="required" id="last_name" class="form-control " placeholder="Last Name" >
								</div>

								<div class="input-group form-group u_mgt">
									<span class="input-group-addon sponsor">Phone Number</span>
									<input type="telephone" name="telephone" required="required" id="telephone" class="form-control " placeholder="Enter Phone Number eg, 254" tabindex="5">
								</div>

								<div class="input-group form-group u_mgt">
									<span class="input-group-addon sponsor">Email</span>
									<input type="email" name="email" id="email" required="required" class="form-control " placeholder="email@domain.com" tabindex="6">
								</div>

								<div class="input-group form-group u_mgt">
									<span class="input-group-addon sponsor">User Name</span>
									<input type="email" name="username" id="username" required="required" class="form-control " placeholder="email@domain.com" tabindex="5" readonly>
								</div>

								<div class="input-group form-group u_mgt">
									<span class="input-group-addon sponsor">User Type</span>
									<select class="form-control " id="user_type" name="user_type" required="required">
												<option value='NULL'>Select User type</option>
												<?php
												foreach ($user_types as $user_types) :
													$id = $user_types ['id'];
													$type_name = $user_types ['level'];
													echo "<option value='$id'>$type_name</option>";
												endforeach;
												?>
									</select>
								</div>
									<?php

									$identifier = $this -> session -> userdata('user_indicator');
									
									if ($identifier=='district') {
									?>
									


									<?php }elseif ($identifier=='county') { ?>
									<div class="input-group form-group u_mgt">
										<span class="input-group-addon sponsor">Subcounty Name</span>
										<select class="form-control " id="district_name" required="required">
											<option value=''>Select Sub-County</option>

											<?php
											foreach ($district_data as $district_) :
												$district_id = $district_ ['id'];
												$district_name = $district_ ['district'];
												echo "<option value='$district_id'>$district_name</option>";
											endforeach;
											?>
										</select>
									</div>

									<div class="input-group form-group u_mgt">
										<span class="input-group-addon sponsor">Facility Name</span>
										<select class="form-control " id="facility_id" required="required">
												<option value="">Select Facility</option>
												
										</select>

									</div>

								<?php }?>
								<div class="row" style="margin:auto" id="processing">
									<div class=" col-md-12">
										<div class="form-group">
										</div>
									</div>
								</div>
								</center>

							</fieldset>

						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default borderless" data-dismiss="modal">
					Close
				</button>
				
				<button class="btn btn-primary borderless" id="create_new">
					Save changes
				</button>
			</div>
		</div>
	</div>
</div><!-- end Modal new user -->
