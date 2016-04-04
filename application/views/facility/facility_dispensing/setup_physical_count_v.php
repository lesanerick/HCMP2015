<style>
	.row div p{
	padding:10px;
}
</style>
<div class="test"><div class="container" style="width: 96%; margin: auto;">
	
	<div class="row">
		<div class="col-md-7" id=""><p class="bg-info">
			 Enter the units of the commodity and click on Save</p>
		</div>		
	</div>
	</div>

</div>
<center>
<div style="max-height:600px; overflow-y:auto; width: 96%">
<form id="update_sp_stock" method="post" action="<?php echo base_url('dispensing/update_service_point_prices')?>">
<table width="100%" class="row-fluid table table-hover table-bordered table-update"  id="example">
<thead>
<tr style="background-color: white">						
						<th>Facility&nbsp;Code</th>
						<th>Commodity name</th>						
						<th>Price</th>						
					    <th>Initial Units</th>	
					    <th>Current Balance</th>
					    <th>Batch #</th>
					    <th>Physical count</th>	 
					    <th>Reason/Explanation</th>	     
					    		    
</tr>
</thead>
<tbody>
<?php   foreach($commodities as $facility_commodities):
	       $status=$facility_commodities['selected']=='1' ? 'checked="true"'  : null; 	   
		   echo "<tr>
		   <input type='hidden' name='commodity_id[]' class='commodity_id' value='$facility_commodities[commodity_id]'/>	   
		   <td>$facility_commodities[facility_code]</td>	
		   <td>$facility_commodities[commodity_name]</td>	 
		   <td>$facility_commodities[price]</td>
		   <td>$facility_commodities[total_commodity_units]</td>
		   <td>$facility_commodities[current_balance]</td>
		   <td>$facility_commodities[batch_no]</td>
		   <td><input class='form-control input-small price' type='text' name='physical_count' value='$facility_commodities[physical_count]' </td>
		   <td><input class='form-control input-small price' type='text' name='reason'  </td>
		   </tr>"; 
       endforeach; 
?>
</tbody>
</table>
</form>
</div> 
</center>
<hr />
<div class="container-fluid">
<div style="float: right">
<button class="save btn btn-success">
<span class="glyphicon glyphicon-open"></span>Save</button></div>
</div>
</div>
<script>
$(document).ready(function() {	
	window.onbeforeunload = function() {
        return "Are you sure you want to leave?";
    }
    
	//datatables settings 
	$('#example').dataTable( {
		   "sDom": "T lfrtip",
	     "sScrollY": "377px",
	     "sScrollX": "100%",
                    "sPaginationType": "bootstrap",
                    "oLanguage": {
                        "sLengthMenu": "_MENU_ Records per page",
                        "sInfo": "Showing _START_ to _END_ of _TOTAL_ records",
                    },
			      "oTableTools": {
                 "aButtons": [
				"copy",
				"print",
				{
					"sExtends":    "collection",
					"sButtonText": 'Save',
					"aButtons":    [ "csv", "xls", "pdf" ]
				}
			],
			"sSwfPath": "<?php echo base_url(); ?>assets/datatable/media/swf/copy_csv_xls_pdf.swf"
		},
        "bPaginate": false} );
	$('#example_filter label input').addClass('form-control');
	$('#example_length label select').addClass('form-control');			
	
	$('.save').button().click(function() {
		$('#update_sp_stock').submit();
	
		
	});
})
 
</script>