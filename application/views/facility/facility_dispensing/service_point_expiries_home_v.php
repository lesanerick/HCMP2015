<?php //echo "<pre>"; print_r($expiry_data); echo "</pre>"; exit; ?>
<div>
	<h2>Expiries: Not Decommisioned</h2>
	<table cellpadding="0" cellspacing="0" width="100%" border="0" class="row-fluid table table-hover table-bordered table-update my" id="expiries">
		<thead>
			<tr>
				<th>Facility Code</th>
				<th>Commodity Name</th>
				<th>Total Commodity Units</th>
				<th>Current Balance</th>
				<th>Batch No</th>
				<th>Expiry Date</th>
				<th>Service Point Name</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($expiry_data as $expiry_data) {
				echo "<tr>
				<td>$expiry_data[facility_code]</td>
				<td>$expiry_data[commodity_name]</td>
				<td>$expiry_data[total_commodity_units]</td>
				<td>$expiry_data[current_balance]</td>
				<td>$expiry_data[batch_no]</td>
				<td>$expiry_data[expiry_date]</td>
				<td>$expiry_data[service_point_name]</td>
				</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<div id="decom-button">
	<br />
	<button id="decbutton" class="btn btn-success pull-right">Decommission All</button>
</div>
<script>
	/* Table initialisation */
$(document).ready(function() {
	//datatables settings 
	$("#expiries").dataTable( {
	     "sDom": "T lfrtip",
	     "sScrollY": "377px",
                    "sPaginationType": "bootstrap",
                    "oLanguage": {
                        "sLengthMenu": "_MENU_ Records per page"
                    },
			      "oTableTools": {
			      	"sSwfPath": "<?php echo base_url('assets/datatable/media/swf/copy_csv_xls_pdf.swf'); ?>",
                 "aButtons": [
				"copy",
				"print",
				{
					"sExtends":    "collection",
					"sButtonText": 'Save',
					"aButtons":    [ "csv", "xls", "pdf" ]
				}
			],
		
		},
		
	} );
	$('.dataTables_filter label input').addClass('form-control');
	$('.dataTables_length label select').addClass('form-control');

	$("#decbutton").on("click", function(){
		//console.log("Dec Button Clicked!");
		decommisionExpiries();
	});

	function decommisionExpiries(){
		var url = <?php echo base_url() ?>;
		url += "dispensing/decommision_expiries";
		$.ajax({
				url: url,
				type: "POST",
				complete: function(xhr,status){
					console.log("The request is complete!");
				}
			});
	}
	});
</script>