<?php
/**
 * @author Mureithi
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Titus extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> helper(array('url','file','download'));
		$this -> load -> library(array('PHPExcel/PHPExcel','mpdf/mpdf'));
	}
	public function upload_excel(){
		// echo "<pre>";print_r($this->input->post());echo "</pre>";exit;
		// ini_set('memory_limit', '1024M'); // or you could use 1G
		$config['upload_path'] = 'uploads/excel/';
		$config['allowed_types'] = 'xls|xlsx';
		$config['max_size']	= '2048';
		$category = $this->input->post("category");

		$res = $this->load->library('upload', $config);
		// echo "<pre>";print_r($res);exit;
		// $field_name = "recipient_excel";
		if ( ! $this->upload->do_upload("recipient_excel"))
		{
			//echo "<pre>";print_r($this->upload->display_errors());echo "</pre>";
			// echo "I didnt work";
		}
		else
		{
			// $data = array('upload_data' => $this->upload->data());
			// echo "<pre>";print_r($this->upload->data());echo "</pre>";
			$result = $this->upload->data();
			// echo "<pre>";print_r($result['file_name']);echo "</pre>";
			// redirect(base_url().'users/upload_excel/'.);
			$this->upload_recepients($result['file_name'],$category);
			// echo "I worked";
		}
	}//end of upload excel

	public function upload_recepients($file_name = NULL,$category = NULL){
		//  Include PHPExcel_IOFactory
		// include 'PHPExcel/IOFactory.php';
		// include 'PHPExcel/PHPExcel.php';

		// $inputFileName = 'excel_files/garissa_sms_recepients_updated.xlsx';
		// echo $category;exit;
		$this->load->model('users','users');
		$file_name = 'safaricom.xlsx';
		$inputFileName = 'print_docs/excel/excel_template_test/'.$file_name;

		$objReader = new PHPExcel_Reader_Excel2007();
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($inputFileName);

		// echo "<pre>";print_r($inputFileName);exit;

		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow()+1; 
		$highestColumn = $sheet->getHighestColumn();

		// echo "<pre>";print_r($highestRow);echo "</pre>";exit;
		$rowData = array();
		for ($row = 2; $row < $highestRow; $row++){ 
		    //  Read a row of data into an array
		    $rowData_ = $sheet->rangeToArray('A' . $row . ':A' . $row);
		// echo "<pre>";print_r($rowData_);echo "</pre>";
		    array_push($rowData, $rowData_[0]);
		    //  Insert row data array into your database of choice here
		}
		// $final_array[] = array();
		foreach ($rowData as $key => $value) {
			$phone_number = $value[0];		
			$phone_number_0 = '0'.$phone_number;
			$date= null;	
			$date_added_0 = Users::get_date_added($phone_number_0);
			if(count($date_added_0)>0){	
				$date = $date_added_0[0]['created_at'];
			}else{
				$phone_number_254 = '254'.$phone_number;
				$date_added_254 = Users::get_date_added($phone_number_254);
				if(count($date_added_254)>0){
					$date = $date_added_254[0]['created_at'];
				}else{
					$date = 'No Data Available';
				}
			}

			$final_array[] = array('phone'=>$phone_number,'date'=>$date);
		}

		echo "<pre>";
		print_r($final_array);die;
		/*
		names
		facility_name
		mfl
		district
		id_number
		mobile
		email
		trainingsite
		*/

		foreach ($rowData as $r_data) {
			// echo "<pre>";print_r($r_data);echo "</pre>";
			$status = 1;
			$district = strtolower($r_data[3]);
			$district = ucfirst($district);
			$fault_index = NULL;
			// echo $district;

			$facility_code = $r_data[2];

			$query = "SELECT * FROM facilities WHERE facility_code = '$facility_code'";
			// $query = "SELECT * FROM districts WHERE district = '$district'";

			$result = $this->db->query($query)->result_array();
			// $district_name = $result[0]['district'];

			if (empty($result)) {
				$queryy = "SELECT * FROM districts WHERE district = '$district'";
				$resultt = $this->db->query($queryy)->result_array();
					// echo $r_data[0]."</br>";
					// $query = "";
				if (empty($resultt)) {
					$fault_index = 1;
					$status = 2;
					$district_id = NULL;
				}else{
					$district_id = $result[0]['id'];
					$fault_index = 0;
					// echo "<pre>";print_r($resultt); echo "</pre>";
				}
			}//if no facility code match
			else{
				$district_id = $result[0]['district'];
			}
		
					$names = $r_data[0];
					$phone = $r_data[5];
					if (isset($phone)) {
						$phone = preg_replace('/\s+/', '', $phone);
						$phone = ltrim($phone, '0');
						$phone = '254'.$phone;
					}else{
						$phone = NULL;
					}
					
					$email = $r_data[6];
					$number_length = isset($phone)?strlen($phone):0;
					// echo $phone;
					// echo "Number Length:  ".$number_length;
					if ($number_length != 12) {
						if (isset($fault_index)) {
							$fault_index = 3;//both error in phone and district
							// $status = 2;
						}else{
							$fault_index = 2;
						}
							$status = 2;
					}

					$fault_index = isset($fault_index)? $fault_index:0;

					$sms_status = isset($status)? $status: 1;
					$rec = array();
					$rec_data = array(
						'fname' => $names,
						'email' => $email,
						'phone_no' => $phone,
						// 'email_status' => 1,
						'sms_status' => $sms_status,
						'user_type' => 1,
						'category_id' => $category,
						'district_id'=>$district_id,
						'fault_index'=>$fault_index
						);

					array_push($rec, $rec_data);
					$insertion = $this->db->insert_batch('recepients',$rec);
				// echo "QUERY SUCCESSFUL. ".$insertion." ".mysql_insert_id()."</br>";
		}
		
		unlink($inputFileName);
		// echo "QUERY SUCCESSFUL. LAST ID INSERTED: ".mysql_insert_id(); exit;
		redirect( base_url().'users/recipients/upload');

	}//end of recepient upload


}


?>