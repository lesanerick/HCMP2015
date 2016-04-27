<?php
/**
 * @author Kariuki
 */
session_start();
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class User extends MY_Controller {

	function __construct() {
		parent::__construct();
		
		$this -> load -> helper(array('form', 'url'));
		$this -> load -> library(array('hcmp_functions', 'form_validation'));
	}


	public function index() {
		$data['title'] = "Login";
		$this -> load -> view("shared_files/login_pages/login_v", $data);
	}

	private function _submit_validate() {

		$this -> form_validation -> set_rules('username', 'Username', 'trim|required|callback_authenticate');

		$this -> form_validation -> set_rules('password', 'Password', 'trim|required');

		$this -> form_validation -> set_message('authenticate', 'Invalid login. Please try again.');

		return $this -> form_validation -> run();

	}

	public function login_submit() {

		$user = new Users();

		$password = $this -> input -> post('password');
		$username = $this -> input -> post('username');
		$returned_user = $user -> login($username, $password);

		//If user successfully logs in, proceed here
		if ($returned_user) {
			//Create basic data to be saved in the session
			$reply = Users::login($username, $password);
			$user_data = $reply -> toArray();

			$access_typeid = $user_data['usertype_id'];
			$fname = $user_data['fname'];
			$user_id = $user_data['id'];
			$lname = $user_data['lname'];
			$district_id = $user_data['district'];
			$facility_id = $user_data['facility'];
			$phone = $user_data['telephone'];
			$user_email = $user_data['email'];
			$county_id = $user_data['county_id'];
			$partner_id = $user_data['partner'];
			$fullname = $fname . ' ' . $lname;
			$banner_name = '';
			$access_level = Access_level::get_access_level_name($access_typeid);

			$user_indicator = $access_level['user_indicator'];
			
			if ($user_indicator  == 'district') :
             //get subcounty name
				$district_name = districts::get_district_name_($district_id);
			$county_name = Counties::get_county_name($county_id);
			$banner_name = $county_name['county']." County".", ".$district_name['district']." Sub-county ";
			$facility_no = Facilities::get_district_facilities_using_hcmp($district_id);
			$facility_count = "Total Facilities Using HCMP in ".$district_name['district']." Subcounty: ".count($facility_no);

			elseif ($user_indicator  == 'county') : 
				
            //get county name
				$county_name = Counties::get_county_name($county_id);
			$banner_name = $county_name['county']." County";
			$facility_no = Counties::get_facilities_in_county($county_id);
			$facility_count = "Total Facilities Using HCMP in ".$county_name['county']." County: ".count($facility_no);
			
			elseif ($user_indicator  == 'facility' || $user_indicator == 'facility_admin' || $user_indicator == 'recovery') :
             //get facility name
				$facility_name = Facilities::get_facility_name2($facility_id);
			$district_name = districts::get_district_name_($district_id);
			$county_name = Counties::get_county_name($county_id);
			$banner_name = $county_name['county']." County, ".$district_name['district']." Sub-county, ".$facility_name['facility_name'];
			$facility_count = NULL;
			endif;
			
			$session_data = array('county_id' => $county_id,'partner_id' => $partner_id, 'phone_no' => $phone,
				'user_email' => $user_email, 'user_id' => $user_id, 'user_indicator' => $user_indicator,
				'fname' => $fname, 'lname' => $lname, 'facility_id' => $facility_id,
				'district_id' => $district_id, 'user_type_id' => 
				$access_typeid,'full_name' => $fullname,
				'banner_name'=>$banner_name,'facility_count'=>$facility_count);

			$this -> session -> set_userdata($session_data);
			
			//get menu items
			$menu_items = Menu::getByUsertype($access_typeid);
			//Create array that will hold all the accessible menus in the session
			$menus = array();
			$menuids = array();
			$counter = 0;
			foreach ($menu_items as $menu_item) {
				$menus[$counter] = array("menu_text" => $menu_item -> menu_text, "menu_url" => $menu_item -> menu_url, "menu_id" => $menu_item -> id, "parent_status" => $menu_item -> parent_status);
				$counter++;
				$menuids[] = $menu_item -> id;
			}
			
			$sub_menus = array();
			foreach ($menuids as $parentid) {

				$sub_items = Sub_menu::getByparent((int)$parentid);

				foreach ($sub_items as $item) {
					$sub_menus[] = array("submenu_text" => $item -> subm_text, "submenu_url" => $item -> subm_url, "menu_id" => $item -> parent_id);

				}

			}
			
			//Save this menus array in the session
			$this -> session -> set_userdata("menus" ,$menus);
			//Save this sub menus array in the session
			$_SESSION["submenus"]= $sub_menus; 
			
			//creating a new log value
			Log::update_log_out_action($this -> session -> userdata('user_id'));
			$facility_code = $this -> session -> userdata('facility_id');
			$u1 = new Log();
			$action = 'Logged In';
			$u1->user_id = $this -> session -> userdata('user_id');			
			$u1->action = $action;			
			$u1->facility_code = ($facility_code==0) ? null : $facility_code;
			$u1->save();
			
			redirect('home');
		} else {
			$data['popup'] = "errorpopup";
			$data['title'] = "Login";
			$this -> load -> view("shared_files/login_pages/login_v", $data);
		}
	}

	public function logout() {

		Log::update_log_out_action($this -> session -> userdata('user_id'));
		$this -> session -> sess_destroy(); session_destroy();
		$this -> clearBrowserCache();

		//Titus
		// $last_run = Sms_listing::get_last_run();
		// echo "<pre>";
		// print_r($last_run->result_array());die;
		$data['title'] = "Login";
		$this -> load -> view("shared_files/login_pages/login_v", $data);
	}
	function clearBrowserCache() {
		
		header ("Expires: ".gmdate("D, d M Y H:i:s", time())." GMT");  
		header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
		header ("Cache-Control: no-cache, must-revalidate");  
		header ("Pragma: no-cache");
		clearstatcache();
		
	} 


	public function forgot_password() {

		$this -> load -> view("shared_files/login_pages/forgotpassword_v");

	}

	public function password_recovery() {

		$email = $_POST['username'];

		//check if user exists and is activated

		$myresult = Users::check_user_exist($email);

		//get user details
		if (count($myresult) > 0) {
			foreach ($myresult as $key => $value) {

				$user_id = $value["id"];
				$Usersname = $value["fname"] . " " . $value["lname"];
				$email_address = $value["email"];

			}

			//check if user requested for a password recovery in last 3 days.

			$check_code_request = Log_monitor::check_code_request($user_id);
			
			if (count($check_code_request) > 0) {
				$data['user_email'] = $email_address;
				$data['popup'] = "request_valid";
				$data['title'] = "Password Recovery";
				$this -> load -> view("shared_files/login_pages/enter_reset_code_v", $data);

			} else {

				//generate random code
				$range = microtime(true);
				$rand = rand(0, $range);
				//encrypt code
				$save_code = $rand;
				$result=base_url().'assets/img/coat_of_arms-resized1.png';
				
				//Send Code to User
				$subject = "Request For Password Reset";
				$message = '
				<table class="body">
					<tr>
						<td class="center" align="center" valign="top">
							<center>

								<table class="row header">
									<tr>
										<td class="center" align="center">
											<center>

												<table class="container">
													<tr>
														<td class="wrapper last">

															<table class="twelve columns">
																<tr>
																	<td class="six sub-columns">
																		<img src="'.$result.'">
																		<h6 style="margin:15 0 0 10;">HCMP</h6>
																	</td>
																	<td class="six sub-columns last" style="text-align:right; vertical-align:middle;">
																		<span class="template-label"></span>
																	</td>
																	<td class="expander"></td>
																</tr>
															</table>

														</td>
													</tr>
												</table>

											</center>
										</td>
									</tr>
								</table>

								<table class="container">
									<tr>
										<td>

											<table class="row">
												<tr>
													<td class="wrapper last">

														<table class="twelve columns">
															<tr>
																<td>
																	Hi ' . $Usersname . ', </br>
																	<p>
																		HCMP account username '.$email_address.'.You recently requested for a password reset.</br>
																		If you made this request ,this is your reset code.</br></p> 
																		
																		<table class="twelve columns">
																			<tr>
																				<td class="panel">
																					<p><strong style="font-size:20px;">' . $rand . '</strong> <a href="#"></a></p>
																				</td>
																				<td class="expander"></td>
																			</tr>
																		</table>
																		
																	</p>
																	<p>
																		<strong style="font-size:16px;">This code will expire in 3 Days.</strong>
																	</p>
																	<p>
																		If you did not request for a password reset,your account might have been hijacked. </br>
																		To get back into your account, you will need to reset your password or contact your administrator.
																	</p>
																	
																</td>
																<td class="expander"></td>
															</tr>
														</table>

													</td>
												</tr>
											</table>

											<table class="row callout">
												<tr>
													<td class="wrapper last">

														

													</td>
												</tr>
											</table>';

				//exit;

											$this -> hcmp_functions -> send_email($email_address, $message, $subject, $attach_file = NULL, $bcc_email = NULL, $cc_email = NULL);

				//log the password recovery requests as a code
											$Logthis = new Log_monitor();
											$Logthis -> user_id = $user_id;
											$Logthis -> log_activity = 4;
											$Logthis -> forgetpw_code = $save_code;
											$Logthis -> status = 1;
											$Logthis -> save();

				//deactivate user

											Users::set_deactivate_for_recovery($user_id);

											$data['user_email'] = $email_address;
											$data['popup'] = "success";
											$data['title'] = "Password Recovery";
											$this -> load -> view("shared_files/login_pages/enter_reset_code_v", $data);

										}
									} else {

										$data['popup'] = "error_no_exist";
										$data['title'] = "Password Recovery";
										$this -> load -> view("shared_files/login_pages/forgotpassword_v", $data);

									}

								}

								public function confirm_code() {

									$reset_code = $_POST['code'];
									$email = $_POST['username'];

									$code = md5($reset_code);

									$code = $reset_code;


		//get user details

									$userdetail_result = Users::check_user_exist($email);
									
									foreach ($userdetail_result as $key => $value) {

										$user_id = $value["id"];

									}
		//check if user requested for a password recovery

									$user_code_check_result = Log_monitor::check_code_exist($code, $user_id);
									
									

									if (count($user_code_check_result) > 0) {

			//echo "code correct";
										$data['user_email'] = $email;
										$data['popup'] = "success";
										$data['title'] = "Password Recovery";
										$this -> load -> view("shared_files/login_pages/recover_password_v.php", $data);

									} else {

										$data['user_email'] = $email;
										$data['popup'] = "error";
										$data['title'] = "Password Recovery";
										$this -> load -> view("shared_files/login_pages/enter_reset_code_v", $data);

									}

								}

								public function change_pw_recovery() {

									$email = $_POST['username'];
									$new_pass = $_POST['new_password'];
									$new_password_confirm = $_POST['new_password_confirm'];

									if ($new_pass != $new_password_confirm || $new_pass = "" || $new_password_confirm = "") {
										$data['user_email'] = $email;
										$data['popup'] = "error_nomatch";
										$data['title'] = "Password Recovery";
										$this -> load -> view("shared_files/login_pages/recover_password_v.php", $data);
									} else {
			//get user details
										$new_password_confirm = $_POST['new_password_confirm'];

										$userdetail_result = Users::check_user_exist($email);

										foreach ($userdetail_result as $key => $value) {

											$user_id = $value["id"];

										}

										Users::reset_password($user_id, $new_password_confirm);
										Log_monitor::deactivate_code($user_id);
										$data['user_email'] = $email;
										$data['popup'] = "passwordchange";
										$data['title'] = "Login";
										$this -> load -> view("shared_files/login_pages/login_v.php", $data);

									}

								}

								public function reset_multiple_pass($facility_code){
									Users::reset_password_multiple($facility_code, '123456');
									echo true;
								}

								public function reset_select_multiple_pass($user_array){

									foreach($user_array as $user_id){
										Users::reset_password($user_id, '123456');
										
									}
									echo true;

								}
								public function user_create($reset_user = NULL,$password_reset = NULL) {
									if (isset($password_reset) && $password_reset == 1) {
										$data['reset_user_id'] = isset($reset_user)?$reset_user:NULL;
										$data['pwd_reset'] = 1;
										$return = false;
										$user_data = users::get_user_names($reset_user);
										if(count($user_data)>0){
											$data['fname'] = $user_data[0]['fname'];
											$data['lname'] = $user_data[0]['lname'];
											$data['username'] = $user_data[0]['username'];
											$return = true;										
										}else{
											$return = false;
										}										
										echo $return;
									}else{
										$identifier = $this -> session -> userdata('user_indicator');
										$user_type_id = $this -> session -> userdata('user_type_id');
										$district = $this -> session -> userdata('district_id');
										$county = $this -> session -> userdata('county_id');
										$facility = $this -> session -> userdata('facility_id');
										
			//query to get user listing by type of user

										switch ($identifier):
										case 'moh':
										$permissions='moh_permissions';
										$template = 'shared_files/template/dashboard_template_v';
										break;
										case 'facility_admin':
										$permissions='facilityadmin_permissions';
										$data['listing']= Users::get_user_list_facility($facility);		
										$template = 'shared_files/template/template';
										break;
										case 'district':
										$permissions='district_permissions';
										$data['listing']= Users::get_user_list_district($district);
										$data['facilities']=Facilities::getFacilities($district);
										$data['counts']=Users::get_users_district($district);
										$template = 'shared_files/template/template';
										break;
										case 'moh_user':
										$data['listing']= Users::get_user_list($user_type_id);	
										$template = 'shared_files/template/dashboard_template_v';
										break;
										case 'district_tech':
										$data['listing']= Users::get_user_list($user_type_id);	
										$template = 'shared_files/template/template';
										break;
										case 'rtk_manager':
										$data['listing']= Users::get_user_list($user_type_id);	
										$template = 'shared_files/template/template';
										break;
										case 'super_admin':
										$permissions='super_permissions';
										$data['title'] = "Users";
										$data['content_view'] = "Admin/users_v";
										$data['listing']= Users::get_user_list_all();
										$data['counts']=Users::get_users_count();
										$data['counties']=Counties::getAll();	
										$template = 'shared_files/template/dashboard_v';
										break;
										case 'allocation_committee':
										$data['listing']= Users::get_user_list($user_type_id);	
										$template = 'shared_files/template/template';
										break;	
										case 'county':
										$permissions='county_permissions';
										$data['listing']= Users::get_user_list_county($county);	
										$data['district_data'] = districts::getDistrict($county);
										$data['counts']=Users::get_users_county($county);
										$template = 'shared_files/template/template';
										
										break;	
										endswitch;

										$data['title'] = "User Management";
										$data['user_types']=Access_level::get_access_levels($permissions);	
										$data['banner_text'] = "User Management";
										$data['content_view'] = "shared_files/user_creation_v";
										$data['current_user_id'] = $this-> session -> userdata('user_id');
										$this -> load -> view($template, $data);
									}
		//get user details in session
									
								}

								public function get_user_type_json()	{
									
									$identifier = $this -> session -> userdata('user_indicator');	
									if ($identifier=="county") {
										$permissions='county_permissions';	
									} elseif($identifier=="facility_admin") {
										$permissions='facilityadmin_permissions';
									}else{
										$permissions='district_permissions';
									}
									
									
									echo json_encode(Access_level::get_access_levels($permissions));
									
								}
								
								public function check_user_json()	{
									
									$test_email=$_POST['email'];
									$mycount=count(Users::check_if_email($test_email));
									if ($mycount > 0) {
										
										$response = array('msg' => 'Username Exists.Try again','response'=> 'false');
										echo json_encode($response);
										
									} else {
										$response = array('msg' => 'Username accepted','response'=> 'true');
										echo json_encode($response);
									}
									
									
								}
								
								

								public function addnew_user(){

									$identifier = $this -> session -> userdata('user_indicator');
									
		//var_dump($this->input->post());

									$fname = $_POST['first_name'];
									$lname = $_POST['last_name'];
									$telephone = $_POST['telephone'];
									$email_address = $_POST['email'];
									$username = $_POST['username'];
									$facility_id = $_POST['facility_id'];
									$district_code = ($_POST['district_name']=='NULL')? 0: $_POST['district_name'];
									$user_type = $_POST['user_type'];
									$full_name= $fname .''.$lname; 
									$county=($_POST['county']=='NULL')? 0: $_POST['county'];
		//Generate a activation code
									$range = microtime(true);
									
									$activation = rand(0, $range);
		//default password is already set.
									$default='123456';
		//removed this as the model already hashes the password at the other end
		//$salt = '#*seCrEt!@-*%';
									
		//$password=( md5($salt . $default));	

									switch ($identifier):
									case 'moh':
									
									break;
									case 'facility_admin':
									$facility_id=$this -> session -> userdata('facility_id');
									$district_code=$this -> session -> userdata('district_id');
									$county=$this -> session -> userdata('county_id');

									break;
									case 'district':
									
									$district_code=$this -> session -> userdata('district_id');
									$county=$this -> session -> userdata('county_id');
									
									break;
									case 'super_admin':
									$county=($_POST['county_id']=='NULL')? 0: $_POST['county_id'];	
									case 'county':
									$county=$this -> session -> userdata('county_id');
									
									break;	
									endswitch;
									if($email_address!=''):
										
										$savethis =  new Users();
									$savethis -> fname = $fname;
									$savethis -> lname = $lname;
									$savethis -> email = $email_address;
									$savethis -> username = $username;
									$savethis -> password = $default;
									$savethis -> activation = md5($activation) ;
									$savethis -> usertype_id = $user_type;
									$savethis -> telephone = $telephone;
									$savethis -> district = $district_code;
									$savethis -> facility = $facility_id;
									$savethis -> status = 1;
									$savethis -> county_id = $county;
									$savethis -> save(); 
									
									
									
									
			//$phones=$telephone;
									// $message="Dear $fname $lname, your default password is : $default. \nVisit health-cmp.or.ke to change it and access the system.";
									
									// $message=urlencode($message);
    		//echo '<pre>'; print_r($phone_numbers);echo '<pre>';exit;
									// file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$telephone&text=$message");
									
		//Send registered user email with password and validation link
									
									$full_name = $fname.' '.$lname;
									$link = 'health-cmp.or.ke';
									$site_url=base_url();
									$sms_code=$activation;
									
									$subject = "Account Creation";
									$message = '
									<table class="body">
										<tr>
											<td class="center" align="center" valign="top">
												<center>

													<table class="row header">
														<tr>
															<td class="center" align="center">
																<center>

																	<table class="container">
																		<tr>
																			<td class="wrapper last">

																				<table class="twelve columns">
																					<tr>
																						<td class="six sub-columns last" style="text-align:right; vertical-align:middle;">
																							<span class="template-label"></span>
																						</td>
																						<td class="expander"></td>
																					</tr>
																				</table>

																			</td>
																		</tr>
																	</table>

																</center>
															</td>
														</tr>
													</table>

													<table class="container">
														<tr>
															<td>

																<table class="row">
																	<tr>
																		<td class="wrapper last">
																			
																			<table class="twelve columns">
																				<tr>
																					<td class="panel">
																						<a href="' . $link . '"
																						style="background-color:#ffffff;color:#4566A9;display:inline-block;font-family:sans-serif;font-size:18px;line-height:40px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Welcome to HCMP.</a>
																					</td>
																					<td class="expander"></td>
																				</tr>
																			</table></br></br>

																			<table class="twelve columns">
																				<tr>
																					<td>
																						<p>
																							Your HCMP Account - ' . $email_address . ' -  was recently created.</br>
																							
																							
																							
																						</td>
																						<td class="expander"></td>
																					</tr>
																				</table>

																			</td>
																		</tr>
																	</table>

																	<table class="row callout">
																		<tr>
																			<td class="wrapper last">

																			</tr>
																		</table>'; 

																		$email_address=$email_address.',karsanrichard@gmail.com,ttunduny@gmail.com';
																		$this -> hcmp_functions -> send_email($email_address, $message, $subject, $attach_file = NULL, $bcc_email = NULL, $cc_email = NULL,$full_name);

				//exit;
		//save report access

		//save user
																		
																		endif;


																	}
public function addnew_user_offline(){

	$identifier = $this -> session -> userdata('user_indicator');
	
//var_dump($this->input->post());

	$fname = $_POST['first_name'];
	$lname = $_POST['last_name'];
	$telephone = $_POST['telephone'];
	$email_address = $_POST['email'];
	$username = $_POST['username'];
	$facility_id = $_POST['facility_id'];
	$district_code = ($_POST['district_name']=='NULL')? 0: $_POST['district_name'];
	$user_type = $_POST['user_type'];
	$full_name= $fname .''.$lname; 
	$county=($_POST['county']=='NULL')? 0: $_POST['county'];
//Generate a activation code
	$range = microtime(true);
	
	$activation = rand(0, $range);
//default password is already set.
	$default='123456';
//removed this as the model already hashes the password at the other end
//$salt = '#*seCrEt!@-*%';
	
//$password=( md5($salt . $default));	

	switch ($identifier):
	case 'moh':
	
	break;
	case 'facility_admin':
	$facility_id=$this -> session -> userdata('facility_id');
	$district_code=$this -> session -> userdata('district_id');
	$county=$this -> session -> userdata('county_id');

	break;
	case 'district':
	
	$district_code=$this -> session -> userdata('district_id');
	$county=$this -> session -> userdata('county_id');
	
	break;
	case 'super_admin':
	$county=($_POST['county_id']=='NULL')? 0: $_POST['county_id'];	
	case 'county':
	$county=$this -> session -> userdata('county_id');
	
	break;	
	endswitch;
	if($email_address!=''):
		
		$savethis =  new Users();
	$savethis -> fname = $fname;
	$savethis -> lname = $lname;
	$savethis -> email = $email_address;
	$savethis -> username = $email_address;
	$savethis -> password = $default;
	$savethis -> activation = md5($activation) ;
	$savethis -> usertype_id = $user_type;
	$savethis -> telephone = $telephone;
	$savethis -> district = $district_code;
	$savethis -> facility = $facility_id;
	$savethis -> status = 1;
	$savethis -> county_id = $county;
	$savethis -> save(); 
	
	
	
	$full_name = $fname.' '.$lname;
	$link = 'health-cmp.or.ke';
	$site_url=base_url();
	$sms_code=$activation;
	
	$subject = "Account Creation";
	$message = '
	<table class="body">
		<tr>
			<td class="center" align="center" valign="top">
				<center>

					<table class="row header">
						<tr>
							<td class="center" align="center">
								<center>

									<table class="container">
										<tr>
											<td class="wrapper last">

												<table class="twelve columns">
													<tr>
														<td class="six sub-columns last" style="text-align:right; vertical-align:middle;">
															<span class="template-label"></span>
														</td>
														<td class="expander"></td>
													</tr>
												</table>

											</td>
										</tr>
									</table>

								</center>
							</td>
						</tr>
					</table>

					<table class="container">
						<tr>
							<td>

								<table class="row">
									<tr>
										<td class="wrapper last">
											
											<table class="twelve columns">
												<tr>
													<td class="panel">
														<a href="' . $link . '"
														style="background-color:#ffffff;color:#4566A9;display:inline-block;font-family:sans-serif;font-size:18px;line-height:40px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Welcome to HCMP.</a>
													</td>
													<td class="expander"></td>
												</tr>
											</table></br></br>

											<table class="twelve columns">
												<tr>
													<td>
														<p>
															Your HCMP Account - ' . $email_address . ' -  was recently created.</br>
															
															
															
														</td>
														<td class="expander"></td>
													</tr>
												</table>

											</td>
										</tr>
									</table>

									<table class="row callout">
										<tr>
											<td class="wrapper last">

											</tr>
										</table>'; 

	// $email_address=$email_address.',karsanrichard@gmail.com,ttunduny@gmail.com';
	$email_address=$email_address.',ttunduny@gmail.com';
	$this -> hcmp_functions -> send_email($email_address, $message, $subject, $attach_file = NULL, $bcc_email = NULL, $cc_email = NULL,$full_name);

//exit;
//save report access

//save user
	
	endif;
	echo "true";


}
																	public function edit_user(){

																		$county = $this -> session -> userdata('county_id');
																		$identifier = $this -> session -> userdata('user_indicator');

																		$fname = $_POST['fname_edit'];
																		$lname = $_POST['lname_edit'];
																		$status = $_POST['status'];
																		$telephone_edit= $_POST['telephone_edit'];
																		$email_edit = $_POST['email_edit'];
																		$username_edit = $_POST['username_edit'];
																		$user_type_edit_district = $_POST['user_type_edit_district'];
																		$district_name_edit = $_POST['district_name_edit'];
																		$email_recieve_edit = $_POST['email_recieve_edit'];
																		$sms_recieve_edit = $_POST['sms_recieve_edit'];

																		$user_id= $_POST['user_id'];
		//echo $email_recieve_edit;exit;

																		if ($status=="true") {
																			
																			$status=1;
																			
																		} elseif($status=="false") {
																			
																			$status=0;
																		}

																		if ($email_recieve_edit=="true") {
																			
																			$email_recieve_edit=1;
																			
																		} elseif($email_recieve_edit=="false") {
																			
																			$email_recieve_edit=0;
																		}

																		if ($sms_recieve_edit=="true") {
																			
																			$sms_recieve_edit=1;
																			
																		} elseif($sms_recieve_edit=="false") {
																			
																			$sms_recieve_edit=0;
																		}

																		if ($identifier=="district") {
																			
																			$facility_id_edit = $_POST['facility_id_edit_district'];
																			
																		} elseif($identifier=="county") {
																			
																			$facility_id_edit= $_POST['facility_id_edit'];
																		}
																		
																		
		//update user
																		
																		$update_user = Doctrine_Manager::getInstance()->getCurrentConnection();
																		$update_user->execute("UPDATE `user` SET fname ='$fname' ,lname ='$lname',email ='$email_edit',usertype_id =$user_type_edit_district,telephone ='$telephone_edit',
																			district ='$district_name_edit',facility ='$facility_id_edit',status ='$status',county_id ='$county',
																			email_recieve ='$email_recieve_edit',
																			sms_recieve ='$sms_recieve_edit'
																			WHERE `id`= '$user_id'");
																		
																	}
																	public function activation($myurl){
																		
																		$myurl=$this->uri->segment(3);
																		$cipher= md5($myurl);
																		
			//query to find match 
																		Users::check_activation($cipher);
																		$restrict= count(Users::check_activation($cipher));
																		
																		
																		if ($restrict==0) {
																			
																			$this -> load -> view('shared_files/404');
																		}else {
																			$this -> load -> view('shared_files/activation');
																		}
																		
																	}
																	
																	public function activation_final_phase(){
																		
																		$email = $_POST['username'];
																		$password = $_POST['new_password'];

																		$myurl=$this->uri->segment(3);

			//confirm user exists and is inactive
																		
																		$data=Users::check_user_exist_activate($email);
																		foreach ($data as $key => $value) {					
																			
																			$user_id=$value->id;
																		}
																		
																		$new_password_confirm=$password ;
																		
																		Users::reset_password($user_id, $new_password_confirm);
																		$this -> session -> sess_destroy(); session_destroy();
																		
																		
																	}
																	public function change_default(){
																		
																		$email = $_POST['username'];
																		$password = $_POST['new_password'];

																		$myurl=$this->uri->segment(3);

			//confirm user exists and is inactive
																		
																		$data=Users::check_user_exist_activate($email);
																		foreach ($data as $key => $value) {					
																			
																			$user_id=$value->id;
																		}
																		
																		$new_password_confirm=$password ;
																		
																		Users::reset_password($user_id, $new_password_confirm);
																		$this -> session -> sess_destroy(); session_destroy();
																		$data['title'] = "Login";
																		$this -> load -> view("shared_files/login_pages/login_v", $data);
																		
																	}


																	public function save_new_password() {
		// echo "<pre>";
		// echo json_encode($_POST);die;
																		$id=$this -> session -> userdata('user_id');		
																		$old_password=$_POST['current_password'];
																		$new_passw=$_POST['new_password_confirm'];
																		$salt = '#*seCrEt!@-*%';
		//retrieve password and compare
																		
																		$getdata=Users::getuserby_id($id);
																		$db_password=$getdata[0]['password'];
		//echo "</br>";
																		$captured_password=( md5($salt . $old_password));
		// echo json_encode($db_password);die;
		//exit;
		// $valid_old_password = $this -> correct_current_password($db_password,$captured_password);
																		
																		if ($db_password != $captured_password) {
																			$response = array('msg' => '<div class="bg-danger">Your current password does not match.Please try again</div>','response'=> 'false');
																			
																			echo json_encode($response);
			//echo "Dont match";
																		} else {
																			
																			$salt = '#*seCrEt!@-*%';
																			
																			$new_password=( md5($salt . $new_passw));						
																			$updatep = Doctrine_Manager::getInstance()->getCurrentConnection();			
//update password
																			$updatep->execute("UPDATE user SET password='$new_password'  WHERE id='$id'; ");
																			$response = array('msg' => 'Success!!! Your password has been changed. Exit to continue','response'=> 'true');
																			echo json_encode($response);
																		}
																		
																		

																	}

																	public function sms_activate(){
																		
																		$this -> load -> view("shared_files/sms_activation_v");
																	}
																	
																	
																	public function sms_activation(){
																		
																		
																		$phone = $_POST['phone_n'];
																		$actication_code= $_POST['a_code'];
																		$new_pass = $_POST['new_password'];
																		$write_new = $_POST['new_password_confirm'];
																		$code = md5($actication_code);
																		$exist_active=Users::check_db_activation($phone,$code);
																		$count=count($exist_active);
																		$new_password_confirm=$write_new;
																		
																		
																		if ($new_pass != $write_new || $new_pass = "" || $write_new = ""||$phone="") {
																			$data['popup'] = "error_nomatch";
																			$data['title'] = "Password Recovery";
																			$this -> load -> view("shared_files/sms_activation_v", $data);
																			
																		} else {
																			foreach ($exist_active as $key => $value) {

																				$user_id = $value["id"];
																				$activation_db = $value["activation"];

																			}
																			
																			
																			if ($count>0 && $activation_db=$code) {
				//update as activated
																				
																				Users::reset_password($user_id, $new_password_confirm);
																				$data['popup'] = "activation";
																				$data['title'] = "Password Recovery";
																				$this -> load -> view("shared_files/login_pages/login_v", $data);
																				
																			} else {
																				
																				$data['popup'] = "error_nomatch";
																				$data['title'] = "Password Recovery";
																				$this -> load -> view("shared_files/sms_activation_v", $data);
																			}
																			
																			

																		}
																		

																		
																	}

																	public function reset_pass_to_default($user_id){
			// echo "<pre>";var_dump($this->input->post());exit;	
			// $user_id = $this->input->post('user_id');
			// echo "This: ".$user_id;exit;
																		$query =  Doctrine_Manager::getInstance() -> getCurrentConnection() -> execute("
																			UPDATE `hcmp_rtk`.`user` SET `password`='b56578e2f9d28c7497f42b32cbaf7d68' WHERE `id`=$user_id;");
																		$pwd_reset = 1;
																		$user_id = $user_id;
																		
			// echo "<pre>";print_r($user_data);echo "</pre>"; exit;	
																		$this -> user_create($user_id,$pwd_reset);																		

																	}

																	public function user_create_multiple($facility_code=null){

		//get user details in session
																		$identifier = $this -> session -> userdata('user_indicator');
																		$user_type_id = $this -> session -> userdata('user_type_id');
																		$district = $this -> session -> userdata('district_id');
																		$county = $this -> session -> userdata('county_id');
																		$facility = $this -> session -> userdata('facility_id');
		//query to get user listing by type of user

																		switch ($identifier):
																		case 'moh':
																		$permissions='moh_permissions';
																		$template = 'shared_files/template/dashboard_template_v';
																		break;
																		case 'facility_admin':
																		$permissions='facilityadmin_permissions';
																		$data['listing']= Users::get_user_list_facility($facility);		
																		$template = 'shared_files/template/template';
																		break;
																		case 'district':
																		$permissions='district_permissions';
																		$data['listing']= Users::get_user_list_district($district);
																		if($facility_code==0){
																			$data['facilities']=Facilities::getFacilities($district);
																			$facility_name = null;
																			$facility_banner_text = null;
																			$no_of_facilities = 0;
																		}else{
																			$data['facilities']=Facilities::getFacilities_from_facility_code($facility_code);
																			$facility_banner_text =  ' to: '.$data['facilities'][0]['facility_name'];			
																			$facility_name = $data['facilities'][0]['facility_name'];						
																			$no_of_facilities = 1;					
																		}
																		
																		$data['counts']=Users::get_users_district($district);
																		$template = 'shared_files/template/template';
																		break;
																		case 'moh_user':
																		$data['listing']= Users::get_user_list($user_type_id);	
																		$template = 'shared_files/template/dashboard_template_v';
																		break;
																		case 'district_tech':
																		$data['listing']= Users::get_user_list($user_type_id);	
																		$template = 'shared_files/template/template';
																		break;
																		case 'rtk_manager':
																		$data['listing']= Users::get_user_list($user_type_id);	
																		$template = 'shared_files/template/template';
																		break;
																		case 'super_admin':
																		$permissions='super_permissions';
																		$data['title'] = "Users";
																		$data['content_view'] = "Admin/users_v";
																		$data['listing']= Users::get_user_list_all();
																		$data['counts']=Users::get_users_count();
																		$data['counties']=Counties::getAll();	
																		$template = 'shared_files/template/dashboard_v';
																		break;
																		case 'allocation_committee':
																		$data['listing']= Users::get_user_list($user_type_id);	
																		$template = 'shared_files/template/template';
																		break;	
																		case 'county':
																		$permissions='county_permissions';
																		if($facility_code==0){
																			$data['facilities']=Facilities::getFacilities($district);
																			$facility_name = null;
																			$facility_banner_text = null;
																			$district_name = null;										
																			$district_id = null;										
																			$no_of_facilities = 0;
																		}else{
																			$data['facilities']=Facilities::getFacilities_from_facility_code($facility_code);
																			$facility_banner_text =  ' to: '.$data['facilities'][0]['facility_name'];			
																			$facility_name = $data['facilities'][0]['facility_name'];					
																			$district_id = $data['facilities'][0]['district'];								
																			$district_data = Districts::get_district_name($district_id);		
																			$district_name = $district_data[0]['district'];					
																			$no_of_facilities = 1;					
																		}
																		$data['listing']= Users::get_user_list_county($county);	
																		$data['district_data'] = districts::getDistrict($county);
																		$data['counts']=Users::get_users_county($county);
																		$template = 'shared_files/template/template';
																		
																		break;	
																		endswitch;

																		$data['title'] = "Add Multiple Users";
																		$data['user_types']=Access_level::get_access_levels($permissions);	
																		$data['banner_text'] = "Multiple User Addition";
																		$data['facility_name'] = $facility_name;
																		$data['facility_code'] = $facility_code;
																		$data['facility_banner_text'] = $facility_banner_text;
																		$data['district_name'] = $district_name;
																		$data['district_id'] = $district_id;
																		$data['no_of_facilities'] = $no_of_facilities;
																		$data['content_view'] = "shared_files/add_users_multiple";		
																		$this -> load -> view($template, $data);
																	}

																	public function add_users_backend($fname,$lname,$email_address,$username,$telephone,$user_type,$facility_id){
																		$identifier = $this -> session -> userdata('user_indicator');
																		$county = $this -> session -> userdata('county_id');
																		$district = $this -> session -> userdata('district_id');
																		$district = (isset($district))? $this -> session -> userdata('district_id') : NULL ;
																		$district_code = $district;
																		$full_name= $fname .''.$lname; 
		//Generate a activation code
																		$range = microtime(true);
																		$activation = rand(0, $range);
		//default password is already set.
																		$default='123456';
		//removed this as the model already hashes the password at the other end
		//$salt = '#*seCrEt!@-*%';
		//$password=( md5($salt . $default));	

																		switch ($identifier):
																		case 'moh':
																		
																		break;
																		case 'facility_admin':
																		$facility_id=$this -> session -> userdata('facility_id');
																		$district_code=$this -> session -> userdata('district_id');
																		$county=$this -> session -> userdata('county_id');

																		break;
																		case 'district':
																		
																		$district_code=$this -> session -> userdata('district_id');
																		$county=$this -> session -> userdata('county_id');
																		
																		break;
																		case 'super_admin':
																		$county=($_POST['county_id']=='NULL')? 0: $_POST['county_id'];	
																		case 'county':
																		$county=$this -> session -> userdata('county_id');
																		
																		break;	
																		endswitch;
																		if($email_address!=''):
																			
																			$savethis =  new Users();
																		$savethis -> fname = $fname;
																		$savethis -> lname = $lname;
																		$savethis -> email = $email_address;
																		$savethis -> username = $username;
																		$savethis -> password = $default;
																		$savethis -> activation = md5($activation) ;
																		$savethis -> usertype_id = $user_type;
																		$savethis -> telephone = $telephone;
																		$savethis -> district = $district_code;
																		$savethis -> facility = $facility_id;
																		$savethis -> status = 1;
																		$savethis -> county_id = $county;
																		$savethis -> save(); 
																		
			//$phones=$telephone;
																		$message="Dear $fname $lname, your default password is : $default. \nVisit health-cmp.or.ke to change it and access the system.";
																		
																		$message=urlencode($message);
    		//echo '<pre>'; print_r($phone_numbers);echo '<pre>';exit;
																		file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$telephone&text=$message");
																		
		//Send registered user email with password and validation link
																		
																		$full_name = $fname.' '.$lname;
																		$link = 'health-cmp.or.ke';
																		$site_url=base_url();
																		$sms_code=$activation;
																		
																		$subject = "Account Creation";
																		$message = '
																		<table class="body">
																			<tr>
																				<td class="center" align="center" valign="top">
																					<center>

																						<table class="row header">
																							<tr>
																								<td class="center" align="center">
																									<center>

																										<table class="container">
																											<tr>
																												<td class="wrapper last">

																													<table class="twelve columns">
																														<tr>
																															<td class="six sub-columns last" style="text-align:right; vertical-align:middle;">
																																<span class="template-label"></span>
																															</td>
																															<td class="expander"></td>
																														</tr>
																													</table>

																												</td>
																											</tr>
																										</table>

																									</center>
																								</td>
																							</tr>
																						</table>

																						<table class="container">
																							<tr>
																								<td>

																									<table class="row">
																										<tr>
																											<td class="wrapper last">
																												
																												<table class="twelve columns">
																													<tr>
																														<td class="panel">
																															<a href="' . $link . '"
																															style="background-color:#ffffff;color:#4566A9;display:inline-block;font-family:sans-serif;font-size:18px;line-height:40px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Welcome to HCMP.</a>
																														</td>
																														<td class="expander"></td>
																													</tr>
																												</table></br></br>

																												<table class="twelve columns">
																													<tr>
																														<td>
																															<p>
																																Your HCMP Account - ' . $email_address . ' -  was recently created.</br>
																																
																																
																																
																															</td>
																															<td class="expander"></td>
																														</tr>
																													</table>

																												</td>
																											</tr>
																										</table>

																										<table class="row callout">
																											<tr>
																												<td class="wrapper last">

																												</tr>
																											</table>'; 

																											$email_address=$email_address.',karsanrichard@gmail.com';
																											$this -> hcmp_functions -> send_email($email_address, $message, $subject, $attach_file = NULL, $bcc_email = NULL, $cc_email = NULL,$full_name);

				//exit;
		//save report access

		//save user
																											
																											endif;
																											return "SUCCESSFUL";
																											// redirect('facility_activation/facility_dash');
																										}
			// echo "I HAVE ENDED HEEEERE";
																										

																										public function contact_us(){
																											$subject = "title";
																											$description = "subjects";
																											$facility_id=$this -> session -> userdata('facility_id');
																											
																											$one = array();
																											$two = array(
																												'title' => $subject,
																												'description' => $description,
																												'facility_code' => $facility_id
																												);

			// echo $subject . ' ' . $description;
																											array_push($one, $two);
																											$res = $this->db->insert_batch('messages',$one);

																											echo $res;
																										}

																										public function tester(){
																											$this->load->model('users');
																											$last_inserted = $this ->users->set_report_access();
																											echo "<pre>This";print_r($last_inserted);echo "</pre>";exit;
			//$this->Users::set_report_access();
																										}
																									}
