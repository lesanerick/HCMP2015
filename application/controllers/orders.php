<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 * @author Kariuki
 */
class orders extends MY_Controller {
	function __construct() {
		parent::__construct();
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		$this -> load -> helper(array('form', 'url'));
		$this -> load -> library(array('hcmp_functions', 'form_validation'));

	}

	public function order_listing($level, $facility_code = null) {
		/*
		 $data['order_counts']=Counties::get_county_order_details("","", $facility_c);
		 $data['delivered']=Counties::get_county_received("","", $facility_c);
		 $data['pending']=Counties::get_pending_county("","", $facility_c);
		 $data['approved']=Counties::get_approved_county("","", $facility_c);
		 $data['rejected']=Counties::get_rejected_county("","", $facility_c);
		 $data['content_view'] = "facility/facility_issues/facility_issues_service_points_v";
		 $data['title'] = "Order Listing";
		 $data['banner_text'] = "Order Listing";	*/
	}

	public function index() {
		$test = $this -> hcmp_functions -> create_order_delivery_color_coded_table(1);
		echo $test['table'];
	}

	public function test_read_write_excel() {

		$inputFileName = 'print_docs/excel/excel_template/KEMSA Customer Order Form.xlsx';
		$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		// echo "$inputFileType";die;		
		$file_name = time() . '.xlsx';
		$excel2 = PHPExcel_IOFactory::createReader($inputFileType);
		$excel2 = $objPHPExcel = $excel2 -> load($inputFileName);
		// Empty Sheet

		$sheet = $objPHPExcel -> getSheet(0);
		$highestRow = $sheet -> getHighestRow();

		$highestColumn = $sheet -> getHighestColumn();

		$excel2 -> setActiveSheetIndex(0);

		$excel2 -> getActiveSheet() -> setCellValue('H4', '4') -> setCellValue('H5', '5') -> setCellValue('H6', '6') -> setCellValue('H7', '7') -> setCellValue('H8', '7');

		//  Loop through each row of the worksheet in turn
		for ($row = 1; $row <= $highestRow; $row++) {
			//  Read a row of data into an array
			$rowData = $sheet -> rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
			if (isset($rowData[0][2]) && $rowData[0][2] != 'Product Code') {
				$excel2 -> getActiveSheet() -> setCellValue("H$row", '7');
			}

		}

		$objWriter = PHPExcel_IOFactory::createWriter($excel2, $inputFileType);
		$objWriter -> save("print_docs/excel/excel_files/" . $file_name);

	}

	public function getDistrict() {
		//for ajax
		$county = $_POST['county'];
		$districts = Districts::getDistrict($county);
		$list = "";

		foreach ($districts as $districts) {
			$list .= $districts -> id;
			$list .= "*";
			$list .= $districts -> district;
			$list .= "_";
		}

		echo $list;
	}

	public function getFacilities() {
		//for ajax
		$district = $_POST['district'];
		$facilities = Facilities::getFacilities($district);
		$list = "";

		foreach ($facilities as $facilities) {
			$list .= $facilities -> facility_code;
			$list .= "*";
			$list .= $facilities -> facility_name;
			$list .= "_";
		}

		echo $list;
	}

	//Facility Transaction Data when MEDS option is selected
	public function facility_order_meds() {//karsan
		$facility_code = $this -> session -> userdata('facility_id');
		$facility_data = Facilities::get_facility_name_($facility_code) -> toArray();

		$items = Facility_Transaction_Table::get_commodities_for_ordering($facility_code);
		// echo "THIS WORKS";exit;
		$meds_categories = meds_categories::get_all();
		//$meds_commodities = meds_commodities::get_all();
		//echo "<pre>";print_r($meds_categories);exit;

		$data['categories'] = $meds_categories;
		$data['order_details'] = $data['facility_order'] = $items;
		$data['facility_commodity_list'] = Commodities::get_commodities_not_in_facility($facility_code);
		$data['title'] = "Facility New Order MEDS";
		$data['content_view'] = "facility/facility_orders/facility_order_meds";
		$data['banner_text'] = "Facility New Order MEDS";
		$this -> load -> view("shared_files/template/template", $data);

		//var_dump($temp);exit;
		/*$facility_code = $this -> session -> userdata('facility_id');
		 $facility_data = Facilities::get_facility_name_($facility_code) -> toArray();
		 $data['content_view'] = "facility/facility_orders/facility_order_from_kemsa_v";
		 $data['title'] = "Facility New Order";
		 $data['banner_text'] = "Facility New Order";
		 $data['drawing_rights'] = $facility_data[0]['drawing_rights'];

		 $this -> load -> view('shared_files/template/template', $data);*/
		//$data['facility_stock_data'] = facility_transaction_table::get_all($facility_code);
		//$data['last_issued_data']=facility_issues::get_last_time_facility_issued($facility_code);

	}

	//AJAX Request for getting the sub categories of a particular category
	public function get_sub_categories() {
		$category = $_POST['category'];
		$sub_categories = meds_sub_category::get_all_in_category($category);
		//echo "<pre>";print_r($sub_categories);exit;
		//$facilities = Facilities::getFacilities($district);
		$list = "";
		foreach ($sub_categories as $sub_categories) {
			$list .= $sub_categories -> id;
			$list .= "*";
			$list .= $sub_categories -> sub_category_name;
			$list .= "_";
		}
		echo $list;
	}

	//AJAX Request for getting the commoditeis in a particular sub category
	public function get_commodities_meds() {
		$sub_category = $_POST['sub_category'];
		$commodities = meds_commodities::get_all_in_category($sub_category);
		//$commodities = meds_sub_category::get_all_in_category($category);
		//echo "<pre>";print_r($sub_categories);exit;
		//$facilities = Facilities::getFacilities($district);
		$list = "";
		// echo "<pre>";print_r($commodities);echo "</pre>";exit;
		foreach ($commodities as $commodities) {
			$list .= $commodities -> commodity_code;
			$list .= "^";
			$list .= $commodities -> unit_pack;
			$list .= "^";
			$list .= $commodities -> order_note;
			$list .= "^";
			$list .= $commodities -> unit_price;
			$list .= "^";
			$list .= $commodities -> commodity_name;
			$list .= "_";
		}
		echo $list;
	}

	public function facility_order($source = NULL,$facility_code=null) {
		echo "<pre>";print_r($_FILES);exit;
		header('Content-Type: text/html; charset=UTF-8');
		//pick the facility code from the session as it is set
		if(!isset($facility_code)){
			$facility_code = $this -> session -> userdata('facility_id');
			$data['other_facility_code'] = $facility_code;
		}

		$amc_calc = $this -> amc($county, $district, $facility_code);
		//echo '<pre>'; print_r($amc_calc);echo '<pre>'; exit;
		$items = ((isset($source)) && ($source == 2)) ? Facility_Transaction_Table::get_commodities_for_ordering_meds($facility_code,$source) : Facility_Transaction_Table::get_commodities_for_ordering($facility_code);
		
		// echo '<pre>'; print_r($items);echo '<pre>'; exit;

		if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
			$ext = pathinfo($_FILES["file"]['name'], PATHINFO_EXTENSION);
			//echo $ext;exit;
//echo $_FILES["file"]["tmp_name"];exit;
			if ($ext == 'xls') {
				$excel2 = PHPExcel_IOFactory::createReader('Excel5');
			} else if ($ext == 'xlsx') {
				$excel2 = PHPExcel_IOFactory::createReader('Excel2007');
			} else {
		        	die('Invalid file format given' . $_FILES['file']);
			}

			 $excel2 = $objPHPExcel = $excel2 -> load($_FILES["file"]["tmp_name"]);
			// Empty Sheet

                	$sheet = $objPHPExcel -> getSheet(0);
			$highestRow = $sheet -> getHighestRow();

			$highestColumn = $sheet -> getHighestColumn();
			$temp = array();
			if ($sheet -> getCell('H4') -> getValue() != '') {
				$facility_code = $sheet -> getCell('H4') -> getValue();
			} else {
				$facility_code = $sheet -> getCell('J4') -> getValue();
			}

			 $checker = $sheet -> getCell('E17') -> getValue();

			//  Loop through each row of the worksheet in turn
			$array_code = array();
			$array_commodity = array();
			$array_category = array();
			$array_pack = array();
			$array_price = array();
			$array_order_qty = array();
			$array_order_val = array();

			//$array_code=array();
			for ($row = 17; $row <= $highestRow; $row++) {
				//  Read a row of data into an array
				$rowData = $objPHPExcel -> getActiveSheet() -> rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

				if ($checker == '#REF!' || $checker == '=VLOOKUP(C17,#REF!,1,FALSE)') {
					unset($rowData[0][4]);
					unset($rowData[0][5]);
					foreach ($rowData as $key => $value) {
						unset($rowData);
						$rowData[] = array_values($value);

					}

				}

				//count($rowData);
				$code = preg_replace('/\s+/ ', '', $rowData[0][2]);
				$code = str_replace('-', '', $code);
				$array_index[] = $rowData[0][1] - 1;
				$array_code[] = $code;
				$array_commodity[] = $rowData[0][3];
				$array_category[] = $rowData[0][4];
				$array_price[] = $rowData[0][6];
				$array_order_qty[] = (int)$rowData[0][7];
				$array_order_val[] = $rowData[0][8];
				$array_pack[] = $rowData[0][5];


			}

		// echo '<pre>'; print_r($array_order_qty);echo '<pre>'; exit;

			foreach ($array_order_qty as $id => $key) {

				array_push($temp, array('sub_category_name' => $array_category[$id], 'commodity_name' => $array_commodity[$id], 'unit_size' => $array_pack[$id], 'unit_cost' => ($array_price[$id] == '') ? 0 : (float)$array_price[$id], 'commodity_code' => preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $array_code[$id]), 'commodity_id' => $data['commodity_id'], 'quantity_ordered' => ($array_order_qty[$id] == '') ? 0 : (int)$array_order_qty[$id], 'total_commodity_units' => 0, 'opening_balance' => 0, 'total_receipts' => 0, 'total_issues' => 0, 'comment' => '', 'closing_stock_' => 0, 'closing_stock' => 0, 'days_out_of_stock' => 0, 'date_added' => '', 'losses' => 0, 'status' => 0, 'adjustmentpve' => 0, 'adjustmentnve' => 0, 'historical' => 0));

			}
			foreach ($temp as $key => $value) {
				
				if ($value['commodity_code'] == "" || $value['quantity_ordered'] == 0) {
					unset($temp[$key]);
				}

			}
			$array_id = array();
			$array_codes = array();
			$main_array = array();
			$weka = array();
			$k = 0; 

			// echo "<pre>";print_r($temp);echo "</pre>";exit;
			foreach ($temp as $keys) {

				$kemsa = $keys['commodity_code'];
				
				$kemsa = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $kemsa);
				
				$unit_cost = (int)$keys['unit_cost'];
				//echo $unit_cost.'-'.$k++;echo '<pre>';
				

				$get_id = Commodities::get_id($kemsa, $unit_cost);
				//print_r( $get_id.$k++);echo '<pre>';
				$weka[] = $get_id;
				$array_codes[] = $kemsa;
				$main_array[] = $keys;
				foreach ($get_id as $key2) {
					$array_id[] = $key2['id'];
					$array_total_units[] = $key2['total_commodity_units'];
					//echo '<pre>'; print_r($key2.'-'.$k++);echo '<pre>'; 

				}

			}
//exit;
			$array_combined = array();
			$id_count = count($main_array);
			// echo '<pre>'; print_r($id_count);echo '<pre>'; exit;

			for ($i = 1; $i < $id_count; $i++) {
				$main_array[$i]['commodity_id'] = $array_id[$i];
				$main_array[$i]['total_commodity_units'] = $array_total_units[$i];

			}
			$data['order_details'] = $data['facility_order'] = $main_array;
		} else {

			//create new array to hold pushed amc values
			$new = array();
			foreach ($items as $value) {

				$drud_id = $value['commodity_id'];
				$historical = $value['historical'];
				for ($i = 0; $i < count($items); $i++) {
					if ($drud_id == $amc_calc[$i]['commodity_id']) {

						$historical = $amc_calc[$i]['amc_packs'];

					}
				}
				$unit_size =  $value['unit_size'];
				$commodity_name =  $value['commodity_name'].' ('.$unit_size.')';
				array_push($new, array('sub_category_name' => $value['sub_category_name'], 'commodity_name' => $commodity_name, 'unit_size' => $unit_size, 'unit_cost' => $value['unit_cost'], 'commodity_code' => $value['commodity_code'], 'commodity_id' => $value['commodity_id'], 'quantity_ordered' => $value['quantity_ordered'], 'total_commodity_units' => $value['total_commodity_units'], 'opening_balance' => $value['opening_balance'], 'total_receipts' => $value['total_receipts'], 'total_issues' => $value['total_issues'], 'comment' => $value['comment'], 'closing_stock_' => $value['closing_stock_'], 'closing_stock' => $value['closing_stock'], 'days_out_of_stock' => $value['days_out_of_stock'], 'date_added' => $value['date_added'], 'losses' => $value['losses'], 'status' => $value['status'], 'adjustmentpve' => $value['adjustmentpve'], 'adjustmentnve' => $value['adjustmentnve'], 'historical' => round($historical)));
				// array_push($new, array('sub_category_name' => $value['sub_category_name'], 'commodity_name' => $value['commodity_name'], 'unit_size' => $value['unit_size'], 'unit_cost' => $value['unit_cost'], 'commodity_code' => $value['commodity_code'], 'commodity_id' => $value['commodity_id'], 'quantity_ordered' => $value['quantity_ordered'], 'total_commodity_units' => $value['total_commodity_units'], 'opening_balance' => $value['opening_balance'], 'total_receipts' => $value['total_receipts'], 'total_issues' => $value['total_issues'], 'comment' => $value['comment'], 'closing_stock_' => $value['closing_stock_'], 'closing_stock' => $value['closing_stock'], 'days_out_of_stock' => $value['days_out_of_stock'], 'date_added' => $value['date_added'], 'losses' => $value['losses'], 'status' => $value['status'], 'adjustmentpve' => $value['adjustmentpve'], 'adjustmentnve' => $value['adjustmentnve'], 'historical' => round($historical)));

			}
			
			$items = $new;
			$data['order_details'] = $data['facility_order'] = $items;
		}

		// $data['facility_order'] = $items;
		// echo '<pre>'; print_r($items);echo '<pre>'; exit;
		// $facility_code = $this -> session -> userdata('facility_id');
		$facility_data = Facilities::get_facility_name_($facility_code);	
		// echo "<pre>";print_r($facility_data);
		$source_name = ($source==2) ?'MEDS' : 'KEMSA' ;
		$data['content_view'] = ($source == 2) ? "facility/facility_orders/facility_order_meds_new" : "facility/facility_orders/facility_order_from_kemsa_v";
		$data['facility_code'] = $facility_code;
		$data['title'] = "Facility New Order";
		$data['banner_text'] = "Facility New Order ".$source_name;
		$data['drawing_rights'] = $facility_data[0]['drawing_rights'];
		$data['facility_commodity_list'] = ($source == 2) ? Commodities::get_meds_commodities_not_in_facility($facility_code,$source) : Commodities::get_commodities_not_in_facility($facility_code);

		// echo '<pre>'; print_r($data['facility_commodity_list']);echo '<pre>'; exit;

		$this -> load -> view('shared_files/template/template', $data);
	}

	public function facility_order_($facility_code = null) {
		// hack to ensure that when you are ordering for a facility that is not using hcmp they have all the items
		$checker = $this -> session -> userdata('facility_id') ? null : 1;
		if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
			$more_data = $this -> hcmp_functions -> kemsa_excel_order_uploader($_FILES["file"]["tmp_name"]);
			$data['order_details'] = $data['facility_order'] = $more_data['row_data'];

			$facility_data = Facilities::get_facility_name($more_data['facility_code']) -> toArray();
			$facility_code = $facility_data[0]['facility_code'];
			if (count($facility_data) == 0) {
				$this -> session -> set_flashdata('system_error_message', "Kindly upload a file with correct facility MFL code ");
				redirect("reports/order_listing/subcounty");
			}
			if ($facility_data[0]['using_hcmp'] == 1) {
				//$this -> session -> set_flashdata('system_error_message', "You cannot order for a" . " facility that is already using HCMP, they need to place their order using their accounts");
				//redirect("reports/order_listing/subcounty");
			}
		} else {
			$data['order_details'] = $data['facility_order'] = Facility_Transaction_Table::get_commodities_for_ordering($facility_code, $checker);
			$facility_data = Facilities::get_facility_name($facility_code) -> toArray();
		}
		$data['content_view'] = "facility/facility_orders/facility_order_from_kemsa_v";
		$data['title'] = "Facility New Order";
		$data['system_error_message'] = "You are ordering for " . $facility_data[0]['facility_name'];
		$data['facility_code'] = $facility_code;
		$data['banner_text'] = "Facility New Order";
		$data['drawing_rights'] = $facility_data[0]['drawing_rights'];
		$data['facility_commodity_list'] = Commodities::get_all_from_supllier(1);
		$this -> load -> view('shared_files/template/template', $data);
	}

	public function update_facility_order($order_id, $rejected = null, $option = null) {
		$order_data = facility_orders::get_order_($order_id) -> toArray();
		$data['content_view'] = "facility/facility_orders/update_order_facility_v";
		$data['title'] = "Facility Update Order";
		$data['banner_text'] = "Facility Update Order";
		$data['rejected'] = ($rejected == 'rejected') ? 1 : 0;
		$data['option_'] = ($option == 'readonly') ? 'readonly_' : 0;
		$data['order_details'] = $order_data;
		$items = facility_order_details::get_order_details($order_id);
		//create new array to hold pushed amc values
		$facility_code = $this -> session -> userdata('facility_id');
		$amc_calc = $this -> amc($county, $district, $facility_code);
		$new = array();
		foreach ($items as $value) {

			$drud_id = $value['commodity_id'];
			$historical = $value['historical'];
			for ($i = 0; $i < count($items); $i++) {
				if ($drud_id == $amc_calc[$i]['commodity_id']) {

					$historical = $amc_calc[$i]['amc_packs'];

				}
			}

			array_push($new, array('id' => $value['id'], 'sub_category_name' => $value['sub_category_name'], 'commodity_name' => $value['commodity_name'], 'unit_size' => $value['unit_size'], 'unit_cost' => $value['unit_cost'], 'scp_qty' => $value['scp_qty'], 'cty_qty' => $value['cty_qty'], 'commodity_code' => $value['commodity_code'], 'commodity_id' => $value['commodity_id'], 'quantity_ordered_pack' => $value['quantity_ordered_pack'], 'total_commodity_units' => $value['total_commodity_units'], 'opening_balance' => $value['opening_balance'], 'total_receipts' => $value['total_receipts'], 'total_issues' => $value['total_issues'], 'comment' => $value['comment'], 'closing_stock_' => $value['closing_stock_'], 'closing_stock' => $value['closing_stock'], 'days_out_of_stock' => $value['days_out_of_stock'], 'date_added' => $value['date_added'], 'losses' => $value['losses'], 'status' => $value['status'], 'adjustmentpve' => $value['adjustmentpve'], 'adjustmentnve' => $value['adjustmentnve'], 'historical' => round($historical)));

		}
		//echo '<pre>';print_r($new); echo '</pre>';exit;
		$items = $new;

		$data['facility_order'] = $items;
		$data['facility_commodity_list'] = Commodities::get_all_from_supllier(1);
		$this -> load -> view('shared_files/template/template', $data);
	}

	public function update_meds_facility_order($order_id) {
		$order_data = facility_orders::get_order_($order_id) -> toArray();
		$data['content_view'] = "facility/facility_orders/receive_meds_orders_v";
		$data['title'] = "Facility MEDS Receive Order";
		$data['banner_text'] = "Facility MEDS Receive Order";
		$data['rejected'] = ($rejected == 'rejected') ? 1 : 0;
		$data['option_'] = ($option == 'readonly') ? 'readonly_' : 0;
		$data['order_details'] = $order_data;
		$items = facility_order_details::get_order_details($order_id);
		//create new array to hold pushed amc values
		$facility_code = $this -> session -> userdata('facility_id');
		$amc_calc = $this -> amc($county, $district, $facility_code);
		$new = array();
		foreach ($items as $value) {

			$drud_id = $value['commodity_id'];
			$historical = $value['historical'];
			for ($i = 0; $i < count($items); $i++) {
				if ($drud_id == $amc_calc[$i]['commodity_id']) {

					$historical = $amc_calc[$i]['amc_packs'];

				}
			}

			array_push($new, array('id' => $value['id'], 'sub_category_name' => $value['sub_category_name'], 'commodity_name' => $value['commodity_name'], 'unit_size' => $value['unit_size'], 'unit_cost' => $value['unit_cost'], 'scp_qty' => $value['scp_qty'], 'cty_qty' => $value['cty_qty'], 'commodity_code' => $value['commodity_code'], 'commodity_id' => $value['commodity_id'], 'quantity_ordered_pack' => $value['quantity_ordered_pack'], 'total_commodity_units' => $value['total_commodity_units'], 'opening_balance' => $value['opening_balance'], 'total_receipts' => $value['total_receipts'], 'total_issues' => $value['total_issues'], 'comment' => $value['comment'], 'closing_stock_' => $value['closing_stock_'], 'closing_stock' => $value['closing_stock'], 'days_out_of_stock' => $value['days_out_of_stock'], 'date_added' => $value['date_added'], 'losses' => $value['losses'], 'status' => $value['status'], 'adjustmentpve' => $value['adjustmentpve'], 'adjustmentnve' => $value['adjustmentnve'], 'historical' => round($historical)));

		}
		// echo '<pre>';print_r($new); echo '</pre>';exit;
		$items = $new;

		$data['facility_order'] = $items;
		$data['facility_commodity_list'] = Commodities::get_all_from_meds(2);
		// echo "<pre>";
		// print_r($data['facility_commodity_list']);die;s
		$this -> load -> view('shared_files/template/template', $data);
	}

	public function update_order_subc($order_id, $rejected = null, $option = null) {//karsan
		$order_data = facility_orders::get_order_($order_id) -> toArray();
		$data['content_view'] = "facility/facility_orders/update_order_subc";
		$data['title'] = "Approve Order";
		$data['banner_text'] = "Approve Order";
		$data['rejected'] = ($rejected == 'rejected') ? 1 : 0;
		$data['option_'] = ($option == 'readonly') ? 'readonly_' : 0;
		$data['order_details'] = $order_data;
		// echo "<pre>";print_r($order_data);exit;
		if (($order_data[0]['source'] == 1) || ($order_data[0]['source'] == 0)) {
		 $commodity_list = Commodities::get_all_from_supllier(1);
		}elseif ($order_data[0]['source'] == 2) {
		 $commodity_list = Commodities::get_all_from_meds(2);
		}
		// echo "<pre>";print_r($order_data);echo "</pre>";exit;
		$data['facility_order'] = facility_order_details::get_order_details($order_id);
		$data['facility_commodity_list'] = $commodity_list;
		// echo "<pre>";print_r($data['facility_commodity_list']);echo "</pre>";exit;

		$this -> load -> view('shared_files/template/template', $data);
	}

	public function order_last_phase($order_id, $rejected = null, $option = null) {//karsan
		$order_data = facility_orders::get_order_($order_id) -> toArray();
		// echo "<pre>";print_r($order_data);echo "</pre>";exit;
		$data['content_view'] = "facility/facility_orders/update_order_kemsa_lastphase";
		$data['title'] = "Approve Order";
		$data['banner_text'] = "Approve Order";
		$data['rejected'] = ($rejected == 'rejected') ? 1 : 0;
		$data['option_'] = ($option == 'readonly') ? 'readonly_' : 0;
		$data['order_details'] = $order_data;
		if ($order_data[0]['source'] == 1) {
		$commodity_list = Commodities::get_all_from_supllier(1);
		}elseif ($order_data[0]['source'] == 2) {
		$commodity_list = Commodities::get_all_from_meds(2);
		}else{
		$commodity_list = Commodities::get_all_from_supllier(1);
		}
		$data['facility_commodity_list'] = $commodity_list;
		$data['facility_order'] = facility_order_details::get_order_details($order_id);
		// echo "<pre>";print_r($data['facility_order']);echo "</pre>";exit;
		$this -> load -> view('shared_files/template/template', $data);
	}

	public function facility_meds_order() {
		// echo "<pre>";print_r($this->input->post('commodity_code'));echo "</pre>";exit;
		//security check
		if ($this -> input -> post('commodity_code')) :
			$commodity_type = $this -> input -> post('commodity_type');
			$mfl = $this -> input -> post('mfl');
			// $commodity_code = $this -> input -> post('commodity_code');
			$quantity = $this -> input -> post('quantity');
			$order_cost = $this -> input -> post('cost');
			$this -> load -> database();
			$data_array = array();
			$commodity_id = $this -> input -> post('commodity_code');
			$order_date = date('y-m-d');
			$number_of_id = count($commodity_id);

			for ($i = 0; $i < $number_of_id; $i++) {
				$order_details = array("commodity_type" => $commodity_type[$i], 'mfl' => $mfl[$i], 'commodity_id' => $commodity_id[$i], 'quantity' => $quantity[$i], 'order_cost' => $order_cost[$i], 'order_date' => $order_date);
				// echo "<pre>";print_r($order_details);echo "</pre>";exit;
				//create the array to push to the db
				array_push($data_array, $order_details);

			}// insert the data here
			// echo "<pre>";print_r($data_array);echo"</pre>"; exit;
			$this -> db -> insert_batch('facility_orders_meds', $data_array);

			if ($this -> session -> userdata('user_indicator') == 'district') :
				$district_id = $this -> session -> userdata('district_id');
				$order_listing = 'subcounty';
			elseif ($this -> session -> userdata('user_indicator') == 'county') :
				$order_listing = 'county';
			else :
				$facility_code = $this -> session -> userdata('facility_id');

				$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($facility_code);
				$facility_name = $myobj -> facility_name;
				// get the order form details here
				//create the pdf here
				//echo "Its ait this far";
				//exit ;
				$pdf_body = $this -> create_order_pdf_template($new_order_no);
				$file_name = $facility_name . '_facility_order_no_' . $new_order_no . "_date_created_" . date('d-m-y');
				$pdf_data = array("pdf_title" => "Order Report For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'save_file', 'file_name' => $file_name);
				$this -> hcmp_functions -> create_pdf($pdf_data);
				// create pdf
				$this -> hcmp_functions -> clone_excel_order_template($new_order_no, 'save_file', $file_name);
				//create excel
				$order_listing = 'facility';
				$message_1 = '
			<br>
			Please find the Order Made by ' . $facility_name . ' below for approval.
			<br>
			You may log in to the HCMP system to approve it.<a href="http://health-cmp.or.ke/" target="_blank">Click here</a>
			<br>
			<br>
			<br>
			';
				$subject = 'Pending Approval Order Report For ' . $facility_name;

				$attach_file1 = './pdf/' . $file_name . '.pdf';
				$attach_file2 = base_url()."print_docs/excel/excel_files/" . $file_name . '.xls';

				$message = $message_1 . $pdf_body;

				// $response = $this -> hcmp_functions -> send_order_submission_email($message, $subject, $attach_file1 . "(more)" . $attach_file2, null);

				if ($response) {
					delete_files($attach_file1);
					unlink($attach_file2);
				} else {

				}

			endif;
			$user = $this -> session -> userdata('user_id');
			$user_action = "ordered";
			//updates the log table accordingly based on the action carried out by the user involved
			$update = Doctrine_Manager::getInstance()->getCurrentConnection();
			$update -> execute("update log set $user_action = 1  
			where `user_id`= $user 
			AND action = 'Logged In' 
			and UNIX_TIMESTAMP( `end_time_of_event`) = 0");
			

			$this -> session -> set_flashdata('system_success_message', "Facility Meds Order has Been Saved");
			redirect("home");
		endif;
	}//facility meds order terminado

	public function facility_new_order($source = NULL) {//karsan
		//security check
		// echo "<pre>";print_r($this->input->post()); echo "</pre>";exit;
		ini_set('max_input_vars', -1);
		$commodity_source = (isset($source))? $source : 1;//KEMSA by default
		if ($this -> input -> post('commodity_id')) :
			$this -> load -> database();
			$data_array = array();
			$commodity_id = $this -> input -> post('commodity_id');
			//order details table details
			$quantity_ordered_pack = $this -> input -> post('quantity');
			$quantity_ordered_units = $this -> input -> post('actual_quantity');
			$price = $this -> input -> post('price');
			$o_balance = $this -> input -> post('open');
			$t_receipts = $this -> input -> post('receipts');
			$t_issues = $this -> input -> post('issues');
			$adjustpve = $this -> input -> post('adjustmentpve');
			$adjustnve = $this -> input -> post('adjustmentnve');
			$losses = $this -> input -> post('losses');
			$days = $this -> input -> post('days');
			$c_stock = $this -> input -> post('closing');
			$comment = $this -> input -> post('comment');
			$comment = isset($comment)? $this -> input -> post('comment'):'N/A';
			$s_quantity = $this -> input -> post('suggested');
			$amc = $this -> input -> post('amc');
			$workload = $this -> input -> post('workload');
			//order table details
			$bed_capacity = '0';
			$drawing_rights = '0';
			$status = ($commodity_source==1)? '1' : '2';
			// $status = '2';
			$order_total = $this -> input -> post('total_order_value');
			$order_no = '0';
			$facility_code = $this -> input -> post('facility_code');
			$user_id = $this -> session -> userdata('user_id');
			$order_date = date('y-m-d');
			$number_of_id = count($commodity_id);
			if ($source==2) {
				$amc = ($amc==null) ? '0' : $amc;
				$days = ($days==null) ? '0' : $days;
				$o_balance = ($o_balance==null) ? '0' : $o_balance;
				$price = ($price==null) ? '0' : $price;
				$s_quantity = ($s_quantity==null) ? '0' : $s_quantity;
				$t_receipts = ($t_receipts==null) ? '0' : $t_receipts;
			}
			
			// echo "<pre>";print_r($number_of_id); echo "</pre>";exit;
			for ($i = 0; $i < $number_of_id; $i++) {
				if ($i == 0) {
					$order_details = array("workload" => $workload,
					 'bed_capacity' => $bed_capacity, 
					 'order_total' => $order_total, 
					 'order_no' => $order_no, 
					 'order_date' => $order_date, 
					 'facility_code' => $facility_code, 
					 'ordered_by' => $user_id, 
					 'status' => $status, 
					 'approval_date' => $order_date, 
					 'dispatch_date' => $order_date, 
					 'deliver_date' => $order_date, 
					 'drawing_rights' => $drawing_rights,
					 'source' => $commodity_source
					 );
					
					$insertion = $this -> db -> insert('facility_orders', $order_details);
					$new_order_no = $this -> db -> insert_id();
					// echo "<pre>I RAN : ";print_r($new_order_no);echo "</pre>";exit;
			$comment[$i] = isset($comment)? $this -> input -> post('comment'):'N/A';

				}
				
				$temp_array = array("commodity_id" => (int)$commodity_id[$i], 'quantity_ordered_pack' => (int)$quantity_ordered_pack[$i], 'quantity_ordered_unit' => (int)$quantity_ordered_units[$i], 'quantity_recieved' => 0, 'price' => $price[$i], 'o_balance' => $o_balance[$i], 't_receipts' => $t_receipts[$i], 't_issues' => $t_issues[$i], 'adjustpve' => $adjustpve[$i], 'adjustnve' => $adjustnve[$i], 'losses' => $losses[$i], 'days' => $days[$i], 'c_stock' => $c_stock[$i], 'comment' => $comment[$i], 's_quantity' => $s_quantity[$i], 'amc' => $amc[$i], 'order_number_id' => $new_order_no);
				//create the array to push to the db
				array_push($data_array, $temp_array);

			}
			// echo "<pre>";print_r($data_array); echo "</pre>";exit;
			$result = $this -> db -> insert_batch('facility_order_details', $data_array);
			if ($result){
				// echo "I WORK: ".$result;
				// $new_order_no = $this -> db -> insert_id();
				// echo $new_order_no;
				// exit;
			};
			if ($this -> session -> userdata('user_indicator') == 'district') :
				$order_listing = 'subcounty';
			elseif ($this -> session -> userdata('user_indicator') == 'county') :
				$order_listing = 'county';
			else :
				$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($facility_code);
				$facility_name = $myobj -> facility_name;
				// get the order form details here
				//create the pdf here
				//$pdf_body = $this -> create_order_pdf_template($new_order_no);
				$file_name = $facility_name . '_facility_order_no_' . $new_order_no . "_date_created_" . date('d-m-y');
				$pdf_data = array("pdf_title" => "Order Report For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'save_file', 'file_name' => $file_name);
				$this -> hcmp_functions -> create_pdf($pdf_data);
				// create pdf
				if ($source == 2) {
					//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
					// echo $new_order_no. "I WORK";exit;
				$file_name = $facility_name . '_facility_order_no_' . $new_order_no . "_MEDS_date_created_" . date('d-m-y');

					$result = $this -> hcmp_functions -> clone_meds_excel_order_template($new_order_no, 'save_file', $file_name);
					// echo "I WORKED ".$result;exit;
					$message = '
						<br>
						Please find the Order Made by ' . $facility_name . ' attached.
						<br>
					     <table width="50%" style="text-align:center;" >
							<thead>
							<tr style="">
						<th>Date Ordered</th>
						<th>Source</th>
								</tr>
							</thead>
							<tbody >
							<tr>
							<td style="text-align:center;">' . date('M , d Y') . '</td>
							<td style="text-align:center;">MEDS</td>
							</tr>
							</tbody>
							</table>
						<br>
						';


				$subject = 'MEDS Order Made By ' . $facility_name;

				$excel_order_value = $this-> get_excel_order_total($file_name,2);//karsan

				}else{
					// echo "CREATED";exit;
				$file_name = $facility_name . '_facility_order_no_' . $new_order_no . "_KEMSA_date_created_" . date('d-m-y');

					$result = $this -> hcmp_functions -> clone_excel_order_template($new_order_no, 'save_file', $file_name);
					// echo "<pre>"; print_r($file_name);echo "</pre>";
				$excel_order_value = $this-> get_excel_order_total($file_name,1);//karsan
				
				$message = '
						<br>
						Please find the Order Made by ' . $facility_name . ' attached.
						<br>
					     <table width="50%" style="text-align:center;" >
							<thead>
							<tr style="">
						<th>Date Ordered</th>
						<th>Order Value</th>
						<th>Source</th>
								</tr>
							</thead>
							<tbody >
							<tr>
							<td style="text-align:center;">' . date('M , d Y') . '</td>
							<td style="text-align:center;" > Ksh ' . number_format("$excel_order_value", 2) . '</td>
							<td style="text-align:center;">KEMSA</td>
							</tr>
							</tbody>
							</table>
						<br>
						';


				$subject = 'Order Pending Approval By Sub-County Pharmacist ' . $facility_name;
				}
				// echo("I REACH HERE");exit;
				//update_order_total_to_excel_total hack
				$update_order_total_to_excel_total= Doctrine_Manager::getInstance() -> getCurrentConnection() -> execute("
					UPDATE `facility_orders` SET `order_total`= $excel_order_value WHERE `id`= $new_order_no
					");
				//create excel
				$order_listing = 'facility';

				//$attach_file1 = './pdf/'.$file_name.'.pdf';
				$attach_file = FCPATH."print_docs/excel/excel_files/" . $file_name . '.xls';

				// $email_address = "kelvinmwas@gmail.com";//FOREFATHER
				$email_address = "ttunduny@gmail.com,karsanrichard@gmail.com";
				$response = $this -> hcmp_functions -> send_email($email_address, $message, $subject, $attach_file);
				// echo("Im here");exit;
				/*if ($response) {
					$this->hcmp_functions->download_file('/print_docs/excel/excel_files/'. $file_name . '.xls');
					// echo "I WORK";exit;
					// delete_files($attach_file);
					//unlink($attach_file);
				} else {

				}*/

			endif;
			//updates the log tables with the action
			$user = $this -> session -> userdata('user_id');
			$user_action = "ordered";
			//updates the log table accordingly based on the action carried out by the user involved
			$update = Doctrine_Manager::getInstance()->getCurrentConnection();
			$update -> execute("update log set $user_action = 1  
			where `user_id`= $user 
			AND action = 'Logged In' 
			and UNIX_TIMESTAMP( `end_time_of_event`) = 0");

			$this -> hcmp_functions -> send_order_sms();

			$this -> session -> set_flashdata('system_success_message', "Facility Order No $new_order_no has Been Saved");
			// $this->session->set_userdata($downloadable);
			$downloadable = array(
                   'downloadable_file' => $file_name
               );

			$this->session->set_userdata($downloadable);

			// echo "<pre>";print_r($this->session->all_userdata());echo "</pre>";exit;
			redirect("reports/order_listing/$order_listing");
			// $download = $this->download_contents($file_name,$new_order_no);
			// redirect("orders/download_contents($file_name)", 'refresh');
			// redirect("home");
			// echo "I REACHED HERE";exit;
			// echo "AND HERE".$download;exit;
		endif;

	}

	public function download_contents($filename = NULL){
		// $this->load->helper('download');
		// echo $filename;exit;
		$filename = urldecode($this->session->userdata('downloadable_file'));
		// $filename = str_replace("%20","/",$filename);
		// $filename =urldecode($filename);
		// echo "<pre>";print_r($this->session->userdata('downloadable_file'));echo "</pre>";exit;

		$full_path =  'print_docs/excel/excel_files/'.$filename.'.xls'; 	
		// $dis = $this->hcmp_functions->download_file($full_path);
		$data = file_get_contents($full_path); // Read the file's contents
		// echo $data;exit;
		force_download(basename($full_path), $data);
	}

	 
	 public function get_excel_order_total($filename = NULL,$source = NULL){
	 	// echo $filename;
 		if ($source == 1) {
 			$filename =isset($filename) ? $filename.'.xls' : time().'.xls';
	$inputFileName ="print_docs/excel/excel_files/".$filename;
	$excel2 = PHPExcel_IOFactory::createReader('Excel5');
    $excel2=$objPHPExcel= $excel2->load($inputFileName); // Empty Sheet
    
    $sheet = $objPHPExcel->getSheet(0); 
    $highestRow = $sheet->getHighestRow(); 
	
    $highestColumn = $sheet->getHighestColumn();
	$value = $sheet->getCell( 'J409' )->getCalculatedValue();
	
    $excel2->setActiveSheetIndex(0);

    return $value;
 		}elseif ($source == 2) {
 			$meds_total = 0;
 			$filename =isset($filename) ? $filename.'.xls' : time().'.xls';
 			$inputFileName ="print_docs/excel/excel_files/".$filename;
			$excel2 = PHPExcel_IOFactory::createReader('Excel5');
		    $excel2=$objPHPExcel= $excel2->load($inputFileName); // Empty Sheet
		    
		    $sheet = $objPHPExcel->getSheet(0); 
		    $highestRow = $sheet->getHighestRow(); 
			
		    $highestColumn = $sheet->getHighestColumn();
		    for ($i=0; $i < $highestRow ; $i++) { 
			$value = $sheet->getCell("K$i" )->getCalculatedValue();
			$meds_total = ($value > $meds_total)? $value: $meds_total;
		    }
			// echo $meds_total;
		    $excel2->setActiveSheetIndex(0);

 			// echo $filename;exit;
    return $meds_total;
 		}//end of if

 	}


	public function upd() {
		$this -> hcmp_functions -> send_sms();

	}

	public function update_order_facility($source = NULL) {//karsanrichard
		//security check
		// $dump=$this -> input -> post();
		// echo '<pre>';print_r($dump); echo '</pre>';exit;
		$user_indicator = $this -> session -> userdata('user_indicator');
		if ($this -> input -> post('commodity_id')) :
			//just picks values from the view and assigns them to a variable
			$this -> load -> database();
			$data_array = array();
			$rejected = $this -> input -> post('rejected');
			$rejected_admin = $this -> input -> post('rejected_admin');
			$approved_admin = $this -> input -> post('approved_admin');
			$commodity_id = $this -> input -> post('commodity_id');
			//order details table details
			$quantity_ordered_pack = $this -> input -> post('quantity');
			$order_id = $this -> input -> post('order_number');
			$facility_order_details_id = $this -> input -> post('facility_order_details_id');
			$quantity_ordered_unit = $this -> input -> post('actual_quantity');
			(int)$price = $this -> input -> post('price');
			$o_balance = $this -> input -> post('open');
			$t_receipts = $this -> input -> post('receipts');
			$t_issues = $this -> input -> post('issues');
			$adjustpve = $this -> input -> post('adjustmentpve');
			$adjustnve = $this -> input -> post('adjustmentnve');
			$losses = $this -> input -> post('losses');
			$days = $this -> input -> post('days');
			$c_stock = $this -> input -> post('closing');
			//$comment = array('N/A','N/A','N/A');
			$s_quantity = $this -> input -> post('suggested');
			(int)$amc = $this -> input -> post('amc');
			$workload = '0';
			//order table details
			$bed_capacity = '0';
			$drawing_rights = '0';
			// $order_total = $this -> input -> post('total_order_value');
			$order_total_ = facility_orders::get_order_cost($order_id);
			$order_total = $order_total_[0]['order_total'];
			// echo "<pre>";print_r($order_total);exit;

			$order_no = '0';
			//$facility_code=$this -> session -> userdata('facility_id');
			//$user_id=$this->session->userdata('user_id');
			$order_date = date('y-m-d');
			$number_of_id = count($commodity_id);
			$order_dates = facility_orders::get_order_($order_id);
			//echo '<pre>';print_r($order_dates[0]['order_date']); echo '</pre>';exit;
			$subject = $file_name = $title = $info = $attach_file = null;
			for ($i = 0; $i < $number_of_id; $i++) {

				if ($user_indicator == 'county') {
					$column_packs = 'cty_qty_packs';
					$column_units = 'cty_qty_units';
					$user_ind = 'county';
				} else if ($user_indicator == 'district') {
					$column_packs = 'scp_qty_packs';
					$column_units = 'scp_qty_units';
					$user_ind = 'subcounty';

				} else {
					$column_packs = 'quantity_ordered_pack';
					$column_units = 'quantity_ordered_unit';
					$user_ind = 'subcounty';

				}

				$orders = Doctrine_Manager::getInstance() -> getCurrentConnection() -> execute("INSERT INTO facility_order_details ( 
					`id`,
					`order_number_id`,
					`commodity_id`,
					`$column_packs`,
					`$column_units`,
					`price`,
					`o_balance`,
					`t_receipts`,
					`t_issues`,
					`adjustpve`,
					`losses`,
					`days`,
					`comment`,
					`c_stock`,
					`amc`,
					`adjustnve`,
					`source`)
					VALUES ($facility_order_details_id[$i],
					$order_id,
					$commodity_id[$i],
					$quantity_ordered_pack[$i],
					$quantity_ordered_unit[$i],
					0,
					$o_balance[$i],
					$t_receipts[$i],
					$t_issues[$i],
					$adjustpve[$i],
					$losses[$i],
					$days[$i],
					'$comment[$i]',
					$c_stock[$i],
					$amc[$i],
					$adjustnve[$i],
					$source
					)
					ON DUPLICATE KEY UPDATE
					`commodity_id`=$commodity_id[$i],
					`$column_packs`=$quantity_ordered_pack[$i],
					`$column_units`=$quantity_ordered_unit[$i],
					`price`=0,
					`o_balance`=$o_balance[$i],
					`t_receipts`=$t_receipts[$i],
					`t_issues`=$t_issues[$i],
					`adjustpve`=$adjustpve[$i],
					`adjustnve`=$adjustnve[$i],
					`losses`=$losses[$i],
					`days`=$days[$i],
					`c_stock`=$c_stock[$i],
					`comment`='$comment[$i]',
					`amc`=$amc[$i],
					`order_number_id`=$order_id;");

			}//insert the data here

			// $orders = Doctrine_Manager::getInstance() -> getCurrentConnection() -> execute("UPDATE `facility_orders` SET `order_total` = $order_total,`order_no` = $order_no
						// ,`workload` = $workload ,`bed_capacity` = $bed_capacity WHERE `facility_orders`.`id` = $order_id;");

			$myobj = Doctrine::getTable('facility_orders') -> find($order_id);
			$myobj -> workload = $workload;
			$myobj -> bed_capacity = $bed_capacity;
			$myobj -> order_no = $order_no;
			$myobj -> order_total = $order_total;
			$facility_code = $myobj -> facility_code;

			$myobj1 = Doctrine::getTable('Facilities') -> findOneByfacility_code($facility_code);
			$facility_name = $myobj1 -> facility_name;
			
			//$pdf_body = $this -> create_order_pdf_template($order_id);

			$file_name = $facility_name . '_facility_order_no_' . $order_id . "_date_created_" . date('d-m-y');

			//$pdf_data = array("pdf_title" => "Order Report For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'save_file', 'file_name' => $file_name);

			//$this -> hcmp_functions -> create_pdf($pdf_data);
			// create pdf
			$this -> hcmp_functions -> clone_excel_order_template($order_id, 'save_file', $file_name);
			//create excel

			//$attach_file1 = './pdf/' . $file_name . '.pdf';
			$attach_file = "./print_docs/excel/excel_files/" . $file_name . '.xls';
			//echo $attach_file;

			//exit;

			if ($rejected == 1) {
				$myobj -> status = 1;
				$status = "Updated";
				$subject = 'Updated Order Report Pending Approval For ' . $facility_name;

				//$attach_file = './pdf/' . $file_name . '.pdf';
			}
			if ($rejected_admin == 1) {
				$myobj -> status = 3;
				$status = "Rejected";
				$subject = 'Rejected Order Report For ' . $facility_name;
				$info = '<br> Note the order for  ' . $facility_name . ' Has been rejected by ' . $approve_name1 . ' ' . $approve_name2 . '.
				<br>
		  		Find the attached order, correct it
				<br>';

			}
			//final approval send to supplier
			if ($approved_admin == 1) {

				if ($user_indicator == 'county') {
					//get dates here
					$myobj -> status = 2;
					$tabledata = '<thead>
							<tr style="">
						<th>Date Ordered</th>
						<th>Date Approved Sub-County</th>
						<th>Date Approved County</th>
						<th>Order Value</th>
								</tr>
							</thead>
							<tbody >
							<tr>
							<td style="text-align:center;">' . date('M , d Y', strtotime($order_dates[0]['order_date'])) . '</td>
							<td style="text-align:center;">' . date('M , d Y', strtotime($order_dates[0]['approval_date'])) . '</td>
							<td style="text-align:center;">' . date('M , d Y') . '</td>
							<td style="text-align:center;" >Ksh ' . number_format("$order_total", 2) . '</td>
							</tr>
							</tbody>';
					$subject = 'Approved Order For ' . $facility_name;
					$myobj -> approval_county = date('y-m-d');

						// echo "<pre>  ".$subject."  ".$tabledata;exit;
				} else if ($user_indicator == 'district') {
					//get dates here
					$myobj -> status = 6;
					$tabledata = '<thead>
							<tr style="">
						<th>Date Ordered</th>
						<th>Date Approved Sub-County</th>
						<th>Order Value</th>
								</tr>
							</thead>
							<tbody >
							<tr>
							<td style="text-align:center;">' . date('M , d Y', strtotime($order_dates[0]['order_date'])) . '</td>
							<td style="text-align:center;">' . date('M , d Y') . '</td>
							<td style="text-align:center;" >Ksh ' . number_format("$order_total", 2) . '</td>
							</tr>
							</tbody>';
					$subject = 'Order Pending Approval By County Pharmacist ' . $facility_name;
					$myobj -> approval_date = date('y-m-d');

				}

				$myobj -> approved_by = $this -> session -> userdata('user_id');
				$status = "Approved";
				//$subject = 'Approved Order Report For ' . $facility_name;

			}

			$myobj -> save();

			$message = '
						<br>
						Please find the ' . $status . ' Order Made by ' . $facility_name . ' attached.
						<br>
					     <table width="75%" style="text-align:center;" >
							' . $tabledata . '
							</table>
						<br>
						';

			// echo $tabledata;exit;

			// $response = $this -> hcmp_functions -> send_order_approval_email($message, $subject, $attach_file, $facility_code, $status);

			if ($response) {
				delete_files($attach_file);
			} else {

			}
			//Test for sms
			//$this -> hcmp_functions -> order_update_sms($this -> session -> userdata('facility_id'),$status);
			$this -> session -> set_flashdata('system_success_message', "Facility Order No $order_id has Been $status");
			redirect("reports/order_listing/$user_ind");

		endif;

	}

	public function auto_save_order_detail() {
		//security check
		if ($this -> input -> is_ajax_request()) :
			$commodity_id = $this -> input -> post('commodity_id');
			$order_details_id = $this -> input -> post('order_details_id');
			$batch_no = $this -> input -> post('batch_no');
			$manu = $this -> input -> post('manu');
			$clone_datepicker = $this -> input -> post('clone_datepicker');
			$quantity = $this -> input -> post('quantity');
			$actual_quantity = $this -> input -> post('actual_quantity');
			//build the query to run
			$orders = Doctrine_Manager::getInstance() -> getCurrentConnection() -> execute("update facility_order_details 
             set
            `batch_no`='$batch_no',
            `quantity_recieved_pack`=$quantity,
            `quantity_recieved_unit`=$actual_quantity,
             `maun`='$manu',
            `expiry_date`='$clone_datepicker'
             where
            `id`=$order_details_id ");
			echo 'success';
		endif;
	}

	/*
	 |--------------------------------------------------------------------------
	 | End of update_facility_new_order
	 |--------------------------------------------------------------------------
	 Next section update_order_delivery get the view that loads up the order delivery details
	 */
	public function update_order_delivery($order_id) {
		$facility_code = $this -> session -> userdata('facility_id');
		$data['content_view'] = "facility/facility_orders/update_order_delivery_from_kemsa_v";
		$data['title'] = "Facility Update Order Delivery";
		$data['facility_commodity_list'] = Commodities::get_all_from_supllier(1);
		$data['order_details'] = facility_order_details::get_order_details($order_id);
		$data['general_order_details'] = facility_orders::get_order_($order_id);
		$data['banner_text'] = "Facility KEMSA Update Order Delivery";
		$this -> load -> view('shared_files/template/template', $data);

	}

	public function update_meds_order_delivery($order_id) {
		$facility_code = $this -> session -> userdata('facility_id');
		$data['content_view'] = "facility/facility_orders/update_order_delivery_from_meds_v";
		$data['title'] = "Facility Update Order Delivery";
		$data['facility_commodity_list'] = Commodities::get_all_from_meds(2);		
		$data['order_details'] = facility_order_details::get_order_details($order_id);		
		$data['general_order_details'] = facility_orders::get_order_($order_id);
		$data['banner_text'] = "Facility MEDS Update Order Delivery";
		$ordered_by = $data['general_order_details'][0]['ordered_by'];		
		$data['ordered_by'] = users::get_user_names($ordered_by);		
		$this -> load -> view('shared_files/template/template', $data);

	}


	public function delete_facility_order($for, $order_id) {
		$reset_facility_order_table = Doctrine_Manager::getInstance() -> getCurrentConnection();
		$reset_facility_order_table -> execute("DELETE FROM `facility_order_details` WHERE order_number_id=$order_id; ");

		$reset_facility_order_table = Doctrine_Manager::getInstance() -> getCurrentConnection();
		$reset_facility_order_table -> execute("DELETE FROM `facility_orders` WHERE  id=$order_id; ");

		$this -> session -> set_flashdata('system_success_message', "Order Number $order_id has been deleted");
		redirect("reports/order_listing/$for");
	}

	public function get_facility_sorf($order_id = null, $facility_code = null) {
		if (isset($order_id) && isset($facility_code)) :
			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($facility_code);
			$facility_name = $myobj -> facility_name;
			// get the order form details here
			//create the pdf here
			$pdf_body = $this -> create_order_pdf_template($order_id);
			$file_name = $facility_name . '_facility_order_no_' . $new_order_no . "_date_created_" . date('d-m-y');
			$pdf_data = array("pdf_title" => "Order Report For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'download', 'file_name' => $file_name);

			$this -> hcmp_functions -> create_pdf($pdf_data);
		endif;
		redirect();
	}

	public function create_order_pdf_template($order_no) {
		$from_order_table = facility_orders::get_order_($order_no);
		//get the order data here
		$from_order_details_table = Doctrine_Manager::getInstance() -> getCurrentConnection() -> fetchAll("SELECT 

    a.sub_category_name,
    b.commodity_name,
    b.commodity_code,
    b.unit_size,
    b.unit_cost,
    c.quantity_ordered_pack,
    c.quantity_ordered_unit,
    c.price,
    c.quantity_recieved,
    c.o_balance,
    c.t_receipts,
    c.t_issues,
    c.adjustnve,
    c.adjustpve,
    c.losses,
    c.days,
    c.comment,
    c.c_stock,
    c.s_quantity
    
FROM
    commodity_sub_category a,
    commodities b,
    facility_order_details c
WHERE
    c.order_number_id = $order_no
        AND b.id = c.commodity_id
        AND a.id = b.commodity_sub_category_id
group by b.id
ORDER BY a.id ASC , b.commodity_name ASC  ");
		// get the order details here
		$from_order_details_table_count = count($from_order_details_table);
		foreach ($from_order_table as $order) {
			$o_no = $order -> order_no;
			$o_bed_capacity = $order -> bed_capacity;
			$o_workload = $order -> workload;
			$o_date = $order -> order_date;
			$a_date = $order -> approval_date;
			$a_date = strtotime($a_date) ? date('d M, Y', strtotime($a_date)) : "N/A";
			//checkdate ( (int) date('m', $unixtime) , (int) date('d', $unixtime) , date('y', $unixtime ) ) ? date('d M, Y',$unixtime) : "N/A";
			$o_total = $order -> order_total;
			$d_rights = $order -> drawing_rights;
			$bal = $d_rights - $o_total;
			$creator = $order -> ordered_by;
			$approver = $order -> approved_by;
			$mfl = $order -> facility_code;

			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($mfl);
			$sub_county_id = $myobj -> district;
			// get the order form details here

			$myobj1 = Doctrine::getTable('Districts') -> find($sub_county_id);
			$sub_county_name = $myobj1 -> district;
			$county = $myobj1 -> county;

			$myobj2 = Doctrine::getTable('Counties') -> find($county);
			$county_name = $myobj2 -> county;

			$myobj_order = Doctrine::getTable('users') -> find($creator);
			$creator_email = $myobj_order -> email;
			$creator_name1 = $myobj_order -> fname;
			$creator_name2 = $myobj_order -> lname;
			$creator_telephone = $myobj_order -> telephone;

			$myobj_order_ = Doctrine::getTable('users') -> find($approver);
			$approver_email = $myobj_order_ -> email;
			$approver_name1 = $myobj_order_ -> fname;
			$approver_name2 = $myobj_order_ -> lname;
			$approver_telephone = $myobj_order_ -> telephone;

		}
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
<tr>
<td>MFL No: $mfl</td> 
<td>Health Facility Name:<br/> $facility_name</td>
<td>Total OPD Visits & Revisits: $o_workload </td>
<td>Level:</td>
<td>Dispensary</td>
<td>Health Centre</td>
</tr>
<tr>
<td>County: $county_name</td> 
<td> District: $sub_county_name</td>
<td>In-patient Bed Days :$o_bed_capacity </td>
<td>Order Date:<br/> " . date('d M, Y', strtotime($o_date)) . " </td>
<td>Order no. $o_no</td>
<td >Reporting Period <br/>
Start Date:  <br/>  End Date: " . date('d M, Y', strtotime($o_date)) . "
</td>
</tr>
</table>";
		$html_body .= "
<table class='data-table'>
<thead><tr><th><b>KEMSA Code</b></th><th><b>Description</b></th><th><b>Order Unit Size</b>
</th><th><b>Order Unit Cost</b></th><th ><b>Opening Balance</b></th>
<th ><b>Total Receipts</b></th><th><b>Total issues</b></th><th><b>Adjustments(-ve)</b></th><th><b>Adjustments(+ve)</b></th>
<th><b>Losses</b></th><th><b>Closing Stock</b></th><th><b>No days out of stock</b></th>
<th><b>Order Quantity (Packs)</b></th><th><b>Order Quantity (Units)</b></th><th>
<b>Order cost(Ksh)</b></th><th><b>Comment</b></th></tr> </thead><tbody>";

		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $from_order_details_table_count; $i++) {
			if ($i == 0) {
				$html_body .= '<tr style="background-color:#C6DEFF;"> <td colspan="16" >
				 <li> ' . $from_order_details_table[$i]['sub_category_name'] . ' </li> </td></tr>';
			} else if ($from_order_details_table[$i]['sub_category_name'] != $from_order_details_table[$i - 1]['sub_category_name']) {
				$html_body .= '<tr style="background-color:#C6DEFF;"> <td  colspan="15"> 
       	 	<li> ' . $from_order_details_table[$i]['sub_category_name'] . ' </li> </td></tr>';
			}
			$adjpve = $from_order_details_table[$i]['adjustpve'];
			$adjnve = $from_order_details_table[$i]['adjustnve'];
			$c_stock = $from_order_details_table[$i]['c_stock'];
			$o_t = $from_order_details_table[$i]['quantity_ordered_pack'];
			$o_q = $from_order_details_table[$i]['price'];
			$o_bal = $from_order_details_table[$i]['o_balance'];
			$t_re = $from_order_details_table[$i]['t_receipts'];
			$t_issues = $from_order_details_table[$i]['t_issues'];
			$losses = $from_order_details_table[$i]['losses'];
			$adj = $adjpve + $adjnve;
			if ($o_bal == 0 && $t_re == 0 && $t_issues > 0) {
				$adj = $t_issues;
			}
			$c_stock = $o_bal + $t_re + $adj - $losses - $t_issues;

			if ($c_stock < 0) {
				$adj = $c_stock * -1;
			}
			$c_stock = $o_bal + $t_re + $adj - $losses - $t_issues;
			$html_body .= "<tr>";
			$html_body .= "<td>" . $from_order_details_table[$i]['commodity_code'] . "</td>";
			$html_body .= "<td>" . $from_order_details_table[$i]['commodity_name'] . "</td>";
			$html_body .= "<td>" . $from_order_details_table[$i]['unit_size'] . "</td>";
			$ot = number_format($o_t * $o_q, 2, '.', ',');
			$order_total = $order_total + ($o_t * $o_q);
			$html_body .= "<td>$o_q</td>";
			$html_body .= "<td>" . $o_bal . "</td>";
			$html_body .= "<td>" . $t_re . "</td>";
			$html_body .= "<td>" . $t_issues . "</td>";
			$html_body .= "<td>" . $adjnve . "</td>";
			$html_body .= "<td>" . $adjpve . "</td>";
			$html_body .= "<td>" . $losses . "</td>";
			$html_body .= "<td>" . $c_stock . "</td>";
			$html_body .= "<td>" . $from_order_details_table[$i]['days'] . "</td>";
			$html_body .= "<td>$o_t</td>";
			$html_body .= "<td>" . $from_order_details_table[$i]['quantity_ordered_unit'] . "</td>";
			$html_body .= "<td>$ot</td>";
			$html_body .= "<td>" . $from_order_details_table[$i]['comment'] . "</td></tr>";
		}

		$bal = $d_rights - $order_total;
		$html_body .= '</tbody></table></ol>';
		$html_body1 = '<table class="data-table" width="100%" style="background-color: 	#FFF380;">
		  <tr style="background-color: 	#FFFFFF;" > <td colspan="4" ><div style="width:100%">
		  <div style="float:right; width: 40%;"> Total Order Value:</div>
		  <div style="float:right; width: 40%;" >KSH ' . number_format($order_total, 2, '.', ',') . '</div> </div></td></tr>
		  <tr style="background-color: 	#FFFFFF;"  > 
		  <td colspan="4" ><div>
		  <div style="float: left" > Drawing Rights Available Balance:</div>
		  <div style="float: right" >KSH		' . number_format($bal, 2, '.', ',') . '</div> </td></tr>
		  <tr><td>FACILITY TEL NO:</td><td colspan="3">FACILITY EMAIL:</td>
		  </tr>
		  <tr><td >Prepared by (Name/Designation) ' . $creator_name1 . ' ' . $creator_name2 . '
		  <br/>
		  <br/>Email: ' . $creator_email . '</td><td>Tel: ' . $creator_telephone . '</td><td>Date: ' . date('d M, Y', strtotime($o_date)) . '</td><td>Signature</td>
		  </tr>
		  <tr><td>Checked by (Name/DPF/DPHN)  ' . $approver_name1 . ' ' . $approver_name2 . '
		  <br/>
		  <br/>Email: ' . $approver_email . '</td><td>Tel: ' . $approver_telephone . '</td><td>Date: ' . $a_date . '</td><td>Signature</td>
		  </tr>
		  <tr><td>Authorised by (Name/DMoH) 
		  <br/>
		  <br/>Email:</td><td>Tel: </td><td>Date:</td><td>Signature</td>		   
		  </tr>
		  </table>';

		return $html_body . $html_body1;

	}

	public function amc($county = null, $district = null, $facility_code = null) {
		$district = ($district == "NULL") ? null : $district;
		$facility_code = ($facility_code == "NULL") ? null : $facility_code;
		$county = ($county == "NULL") ? null : $county;

		if (isset($county)) {

			$get_amc = Doctrine_Manager::getInstance() -> getCurrentConnection() -> fetchAll("select commodity_name,commodity_id,avg(facility_issues.qty_issued) as totalunits,
					(avg(facility_issues.qty_issued))/commodities.total_commodity_units as amc_packs,
					commodities.total_commodity_units from facility_issues inner join commodities on facility_issues.commodity_id=commodities.id
					inner join facilities on facility_issues.facility_code=facilities.facility_code inner join districts
					on facilities.district=districts.id where districts.county= $county and s11_No IN('internal issue','(-ve Adj) Stock Deduction')
					group by commodity_id");

			//echo '<pre>'; print_r($get_amc);echo '<pre>';

		} elseif (isset($district)) {

			$get_amc = Doctrine_Manager::getInstance() -> getCurrentConnection() -> fetchAll("select commodity_name,commodity_id,avg(facility_issues.qty_issued) as totalunits,
					(avg(facility_issues.qty_issued))/commodities.total_commodity_units as amc_packs,
					commodities.total_commodity_units from facility_issues inner join commodities on facility_issues.commodity_id=commodities.id inner join facilities
					on facility_issues.facility_code=facilities.facility_code where facilities.district=$district
					and s11_No IN('internal issue','(-ve Adj) Stock Deduction') group by commodity_id");

			//echo '<pre>'; print_r($get_amc);echo '<pre>';

		} elseif (isset($facility_code)) {

			$getdates = Doctrine_Manager::getInstance() -> getCurrentConnection() -> fetchAll("SELECT MIN(created_at) as EarliestDate,MAX(created_at) as LatestDate
					FROM facility_issues WHERE facility_code=$facility_code");

			//echo '<pre>'; print_r($getdates);echo '<pre>'; exit;
			$early = $getdates[0]['EarliestDate'];
			$late = $getdates[0]['LatestDate'];

			$now = time();
			$my_date = strtotime($early);
			$datediff = ($now - $my_date) / (60 * 60 * 24);
			//in days
			$datediff = round($datediff, 1);

			$get_amc = Doctrine_Manager::getInstance() -> getCurrentConnection() -> fetchAll("select commodity_id,sum(facility_issues.qty_issued) as units,(sum(facility_issues.qty_issued)*30/$datediff)/commodities.total_commodity_units as amc_packs,
						commodities.total_commodity_units from facility_issues inner join commodities on facility_issues.commodity_id=commodities.id
						where facility_code=$facility_code and s11_No IN('internal issue','(-ve Adj) Stock Deduction') group by commodity_id");

			//echo '<pre>'; print_r($get_amc);echo '<pre>'; exit;
			return $get_amc;

		} else {

			echo "national";
		}

	}

}
