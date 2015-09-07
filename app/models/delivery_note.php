<?php 
App::import('Model', 'Transaction');
class DeliveryNote extends Transaction {
	var $name = 'DeliveryNote';
	
	var $useTable = 'transactions';
	
	var $export_file = 'files/delivery_notes.csv';
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}
	
	function beforeFind($queryData) {
		$queryData['conditions']['DeliveryNote.transaction_type_id'] = 1;
		return $queryData;
	}
	
	function pdf_generate($id) {
		$file_name = DL_FOLDER . $this->docNumberById($id) . '.pdf';
		if ($fp = fopen($file_name, "w")) {
			$url = 'http://' . $_SERVER['HTTP_HOST'] . '/delivery_notes/view_pdf/' . $id;
			$ch = curl_init($url);
				
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, false);
		
			curl_exec($ch);
			curl_close($ch);
			
			fclose($fp);
		}
	}
	
	function export_fields() {
		$export_fields = parent::export_fields();
	
		$delivery_note_export_fields = array(
			array('field' => 'Address.street', 'position' => '["Address"]["street"]', 'alias' => 'Address.street'),
			array('field' => 'Address.number', 'position' => '["Address"]["number"]', 'alias' => 'Address.number'),
			array('field' => 'Address.city', 'position' => '["Address"]["city"]', 'alias' => 'Address.city'),
			array('field' => 'Address.zip', 'position' => '["Address"]["zip"]', 'alias' => 'Address.zip'),
			array('field' => 'Address.region', 'position' => '["Address"]["region"]', 'alias' => 'Address.region'),
		);
		$export_fields = array_merge($export_fields, $delivery_note_export_fields);
		
		return $export_fields;
	}
}
?>
