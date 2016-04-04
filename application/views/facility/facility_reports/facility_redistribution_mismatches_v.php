<div>
	<h2>Source Facility (This)</h2>
	<?php //echo "<pre>"; print_r($redistribution_data); echo "</pre>"; ?>
	<table cellpadding="0" cellspacing="0" width="100%" border="0" class="row-fluid table table-hover table-bordered table-update my" id="mismatch_source">
		<thead>
			<tr>
				<th>Source Facility Code</th>
				<th>Receive Facility Code</th>
				<th>Commodity ID</th>
				<th>Quantity Sent</th>
				<th>Quantity Received</th>
				<th>Sender ID</th>
				<th>Receiver ID</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($redistribution_data_source as $redistribution_data_source) {
				echo "<tr>
				<td>$redistribution_data_source[source_facility_code]</td>
				<td>$redistribution_data_source[receive_facility_code]</td>
				<td>$redistribution_data_source[commodity_id]</td>
				<td>$redistribution_data_source[quantity_sent]</td>
				<td>$redistribution_data_source[quantity_received]</td>
				<td>$redistribution_data_source[sender_id]</td>
				<td>$redistribution_data_source[receiver_id]</td>
				</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<div>
	<h2>Receive Facility (This)</h2>
	<?php //echo "<pre>"; print_r($redistribution_data); echo "</pre>"; ?>
	<table cellpadding="0" cellspacing="0" width="100%" border="0" class="row-fluid table table-hover table-bordered table-update my" id="mismatch_receive">
		<thead>
			<tr>
				<th>Source Facility Code</th>
				<th>Receive Facility Code</th>
				<th>Commodity ID</th>
				<th>Quantity Sent</th>
				<th>Quantity Received</th>
				<th>Sender ID</th>
				<th>Receiver ID</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($redistribution_data_receive as $redistribution_data_receive) {
				echo "<tr>
				<td>$redistribution_data_receive[source_facility_code]</td>
				<td>$redistribution_data_receive[receive_facility_code]</td>
				<td>$redistribution_data_receive[commodity_id]</td>
				<td>$redistribution_data_receive[quantity_sent]</td>
				<td>$redistribution_data_receive[quantity_received]</td>
				<td>$redistribution_data_receive[sender_id]</td>
				<td>$redistribution_data_receive[receiver_id]</td>
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
	$("#mismatch_source").dataTable( {
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
	$("#mismatch_receive").dataTable( {
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