<?php
class User extends Doctrine_Record {
/////
	public function setTableDefinition() {
		$this->hasColumn('fname', 'varchar', 255);
		$this->hasColumn('lname', 'varchar', 255);
		$this->hasColumn('email', 'string', 255, array('unique' => 'true'));
		$this->hasColumn('username', 'string', 255, array('unique' => 'true'));
		$this->hasColumn('password', 'string', 255);
		$this->hasColumn('usertype_id', 'integer', 11);
		$this->hasColumn('telephone', 'varchar', 255);
		$this->hasColumn('district', 'varchar', 255);
		$this->hasColumn('facility', 'varchar', 255);
		$this->hasColumn('status', 'int', 11);
		$this->hasColumn('county_id', 'int', 11);
		$this->hasColumn('partner', 'int', 11);
	}
	
	public function setUp() {
		$this->setTableName('user');
		$this->actAs('Timestampable');
		$this->hasMutator('password', '_encrypt_password');
		$this -> hasMany('Facilities as Codes', array('local' => 'facility', 'foreign' => 'facility_code'));
		$this -> hasMany('access_level as u_type', array('local' => 'usertype_id', 'foreign' => 'id'));
		$this -> hasMany('facilities as hosi', array('local' => 'facility', 'foreign' => 'facility_code'));
		$this -> hasOne('Facility_Issues as idid', array('local' => 'id', 'foreign' => 'issued_by'));		
	}

	protected function _encrypt_password($value) {
		$salt = '#*seCrEt!@-*%';
		$this->_set('password', md5($salt . $value));
		
	}
	
	public static function login($username, $password) {
		
		$salt = '#*seCrEt!@-*%';
		$value=( md5($salt . $password));
		$query = Doctrine_Query::create() -> select("*") -> from("User") -> where("username = '" . $username . "' and password='". $value ."'");

		$x = $query -> execute();
		return $x[0];
		
		
	}
	public static function getPass($username) {
		
		$query = Doctrine_Query::create() -> select("*") -> from("User") -> where("username = '$username'");

		$x = $query -> execute();
		return $x[0];
		
		
	}
	public static function getsome($id) {
		$query = Doctrine_Query::create() -> select("fname") -> from("user")->where("id='$id' ");
		$level = $query -> execute();
		return $level;
	}
	public static function getAll2($facility,$id) {
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("usertype_id=2 or usertype_id=5 ")->andWhere("id <> $id and facility='$facility'");
		$level = $query -> execute();
		return $level;
	}
	public static function getAll3($use_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("usertype_id=2 and id=$use_id");
		$level = $query -> execute();
		return $level;
		
	}
	public static function getAll4($use_id) {
		$myobj = Doctrine::getTable('user')->findOneById($use_id);
		$id=$myobj->id ;
		$my_array =array('0'=>$id);
		return $my_array;
	}
	public static function getAll(){
		$query = Doctrine_Query::create() -> select("*") -> from("user");
		$level = $query -> execute();
		return $level;
	}
	public static function getAll5($district, $id){
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("district=$district") ->andWhere("id <> $id");
		$level = $query -> execute();
		return $level;
	}
	public static function getUsers($facility_c){
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("facility=$facility_c");
		$level = $query -> execute();
		return $level;
	}
	public static function get_user_info($facility_code) {
		$query = Doctrine_Query::create() -> select("DISTINCT usertype_id, telephone,district, facility") -> from("user")->where("status='1' and  facility='$facility_code'");
		$info = $query -> execute();
		
		return $info;
	}
	public static function getAllUser($use_id){
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("id=$use_id");
		$level = $query -> execute();
		return $level;
	}
	public static function getemails($facility){
		$query = Doctrine_Query::create() -> select("email") -> from("user")->where("facility=$facility");
		$level = $query -> execute();
		return $level;
	}
	///////
	public static function check_user_email($email){
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("`email` like '%$email%'");
		$level = $query -> execute();
		return count($level);
	}
///// reset password/
	public static function reset_password($id){
		$myobj = Doctrine::getTable('user')->findOneById($id);
		$myobj->password='123456' ;
		$myobj->save();
		return true;
	}
///// reset password/
	public static function activate_deactivate_user($id,$option){
		$myobj = Doctrine::getTable('user')->findOneById($id);
		$myobj->status=$option ;
		$myobj->save();
		return true;
	}
	public static function delete_user($id){
		$q = Doctrine_Manager::getInstance()->getCurrentConnection()->execute("
			delete from user where id=$id
			");

		return true;
	}

//////get the dpp details 
	public static function get_dpp_details($distirct){
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("district=$distirct and usertype_id='3' ");
		$level = $query -> execute();
		return $level;
	}

	public static function check_user_exist($email){
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("`email` = '$email'");
		$level = $query -> execute();
		return count($level);
	}

	public static function get_user_details($email){
		$query = Doctrine_Query::create() -> select("*") -> from("user")->where("`email` like '%$email%'");
		$level = $query -> execute();
		return $level;
	}

	public static function get_all_moh_users(){
		$q = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
			SELECT u.id, a.level, u.status, c.county, d.district, u.fname, u.lname, u.email, u.telephone, f.facility_name
			FROM access_level a, user_type_definition u_t, user u
			LEFT JOIN facilities f ON u.facility = f.facility_code
			LEFT JOIN districts d ON u.district = d.id
			LEFT JOIN counties c ON u.county_id = c.id
			WHERE u.usertype_id = a.id
			AND a.type = u_t.id
			AND u_t.id =1
			ORDER BY f.facility_name ASC ");
		return $q;
	}

	public static function get_no_of_users_using_hcmp($county_id=NULL,$district_id=NULL){

		if($district_id!=NULL){
			$q_1 = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
				SELECT COUNT( DISTINCT u.`id` ) AS total_no_of_users
				FROM facilities f, districts d, counties c, user u, log l
				WHERE f.district = d.id
				AND u.facility = f.facility_code
				AND d.county = c.id
				AND u.id = l.user_id
				AND u.status =  '1'
				AND d.id =  '$district_id'
				AND l.action =  'Logged In'
				AND l.start_time_of_event BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()
				");

			$q = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
				SELECT COUNT( DISTINCT u.`id` ) AS total_no_of_users
				FROM facilities f, districts d, counties c, user u, log l
				WHERE f.district = d.id
				AND u.facility = f.facility_code
				AND d.county = c.id
				AND u.id = l.user_id
				AND u.status =  '1'
				AND d.id =  '$district_id'
				");

			$q_2 = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
				SELECT COUNT( l.id ) AS users_logged_in
				FROM log l, user u
				WHERE UNIX_TIMESTAMP(  `end_time_of_event` ) =0
				AND  `action` =  'Logged In'
				AND l.`user_id` = u.id
				AND u.district ='$district_id'
				");

		}else{
			$q_1 = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
				SELECT COUNT( DISTINCT u.`id` ) AS total_no_of_users
				FROM counties c, user u, log l
				WHERE u.id = l.user_id 
				AND u.county_id=c.id
				AND u.status =  '1'
				AND c.id =  '$county_id'
				AND l.start_time_of_event BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()
				");

			$q = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
				SELECT COUNT( DISTINCT u.`id` ) AS total_no_of_users
				FROM  counties c, user u
				WHERE u.county_id=c.id
				AND u.status =  '1'
				AND c.id =  '$county_id'
				");

			$q_2 = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
				SELECT COUNT( l.id ) AS users_logged_in
				FROM log l, user u
				WHERE UNIX_TIMESTAMP(  `end_time_of_event` ) =0
				AND  `action` =  'Logged In'
				AND l.`user_id` = u.id
				AND u.county_id ='$county_id'
				");
		}

		return array('total_no_of_users'=>$q[0]['total_no_of_users'],'total_no_of_users_7_days'=>$q_1[0]['total_no_of_users'],"active_users"=>$q_2[0]['users_logged_in']);
	}
	public function add_user() {        
		$password = 123456;
		$salt = '#*seCrEt!@-*%';
		$time = date("Y-m-d H:i:s"); 
		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$email = $_POST['email'];        
		$county = $_POST['county'];
		$district = $_POST['district'];
		$facility = 0;
		$region = $_POST['region'];   
		$level = $_POST['level']; 
		if($level==15){
			if($region ==0){
				$region = 1;
			}
		} 
		$value=(md5($salt . $password));

		$sql = "INSERT INTO `user`(`fname`, `lname`, `email`, `username`, `password`, `activation`, `usertype_id`, `telephone`, `district`, `partner`, `facility`, `created_at`, `updated_at`, `status`, `county_id`, `email_recieve`, `sms_recieve`)
		VALUES ('$fname','$lname','$email','$email','$value','00000','$level','$telephone','$district','$region','$facility','$time','$time',1,'$county',1,1)";    

		$q_2 = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($sql);
    //$this->db->query($sql);    
	}

	public function edit_user_password($id) {            
		$salt = '#*seCrEt!@-*%';    
		$password = mysql_real_escape_string($_POST['new_pass']);    
		$user_id = mysql_real_escape_string($_POST['user_id']);        
		$value=(md5($salt . $password));    
		$sql = "update `user`set `password`='$value' where `id`='$user_id'";        
		$q_2 = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($sql);
    //$this->db->query($sql);    
	}
	public function get_last_login($user_id){
		$q = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
			SELECT MAX(end_time_of_event) as last
			FROM log
			WHERE user_id = 1331
			LIMIT 1 
			");

		foreach ($q as $time){
			$date = $time['last'];
		}
		return $date;
	}

	public function get_last_order($user_id){
		$q = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
			SELECT f_o.order_no, MAX(f_o.order_date) as last_order, temp2.commodity_name, temp1.quantity_ordered_pack, temp1.quantity_ordered_unit, f_o.order_total

			FROM facility_orders f_o
			Left Join facility_order_details as temp1 on temp1.order_number_id = f_o.order_no
			left join commodities as temp2 on temp2.id = temp1.commodity_id
			WHERE ordered_by =  $user_id
			LIMIT 1
			");
		$date = array();
		if($q){

			foreach ($q as $time){
				$date['last_order'] = $time['last_order'];
				$date['order_no'] = $time['order_no'];
				$date['commodity_name'] = $time['commodity_name'];
				$date['quantity_ordered_pack'] = $time['quantity_ordered_pack'];
				$date['quantity_ordered_unit'] = $time['quantity_ordered_unit'];
				$date['order_total'] = $time['order_total'];
			}

		}
		return $date;
	}

	public function get_last_issue ($user_id){
		$q = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
			SELECT Max(f_i.date_issued) as last_issue,f_i.issued_to,c.commodity_name, f_i.qty_issued

			From facility_issues f_i, commodities c

			Where issued_by = $user_id 

			and c.id = f_i.commodity_id
			");

		if($q){
			foreach ($q as $value) {
				$data['last_issue'] = $value['last_issue'];
				$data['issued_to'] = $value['issued_to'];
				$data['commodity_name'] = $value['commodity_name'];
				$data['qty_issued'] = $value['qty_issued'];
			}
		}
		return $data;
	}

	public function get_currently_logged_in_users($level){//facility subcounty county
		switch ($level) {
			case 'facility':
				$dawa_sawa = "SELECT 
							    l.id,
							    l.action,
							    l.start_time_of_event,
							    l.action_id,
							    l.end_time_of_event,
							    u.fname,
							    u.lname,
							    u.username,
							    f.facility_name,
							    d.district,
							    c.county,
							    a.level
							FROM
							    log l,
							    districts d,
							    counties c,
							    access_level a,
							    user u,
							    facilities f
							WHERE
							    action = \"Logged In\"
							        AND start_time_of_event BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
							        AND end_time_of_event = \"0000-00-00 00:00:00\"
							        AND u.district = d.id
							        AND d.county = c.id
							        AND u.usertype_id = a.id AND u.facility = f.facility_code AND l.user_id = u.id
							        AND u.usertype_id IN (2,5)";
				break;
			case 'subcounty':
				$dawa_sawa = "SELECT 
							    l.id,
							    l.action,
							    l.start_time_of_event,
							    l.action_id,
							    l.end_time_of_event,
							    u.fname,
							    u.lname,
							    u.username,
							    d.district,
							    c.county,
							    a.level
							FROM
							    log l,
							    districts d,
							    counties c,
							    access_level a,
							    user u
							WHERE
							    action = \"Logged In\"
							        AND start_time_of_event BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
							        AND end_time_of_event = \"0000-00-00 00:00:00\"
							        AND u.district = d.id
							        AND d.county = c.id
							        AND u.usertype_id = a.id 
							        AND l.user_id = u.id 
							        AND u.usertype_id = 3";
				break;
			case 'county':
				$dawa_sawa = "SELECT 
							    l.id,
							    l.action,
							    l.start_time_of_event,
							    l.action_id,
							    l.end_time_of_event,
							    u.fname,
							    u.lname,
							    u.username,
							    c.county,
							    a.level
							FROM
							    log l,
							    counties c,
							    access_level a,
							    user u
							WHERE
							    action = \"Logged In\"
							        AND start_time_of_event BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
							        AND end_time_of_event = \"0000-00-00 00:00:00\"
									AND u.county_id = c.id
							        AND u.usertype_id = a.id 
							        AND l.user_id = u.id 
							        AND u.usertype_id = 10";
				break;
			default:
				$dawa_sawa = "SELECT 
							    l.id,
							    l.action,
							    l.start_time_of_event,
							    l.action_id,
							    l.end_time_of_event,
							    u.fname,
							    u.lname,
							    u.username,
							    c.county,
							    a.level
							FROM
							    log l,
							    counties c,
							    access_level a,
							    user u
							WHERE
							    action = \"Logged In\"
							        AND start_time_of_event BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
							        AND end_time_of_event = \"0000-00-00 00:00:00\"
									AND u.county_id = c.id
							        AND u.usertype_id = a.id 
							        AND l.user_id = u.id";
				break;
		}
		// echo $dawa_sawa;
		$query = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll($dawa_sawa);

		return $query;
	}//end of currently logged in users

	public function get_previous_logs($level){
		switch ($level) {
			case 'county':
				$panadol = "SELECT 
							    l.id,
							    l.action,
							    l.start_time_of_event,
							    l.action_id,
							    l.end_time_of_event,
							    u.fname,
							    u.lname,
							    u.username,
							    c.county,
							    a.level,
							    l.start_time_of_event,
							    l.end_time_of_event
							FROM
							    log l,
							    counties c,
							    access_level a,
							    user u
							WHERE
							    start_time_of_event BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()
							        AND u.usertype_id = 10
							        AND u.usertype_id = a.id
							        AND l.user_id = u.id
							        AND u.county_id = c.id
							LIMIT 500";
				break;

			case 'subcounty':
				$panadol = "SELECT 
							  l.id,
							  l.action,
							  l.start_time_of_event,
							  l.action_id,
							  l.end_time_of_event,
							  u.fname,
							  u.lname,
							  u.username,
							  f.facility_name,
							  d.district,
							  c.county,
							  a.level,
							  l.start_time_of_event,
							  l.end_time_of_event
							FROM
							  log l,
							  districts d,
							  counties c,
							  access_level a,
							  user u,
							  facilities f
							WHERE
							      start_time_of_event BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()
							      AND u.usertype_id = 3
							      AND u.district = d.id
							      AND d.county = c.id
							      AND u.usertype_id = a.id 
							      AND l.user_id = u.id
							      LIMIT 500 ";
				break;

			case 'facility':
				$panadol = "SELECT 
							  l.id,
							  l.action,
							  l.start_time_of_event,
							  l.action_id,
							  l.end_time_of_event,
							  u.fname,
							  u.lname,
							  u.username,
							  f.facility_name,
							  d.district,
							  c.county,
							  a.level,
							  l.start_time_of_event,
							  l.end_time_of_event
							FROM
							  log l,
							  districts d,
							  counties c,
							  access_level a,
							  user u,
							  facilities f
							WHERE
							      start_time_of_event BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()
							      AND u.district = d.id
							      AND d.county = c.id
							      AND u.usertype_id = a.id 
							      AND u.facility = f.facility_code 
							      AND l.user_id = u.id
							      AND u.usertype_id IN (2,5)
							      LIMIT 500";
				break;
			
			default:
				$panadol = "SELECT 
							  l.id,
							  l.action,
							  l.start_time_of_event,
							  l.action_id,
							  l.end_time_of_event,
							  u.fname,
							  u.lname,
							  u.username,
							  f.facility_name,
							  d.district,
							  c.county,
							  a.level,
							  l.start_time_of_event,
							  l.end_time_of_event
							FROM
							  log l,
							  districts d,
							  counties c,
							  access_level a,
							  user u,
							  facilities f
							WHERE
							      start_time_of_event BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()
							      AND u.district = d.id
							      AND d.county = c.id
							      AND u.usertype_id = a.id 
							      AND u.facility = f.facility_code 
							      AND l.user_id = u.id
							      LIMIT 500";
				break;

		}//end of switch

			 	// $query = $panadol."<br>";
				$query = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll($panadol);

				return $query;
	}//end of previous logs function


}
