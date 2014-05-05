<?php
/**
 * @author Kariuki
 */
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

		//var_dump($returned_user);
		//echo count($returned_user);
		//exit;
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
			$fullname = $fname . ' ' . $lname;

			//get county name
			$county_name = Counties::get_county_name($county_id);
			$county_name = $county_name['county'];
			//exit;

			$access_level = Access_level::get_access_level_name($access_typeid);
			$user_indicator = $access_level['user_indicator'];

			$session_data = array('county_id' => $county_id, 'phone_no' => $phone, 'user_email' => $user_email, 'user_id' => $user_id, 'user_indicator' => $user_indicator, 'fname' => $fname, 'lname' => $lname, 'facility_id' => $facility_id, //facility code
			'district_id' => $district_id, 'user_type_id' => $access_typeid,'county_name' => $county_name, 'full_name' => $fullname);

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
			$this -> session -> set_userdata(array("menus" => $menus));
			//Save this sub menus array in the session
			$this -> session -> set_userdata(array("sub_menus" => $sub_menus));

			redirect('Home');
		} else {
			$data['popup'] = "errorpopup";
			$data['title'] = "Login";
			$this -> load -> view("shared_files/login_pages/login_v", $data);
		}
	}

	public function logout() {

		//Log::update_log_out_action($this -> session -> userdata('identity'));

		$this -> session -> sess_destroy();
		$data['title'] = "Login";
		$this -> load -> view("shared_files/login_pages/login_v", $data);
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
				$save_code = md5($rand);

				//Send Code to User
				$subject = "Request For Password Reset";
				$message = "<html><body>
		<div style='border-color: #666; margin:auto;'>	
		Hi " . $Usersname . ", </br>
		<p>
		You (HCMP Account - " . $email_address . " - )  recently requested for a password reset.</br>
		If you made this request this is your reset code.</br></p> 
		<p>
		<strong style='font-size:20px;'>" . $rand . "</strong>
		</p>
		<p>
		<strong style='font-size:16px;'>This code will expire in 3 Days.</strong>
		</p>
		<p>
		If you didn't request for a password reset,your account might have been hijacked. </br>
		To get back into your account, you'll need to reset your password or contact your administrator.
		</p>
		<p>
		Yours sincerely,</br>
		</p>
		<p>
		The HCMP team.</br>
		</p>
		This email can't receive replies. </br> For more information, Contact your Administrator.
		</body></html>";

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

	public function user_create() {

		$identifier = $this -> session -> userdata('user_indicator');
		$user_type_id = $this -> session -> userdata('user_type_id');

		//query to get user listing by type of user

		switch ($identifier):
			case 'moh':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/dashboard_template_v';
			break;
			case 'facility_admin':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/template';
			break;
			case 'district':
			$data['listing']= Users::get_user_list($user_type_id);	
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
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/dashboard_template_v';
			break;
			case 'allocation_committee':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/template';
			break;	
			case 'county':
			$data['listing']= Users::get_user_list($user_type_id);	
			$template = 'shared_files/template/dashboard_template_v';
			break;	
        endswitch;

        $data['title'] = "User Management";
		$data['banner_text'] = "User Management";
		$data['content_view'] = "shared_files/user_creation_v";
		$this -> load -> view($template, $data);
	}

}
