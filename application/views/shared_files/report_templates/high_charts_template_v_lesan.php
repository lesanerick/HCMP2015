<ul class='nav nav-tabs'>
      <li class="active"><a id="sys_usage0" href="#system_usage_graph" role="tab" data-toggle="tab" >Days from Last Login</a></li>
      <li class=""><a id="sys_usage1"  href="#system_usage_lesan_1"  role="tab" data-toggle="tab">Days from Last Issue</a></li>
      <li class=""><a id="sys_usage2"  href="#system_usage_lesan_2"  role="tab" data-toggle="tab">Days from Last Redistribution</a></li>
      <li class=""><a id="sys_usage3"  href="#system_usage_lesan_3" role="tab" data-toggle="tab">Days from Last Order</a></li>
      <li class=""><a id="sys_usage4"  href="#system_usage_lesan_4"  role="tab" data-toggle="tab">Days from Last Decommission</a></li>
      <li class=""><a id="sys_usage5" href="#system_usage_lesan_5" role="tab" data-toggle="tab">Days from Last Stock Addition</a></li>
</ul>


<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="system_usage_graph">
        <div style="" id="<?php echo $graph_id ?>"></div>

    </div>
    <div role="tabpanel" class="tab-pane" id="system_usage_lesan_1">
       
        Tab 2
    </div>
    <div role="tabpanel" class="tab-pane" id="system_usage_lesan_2">
        Tab 3
         
    </div>
    <div role="tabpanel" class="tab-pane" id="system_usage_lesan_3">
        Tab 4
    </div>
    <div role="tabpanel" class="tab-pane" id="system_usage_lesan_4">
        Tab 5
        
    </div>
    <div role="tabpanel" class="tab-pane" id="system_usage_lesan_5">
        Tab 6

        

        </div>
  </div>

<!--<script type="text/javascript">

		// $(document).ready(function() {
       			

    
  //       		$('#lastlogin').click(function(){
		// 		$(this).toggleClass('active');

		// 		$('#lastissue').click(function(){
		// 		$(this).toggleClass('active');


  //       });

</script>-->
<!--<div style="height: 100%;width: 100% ;margin: 0 auto;" id="dem_graph_"><?php $error; ?></div>-->

<script type="text/javascript">

    $(document).ready(function(){
       <?php echo $high_graph; ?>
       ajax_request_replace_div_content('reports/system_usage_lesan_0',"#system_usage_graph");

    
     $('#sys_usage0').click(function (e){
            ajax_request_replace_div_content('reports/system_usage_lesan_0',"#system_usage_graph");

        });

     $('#sys_usage1').click(function (e){
            ajax_request_replace_div_content('reports/system_usage_lesan_1',"#system_usage_lesan_1");

        });

     $('#sys_usage2').click(function (e){
            ajax_request_replace_div_content('reports/system_usage_lesan_2',"#system_usage_lesan_2");

        });

     $('#sys_usage3').click(function (e){
            ajax_request_replace_div_content('reports/system_usage_lesan_3',"#system_usage_lesan_3");

        });

     $('#sys_usage4').click(function (e){
            ajax_request_replace_div_content('reports/system_usage_lesan_4',"#system_usage_lesan_4");

        });

     $('#sys_usage5').click(function (e){
            ajax_request_replace_div_content('reports/system_usage_lesan_5',"#system_usage_lesan_5");

        });



    
    });

    

</script>


