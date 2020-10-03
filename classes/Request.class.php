<?php 
namespace CanadaPost;

class Request {
	private $jsonData;

	function __construct(){
		$jsonData = json_decode(trim(file_get_contents('php://input')), true);
		$this->jsonData = is_array($jsonData) ? $jsonData : array();
	}

	public function get($name = ''){

		$data = filter_var_array($this->jsonData, FILTER_SANITIZE_STRING);  
		if(!empty($name)) {
			return isset($data[$name]) ? $data[$name] : null;
		}
		return $data;
	}
}
