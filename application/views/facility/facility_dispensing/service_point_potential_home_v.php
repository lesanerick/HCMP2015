<?php //echo "<pre>"; print_r($potential_expiry_data); echo "</pre>"; exit; ?>
<div>
	<h2>Potential Expiries (6 Month Interval)</h2>
	<table cellpadding="0" cellspacing="0" width="100%" border="0" class="row-fluid table table-hover table-bordered table-update my" id="potential_expiries">
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
			foreach ($potential_expiry_data as $potential_expiry_data) {
				echo "<tr>
				<td>$potential_expiry_data[facility_code]</td>
				<td>$potential_expiry_data[commodity_name]</td>
				<td>$potential_expiry_data[total_commodity_units]</td>
				<td>$potential_expiry_data[current_balance]</td>
				<td>$potential_expiry_data[batch_no]</td>
				<td>$potential_expiry_data[expiry_date]</td>
				<td>$potential_expiry_data[service_point_name]</td>
				</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<script>
	/* Table initialisation */
$(document).ready(function() {
	//datatables settings 
	$("#potential_expiries").dataTable( {
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
	} );
</script>