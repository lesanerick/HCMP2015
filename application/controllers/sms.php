<?php
/**
 * @author Collins
 *
 */
ob_start();
class sms extends MY_Controller {
	var $test_mode = true;
	function __construct() {
		parent::__construct();
		$this -> load -> helper(array('form', 'url', 'file', 'download'));
		$this -> load -> library(array('hcmp_functions', 'form_validation', 'email', 'mpdf/mpdf'));

	}

	/*
	 |--------------------------------------------------------------------------|
	 | SMS	SECTION																|
	 |--------------------------------------------------------------------------|
	 */
	//for testing puposes only
	 public function test_sms() {

	 	$phones = '254707463571';
	 	$message = 'test from system live server';
	 	$message = urlencode($message);
	 	$spam_sms = '254707463571';

	 	$phone_numbers = explode("+", $spam_sms);

	 	foreach ($phone_numbers as $key => $user_no) {
	 		file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
			//echo "Success sent to " . $user_no . '<br>';
	 	}

	 }

	 public function test_email() {
	 	$message = "Test email form the server";
	 	$subject = "Test Subject from the server";
	 	$email_address = "karsanrichard@gmail.com";

	 	$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 }

	//get dpp phone numbers
	 public function get_ddp_phone_numbers($district_id) {
	 	$data = Users::get_dpp_details($district_id);
	 	$phone = "";

	 	foreach ($data as $info) {
	 		$telephone = preg_replace('(^0+)', "254", $info -> telephone);
	 		$phone .= $telephone . '+';
	 	}
	 	return $phone;
	 }

	//get the phone numbers for the county team
	 public function get_cp_phone_numbers($county_id) {
	 	$data = Users::get_county_details($county_id);
	 	$phone = "";

	 	foreach ($data as $info) {
	 		$telephone = preg_replace('(^0+)', "254", $info -> telephone);
	 		$phone .= $telephone . '+';
	 	}
	 	return $phone;
	 }

	//get facility users phone numbers
	 public function get_facility_phone_numbers($facility_code) {

	 	$data = Users::get_user_info($facility_code);
	 	$phone = "";
	 	foreach ($data as $info) {

	 		$telephone = preg_replace('(^0+)', "254", $info -> telephone);

	 		$phone .= $telephone . '+';
	 	}

	 	return $phone;
	 }

	//for sending system usage sms
	 public function check_system_usage() {
		//get the counties using HCMP
	 	$counties = Facilities::get_counties_all_using_HCMP();

	 	foreach ($counties as $counties) :
			//pick the county nae and county ID accordingly
			//counts the number of facilities not using the system
	 		$count_county = 0;

	 	$county_id = $counties['county'];
	 	$county_name = $counties['county_name'];
	 	$district_total = array();
			//Get all the districts in that  particular county
	 	$districts = Facilities::get_all_using_HCMP($county_id);



	 	foreach ($districts as $districts) :
	 		$count_district = 0;
	 	$district_id = $districts['district'];
	 	$district_name = $districts['name'];

				//echo "<pre>";print_r($district_name);exit;
	 	$facilities = Facilities::getFacilities_for_email($district_id);

				//loop through all the facilities in the particular sub county
	 	foreach ($facilities as $facilities_) :
					//facility name
	 		$facility_name = $facilities_ -> facility_name;
	 	$facility_code = $facilities_ -> facility_code;

					//check the last time they logged in as a facility
	 	$system_usage = Log::check_system_usage($facility_code);

	 	$no_of_days = $system_usage[0]['Days_From'];
					//checks if the number of days is greater than five as that is the threshold
	 	if ($no_of_days >= 5) :
						//counts the number of facilities who haven't logged in for more than 5 days
	 		$count_district++;
						//get the phone numbers of the facility users
	 	$phone = $this -> get_facility_phone_numbers($facility_code);

	 	$message = "Dear $facility_name user,\n you have not logged in to HCMP for the past $no_of_days days. The last time you logged in was $no_of_days days ago.\n Kindly log in to health-cmp.or.ke to follow up on the issue.\n HCMP";
	 	$message = urlencode($message);
						//appends the phone numbers of the technical team
	 	$spam_sms = $phone;

	 	$phone_numbers = explode("+", $spam_sms);

	 	foreach ($phone_numbers as $key => $user_no) {
	 		file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
	 	}

	 	endif;

					//end for each for the facilites
	 	endforeach;
				//start for the sub county section
	 	(array_key_exists($district_name, $district_total)) ? : $district_total = array_merge($district_total, array($district_name => $count_district));

				//pick the user data
	 	$user_data = Users::get_scp_details($district_id);

				//loop through the each of the numbers of the users
	 	foreach ($user_data as $data) :
					//pick the name
	 		$name_sub_county = $data['fname'] . " " . $data['lname'];
					//message to be sent out to the sub county guys
	 	$message = "Dear $name_sub_county, $district_name Sub County Pharmacist,\n $count_district facilities in $district_name Sub County have not accessed HCMP for more than 5 days.\n Log in to health-cmp.or.ke to follow up on the issue.\n HCMP";
	 	$message = urlencode($message);

	 	$user_no = $data['telephone'];
	 	file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
	 	endforeach;

	 	$count_county += $count_district;

				//end for each for the districts
	 	endforeach;
			//start for the sub county section
			//first make the message

			//then pick the names and details of the people receiving the texts
	 	$user_data = Users::get_county_pharm_details($county_id);
			//echo "<pre>";print_r($user_data);exit;
			//loop through the each of the numbers of the users
	 	foreach ($user_data as $data) :
				//pick the name
	 		$name_county = $data['fname'] . " " . $data['lname'];
	 	$message = "Dear $name_county,\n $count_county facilities in $county_name County have not accessed HCMP for more than 5 days.\n";

	 	foreach ($district_total as $key => $total) {
	 		if ($total > 0) :
	 			$message .= " $key Sub County - $total facilities.\n";
	 		endif;

	 	}

	 	$message .= "Log in to health-cmp.or.ke to follow up on the issue.\n HCMP";
	 	$message = urlencode($message);

	 	$user_no = $data['telephone'];

	 	file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");

	 	endforeach;

	 	endforeach;
	 }
//for sending system usage sms only for sub county and facility users
    /**
     *
     */
    public function sub_county_sms() {
        //get the counties using HCMP
    	$counties = Facilities::get_counties_all_using_HCMP();

    	foreach ($counties as $counties) :
            //pick the county nae and county ID accordingly
            //counts the number of facilities not using the system
    		$count_county = 0;

    	$county_id = $counties['county'];
    	$county_name = $counties['county_name'];
    	$district_total = array();
            //Get all the districts in that  particular county
    	$districts = Facilities::get_all_using_HCMP($county_id);



    	foreach ($districts as $districts) :
    		$count_district = 0;
    	$facility_names = array();
    	$district_id = $districts['district'];
    	$district_name = $districts['name'];

                //echo "<pre>";print_r($district_name);exit;
    	$facilities = Facilities::getFacilities_for_email($district_id);

                //loop through all the facilities in the particular sub county
    	foreach ($facilities as $facilities_) :
                    //facility name
    		$facility_name = $facilities_ -> facility_name;
    	$facility_code = $facilities_ -> facility_code;

                    //check the last time they logged in as a facility
    	$system_usage = Log::check_system_usage($facility_code);

    	$no_of_days = $system_usage[0]['Days_From'];
                    //checks if the number of days is greater than five as that is the threshold
    	if ($no_of_days >= 5) :
                        //counts the number of facilities who haven't logged in for more than 5 days
    		$count_district++;

                        //create an array t hold the names of the facilities

                        //get the phone numbers of the facility users
    	$phone = $this -> get_facility_phone_numbers($facility_code);

    	$message = "Dear $facility_name user,\n you have not logged in to HCMP for the past $no_of_days days. The last time you logged in was $no_of_days days ago.\n Kindly log in to health-cmp.or.ke to follow up on the issue.\n HCMP";
    	$message = urlencode($message);
                        //appends the phone numbers of the technical team
    	$spam_sms = $phone;

    	$phone_numbers = explode("+", $spam_sms);
    	(array_key_exists($district_name, $facility_names)) ? : $facility_names = array_merge($facility_names, array($district_name => $facility_name));

    	foreach ($phone_numbers as $key => $user_no) {
    		file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
    	}

    	endif;
                    // echo "<pre>  ";print_r($facility_names);exit;
                    //(array_key_exists($district_name, $district_total)) ? : $district_total = array_merge($district_total, array($district_name => $count_district));

                    //end for each for the facilites
    	endforeach;
                //start for the sub county section
    	(array_key_exists($district_name, $district_total)) ? : $district_total = array_merge($district_total, array($district_name => $count_district));

                //pick the user data
    	$user_data = Users::get_scp_details($district_id);

                //loop through the each of the numbers of the users
    	foreach ($user_data as $data) :
                    //pick the name
    		$name_sub_county = $data['fname'] . " " . $data['lname'];
                    //message to be sent out to the sub county guys
    	$message = "Dear $name_sub_county, $district_name Sub County Pharmacist,\n $count_district facilities in $district_name Sub County have not accessed HCMP for more than 5 days.\n Log in to health-cmp.or.ke to follow up on the issue.\n HCMP";
    	$message = urlencode($message);

    	$user_no = $data['telephone'];
    	file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
    	endforeach;

    	$count_county += $count_district;

                //end for each for the sub counties
    	endforeach;


    	endforeach;
    }
	//for sending an sms when an order is approved
    public function send_order_approval_sms($facility_code, $status) {
        //get the facility_name
    	$name = Facilities::get_facility_name($facility_code);
    	$facility_name = $name[0]['facility_name'];

    	$message = ($status == 1) ? $facility_name . " order has been rejected. HCMP" : $facility_name . " order has been approved. HCMP";

    	$data = Users::getUsers($facility_code) -> toArray();
    	$phone = $this -> get_facility_phone_numbers($facility_code);
    	$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);

    	$spam_sms = '254728778002+254707463571' . $phone;

    	$phone_numbers = explode("+", $spam_sms);

    	foreach ($phone_numbers as $key => $user_no) {
    		file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
    	}

    }

	//Called when an order is rejected or approved bt the DPP
	//Accepts two parameters $facility code and status of the order
	/*public function send_order_approval_sms($facility_code, $status) {
	 //$facility_code = $this -> session -> userdata('facility_id');
	 $facility_name = Facilities::get_facility_name2($facility_code);
	 $facility_name = $facility_name['facility_name'];
	 $data = Users::getUsers($facility_code) -> toArray();

	 $message = ($status == 1) ? $facility_name . "'s order has been rejected. HCMP" : $facility_name . "'s order has been approved. HCMP";

	 $data = Users::getUsers($facility_code) -> toArray();
	 $phone = $this -> get_facility_phone_numbers($facility_code);
	 $phone .= $this -> get_ddp_phone_numbers($data[0]['district']);

	 $this -> send_sms(substr($phone, 0, -1), $message);

	}*/
	//For Orders
	//Called when an order is placed by a particular facility
	public function send_order_sms() {
		$facility_code = $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		$facility_name = $facility_name['facility_name'];
		$data = Users::getUsers($facility_code) -> toArray();
		foreach ($data as $users) {
			$user_type = $users['usertype_id'];

			switch ($user_type) {
				//Facility Admin
				case 2 :
				$message = "An  order has been placed by " . $facility_name;
				break;
				//Sub County Head
				case 3 :
				$message = $facility_name . " has placed an order";
				break;
				//County Head
				case 10 :
				$message = "An  order has been placed by " . $facility_name;
				break;
				//For test purposes only
				case 5 :
				$message = "Test order sms. Placed by " . $facility_name;
				break;
			}

			$phone = $this -> get_facility_phone_numbers($facility_code);
			$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
			//$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
			$spam_sms = '254728778002+254707463571' . $phone;

			$phone_numbers = explode("+", $spam_sms);

			foreach ($phone_numbers as $key => $user_no) {
				file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
			}
		}

	}

	//sends an sms when a redistribution is done by a facility
	public function send_stock_donate_sms() {

		$facility_name = $this -> session -> userdata('full_name');
		$facility_code = $this -> session -> userdata('facility_id');
		$data = Users::getUsers($facility_code) -> toArray();

		$phone = $this -> get_facility_phone_numbers($facility_code);
		$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
		$message = $facility_name . " have been donated commodities. HCMP";

		$this -> send_sms(substr($phone, 0, -1), $message);

	}

	/*
	public function send_stock_donate_sms($facility_code, $facility_code2) {
	 //Facility code the mfl code for the facility donating drugs
	 //Facility code 2 is for the facility recieving the commodities
	 $facility_name = Facilities::get_facility_name2($facility_code);
	 $facility_name = $facility_name['facility_name'];
	 $facility_name2 = Facilities::get_facility_name2($facility_code2);
	 $facility_name2 = $facility_name2['facility_name'];
	 $data = Users::getUsers($facility_code) -> toArray();
	 $data2 = Users::getUsers($facility_code) -> toArray();

	 $message = $facility_name . " has donated commodirties to " . $facility_name2;

	 $phone = $this -> get_facility_phone_numbers($facility_code);
	 $phone .= $this -> get_facility_phone_numbers($facility_code2);
	 $phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
	 $phone .= $this -> get_ddp_phone_numbers($data2[0]['district']);
	 $spam_sms = '254728778002+254707463571+' . $phone;

	 $phone_numbers = explode("+", $spam_sms);

	 foreach ($phone_numbers as $key => $user_no)
	 {
	 file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
	 }

	 //$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);

	 }
	 */

	 //sends a text when a stock update is done by a facility

	/* public function send_stock_update_sms() {

	 public function send_stock_update_sms_nullified() {

	 	$facility_name = $this -> session -> userdata('full_name');
	 	$facility_code = $this -> session -> userdata('facility_id'); ;
	 	$data = Users::getUsers($facility_code) -> toArray();

	 	$message = "Stock level for " . $facility_name . " have been updated. HCMP";

	 	$phone = $this -> get_facility_phone_numbers($facility_code);
	 	$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);

	 	$spam_sms = '254728778002+254707463571' . $phone;

	 	$phone_numbers = explode("+", $spam_sms);

	 	foreach ($phone_numbers as $key => $user_no) {
	 		file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
	 	}
*/
	 //$this -> send_sms(substr($phone, 0, -1), $message);


	 //Test for sending emails
	 public function send_email_test() {
	 	$this -> hcmp_functions -> send_email();
	 }
	 public function configure_db(){
	 	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `facility_user_log` AS select distinct `u`.`facility` AS `facility_code`,`l`.`user_id` AS `user_id`,`l`.`start_time_of_event` AS `start_time_of_event`,`l`.`end_time_of_event` AS `end_time_of_event`,`l`.`action` AS `action`,`l`.`issued` AS `issued`,`l`.`ordered` AS `ordered`,`l`.`decommissioned` AS `decommissioned`,`l`.`redistribute` AS `redistribute`,`l`.`add_stock` AS `add_stock` from (`user` `u` join `log` `l`) where ((`u`.`id` = `l`.`user_id`) and (`u`.`usertype_id` = '5'));
";
		$result = $this->db->query($sql);
		echo "$result";
	 }
	 //Checks if there are potential expiries in the system
	 public function check_potential_expiries() {
	 	$year = date("Y");
	 	$potential_expiries = Facility_stocks::get_potential_expiries_sms();
	 	$total_potential = count($potential_expiries);
	 	echo "$total_potential";die;
	 	if ($total_potential > 0) {
	 		$total_facilitites = count($potential_expiries);

	 		$total_commodities = "";

	 		foreach ($potential_expiries as $potential) {
	 			$facility_code = $potential['facility_code'];
	 			$data = Users::getUsers($facility_code) -> toArray();
				//Filter the array to remove empty sections of the array
	 			$data = array_filter($data);
	 			if (!empty($data)) {
	 				foreach ($data as $users) {
	 					$user_type = $users['usertype_id'];

	 					switch ($user_type) {
							//Facility Admin
	 						case 2 :
	 						$message = $potential['total'] . " commodities will expire within 3 months. HCMP";
	 						break;
							//Sub County Head
	 						case 3 :
	 						$message = $total_facilitites . " facilities have commodities that will expire within 3 months. HCMP";
	 						break;
							//County Head
	 						case 10 :
	 						$message = "There are " . $total_potential . " potential expiries in the county. HCMP";
	 						break;
							//For test purposes only
	 						case 5 :
	 						$message = "Test SMS. HCMP";
	 						break;
	 					}

	 					$phone = $this -> get_facility_phone_numbers($facility_code);
	 					$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
	 					$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
	 				}
	 			} else {
				 //DO Nothing
				 //Array is empty
	 			}

	 		}

	 	} else {
	 		//No Potential Expiries
	 	}

	 }

	 //Stock out SMSes
	 //will be called once a week by the system automatically
	public function stock_out_sms() {
	 	$stock_outs = Facility_stocks::get_stock_outs_sms();
	 	$total_stock_outs = count($stock_outs);
	 	if ($total_stock_outs > 0) {
	 		$total_facilitites = count($stock_outs);
	 		$total_commodities = "";

	 		foreach ($stock_outs as $stock_outs_) {
	 			$facility_code = $stock_outs_['facility_code'];
	 			$data = Users::getUsers($facility_code) -> toArray();
	 //Filter the array to remove empty sections of the array
	 			$data = array_filter($data);
	 			if (!empty($data)) {
	 				$total_subcounties = count($data['']);
	 				foreach ($data as $users) {
	 					$user_type = $users['usertype_id'];

	 					switch ($user_type) {
	 //Facility Admin
	 						case 2 :
	 						$message = $stock_outs_['total'] . " commodities are out of stock.";
	 						break;
	 //Sub County Head
	 						case 3 :
	 						$message = $total_facilitites . " facilities have a total number of " . $total_stock_outs . " stock outs.";
	 						break;
	 //County Head
	 						case 10 :
	 						$message = "There are " . $total_stock_outs . " stock outs in the county";
	 						break;
	 //For test purposes only
	 						case 5 :
	 						$message = "To Me";
	 						break;
	 					}

	 					$phone = $this -> get_facility_phone_numbers($facility_code);
	 					$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
	 					$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
	 				}
	 			} else {
	 //DO Nothing
	 //Array is empty
	 			}

	 		}

	 	} else {
	 //No Stock Outs

	 	}
	 }

	 //When stock is updated by a particular facility
	public function send_stock_update_sms() {
	 	$facility_code = $this -> session -> userdata('facility_id');
	 	$facility_name = Facilities::get_facility_name2($facility_code);
	 	$facility_name = $facility_name['facility_name'];
	 	$data = Users::getUsers($facility_code) -> toArray();

	 	foreach ($data as $users) {
	 		$user_type = $users['usertype_id'];

	 		switch ($user_type) {
	 //Facility Admin
	 			case 2 :
	 			$message = "Stock has been updated for " . $facility_name;
	 			break;
	 //Sub County Head
	 			case 3 :
	 			$message = $facility_name . " has updated its stock";
	 			break;
	 //County Head
	 			case 10 :
	 			$message = "Stock has been updated for " . $facility_name;
	 			break;
	 //For test purposes only
	 			case 5 :
	 			$message = "Stock has been updated for " . $facility_name;
	 			break;
	 		}

	 		$phone = $this -> get_facility_phone_numbers($facility_code);
	 		$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
	 		$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
	 	}

	 }

	 public function order_update_sms($facility_code, $status) {
	 //$facility_code = $this -> session -> userdata('facility_id');
	 	$facility_name = Facilities::get_facility_name2($facility_code);
	 	$facility_name = $facility_name['facility_name'];
	 	$data = Users::getUsers($facility_code) -> toArray();
	 	$message = $facility_name . " has updated its order";

	 	$phone = $this -> get_facility_phone_numbers($facility_code);
	 	$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);

	 	$spam_sms = '254707463571+254726534272+254725227833+' . $phones;
	 	$phone_numbers = explode("+", $spam_sms);

	 	foreach ($phone_numbers as $key => $user_no) {
	 		file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
	 //echo "Success sent to ".$user_no.'<br>';
	 	}

	 	$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);

	 }

	 /*public function update_stock_out_table()
	 {

	 $in = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
	 select stock_date,facility_code,
	 kemsa_code from facility_stock
	 where `balance`=0 and `status`=1 and quantity>0
	 group by kemsa_code,facility_code");

	 $jay = count($in);

	 for($i=0;$i<$jay;$i++)
	 {
	 Doctrine_Manager::getInstance()->getCurrentConnection()->execute("
	 insert into facility_stock_out_tracker
	 set facility_id='".$in[$i]['facility_code']."', drug_id='".$in[$i]['kemsa_code']."',
	 `start_date`='".$in[$i]['stock_date']."', status='stocked out' ");

	 }
	 }
	 */

	/*
	 |--------------------------------------------------------------------------|
	 | EMAIL REPORTS SECTION															|
	 |--------------------------------------------------------------------------|
	 */

	//gets the emails of the respective DPPs in charge
	 public function get_ddp_email($district_id) {
	 	$data = Users::get_dpp_emails($district_id);
	 	$user_email = "";
	 	foreach ($data as $info) {

	 		$user_email .= $info -> email . ',';

	 	}
	 	return $user_email;
	 }

	 public function get_ddp_email_county($county_id) {
	 	$data = Users::get_dpp_emails_county_level($county_id);
	 	$user_email = "";
	 	foreach ($data as $info) {

	 		$user_email .= $info -> email . ',';

	 	}
	 	return $user_email;
	 }

	 public function get_county_email($county_id) {
	 	$data = Users::get_county_emails($county_id);
	 	$user_email = "";
	 	foreach ($data as $info) {
	 		$user_email .= $info -> email . ',';

	 	}
	 	return $user_email;
	 }

	//Getting the bcc emails
	 public function get_bcc_notifications() {
	 	$bcc_emails = "karsanrichard@gmail.com,smutheu@clintonhealthaccess.org,
	 	jhungu@clintonhealthaccess.org,gmacharia@clintonhealthacces.org,tngugi@clintonhealthaccess.org,
	 	bwariari@clintonhealthaccess.org,amwaura@clintonhealthaccess.org,eongute@clintonhealthaccess.org,
	 	rkihoto@clintonhealthaccess.org,teddyodera@gmail.com,ericmurugami@yahoo.co.uk,
	 	raykiprono@gmail.com,margie.dora@gmail.com,amostum5@gmail.com,muirurisk@gmail.com,
	 	valwacu@gmail.com,odiwuorybrian@gmail.com,mwakiojoy@gmail.com,emgitar@yahoo.com,ronohb@gmail.com,
	 	kevgithuka@gmail.com,kiganyastephenthua@gmail.com,";

	 	return $bcc_emails;
	 }

	//gets the emails of users in the facility
	 public function get_facility_email($facility_code) {
	 	$data = Users::get_user_emails($facility_code);
	 	$user_email = "";
	 	foreach ($data as $info) {

	 		$user_email .= $info -> email . ',';

	 	}
	 	return $user_email;
	 }

	 public function send_stock_decommission_email($message, $subject, $attach_file) {
	 	$facility_code = $this -> session -> userdata('facility_id');
	 	$data = Users::getUsers($facility_code) -> toArray();
	 	$email_address = $this -> get_facility_email($facility_code);
	 	$email_address .= $this -> get_ddp_email($data[0]['district']);

	 	$this -> send_email(substr($email_address, 0, -1), $message, $subject, $attach_file);

	 }

	/*
	 |--------------------------------------------------------------------------|
	 | EMAIL DAILY/WEEKLY/MONTHLY REPORTS SECTION															|
	 |--------------------------------------------------------------------------|
	 */
	 public function facility_overview_report() {
		//get all the facilities rolled out
	 	$facilities = Facilities::get_all_facilities_on_HCMP();
		//Start buliding the excel file
	 	$excel_data = array();
	 	$excel_data = array('doc_creator' => 'HCMP', 'doc_title' => 'facility overview monthly report ', 'file_name' => 'facility overview monthly report');
	 	$row_data = array();
		//contains the columns in the excel sheet
	 	$column_data = array("County", "Sub County", "MFL", "Facility Name", "Level", "Type", "Owner", "Date of Activation");
	 	$excel_data['column_data'] = $column_data;
		//Loop through the data picked form the database and assign each row to the $row_data variable
		//$row_data is used to dump the data into rows for the excel
	 	foreach ($facilities as $facilities) :
	 		array_push($row_data, array($facilities["county"], $facilities["sub_county"], $facilities["facility_code"], $facilities["facility_name"], $facilities["level"], $facilities["type"], $facilities["owner"], $facilities["date_of_activation"]));
	 	endforeach;

	 	$excel_data['row_data'] = $row_data;
	 	$excel_data['report_type'] = "download_file";
	 	$excel_data['file_name'] = "Facility Overview Report";

	 	$excel_data['excel_title'] = "Facilities Rolled on HCMP as at" . date("Js F Y");

	 	$subject = "Facility Overview Report";

	 	$message = "Good Morning,</br>
	 	<p>Find attached an excel sheet with the breakdown of facilities using HCMP
	 		You may log onto health-cmp.or.ke to confirm.</p>
	 		<p>----</p>
	 		<p>HCMP</p>
	 		<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";
		//used the same report type as for ORS
		//they have the same number of columns
	 		$report_type = "ors_report";
		//create the excel sheet here
	 		$this -> create_excel($excel_data, $report_type);
	 		exit ;
		//where the excel sheet is stored before being attached
	 		$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

		//the email of the receipients
		//$email_address = "karsanrichard@gmail.com";
		//function for sending the actual email
		//$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 	}

	 	public function expiries_report() {
		//Set the current year
	 		$year = date("Y");
	 		$picurl = base_url() . 'assets/img/coat_of_arms-resized1.png';
		//get the facilities in the district
	 		$counties = Facilities::get_counties_all_using_HCMP();

	 		foreach ($counties as $counties) {
			//holds the dat for the entire county
			//once it is done executing for one county it is reset to zero
	 			$county_total = array();
			//Used to store the total cost of all expiries
	 			$county_expiries_total = 0;
			//pick the county nae and county ID accordingly
	 			$county_id = $counties['county'];
	 			$county_name = $counties['county_name'];

			//Get all the ddistricts in that  particular county
	 			$districts = Facilities::get_all_using_HCMP($county_id);
			//holds the data for all the districts in a particular county
	 			$district_total = array();

	 			foreach ($districts as $districts) {

				//Used to store the total cost of all expiries
	 				$sub_county_expiries_total = 0;

	 				$district_id = $districts['district'];
	 				$district_name = $districts['name'];
				//get all facilities in that district
	 				$facilities = Facilities::getFacilities_for_email($district_id);
				//holds all the data for all facilities in a particular district
	 				$facility_total = array();

	 				foreach ($facilities as $facilities_) :
					//holds the total value of expiries for that particular facility in that district
	 					$facility_potential_expiries_total = 0;
					//$facility_potential_expiries = array();
	 				$facility_code = $facilities_ -> facility_code;
	 				$facility_name = Facilities::get_facility_name2($facility_code);
	 				$facility_name = $facility_name['facility_name'];

					//get potential expiries in that particular facility
	 				$facility_expiries = Facility_stocks::All_expiries_email($facility_code);

					//push the result into another array that will be used by the distrct
	 				(array_key_exists($facility_name, $facility_total)) ? $facility_total[$facility_name] = array_merge($facility_total[$facility_name], array($facility_expiries)) : $facility_total = array_merge($facility_total, array($facility_name => array($facility_expiries)));
					//Start buliding the excel file
	 				$excel_data = array();
	 				$excel_data = array('doc_creator' => $facility_name, 'doc_title' => 'facility expiries monthly report ', 'file_name' => 'facility weekly report');
	 				$row_data = array();

	 				$column_data = array("Facility Name", "MFL Code", "Sub County", "County","Commodity Name", "Unit Size", "Quantity (packs)", "Quantity (units)", "Unit Cost(KSH)", "Total Expired(KSH)", "Date of Expiry", "Supplier", "Date Added", "Manufacturer");

	 				$excel_data['column_data'] = $column_data;

	 				foreach ($facility_expiries as $facility_expiries) :
	 					array_push($row_data, array($facility_expiries["facility_name"], $facility_expiries["facility_code"], $facility_expiries["subcounty"], $facility_expiries["county"],$facility_expiries["commodity_name"], $facility_expiries["unit_size"], $facility_expiries["packs"], $facility_expiries["units"], $facility_expiries["unit_cost"], $facility_expiries["total"], $facility_expiries["expiry_date"], $facility_expiries["source_name"], $facility_expiries["date_added"], $facility_expiries["manufacture"]));
	 				$facility_potential_expiries_total = $facility_potential_expiries_total + $facility_expiries["total"];
	 				endforeach;

	 				if (empty($row_data)) {
						//do nothing
	 				} else {
	 					$excel_data['row_data'] = $row_data;
	 					$excel_data['report_type'] = "download_file";
	 					$excel_data['file_name'] = $facility_name . "_Expiries_Report";
	 					$excel_data['excel_title'] = "Expiries Report for " . $facility_name . " for the month of " . date("F Y");

	 					$subject = "Expiries: " . $facility_name;

	 					$message = "Dear " . $facility_name . " facility,</br>
	 					<p>Find attached an excel sheet with the " . $facility_name . " breakdown of Expiries.
	 						You may log onto health-cmp.or.ke to decommission them.</p>
	 						<p>----</p>
	 						<p>HCMP</p>
	 						<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";
						//create the excel here
	 						$report_type = "expiries";
						//holds the total figure for the expiries
	 						$total_figure = $facility_potential_expiries_total;
						//excel is created by php excel here
	 						$this -> create_excel($excel_data, $report_type, $total_figure);
						//where the excel sheet is stored before being attached
	 						$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

						//the email of the receipients
	 						$email_address = $this -> get_facility_email($facility_code);
						//function for sending the actual email
	 						$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 					}

					//End foreach for facility
	 					endforeach;

	 					(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = array_merge($district_total[$district_name], array($facility_total)) : $district_total = array_merge($district_total, array($district_name => array($facility_total)));
				//Building the excel sheet to be sent to the district admin
	 					$excel_data = array();
	 					$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district expiries weekly report ', 'file_name' => 'district weekly report');
	 					$row_data = array();
	 					$column_data = array("Facility Name", "MFL Code", "Sub County", "County","Commodity Name", "Unit Size", "Quantity (packs)", "Quantity (units)", "Unit Cost(KSH)", "Total Expired(KSH)", "Date of Expiry", "Supplier", "Date Added", "Manufacturer");
	 					$excel_data['column_data'] = $column_data;

	 					foreach ($facility_total as $facility_total_1) :
	 						foreach ($facility_total_1 as $facility_total_2) :
	 							foreach ($facility_total_2 as $facility_total1) :
	 								array_push($row_data, array($facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["subcounty"], $facility_total1["county"],$facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["packs"], $facility_total1["units"], $facility_total1["unit_cost"], $facility_total1["total"], $facility_total1["expiry_date"], $facility_total1["source_name"], $facility_total1["date_added"], $facility_total1["manufacture"]));
	 							$sub_county_expiries_total = $sub_county_expiries_total + $facility_total1["total"];
	 							endforeach;
	 							endforeach;
	 							endforeach;

	 							if (empty($row_data)) {
					//do nothing
	 							} else {

	 								$excel_data['row_data'] = $row_data;
	 								$excel_data['report_type'] = "download_file";
	 								$excel_data['file_name'] = $district_name . "_Weekly_District_Expiries_Report";
	 								$excel_data['excel_title'] = "Expiries Report for " . $district_name . " Sub County for the month of " . date("F Y");

					//tyoe of report created
	 								$report_type = "expiries";
					//holds the total cost
	 								$total_figure = $sub_county_expiries_total;
					//creates the excel sheet
	 								$this -> create_excel($excel_data, $report_type, $total_figure);
					//path of the excel sheet
	 								$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
					//creating the email subject and message body
	 								$subject = "Expiries: " . $district_name . " Sub County (Next 3 Months)";
	 								$message = "Dear " . $district_name . " Sub County,
	 								<p>Find attached an excel sheet with the " . $district_name . " Sub County breakdown of Expiries.
	 									You may log onto health-cmp.or.ke to decommission them.</p>
	 									<p>----</p>
	 									<p>HCMP</p>
	 									<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

					//email address to receive
	 									$email_address = $this -> get_ddp_email($district_id);
	 									$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
	 								}

	 							}

			//Building the excel sheet to be sent to the district admin
	 							$excel_data = array();
	 							$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'county expiries weekly report ', 'file_name' => 'district report');
	 							$row_data = array();
	 							$column_data = array("Facility Name", "MFL Code", "Sub County", "County","Commodity Name", "Unit Size", "Quantity (packs)", "Quantity (units)", "Unit Cost(KSH)", "Total Expired(KSH)", "Date of Expiry", "Supplier", "Date Added", "Manufacturer");
	 							$excel_data['column_data'] = $column_data;

	 							foreach ($district_total as $facility_total_1) :
	 								foreach ($facility_total_1 as $facility_total_2) :
	 									foreach ($facility_total_2 as $facility_total_3) :
	 										foreach ($facility_total_3 as $facility_total_4) :
	 											foreach ($facility_total_4 as $facility_total1) :
	 												array_push($row_data, array($facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["subcounty"], $facility_total1["county"],$facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["packs"], $facility_total1["units"], $facility_total1["unit_cost"], $facility_total1["total"], $facility_total1["expiry_date"], $facility_total1["source_name"], $facility_total1["date_added"], $facility_total1["manufacture"]));
	 											$county_expiries_total = $county_expiries_total + $facility_total1["total"];
	 											endforeach;
	 											endforeach;
	 											endforeach;
	 											endforeach;
	 											endforeach;

	 											if (empty($row_data)) {
				//do nothing
	 											} else {
	 												$excel_data['row_data'] = $row_data;
	 												$excel_data['report_type'] = "download_file";
	 												$excel_data['file_name'] = $county_name . "_Weekly_County_Expiries_Report";
	 												$excel_data['excel_title'] = "Expiries Report for " . $county_name . " County for the month of " . date("F Y");

				//create the excel here
	 												$report_type = "expiries";
	 												$total_figure = $county_expiries_total;
	 												$this -> create_excel($excel_data, $report_type, $total_figure);
				//Where the excel sheet will be saved before being attached
	 												$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

				//Start creating the email Subject and message body here
	 												$subject = "Expiries: " . $county_name . " County (Next 3 Months)";
	 												$message = "Dear " . $county_name . " County,
	 												<p>Find attached an excel sheet with the " . $county_name . " County's breakdown of Expiries.
	 													You may log onto health-cmp.or.ke to decommission them.</p>
	 													<p>----</p>
	 													<p>HCMP</p>
	 													<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 													$email_address = $this -> get_ddp_email_county($county_id);
	 													$bcc = $this -> get_bcc_notifications();
	 													$cc = $this -> get_county_email($county_id);

	 													$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc, $cc);
	 												}

	 											}

	 										}

	 										public function weekly_potential_expiries_report() {
		//Set the current year
	 											$year = date("Y");
	 											$picurl = base_url() . 'assets/img/coat_of_arms-resized1.png';
		//get the facilities in the district
		//$counties = Facilities::get_Taita();
	 											$counties = Facilities::get_counties_all_using_HCMP();

	 											foreach ($counties as $counties) {
			//holds the data for the entire county
			//once it is done executing for one county it is reset to zero
	 												$county_total = array();
			//pick the county name and county ID accordingly
	 												$county_id = $counties['county'];
	 												$county_name = $counties['county_name'];

			//Get all the districts in that  particular county
	 												$districts = Facilities::get_all_using_HCMP($county_id);
			//holds the data for all the districts in a particular county
			//echo "<pre>";print_r($districts);exit;
	 												$district_total = array();

	 												foreach ($districts as $districts) {

	 													$district_id = $districts['district'];
	 													$district_name = $districts['name'];
				//get all facilities in that district
	 													$facilities = Facilities::getFacilities_for_email($district_id);
				//holds all the data for all facilities in a particular district

	 													$facility_total = array();

	 													foreach ($facilities as $facilities_) :

					//holds the total value of expiries for that particular facility in that district
	 														$facility_potential_expiries_total = 0;
					//$facility_potential_expiries = array();
	 													$facility_code = $facilities_ -> facility_code;
	 													$facility_name = Facilities::get_facility_name2($facility_code);
	 													$facility_name = $facility_name['facility_name'];

					//get potential expiries in that particular facility
	 													$facility_potential_expiries = Facility_stocks::potential_expiries_email($facility_code);
					//echo "<pre>";print_r($facility_potential_expiries);exit;
					//push the result into another array that will be used by the distrct
	 													(array_key_exists($facility_name, $facility_total)) ? $facility_total[$facility_name] = array_merge($facility_total[$facility_name], array($facility_potential_expiries)) : $facility_total = array_merge($facility_total, array($facility_name => array($facility_potential_expiries)));
					//Start buliding the excel file
	 													$excel_data = array();
	 													$excel_data = array('doc_creator' => $facility_name, 'doc_title' => 'facility potential expiries weekly report ', 'file_name' => 'facility weekly report');
	 													$row_data = array();
	 													$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Date of Expiry", "Supplier", "Date Added", "Manufacturer", "Facility Name", "MFL Code", "Sub County", "County");
	 													$excel_data['column_data'] = $column_data;

	 													foreach ($facility_potential_expiries as $facility_potential_expiries) :
	 														array_push($row_data, array($facility_potential_expiries["commodity_name"], $facility_potential_expiries["unit_size"], $facility_potential_expiries["units"], $facility_potential_expiries["packs"], $facility_potential_expiries["unit_cost"], $facility_potential_expiries["total_ksh"], $facility_potential_expiries["expiry_date"], $facility_potential_expiries["source_name"], $facility_potential_expiries["date_added"], $facility_potential_expiries["manufacture"], $facility_potential_expiries["facility_name"], $facility_potential_expiries["facility_code"], $facility_potential_expiries["subcounty"], $facility_potential_expiries["county"]));
	 													$facility_potential_expiries_total += $facility_potential_expiries["total_ksh"];
	 													endforeach;

	 													if (empty($row_data)) {
						//do nothing
	 													} else {
	 														$excel_data['row_data'] = $row_data;
	 														$excel_data['report_type'] = "download_file";
	 														$excel_data['file_name'] = $facility_name . "_Potential_Expiries_Report";
	 														$excel_data['excel_title'] = "Potential Expiries Report for " . $facility_name . " as at " . date("jS F Y");

	 														$subject = "Potential Expiries: " . $facility_name . " (Next 3 Months)";

	 														$message = "Dear " . $facility_name . ",
	 														<p>Find attached an excel sheet with the " . $facility_name . " breakdown of the Potential Expiries.
	 															Kindly sensitize the need to re-distribute these short expiry commodities.</p>
	 															<p>You may log onto health-cmp.or.ke for follow up.</p>

	 															<p>----</p>

	 															<p>HCMP</p>

	 															<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 															$report_type = "potential_expiries";
	 															$this -> create_excel($excel_data, $report_type);

	 															$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

	 															$email_address = $this -> get_facility_email($facility_code);

	 															$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 														}

					//End foreach for facility
	 														endforeach;

	 														(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = array_merge($district_total[$district_name], array($facility_total)) : $district_total = array_merge($district_total, array($district_name => array($facility_total)));
				//Building the excel sheet to be sent to the district admin
	 														$excel_data = array();
	 														$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district potential expiries weekly report ', 'file_name' => 'district weekly report');
	 														$row_data = array();
	 														$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Date of Expiry", "Supplier", "Date Added", "Manufacturer", "Facility Name", "MFL Code", "Sub County", "County");
	 														$excel_data['column_data'] = $column_data;

	 														foreach ($facility_total as $facility_total_1) :
	 															foreach ($facility_total_1 as $facility_total_2) :
	 																foreach ($facility_total_2 as $facility_total1) :
	 																	array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["units"], $facility_total1["packs"], $facility_total1["unit_cost"], $facility_total1["total_ksh"], $facility_total1["expiry_date"], $facility_total1["source_name"], $facility_total1["date_added"], $facility_total1["manufacture"], $facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["subcounty"], $facility_total1["county"]));

	 																endforeach;
	 																endforeach;
	 																endforeach;

	 																if (empty($row_data)) {
					//do nothing
	 																} else {

	 																	$excel_data['row_data'] = $row_data;
	 																	$excel_data['report_type'] = "download_file";
	 																	$excel_data['file_name'] = $district_name . "_Weekly_Sub_County_Potential_Expiries_Report";
	 																	$excel_data['excel_title'] = "Potential Expiries Report for " . $district_name . " Sub County as at " . date("jS F Y");

					//Create the excel file here
	 																	$report_type = "potential_expiries";
	 																	$this -> create_excel($excel_data, $report_type);

	 																	$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																	$subject = "Potential Expiries: " . $district_name . " Sub County (Next 3 Months)";

	 																	$message = "<p>Dear " . $district_name . " Sub County,</p>
	 																	<p>Find attached an excel sheet with the " . $district_name . " Sub County's breakdown of Potential Expiries.
	 																		Kindly sensitize the need to re-distribute these short expiry commodities.</p>
	 																		<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																		<p>----</p>

	 																		<p>HCMP</p>

	 																		<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																		$email_address = $this -> get_ddp_email($district_id);
	 																		$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 																	}

	 																}

			//Building the excel sheet to be sent to the district admin
	 																$excel_data = array();
	 																$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district potential expiries weekly report ', 'file_name' => 'district weekly report');
	 																$row_data = array();
	 																$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Date of Expiry", "Supplier", "Date Added", "Manufacturer", "Facility Name", "MFL Code", "Sub County", "County");
	 																$excel_data['column_data'] = $column_data;

	 																foreach ($district_total as $facility_total_1) :
	 																	foreach ($facility_total_1 as $facility_total_2) :
	 																		foreach ($facility_total_2 as $facility_total_3) :
	 																			foreach ($facility_total_3 as $facility_total_4) :
	 																				foreach ($facility_total_4 as $facility_total1) :
	 																					array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["units"], $facility_total1["packs"], $facility_total1["unit_cost"], $facility_total1["total_ksh"], $facility_total1["expiry_date"], $facility_total1["source_name"], $facility_total1["date_added"], $facility_total1["manufacture"], $facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["subcounty"], $facility_total1["county"]));

	 																				endforeach;
	 																				endforeach;
	 																				endforeach;
	 																				endforeach;
	 																				endforeach;

	 																				if (empty($row_data)) {
				//do nothing
	 																				} else {
	 																					$excel_data['row_data'] = $row_data;
	 																					$excel_data['report_type'] = "download_file";
	 																					$excel_data['file_name'] = $county_name . "_Weekly_County_Potential_Expiries_Report";
	 																					$excel_data['excel_title'] = "Potential Expiries Report for " . $county_name . " County as at " . date("jS F Y");

				//create the excel file
	 																					$report_type = "potential_expiries";
	 																					$this -> create_excel($excel_data, $report_type);

	 																					$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																					$subject = "Potential Expiries: " . $county_name . " County (Next 3 Months)";

	 																					$message = "<p>Dear " . $county_name . " County,</p>
	 																					<p>Find attached an excel sheet with the " . $county_name . " County's breakdown of Potential Expiries.
	 																						Kindly sensitize the need to re-distribute these short expiry commodities.</p>
	 																						<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																						<p>----</p>

	 																						<p>HCMP</p>

	 																						<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																						$email_address = $this -> get_ddp_email_county($county_id);

	 																						$bcc = $this -> get_bcc_notifications();
	 																						$cc = $this -> get_county_email($county_id);

	 																						$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc, $cc);
	 																					}

	 																				}

	 																			}

	 																			public function weekly_stockouts_report() {
		//Set the current year
	 																				$year = date("Y");
		//get the facilities in the district
		//$counties = Facilities::get_Taita();
		//$counties = Facilities::get_counties_all_using_HCMP();
	 																				$counties = Facilities::get_Taita();
	 																				foreach ($counties as $counties) {
			//holds the dat for the entire county
			//once it is done executing for one county it is reset to zero
	 																					$county_total = array();
			//pick the county nae and county ID accordingly
	 																					$county_id = $counties['county'];
	 																					$county_name = $counties['county_name'];

			//Get all the ddistricts in that  particular county
	 																					$districts = Facilities::get_all_using_HCMP($county_id);
			//echo "<pre>";print_r($districts);exit;

			//holds the data for all the districts in a particular county
	 																					$district_total = array();

	 																					foreach ($districts as $districts) {

	 																						$district_id = $districts['district'];
	 																						$district_name = $districts['name'];
				//get all facilities in that district
	 																						$facilities = Facilities::getFacilities_for_email($district_id);
				//holds all the data for all facilities in a particular district
	 																						$facility_total = array();

	 																						foreach ($facilities as $facilities_) :
					//holds the total value of expiries for that particular facility in that district
	 																							$facility_potential_expiries_total = 0;
					//$facility_potential_expiries = array();
	 																						$facility_code = $facilities_ -> facility_code;
	 																						$facility_name = Facilities::get_facility_name2($facility_code);
	 																						$facility_name = $facility_name['facility_name'];

	 																						$facility_potential_expiries = Facility_stocks::get_stock_outs_for_email($facility_code);

					//get potential expiries in that particular facility
					//push the result into another array that will be used by the distrct
	 																						(array_key_exists($facility_name, $facility_total)) ? $facility_total[$facility_name] = array_merge($facility_total[$facility_name], array($facility_potential_expiries)) : $facility_total = array_merge($facility_total, array($facility_name => array($facility_potential_expiries)));
					//Start building the excel file
	 																						$excel_data = array();
	 																						$excel_data = array('doc_creator' => $facility_name, 'doc_title' => 'facility stokouts weekly report ', 'file_name' => 'facility weekly report');
	 																						$row_data = array();
	 																						$column_data = array("Commodity Name", "Unit Size", "Unit Cost(KSH)", "Quantity Available (Units)", "Quantity Available (Packs)", "Supplier", "Manufacturer", "Facility Name", "Subcounty", "County");
	 																						$excel_data['column_data'] = $column_data;

	 																						foreach ($facility_potential_expiries as $facility_potential_expiries) :
	 																							array_push($row_data, array($facility_potential_expiries["commodity_name"], $facility_potential_expiries["unit_size"], $facility_potential_expiries["unit_cost"], $facility_potential_expiries["current_balance"], $facility_potential_expiries["current_balance_packs"], $facility_potential_expiries["source_name"], $facility_potential_expiries["manufacture"], $facility_potential_expiries["facility_name"], $facility_potential_expiries["district"], $facility_potential_expiries["county"]));
						//$facility_potential_expiries_total = $facility_potential_expiries_total + $facility_potential_expiries[""]
	 																						endforeach;
	 																						if (empty($row_data)) {
						//do nothing
	 																						} else {

	 																							$excel_data['row_data'] = $row_data;
	 																							$excel_data['report_type'] = "download_file";
	 																							$excel_data['file_name'] = $facility_name . "_Stock_Outs_Report";
	 																							$excel_data['excel_title'] = "Stock Outs Report for " . $facility_name . " as at " . date("jS F Y");

	 																							$message = "<p>Dear " . $facility_name . ",</p>
	 																							<p>Find attached an excel sheet with the " . $facility_name . " breakdown of commodities with Low Stock Levels.
	 																								You may log onto health-cmp.or.ke for follow up.</p>

	 																								<p>----</p>

	 																								<p>HCMP</p>

	 																								<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

						//Create the excel here
	 																								$report_type = "stockouts";
	 																								$this -> create_excel($excel_data, $report_type);
						//For Mac
						//$handler = "/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
						//For Windows
	 																								$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																								$subject = "Stock Outs Report: " . $facility_name;

	 																								$email_address = $this -> get_facility_email($facility_code);
	 																								$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 																							}

					//End foreach for facility
	 																							endforeach;

	 																							(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = array_merge($district_total[$district_name], array($facility_total)) : $district_total = array_merge($district_total, array($district_name => array($facility_total)));
				//Building the excel sheet to be sent to the district admin
	 																							$excel_data = array();
	 																							$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district stock outs weekly report ', 'file_name' => 'district weekly report');
	 																							$row_data = array();
	 																							$column_data = array("Commodity Name", "Unit Size", "Unit Cost(KSH)", "Quantity Available (Units)", "Quantity Available (Packs)", "Supplier", "Manufacturer", "Facility Name", "Subcounty", "County");
	 																							$excel_data['column_data'] = $column_data;

	 																							foreach ($facility_total as $facility_total_1) :
	 																								foreach ($facility_total_1 as $facility_total_2) :
	 																									foreach ($facility_total_2 as $facility_total1) :
	 																										array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["unit_cost"], $facility_total1["current_balance"], $facility_total1["current_balance_packs"], $facility_total1["source_name"], $facility_total1["manufacture"], $facility_total1["facility_name"], $facility_total1["district"], $facility_total1["county"]));

	 																									endforeach;
	 																									endforeach;
	 																									endforeach;
	 																									if (empty($row_data)) {
					//do nothing
	 																									} else {
	 																										$excel_data['row_data'] = $row_data;
	 																										$excel_data['report_type'] = "download_file";
	 																										$excel_data['file_name'] = $district_name . "_Weekly_Sub_County_Stock_Outs_Report";
	 																										$excel_data['excel_title'] = "Stock Outs Report for " . $district_name . " Sub County as at " . date("jS F Y");

	 																										$report_type = "stockouts";
	 																										$this -> create_excel($excel_data, $report_type);
					//For Mac
					//$handler = "/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
					//For Windows
	 																										$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

	 																										$subject = "Stock Outs: " . $district_name . " Sub County";

	 																										$message = "Dear " . $district_name . " Sub County,
	 																										<p>Find attached an excel sheet with the " . $district_name . " Sub County breakdown of Stock Outs in the Sub County.
	 																											You may log onto health-cmp.or.ke for follow up.</p>

	 																											<p>----</p>

	 																											<p>HCMP</p>

	 																											<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																											$email_address = $this -> get_ddp_email($district_id);
	 																											$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 																										}

	 																									}

			//Building the excel sheet to be sent to the district admin
	 																									$excel_data = array();
	 																									$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'county stock outs weekly report ', 'file_name' => 'district weekly report');
	 																									$row_data = array();
	 																									$column_data = array("Commodity Name", "Unit Size", "Unit Cost(KSH)", "Quantity Available (Units)", "Quantity Available (Packs)", "Supplier", "Manufacturer", "Facility Name", "Subcounty", "County");
	 																									$excel_data['column_data'] = $column_data;

	 																									foreach ($district_total as $facility_total_1) :
	 																										foreach ($facility_total_1 as $facility_total_2) :
	 																											foreach ($facility_total_2 as $facility_total_3) :
	 																												foreach ($facility_total_3 as $facility_total_4) :
	 																													foreach ($facility_total_4 as $facility_total1) :
	 																														array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["unit_cost"], $facility_total1["current_balance"], $facility_total1["current_balance_packs"], $facility_total1["source_name"], $facility_total1["manufacture"], $facility_total1["facility_name"], $facility_total1["district"], $facility_total1["county"]));

	 																													endforeach;
	 																													endforeach;
	 																													endforeach;
	 																													endforeach;
	 																													endforeach;
	 																													if (empty($row_data)) {
				//do nothing
	 																													} else {
	 																														$excel_data['row_data'] = $row_data;
	 																														$excel_data['report_type'] = "download_file";
	 																														$excel_data['file_name'] = $county_name . "_Weekly_County_Stock_Outs_Report";
	 																														$excel_data['excel_title'] = "Stock Outs Report for " . $county_name . " County as at " . date("jS F Y");

	 																														$report_type = "stockouts";
	 																														$this -> create_excel($excel_data, $report_type);
				//For Mac
				//$handler = "/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
				//For Windows
	 																														$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

	 																														$subject = "Stock Outs: " . $county_name . " County";

	 																														$message = "Dear " . $county_name . " County,
	 																														<p>Find attached an excel sheet with the " . $county_name . " County breakdown of Stock Outs in the county.
	 																															You may log onto health-cmp.or.ke for follow up.</p>

	 																															<p>----</p>

	 																															<p>HCMP</p>

	 																															<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																															$email_address = $this -> get_ddp_email_county($county_id);
	 																															$bcc = $this -> get_bcc_notifications();
	 																															$cc = $this -> get_county_email($county_id);

	 																															$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc, $cc);

	 																														}

	 																													}

	 																												}

	//Consumption Report
	 																												public function consumption_report() {
		//Set the current year
	 																													$year = date("Y");
		//$picurl = base_url() . 'assets/img/coat_of_arms-resized1.png';
		//get the facilities in the district
	 																													$counties = Facilities::get_counties_all_using_HCMP();

	 																													foreach ($counties as $counties) {
			//holds the data for the entire county
			//once it is done executing for one county it is reset to zero
	 																														$county_total = array();
			//pick the county nae and county ID accordingly
	 																														$county_id = $counties['county'];
	 																														$county_name = $counties['county_name'];

			//Get all the districts in that  particular county
	 																														$districts = Facilities::get_all_using_HCMP($county_id);
			//holds the data for all the districts in a particular county
	 																														$district_total = array();

	 																														foreach ($districts as $districts) {

	 																															$district_id = $districts['district'];
	 																															$district_name = $districts['name'];
				//get all facilities in that district
	 																															$facilities = Facilities::getFacilities_for_email($district_id);
				//holds all the data for all facilities in a particular district
	 																															$facility_total = array();

	 																															foreach ($facilities as $facilities_) :
					//holds the total value of expiries for that particular facility in that district
	 																																$facility_potential_expiries_total = 0;
					//$facility_potential_expiries = array();
	 																															$facility_code = $facilities_ -> facility_code;
	 																															$facility_name = Facilities::get_facility_name2($facility_code);
	 																															$facility_name = $facility_name['facility_name'];

					//get potential expiries in that particular facility
	 																															$facility_consumption = facility_issues::get_consumption_report_facility($facility_code);

					//push the result into another array that will be used by the distrct
	 																															(array_key_exists($facility_name, $facility_total)) ? $facility_total[$facility_name] = array_merge($facility_total[$facility_name], array($facility_consumption)) : $facility_total = array_merge($facility_total, array($facility_name => array($facility_consumption)));
					//Start buliding the excel file
	 																															$excel_data = array();
	 																															$excel_data = array('doc_creator' => $facility_name, 'doc_title' => 'facility consumption report ', 'file_name' => 'facility consumption report');
	 																															$row_data = array();
	 																															$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Supplier", "Facility Name", "MFL Code", "Sub County", "County");
	 																															$excel_data['column_data'] = $column_data;

	 																															foreach ($facility_consumption as $facility_consumption) :
	 																																array_push($row_data, array($facility_consumption["commodity_name"], $facility_consumption["unit_size"], $facility_consumption["total_units"], $facility_consumption["total_packs"], $facility_consumption["unit_cost"], $facility_consumption["total_cost"], $facility_consumption["source_name"], $facility_consumption["facility_name"], $facility_consumption["facility_code"], $facility_consumption["district"], $facility_consumption["county"]));
	 																															$facility_potential_expiries_total += $facility_consumption["total_cost"];
	 																															endforeach;

	 																															if (empty($row_data)) {
						//do nothing
	 																															} else {
	 																																$excel_data['row_data'] = $row_data;
	 																																$excel_data['report_type'] = "download_file";
	 																																$excel_data['file_name'] = $facility_name . "_Consumption_Report";
	 																																$excel_data['excel_title'] = "Consumption Report for " . $facility_name . " for the month of " . date("F Y");

	 																																$subject = "Consumption: " . $facility_name;

	 																																$message = "Dear " . $facility_name . ",
	 																																<p>Find attached an excel sheet with the " . $facility_name . " breakdown of the Consumption.
	 																																</p>
	 																																<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																																<p>----</p>

	 																																<p>HCMP</p>

	 																																<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																																$report_type = "consumption";
	 																																$this -> create_excel($excel_data, $report_type);
	 																																$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

	 																																$email_address = $this -> get_facility_email($facility_code);
	 																																$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 																															}

					//End foreach for facility
	 																															endforeach;

	 																															(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = array_merge($district_total[$district_name], array($facility_total)) : $district_total = array_merge($district_total, array($district_name => array($facility_total)));
				//Building the excel sheet to be sent to the district admin
	 																															$excel_data = array();
	 																															$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'sub county consumption report ', 'file_name' => 'sub county consumption report');
	 																															$row_data = array();
	 																															$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Supplier", "Facility Name", "MFL Code", "Sub County", "County");
	 																															$excel_data['column_data'] = $column_data;

	 																															foreach ($facility_total as $facility_total_1) :
	 																																foreach ($facility_total_1 as $facility_total_2) :
	 																																	foreach ($facility_total_2 as $facility_total1) :
	 																																		array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["total_units"], $facility_total1["total_packs"], $facility_total1["unit_cost"], $facility_total1["total_cost"], $facility_total1["source_name"], $facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["district"], $facility_total1["county"]));

	 																																	endforeach;
	 																																	endforeach;
	 																																	endforeach;

	 																																	if (empty($row_data)) {
					//do nothing
	 																																	} else {

	 																																		$excel_data['row_data'] = $row_data;
	 																																		$excel_data['report_type'] = "download_file";
	 																																		$excel_data['file_name'] = $district_name . "_Weekly_Sub_County_Consumption_Report";
	 																																		$excel_data['excel_title'] = "Consumption Report for " . $district_name . " Sub County for the month of " . date("F Y");

					//Create the excel file here
	 																																		$report_type = "consumption";
	 																																		$this -> create_excel($excel_data, $report_type);
					//exit;

	 																																		$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																																		$subject = "Consumption: " . $district_name . " Sub County";

	 																																		$message = "<p>Dear " . $district_name . " Sub County,</p>
	 																																		<p>Find attached an excel sheet with the " . $district_name . " Sub County's breakdown of Consumption.
	 																																		</p>
	 																																		<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																																		<p>----</p>

	 																																		<p>HCMP</p>

	 																																		<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																																		$email_address = $this -> get_ddp_email($district_id);
	 																																		$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
	 																																	}

	 																																}

			//Building the excel sheet to be sent to the County Admin
	 																																$excel_data = array();
	 																																$excel_data = array('doc_creator' => $county_name, 'doc_title' => 'county consumption report ', 'file_name' => 'county consumption report');
	 																																$row_data = array();
	 																																$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Supplier", "Facility Name", "MFL Code", "Sub County", "County");
	 																																$excel_data['column_data'] = $column_data;

	 																																foreach ($district_total as $facility_total_1) :
	 																																	foreach ($facility_total_1 as $facility_total_2) :
	 																																		foreach ($facility_total_2 as $facility_total_3) :
	 																																			foreach ($facility_total_3 as $facility_total_4) :
	 																																				foreach ($facility_total_4 as $facility_total1) :
	 																																					array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["total_units"], $facility_total1["total_packs"], $facility_total1["unit_cost"], $facility_total1["total_cost"], $facility_total1["source_name"], $facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["district"], $facility_total1["county"]));

	 																																				endforeach;
	 																																				endforeach;
	 																																				endforeach;
	 																																				endforeach;
	 																																				endforeach;

	 																																				if (empty($row_data)) {
				//do nothing
	 																																				} else {
	 																																					$excel_data['row_data'] = $row_data;
	 																																					$excel_data['report_type'] = "download_file";
	 																																					$excel_data['file_name'] = $county_name . "_County_Consumption_Report";
	 																																					$excel_data['excel_title'] = "Consumption Report for " . $county_name . " County for the month of " . date("F Y");

				//create the excel file
	 																																					$report_type = "consumption";
	 																																					$this -> create_excel($excel_data, $report_type);

	 																																					$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																																					$subject = "Consumption: " . $county_name . " County";
				//exit;
	 																																					$message = "<p>Dear " . $county_name . " County,</p>
	 																																					<p>Find attached an excel sheet with the " . $county_name . " County's breakdown of Consumption.
	 																																					</p>
	 																																					<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																																					<p>----</p>

	 																																					<p>HCMP</p>

	 																																					<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																																					$email_address = $this -> get_ddp_email_county($county_id);
	 																																					$bcc = $this -> get_bcc_notifications();
	 																																					$cc = $this -> get_county_email($county_id);

	 																																					$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc, $cc);
	 																																				}

	 																																			}

	 																																		}

	//Stock Levels Report
	 																																		public function stock_levels_report() {
		//Set the current year
	 																																			$year = date("Y");
		//$picurl = base_url() . 'assets/img/coat_of_arms-resized1.png';
		//get the facilities in the district
	 																																			$counties = Facilities::get_counties_all_using_HCMP();

	 																																			foreach ($counties as $counties) {
			//holds the data for the entire county
			//once it is done executing for one county it is reset to zero
	 																																				$county_total = array();
			//pick the county nae and county ID accordingly
	 																																				$county_id = $counties['county'];
	 																																				$county_name = $counties['county_name'];

			//Get all the districts in that  particular county
	 																																				$districts = Facilities::get_all_using_HCMP($county_id);
			//holds the data for all the districts in a particular county
	 																																				$district_total = array();

	 																																				foreach ($districts as $districts) {

	 																																					$district_id = $districts['district'];
	 																																					$district_name = $districts['name'];
				//get all facilities in that district
	 																																					$facilities = Facilities::getFacilities_for_email($district_id);
				//holds all the data for all facilities in a particular district
	 																																					$facility_total = array();

	 																																					foreach ($facilities as $facilities_) :
					//holds the total value of expiries for that particular facility in that district
	 																																						$facility_potential_expiries_total = 0;
					//$facility_potential_expiries = array();
	 																																					$facility_code = $facilities_ -> facility_code;
	 																																					$facility_name = Facilities::get_facility_name2($facility_code);
	 																																					$facility_name = $facility_name['facility_name'];

					//get the stocking levels in that particular facility

	 																																					$facility_consumption = facility_issues::get_consumption_report_facility($facility_code);

					//push the result into another array that will be used by the distrct
	 																																					(array_key_exists($facility_name, $facility_total)) ? $facility_total[$facility_name] = array_merge($facility_total[$facility_name], array($facility_consumption)) : $facility_total = array_merge($facility_total, array($facility_name => array($facility_consumption)));
					//Start buliding the excel file
	 																																					$excel_data = array();
	 																																					$excel_data = array('doc_creator' => $facility_name, 'doc_title' => 'facility stock level report ', 'file_name' => 'facility stock level report');
	 																																					$row_data = array();
	 																																					$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Supplier", "Facility Name", "MFL Code", "Sub County", "County");
	 																																					$excel_data['column_data'] = $column_data;

	 																																					foreach ($facility_consumption as $facility_consumption) :
	 																																						array_push($row_data, array($facility_consumption["commodity_name"], $facility_consumption["unit_size"], $facility_consumption["total_units"], $facility_consumption["total_packs"], $facility_consumption["unit_cost"], $facility_consumption["total_cost"], $facility_consumption["source_name"], $facility_consumption["facility_name"], $facility_consumption["facility_code"], $facility_consumption["district"], $facility_consumption["county"]));
	 																																					$facility_potential_expiries_total += $facility_consumption["total_cost"];
	 																																					endforeach;

	 																																					if (empty($row_data)) {
						//do nothing
	 																																					} else {
	 																																						$excel_data['row_data'] = $row_data;
	 																																						$excel_data['report_type'] = "download_file";
	 																																						$excel_data['file_name'] = $facility_name . "_Stock_Level_Report";
	 																																						$excel_data['excel_title'] = "Stock Levels Report for " . $facility_name . " for the month as at " . date("jS F Y");

	 																																						$subject = "Stock Levels: " . $facility_name;

	 																																						$message = "Dear " . $facility_name . ",
	 																																						<p>Find attached an excel sheet with the " . $facility_name . " breakdown of Stock Levels.
	 																																						</p>
	 																																						<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																																						<p>----</p>

	 																																						<p>HCMP</p>

	 																																						<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

	 																																						$report_type = "stock_level";
	 																																						$this -> create_excel($excel_data, $report_type);
	 																																						$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

						//$email_address = $this->get_facility_email($facility_code);
						//$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

	 																																					}

					//End foreach for facility
	 																																					endforeach;

	 																																					(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = array_merge($district_total[$district_name], array($facility_total)) : $district_total = array_merge($district_total, array($district_name => array($facility_total)));
				//Building the excel sheet to be sent to the district admin
	 																																					$excel_data = array();
	 																																					$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'sub county stock level report ', 'file_name' => 'sub county stock level report');
	 																																					$row_data = array();
	 																																					$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Supplier", "Facility Name", "MFL Code", "Sub County", "County");
	 																																					$excel_data['column_data'] = $column_data;

	 																																					foreach ($facility_total as $facility_total_1) :
	 																																						foreach ($facility_total_1 as $facility_total_2) :
	 																																							foreach ($facility_total_2 as $facility_total1) :
	 																																								array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["total_units"], $facility_total1["total_packs"], $facility_total1["unit_cost"], $facility_total1["total_cost"], $facility_total1["source_name"], $facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["district"], $facility_total1["county"]));

	 																																							endforeach;
	 																																							endforeach;
	 																																							endforeach;

	 																																							if (empty($row_data)) {
					//do nothing
	 																																							} else {

	 																																								$excel_data['row_data'] = $row_data;
	 																																								$excel_data['report_type'] = "download_file";
	 																																								$excel_data['file_name'] = $district_name . "_Sub_County_Stock_Level_Report";
	 																																								$excel_data['excel_title'] = "Stock Levels Report for " . $district_name . " Sub County as at " . date("jS F Y");

					//Create the excel file here
	 																																								$report_type = "stock_level";
	 																																								$this -> create_excel($excel_data, $report_type);
					//exit;

	 																																								$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																																								$subject = "Stock Levels: " . $district_name . " Sub County";

	 																																								$message = "<p>Dear " . $district_name . " Sub County,</p>
	 																																								<p>Find attached an excel sheet with the " . $district_name . " Sub County's breakdown of Stock Levels.
	 																																								</p>
	 																																								<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																																								<p>----</p>

	 																																								<p>HCMP</p>

	 																																								<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

					//$email_address = $this -> get_ddp_email($district_id);
					//$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
	 																																							}

	 																																						}

			//Building the excel sheet to be sent to the County Admin
	 																																						$excel_data = array();
	 																																						$excel_data = array('doc_creator' => $county_name, 'doc_title' => 'county stock levels report ', 'file_name' => 'county stock levels report');
	 																																						$row_data = array();
	 																																						$column_data = array("Commodity Name", "Unit Size", "Quantity (units)", "Quantity (packs)", "Unit Cost(KSH)", "Total Cost(KSH)", "Supplier", "Facility Name", "MFL Code", "Sub County", "County");
	 																																						$excel_data['column_data'] = $column_data;

	 																																						foreach ($district_total as $facility_total_1) :
	 																																							foreach ($facility_total_1 as $facility_total_2) :
	 																																								foreach ($facility_total_2 as $facility_total_3) :
	 																																									foreach ($facility_total_3 as $facility_total_4) :
	 																																										foreach ($facility_total_4 as $facility_total1) :
	 																																											array_push($row_data, array($facility_total1["commodity_name"], $facility_total1["unit_size"], $facility_total1["total_units"], $facility_total1["total_packs"], $facility_total1["unit_cost"], $facility_total1["total_cost"], $facility_total1["source_name"], $facility_total1["facility_name"], $facility_total1["facility_code"], $facility_total1["district"], $facility_total1["county"]));

	 																																										endforeach;
	 																																										endforeach;
	 																																										endforeach;
	 																																										endforeach;
	 																																										endforeach;

	 																																										if (empty($row_data)) {
				//do nothing
	 																																										} else {
	 																																											$excel_data['row_data'] = $row_data;
	 																																											$excel_data['report_type'] = "download_file";
	 																																											$excel_data['file_name'] = $county_name . "_County_Stock_Level_Report";
	 																																											$excel_data['excel_title'] = "Stock Level Report for " . $county_name . " County as at " . date("jS F Y");

				//create the excel file
	 																																											$report_type = "stock_level";
	 																																											$this -> create_excel($excel_data, $report_type);

	 																																											$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																																											$subject = "Stock Level: " . $county_name . " County";
				//exit;
	 																																											$message = "<p>Dear " . $county_name . " County,</p>
	 																																											<p>Find attached an excel sheet with the " . $county_name . " County's breakdown of Stock Level.
	 																																											</p>
	 																																											<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																																											<p>----</p>

	 																																											<p>HCMP</p>

	 																																											<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";

				//$email_address = $this -> get_county_email($county_id);
				//$email_address = "smutheu@clintonhealthaccess.org,kelvinmwas@gmail.com,karsanrichard@gmail.com";
	 																																											$bcc = $this -> get_bcc_notifications();
	 																																											if ($county_id == 1) :
	 																																												$cc_email = $this -> get_bcc_notifications();
				//$cc_email = "";
	 																																											else :
	 																																												$cc_email = "";
	 																																											endif;

	 																																											$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc, $cc_email);
	 																																										}

	 																																									}

	 																																								}

	 																																								/*CHAI REPORTS for ORS AND ZINC FOR THE ENTIRE COUNTRY*/
	 																																								public function ors_zinc_report() {
		//Set the current year
	 																																									$year = date("Y");
	 																																									$county_total = array();
	 																																									$excel_data = array();
	 																																									$excel_data = array('doc_creator' => "HCMP", 'doc_title' => ' stock level report ', 'file_name' => 'stock level report');
	 																																									$row_data = array();
	 																																									$column_data = array("County", "Sub-County", "Facility Code", "Facility Name", "Commodity Name", "Unit Size", "Unit Cost(KES)", "Supplier", "Manufacturer", "Batch Number", "Expiry Date", "Stock at Hand (units)", "Stock at Hand (packs)", "AMC (units)", "AMC (packs)", "Stock at Hand MOS(packs)","Date Last Issued","Days From Last Issue");
	 																																									$excel_data['column_data'] = $column_data;
		//the commodities variable will hold the values for the three commodities ie ORS and Zinc
	 																																									$commodities = array(51, 267, 36,456);
	 																																									foreach ($commodities as $commodities) :
	 																																										$commodity_stock_level = array();
			//holds the data for the entire county
			//once it is done executing for one commodity it will reset to zero
	 																																									$commodity_total = array();
			//pick the commodity names and details
			//get the stock level for that commodity
	 																																									$commodity_stock_level = Facility_stocks::get_commodity_stock_level($commodities);
			//echo "<pre>";print_r($commodity_stock_level);exit;
			//Start buliding the excel file
	 																																									foreach ($commodity_stock_level as $commodity_stock_level) :
				//pick the facility code from the data
	 																																										$facility_code = $commodity_stock_level["facility_code"];
				//get the last date of issue from the database
	 																																									$date_last_issue = Facilities::get_last_issue($facility_code);
				//ensure that if the date is null change the message
	 																																									$date_of_last_issue = ($date_last_issue[0]['Date Last Issued']!=0) ? date('j M, Y', strtotime($date_last_issue[0]['Date Last Issued'])) : "No Data Found";
	 																																									$days_since_last_issue = ($date_last_issue[0]['Days From Last Issue']!=0) ? $date_last_issue[0]['Days From Last Issue'] : 0;
	 																																									array_push($row_data, array($commodity_stock_level["county"], $commodity_stock_level["subcounty"], $commodity_stock_level["facility_code"], $commodity_stock_level["facility_name"], $commodity_stock_level["commodity_name"], $commodity_stock_level["unit_size"], $commodity_stock_level["unit_cost"], $commodity_stock_level["supplier"], $commodity_stock_level["manufacture"], $commodity_stock_level["batch_no"], $commodity_stock_level["expiry_date"], $commodity_stock_level["balance_units"], $commodity_stock_level["balance_packs"], $commodity_stock_level["amc_units"], $commodity_stock_level["amc"], $commodity_stock_level["mos"],$date_of_last_issue,$days_since_last_issue));

	 																																									endforeach;

			//Switch statement to build on the remaining part of the message body
			//get the number of facilities stocked out on a specific commodity
	 																																									$no_of_stock_outs = Facility_stocks::facilities_stocked_specific_commodity($commodities);

			//get the number of facilities reporting on a specific commodity
	 																																									$no_of_facilities_reporting = Facility_stocks::facilities_reporting_on_a_specific_commodity($commodities);
			//get the number of batches expiring within 3 months
	 																																									$no_of_batches = Facility_stocks::batches_expiring_specific_commodities($commodities);

	 																																									switch($commodities) :
	 																																									case 51 :
	 																																									$message_body .= "<p>Number of Facilities Reporting on ORS Satchets (100):" . $no_of_facilities_reporting . "</p>";
	 																																									$message_body .= "<p>Number of Facilities Stocked out on ORS Satchets (100): " . $no_of_stock_outs . "</p>";
	 																																									$message_body .= "<p>Number of ORS (100) Batches expiring in the next 3 months: " . $no_of_batches . "</p>";
	 																																									break;
	 																																									case 267 :
	 																																									$message_body .= "<p>Number of Facilities Reporting on ORS Satchets (50):" . $no_of_facilities_reporting . "</p>";
	 																																									$message_body .= "<p>Number of Facilities Stocked out on ORS Satchets (50): " . $no_of_stock_outs . "</p>";
	 																																									$message_body .= "<p>Number of ORS (50) Batches expiring in the next 3 months:  " . $no_of_batches . "</p>";
	 																																									break;
	 																																									case 36 :
	 																																									$message_body .= "<p>Number of Facilities Reporting on Zinc Sulphate 20mg: " . $no_of_facilities_reporting . "</p>";
	 																																									$message_body .= "<p>Number of Facilities Stocked out on Zinc Sulphate 20mg: " . $no_of_stock_outs . "</p>";
	 																																									$message_body .= "<p>Number of Zinc Sulphate Batches expiring in the next 3 months: " . $no_of_batches . "</p>";
	 																																									break;
	 																																									case 456 :
	 																																									$message_body .= "<p>Number of Facilities Reporting on ORS 4 Satchets & Zinc 10 Tablets 20 Mg: " . $no_of_facilities_reporting . "</p>";
	 																																									$message_body .= "<p>Number of Facilities Stocked out on ORS 4 Satchets & Zinc 10 Tablets 20 Mg: " . $no_of_stock_outs . "</p>";
	 																																									$message_body .= "<p>Number of ORS 4 Satchets & Zinc 10 Tablets 20 Mg Batches expiring in the next 3 months: " . $no_of_batches . "</p>";
	 																																									break;
	 																																									endswitch;


	 																																									endforeach;

	 																																									$excel_data['row_data'] = $row_data;
	 																																									$excel_data['report_type'] = "download_file";
	 																																									$excel_data['file_name'] = "Stock_Level_Report";
	 																																									$excel_data['excel_title'] = "Stock Level Report for Zinc sulphate Tablets  20mg and ORS sachet (for 500ml) low osmolality (100) & (50) as at " . date("jS F Y");

		//Start the email section of the report
		//Get the number of facilities using HCMP
	 																																									$no_of_facilities = Facilities::get_all_on_HCMP();

	 																																									$subject = "Stock Level Report: Zinc sulphate Tablets  20mg and ORS sachet (for 500ml) low osmolality ";

	 																																									$message = "<p>Find attached an excel sheet with a Stock Level Report for 
	 																																									Zinc Sulphate 20mg, ORS sachet (for 500ml) low osmolality (100 & 50) and ORS 4 Satchets & Zinc 10 Tablets 20 Mg as at " . date("jS F Y") . "</p>";
	 																																									$message .= "<p>Number of facilities using HCMP: " . $no_of_facilities . "</p>";
	 																																									$message .= $message_body;
	 																																									$message .= "
	 																																									<p>You may log onto health-cmp.or.ke for follow up.</p>

	 																																									<p>----</p>

	 																																									<p>HCMP</p>

	 																																									<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";
	 																																									$report_type = "ors_report";
	 																																									$this -> create_excel($excel_data, $report_type);
       // exit;


		//path for windows
	 																																									$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

		//path for Mac
		//$handler = "/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	 																																									$email_address = "smutheu@clintonhealthaccess.org,jaynerawz@gmail.com";
	 																																									$bcc = "karsanrichard@gmail.com,kelvinmwas@gmail.com";

	 																																									$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc);

	 																																								}
	 																																								public function ors_zinc_redistribution_report() {
        //Set the current year
	 																																									$year = date("Y");
	 																																									$county_total = array();
	 																																									$excel_data = array();
	 																																									$column_data = array();
	 																																									$excel_data = array('doc_creator' => "HCMP", 'doc_title' => 'ors zinc redistribution report ', 'file_name' => 'ors zinc redistribution report');
	 																																									$row_data = array();

	 																																									$column_data = array("Sender Name", "Receiver Name", "Source Facility Code", "Source Facility Name","Source Sub County","Receiver Facility Name","Receiver Facility Code","Receiver Sub County", "Commodity Name","Commodity Code", "Unit Size", "Unit Cost(KES)","Quantity Sent(Units)","Quantity Sent(Packs)","Quantity Received(Units)","Quantity Received(Packs)","Manufacturer","Batch Number", "Expiry Date","Status","Date Sent","HCMP Supported Facility","Date Received");
	 																																									$excel_data['column_data'] = $column_data;
        //the commodities variable will hold the values for the three commodities ie ORS and Zinc
	 																																									$commodities = array(51, 267, 36,456);
	 																																									foreach ($commodities as $commodities) :
	 																																										$commodity_stock_level = array();
            //holds the data for the entire county
            //once it is done executing for one commodity it will reset to zero
	 																																									$commodity_total = array();
            //pick the commodity names and details
            //get the redistribution report for the specified commodities
	 																																									$commodity_redistribution = redistribution_data::get_redistribution_data_ors_zinc($commodities);

            //Start building the excel file
	 																																									foreach ($commodity_redistribution as $commodity_redistribution) :

                //format the names for the receiver and sender accordingly
	 																																										$sender = $commodity_redistribution["sender_name_fname"]." ".$commodity_redistribution["sender_name_lname"];
	 																																									$receiver = $commodity_redistribution["receiver_name_fname"]." ".$commodity_redistribution["receiver_name_lname"];

                //then get the quantity of the commodities in packs
	 																																									$total_commodity_units = "";
	 																																									$total_commodity_units = Commodities::get_commodity_unit($commodities);
	 																																									$total_commodity_units = $total_commodity_units[0]['total_commodity_units'];

                //get the quantity received in packs
	 																																									$quantity_sent_packs = $commodity_redistribution["quantity_sent"]/$total_commodity_units;
	 																																									$quantity_received_packs = $commodity_redistribution["quantity_received"]/$total_commodity_units;

                //now let us set the the status
	 																																									$status = "";
	 																																									$status = ($commodity_redistribution["status"]!=0)?"Received" : "Not Received";

                //check if the facility is supported facility
	 																																									$active = "";
	 																																									$active = Facilities::check_active_facility($commodity_redistribution["receiver_facility_code"]);
	 																																									$active = $active[0]["HCMP Supported"];

                //format the date received
	 																																									$date_received = "";
	 																																									$date_received = ($commodity_redistribution["date_received"] !=0)?$commodity_redistribution["date_received"]:"Not Received";

                //echo "<pre>";print_r($date_received);exit;
                //pick the facility code from the data

                /*$facility_code = $commodity_redistribution["facility_code"];
                //get the last date of issue from the database
                $date_last_issue = Facilities::get_last_redistribution($facility_code);
                //ensure that if the date is null change the message
                $date_of_last_issue = ($date_last_issue[0]['Date Last Redistributed']!=0) ? date('j M, Y', strtotime($date_last_issue[0]['Date Last Redistributed'])) : "No Data Found";
                $days_since_last_issue = ($date_last_issue[0]['Days From Last Redistribution']!=0) ? $date_last_issue[0]['Days From Last Redistribution'] : 0;
                */
               //"Date Sent","HCMP Supported Facility","Date Received");

                //push the result from the array into row data that will be dumped into the excel file
                array_push($row_data, array($sender,$receiver, $commodity_redistribution["source_facility_code"],
                	$commodity_redistribution["source_facility_name"],
                	$commodity_redistribution["source_district"],
                	$commodity_redistribution["receiver_facility_name"],
                	$commodity_redistribution["receiver_facility_code"],
                	$commodity_redistribution["receiver_district"],
                	$commodity_redistribution["commodity_name"],
                	$commodity_redistribution["commodity_code"],
                	$commodity_redistribution["unit_size"],
                	$commodity_redistribution["unit_cost"],
                	$commodity_redistribution["quantity_sent"],
                	$quantity_sent_packs,
                	$commodity_redistribution["quantity_received"],
                	$quantity_received_packs,
                	$commodity_redistribution["manufacturer"],
                	$commodity_redistribution["batch_no"],
                	$commodity_redistribution["expiry_date"],
                	$status,
                	$commodity_redistribution["date_sent"],
                	$active,
                	$date_received));

                //echo "<pre>";print_r($row_data);exit;

endforeach;




endforeach;

$excel_data['row_data'] = $row_data;
$excel_data['report_type'] = "download_file";
$excel_data['file_name'] = "Zinc ORS Redistribution Report";
$excel_data['excel_title'] = "Redistribution Report for Zinc sulphate Tablets  20mg and ORS sachet (for 500ml) low osmolality (100) & (50) for the year " . date("Y");

        //Start the email section of the report
        //Get the number of facilities using HCMP
$no_of_facilities = Facilities::get_all_on_HCMP();

$subject = "Redistribution Report: Zinc sulphate Tablets  20mg and ORS sachet (for 500ml) low osmolality ";

$message = "<p>Find attached an excel sheet with a Redistribution Report for
Zinc Sulphate 20mg, ORS sachet (for 500ml) low osmolality (100 & 50) and ORS 4 Satchets & Zinc 10 Tablets 20 Mg as at " . date("jS F Y") . "</p>";
$message .= "<p>Number of facilities using HCMP: " . $no_of_facilities . "</p>";
$message .= $message_body;
$message .= "
<p>You may log onto health-cmp.or.ke for follow up.</p>

<p>----</p>

<p>HCMP</p>

<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";
$report_type = "ors_report";
$this -> create_excel($excel_data, $report_type);
        //exit;


        //path for windows
$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

        //path for Mac
        //$handler = "/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
$email_address = "smutheu@clintonhealthaccess.org";
$bcc = "karsanrichard@gmail.com,kelvinmwas@gmail.com";

$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc);

}
public function ors_zinc_consumption_report() {
        //Set the current year
	$year = date("Y");
	$county_total = array();
	$excel_data = array();
	$excel_data = array('doc_creator' => "HCMP", 'doc_title' => ' consumption report ', 'file_name' => 'stock level report');
	$row_data = array();

	$column_data = array("County", "Sub-County", "Facility Code", "Facility Name", "Commodity Name", "Unit Cost(KES)", "Supplier","Batch Number", "Quantity Consumed (Packs)", "Quantity Consumed (Units)", "Date Last Issued","Days From Last Issue");
	$excel_data['column_data'] = $column_data;
        //the commodities variable will hold the values for the three commodities ie ORS and Zinc
	$commodities = array(51, 267, 36,456);
	foreach ($commodities as $commodities) :
		$commodity_stock_level = array();
            //holds the data for the entire county
            //once it is done executing for one commodity it will reset to zero
	$commodity_total = array();
            //pick the commodity names and details
            //get the consumption for that commodity
	$commodity_consumption = facility_issues::get_consumption_report_ors_zinc($commodities);

            //Start building the excel file
	foreach ($commodity_consumption as $commodity_consumption) :
                //pick the facility code from the data
		$facility_code = $commodity_consumption["facility_code"];

                //get the last date of issue from the database
	$date_last_issue = Facilities::get_last_issue($facility_code);
                //ensure that if the date is null change the message
	$date_of_last_issue = ($date_last_issue[0]['Date Last Issued']!=0) ? date('j M, Y', strtotime($date_last_issue[0]['Date Last Issued'])) : "No Data Found";
	$days_since_last_issue = ($date_last_issue[0]['Days From Last Issue']!=0) ? $date_last_issue[0]['Days From Last Issue'] : 0;

	array_push($row_data, array($commodity_consumption["county"], $commodity_consumption["district"], $commodity_consumption["facility_code"], $commodity_consumption["facility_name"], $commodity_consumption["commodity_name"],$commodity_consumption["unit_cost"], $commodity_consumption["source_name"], $commodity_consumption["batch_no"], $commodity_consumption["total_packs"], $commodity_consumption["total_units"],$date_of_last_issue,$days_since_last_issue));

	endforeach;


	endforeach;

	$excel_data['row_data'] = $row_data;
	$excel_data['report_type'] = "download_file";
	$excel_data['file_name'] = "Consumption Report For Zinc & ORS";
	$excel_data['excel_title'] = "Consumption Report for Zinc sulphate Tablets  20mg and ORS sachet (for 500ml) low osmolality (100) & (50) as at " . date("jS F Y");

        //Start the email section of the report
        //Get the number of facilities using HCMP
	$no_of_facilities = Facilities::get_all_on_HCMP();

	$subject = "Consumption Report: Zinc sulphate Tablets  20mg and ORS sachet (for 500ml) low osmolality ";

	$message = "<p>Find attached an excel sheet with a Consumption Report for
	Zinc Sulphate 20mg, ORS sachet (for 500ml) low osmolality (100 & 50) and ORS 4 Satchets & Zinc 10 Tablets 20 Mg as at " . date("jS F Y") . "</p>";
	$message .= "<p>Number of facilities using HCMP: " . $no_of_facilities . "</p>";
	$message .= $message_body;
	$message .= "
	<p>You may log onto health-cmp.or.ke for follow up.</p>

	<p>----</p>

	<p>HCMP</p>

	<p>This email was automatically generated. Please do not respond to this email address or it will be ignored.</p>";
	$report_type = "ors_report";
	$this -> create_excel($excel_data, $report_type);
        //exit;


        //path for windows
	$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";

        //path for Mac
        //$handler = "/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
	$email_address = "smutheu@clintonhealthaccess.org";
	$bcc = "karsanrichard@gmail.com,kelvinmwas@gmail.com";

	$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc);

}

public function create_excel_ors($excel_data = NUll, $report_type = NULL, $total_figure = NULL) {
	$styleArray = array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
	$styleArray2 = array('font' => array('bold' => true));
		//$objWorksheet1 = $objPHPExcel->createSheet();
		//$objWorksheet1->setTitle('Another sheet');
		//check if the excel data has been set if not exit the excel generation
	if (count($excel_data) > 0) :

		$objPHPExcel = new PHPExcel();
	$objPHPExcel -> getProperties() -> setCreator("HCMP");
	$objPHPExcel -> getProperties() -> setLastModifiedBy($excel_data['doc_creator']);
	$objPHPExcel -> getProperties() -> setTitle($excel_data['doc_title']);
	$objPHPExcel -> getProperties() -> setSubject($excel_data['doc_title']);
	$objPHPExcel -> getProperties() -> setDescription("");

	$objPHPExcel -> setActiveSheetIndex(0);
	$objPHPExcel -> getActiveSheet() -> mergeCells('A1:H1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);

	$rowExec = 2;
	$column = 0;
			//Looping through the cells

	foreach ($excel_data['column_data'] as $column_data) {
		$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $column_data);
		$objPHPExcel -> getActiveSheet() -> getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column)) -> setAutoSize(true);
		$objPHPExcel -> getActiveSheet() -> getStyleByColumnAndRow($column, $rowExec) -> getFont() -> setBold(true);
		$column++;
	}

	$rowExec = 3;

	foreach ($excel_data['row_data'] as $row_data) {
		$column = 0;
		foreach ($row_data as $cell) {
					//Looping through the cells per facility
			$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $cell);
			$column++;
		}

		$rowExec++;
	}

	$objPHPExcel -> getActiveSheet() -> setTitle('Simple');
			//echo date('H:i:s') . " Write to Excel2007 format\n";
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

			// We'll be outputting an excel file
	if (isset($excel_data['report_type'])) {
				///Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files
		$objWriter -> save("./print_docs/excel/excel_files/" . $excel_data['file_name'] . '.xls');
				//$objWriter->save("/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/".$excel_data['file_name'].'.xls');
				//exit;
	} else {
				// We'll be outputting an excel file
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
				// It will be called file.xls
		header("Content-Disposition: attachment; filename=" . $excel_data['file_name'] . '.xls');
				// Write file to the browser
		$objWriter -> save('php://output');
		$objPHPExcel -> disconnectWorksheets();
		unset($objPHPExcel);
	}

	endif;
}

public function create_excel($excel_data = NUll, $report_type = NULL, $total_figure = NULL) {
	$styleArray = array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
	$styleArray2 = array('font' => array('bold' => true));

		//check if the excel data has been set if not exit the excel generation
		//$objWorksheet1 = $objPHPExcel->createSheet();
		//$objWorksheet1->setTitle('Another sheet');

	if (count($excel_data) > 0) :

		$objPHPExcel = new PHPExcel();
	$objPHPExcel -> getProperties() -> setCreator("HCMP");
	$objPHPExcel -> getProperties() -> setLastModifiedBy($excel_data['doc_creator']);
	$objPHPExcel -> getProperties() -> setTitle($excel_data['doc_title']);
	$objPHPExcel -> getProperties() -> setSubject($excel_data['doc_title']);
	$objPHPExcel -> getProperties() -> setDescription("");

	$objPHPExcel -> setActiveSheetIndex(0);

	if ($report_type == "expiries") :
		$objPHPExcel -> getActiveSheet() -> mergeCells('A1:N1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);
	$cell_count = count($excel_data["row_data"]);
	$cell_count2 = $cell_count + 2;
	$cell_count3 = $cell_count + 3;
	$objPHPExcel -> getActiveSheet() -> setCellValue('F' . $cell_count3, "=SUM(F3:F" . $cell_count2 . ")");
	$objPHPExcel -> getActiveSheet() -> setCellValue('A' . $cell_count3, "Total Cost of Expiries");
	$objPHPExcel -> getActiveSheet() -> getStyle('A' . $cell_count3) -> applyFromArray($styleArray2);
	$objPHPExcel -> getActiveSheet() -> getStyle('F' . $cell_count3) -> applyFromArray($styleArray2);
	elseif ($report_type == "ors_report") :
		$objPHPExcel -> getActiveSheet() -> mergeCells('A1:H1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);
	elseif ($report_type == "potential_expiries") :
		$objPHPExcel -> getActiveSheet() -> mergeCells('A1:N1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);
	elseif ($report_type == "stockouts") :
		$objPHPExcel -> getActiveSheet() -> mergeCells('A1:J1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);
	elseif ($report_type == "order_costs") :
		$objPHPExcel -> getActiveSheet() -> mergeCells('A1:J1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);
	elseif ($report_type == "consumption") :
		$objPHPExcel -> getActiveSheet() -> mergeCells('A1:K1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);
	elseif ($report_type == "stock_level") :
		$objPHPExcel -> getActiveSheet() -> mergeCells('A1:G1');
	$objPHPExcel -> getActiveSheet() -> setCellValue('A1', $excel_data['excel_title']);
	$objPHPExcel -> getActiveSheet() -> getStyle('A1') -> applyFromArray($styleArray);

	endif;

	$rowExec = 2;
	$column = 0;
			//Looping through the cells

	foreach ($excel_data['column_data'] as $column_data) {
		$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $column_data);
		$objPHPExcel -> getActiveSheet() -> getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column)) -> setAutoSize(true);
				//$objPHPExcel->getActiveSheet()->getStyle($column, $rowExec)->getFont()->setBold(true);
		$objPHPExcel -> getActiveSheet() -> getStyleByColumnAndRow($column, $rowExec) -> getFont() -> setBold(true);
		$column++;
	}

	$rowExec = 3;

	foreach ($excel_data['row_data'] as $row_data) {
		$column = 0;
		foreach ($row_data as $cell) {
					//Looping through the cells per facility
			$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $cell);
			$column++;
		}

		$rowExec++;
	}

	$objPHPExcel -> getActiveSheet() -> setTitle('Simple');
			//echo date('H:i:s') . " Write to Excel2007 format\n";
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

			// We'll be outputting an excel file
	if (isset($excel_data['report_type'])) {
				//For Windows
		$objWriter -> save("./print_docs/excel/excel_files/" . $excel_data['file_name'] . '.xls');
				//For Mac
				//$objWriter->save("/Applications/XAMPP/xamppfiles/htdocs/hcmp/print_docs/excel/excel_files/".$excel_data['file_name'].'.xls');
				//exit;
	} else {
				// We'll be outputting an excel file
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
				// It will be called file.xls
		header("Content-Disposition: attachment; filename=" . $excel_data['file_name'] . '.xls');
				// Write file to the browser
		$objWriter -> save('php://output');
		$objPHPExcel -> disconnectWorksheets();
		unset($objPHPExcel);
	}

	endif;
}


public function create_pdf($pdf_data = NULL, $district_id = NULL, $html_body = NULL) {
		//echo $district_id;
		//exit;
	if (count($pdf_data) > 0) :
		$url = base_url() . 'assets/img/coat_of_arms.png';
	$html_title = "<div align=center><img src='$url' height='70' width='70'style='vertical-align: top;'> </img></div>
	<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold; font-size: 14px;'>
		Ministry of Health</div>
		<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold;display: block; font-size: 13px;'>
			Health Commodities Management Platform</div>
			<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold;display: block; font-size: 13px;'>
				Weekly Reports</div><hr/>";

				$table_style = '<style>table.data-table {border: 1px solid #DDD;margin: 10px auto;border-spacing: 0px;}
				table.data-table th {border: none;color: #036;text-align: center;border: 1px solid #DDD;border-top: none;max-width: 450px;}
				table.data-table td, table th {padding: 4px;}
				table.data-table td {border: none;border-left: 1px solid #DDD;border-right: 1px solid #DDD;height: 30px;margin: 0px;border-bottom: 1px solid #DDD;}
			</style>';
			//$name=$this -> session -> userdata('full_name');

			$this -> mpdf = new mPDF('', 'A4-L', 0, '', 15, 15, 16, 16, 9, 9, '');
			$this -> mpdf -> ignore_invalid_utf8 = true;
			$this -> mpdf -> WriteHTML($html_title);
			$this -> mpdf -> defaultheaderline = 1;
			$this -> mpdf -> simpleTables = true;
			$this -> mpdf -> WriteHTML($table_style . $pdf_data);
			$this -> mpdf -> SetFooter("{DATE D j M Y }|{PAGENO}/{nb}|Prepared by: HCMP");
			//$this -> mpdf -> Output($report_name, 'I');
			//exit;
			$report_name = "Weekly Report.pdf";
			$path = $_SERVER["DOCUMENT_ROOT"];
			$handler = $path . "/HCMPv2/pdf/" . $report_name;
			write_file($handler, $this -> mpdf -> Output($report_name, 'S'));
			//$email_address = "karsanrichard@gmail.com";
			$message = $html_body;
			//echo $html_body;
			//exit;
			$subject = "Weekly Reports";
			//Get the email address of the dpp to whom the email is going to
			$email_address = $this -> get_ddp_email($district_id);
			//$email_address = "karsanrichard@gmail.com";
			$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
			endif;
		}

		function email_sender($report_name) {

		//setting the connection variables
			$config['protocol'] = 'smtp';
			$config['smtp_host'] = 'ssl://smtp.googlemail.com';
			$config['smtp_port'] = 465;
			$config['smtp_user'] = stripslashes('hcmpkenya.test@gmail.com');
			$config['smtp_pass'] = stripslashes('verystrongpassword');
			ini_set("SMTP", "ssl://smtp.gmail.com");
			ini_set("smtp_port", "465");
			ini_set("max_execution_time", "50000");

			$emails = "karsanrichard@gmail.com";

		//pulling emails from the DB
			$this -> load -> library('email', $config);
			$path = $_SERVER["DOCUMENT_ROOT"];
			$file = $path . "/HCMPv2/application/assets/pdf/" . $report_name;
		//puts the path where the pdf's are stored

			foreach ($emails as $email) {
				$this -> email -> attach($file);
				$address = $email['email'];
				$this -> email -> set_newline("\r\n");
				$this -> email -> from('hcmpkenya.test@gmail.com', "HCMP Kenya");
			//user variable displays current user logged in from sessions
				$this -> email -> to("$address");
				$this -> email -> subject('Weekly HCMP Report ');
				$this -> email -> message('Please find the Report Attached');

			//success message else show the error
				if ($this -> email -> send()) {
					echo 'Your email was sent, successfully to ' . $address . '<br/>';
				//unlink($file);
					$this -> email -> clear(TRUE);

				} else {
					show_error($this -> email -> print_debugger());
				}

			}
			ob_end_flush();
			unlink($file);
		//delete the attachment after sending to avoid clog up of pdf'ss
		}
public function new_weekly_usage($year=null,$month=null){
	if(!isset($month)){
		$month = date('m');
	}
	if(!isset($year)){
		$year = date('Y');
	}
	
	$full_date = $year.'-'.$month.'-01';
	$month_text = date('F',strtotime($full_date));		
	$data['year'] = $year;
	$data['month'] = $month;
	$data['month_text'] = $month_text;
	$data['title'] = "System Usage";
	$data['content_view'] = "Admin/new_log_summary_v";
	$data['banner_text'] = "System Usage";	
	$start_date = $year.'-'.$month.'-01';
	$end_date = $year.'-'.$month.'-31';		
	$logged_within_month = Facilities::get_facilities_logged_in_month($start_date,$end_date);
	$issued_within_month = Facilities::get_facilities_issued_in_month($start_date,$end_date);
	$not_logged_within_month = Facilities::get_facilities_not_in_month($start_date,$end_date);
	$not_issued_within_month = Facilities::get_facilities_not_in_month($start_date,$end_date,null,null,'issued');
	$logged_within_month_4 = Facilities::get_facilities_count($start_date,$end_date,4);
	$issued_within_month_4 = Facilities::get_facilities_count($start_date,$end_date,4,null,null,'issued');

	$data['monthly_logs'] =array('logged_in'=>$logged_within_month,'not_logged_in'=>$not_logged_within_month,'logged_in_count'=>$logged_within_month_4,'issued_within_month'=>$issued_within_month,'issued_count'=>$issued_within_month_4,'not_issued'=>$not_issued_within_month);

	$this -> load -> view("shared_files/template/dashboard_v", $data);
}
public function log_summary_weekly_view(){
			$time=date('M , d Y');
			$active_facilities = Facilities::getAll_();
		// echo "<pre>";print_r($active_facilities);echo "</pre>";exit;
			$last_seen = Facilities::get_facility_data_specific(NULL,$county_id,$district_id,$facility_code,'all');
			$last_issued = Facilities::get_facility_data_specific('last_issued',$county_id,$district_id,$facility_code,'all');
			$last_ordered = Facilities::get_facility_data_specific('last_ordered',$county_id,$district_id,$facility_code,'all');
			$decommissioned = Facilities::get_facility_data_specific('last_decommissioned',$county_id,$district_id,$facility_code,'all');
			$redistributed = Facilities::get_facility_data_specific('last_redistributed',$county_id,$district_id,$facility_code,'all');
			$added_stock = Facilities::get_facility_data_specific('last_added_stock',$county_id,$district_id,$facility_code,'all');
		// $all_faciliteis = Facilities::getAll_();

		// echo "<pre>";print_r($all_faciliteis);echo "</pre>";exit;
			$final_array = array();
			$last_seen_count = count($last_seen);
			$last_issued_count = count($last_issued);
			$last_ordered_count = count($last_ordered);
			$decommissioned_count = count($decommissioned);
			$redistributed_count = count($redistributed);
			$added_stock_count = count($added_stock);

		// echo "<pre>".$highest;exit;
		// echo "<pre>";print_r($last_seen);echo "</pre>";
		// echo "END OF LAST SEEN";
		// echo "<pre>";print_r($last_issued);echo "</pre>";
		// echo "END OF LAST ISSUED";
		// echo "<pre>";print_r($last_ordered);echo "</pre>";
		// echo "END OF LAST ORDERED";
		// echo "<pre>";print_r($decommissioned);echo "</pre>";
		// echo "END OF LAST DECOMMISSSIONED";
		// echo "<pre>";print_r($redistributed);echo "</pre>";
		// echo "END OF LAST REDISTRIBUTED";
		// echo "<pre>";print_r($added_stock);echo "</pre>";
		// echo "END OF LAST ADDED STOCK";
		// exit;


			foreach ($active_facilities as $a_c) { 
				$final_array[] = array(
					'Facility Name' => $a_c['facility_name'], 
					'Facility Code' => $a_c['facility_code'],
					'County' => $a_c['county'],
					'Sub-County' => $a_c['subcounty']
					);
		}//active_facilities foreach

		// $final_array = array_unique($final_array,SORT_REGULAR);
		// $final_array = array_map("unserialize", array_unique(array_map("serialize", $final_array)));
		// echo "<pre>";print_r($final_array);echo "</pre>";exit;

		$final_array_count = count($final_array);
		$last_seen_time = NULL;
		$last_issued_time = NULL;
		$last_order_time = NULL;
		$last_deccommissioned_time = NULL;
		$last_redistributed_time = NULL;
		$last_added_stock_time = NULL;

		foreach ($final_array as $keyy => $value) {
			foreach ($last_seen as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_seen[$key]['facility_code']){
					if ($last_seen[$key]['last_seen'] > $last_seen_time) {
						$last_seen_time = $last_seen[$key]['last_seen'];
						$days_last_seen = $last_seen[$key]['difference_in_days'];
							// echo "<pre>".$last_seen_time;
					}
					$final_array[$keyy]['Date Last Seen'] = $last_seen_time;
					$final_array[$keyy]['Days From Last Seen'] = $days_last_seen;
							// echo "<pre>".$last_seen_time;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_seen_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		// echo "<pre>";print_r($final_array);echo "</pre>";exit;

		//last issued time
		foreach ($final_array as $keyy => $value) {
			foreach ($last_issued as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_issued[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_issued[$key]['last_seen'] > $last_issued_time) {
						$last_issued_time = $last_issued[$key]['last_seen'];
						$days = $last_issued[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Issued'] = $last_issued_time;
					$final_array[$keyy]['Days From Last Issue'] = $days;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_issued_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}
		

		//last ordered
		foreach ($final_array as $keyy => $value) {
			foreach ($last_ordered as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_ordered[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_ordered[$key]['last_seen'] > $last_order_time) {
						$last_order_time = $last_ordered[$key]['last_seen'];
						$days_last_ordered = $last_ordered[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Ordered'] = $last_order_time;
					$final_array[$keyy]['Days From Last Order'] = $days_last_ordered;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_order_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		foreach ($final_array as $keyy => $value) {
			foreach ($decommissioned as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $decommissioned[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($decommissioned[$key]['last_seen'] > $last_deccommissioned_time) {
						$last_deccommissioned_time = $decommissioned[$key]['last_seen'];
						$days_last_decommissioned = $decommissioned[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Decommissioned'] = $last_deccommissioned_time;
					$final_array[$keyy]['Days From Last Decommissioned'] = $days_last_decommissioned;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_deccommissioned_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		
		foreach ($final_array as $keyy => $value) {
			foreach ($redistributed as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $redistributed[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($redistributed[$key]['last_seen'] > $last_redistributed_time) {
						$last_redistributed_time = $redistributed[$key]['last_seen'];
						$days_last_redistributed = $redistributed[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Redistributed'] = $last_redistributed_time;
					$final_array[$keyy]['Days From Last Redistributed'] = $days_last_redistributed;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_redistributed_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		foreach ($final_array as $keyy => $value) {
			foreach ($added_stock as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $added_stock[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($added_stock[$key]['last_seen'] > $last_added_stock_time) {
						$last_added_stock_time = $added_stock[$key]['last_seen'];
						$days_last_added_stock = $added_stock[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Received Order'] = $last_added_stock_time;
					$final_array[$keyy]['Days From Last Received Order'] = $days_last_added_stock;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_added_stock_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}
		


		// echo "<pre>";print_r($final_array);echo "</pre>";exit;
		// echo $last_order_time;
		// exit;

		// echo "<pre>";print_r($final_array_count);echo "</pre>";exit;

		$row_data = array();
		$counterrrr = 0;
		foreach ($final_array as $facility) :
			// echo "<pre>". $counterrrr . "</pre>";
		// $counterrrr = $counterrrr + 1;
			//random code to allow for commit
			$issue_date = (isset($facility['Date Last Issued'])) ? date('Y-m-d', strtotime($facility['Date Last Issued'])) : "No Data Available";
		$last_seen = (isset($facility['Date Last Seen'])) ? date('Y-m-d', strtotime($facility['Date Last Seen'])) : "No Data Available";
		$redistribution = (isset($facility['Date Last Redistributed'])) ? date('Y-m-d', strtotime($facility['Date Last Redistributed'])) : "No Data Available";
		$order_date = (isset($facility['Date Last Ordered'])) ? date('Y-m-d', strtotime($facility['Date Last Ordered'])) : "No Data Available";
		$decommission_date = (isset($facility['Date Last Decommissioned'])) ? date('Y-m-d', strtotime($facility['Date Last Decommissioned'])) : "No Data Available";
		$date_order = (isset($facility['Date Last Received Order'])) ? date('Y-m-d', strtotime($facility['Date Last Received Order'])) : "No Data Available";

		$days_from_last_seen = isset($facility['Days From Last Seen'])?$facility['Days From Last Seen']:'    -    ';
		$days_from_last_issued = isset($facility['Days From Last Issue'])?$facility['Days From Last Issue']:'  -    ';
		$days_from_last_redist = isset($facility['Days From Last Redistributed'])?$facility['Days From Last Redistributed']:'    -    ';
		$days_from_last_ordered = isset($facility['Days From Last Order'])?$facility['Days From Last Order']:'    -  ';
		$decomissioned_days = isset($facility['Days From Last Decommissioned'])?$facility['Days From Last Decommissioned']:'    -    ';
		$days_from_last_recieved = isset($facility['Days From Last Received Order'])?$facility['Days From Last Received Order']:'    -    ';

		array_push($row_data, array($facility['Facility Name'], 
			$facility['Facility Code'], 
			$facility['Sub-County'],
			$facility['County'], 
			$last_seen, 
			$days_from_last_seen, 
			$issue_date, 
			$days_from_last_issued, 
			$redistribution, 
			$days_from_last_redist, 
			$order_date, 
			$days_from_last_ordered, 
			$decommission_date, 
			$decomissioned_days, 
			$date_order, 
			$days_from_last_recieved));

		endforeach;
		$excel_data = array();
			// $excel_data = array('doc_creator' => 'HCMP ', 'doc_title' => 'System Usage Breakdown ', 'file_name' => 'system usage breakdown');
		$excel_data = array('doc_creator' => 'HCMP-Kenya', 'doc_title' => 'HCMP_Facility_Activity_Log_Summary ', 'file_name' => 'HCMP_Facility_Activity_Log_Summary_as_at_'.$time);
		// $column_data = array(
		// 	"Facility Name", 
		// 	"Facility Code", 
		// 	"Sub County", 
		// 	"County",  
		// 	"Date Last Logged In", 
		// 	"Days From Last Log In", 
		// 	"Date Last Issued", 
		// 	"Days From Last Issue", 
		// 	"Date Last Redistributed", 
		// 	"Days From Last Redistribution", 
		// 	"Date Last Ordered", 
		// 	"Days From Last Order", 
		// 	"Date Last Decommissioned", 
		// 	"Days From Last Decommission", 
		// 	"Date Last Received Order", 
		// 	"Days From Added Stock");
		$column_data = array(
			"Facility Name", 
			"Facility Code", 
			"Sub County", 
			"County",  
			"Date Last Logged In", 
			"Days From Last Log In", 
			"Date Last Issued", 
			"Days From Last Issue", 
			"Date Last Redistributed", 
			"Days From Last Redistribution", 
			"Date Last Ordered", 
			"Days From Last Order", 
			"Date Last Decommissioned", 
			"Days From Last Decommission", 
			"Date Last Received Order", 
			"Days From Added Stock");
		$data['column_data'] = $column_data;
		$data['row_data'] = $row_data;
		$data['title'] = "System Usage";
		$data['content_view'] = "Admin/log_summary_v";
		$data['banner_text'] = "System Usage";


		$this -> load -> view("shared_files/template/dashboard_v", $data);
		// echo 'This '.$res;exit;
		
		
						// $handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
						// $subject = "Weekly Log Summary as at ".$time;

						// // $email_address = "smutheu@clintonhealthaccess.org,jaynerawz@gmail.com,karsanrichard@gmail.com";
      //                   $email_address = "ttunduny@gmail.com";
      //                   //$bcc = "";
						// $this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);


		
	}
	public function log_summary_weekly($county_id = NULL,$district_id = NULL,$facility_code = NULL){//Karsan
		$time=date('M , d Y');
		$time_file= $time.'_'.date('h').'_'.date('i').'_'.date('s');
		// echo "$time_file";die;

		$active_facilities = Facilities::getAll_($county_id,$district_id);
		// echo "<pre>";print_r($active_facilities);echo "</pre>";exit;
		$last_seen = Facilities::get_facility_data_specific(NULL,$county_id,$district_id,$facility_code,'all');
		$last_issued = Facilities::get_facility_data_specific('last_issued',$county_id,$district_id,$facility_code,'all');
		$last_ordered = Facilities::get_facility_data_specific('last_ordered',$county_id,$district_id,$facility_code,'all');
		$decommissioned = Facilities::get_facility_data_specific('last_decommissioned',$county_id,$district_id,$facility_code,'all');
		$redistributed = Facilities::get_facility_data_specific('last_redistributed',$county_id,$district_id,$facility_code,'all');
		$added_stock = Facilities::get_facility_data_specific('last_added_stock',$county_id,$district_id,$facility_code,'all');
		// $all_faciliteis = Facilities::getAll_();

		// echo "<pre>";print_r($all_faciliteis);echo "</pre>";exit;
		$final_array = array();
		$last_seen_count = count($last_seen);
		$last_issued_count = count($last_issued);
		$last_ordered_count = count($last_ordered);
		$decommissioned_count = count($decommissioned);
		$redistributed_count = count($redistributed);
		$added_stock_count = count($added_stock);




		foreach ($active_facilities as $a_c) { 
			$final_array[] = array(
				'Facility Name' => $a_c['facility_name'], 
				'Facility Code' => $a_c['facility_code'],
				'County' => $a_c['county'],
				'Sub-County' => $a_c['subcounty']
				);
		}//active_facilities foreach

		
		$final_array_count = count($final_array);


		$last_seen_time = NULL;
		$last_issued_time = NULL;
		$last_order_time = NULL;
		$last_deccommissioned_time = NULL;
		$last_redistributed_time = NULL;
		$last_added_stock_time = NULL;

		foreach ($final_array as $keyy => $value) {
			foreach ($last_seen as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_seen[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_seen[$key]['last_seen'] > $last_seen_time) {
						$last_seen_time = $last_seen[$key]['last_seen'];
						$days_last_seen = $last_seen[$key]['difference_in_days'];
							// echo "<pre>".$last_seen_time;
					}
					$final_array[$keyy]['Date Last Seen'] = $last_seen_time;
					$final_array[$keyy]['Days From Last Seen'] = $days_last_seen;
							// echo "<pre>".$last_seen_time;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_seen_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		// echo "<pre>";print_r($final_array);echo "</pre>";exit;

		//last issued time
		foreach ($final_array as $keyy => $value) {
			foreach ($last_issued as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_issued[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_issued[$key]['last_seen'] > $last_issued_time) {
						$last_issued_time = $last_issued[$key]['last_seen'];
						$days = $last_issued[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Issued'] = $last_issued_time;
					$final_array[$keyy]['Days From Last Issue'] = $days;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_issued_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}
		

		//last ordered
		foreach ($final_array as $keyy => $value) {
			foreach ($last_ordered as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_ordered[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_ordered[$key]['last_seen'] > $last_order_time) {
						$last_order_time = $last_ordered[$key]['last_seen'];
						$days_last_ordered = $last_ordered[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Ordered'] = $last_order_time;
					$final_array[$keyy]['Days From Last Order'] = $days_last_ordered;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_order_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		foreach ($final_array as $keyy => $value) {
			foreach ($decommissioned as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $decommissioned[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($decommissioned[$key]['last_seen'] > $last_deccommissioned_time) {
						$last_deccommissioned_time = $decommissioned[$key]['last_seen'];
						$days_last_decommissioned = $decommissioned[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Decommissioned'] = $last_deccommissioned_time;
					$final_array[$keyy]['Days From Last Decommissioned'] = $days_last_decommissioned;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_deccommissioned_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		
		foreach ($final_array as $keyy => $value) {
			foreach ($redistributed as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $redistributed[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($redistributed[$key]['last_seen'] > $last_redistributed_time) {
						$last_redistributed_time = $redistributed[$key]['last_seen'];
						$days_last_redistributed = $redistributed[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Redistributed'] = $last_redistributed_time;
					$final_array[$keyy]['Days From Last Redistributed'] = $days_last_redistributed;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_redistributed_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		foreach ($final_array as $keyy => $value) {
			foreach ($added_stock as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $added_stock[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($added_stock[$key]['last_seen'] > $last_added_stock_time) {
						$last_added_stock_time = $added_stock[$key]['last_seen'];
						$days_last_added_stock = $added_stock[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Received Order'] = $last_added_stock_time;
					$final_array[$keyy]['Days From Last Received Order'] = $days_last_added_stock;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_added_stock_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}
		
		// echo "<pre>";print_r($final_array);exit;

		$row_data = array();
		$counterrrr = 0;
		foreach ($final_array as $facility) :
			// echo "<pre>". $counterrrr . "</pre>";
		// $counterrrr = $counterrrr + 1;
			//random code to allow for commit
			$issue_date = (isset($facility['Date Last Issued'])) ? date('Y-m-d', strtotime($facility['Date Last Issued'])) : "No Data Available";
		$last_seen = (isset($facility['Date Last Seen'])) ? date('Y-m-d', strtotime($facility['Date Last Seen'])) : "No Data Available";
		$redistribution = (isset($facility['Date Last Redistributed'])) ? date('Y-m-d', strtotime($facility['Date Last Redistributed'])) : "No Data Available";
		$order_date = (isset($facility['Date Last Ordered'])) ? date('Y-m-d', strtotime($facility['Date Last Ordered'])) : "No Data Available";
		$decommission_date = (isset($facility['Date Last Decommissioned'])) ? date('Y-m-d', strtotime($facility['Date Last Decommissioned'])) : "No Data Available";
		$date_order = (isset($facility['Date Last Received Order'])) ? date('Y-m-d', strtotime($facility['Date Last Received Order'])) : "No Data Available";

		$days_from_last_seen = isset($facility['Days From Last Seen'])?$facility['Days From Last Seen']:'    -    ';
		$days_from_last_issued = isset($facility['Days From Last Issue'])?$facility['Days From Last Issue']:'  -    ';
		$days_from_last_redist = isset($facility['Days From Last Redistributed'])?$facility['Days From Last Redistributed']:'    -    ';
		$days_from_last_ordered = isset($facility['Days From Last Order'])?$facility['Days From Last Order']:'    -  ';
		$decomissioned_days = isset($facility['Days From Last Decommissioned'])?$facility['Days From Last Decommissioned']:'    -    ';
		$days_from_last_recieved = isset($facility['Days From Last Received Order'])?$facility['Days From Last Received Order']:'    -    ';

		array_push($row_data, array($facility['Facility Name'], 
			$facility['Facility Code'], 
			$facility['Sub-County'],
			$facility['County'], 
			$last_seen, 
			$days_from_last_seen, 
			$issue_date, 
			$days_from_last_issued, 
			$redistribution, 
			$days_from_last_redist, 
			$order_date, 
			$days_from_last_ordered, 
			$decommission_date, 
			$decomissioned_days, 
			$date_order, 
			$days_from_last_recieved));

		endforeach;
		$excel_data = array();
			// $excel_data = array('doc_creator' => 'HCMP ', 'doc_title' => 'System Usage Breakdown ', 'file_name' => 'system usage breakdown');
		$excel_data = array('doc_creator' => 'HCMP-Kenya', 'doc_title' => 'HCMP_Facility_Activity_Log_Summary ', 'file_name' => 'HCMP_Facility_Activity_Log_Summary_as_at_'.$time_file);
		$column_data = array(
			"Facility Name", 
			"Facility Code", 
			"Sub County", 
			"County",  
			"Date Last Logged In", 
			"Days From Last Log In", 
			"Date Last Issued", 
			"Days From Last Issue", 
			"Date Last Redistributed", 
			"Days From Last Redistribution", 
			"Date Last Ordered", 
			"Days From Last Order", 
			"Date Last Decommissioned", 
			"Days From Last Decommission", 
			"Date Last Received Order", 
			"Days From Last Stock Addition");
		$excel_data['column_data'] = $column_data;
		$excel_data['row_data'] = $row_data;
		$excel_data['report_type']='Log Summary';
		// echo "<pre>";print_r($excel_data);echo "</pre>";exit;
		$res = $this -> hcmp_functions -> create_excel($excel_data);

		// echo 'This '.$res;exit;
		
		$message ='';	
		$message.="<style> table {
			border-collapse: collapse; 
		}td,th{
			padding: 12px;
			text-align:center;
		}

		*{margin:0;padding:0}*{font-family:'Helvetica Neue',Helvetica,Helvetica,Arial,sans-serif}img{max-width:100%}.collapse{padding:0}body{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100%!important;height:100%}a{color:#2BA6CB}.btn{text-decoration:none;color:#FFF;background-color:#666;padding:10px 16px;font-weight:700;margin-right:10px;text-align:center;cursor:pointer;display:inline-block}p.callout{padding:15px;background-color:#ECF8FF;margin-bottom:15px}.callout a{font-weight:700;color:#2BA6CB}table.social{background-color:#ebebeb}.social .soc-btn{padding:3px 7px;font-size:12px;margin-bottom:10px;text-decoration:none;color:#FFF;font-weight:700;display:block;text-align:center}a.fb{background-color:#3B5998!important}a.tw{background-color:#1daced!important}a.gp{background-color:#DB4A39!important}a.ms{background-color:#000!important}.sidebar .soc-btn{display:block;width:100%}table.head-wrap{width:100%}.header.container table td.logo{padding:15px}.header.container table td.label{padding:15px 15px 15px 0}table.body-wrap{width:100%}table.footer-wrap{width:100%;clear:both!important}.footer-wrap .container td.content p{border-top:1px solid #d7d7d7;padding-top:15px;font-size:9px;font-weight:500}h1,h2,h3,h4,h5,h6{font-family:HelveticaNeue-Light,'Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;line-height:1.1;margin-bottom:15px;color:#000}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small{font-size:60%;color:#6f6f6f;line-height:0;text-transform:none}h1{font-weight:200;font-size:44px}h2{font-weight:200;font-size:37px}h3{font-weight:500;font-size:27px}h4{font-weight:500;font-size:23px}h5{font-weight:900;font-size:17px}h6{font-weight:900;font-size:14px;text-transform:uppercase;color:#444}.collapse{margin:0!important}p,ul{margin-bottom:10px;font-weight:400;font-size:14px;line-height:1.6}p.lead{font-size:17px}p.last{margin-bottom:0}ul li{margin-left:5px;list-style-position:inside}ul.sidebar{background:#ebebeb;display:block;list-style-type:none}ul.sidebar li{display:block;margin:0}ul.sidebar li a{text-decoration:none;color:#666;padding:10px 16px;cursor:pointer;border-bottom:1px solid #777;border-top:1px solid #FFF;display:block;margin:0}ul.sidebar li a.last{border-bottom-width:0}ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p{margin-bottom:0!important}.container{display:block!important;max-width:100%!important;margin:0 auto!important;clear:both!important}.content{padding:15px;max-width:80%px;margin:0 auto;display:block}.content table{width:100%}.column{width:300px;float:left}.column tr td{padding:15px}.column-wrap{padding:0!important;margin:0 auto;max-width:600px!important}.column table{width:100%}.social .column{width:280px;min-width:279px;float:left}.clear{display:block;clear:both}@media only screen and (max-width:600px){a[class=btn]{display:block!important;margin-bottom:10px!important;background-image:none!important;margin-right:0!important}div[class=column]{width:auto!important;float:none!important}table.social div[class=column]{width:auto!important}}</style>";
		$message .='
		<tr>
			<td colspan="12">
			</tr>
		</tbody>
		</table>'; 
		$message.="<!-- BODY -->
		<table class='body-wrap'>
			<tr>
				<td></td>
				<td class='container' bgcolor='#FFFFFF'>

					<div class='content'>
						<table>
							<tr>
								<td>

									<p class='lead'>Find attached a summary of Facility Activity Log, as at $time</p>

									<table class='social' width='100%'>
										<tr>
											<td>

												<!-- column 1 -->
												<table align='left' class='column'>

												</table><!-- /column 1 -->	

												<!-- column 2 -->
												<table align='left' class='column'>
													<tr>

													</tr>
												</table><!-- /column 2 -->

												<span class='clear'></span>	

											</td>
										</tr>
									</table><!-- /social & contact -->

								</td>
							</tr>
						</table>
					</div><!-- /content -->

				</td>
				<td></td>
			</tr>
		</table><!-- /BODY -->";	

		$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
		$subject = "System Usage as at ".$time;

		$email_address = "smutheu@clintonhealthaccess.org,karsanrichard@gmail.com,ttunduny@gmail.com,teddyodera@gmail.com";
		// $email_address = "karsanrichard@gmail.com";
							// $email_address = "karsanrichard@gmail.com,ttunduny@gmail.com";
	                        // $email_address = "ttunduny@gmail.com";
	                        //$bcc = "";
		$status = $this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
		// echo "I work till here";exit;
		// echo "<pre>";print_r($status);exit;
		redirect('sms/new_weekly_usage');
   }


   public function send_system_usage_specific($county_id = NULL,$district_id = NULL,$facility_code = NULL,$listing_email_address = NULL){//Karsan
		$time=date('M , d Y');
		$county_name = counties::get_county_name($county_id);
		$district_name = districts::get_district_name_($district_id);

		$cp_email_query = "SELECT * FROM email_listing_new WHERE county = $county_id AND usertype = 10";
		$cp_emails = $this->db->query($cp_email_query)->result_array();

		$scp_email_query = "SELECT * FROM email_listing_new WHERE county = $county_id AND usertype = 3";
		$scp_emails = $this->db->query($scp_email_query)->result_array();

		// echo "<pre>";print_r($cp_emails);exit;

		//CP
		$cp_emails_str = '';
		foreach ($cp_emails as $emails => $value) {
			$cp_emails_str = $cp_emails_str.','.$value['email'];
			// echo "<pre>";print_r($value['email']);exit;
		}
		$cp_emails_str = ltrim($cp_emails_str,',');

		//SCP
		$scp_emails_str = '';
		foreach ($scp_emails as $emails => $value) {
			$scp_emails_str = $scp_emails_str.','.$value['email'];
			// echo "<pre>";print_r($value['email']);exit;
		}
		$scp_emails_str = ltrim($scp_emails_str,',');

		// echo $scp_emails_str;exit;

		// echo "<pre>";print_r($scp_emails);exit;

		// echo "<pre>";print_r($district_name);
		// echo "<pre>OVER ";

		$appended = !empty($district_name)? $district_name['district']." Subcounty" :$county_name['county']." County";
		$excel_title = 'HCMP facility activity log summary for '.$appended;
		$email_subject = 'HCMP System usage report for '.$appended.' as at '.$time;
		// echo $county_name.' '.$district_name;
		// echo $excel_title;exit;

		$active_facilities = Facilities::getAll_($county_id,$district_id);
		// echo "<pre>";print_r($active_facilities);echo "</pre>";exit;
		$last_seen = Facilities::get_facility_data_specific(NULL,$county_id,$district_id,$facility_code,'all');
		$last_issued = Facilities::get_facility_data_specific('last_issued',$county_id,$district_id,$facility_code,'all');
		$last_ordered = Facilities::get_facility_data_specific('last_ordered',$county_id,$district_id,$facility_code,'all');
		$decommissioned = Facilities::get_facility_data_specific('last_decommissioned',$county_id,$district_id,$facility_code,'all');
		$redistributed = Facilities::get_facility_data_specific('last_redistributed',$county_id,$district_id,$facility_code,'all');
		$added_stock = Facilities::get_facility_data_specific('last_added_stock',$county_id,$district_id,$facility_code,'all');
		// $all_faciliteis = Facilities::getAll_();

		// echo "<pre>";print_r($active_facilities);echo "</pre>";exit;
		$facility_count = (empty($active_facilities))? NULL : count($active_facilities);

		$final_array = array();
		$last_seen_count = count($last_seen);
		$last_issued_count = count($last_issued);
		$last_ordered_count = count($last_ordered);
		$decommissioned_count = count($decommissioned);
		$redistributed_count = count($redistributed);
		$added_stock_count = count($added_stock);




		foreach ($active_facilities as $a_c) { 
			$final_array[] = array(
				'Facility Name' => $a_c['facility_name'], 
				'Facility Code' => $a_c['facility_code'],
				'County' => $a_c['county'],
				'Sub-County' => $a_c['subcounty']
				);
		}//active_facilities foreach

		
		$final_array_count = count($final_array);


		$last_seen_time = NULL;
		$last_issued_time = NULL;
		$last_order_time = NULL;
		$last_deccommissioned_time = NULL;
		$last_redistributed_time = NULL;
		$last_added_stock_time = NULL;

		foreach ($final_array as $keyy => $value) {
			foreach ($last_seen as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_seen[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_seen[$key]['last_seen'] > $last_seen_time) {
						$last_seen_time = $last_seen[$key]['last_seen'];
						$days_last_seen = $last_seen[$key]['difference_in_days'];
							// echo "<pre>".$last_seen_time;
					}
					$final_array[$keyy]['Date Last Seen'] = $last_seen_time;
					$final_array[$keyy]['Days From Last Seen'] = $days_last_seen;
							// echo "<pre>".$last_seen_time;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_seen_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		// echo "<pre>";print_r($final_array);echo "</pre>";exit;

		//last issued time
		foreach ($final_array as $keyy => $value) {
			foreach ($last_issued as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_issued[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_issued[$key]['last_seen'] > $last_issued_time) {
						$last_issued_time = $last_issued[$key]['last_seen'];
						$days = $last_issued[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Issued'] = $last_issued_time;
					$final_array[$keyy]['Days From Last Issue'] = $days;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_issued_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}
		

		//last ordered
		foreach ($final_array as $keyy => $value) {
			foreach ($last_ordered as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $last_ordered[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($last_ordered[$key]['last_seen'] > $last_order_time) {
						$last_order_time = $last_ordered[$key]['last_seen'];
						$days_last_ordered = $last_ordered[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Ordered'] = $last_order_time;
					$final_array[$keyy]['Days From Last Order'] = $days_last_ordered;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_order_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		foreach ($final_array as $keyy => $value) {
			foreach ($decommissioned as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $decommissioned[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($decommissioned[$key]['last_seen'] > $last_deccommissioned_time) {
						$last_deccommissioned_time = $decommissioned[$key]['last_seen'];
						$days_last_decommissioned = $decommissioned[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Decommissioned'] = $last_deccommissioned_time;
					$final_array[$keyy]['Days From Last Decommissioned'] = $days_last_decommissioned;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_deccommissioned_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		
		foreach ($final_array as $keyy => $value) {
			foreach ($redistributed as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $redistributed[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($redistributed[$key]['last_seen'] > $last_redistributed_time) {
						$last_redistributed_time = $redistributed[$key]['last_seen'];
						$days_last_redistributed = $redistributed[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Redistributed'] = $last_redistributed_time;
					$final_array[$keyy]['Days From Last Redistributed'] = $days_last_redistributed;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_redistributed_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}

		foreach ($final_array as $keyy => $value) {
			foreach ($added_stock as $key => $value) {
				if ($final_array[$keyy]['Facility Code'] == $added_stock[$key]['facility_code']){
						// echo "<pre>".$last_ordered[$j]['last_seen']."	".$last_ordered[$j]['facility_code'];
					if ($added_stock[$key]['last_seen'] > $last_added_stock_time) {
						$last_added_stock_time = $added_stock[$key]['last_seen'];
						$days_last_added_stock = $added_stock[$key]['difference_in_days'];
							// echo "<pre>".$last_order_time;
					}
					$final_array[$keyy]['Date Last Received Order'] = $last_added_stock_time;
					$final_array[$keyy]['Days From Last Received Order'] = $days_last_added_stock;
			        	// $final_array[$i]['Days From Last Seen'] = abs($last_seen_time - $now);
					$last_added_stock_time = NULL;
			        }//end of facility code if

			}//end of last seen foreach
		}
		
		// echo "<pre>";print_r($final_array);exit;

		$row_data = array();
		$counterrrr = 0;
		foreach ($final_array as $facility) :
			// echo "<pre>". $counterrrr . "</pre>";
		// $counterrrr = $counterrrr + 1;
			//random code to allow for commit
			$issue_date = (isset($facility['Date Last Issued'])) ? date('Y-m-d', strtotime($facility['Date Last Issued'])) : "No Data Available";
		$last_seen = (isset($facility['Date Last Seen'])) ? date('Y-m-d', strtotime($facility['Date Last Seen'])) : "No Data Available";
		$redistribution = (isset($facility['Date Last Redistributed'])) ? date('Y-m-d', strtotime($facility['Date Last Redistributed'])) : "No Data Available";
		$order_date = (isset($facility['Date Last Ordered'])) ? date('Y-m-d', strtotime($facility['Date Last Ordered'])) : "No Data Available";
		$decommission_date = (isset($facility['Date Last Decommissioned'])) ? date('Y-m-d', strtotime($facility['Date Last Decommissioned'])) : "No Data Available";
		$date_order = (isset($facility['Date Last Received Order'])) ? date('Y-m-d', strtotime($facility['Date Last Received Order'])) : "No Data Available";

		$days_from_last_seen = isset($facility['Days From Last Seen'])?$facility['Days From Last Seen']:'    -    ';
		$days_from_last_issued = isset($facility['Days From Last Issue'])?$facility['Days From Last Issue']:'  -    ';
		$days_from_last_redist = isset($facility['Days From Last Redistributed'])?$facility['Days From Last Redistributed']:'    -    ';
		$days_from_last_ordered = isset($facility['Days From Last Order'])?$facility['Days From Last Order']:'    -  ';
		$decomissioned_days = isset($facility['Days From Last Decommissioned'])?$facility['Days From Last Decommissioned']:'    -    ';
		$days_from_last_recieved = isset($facility['Days From Last Received Order'])?$facility['Days From Last Received Order']:'    -    ';

		array_push($row_data, array($facility['Facility Name'], 
			$facility['Facility Code'], 
			$facility['Sub-County'],
			$facility['County'], 
			$last_seen, 
			$days_from_last_seen, 
			$issue_date, 
			$days_from_last_issued, 
			$redistribution, 
			$days_from_last_redist, 
			$order_date, 
			$days_from_last_ordered, 
			$decommission_date, 
			$decomissioned_days, 
			$date_order, 
			$days_from_last_recieved));


		endforeach;

		$excel_data = array();
			// $excel_data = array('doc_creator' => 'HCMP ', 'doc_title' => 'System Usage Breakdown ', 'file_name' => 'system usage breakdown');
		$excel_data = array('doc_creator' => 'HCMP-Kenya', 'doc_title' => $excel_title, 'file_name' => 'HCMP_Facility_Activity_Log_Summary_as_at_'.$time);
		$column_data = array(
			"Facility Name", 
			"Facility Code", 
			"Sub County", 
			"County",  
			"Date Last Logged In", 
			"Days From Last Log In", 
			"Date Last Issued", 
			"Days From Last Issue", 
			"Date Last Redistributed", 
			"Days From Last Redistribution", 
			"Date Last Ordered", 
			"Days From Last Order", 
			"Date Last Decommissioned", 
			"Days From Last Decommission", 
			"Date Last Received Order", 
			"Days From Last Stock Addition");
		$excel_data['column_data'] = $column_data;
		$excel_data['row_data'] = $row_data;
		$excel_data['report_type']='Log Summary';
		// echo "<pre>";print_r($excel_data);echo "</pre>";exit;
		$res = $this -> hcmp_functions -> create_excel($excel_data);

		// echo 'This '.$res;exit;
		
		$message ='';	
		$message.="<style> table {
			border-collapse: collapse; 
		}td,th{
			padding: 12px;
			text-align:center;
		}

		*{margin:0;padding:0}*{font-family:'Helvetica Neue',Helvetica,Helvetica,Arial,sans-serif}img{max-width:100%}.collapse{padding:0}body{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100%!important;height:100%}a{color:#2BA6CB}.btn{text-decoration:none;color:#FFF;background-color:#666;padding:10px 16px;font-weight:700;margin-right:10px;text-align:center;cursor:pointer;display:inline-block}p.callout{padding:15px;background-color:#ECF8FF;margin-bottom:15px}.callout a{font-weight:700;color:#2BA6CB}table.social{background-color:#ebebeb}.social .soc-btn{padding:3px 7px;font-size:12px;margin-bottom:10px;text-decoration:none;color:#FFF;font-weight:700;display:block;text-align:center}a.fb{background-color:#3B5998!important}a.tw{background-color:#1daced!important}a.gp{background-color:#DB4A39!important}a.ms{background-color:#000!important}.sidebar .soc-btn{display:block;width:100%}table.head-wrap{width:100%}.header.container table td.logo{padding:15px}.header.container table td.label{padding:15px 15px 15px 0}table.body-wrap{width:100%}table.footer-wrap{width:100%;clear:both!important}.footer-wrap .container td.content p{border-top:1px solid #d7d7d7;padding-top:15px;font-size:9px;font-weight:500}h1,h2,h3,h4,h5,h6{font-family:HelveticaNeue-Light,'Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;line-height:1.1;margin-bottom:15px;color:#000}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small{font-size:60%;color:#6f6f6f;line-height:0;text-transform:none}h1{font-weight:200;font-size:44px}h2{font-weight:200;font-size:37px}h3{font-weight:500;font-size:27px}h4{font-weight:500;font-size:23px}h5{font-weight:900;font-size:17px}h6{font-weight:900;font-size:14px;text-transform:uppercase;color:#444}.collapse{margin:0!important}p,ul{margin-bottom:10px;font-weight:400;font-size:14px;line-height:1.6}p.lead{font-size:17px}p.last{margin-bottom:0}ul li{margin-left:5px;list-style-position:inside}ul.sidebar{background:#ebebeb;display:block;list-style-type:none}ul.sidebar li{display:block;margin:0}ul.sidebar li a{text-decoration:none;color:#666;padding:10px 16px;cursor:pointer;border-bottom:1px solid #777;border-top:1px solid #FFF;display:block;margin:0}ul.sidebar li a.last{border-bottom-width:0}ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p{margin-bottom:0!important}.container{display:block!important;max-width:100%!important;margin:0 auto!important;clear:both!important}.content{padding:15px;max-width:80%px;margin:0 auto;display:block}.content table{width:100%}.column{width:300px;float:left}.column tr td{padding:15px}.column-wrap{padding:0!important;margin:0 auto;max-width:600px!important}.column table{width:100%}.social .column{width:280px;min-width:279px;float:left}.clear{display:block;clear:both}@media only screen and (max-width:600px){a[class=btn]{display:block!important;margin-bottom:10px!important;background-image:none!important;margin-right:0!important}div[class=column]{width:auto!important;float:none!important}table.social div[class=column]{width:auto!important}}</style>";
		$message .='
		<tr>
			<td colspan="12">
			</tr>
		</tbody>
		</table>'; 
		$message.="<!-- BODY -->
		<table class='body-wrap'>
			<tr>
				<td></td>
				<td class='container' bgcolor='#FFFFFF'>

					<div class='content'>
						<table>
							<tr>
								<td>

									<p class='lead'>Find attached a summary of Facility Activity Log, as at $time</p>

									<table class='social' width='100%'>
										<tr>
											<td>

												<!-- column 1 -->
												<table align='left' class='column'>

												</table><!-- /column 1 -->	

												<!-- column 2 -->
												<table align='left' class='column'>
													<tr>

													</tr>
												</table><!-- /column 2 -->

												<span class='clear'></span>	

											</td>
										</tr>
									</table><!-- /social & contact -->

								</td>
							</tr>
						</table>
					</div><!-- /content -->

				</td>
				<td></td>
			</tr>
		</table><!-- /BODY -->";	

		$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
		// $subject = $excel_title." as at ".$time;

		// $email_address = "smutheu@clintonhealthaccess.org,sethrichard40@gmail.com,ttunduny@gmail.com,teddyodera@gmail.com";
		// $email_address = $listing_email_address;
		$email_address = $cp_emails_str;
		$cc_email = $scp_emails_str;
		$bcc_email = "smutheu@clintonhealthaccess.org,karsanrichard@gmail.com,ttunduny@gmail.com,teddyodera@gmail.com,ronohb@gmail.com,odiwuorybrian@gmail.com,mwakiojoy@gmail.com,kevgithuka@gmail.com,margie.odora@gmail.com,kiganyastephenthua@gmail.com";

		// $email_address = "karsanrichard@gmail.com";
		// $bcc_email = "";
		// $cc_email = "";
		// $email_address = "karsanrichard@gmail.com,ttunduny@gmail.com";
        // $email_address = "ttunduny@gmail.com";
        //$bcc = "";
        if (!empty($facility_count) && $facility_count>0) {
			$status = $this -> hcmp_functions -> send_email($email_address, $message, $email_subject, $handler,$bcc_email,$cc_email);
        }
		// echo "I work till here";exit;
		// echo "<pre>";print_r($status);exit;
		// redirect('sms/new_weekly_usage');
   }

   public function send_sytem_usage()
   {
   	$listing = $this->db->query("SELECT county FROM email_listing_new WHERE usertype = 10 GROUP BY county")->result_array();

   	// echo "<pre>";print_r($listing);exit;
   	foreach ($listing as $list => $value) {
   		# code...
		// echo "<pre>";print_r($value);
		$county_id = $value['county'];
		// $subcounty = $value['sub_county'];
		// $email_address = $value['email'];

		$send_it = $this-> send_system_usage_specific($county_id,NULL,NULL,$email_address);
		// echo "done";exit;
   	}

   	// echo "Whole thing done";exit;
   }//end of send system usage

   public function tester(){
   	$data = $this->db->query("SELECT * FROM email_listing_new");
		echo "<pre>";print_r($data);exit;

   	$var = $this->hcmp_functions->send_system_text("redistribute");
   	echo "<pre>";print_r($var);echo "</pre>";exit;
   }

  
}

