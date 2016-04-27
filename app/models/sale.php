<?php 
App::import('Model', 'Transaction');
class Sale extends Transaction {
	var $name = 'Sale';
	
	var $useTable = 'transactions';
	
	var $export_file = 'files/sales.csv';
	
	var $delivery_note_created = false;
	
	function __construct($id = null, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}
	
	function beforeFind($queryData) {
		$queryData['conditions']['Sale.transaction_type_id'] = 3;
		return $queryData;
	}
	
	function afterSave($created) {
		$data = $this->data;
		parent::afterSave($created);
		if ($created) {
			$wallet = 0;
			foreach ($data['ProductsTransaction'] as $pt) {
				$wallet += $pt['education'] * (-$pt['quantity']);
			}
			
			return $this->Purchaser->wallet_transaction($data['Sale']['purchaser_id'], $wallet, 'sale', $this->id, $data['Sale']['user_id']);
		}
		return true;
	}
	
	function getPrice($id) {
		$sale = $this->find('first', array(
			'conditions' => array('Sale.id' => $id),
			'contain' => array('ProductsTransaction'),
		));
		$wallet = 0;
		foreach ($sale['ProductsTransaction'] as $pt) {
			$wallet += $pt['education'] * (-$pt['quantity']);
		}
		
		return $wallet;
	}
	
	function getLast($purchaserId, $productId) {
		$last = $this->ProductsTransaction->find('first', array(
			'conditions' => array(
				'Sale.purchaser_id' => $purchaserId,
				'ProductsTransaction.product_id' => $productId,
				// kdyz mam sale v contain, nebere to Sale::beforeFind, takze omezeni na to, ze je to typ "prodej" musim pridat implicitne
				'Sale.transaction_type_id' => 3
			),
			'contain' => array('Sale'),
			'fields' => array('Sale.date'),
			'order' => array('Sale.date' => 'desc')
		));
		
		return $last;
	}
	
	function getLastDate($purchaserId, $productId, $czech = false) {
		if ($last = $this->getLast($purchaserId, $productId)) {
			if ($czech) {
				return czech_date($last['Sale']['date']);
			}
			return $last['Sale']['date'];
		}
		
		return false;
	}
	
	function export_fields() {
		$export_fields = array(
			array('field' => 'ProductsTransaction.id', 'position' => '["ProductsTransaction"]["id"]', 'alias' => 'ProductsTransaction.id'),
			array('field' => $this->alias . '.date', 'position' => '["' . $this->alias . '"]["date"]', 'alias' => $this->alias . '.date'),
			array('field' => $this->alias . '.code', 'position' => '["' . $this->alias . '"]["code"]', 'alias' => $this->alias . '.code'),
			array('field' => $this->purchaserUserName . ' AS purchaser_user_full_name', 'position' => '[0]["purchaser_user_full_name"]', 'alias' => 'PurchaserUser.fullname'),
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
			array('field' => 'ProductsTransaction.unit_price', 'position' => '["ProductsTransaction"]["unit_price"]', 'alias' => 'ProductsTransaction.unit_price'),
			array('field' => '`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity` AS `ProductsTransaction__total_price`', 'position' => '["ProductsTransaction"]["total_price"]', 'alias' => 'ProductsTransaction.total_price'),
			array('field' => 'Unit.shortcut', 'position' => '["Unit"]["shortcut"]', 'alias' => 'Unit.shortcut'),
			array('field' => 'Address.street', 'position' => '["Address"]["street"]', 'alias' => 'Address.street'),
			array('field' => 'Address.number', 'position' => '["Address"]["number"]', 'alias' => 'Address.number'),
			array('field' => 'Address.city', 'position' => '["Address"]["city"]', 'alias' => 'Address.city'),
			array('field' => 'Address.zip', 'position' => '["Address"]["zip"]', 'alias' => 'Address.zip'),
			array('field' => 'Address.region', 'position' => '["Address"]["region"]', 'alias' => 'Address.region'),
		);
		
		// u prodeju se chci zbavit zapornych hodnot u mnozstvi a celkove ceny
		$res = array();
		foreach ($export_fields as $export_field) {
			if ($export_field['alias'] == 'ProductsTransaction.quantity') {
				$res[] = array(
					'field' => 'ABS(`ProductsTransaction`.`quantity`) AS ProductsTransaction__abs_quantity',
					'position' => '["ProductsTransaction"]["abs_quantity"]',
					'alias' => 'ProductsTransaction.abs_quantity'
				);
			} elseif ($export_field['alias'] == 'ProductsTransaction.total_price') {
				$res[] = array(
					'field' => 'ABS(`ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`) AS ProductsTransaction__abs_total_price',
					'position' => '["ProductsTransaction"]["abs_total_price"]',
					'alias' => 'ProductsTransaction.abs_total_price'
				);
			} elseif ($export_field['alias'] == 'Transaction.margin') {
				$res[] = array(
					'field' => 'ABS(ROUND((`ProductsTransaction`.`product_margin` * `ProductsTransaction`.`unit_price` * `ProductsTransaction`.`quantity`) / 100, 2)) AS Transaction__abs_margin',
					'position' => '["Transaction"]["abs_margin"]',
					'alias' => 'Transaction.abs_margin'
				);
			} else {
				$res[] = $export_field;
			}
		}
		return $res;
	}
}
?>
