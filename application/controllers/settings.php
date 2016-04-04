<?php
/*
 * @author Karsan
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Settings extends MY_Controller 
{
	function __construct() 
	{
		// echo "I WORK";exit;
		// echo md5(123456);exit;
		parent::__construct();
		$this -> load -> helper(array('form', 'url'));
		$this -> load -> library(array('hcmp_functions', 'form_validation'));
	}

	public function index(){
		$this->settings_dash();
	}
	public function settings_dash(){

		$data['title'] = "Facility Setup";
		$data['banner_text'] = "Setup Facility";
		$template = 'shared_files/template/settings_template';						
		$data['content_view'] = "shared_files/facility_setup_v";
		$this -> load -> view($template, $data);

	}

	public function get_facility_details($facility_code){
		$service_points = Service_points::get_all_active($facility_code);
		$users = Facilities::get_facilities_user_activation_data($facility_code);	
		$option_service_points = '';	
		foreach ($service_points as $service_point){
			$service_point_name=$service_point->service_point_name;
			$facility_code=$service_point->facility_code;
			$service_point_id=$service_point->id;
			$option_service_points_list = '<option value="'.$service_point_id.'">'.$service_point_name.'</option>';
			$option_service_points.=$option_service_points_list;
			// $service_point_array[] = array($service_point_id,$service_point_name,$facility_code);
		}

		foreach ($users as $key => $value) {
			$name = $value['fname'].' '.$value['lname'];
			$created_at = $value['created_at'];
			$last_login = $value['end_time_of_event'];
			$created = date('d F Y',strtotime($created_at));
			$last_login = date('d F Y',strtotime($last_login));
			$output[] = array($name,$created,$last_login);
		}
		$final_output = array('number' =>count($facility_data) ,'users'=>$output,'service_points'=>$option_service_points);
		echo json_encode($final_output);
	}


	public function facility_offline(){
		$identifier = $this -> session -> userdata('user_indicator');
		$user_type_id = $this -> session -> userdata('user_type_id');
		$district = $this -> session -> userdata('district_id');
		// echo $identifier;exit;
		$county = $this -> session -> userdata('county_id');

		// $data['facilities'] = isset($district) ? Facilities::get_facility_details($district) : Facilities::get_facilities_per_county($county);

		if ($identifier == "district") {
			$data['facilities'] = Facilities::get_facility_details($district);
			$data['identifier'] = $identifier;
		}elseif ($identifier == "county") {
			$data['facilities'] = Facilities::get_facility_details(NULL,$county);
			$data['identifier'] = $identifier;
			$data['district_info'] = Districts::get_districts($county);
			

		}
		$permissions='district_permissions';
		// $data['facilities']=Facilities::get_facility_details($district);
		// $data['facilities']=Facilities::get_facilities_per_county($county);
		// echo "<pre>";print_r($data['facilities']);echo "</pre>";exit;
		$data['title'] = "Offline Facility Setup";
		$data['banner_text'] = "Setup Facility Offline";
		$template = 'shared_files/template/template';
		// $data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$data['active_panel'] = 'system_usage';
		// $data['report_view'] = "shared_files/Facility_activation_v";
		$data['content_view'] = "shared_files/facility_offline";
		$this -> load -> view($template, $data);

	}

	public function set_point($facility_code,$service_point){		
		$update_points = Doctrine_Manager::getInstance()->getCurrentConnection();
		$sql = "INSERT INTO  `selected_service_points`(`facility_code`,`service_point_id`,`status`) VALUES ('$facility_code','$service_point','1')";
		$update_points -> execute($sql);
		echo true;
	}

	public function get_facility_user_data($facility_code){
		// $facility_code = $_POST['facility_code'];
		$facility_data = Facilities::get_facilities_user_activation_data($facility_code);
		// echo "<pre>";print_r($facility_data);echo "</pre>";exit;		
		foreach ($facility_data as $key => $value) {
			$name = $value['fname'].' '.$value['lname'];
			$created_at = $value['created_at'];
			$last_login = $value['end_time_of_event'];
			$created = date('d F Y',strtotime($created_at));
			$last_login = date('d F Y',strtotime($last_login));
			$output[] = array($name,$created,$last_login);
		}
		echo json_encode($output);
	}

	

	
}

?>