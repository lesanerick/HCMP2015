<div class="container-fluid">
	<div class="page_content">
		<div class="container-fluid">
			<div class="row" style="padding:5%;">
			<!-- <div class="col-md-3" style="margin:10px 0;float:right;"> -->
				<?php $att=array("name"=>'db_upload','id'=>'db_upload'); echo form_open_multipart('client_sync/upload_db_file',$att);?>
					<input class="form-control" type="file" name="db_file" required="required" />

					<input type="submit" value="upload" />
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>