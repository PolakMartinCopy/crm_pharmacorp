<?php 
App::import('Model', 'Transaction');
class DeliveryNote extends Transaction {
	var $name = 'DeliveryNote';
	
	var $useTable = 'transactions';
	
	var $export_file = 'files/delivery_notes.csv';
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->bindModel(
			array(
				'belongsTo' => array(
					'Shipping' => array(
						'className' => 'Shipping'
					)
				)
			)
		);
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
		$export_fields = array(
			array('field' => 'ProductsTransaction.id', 'position' => '["ProductsTransaction"]["id"]', 'alias' => 'ProductsTransaction.id'),
			array('field' => $this->alias . '.date', 'position' => '["' . $this->alias . '"]["date"]', 'alias' => $this->alias . '.date'),
			array('field' => $this->alias . '.code', 'position' => '["' . $this->alias . '"]["code"]', 'alias' => $this->alias . '.code'),
			array('field' => $this->Purchaser->userName . ' AS purchaser_user_full_name', 'position' => '[0]["purchaser_user_full_name"]', 'alias' => 'PurchaserUser.fullname'),
			array('field' => 'BusinessPartner.id', 'position' => '["BusinessPartner"]["id"]', 'alias' => 'BusinessPartner.id'),
			array('field' => 'BusinessPartner.name', 'position' => '["BusinessPartner"]["name"]', 'alias' => 'BusinessPartner.name'),
			array('field' => 'BusinessPartner.ico', 'position' => '["BusinessPartner"]["ico"]', 'alias' => 'BusinessPartner.ico'),
			array('field' => 'Purchaser.id', 'position' => '["Purchaser"]["id"]', 'alias' => 'Purchaser.id'),
			array('field' => $this->Purchaser->virtualFields['name'], 'position' => '[0][\'' .  $this->Purchaser->virtualFields['name'] . '\']', 'alias' => 'Purchaser.name', 'escape_quotes' => false),
			array('field' => 'Product.id', 'position' => '["Product"]["id"]', 'alias' => 'Product.id'),
			array('field' => 'Product.name', 'position' => '["Product"]["name"]', 'alias' => 'Product.name'),
			array('field' => 'Product.vzp_code', 'position' => '["Product"]["vzp_code"]', 'alias' => 'Product.vzp_code'),
			array('field' => 'Product.group_code', 'position' => '["Product"]["group_code"]', 'alias' => 'Product.group_code'),
			array('field' => 'ProductsTransaction.quantity', 'position' => '["ProductsTransaction"]["quantity"]', 'alias' => 'ProductsTransaction.quantity'),
			array('field' => 'ProductsTransaction.lot', 'position' => '["ProductsTransaction"]["lot"]', 'alias' => 'ProductsTransaction.lot'),
			array('field' => 'ProductsTransaction.exp', 'position' => '["ProductsTransaction"]["exp"]', 'alias' => 'ProductsTransaction.exp'),
			array('field' => 'ProductsTransaction.unit_price', 'position' => '["ProductsTransaction"]["unit_price"]', 'alias' => 'ProductsTransaction.unit_price'),
			array('field' => '`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity` AS `ProductsTransaction__total_price`', 'position' => '["ProductsTransaction"]["total_price"]', 'alias' => 'ProductsTransaction.total_price'),
			array('field' => 'Unit.shortcut', 'position' => '["Unit"]["shortcut"]', 'alias' => 'Unit.shortcut'),
			array('field' => 'Address.street', 'position' => '["Address"]["street"]', 'alias' => 'Address.street'),
			array('field' => 'Address.number', 'position' => '["Address"]["number"]', 'alias' => 'Address.number'),
			array('field' => 'Address.city', 'position' => '["Address"]["city"]', 'alias' => 'Address.city'),
			array('field' => 'Address.zip', 'position' => '["Address"]["zip"]', 'alias' => 'Address.zip'),
			array('field' => 'Address.region', 'position' => '["Address"]["region"]', 'alias' => 'Address.region'),
		);
	
		return $export_fields;
	}
}
?>
