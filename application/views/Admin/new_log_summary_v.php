<?php //echo "<pre>";print_r($facilities_listing_inactive);echo "</pre>";exit; ?>
<style>
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
	#addModal .modal-dialog,#editModal .modal-dialog {
		width: 54%;
		
	}
		
  .table{
    font-size: 13px;
  }
	
  #date_filter_nav{
    width:100%;
    height: 60px;
    margin-top: 10px;
    margin-bottom: 10px;
    border-bottom: 1px ridge #e3e3e3;
    /*background-color: #e3e3e3;*/
  }
</style>

<!---random comment to enable for commit -->


<div class="container-fluid">
	
	<div class="row" style="margin-top: 1%;" >
		<div class="col-md-12">
			<div id="date_filter_nav">
        <label class="form-control btn btn-primary" style="width:15%;float:left;">Select Period: </label>
        <select id="year" class="form-control" style="width:20%;float:left">
         <option value="0">Select Year</option>         
          <?php            
            for ($i=0; $i <=1; $i++) {               
              $year_1 = date('Y',strtotime("-$i year"));
              if($year==$year_1){?>
                <option value="<?php echo $year?>" selected><?php echo $year;?></option>
              <?php }else{?>
                <option value="<?php echo $year_1?>"><?php echo $year_1;?></option>
              <?php }
              ?>
          <?php }
          ?>          
        </select>

        <select id="month" class="form-control" style="width:20%;float:left">
          <option value="0">Select Month</option>
          <?php            
            for ($i=0; $i <=11; $i++) {               
              $month_1 = date('m',strtotime("-$i month"));
              $month_text_options = date('F',strtotime("-$i month"));
              if($month==$month_1){?>
                <option value="<?php echo $month?>" selected><?php echo $month_text;?></option>
              <?php }else{?>
                <option value="<?php echo $month_1?>"><?php echo $month_text_options;?></option>
              <?php }
              ?>
          <?php }
          ?>   
          
          ?>          
        </select>
        <button class="btn btn-success form-control" style="width:20%;float:left" id="filter_month_year">Filter</button>

         <button class="btn btn-primary send_email form-control" style="width:15%;float:right;margin-left:5px;">
          Email Weekly Log Summary
        </button>

      </div>
			<ul class="nav nav-tabs" id="Tab">
  <li class="active"><a href="#home" data-toggle="tab"><span class="glyphicon glyphicon-cog"></span>Facilities that Logged In</a></li>
  <li><a href="#nli" data-toggle="tab"><span class="glyphicon glyphicon-list"></span> Facilities that haven't Logged In</a></li>
  <li><a href="#issued" data-toggle="tab"><span class="glyphicon glyphicon-cog"></span> Facilities that Issued</a></li>
  <li><a href="#n_issued" data-toggle="tab"><span class="glyphicon glyphicon-list"></span> Facilities that haven't Issued</a></li>
</ul>

<div class="tab-content" style="margin-top: 5px;">
<div class="tab-pane active" id="home">
  	

<br/><br/>
<center>
<div style="width:100%;height:auto;float:left;margin-left:10px;">

<h4>Facilities that Have Logged In at least once during the month of <?php echo $month_text.' '.$year;?></h4>
<table id="sms_usage" class="table table-hover table-bordered table-update col-md-10">
  <thead>
    <tr>
      <th ><b>Facility Name</b></th>
      <th ><b>Facility Code</b></th>
      <th ><b>Sub-County</b></th>
      <th ><b>County</b></th>
    </tr> 
  </thead>
  <tbody>
    <?php 
      $row_data = $monthly_logs['logged_in'];      
      $count = count($row_data);
      foreach ($row_data as $key => $value) {
        $facility_name = $value['facility_name'];
        $facility_code = $value['facility_code'];
        $sub_county = $value['district'];
        $county = $value['county']; ?>
        <tr>
          <td><?php echo $facility_name;?></td>
          <td><?php echo $facility_code;?></td>
          <td><?php echo $sub_county;?></td>
          <td><?php echo $county;?></td>
        </tr>
      <?php }
     
    ?>
  </tbody>
  </table>
</div>
</center>

<br/><br/>
<center>
<hr/>
<div style="width:100%;margin-top:60px;height:auto;float:left;margin-left:10px;border-top:1px ridge">
<br/>
<h4>Facilities that Have Logged In at least 4 times during the month of <?php echo $month_text.' '.$year;?></h4>
<table id="facility_nli1" class="table table-hover table-bordered table-update col-md-10">
  <thead>
    <tr>
      <th ><b>Facility Name</b></th>
      <th ><b>Facility Code</b></th>
      <th ><b>Sub-County</b></th>
      <th ><b>County</b></th>
      <!-- <th ><b>Number of Logins</b></th> -->
    </tr> 
  </thead>
  <tbody>
    <?php 
      $row_data = $monthly_logs['logged_in_count'];      
      $count = count($row_data);
      foreach ($row_data as $key => $value) {
        // echo "<pre>";print_r($value);die;
        $facility_name = $value['facility_name'];
        $facility_code = $value['facility_code'];
        $sub_county = $value['district'];
        $number = $value['days'];
        $county = $value['county']; ?>
        <tr>
          <td><?php echo $facility_name;?></td>
          <td><?php echo $facility_code;?></td>
          <td><?php echo $sub_county;?></td>
          <td><?php echo $county;?></td>
          <!-- <td><?php //echo $number;?></td> -->
        </tr>
      <?php }
     
    ?>
  </tbody>
  </table>
</div>
</center>
<br/>
<br/>
  	
</div>
<div class="tab-pane" id="nli">  

<br/><br/>
<center>
<div style="width:100%">

<h4>Facilities that Have Not Logged In at least once during the month of <?php echo $month_text.' '.$year;?></h4>
<table id="facility_nli1" class="table table-hover table-bordered table-update col-md-10">
  <thead>
    <tr>
      <th ><b>Facility Name</b></th>
      <th ><b>Facility Code</b></th>
      <th ><b>Sub-County</b></th>
      <th ><b>County</b></th>
    </tr> 
  </thead>
  <tbody>
    <?php 
      $row_data = $monthly_logs['not_logged_in'];      
      $count = count($row_data);
      foreach ($row_data as $key => $value) {
        $facility_name = $value['facility_name'];
        $facility_code = $value['facility_code'];
        $sub_county = $value['district'];
        $county = $value['county']; ?>
        <tr>
          <td><?php echo $facility_name;?></td>
          <td><?php echo $facility_code;?></td>
          <td><?php echo $sub_county;?></td>
          <td><?php echo $county;?></td>
        </tr>
      <?php }
     
    ?>
  </tbody>
  </table>
</div>
</center>

    
  </div>
  <div class="tab-pane" id="issued">  
    <center>
<div style="width:100%;height:auto;float:left;margin-left:10px;">

<h4>Facilities that Issued at least once during the month of <?php echo $month_text.' '.$year;?></h4>
<table id="sms_usage" class="table table-hover table-bordered table-update col-md-10">
  <thead>
    <tr>
      <th ><b>Facility Name</b></th>
      <th ><b>Facility Code</b></th>
      <th ><b>Sub-County</b></th>
      <th ><b>County</b></th>
    </tr> 
  </thead>
  <tbody>
    <?php 
      $row_data = $monthly_logs['issued_within_month'];      
      $count = count($row_data);
      foreach ($row_data as $key => $value) {
        $facility_name = $value['facility_name'];
        $facility_code = $value['facility_code'];
        $sub_county = $value['district'];
        $county = $value['county']; ?>
        <tr>
          <td><?php echo $facility_name;?></td>
          <td><?php echo $facility_code;?></td>
          <td><?php echo $sub_county;?></td>
          <td><?php echo $county;?></td>
        </tr>
      <?php }
     
    ?>
  </tbody>
  </table>
</div>
</center>

<br/><br/>
<center>
<hr/>
<div style="width:100%;margin-top:60px;height:auto;float:left;margin-left:10px;border-top:1px ridge">
<br/>
<h4>Facilities that Issued at least 4 times during the month of <?php echo $month_text.' '.$year;?></h4>
<table id="facility_nli1" class="table table-hover table-bordered table-update col-md-10">
  <thead>
    <tr>
      <th ><b>Facility Name</b></th>
      <th ><b>Facility Code</b></th>
      <th ><b>Sub-County</b></th>
      <th ><b>County</b></th>
      <!-- <th ><b>Number of Issues</b></th> -->
    </tr> 
  </thead>
  <tbody>
    <?php 
      $row_data = $monthly_logs['issued_count'];      
      $count = count($row_data);
      foreach ($row_data as $key => $value) {
        $facility_name = $value['facility_name'];
        $facility_code = $value['facility_code'];
        $sub_county = $value['district'];
        $number = $value['days'];
        $county = $value['county']; ?>
        <tr>
          <td><?php echo $facility_name;?></td>
          <td><?php echo $facility_code;?></td>
          <td><?php echo $sub_county;?></td>
          <td><?php echo $county;?></td>
          <!-- <td><?php //echo $number;?></td> -->
        </tr>
      <?php }
     
    ?>
  </tbody>
  </table>
</div>
</center>


    
  </div>
  <div class="tab-pane" id="n_issued">  

<br/><br/>
<center>
<div style="width:100%">

<h4>Facilities that Have Not Issued during the month of <?php echo $month_text.' '.$year;?></h4>
<table id="facility_nli1" class="table table-hover table-bordered table-update col-md-10">
  <thead>
    <tr>
      <th ><b>Facility Name</b></th>
      <th ><b>Facility Code</b></th>
      <th ><b>Sub-County</b></th>
      <th ><b>County</b></th>
    </tr> 
  </thead>
  <tbody>
    <?php 
      $row_data = $monthly_logs['not_issued'];      
      $count = count($row_data);
      foreach ($row_data as $key => $value) {
        $facility_name = $value['facility_name'];
        $facility_code = $value['facility_code'];
        $sub_county = $value['district'];
        $county = $value['county']; ?>
        <tr>
          <td><?php echo $facility_name;?></td>
          <td><?php echo $facility_code;?></td>
          <td><?php echo $sub_county;?></td>
          <td><?php echo $county;?></td>
        </tr>
      <?php }
     
    ?>
  </tbody>
  </table>
</div>
</center>

    
  </div>

  <!-- <div class="tab-pane" id="profile">
    <?php 
     //$this -> load -> view('Admin/facilities_listing_inactive');
     ?>
  </div> -->
  
</div>

		</div>
	</div>
	
	
</div>




	
<script>
      $(document).ready(function () {
        
    // $('#sms_usage').dataTable({
    //   "paging":   true,
    //       "ordering": false,
    //       "info":     false
    //     }); 

        $('.send_email').click(function(e){
          e.preventDefault();
          var url = "<?php echo base_url(); ?>sms/log_summary_weekly";
          window.location.href = url;
        });

        $('#filter_month_year').click(function (y){
          y.preventDefault();
          var year = $('#year').val();
          var month = $('#month').val();
          var url = "<?php echo base_url(); ?>sms/new_weekly_usage/";
          if (year!=0) {url =url+year};
          if (month!=0) {
            if(year!=0){
              url =url+'/'+month;
            }else{
              url = url+''+'/'+month;
            }
          };          
          window.location.href = url;

        });
  });
</script>

