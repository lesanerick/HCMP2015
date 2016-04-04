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
					<div id="users_none">						

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
					</div>

					

				</div>
			</div>
		</div>
	</div>
</div>
</div>

<script>
   $(document).ready(function () {
   	$("#activate").hide();
   	$("#users_none").hide();
   	$("#users_all").hide();
	$('#filter_facility').click(function() {
	    // handle deletion here
	  	var facility_code = $("#facility_select").val();
	  	var facility_name = $("#facility_select").text();
	  	var status = $("#facility_select").find(':selected').attr('status');
	  	if(status==0){
	  		$("#activate").show();
	  	}else{
	  		$("#activate").hide();
	  		var base_url = "<?php echo base_url() . 'facility_activation/get_facility_stats/'; ?>";
		    var url = base_url+facility_code;				
			$.ajax({
				url: url,
				dataType: 'json',
				success: function(s){
					var count = s.number;
					var users = s.list;
					if(count==0){
						$("#users_none").show();
					}else{
						$.each(users, function( index, value ) {
	                       var row = $("<tr><td>" + value[0] + "</td><td>" + value[1] + "</td><td>"+value[2]+"</td></tr>");
	                       $("#users_table").append(row);
	                    });
						$("#activate").hide();	                    
						$("#users_none").hide();	                    
						$("#users_all").show();	                    
					}
				},
				error: function(e){
					console.log(e.responseText);
				}
			});
	  	}
	});

	$('#reset_pass').click(function() {
	    // handle deletion here
	  	var facility_code = $("#facility_select").val();
	  	var my_message = '';
  		var base_url = "<?php echo base_url() . 'user/reset_multiple_pass/'; ?>";
	    var url = base_url+facility_code;			    
		$.ajax({
			url: url,
			dataType: 'json',
			success: function(s){				
				my_message = "User passwords reset successfully";				
				alertify.set({ delay: 10000 });
          		alertify.success(my_message, null);
			},
			error: function(e){
				console.log(e.responseText);
			}
		});
	  	
	});

	
	$('#facility_select').change(function(){
	  	$("#activate").hide();
	  	$("#users_none").hide();
	  	$("#users_all").hide();
	});

	$('.make_db').click(function(e){
		var base_url = "<?php echo base_url() . 'dumper/dump_db/'; ?>";
	  	var facility_code = $("#facility_select").val();		
		var url = base_url+facility_code+'/hcmp_rtk';				
		window.open(url, '_blank'); 
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
