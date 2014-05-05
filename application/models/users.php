<?php
class Users extends Doctrine_Record {
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

		$query = Doctrine_Query::create() -> select("*") -> from("Users") -> where("username = '" . $username . "' AND status=1");

		$user = $query -> fetchOne();
		if ($user) {

			$user2 = new Users();
			$user2 -> password = $password;

			if ($user -> password == $user2 -> password) {
				return $user;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}
	
	public static function getsome($id) {
		$query = Doctrine_Query::create() -> select("fname") -> from("users")->where("id='$id' ");
		$level = $query -> execute();
		return $level;
	}
	public static function get_user_names($id)
	{
		$query = Doctrine_Query::create() -> select("fname, lname") -> from("users")->where("id='$id'");
		$names = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $names;
	}

	public static function check_user_exist($email)
	{
		$query = Doctrine_Query::create() -> select("*") -> from("Users")->where("username='$email' AND status IN(1,2)");
		$result = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $result;
	}

	public static function reset_password($user_id,$new_password_confirm)
	{
		
		//$new_password_confirm;
			$salt ='#*seCrEt!@-*%';
			$value= md5($salt . $new_password_confirm);
			
			$update = Doctrine_Manager::getInstance()->getCurrentConnection();
			$update->execute("UPDATE user SET password='$value',status=1  WHERE id='$user_id' ; ");
	}

	public static function set_deactivate_for_recovery($user_id)
	{
					
			$update = Doctrine_Manager::getInstance()->getCurrentConnection();

			$update->execute("UPDATE user SET status=2  WHERE id='$user_id' ;");
	}
	
	public static function get_user_list($user_type_id)
	{
					
			
	}


}
